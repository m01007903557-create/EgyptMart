<?php
/**
 * File: reseller-list.php
 * Version: 2.0.0
 * Description: عرض وإدارة قائمة الموزعين مع إمكانية التفعيل والتعليق والتعديل (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// تضمين الملفات المطلوبة
require_once "../common.php";
require_once "lib/pagination.php";

// التحقق من تسجيل الدخول (معلق كما في الكود الأصلي)
// check_user_login();

// الحصول على معرف الموزع من الجلسة
$reseller_id = $_SESSION['reseller_id'] ?? 0;

// جلب بيانات الموزع الحالي
$resellerrow = null;
if ($reseller_id > 0) {
    $sql = "SELECT * FROM reseller WHERE reseller_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $reseller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $resellerrow = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
    }
}

/**
 * Class ResellerList - إدارة قائمة الموزعين مع الترقيم
 */
class ResellerList {
    private string $sqlList = '';
    private int $start = 0;
    private int $limit = 0;
    private ?mysqli $dbConnection = null;
    
    /**
     * المُنشئ مع حقن التبعية
     */
    public function __construct(?mysqli $databaseConnection = null) {
        global $con;
        $this->dbConnection = $databaseConnection ?? $con;
    }
    
    /**
     * تعيين استعلام SQL
     */
    public function setsql(string $sql): self {
        $this->sqlList = $sql;
        return $this;
    }
    
    /**
     * تعيين نقطة البداية
     */
    public function setStart(int $start): self {
        $this->start = max(0, $start);
        return $this;
    }
    
    /**
     * تعيين عدد العناصر في الصفحة
     */
    public function setLimit(int $limit): self {
        $this->limit = max(1, min(100, $limit));
        return $this;
    }
    
    /**
     * الحصول على إجمالي عدد السجلات
     */
    public function totalrecord(): int {
        if (empty($this->sqlList)) {
            return 0;
        }
        
        $countSql = preg_replace('/ORDER\s+BY\s+.*?(?=\s+LIMIT|\s*$)/i', '', $this->sqlList);
        $result = mysqli_query($this->dbConnection, $countSql);
        
        if (!$result) {
            error_log('خطأ في استعلام العدد: ' . mysqli_error($this->dbConnection));
            return 0;
        }
        
        return mysqli_num_rows($result);
    }
    
    /**
     * الحصول على عرض القائمة مع الترقيم
     */
    public function listview() {
        if (empty($this->sqlList)) {
            return false;
        }
        
        $sql = $this->sqlList . " LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->dbConnection));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $this->limit, $this->start);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * حذف سجل
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM admin_login_details WHERE admin_login_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $cleanId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
    
    /**
     * تغيير حالة الموزع إلى نشط
     */
    public function changereseller($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "UPDATE reseller SET reseller_status = '0' WHERE reseller_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $cleanId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
    
    /**
     * تغيير حالة الموزع إلى معلق
     */
    public function changesuspend($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "UPDATE reseller SET reseller_status = '1' WHERE reseller_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $cleanId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $success;
    }
    
    /**
     * إنشاء رابط الحذف
     */
    public function deletelink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id=" . $cleanId;
        }
        
        return "reseller-list.php?" . htmlspecialchars($queryString) . "&action=del&ad-id=" . $cleanId;
    }
    
    /**
     * إنشاء رابط التفعيل
     */
    public function paidlink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=paid&did=" . $cleanId;
        }
        
        return "reseller-list.php?" . htmlspecialchars($queryString) . "&action=paid&did=" . $cleanId;
    }
    
    /**
     * إنشاء رابط التعليق
     */
    public function suspendlink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=suspend&did=" . $cleanId;
        }
        
        return "reseller-list.php?" . htmlspecialchars($queryString) . "&action=suspend&did=" . $cleanId;
    }
}

/**
 * Class Pagination - إدارة الترقيم
 */
class Pagination {
    private int $defaultLimit = 10;
    private int $maxLimit = 100;
    
