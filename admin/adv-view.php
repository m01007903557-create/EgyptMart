<?php
/**
 * File: admin/adv-view.php
 * Version: PHP 8.3
 * Description: عرض وإدارة الإعلانات في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة الإعلانات مع إمكانية
 * التعديل والحذف وتغيير الحالة وإضافة إعلانات جديدة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة الإعلانات
 */
class sliderviewlist
{
    public $sqlList = "";
    public $start = 0;
    public $limit = 0;
    public $con;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        global $con;
        $this->con = $con;
    }
    
    /**
     * تعيين استعلام SQL
     * @param string $sql الاستعلام
     */
    public function setsql($sql): void
    {
        $this->sqlList = $sql;
    }
    
    /**
     * حساب إجمالي عدد السجلات
     * @return int عدد السجلات
     */
    public function totalrecord(): int
    {
        $result = mysqli_query($this->con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * جلب السجلات
     * @return mysqli_result|bool نتيجة الاستعلام
     */
    public function listview()
    {
        return mysqli_query($this->con, $this->sqlList);
    }
    
    /**
     * حساب عدد الصفحات
     * @param int $rowPage عدد السجلات في الصفحة
     * @return int عدد الصفحات
     */
    public function numpage(int $rowPage): int
    {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
    /**
     * حذف إعلان مع الصورة المرتبطة به
     * @param int $adid معرف الإعلان
     */
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        if ($adid <= 0) return;
        
        // جلب معلومات الصورة لحذفها
        $chquesql = "SELECT * FROM advertisement WHERE adv_id = " . $adid;
        $chqueres = mysqli_query($this->con, $chquesql);
        
        if ($chqueres && mysqli_num_rows($chqueres) > 0) {
            $chquerow = mysqli_fetch_array($chqueres);
            
            // حذف الصورة من المجلد إذا كانت موجودة
            if (!empty($chquerow['adv_img'])) {
                $path = "../upload/advertisement/" . $chquerow['adv_img'];
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
        
        // حذف السجل من قاعدة البيانات
        $sql_del = "DELETE FROM advertisement WHERE adv_id = " . $adid;
        mysqli_query($this->con, $sql_del);
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف الإعلان
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&fid=" . $id;
        } else {
            $dellink = "adv-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        }
        return $dellink;
    }
}

// تهيئة كائن Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

// تهيئة كائن القائمة
$al = new sliderviewlist();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $fid = (int)$_GET['fid'];
    if ($fid > 0) {
        $al->deleterecord($fid);
    }
    header("location: adv-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $pagination->getlimit(20);
$al->setsql("SELECT * FROM advertisement ORDER BY adv_updated_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "adv-view.php";
$pagestring = "?limit=" . $limit . "&page=";

$recObj = $al->listview();
$count = $recObj ? mysqli_num_rows($recObj) : 0;

// عرض عدد العناصر
$showitems = ($al->start + 1) . " - ";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " من " . $al->totalrecord() . " عنصر";

/******************** حذف متعدد *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $id = (int)$id;
        if ($id > 0) {
            $al->deleterecord($id);
        }
    }
    header("location: adv-view.php");
    exit();
}
/*************************************************/
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
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="adv-view.php">Manage Advertisement</a>
                    </li>
                    <li class="active">View Advertisement</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>Delete
                                </button>
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='adv-add.php'">
                                    <i class="icon-pencil align-top bigger-120"></i>Add Advertisement
                                </button>
                                <span style="float: left; margin-top: 5px;"><?php echo htmlspecialchars($showitems); ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center"><label><input type="checkbox" class="ace" id="check_all"><span class="lbl"></span></label></th>
                                            <th><strong>Image</strong></th>
                                            <th><strong>Link</strong></th>
                                            <th><strong>Width & Height</strong></th>
                                            <th><strong>Status</strong></th>
                                            <th><strong>Change Status</strong></th>
                                            <th><strong>Action</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        if ($count > 0):
                                            while ($row = mysqli_fetch_object($recObj)):
                                        ?>
                                            <tr>
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->adv_id; ?>" />
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td style="text-align:center;">
                                                    <?php if (!empty($row->adv_img)): ?>
                                                        <img src="../upload/advertisement/<?php echo htmlspecialchars($row->adv_img); ?>" width="200px;" height="150px;" style="border: 1px solid #ddd; padding: 3px;" alt="Advertisement" />
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center"><?php echo htmlspecialchars($row->adv_link ?? ''); ?></td>
                                                <td style="text-align:center"><?php echo (int)$row->adv_imagewidth . " x " . (int)$row->adv_imageheight; ?></td>
                                                <td style="width:90px; text-align:center">
                                                    <?php if ($row->adv_status == '1'): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center;">
                                                    <select onchange="changeStatus(this.value, <?php echo (int)$row->adv_id; ?>)" style="width: 100px;">
                                                        <option value="">-- Select --</option>
                                                        <?php if ($row->adv_status == '1'): ?>
                                                            <option value="0">Deactivate</option>
                                                        <?php else: ?>
                                                            <option value="1">Activate</option>
                                                        <?php endif; ?>
                                                    </select>
                                                </td>
                                                <td align="center">
                                                    <a href="adv-edit.php?aid=<?php echo (int)$row->adv_id; ?>" title="Edit">
                                                        <img alt="edit" src="images/edit.jpg" border="0">
                                                    </a>
                                                    <a href="<?php echo $al->deletelink((int)$row->adv_id); ?>" title="Delete" onclick="return confirm('هل أنت متأكد من حذف هذا الإعلان؟')">
                                                        <img alt="delete" src="images/delete.jpg" border="0">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="7" align="center" style="padding: 20px; color: #F00;">
                                                    لا توجد إعلانات
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                
                <!-- Pagination -->
                <?php if ($totalitems > $limit): ?>
                <div class="pager">
                    <?php echo $pagination->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring); ?>
                </div>
                <?php endif; ?>
                
                <br clear="all" />
            </div>
        </div>
    </div>
    
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<script type="text/javascript">
/**
 * تغيير حالة الإعلان
 * @param {string} stat - الحالة الجديدة (0 أو 1)
 * @param {number} id - معرف الإعلان
 */
function changeStatus(stat, id) {
    if (stat == '') return;
    
    $.post("ajax-file/adv-change-status.php", {stat: stat, id: id}, function(data) {
        location.reload();
    }).fail(function() {
        alert("حدث خطأ في تغيير الحالة");
    });
}
</script>

<!-- JavaScript Libraries -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!-- DataTables -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<!-- ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    // تهيئة DataTable
    var oTable1 = $('#sample-table-2').dataTable({
        "aoColumns": [
            { "bSortable": false },
            { "bSortable": false },
            null,
            null,
            null,
            { "bSortable": false },
            { "bSortable": false }
        ]
    });
    
    // تحديد الكل
    $('#check_all').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox').each(function() {
            this.checked = that.checked;
            $(this).closest('tr').toggleClass('selected');
        });
    });
    
    // أداة المساعدة
    $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
    
    function tooltip_placement(context, source) {
        var $source = $(source);
        var $parent = $source.closest('table');
        var off1 = $parent.offset();
        var w1 = $parent.width();
        var off2 = $source.offset();
        var w2 = $source.width();
        
        if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) return 'right';
        return 'left';
    }
});
</script>

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>