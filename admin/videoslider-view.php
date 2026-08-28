<?php
/**
 * File: videoslider-view.php
 * Version: 2.0.0
 * Description: عرض وإدارة سلايدر الفيديو مع إمكانية الحذف وتغيير الحالة (تمت الترقية إلى PHP 8.3)
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


// التحقق من تسجيل الدخول
check_admin_login();

/**
 * Class SliderViewList - إدارة قائمة سلايدر الفيديو
 */
class SliderViewList {
   public  string $sqlList = '';
    public  int $start = 0;
    public  int $limit = 0;
   public  ?mysqli $dbConnection = null;
    
    /**
     * المُنشئ
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
     * الحصول على عرض القائمة
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
     * حذف سجل مع الصورة المرتبطة
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        // حذف الصورة المرتبطة
        $this->deleteImage($cleanId);
        
        // حذف السجل من قاعدة البيانات
        $sql = "DELETE FROM video_slider WHERE adv_id = ?";
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
     * حذف صورة السلايدر
     */
    private function deleteImage(int $id): void {
        $sql = "SELECT adv_img FROM video_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row && !empty($row['adv_img'])) {
            $filePath = __DIR__ . '/../upload/video_slider/' . $row['adv_img'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    /**
     * إنشاء رابط الحذف
     */
    public function deletelink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid=" . $cleanId;
        }
        
        return "videoslider-view.php?" . htmlspecialchars($queryString) . "&action=del&fid=" . $cleanId;
    }
}

/**
 * Class Pagination - إدارة الترقيم
 */
class Pagination {
    private int $defaultLimit = 20;
    private int $maxLimit = 100;
    
    public function setpage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page);
    }
    
    public function setlimit(int $default = 20): int {
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
}



// تهيئة الكلاسات
$p = new Pagination();
$page = $p->setpage();
$al = new SliderViewList();

// معالجة حذف سجل مفرد
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
    if ($fid !== false && $fid > 0) {
        if ($al->deleterecord($fid)) {
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم الحذف بنجاح</div>';
        }
    }
    header("Location: videoslider-view.php");
    exit();
}

// معالجة الحذف المتعدد
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    foreach ($_POST['cb'] as $id) {
        $cleanId = filter_var($id, FILTER_VALIDATE_INT);
        if ($cleanId !== false && $cleanId > 0) {
            if ($al->deleterecord($cleanId)) {
                $deleted++;
            }
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم حذف ' . $deleted . ' سجل/سجلات بنجاح</div>';
    }
    
    header("Location: videoslider-view.php");
    exit();
}

// إعدادات الترقيم
$limit = $p->setlimit(20);
$al->setLimit($limit);
$al->setsql("SELECT * FROM video_slider ORDER BY adv_updated_date DESC");
$totalitems = $al->totalrecord();
$al->setStart($p->setstart($page, $limit, $totalitems));
$recObj = $al->listview();
$count = $recObj ? mysqli_num_rows($recObj) : 0;

