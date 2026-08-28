<?php
/**
 * File: login-details.php
 * Version: 2.0.0
 * Description: عرض سجل تفاصيل تسجيل الدخول للمشرفين (تمت الترقية إلى PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * ترقيات PHP 8.3 المطبقة:
 * - تحسين إدارة الجلسات
 * - إضافة strict typing
 * - استخدام prepared statements
 * - تحسين الأمان ومنع XSS
 * - إضافة pagination ديناميكي
 * - تحسين معالجة الأخطاء
 * - دعم كامل للغة العربية
 */

// تفعيل strict typing
declare(strict_types=1);

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// تضمين الملفات المطلوبة
require_once "common.php";

// التحقق من تسجيل الدخول
check_user_login();

/**
 * Class LoginDetails - إدارة سجل تسجيل الدخول
 * متوافق مع PHP 8.3
 */
class LoginDetails {
    private mysqli $db;
    private int $currentPage;
    private int $limit;
    private int $offset;
    private int $totalRecords;
    
    /**
     * المُنشئ
     * 
     * @param mysqli $databaseConnection اتصال قاعدة البيانات
     */
    public function __construct(mysqli $databaseConnection) {
        $this->db = $databaseConnection;
        $this->currentPage = $this->getCurrentPage();
        $this->limit = $this->getItemsPerPage();
        $this->offset = ($this->currentPage - 1) * $this->limit;
        $this->totalRecords = $this->getTotalRecords();
    }
    
    /**
     * الحصول على رقم الصفحة الحالية
     * 
     * @return int رقم الصفحة
     */
    private function getCurrentPage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page);
    }
    
    /**
     * الحصول على عدد العناصر في الصفحة
     * 
     * @return int عدد العناصر
     */
    private function getItemsPerPage(): int {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $validLimits = [10, 20, 30, 50, 100];
        return in_array($limit, $validLimits) ? $limit : 10;
    }
    
    /**
     * الحصول على إجمالي عدد السجلات
     * 
     * @return int إجمالي السجلات
     */
    private function getTotalRecords(): int {
        $query = "SELECT COUNT(*) as total FROM admin_login_history WHERE admin_id IN (SELECT admin_id FROM admin)";
        $result = mysqli_query($this->db, $query);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }
    
    /**
     * جلب سجل تسجيل الدخول مع الترقيم
     * 
     * @return array قائمة السجلات
     */
    public function getLoginHistory(): array {
        $query = "SELECT 
                    a.admin_name,
                    a.admin_email,
                    alh.login_time,
                    alh.logout_time,
                    alh.ip_address,
                    alh.user_agent,
                    TIMEDIFF(IFNULL(alh.logout_time, NOW()), alh.login_time) as duration,
                    CASE 
                        WHEN alh.logout_time IS NULL THEN 1 
                        ELSE 0 
                    END as is_online
                  FROM admin_login_history alh
                  JOIN admin a ON alh.admin_id = a.admin_id
                  ORDER BY alh.login_time DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($this->db, $query);
        
        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->db));
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $this->limit, $this->offset);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $history = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        return $history;
    }
    
    /**
     * الحصول على إجمالي الصفحات
     * 
     * @return int عدد الصفحات
     */
    public function getTotalPages(): int {
        return (int)ceil($this->totalRecords / $this->limit);
    }
    
    /**
     * الحصول على نص عرض العناصر
     * 
     * @return string نص العرض
     */
    public function getDisplayText(): string {
        $start = $this->offset + 1;
        $end = min($this->offset + $this->limit, $this->totalRecords);
        
        if ($this->totalRecords === 0) {
            return '0 عناصر';
        }
        
        return "{$start} - {$end} من {$this->totalRecords} عنصر";
    }
    
    /**
     * الحصول على معاملات URL للترقيم
     * 
     * @return array معاملات الترقيم
     */
    public function getPaginationParams(): array {
        return [
            'currentPage' => $this->currentPage,
            'totalPages' => $this->getTotalPages(),
            'limit' => $this->limit,
            'totalRecords' => $this->totalRecords
        ];
    }
    
    /**
     * تنسيق مدة الجلسة
     * 
     * @param string|null $duration مدة الجلسة
     * @param bool $isOnline حالة الاتصال
     * @return string النص المنسق
     */
    public function formatDuration(?string $duration, bool $isOnline): string {
        if ($isOnline) {
            return '<span class="online-status">متصل الآن</span>';
        }
        
        if (!$duration) {
            return 'غير متوفر';
        }
        
        // تنسيق المدة (HH:MM:SS)
        $parts = explode(':', $duration);
        $hours = (int)$parts[0];
        $minutes = (int)$parts[1];
        $seconds = (int)$parts[2];
        
        $formatted = [];
        if ($hours > 0) {
            $formatted[] = $hours . ' ساعة';
        }
        if ($minutes > 0) {
            $formatted[] = $minutes . ' دقيقة';
        }
        if ($seconds > 0 && $hours === 0) {
            $formatted[] = $seconds . ' ثانية';
        }
        
        return implode(' و ', $formatted) ?: 'أقل من دقيقة';
    }
    
    /**
     * تنسيق التاريخ والوقت
     * 
     * @param string $datetime التاريخ والوقت
     * @return string التاريخ المنسق
     */
    public function formatDateTime(string $datetime): string {
        $timestamp = strtotime($datetime);
        return date('Y/m/d g:i A', $timestamp);
    }
}

