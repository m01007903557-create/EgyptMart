<?php
/**
 * File: index_page_view.php
 * Version: 2.0.0
 * Description: عرض وإدارة محتوى الصفحة الرئيسية مع الترقيم (تمت الترقية إلى PHP 8.3)
 

// تفعيل strict typing
declare(strict_types=1);

// تشغيل عرض الأخطاء في بيئة التطوير
error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات المطلوبة
require_once "../common.php";
require_once "lib/pagination.php";

// التحقق من تسجيل الدخول (معلق كما في الكود الأصلي)
// check_user_login();

/**
 * Class ListCat - إدارة قائمة محتوى الصفحة الرئيسية مع الترقيم
 * متوافق مع PHP 8.3
 */
class ListCat {
    // الخصائص مع كتابة الأنواع
    private string $sqlList = '';
    private int $start = 0;
    private int $limit = 0;
    private ?mysqli $dbConnection = null;
    
    /**
     * المُنشئ مع حقن التبعية
     * 
     * @param mysqli|null $databaseConnection اتصال قاعدة البيانات
     */
    public function __construct(?mysqli $databaseConnection = null) {
        global $con;
        $this->dbConnection = $databaseConnection ?? $con;
    }
    
    /**
     * تعيين استعلام SQL
     * 
     * @param string $sql استعلام SQL
     * @return self
     */
    public function setsql(string $sql): self {
        $this->sqlList = $sql;
        return $this;
    }
    
    /**
     * تعيين نقطة البداية
     * 
     * @param int $start نقطة البداية
     * @return self
     */
    public function setStart(int $start): self {
        $this->start = max(0, $start);
        return $this;
    }
    
    /**
     * تعيين عدد العناصر لكل صفحة
     * 
     * @param int $limit عدد العناصر
     * @return self
     */
    public function setLimit(int $limit): self {
        $this->limit = max(1, min(100, $limit)); // حد أقصى 100
        return $this;
    }
    
    /**
     * الحصول على إجمالي عدد السجلات
     * 
     * @return int إجمالي السجلات
     * @throws RuntimeException في حالة فشل الاستعلام
     */
    public function totalrecord(): int {
        if (empty($this->sqlList)) {
            return 0;
        }
        
        // إزالة ORDER BY لتحسين الأداء
        $countSql = preg_replace('/ORDER\s+BY\s+.*?(?=\s+LIMIT|\s*$)/i', '', $this->sqlList);
        
        $result = mysqli_query($this->dbConnection, $countSql);
        
        if (!$result) {
            throw new RuntimeException('فشل استعلام قاعدة البيانات: ' . mysqli_error($this->dbConnection));
        }
        
        return mysqli_num_rows($result);
    }
    
    /**
     * الحصول على عرض القائمة مع الترقيم
     * 
     * @return mysqli_result|false نتيجة قاعدة البيانات
     * @throws RuntimeException في حالة فشل الاستعلام
     */
    public function listview() {
        if (empty($this->sqlList)) {
            return false;
        }
        
        // استخدام prepared statement للأمان
        $baseSql = preg_replace('/\s+ORDER\s+BY\s+.*?(?=\s*$)/i', '', $this->sqlList);
        $sql = $baseSql . " ORDER BY ic_id ASC LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('فشل تحضير الاستعلام: ' . mysqli_error($this->dbConnection));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $this->limit, $this->start);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * حساب عدد الصفحات
     * 
     * @param int $rowPage عدد العناصر في الصفحة
     * @return int عدد الصفحات
     */
    public function numpage(int $rowPage): int {
        $total = $this->totalrecord();
        return $rowPage > 0 ? (int)floor($total / $rowPage) : 0;
    }
    
    /**
     * حذف سجل (تعطيل)
     * 
     * @param int|string $adid معرف السجل
     * @return bool نجاح العملية
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "UPDATE airlines SET al_status = '0' WHERE al_id = ?";
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
     * 
     * @param int|string $id معرف السجل
     * @return string رابط الحذف
     */
    public function deletelink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid=" . $cleanId;
        }
        
        return "index_page_view.php?" . htmlspecialchars($queryString) . "&action=del&fid=" . $cleanId;
    }
}

/**
 * Class Pagination - إدارة الترقيم
 */
class Pagination {
    private int $defaultLimit = 20;
    private int $maxLimit = 100;
    
