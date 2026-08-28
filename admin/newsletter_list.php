<?php
/**
 * ملف قائمة المشتركين في النشرة البريدية (Newsletter Subscribers List)
 * 
 * @filename    newsletter_list.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن عرض وإدارة المشتركين في النشرة البريدية
 *              مع إمكانية الحذف الفردي والجماعي والتحكم بعدد العناصر في الصفحة
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت
ob_start();

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم (يمكن تفعيلها)
// check_user_login();

/**
 * كلاس إدارة المشتركين في النشرة البريدية
 */
class newslist {
    
    private $sqlList = "";
    private $start = 0;
    private $limit = 10;
    private $errors = [];
    
    /**
     * تعيين استعلام SQL
     * @param string $sql
     */
    public function setsql($sql) {
        $this->sqlList = $sql;
    }
    
    /**
     * حساب إجمالي السجلات
     * @return int
     */
    public function totalrecord() {
        global $con;
        
        $result = mysqli_query($con, $this->sqlList);
        if (!$result) {
            $this->errors[] = "خطأ في الاستعلام: " . mysqli_error($con);
            return 0;
        }
        
        return mysqli_num_rows($result);
    }
    
    /**
     * تنفيذ استعلام العرض مع التصفح
     * @return mysqli_result|false
     */
    public function listview() {
        global $con;
        
        $start = intval($this->start);
        $limit = intval($this->limit);
        $sql = $this->sqlList . " LIMIT " . $start . ", " . $limit;
        
        $result = mysqli_query($con, $sql);
        if (!$result) {
            $this->errors[] = "خطأ في عرض البيانات: " . mysqli_error($con);
            return false;
        }
        
        return $result;
    }
    
    /**
     * حساب عدد الصفحات
     * @param int $rowPage
     * @return int
     */
    public function numpage($rowPage) {
        $total = $this->totalrecord();
        return ($rowPage > 0) ? ceil($total / $rowPage) : 0;
    }
    
    /**
     * حذف سجل
     * @param int $adid
     * @return bool
     */
    public function deleterecord($adid) {
        global $con;
        
        $adid = filter_var($adid, FILTER_VALIDATE_INT);
        if (!$adid || $adid <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM newsletter WHERE nwsletter_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $adid);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id
     * @return string
     */
    public function deletelink($id) {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&clid=" . $id;
        } else {
            return "newsletter_list.php?" . $queryString . "&action=del&clid=" . $id;
        }
    }
    
    /**
     * تعيين قيمة البداية
     * @param int $start
     */
    public function setStart($start) {
        $this->start = max(0, intval($start));
    }
    
    /**
     * تعيين قيمة الحد
     * @param int $limit
     */
    public function setLimit($limit) {
        $this->limit = max(1, min(100, intval($limit)));
    }
    
    /**
     * الحصول على أخطاء
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
}

/**
 * كلاس إدارة التصفح (Pagination)
 */
class Pagination {
    
    /**
     * الحصول على رقم الصفحة الحالية
     * @return int
     */
    public function setpage() {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999]
        ]);
        return $page ?: 1;
    }
    
    /**
     * تحديد قيمة البداية
     * @param int $page
     * @param int $limit
     * @param int $total
     * @return int
     */
    public function setstart($page, $limit, $total) {
        if ($total <= 0) return 0;
        $start = ($page - 1) * $limit;
        return max(0, min($start, $total - $limit));
    }
    
    /**
     * تحديد قيمة الحد
     * @param int $default
     * @return int
     */
    public function setlimit($default = 10) {
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 100]
        ]);
        return $limit ?: $default;
    }
    
    /**
     * إنشاء روابط التصفح
     * @param int $page
     * @param int $totalitems
     * @param int $limit
     * @param int $adjacents
     * @param string $targetpage
     * @param string $pagestring
     * @return string
     */
    public function getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring) {
        $prev = $page - 1;
        $next = $page + 1;
        $totalpages = ceil($totalitems / $limit);
        
        $pagination = '<ul class="pagination">';
        
        // رابط الصفحة السابقة
        if ($page > 1) {
            $pagination .= '<li class="prev"><a href="' . $targetpage . $pagestring . $prev . '">« السابق</a></li>';
        } else {
            $pagination .= '<li class="prev disabled"><a href="#">« السابق</a></li>';
        }
        
        // روابط الصفحات
        if ($totalpages <= (2 * $adjacents + 1)) {
            for ($i = 1; $i <= $totalpages; $i++) {
                if ($i == $page) {
                    $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
                } else {
                    $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                }
            }
        } else {
            // الصفحات الأولى
            if ($page <= $adjacents + 1) {
                for ($i = 1; $i <= ($adjacents * 2 + 1); $i++) {
                    if ($i == $page) {
                        $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
                    } else {
                        $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                    }
                }
            }
            // الصفحات الأخيرة
            elseif ($page >= $totalpages - $adjacents) {
                for ($i = $totalpages - ($adjacents * 2); $i <= $totalpages; $i++) {
                    if ($i == $page) {
                        $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
                    } else {
                        $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                    }
                }
            }
            // الصفحات الوسطى
            else {
                for ($i = $page - $adjacents; $i <= $page + $adjacents; $i++) {
                    if ($i == $page) {
                        $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
                    } else {
                        $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                    }
                }
            }
        }
        
        // رابط الصفحة التالية
        if ($page < $totalpages) {
            $pagination .= '<li class="next"><a href="' . $targetpage . $pagestring . $next . '">التالي »</a></li>';
        } else {
            $pagination .= '<li class="next disabled"><a href="#">التالي »</a></li>';
        }
        
        $pagination .= '</ul>';
        
        return $pagination;
    }
}

