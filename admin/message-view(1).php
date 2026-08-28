<?php
/**
 * ملف عرض الرسائل (Messages View)
 * 
 * @filename    message-view.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن عرض وإدارة الرسائل الداخلية في النظام
 *              مع إمكانية الحذف الفردي والجماعي وعرض التفاصيل
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت والجلسة
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم
require_once "../common.php";
require_once "../lib/pagination.php";


/**
 * دالة الحصول على معلومات المستخدم
 * @param int $user_id
 * @param string $field
 * @return string
 */
function getUserInfo($user_id, $field) {
    global $con;
    
    $user_id = filter_var($user_id, FILTER_VALIDATE_INT);
    if (!$user_id || $user_id <= 0) {
        return 'غير معروف';
    }
    
    // التحقق من الحقل المسموح به
    $allowed_fields = ['name_prefix', 'fname', 'lname', 'email', 'phone'];
    if (!in_array($field, $allowed_fields)) {
        return '';
    }
    
    $sql = "SELECT $field FROM user WHERE usr_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $value);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * دالة الحصول على اسم المستخدم الكامل
 * @param int $user_id
 * @return string
 */
function getUserFullName($user_id) {
    $prefix = getUserInfo($user_id, 'name_prefix');
    $fname = getUserInfo($user_id, 'fname');
    $lname = getUserInfo($user_id, 'lname');
    
    return trim($prefix . ' ' . $fname . ' ' . $lname);
}

/**
 * كلاس إدارة الرسائل
 */
class listMessage {
    
    public $sqlList = "";
    public $start = 0;
    public $limit = 20;
    public $errors = [];
    
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
        
        // إضافة LIMIT للاستعلام
        $sql = $this->sqlList;
        if ($this->limit > 0) {
            $start = intval($this->start);
            $limit = intval($this->limit);
            $sql .= " LIMIT $start, $limit";
        }
        
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
        
        $sql = "DELETE FROM message WHERE msg_id = ?";
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
            return "?action=del&fid=" . $id;
        } else {
            return "message-view.php?" . $queryString . "&action=del&fid=" . $id;
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
        $this->limit = max(1, min(100, intval($limit))); // حد أقصى 100 عنصر في الصفحة
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
    public function setlimit($default = 20) {
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 100]
        ]);
        return $limit ?: $default;
    }
}

// تهيئة الكلاسات
$p = new Pagination();
$page = $p->setpage();

$al = new listMessage();

// معالجة الحذف الفردي
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
    
    if ($fid && $fid > 0) {
        if ($al->deleterecord($fid)) {
            $_SESSION['success_msg'] = "تم حذف الرسالة بنجاح";
        } else {
            $_SESSION['error_msg'] = "حدث خطأ أثناء حذف الرسالة";
        }
    }
    
    header("Location: message-view.php");
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
        
        $_SESSION['success_msg'] = "تم حذف " . $deleted . " رسالة/رسائل بنجاح";
    } else {
        $_SESSION['warning_msg'] = "لم يتم تحديد أي رسالة للحذف";
    }
    
    header("Location: message-view.php");
    exit();
}

// إعدادات التصفح
$al->setLimit($p->setlimit(20));
$al->setsql("SELECT * FROM message ORDER BY msg_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->setStart($p->setstart($page, $limit, $totalitems));

// إعدادات التصفح
$adjacents = 2;
$targetpage = "message-view.php";
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

// رسائل النظام
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);

$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['error_msg']);

$warning_msg = $_SESSION['warning_msg'] ?? '';
unset($_SESSION['warning_msg']);
?>