    /**
     * الحصول على رقم الصفحة الحالي
     * 
     * @return int رقم الصفحة
     */
    public function setpage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page);
    }
    
    /**
     * تعيين والتحقق من الحد الأقصى
     * 
     * @param int $default الحد الافتراضي
     * @return int الحد الأقصى بعد التحقق
     */
    public function setlimit(int $default = 20): int {
        $this->defaultLimit = $default;
        
        if (!isset($_GET['limit'])) {
            return $this->defaultLimit;
        }
        
        $limit = (int)$_GET['limit'];
        
        // التحقق من نطاق الحد
        if ($limit < 1) {
            return 1;
        }
        
        if ($limit > $this->maxLimit) {
            return $this->maxLimit;
        }
        
        return $limit;
    }
    
    /**
     * حساب نقطة البداية
     * 
     * @param int $page الصفحة الحالية
     * @param int $limit عدد العناصر في الصفحة
     * @param int $total إجمالي العناصر
     * @return int نقطة البداية
     */
    public function setstart(int $page, int $limit, int $total): int {
        $start = ($page - 1) * $limit;
        
        // التأكد من عدم تجاوز الحد الأقصى
        if ($start >= $total && $total > 0) {
            $start = max(0, $total - $limit);
        }
        
        return max(0, $start);
    }
    
    /**
     * إنشاء روابط الترقيم
     * 
     * @param int $page الصفحة الحالية
     * @param int $totalitems إجمالي العناصر
     * @param int $limit العناصر لكل صفحة
     * @param int $adjacents عدد الصفحات المجاورة
     * @param string $targetpage الصفحة المستهدفة
     * @param string $pagestring نص معامل الصفحة
     * @return string HTML روابط الترقيم
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
    
    $al = new ListCat();
    
    /******************** حذف سجل *********************/
    if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
        $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
        if ($fid !== false && $fid > 0) {
            $al->deleterecord($fid);
        }
        
        // إعادة توجيه نظيفة
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        header("Location: " . $protocol . $host . $script);
        exit();
    }
    /*************************************************/

    // تعيين معاملات الترقيم
    $limit = $p->setlimit(20);
    $al->setLimit($limit);
    $al->setsql("SELECT * FROM index_content WHERE 1=1");
    
    $totalitems = $al->totalrecord();
    $al->setStart($p->setstart($page, $limit, $totalitems));
    
    $adjacents = 1;
    $targetpage = "index_page_view.php";
    $pagestring = "?limit=" . $limit . "&page=";
    
    $recObj = $al->listview();
    
    // حساب نص عرض العناصر
    $startItem = $totalitems > 0 ? $al->start + 1 : 0;
    $endItem = min($al->start + $limit, $totalitems);
    $showitems = $totalitems > 0 
        ? $startItem . " - " . $endItem . " من " . $totalitems . " عنصر" 
        : "0 عناصر";
        
} catch (Exception $e) {
    error_log('خطأ في عرض الصفحة الرئيسية: ' . $e->getMessage());
    $error = 'حدث خطأ أثناء تحميل الصفحة';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>لوحة التحكم - إدارة الصفحة الرئيسية</title>
<link rel="shortcut icon" href="" type="image/x-icon">
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>

<style>
/* تحسينات إضافية للواجهة العربية */
body {
    direction: rtl;
    text-align: right;
}
.control_Panel {
    direction: rtl;
}
.items th, .items td {
    text-align: right;
}
.checkbox {
    text-align: center !important;
}
.action {
    text-align: center !important;
}
.status-active {
    color: green;
    font-weight: bold;
}
.status-inactive {
    color: red;
    font-weight: bold;
}
.error-message {
    background-color: #f2dede;
    color: #a94442;
    border: 1px solid #ebccd1;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}
</style>

<script type="text/javascript">
// دوال JavaScript محدثة
function changeStatus(stat, id) {
    if (!stat || !id) {
        alert('حالة أو معرف غير صالح');
        return;
    }
    
    $.post("ajax-files/changeIndexContentStatus.php", 
        {stat: stat, id: id}, 
        function(data) {
            location.reload();
        }
    ).fail(function() {
        alert('فشل تحديث الحالة. الرجاء المحاولة مرة أخرى.');
    });
}

// تحديد الكل / إلغاء التحديد
function checkedAll() {
    var checkboxes = document.querySelectorAll('input[name="cb[]"]');
    var checkAll = document.getElementById('check_all');
    
    if (checkAll) {
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = checkAll.checked;
        });
    }
}

