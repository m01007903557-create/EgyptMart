<?php
/**
 * ملف عرض متطلبات العضوية (Membership Requirements)
 * 
 * @filename    membership-requirements-view.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن عرض وإدارة متطلبات العضوية
 *              مع إمكانية الحذف الفردي والجماعي وعرض التفاصيل
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت والجلسة
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../common.php";



// التحقق من صلاحيات المستخدم
check_admin_login();

/**
 * كلاس إدارة متطلبات العضوية
 */
class AdminLoginlist {
    
    public $sqlList = "";
    public $start = "";
    public $limit = "";
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
     * تنفيذ استعلام العرض
     * @return mysqli_result|false
     */
    public function listview() {
        global $con;
        
        // إضافة LIMIT للاستعلام
        $sql = $this->sqlList;
        if (!empty($this->limit) && $this->limit > 0) {
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
        return floor($this->totalrecord() / $rowPage);
    }
    
    /**
     * حذف سجل (تغيير الحالة إلى 0)
     * @param int $adid
     * @return bool
     */
    public function deleterecord($adid) {
        global $con;
        
        $adid = filter_var($adid, FILTER_VALIDATE_INT);
        if (!$adid) {
            return false;
        }
        
        $sql = "UPDATE membership_requirements SET status = 0 WHERE mp_req_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $adid);
        
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * تحديث حالة الفتح (opened)
     * @param int $id
     * @return bool
     */
    public function markAsOpened($id) {
        global $con;
        
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if (!$id) {
            return false;
        }
        
        $sql = "UPDATE membership_requirements SET opened = 1 WHERE mp_req_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
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
            return "membership-requirements-view.php?" . $queryString . "&action=del&fid=" . $id;
        }
    }
    
    /**
     * الحصول على أخطاء
     * @return array
     */
    public function getErrors() {
        return $this->errors;
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
        $this->limit = max(1, intval($limit));
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
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
        return ($page && $page > 0) ? $page : 1;
    }
    
    /**
     * تحديد قيمة البداية
     * @param int $page
     * @param int $limit
     * @param int $total
     * @return int
     */
    public function setstart($page, $limit, $total) {
        $start = ($page - 1) * $limit;
        return max(0, min($start, $total - $limit));
    }
    
    /**
     * تحديد قيمة الحد
     * @param int $default
     * @return int
     */
    public function setlimit($default = 20) {
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
        return ($limit && $limit > 0) ? $limit : $default;
    }
}

// تهيئة الكلاسات
$p = new Pagination();
$page = $p->setpage();

$al = new AdminLoginlist();

// معالجة الحذف الفردي
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
    
    if ($fid) {
        if ($al->deleterecord($fid)) {
            $_SESSION['success_msg'] = "تم حذف متطلب العضوية بنجاح";
        } else {
            $_SESSION['error_msg'] = "حدث خطأ أثناء حذف متطلب العضوية";
        }
    }
    
    header("Location: membership-requirements-view.php");
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
        
        $_SESSION['success_msg'] = "تم حذف " . $deleted . " متطلب/متطلبات بنجاح";
    } else {
        $_SESSION['warning_msg'] = "لم يتم تحديد أي عنصر للحذف";
    }
    
    header("Location: membership-requirements-view.php");
    exit();
}

// معالجة عرض التفاصيل (تحديث حالة الفتح)
if (isset($_GET['fid']) && !isset($_GET['action'])) {
    $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
    if ($fid) {
        $al->markAsOpened($fid);
    }
}

// إعدادات التصفح
$al->setLimit($p->setlimit(20));
$al->setsql("SELECT * FROM membership_requirements WHERE status = '1' ORDER BY mp_req_id DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->setStart($p->setstart($page, $limit, $totalitems));

// إعدادات التصفح
$adjacents = 1;
$targetpage = "membership-requirements-view.php";
$pagestring = "?limit=" . $limit . "&page=";

// جلب البيانات
$recObj = $al->listview();

// حساب عرض العناصر
$showitems = ($al->start + 1) . "-";
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
                        <a href="membership-requirements-view.php">متطلبات العضوية</a>
                    </li>
                    <li class="active">عرض متطلبات العضوية</li>
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
                
                <form name="con_view" id="con_view" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            
                            <!-- شريط الأدوات -->
                            <div class="table-header">
                                <div class="row">
                                    <div class="col-xs-6">
                                        <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                                onclick="return confirm('هل أنت متأكد من حذف العناصر المحددة؟')">
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
                                            <th>الاسم</th>
                                            <th>البريد الإلكتروني</th>
                                            <th>الجوال</th>
                                            <th>الدولة</th>
                                            <th class="center">التفاصيل</th>
                                            <th class="center">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = mysqli_num_rows($recObj);
                                        if ($count > 0):
                                            while($row = mysqli_fetch_object($recObj)):
                                                // تحديد نمط الصف بناءً على حالة الفتح
                                                $row_class = ($row->opened == 0) ? 'unread' : '';
                                        ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo $row->mp_req_id; ?>">
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($row->updated_date)); ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($row->name)); ?></td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($row->email); ?>">
                                                        <?php echo htmlspecialchars($row->email); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($row->mobile); ?></td>
                                                <td><?php echo htmlspecialchars($row->country); ?></td>
                                                <td class="center">
                                                    <a href="membership-requirement.php?fid=<?php echo $row->mp_req_id; ?>" 
                                                       class="btn btn-xs btn-info" title="عرض التفاصيل">
                                                        <i class="icon-search bigger-120"></i>
                                                    </a>
                                                    <?php if ($row->replied == 1): ?>
                                                        <span class="label label-success" title="تم الرد">
                                                            <i class="icon-reply"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <a href="<?php echo $al->deletelink($row->mp_req_id); ?>" 
                                                       class="btn btn-xs btn-danger" 
                                                       onclick="return confirm('هل أنت متأكد من حذف هذا المتطلب؟')"
                                                       title="حذف">
                                                        <i class="icon-trash bigger-120"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="8" class="center">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i> لا توجد متطلبات عضوية لعرضها
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
                                            for ($i = 1; $i <= $total_pages; $i++) {
                                                if ($i == $page) {
                                                    echo '<li class="active"><a href="#">' . $i . '</a></li>';
                                                } else {
                                                    echo '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                                                }
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
            null, null, null, null, null,
            { "bSortable": false },
            { "bSortable": false }
        ],
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Arabic.json"
        },
        "paging": false, // نستخدم التصفح المخصص
        "info": false
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
    
    // تنسيق الصفوف غير المقروءة
    $('tr.unread').css('font-weight', 'bold');
});
</script>

<!-- تنسيقات إضافية -->
<style>
.unread {
    font-weight: bold;
    background-color: #f9f9f9;
}
.unread:hover {
    background-color: #f5f5f5 !important;
}
.table-header {
    margin-bottom: 10px;
    padding: 8px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
}
</style>

<!-- نهاية ملف membership-requirements-view.php - الإصدار 2.0.0 -->
</body>
</html>