// تهيئة الكلاسات
$p = new Pagination();
$page = $p->setpage();

$al = new newslist();

// معالجة الحذف الفردي
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['clid'])) {
    $clid = filter_input(INPUT_GET, 'clid', FILTER_VALIDATE_INT);
    
    if ($clid && $clid > 0) {
        if ($al->deleterecord($clid)) {
            $_SESSION['msg'] = '<div class="alert alert-success">✅ تم حذف المشترك بنجاح</div>';
        } else {
            $_SESSION['msg'] = '<div class="alert alert-danger">❌ حدث خطأ أثناء حذف المشترك</div>';
        }
    }
    
    header("Location: newsletter_list.php");
    exit();
}

// معالجة الحذف الجماعي
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnDelete'])) {
    
    if (isset($_POST['cb']) && is_array($_POST['cb'])) {
        $deleted = 0;
        
        foreach ($_POST['cb'] as $id) {
            $id = filter_var($id, FILTER_VALIDATE_INT);
            if ($id && $al->deleterecord($id)) {
                $deleted++;
            }
        }
        
        $_SESSION['msg'] = '<div class="alert alert-success">✅ تم حذف ' . $deleted . ' مشترك/مشتركين بنجاح</div>';
    } else {
        $_SESSION['msg'] = '<div class="alert alert-warning">⚠️ لم يتم تحديد أي مشترك للحذف</div>';
    }
    
    header("Location: newsletter_list.php");
    exit();
}

// إعدادات التصفح
$al->setLimit($p->setlimit(10));
$al->setsql("SELECT * FROM newsletter ORDER BY nwsletter_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->setStart($p->setstart($page, $limit, $totalitems));

// إعدادات التصفح
$adjacents = 2;
$targetpage = "newsletter_list.php";
$pagestring = "?limit=" . $limit . "&page=";

// جلب البيانات
$recObj = $al->listview();
$count = ($recObj) ? mysqli_num_rows($recObj) : 0;

// حساب عرض العناصر
$showitems = ($totalitems > 0) ? ($al->start + 1) . " - " : "0 - ";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " من " . $totalitems . " عنصر";

// رسالة النظام
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>لوحة التحكم - قائمة المشتركين في النشرة البريدية</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<script src="js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>
<style>
    /* تنسيقات مخصصة */
    .alert {
        padding: 10px;
        margin: 10px 0;
        border-radius: 4px;
        text-align: center;
    }
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .alert-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }
    .grid-view {
        direction: rtl;
        text-align: right;
    }
    .items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .items th {
        background-color: #f2f2f2;
        padding: 8px;
        border: 1px solid #ddd;
    }
    .items td {
        padding: 8px;
        border: 1px solid #ddd;
    }
    .items tr:hover {
        background-color: #f5f5f5;
    }
    .checkbox {
        text-align: center;
    }
    .pagination {
        display: inline-block;
        padding: 0;
        margin: 10px 0;
    }
    .pagination li {
        display: inline;
    }
    .pagination li a {
        color: black;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        border: 1px solid #ddd;
        margin: 0 2px;
    }
    .pagination li.active a {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    .pagination li.disabled a {
        color: #ccc;
        pointer-events: none;
    }
    .delete-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
    }
    .delete-btn:hover {
        background-color: #c82333;
    }
    .summary {
        margin: 10px 0;
        color: #666;
    }
    .form select {
        padding: 3px;
        border-radius: 3px;
        border: 1px solid #ddd;
    }
    .usr-name {
        text-align: center;
    }