    public function setpage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page);
    }
    
    public function setlimit(int $default = 10): int {
        $this->defaultLimit = $default;
        
        if (!isset($_GET['limit'])) {
            return $this->defaultLimit;
        }
        
        $limit = (int)$_GET['limit'];
        
        if ($limit < 1) return 1;
        if ($limit > $this->maxLimit) return $this->maxLimit;
        
        return $limit;
    }
    
    public function setstart(int $page, int $limit, int $total): int {
        $start = ($page - 1) * $limit;
        
        if ($start >= $total && $total > 0) {
            $start = max(0, $total - $limit);
        }
        
        return max(0, $start);
    }
    
    /**
     * إنشاء روابط الترقيم
     */
    public function getPaginationString(
        int $page, 
        int $totalitems, 
        int $limit, 
        int $adjacents = 1, 
        string $targetpage = "/", 
        string $pagestring = "?page="
    ): string {
        if ($totalitems <= $limit) {
            return '';
        }
        
        $totalpages = (int)ceil($totalitems / $limit);
        $pagination = [];
        
        if ($totalpages > 1) {
            // زر السابق
            if ($page > 1) {
                $pagination[] = sprintf(
                    '<a href="%s%s%d">&laquo; السابق</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring),
                    $page - 1
                );
            } else {
                $pagination[] = '<span class="disabled">&laquo; السابق</span>';
            }
            
            // أرقام الصفحات
            $start = max(1, $page - $adjacents);
            $end = min($totalpages, $page + $adjacents);
            
            if ($start > 1) {
                $pagination[] = sprintf(
                    '<a href="%s%s1">1</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring)
                );
                if ($start > 2) {
                    $pagination[] = '<span class="elipses">...</span>';
                }
            }
            
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                    $pagination[] = sprintf('<span class="current">%d</span>', $i);
                } else {
                    $pagination[] = sprintf(
                        '<a href="%s%s%d">%d</a>',
                        htmlspecialchars($targetpage),
                        htmlspecialchars($pagestring),
                        $i,
                        $i
                    );
                }
            }
            
            if ($end < $totalpages) {
                if ($end < $totalpages - 1) {
                    $pagination[] = '<span class="elipses">...</span>';
                }
                $pagination[] = sprintf(
                    '<a href="%s%s%d">%d</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring),
                    $totalpages,
                    $totalpages
                );
            }
            
            // زر التالي
            if ($page < $totalpages) {
                $pagination[] = sprintf(
                    '<a href="%s%s%d">التالي &raquo;</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring),
                    $page + 1
                );
            } else {
                $pagination[] = '<span class="disabled">التالي &raquo;</span>';
            }
        }
        
        return implode(' ', $pagination);
    }
}

// تهيئة الترقيم
try {
    $p = new Pagination();
    $page = $p->setpage();
    
    $al = new ResellerList();
    
    /******************** حذف سجل *********************/
    if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
        $adId = filter_input(INPUT_GET, 'ad-id', FILTER_VALIDATE_INT);
        if ($adId !== false && $adId > 0) {
            $al->deleterecord($adId);
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم الحذف بنجاح</div>';
        }
        header("Location: reseller-list.php");
        exit();
    }
    /*************************************************/
    
    /******************** تفعيل موزع *********************/
    if (isset($_GET['action']) && $_GET['action'] === "paid" && isset($_GET['did'])) {
        $did = filter_input(INPUT_GET, 'did', FILTER_VALIDATE_INT);
        if ($did !== false && $did > 0) {
            $al->changereseller($did);
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم تغيير الحالة بنجاح</div>';
        }
        header("Location: reseller-list.php");
        exit();
    }
    /*************************************************/
    
    /******************** تعليق موزع *********************/
    if (isset($_GET['action']) && $_GET['action'] === "suspend" && isset($_GET['did'])) {
        $did = filter_input(INPUT_GET, 'did', FILTER_VALIDATE_INT);
        if ($did !== false && $did > 0) {
            $al->changesuspend($did);
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم تعليق الموزع بنجاح</div>';
        }
        header("Location: reseller-list.php");
        exit();
    }
    /*************************************************/
    
    // تعيين معاملات الترقيم
    $limit = $p->setlimit(10);
    $al->setLimit($limit);
    $al->setsql("SELECT * FROM reseller ORDER BY reseller_creation_date DESC");
    
    $totalitems = $al->totalrecord();
    $al->setStart($p->setstart($page, $limit, $totalitems));
    
    $adjacents = 1;
    $targetpage = "reseller-list.php";
    $pagestring = "?limit=" . $limit . "&page=";
    
    $recObj = $al->listview();
    $count = $recObj ? mysqli_num_rows($recObj) : 0;
    
    // حساب نص عرض العناصر
    $startItem = $totalitems > 0 ? $al->start + 1 : 0;
    $endItem = min($al->start + $limit, $totalitems);
    $showitems = $totalitems > 0 
        ? $startItem . " - " . $endItem . " من " . $totalitems . " موزع" 
        : "0 موزعين";
        
} catch (Exception $e) {
    error_log('خطأ في عرض الموزعين: ' . $e->getMessage());
    $error = 'حدث خطأ أثناء تحميل الصفحة';
}