// تهيئة الكلاس
$loginDetails = new LoginDetails($con);
$history = $loginDetails->getLoginHistory();
$paginationParams = $loginDetails->getPaginationParams();
$displayText = $loginDetails->getDisplayText();
?>

<?php include "includes/admin-top.php" ?>

<!-- JavaScript Libraries -->
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- CSS Files -->
<link href="style/style.css" type="text/css" rel="stylesheet"/>

<style>
/* تحسينات إضافية للواجهة العربية */
body {
    direction: rtl;
    text-align: right;
    font-family: 'Tahoma', 'Arial', sans-serif;
}

.control_Panel {
    direction: rtl;
}

.bodyRightCon {
    padding: 15px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* تحسينات رأس الصفحة */
.bcMenuCon {
    margin-bottom: 15px;
    padding: 10px;
    background: #f5f5f5;
    border-radius: 4px;
}

.bcMenu ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.bcMenu ul li {
    display: inline-block;
    color: #555;
    font-size: 16px;
    font-weight: bold;
}

/* تحسينات أدوات الترقيم */
.pagicon {
    margin-top: 10px;
    padding: 10px 0;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.item-no {
    color: #666;
    font-size: 14px;
}

.page-rslt {
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-rslt select {
    padding: 4px 8px;
    border: 1px solid #ccc;
    border-radius: 3px;
    background: #fff;
    cursor: pointer;
}

.page-no {
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-no .prev,
.page-no .next {
    display: inline-block;
}

.page-no .prev a,
.page-no .next a {
    display: block;
    padding: 3px 8px;
    background: #f0f0f0;
    border: 1px solid #ddd;
    border-radius: 3px;
    color: #333;
    text-decoration: none;
}

.page-no .prev a:hover,
.page-no .next a:hover {
    background: #e0e0e0;
}

.page-no .pagenmbr {
    display: flex;
    align-items: center;
    gap: 5px;
}

.page-no .pagenmbr input {
    width: 50px;
    text-align: center;
    padding: 4px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

/* تحسينات جدول البيانات */
.admin-hdr-bg {
    background: #4a6a8b;
    color: white;
    font-weight: bold;
    border-radius: 4px 4px 0 0;
    overflow: hidden;
}

.admin-hdr-bg .eID {
    float: right;
    width: 24%;
    padding: 12px 10px;
    border-left: 1px solid #5a7a9b;
    box-sizing: border-box;
}

.admin-hdr-bg .eID:last-child {
    border-left: none;
}

.admin-dtls ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.admin-dtls li {
    border-bottom: 1px solid #eee;
    background: #fff;
    transition: background-color 0.2s;
}

.admin-dtls li:hover {
    background: #f9f9f9;
}

.admin-dtls li.row-clr {
    background: #f8f9fa;
}

.admin-dtls li.row-clr:hover {
    background: #f2f2f2;
}

.admin-dtls .eID {
    float: right;
    width: 24%;
    padding: 12px 10px;
    border-left: 1px solid #eee;
    box-sizing: border-box;
    color: #333;
    font-size: 13px;
}

.admin-dtls .eID:last-child {
    border-left: none;
}

/* حالة الاتصال */
.online-status {
    color: #28a745;
    font-weight: bold;
    background: #d4edda;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    display: inline-block;
}

.offline-status {
    color: #6c757d;
}

/* رسالة عدم وجود بيانات */
.no-data {
    text-align: center;
    padding: 40px;
    background: #f8f9fa;
    border-radius: 4px;
    color: #666;
}

/* تحسينات للأجهزة المحمولة */
@media (max-width: 768px) {
    .admin-hdr-bg .eID,
    .admin-dtls .eID {
        width: 100%;
        border-left: none;
        border-bottom: 1px solid #eee;
    }
    
    .admin-hdr-bg {
        display: none;
    }
    
    .pagicon {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .page-no {
        width: 100%;
        justify-content: center;
    }
}

.clr {
    clear: both;
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div class="bodyRightCon">
        <div class="bodyRightightCon_inner">
            <div class="bcMenuCon">
                <div class="bcMenu">
                    <ul>
                        <li>&rsaquo;&nbsp;&nbsp;إدارة المشرفين&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;سجل تسجيل الدخول</li>
                    </ul>
                    <div class="clr"></div>
                </div>
                
                <!-- أدوات الترقيم والتحكم -->
                <div class="pagicon">
                    <div class="item-no"><?php echo htmlspecialchars($displayText); ?></div>
                    
                    <div class="page-rslt">
                        عرض:
                        <select name="limit" id="limit" onchange="changeLimit(this.value)">
                            <option value="10" <?php echo $paginationParams['limit'] == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $paginationParams['limit'] == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="30" <?php echo $paginationParams['limit'] == 30 ? 'selected' : ''; ?>>30</option>
                            <option value="50" <?php echo $paginationParams['limit'] == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $paginationParams['limit'] == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                    
                    <div class="page-no">
                        <div class="prev">
                            <?php if ($paginationParams['currentPage'] > 1): ?>
                                <a href="?page=<?php echo $paginationParams['currentPage'] - 1; ?>&limit=<?php echo $paginationParams['limit']; ?>">
                                    <img src="images/prev.jpg" alt="السابق"/>
                                </a>
                            <?php else: ?>
                                <span style="opacity:0.5; cursor:not-allowed;">
                                    <img src="images/prev.jpg" alt="السابق"/>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="pagenmbr">
                            <input type="text" 
                                   id="pageInput" 
                                   value="<?php echo $paginationParams['currentPage']; ?>" 
                                   onkeypress="if(event.keyCode==13) goToPage(this.value);">
                            <span>من <?php echo $paginationParams['totalPages']; ?></span>
                        </div>
                        
                        <div class="next">
                            <?php if ($paginationParams['currentPage'] < $paginationParams['totalPages']): ?>
                                <a href="?page=<?php echo $paginationParams['currentPage'] + 1; ?>&limit=<?php echo $paginationParams['limit']; ?>">
                                    <img src="images/next.jpg" alt="التالي"/>
                                </a>
                            <?php else: ?>
                                <span style="opacity:0.5; cursor:not-allowed;">
                                    <img src="images/next.jpg" alt="التالي"/>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="clr"></div>
                    </div>
                    <div class="clr"></div>
                </div>
                <br clear="all"/>
            </div>
            
            <!-- جدول سجل تسجيل الدخول -->
            <div>
                <div class="admin-hdr-bg">
                    <div class="eID"><strong>البريد الإلكتروني</strong></div>
                    <div class="eID"><strong>اسم المستخدم</strong></div>
                    <div class="eID"><strong>وقت الدخول</strong></div>
                    <div class="eID"><strong>عنوان IP</strong></div>
                    <div class="eID" style="border-left:none;"><strong>مدة الجلسة</strong></div>
                    <div class="clr"></div>
                </div>
                
                <div class="admin-dtls">
                    <ul>
                        <?php if (empty($history)): ?>
                            <li class="no-data">
                                لا توجد سجلات تسجيل دخول لعرضها
                            </li>
                        <?php else: ?>
                            <?php foreach ($history as $index => $row): ?>
                                <li class="<?php echo ($index % 2 == 0) ? '' : 'row-clr'; ?>">
                                    <div class="eID">
                                        <?php echo htmlspecialchars($row['admin_email'] ?? 'غير محدد'); ?>
                                    </div>
                                    <div class="eID">
                                        <?php echo htmlspecialchars($row['admin_name'] ?? 'غير محدد'); ?>
                                    </div>
                                    <div class="eID">
                                        <?php 
                                        $loginTime = $loginDetails->formatDateTime($row['login_time']);
                                        echo htmlspecialchars($loginTime);
                                        ?>
                                    </div>
                                    <div class="eID">
                                        <?php echo htmlspecialchars($row['ip_address'] ?? 'غير معروف'); ?>
                                    </div>
                                    <div class="eID" style="border-left:none;">
                                        <?php 
                                        $isOnline = (bool)($row['is_online'] ?? false);
                                        $duration = $loginDetails->formatDuration($row['duration'] ?? null, $isOnline);
                                        echo $duration; // Already escaped in formatDuration
                                        ?>
                                    </div>
                                    <div class="clr"></div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <br clear="all"/>
</div>
<br clear="all" />

<?php include "includes/footer.php" ?>

<!-- JavaScript للتحكم بالصفحة -->
<script type="text/javascript">
/**
 * تغيير عدد العناصر في الصفحة
 * @param {string} limit - العدد الجديد
 */
function changeLimit(limit) {
    window.location.href = '?page=1&limit=' + limit;
}

/**
 * الذهاب إلى صفحة محددة
 * @param {string} page - رقم الصفحة
 */
function goToPage(page) {
    page = parseInt(page);
    var maxPage = <?php echo $paginationParams['totalPages']; ?>;
    
    if (isNaN(page) || page < 1) {
        page = 1;
    } else if (page > maxPage) {
        page = maxPage;
    }
    
    window.location.href = '?page=' + page + '&limit=<?php echo $paginationParams['limit']; ?>';
}

/**
 * تحديث تلقائي للصفحة كل 30 ثانية
 */
setTimeout(function() {
    location.reload();
}, 30000); // 30 ثانية

/**
 * تحديث يدوي
 */
function refreshPage() {
    location.reload();
}

/**
 * تصدير البيانات (اختياري)
 */
function exportData() {
    var format = prompt('اختر صيغة التصدير (CSV أو Excel):', 'CSV');
    if (format) {
        window.location.href = 'export_login_history.php?format=' + format.toUpperCase();
    }
}

/**
 * بحث في السجل (اختياري)
 */
function searchHistory() {
    var searchTerm = prompt('أدخل كلمة البحث:', '');
    if (searchTerm !== null) {
        window.location.href = '?search=' + encodeURIComponent(searchTerm);
    }
}
</script>

</body>
</html>