</style>

<script type="text/javascript">
// دالة تحديد/إلغاء تحديد الكل
function checkedAll() {
    var checkboxes = document.getElementsByName('cb[]');
    var checkAll = document.getElementById('check_all');
    
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = checkAll.checked;
    }
}

// دالة التأكيد قبل الحذف الجماعي
function confirmBulkDelete() {
    return confirm('هل أنت متأكد من حذف المشتركين المحددين؟');
}

$(document).ready(function() {
    // تحديث عدد العناصر عند تغيير القائمة
    $('#limit').on('change', function() {
        window.location.href = 'newsletter_list.php?page=<?php echo $page; ?>&limit=' + this.value;
    });
    
    // إضافة tooltips للأزرار
    $('a[title]').tooltip();
});
</script>
</head>

<body>
<div class="main">
    <?php include "includes/admin-top.php" ?>
    
    <div class="control_Panel">
        <?php include "includes/admin-left-con.php" ?>
        
        <div id="content-container">
            <div id="content">
                
                <h2>
                    <i class="icon-envelope"></i> 
                    إدارة النشرة البريدية › قائمة المشتركين
                </h2>
                
                <!-- رسائل النظام -->
                <?php if ($msg): ?>
                    <div class="alert"><?php echo $msg; ?></div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post" onsubmit="return confirmBulkDelete();">
                    
                    <div id="whatsNew-grid" class="grid-view">
                        
                        <!-- شريط الأدوات -->
                        <table style="width: 100%; margin-bottom: 10px;">
                            <tr>
                                <td style="width: 80px;">
                                    <button type="submit" name="btnDelete" class="delete-btn">
                                        <i class="icon-trash"></i> حذف
                                    </button>
                                </td>
                                <td>
                                    <div class="summary">
                                        عرض <?php echo $showitems; ?>
                                    </div>
                                </td>
                                <td align="left">
                                    <div class="summary">
                                        <span>عرض:</span>
                                        <select name="limit" id="limit" style="margin: 0 5px;">
                                            <?php for($i = 10; $i <= 40; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($i == $limit) ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <span>نتيجة في الصفحة</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- جدول البيانات -->
                        <table class="items">
                            <thead>
                                <tr>
                                    <th class="checkbox" style="width:40px;">
                                        <input type="checkbox" name="check_all" id="check_all" value="yes" onclick="checkedAll();">
                                    </th>
                                    <th style="width:250px;">البريد الإلكتروني</th>
                                    <th style="width:120px;">تاريخ الاشتراك</th>
                                    <th style="width:90px;">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $j = 0;
                                if ($count > 0):
                                    while($row = mysqli_fetch_object($recObj)):       
                                ?>
                                    <tr class="<?php echo ($j % 2 == 1) ? 'row-clr' : ''; ?>">
                                        <td class="checkbox">
                                            <input type="checkbox" name="cb[]" value="<?php echo $row->nwsletter_id; ?>">
                                        </td>
                                        <td class="usr-name" style="text-align:center;">
                                            <a href="mailto:<?php echo htmlspecialchars($row->nwsletter_useremail); ?>">
                                                <?php echo htmlspecialchars($row->nwsletter_useremail); ?>
                                            </a>
                                        </td>   
                                        <td class="usr-name" style="text-align:center;">
                                            <i class="icon-calendar"></i>
                                            <?php echo date('Y-m-d', strtotime($row->nwsletter_date)); ?>
                                            <br>
                                            <small><?php echo date('h:i A', strtotime($row->nwsletter_date)); ?></small>
                                        </td>
                                        <td class="action" style="text-align:center;">
                                            <a href="<?php echo $al->deletelink($row->nwsletter_id); ?>" 
                                               title="حذف" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا المشترك؟')">
                                                <img src="images/delete.jpg" alt="حذف" border="0">
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $j++;
                                    endwhile; 
                                else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center; padding:20px;">
                                            <div class="alert alert-warning">
                                                لا يوجد مشتركين في النشرة البريدية
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- روابط التصفح -->
                        <div class="pager" style="text-align:center;">
                            <?php echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring); ?>
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

<!-- نهاية ملف newsletter_list.php - الإصدار 2.0.0 -->
</body>
</html>