// جلب رمز العملة
$currencySymbol = '$';
$curSql = "SELECT st_value FROM site_settings WHERE st_field = 'currency-symbol'";
$curResult = mysqli_query($con, $curSql);
if ($curResult && $curRow = mysqli_fetch_assoc($curResult)) {
    $currencySymbol = $curRow['st_value'] ?: '$';
}

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>لوحة التحكم - قائمة الموزعين</title>
<link rel="shortcut icon" href="" type="image/x-icon">
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>

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
.items th, .items td {
    text-align: center;
    vertical-align: middle;
}
.checkbox {
    text-align: center;
    width: 40px;
}
.action {
    text-align: center;
}
.success-message {
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}
.error-message {
    background-color: #f2dede;
    color: #a94442;
    border: 1px solid #ebccd1;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}
.reseller-logo {
    max-width: 100px;
    max-height: 100px;
    border: 1px solid #ddd;
    padding: 2px;
    border-radius: 3px;
    background: white;
}
.status-active {
    color: green;
    font-weight: bold;
}
.status-suspended {
    color: red;
    font-weight: bold;
}
</style>

<script type="text/javascript">
// دوال JavaScript محدثة
function toggleCheckAll(source) {
    var checkboxes = document.querySelectorAll('input[name="cb[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = source.checked;
    });
}

// تهيئة الصفحة
document.addEventListener('DOMContentLoaded', function() {
    var checkAll = document.getElementById('check_all');
    if (checkAll) {
        checkAll.addEventListener('click', function() {
            toggleCheckAll(this);
        });
    }
    
    // إخفاء رسائل النجاح بعد 5 ثوان
    setTimeout(function() {
        var msgDiv = document.querySelector('.success-message, .error-message');
        if (msgDiv) {
            msgDiv.style.display = 'none';
        }
    }, 5000);
});

// تأكيد تغيير الحالة
function confirmStatusChange(action) {
    return confirm('هل أنت متأكد من تغيير حالة الموزع؟');
}
</script>
</head>

