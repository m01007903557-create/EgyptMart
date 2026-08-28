<?php
/**
 * File: newsletter-view.php
 * Version: 2.0.0
 * Description: عرض وإدارة النشرات البريدية المرسلة مع إمكانية البحث والحذف (تمت الترقية إلى PHP 8.3)
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
require_once "../lib/pagination.php";

// التحقق من تسجيل الدخول
check_admin_login();

/**
 * Class ListCat - إدارة قائمة النشرات البريدية
 */
class ListCat {
    public string $sqlList = '';
    public int $start = 0;
    public int $limit = 0;
    public ?mysqli $dbConnection = null;
    
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
    public function getStart(int $start): self {
        $this->start = max(0, $start);
        return $this;
    }
    
    /**
     * تعيين عدد العناصر في الصفحة
     */
    public function getLimit(int $limit): self {
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
     * حذف سجل (تعطيل)
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "UPDATE newsletter_content SET nc_status = '0' WHERE nc_id = ?";
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
            return "?action=del&fid=" . $cleanId;
        }
        
        return "newsletter-view.php?" . htmlspecialchars($queryString) . "&action=del&fid=" . $cleanId;
    }
}

/**
 * Class Pagination - إدارة الترقيم
 */





// تهيئة الكلاسات
$p = new Pagination();
$page = $p->getCurrentPage();
$al = new ListCat();

// معالجة حذف سجل مفرد
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
    if ($fid !== false && $fid > 0) {
        $al->deleterecord($fid);
        $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم حذف النشرة بنجاح</div>';
    }
    header("Location: newsletter-view.php");
    exit();
}

// معالجة الحذف المتعدد
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    foreach ($_POST['cb'] as $id) {
        $cleanId = filter_var($id, FILTER_VALIDATE_INT);
        if ($cleanId !== false && $cleanId > 0) {
            mysqli_query($con, "UPDATE newsletter_content SET nc_status = 0 WHERE nc_id = $cleanId");
            $deleted++;
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> تم حذف ' . $deleted . ' نشرة بنجاح</div>';
    }
    
    header("Location: newsletter-view.php");
    exit();
}

// إعدادات الترقيم
$limit = $p->getLimit(20);
$al->getLimit($limit);
$al->setsql("SELECT * FROM newsletter_content WHERE nc_status = '1' ORDER BY nc_id DESC");
$totalitems = $al->totalrecord();
$al->getStart($p->getstart($page, $limit, $totalitems));
$recObj = $al->listview();
$count = $recObj ? mysqli_num_rows($recObj) : 0;

// حساب نص عرض العناصر
$startItem = $totalitems > 0 ? $al->start + 1 : 0;
$endItem = min($al->start + $limit, $totalitems);
$showitems = $totalitems > 0 ? "$startItem - $endItem من $totalitems نشرة" : "0 نشرات";

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
                        <a href="newsletter-view.php">إدارة النشرات</a>
                    </li>
                    <li class="active">عرض النشرات</li>
                </ul>
            </div>

            <div class="page-content">
                <?php if ($msg): ?>
                    <div id="msg"><?php echo $msg; ?></div>
                <?php endif; ?>

                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('هل أنت متأكد من حذف النشرات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>
                                    حذف المحدد
                                </button>
                                <button type="button" class="btn btn-xs btn-success" 
                                        onclick="window.location='newsletter-send.php'">
                                    <i class="icon-pencil align-top bigger-120"></i>
                                    إرسال نشرة جديدة
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
                                            <th><strong>عنوان النشرة</strong></th>
                                            <th style="text-align:center"><strong>محتوى النشرة</strong></th>
                                            <th style="text-align:center"><strong>الإجراءات</strong></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        if ($count > 0):
                                            while($row = mysqli_fetch_assoc($recObj)): 
                                        ?>
                                            <tr <?php if($j % 2 == 1) echo 'class="row-clr"'; ?>>
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row['nc_id']; ?>">
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td>
                                                    <div class="add_width_set_1">
                                                        <?php echo htmlspecialchars($row['nc_subject'] ?? ''); ?>
                                                    </div>
                                                </td>
                                                <td style="text-align:center">
                                                    <div class="add_width_set">
                                                        <?php 
                                                        // عرض مختصر للمحتوى
                                                        $content = strip_tags($row['nc_content'] ?? '');
                                                        $shortContent = mb_substr($content, 0, 200) . (mb_strlen($content) > 200 ? '...' : '');
                                                        echo htmlspecialchars($shortContent);
                                                        ?>
                                                    </div>
                                                </td>
                                                <td style="text-align:center">
                                                    <a href="newsletter-edit.php?fid=<?php echo (int)$row['nc_id']; ?>" 
                                                       title="تعديل">
                                                        <img alt="تعديل" src="images/edit.jpg" border="0">
                                                    </a>
                                                    <a href="<?php echo htmlspecialchars($al->deletelink($row['nc_id'])); ?>" 
                                                       title="حذف" 
                                                       onclick="return confirm('هل أنت متأكد من حذف هذه النشرة؟')">
                                                        <img alt="حذف" src="images/delete.jpg" border="0">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="4" align="center">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i>
                                                        لا توجد نشرات لعرضها
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
                <br clear="all" />
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<style>
.add_width_set {
    word-wrap: break-word;
    overflow-wrap: break-word;
    width: 500px;
    max-height: 100px;
    overflow-y: auto;
    padding: 5px;
    background: #f9f9f9;
    border-radius: 3px;
}
.add_width_set_1 {
    word-wrap: break-word;
    overflow-wrap: break-word;
    width: 200px;
    font-weight: bold;
    color: #333;
}
.table-header {
    padding: 10px;
    background: #f5f5f5;
    border-bottom: 1px solid #ddd;
    overflow: hidden;
}
</style>

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
// دالة تحديد الكل / إلغاء التحديد
function toggleCheckAll(source) {
    var checkboxes = document.querySelectorAll('input[name="cb[]"]');
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = source.checked;
        checkbox.closest('tr')?.classList.toggle('selected', source.checked);
    });
}

jQuery(function($) {
    // تفعيل DataTable
    var oTable1 = $('#sample-table-2').dataTable({
        retrieve: true,
        language: {
            "emptyTable": "لا توجد نتائج",
            "search": "بحث:",
            "lengthMenu": "عرض _MENU_ عنصر في الصفحة",
            "info": "عرض _START_ إلى _END_ من _TOTAL_ عنصر",
            "infoEmpty": "عرض 0 إلى 0 من 0 عنصر",
            "infoFiltered": "(مرشح من _MAX_ عنصر)",
            "zeroRecords": "لا توجد نتائج",
            "oPaginate": {
                "sFirst": "الأول",
                "sPrevious": "السابق",
                "sNext": "التالي",
                "sLast": "الأخير"
            }
        },
        paging: true,
        columnDefs: [{
            "defaultContent": "-",
            "targets": "_all"
        }],
        "aoColumns": [
            { "bSortable": false },
            null, 
            null,
            { "bSortable": false }
        ]
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
        $('#msg').fadeOut('slow');
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

</body>
</html>