<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php" ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="message-view.php">إدارة الرسائل</a>
                    </li>
                    <li class="active">عرض الرسائل</li>
                </ul>
            </div>
            
            <div class="page-content">
                
                <!-- رسائل النظام -->
                <?php if ($success_msg): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i> <?php echo htmlspecialchars($success_msg); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_msg): ?>
                    <div class="alert alert-danger">
                        <i class="icon-remove"></i> <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($warning_msg): ?>
                    <div class="alert alert-warning">
                        <i class="icon-warning"></i> <?php echo htmlspecialchars($warning_msg); ?>
                    </div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            
                            <!-- شريط الأدوات -->
                            <div class="table-header">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                                onclick="return confirm('هل أنت متأكد من حذف الرسائل المحددة؟')">
                                            <i class="icon-trash bigger-120"></i> حذف المحدد
                                        </button>
                                    </div>
                                    <div class="col-xs-6 text-left">
                                        <small>عرض <?php echo $showitems; ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- جدول البيانات -->
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" id="select-all">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th>التاريخ</th>
                                            <th>من</th>
                                            <th>إلى</th>
                                            <th>الموضوع</th>
                                            <th class="center">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($count > 0): ?>
                                            <?php 
                                            $j = 0;
                                            while($row = mysqli_fetch_object($recObj)): 
                                            ?>
                                            <tr <?php echo ($j % 2 == 1) ? 'class="row-clr"' : ''; ?>>
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo $row->msg_id; ?>">
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <?php echo date('Y-m-d H:i', strtotime($row->msg_date)); ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo getUserFullName($row->msg_from); ?></strong>
                                                </td>
                                                <td>
                                                    <strong><?php echo getUserFullName($row->msg_to); ?></strong>
                                                </td>
                                                <td>
                                                    <a href="message-edit.php?fid=<?php echo $row->msg_id; ?>" 
                                                       title="عرض التفاصيل">
                                                        <?php echo htmlspecialchars($row->msg_subject); ?>
                                                    </a>
                                                </td>
                                                <td class="center">
                                                    <div class="btn-group">
                                                        <a href="message-edit.php?fid=<?php echo $row->msg_id; ?>" 
                                                           class="btn btn-xs btn-info" title="عرض">
                                                            <i class="icon-search bigger-120"></i>
                                                        </a>
                                                        <a href="<?php echo $al->deletelink($row->msg_id); ?>" 
                                                           class="btn btn-xs btn-danger" 
                                                           onclick="return confirm('هل أنت متأكد من حذف هذه الرسالة؟')"
                                                           title="حذف">
                                                            <i class="icon-trash bigger-120"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php 
                                                $j++;
                                            endwhile; 
                                        else: ?>
                                            <tr>
                                                <td colspan="6" class="center">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i> لا توجد رسائل لعرضها
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- روابط التصفح -->
                            <?php if ($totalitems > $limit): ?>
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="dataTables_paginate paging_bootstrap">
                                        <ul class="pagination">
                                            <?php
                                            $total_pages = ceil($totalitems / $limit);
                                            
                                            // رابط الصفحة السابقة
                                            if ($page > 1) {
                                                $prev_page = $page - 1;
                                                echo '<li class="prev"><a href="' . $targetpage . $pagestring . $prev_page . '"><i class="icon-double-angle-left"></i></a></li>';
                                            } else {
                                                echo '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
                                            }
                                            
                                            // روابط الصفحات
                                            $start_page = max(1, $page - $adjacents);
                                            $end_page = min($total_pages, $page + $adjacents);
                                            
                                            if ($start_page > 1) {
                                                echo '<li><a href="' . $targetpage . $pagestring . '1">1</a></li>';
                                                if ($start_page > 2) {
                                                    echo '<li class="disabled"><a href="#">...</a></li>';
                                                }
                                            }
                                            
                                            for ($i = $start_page; $i <= $end_page; $i++) {
                                                if ($i == $page) {
                                                    echo '<li class="active"><a href="#">' . $i . '</a></li>';
                                                } else {
                                                    echo '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                                                }
                                            }
                                            
                                            if ($end_page < $total_pages) {
                                                if ($end_page < $total_pages - 1) {
                                                    echo '<li class="disabled"><a href="#">...</a></li>';
                                                }
                                                echo '<li><a href="' . $targetpage . $pagestring . $total_pages . '">' . $total_pages . '</a></li>';
                                            }
                                            
                                            // رابط الصفحة التالية
                                            if ($page < $total_pages) {
                                                $next_page = $page + 1;
                                                echo '<li class="next"><a href="' . $targetpage . $pagestring . $next_page . '"><i class="icon-double-angle-right"></i></a></li>';
                                            } else {
                                                echo '<li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>';
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript files -->
<script src="assets/js/jquery-2.0.3.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    
    // تفعيل DataTable
    var oTable1 = $('#sample-table-2').dataTable({
        "aoColumns": [
            { "bSortable": false },
            { "sType": "date" }, null, null, null,
            { "bSortable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
        },
        "paging": false, // نستخدم التصفح المخصص
        "info": false,
        "searching": true,
        "order": [[1, "desc"]] // ترتيب حسب التاريخ تنازلياً
    });
    
    // تحديد/إلغاء تحديد الكل
    $('#select-all').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
    
    // تفعيل tooltips
    $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
    
    function tooltip_placement(context, source) {
        var $source = $(source);
        var $parent = $source.closest('table');
        var off1 = $parent.offset();
        var w1 = $parent.width();
        var off2 = $source.offset();
        var w2 = $source.width();
        
        if(parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) 
            return 'right';
        return 'left';
    }
});
</script>

<!-- تنسيقات إضافية -->
<style>
.table-header {
    margin-bottom: 10px;
    padding: 8px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
}
td a {
    color: #333;
    text-decoration: none;
}
td a:hover {
    color: #4CAF50;
    text-decoration: underline;
}
</style>

<!-- نهاية ملف message-view.php - الإصدار 2.0.0 -->
</body>
</html>