<body>
<div class="main">
    <?php include "includes/admin-top.php" ?>
    
    <div class="control_Panel">
        <?php include "includes/admin-left-con.php" ?>
        
        <div id="content-container">
            <div id="content">
                
                <?php if ($msg): ?>
                    <div class="success-message"><?php echo $msg; ?></div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post"> 
                    <h2>&rsaquo;&nbsp;&nbsp;إدارة الموزعين&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;قائمة الموزعين</h2>
                    
                    <div id="whatsNew-grid" class="grid-view">
                        <table style="width:100%; margin-bottom:15px;">
                            <tr>
                                <td style="width:150px;">
                                    <input type="button" class="delete-btn" 
                                           onClick="window.location ='reseller-registration.php'" 
                                           value="تسجيل موزع جديد">
                                </td>
                                <td align="left">
                                    <div class="summary">
                                        <div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
                                            <select name="limit" id="limit" 
                                                    onchange="window.location.href='reseller-list.php?page=<?php echo (int)$page; ?>&amp;limit='+this.value;">
                                                <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                    <option value="<?php echo $i; ?>" 
                                                        <?php echo ($i == $limit) ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        نتيجة في الصفحة
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </table>
                       
                        <table class="items">
                            <thead>
                                <tr>
                                    <th align="center" style="width:40px;">#</th>
                                    <th class="usr-name" style="width:140px;"><strong>الشعار</strong></th>
                                    <th class="usr-name" style="width:140px;"><strong>الاسم الكامل</strong></th>
                                    <th class="usr-name" style="width:170px;"><strong>البريد الإلكتروني</strong></th>
                                    <th class="usr-name" style="width:140px;"><strong>اسم المستخدم</strong></th>
                                    <th class="usr-name" style="width:170px;"><strong>الموقع الإلكتروني</strong></th>
                                    <th class="usr-name" style="width:100px;"><strong>الحالة</strong></th>
                                    <th class="usr-name" style="width:100px;"><strong>الإجراءات</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $j = 1;
                                if ($count > 0):
                                    while($row = mysqli_fetch_assoc($recObj)): 
                                        
                                        // التحقق من وجود علاقة مع المنتجات (اختياري)
                                        $hasRelation = false;
                                        if ($reseller_id > 0) {
                                            $checkSql = "SELECT * FROM reselluser_specification WHERE uspf_uid = ? AND uspf_pdid = ?";
                                            $checkStmt = mysqli_prepare($con, $checkSql);
                                            if ($checkStmt) {
                                                mysqli_stmt_bind_param($checkStmt, "ii", $reseller_id, $row['pd_id'] ?? 0);
                                                mysqli_stmt_execute($checkStmt);
                                                $checkResult = mysqli_stmt_get_result($checkStmt);
                                                $hasRelation = mysqli_num_rows($checkResult) > 0;
                                                mysqli_stmt_close($checkStmt);
                                            }
                                        }
                                ?>
                                    <tr <?php if($j % 2 == 1) echo 'class="row-clr"'; ?>>
                                        <td class="checkbox">#<?php echo $j; ?></td>
                                        
                                        <td class="usr-name" style="text-align:center;">
                                            <?php if (!empty($row['reseller_logo'])): ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['reseller_logo']); ?>" 
                                                     class="reseller-logo" 
                                                     alt="شعار الموزع" />
                                            <?php else: ?>
                                                <img src="../products_images/il_75x75.jpg" 
                                                     class="reseller-logo" 
                                                     alt="لا يوجد شعار" />
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="usr-name">
                                            <?php echo htmlspecialchars(ucwords($row['reseller_fullname'] ?? '')); ?>
                                        </td>
                                        
                                        <td class="usr-name">
                                            <a href="mailto:<?php echo htmlspecialchars($row['reseller_email'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($row['reseller_email'] ?? ''); ?>
                                            </a>
                                        </td>
                                        
                                        <td class="usr-name">
                                            <?php echo htmlspecialchars(ucwords($row['reseller_uname'] ?? '')); ?>
                                        </td>
                                        
                                        <td class="usr-name">
                                            <a href="<?php echo htmlspecialchars($row['reseller_website'] ?? '#'); ?>" target="_blank">
                                                <?php echo htmlspecialchars($row['reseller_website'] ?? '-'); ?>
                                            </a>
                                        </td>
                                        
                                        <td class="usr-name">
                                            <?php if (isset($row['reseller_status']) && $row['reseller_status'] == 1): ?>
                                                <a href="<?php echo $al->paidlink((int)$row['reseller_id']); ?>" 
                                                   class="status-active"
                                                   onclick="return confirm('هل أنت متأكد من تفعيل هذا الموزع؟')"
                                                   title="انقر للتعليق">
                                                    نشط
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo $al->suspendlink((int)$row['reseller_id']); ?>" 
                                                   class="status-suspended"
                                                   onclick="return confirm('هل أنت متأكد من تعليق هذا الموزع؟')"
                                                   title="انقر للتفعيل">
                                                    معلق
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="usr-name" style="text-align:center;">
                                            <?php 
                                            // إنشاء رابط آمن للتعديل
                                            $token = bin2hex(random_bytes(8));
                                            $editHash = md5($row['reseller_id'] . $token);
                                            ?>
                                            <a href="reseller-edit.php?r=<?php echo $token . $editHash; ?>" 
                                               title="تعديل" style="margin-left:5px;">
                                                <img alt="تعديل" src="images/edit.jpg">
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $j++;
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="8" align="center">
                                            <div class="alert alert-info">
                                                لا توجد موزعين لعرضهم
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <div class="pager">
                            <?php 
                            if (isset($p) && $totalitems > 0) {
                                echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring);
                            }
                            ?>
                        </div>
                    </div>
                    
                    <br clear="all"/>
                </form>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

</body>
</html>
<?php ob_end_flush(); ?>