// تهيئة الصفحة
document.addEventListener('DOMContentLoaded', function() {
    var checkAll = document.getElementById('check_all');
    if (checkAll) {
        checkAll.addEventListener('click', checkedAll);
    }
    
    // إخفاء رسائل الخطأ بعد 5 ثواني
    var errorMessage = document.querySelector('.error-message');
    if (errorMessage) {
        setTimeout(function() {
            errorMessage.style.display = 'none';
        }, 5000);
    }
});

// تأكيد الحذف
function confirmDelete() {
    return confirm('هل أنت متأكد من حذف السجل/السجلات المحددة؟');
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
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post"> 
                    <h1>إدارة صفحات الموقع&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;الصفحة الرئيسية</h1>

                    <div id="whatsNew-grid" class="grid-view">
                        <table>
                            <tr>
                                <td>
                                    <input name="btnDelete" type="submit" value="حذف" class="delete-btn" 
                                           onclick="return confirm('هل أنت متأكد من حذف السجل/السجلات المحددة؟')" />
                                </td>
                                <td><?php echo htmlspecialchars($showitems); ?></td>
                                <td align="left">
                                    <div class="summary">
                                        <div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
                                            <select name="limit" id="limit" 
                                                    onchange="window.location.href='index_page_view.php?page=<?php echo (int)$page; ?>&amp;limit='+this.value;">
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
                                    <th class="checkbox" align="center" style="width: 10px;">
                                        <input name="check_all" value="yes" id="check_all" type="checkbox">
                                    </th>
                                    <th class="usr-name" style="width: 200px;"><strong>العنوان</strong></th>
                                    <th class="usr-name" style="width: 200px;"><strong>المحتوى</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>الحالة</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>تغيير الحالة</strong></th>
                                    <th class="action" style="width: 50px;"><strong>تعديل</strong></th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php 
                                $j = 0;
                                if ($recObj && mysqli_num_rows($recObj) > 0):
                                    while($row = mysqli_fetch_assoc($recObj)):
                                ?>
                                    <tr <?php if($j % 2 == 1) echo 'class="row-clr"'; ?>>
                                        <td class="checkbox" align="center">
                                            <input name="cb[]" type="checkbox" value="<?php echo (int)$row['ic_id']; ?>" />
                                        </td>
                                        <td class="usr-name">
                                            <b>
                                                <?php 
                                                // تنسيق العنوان
                                                $a_c = explode("-", $row['ic_heading'] ?? '');
                                                $formattedHeading = '';
                                                foreach($a_c as $v) {
                                                    $formattedHeading .= ucfirst($v) . " ";
                                                }
                                                echo htmlspecialchars(trim($formattedHeading) ?: 'بدون عنوان');
                                                ?>
                                            </b>
                                        </td>
                                        <td class="usr-name" style="width:200px; text-align:center">
                                            <?php 
                                            // عرض مختصر للمحتوى
                                            $content = $row['ic_content'] ?? '';
                                            $shortContent = strlen($content) > 100 
                                                ? substr($content, 0, 100) . '...' 
                                                : $content;
                                            echo htmlspecialchars($shortContent);
                                            ?>
                                        </td>
                                        
                                        <td class="usr-name" style="width:90px; text-align:center">
                                            <?php if (isset($row['ic_status'])): ?>
                                                <?php if($row['ic_status'] == '1'): ?>
                                                    <span class="status-active">فعال</span>
                                                <?php elseif($row['ic_status'] == '0'): ?>
                                                    <span class="status-inactive">غير فعال</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="usr-name" style="width:90px; text-align:center">
                                            <select onchange="changeStatus(this.value,'<?php echo (int)$row['ic_id']; ?>')">
                                                <option value="">اختر</option>
                                                <?php if(($row['ic_status'] ?? '') == '1'): ?>
                                                    <option value="0">إلغاء التفعيل</option>
                                                <?php else: ?>
                                                    <option value="1">تفعيل</option>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        
                                        <td class="action" align="center">
                                            <a href="index_page_edit.php?sid=<?php echo (int)$row['ic_id']; ?>" 
                                               title="تعديل">
                                                <img alt="تعديل" src="images/edit.jpg" border="0">
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $j++;
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="6" align="center">لا توجد سجلات</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <div class="pager">
                            <?php 
                            if (isset($p) && isset($totalitems) && $totalitems > 0) {
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

<!-- مؤشر التحميل لطلبات AJAX -->
<script type="text/javascript">
$(document).ajaxStart(function() {
    $('body').css('cursor', 'wait');
}).ajaxStop(function() {
    $('body').css('cursor', 'default');
});
</script>

</body>
</html>