// حساب نص عرض العناصر
$startItem = $totalitems > 0 ? $al->start + 1 : 0;
$endItem = min($al->start + $limit, $totalitems);
$showitems = $totalitems > 0 ? "$startItem - $endItem من $totalitems سجل" : "0 سجلات";

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
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
                        <a href="videoslider-view.php">إدارة السلايدر</a>
                    </li>
                    <li class="active">عرض السلايدر</li>
                </ul>
            </div>

            <div class="page-content">
                
                <?php if ($msg): ?>
                    <div class="row">
                        <div class="col-xs-12">
                            <?php echo $msg; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post"> 
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>
                                    حذف المحدد
                                </button>
                                <button type="button" class="btn btn-xs btn-success" 
                                        onclick="window.location='videoslider-add.php'">
                                    <i class="icon-pencil align-top bigger-120"></i>
                                    إضافة سلايدر جديد
                                </button>
                                <span style="float:left; margin-top:5px; color:#666;">
                                    <?php echo htmlspecialchars($showitems); ?>
                                </span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" onclick="toggleCheckAll(this)">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th><strong>الروابط</strong></th>
                                            <th><strong>العنوان</strong></th>
                                            <th style="text-align:center;"><strong>المعلومات</strong></th>
                                            <th><strong>الحالة</strong></th>
                                            <th><strong>تغيير الحالة</strong></th>
                                            <th><strong>الإجراءات</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        if ($count > 0):
                                            while($row = mysqli_fetch_assoc($recObj)):
                                                
                                                // تنسيق أسماء الدول
                                                $countriesDisplay = '';
                                                if (!empty($row['adv_country'])) {
                                                    $countryIds = explode(",", $row['adv_country']);
                                                    $countryNames = [];
                                                    foreach ($countryIds as $countryId) {
                                                        $countryId = (int)$countryId;
                                                        if ($countryId > 0) {
                                                            $countryNames[] = get_country_name($countryId);
                                                        }
                                                    }
                                                    $countriesDisplay = implode("، ", array_filter($countryNames));
                                                }
                                        ?>
                                            <tr>
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row['adv_id']; ?>">
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td style="text-align:right;">
                                                    <strong>رابط الفيديو:</strong><br>
                                                    <a href="<?php echo htmlspecialchars($row['adv_link'] ?? '#'); ?>" target="_blank" style="font-size:11px;">
                                                        <?php echo htmlspecialchars(substr($row['adv_link'] ?? '', 0, 50)) . (strlen($row['adv_link'] ?? '') > 50 ? '...' : ''); ?>
                                                    </a>
                                                    <br><br>
                                                    <strong>رابط إعادة التوجيه:</strong><br>
                                                    <?php if (!empty($row['adv_redirect'])): ?>
                                                        <a href="<?php echo htmlspecialchars($row['adv_redirect']); ?>" target="_blank" style="font-size:11px;">
                                                            <?php echo htmlspecialchars(substr($row['adv_redirect'], 0, 50)) . (strlen($row['adv_redirect']) > 50 ? '...' : ''); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span style="color:#999;">لا يوجد</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center; font-weight:bold;">
                                                    <?php echo htmlspecialchars($row['adv_title'] ?? ''); ?>
                                                </td>
                                                <td style="text-align:right;">
                                                    <strong>الوصف:</strong><br>
                                                    <?php 
                                                    $desc = strip_tags($row['adv_description'] ?? '');
                                                    echo nl2br(htmlspecialchars(substr($desc, 0, 100))) . (strlen($desc) > 100 ? '...' : '');
                                                    ?>
                                                    <br><br>
                                                    <?php if (!empty($countriesDisplay)): ?>
                                                        <strong>الدول:</strong> <?php echo htmlspecialchars($countriesDisplay); ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center">
                                                    <?php if (isset($row['adv_status']) && $row['adv_status'] == '1'): ?>
                                                        <span class="label label-success">نشط</span>
                                                    <?php else: ?>
                                                        <span class="label label-default">غير نشط</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center;">
                                                    <select onchange="changeStatus(this.value, '<?php echo (int)$row['adv_id']; ?>')">
                                                        <option value="">اختر</option>
                                                        <?php if (($row['adv_status'] ?? '') == '1'): ?>
                                                            <option value="0">إلغاء التفعيل</option>
                                                        <?php else: ?>
                                                            <option value="1">تفعيل</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </td>
                                                <td align="center">
                                                    <a href="videoslider-edit.php?aid=<?php echo (int)$row['adv_id']; ?>" 
                                                       class="btn btn-xs btn-info" title="تعديل">
                                                        <i class="icon-pencil"></i>
                                                    </a>
                                                    <a href="<?php echo htmlspecialchars($al->deletelink($row['adv_id'])); ?>" 
                                                       class="btn btn-xs btn-danger" 
                                                       onclick="return confirm('هل أنت متأكد من حذف هذا السجل؟')"
                                                       title="حذف">
                                                        <i class="icon-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i>
                                                        لا توجد سجلات لعرضها
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<script type="text/javascript">
window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
// دالة تغيير الحالة
function changeStatus(stat, id) {
    if (!stat || !id) {
        alert('الرجاء اختيار حالة صحيحة');
        return;
    }
    
    $.post("ajax-file/videoslider-change-status.php", {stat: stat, id: id})
        .done(function(data) {
            location.reload();
        })
        .fail(function() {
            alert('حدث خطأ في تغيير الحالة');
        });
}

// دالة تحديد الكل
function toggleCheckAll(source) {
    var checkboxes = document.querySelectorAll('input[name="cb[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = source.checked;
        checkbox.closest('tr')?.classList.toggle('selected', source.checked);
    });
}

jQuery(function($) {
    // تفعيل DataTable مع ترجمة
    var oTable1 = $('#sample-table-2').dataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            null,
            null,
            null,
            { "bSortable": false },
            { "bSortable": false }
        ],
        "oLanguage": {
            "sSearch": "بحث:",
            "sLengthMenu": "عرض _MENU_ عنصر في الصفحة",
            "sInfo": "عرض _START_ إلى _END_ من _TOTAL_ عنصر",
            "sInfoEmpty": "عرض 0 إلى 0 من 0 عنصر",
            "sInfoFiltered": "(مرشح من _MAX_ عنصر)",
            "sZeroRecords": "لا توجد نتائج",
            "oPaginate": {
                "sFirst": "الأول",
                "sPrevious": "السابق",
                "sNext": "التالي",
                "sLast": "الأخير"
            }
        }
    });
    
    // تفعيل خاصية تحديد الكل
    $('table th input:checkbox').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
    
    // تفعيل tooltips
    $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
    
    function tooltip_placement(context, source) {
        var $source = $(source);
        var $parent = $source.closest('table');
        var off1 = $parent.offset();
        var w1 = $parent.width();
        var off2 = $source.offset();
        var w2 = $source.width();
        
        if(parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) return 'right';
        return 'left';
    }
});
</script>

<style>
.label {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: normal;
}
.label-success {
    background-color: #5cb85c;
    color: white;
}
.label-default {
    background-color: #777;
    color: white;
}
.table-header {
    padding: 10px;
    background: #f5f5f5;
    border-bottom: 1px solid #ddd;
    margin-bottom: 10px;
    overflow: hidden;
}
.btn-xs {
    margin: 0 2px;
}
</style>

</body>
</html>
<?php ob_end_flush(); ?>