<?php
/**
 * File: admin/adsense-view.php
 * Version: PHP 8.3
 * Description: عرض وإدارة إعلانات Google AdSense في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة إعلانات Google AdSense مع إمكانية
 * التعديل والحذف والإضافة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once "../common.php";
require_once "../lib/pagination.php";


// التحقق من تسجيل دخول المستخدم
check_admin_login();


// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة إعلانات Google AdSense
 */
class listMessage
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
     * حذف إعلان
     * @param int $adid معرف الإعلان
     */
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        if ($adid > 0) {
            $sql = "DELETE FROM google_adsense WHERE ga_id = " . $adid;
            mysqli_query($this->con, $sql);
        }
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
            $dellink = "adsense-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        }
        return $dellink;
    }
}

// تهيئة كائن Pagination
$p = new Pagination();
$page = $p->getCurrentPage();

// تهيئة كائن القائمة
$al = new listMessage();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $fid = (int)$_GET['fid'];
    if ($fid > 0) {
        $al->deleterecord($fid);
    }
    header("location: adsense-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $p->getLimit(20);
$al->setsql("SELECT * FROM google_adsense WHERE ga_status = '1' ORDER BY ga_updated_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->getstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "adsense-view.php";
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
    header("Location: adsense-view.php");
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
                        <a href="adsense-view.php">Manage Google Adsense</a>
                    </li>
                    <li class="active">View Google Adsense</li>
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
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='adsense-add.php'">
                                    <i class="icon-plus-sign"></i> Add Google Adsense
                                </button>
                                <span style="float: left; margin-top: 5px;"><?php echo htmlspecialchars($showitems); ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" id="check_all">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th><strong>Adsense</strong></th>
                                            <th style="text-align:center"><strong>Action</strong></th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        if ($count > 0):
                                            while ($row = mysqli_fetch_object($recObj)):
                                                $row_class = ($j % 2 == 1) ? 'row-clr' : '';
                                        ?>
                                            <tr class="<?php echo $row_class; ?>">
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->ga_id; ?>" />
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td><?php echo htmlspecialchars($row->ga_content ?? ''); ?></td>
                                                <td style="text-align:center">
                                                    <a href="adsense-edit.php?fid=<?php echo (int)$row->ga_id; ?>" title="Edit">
                                                        <img alt="edit" src="images/edit.jpg" border="0">
                                                    </a>
                                                    <a href="<?php echo $al->deletelink((int)$row->ga_id); ?>" title="Delete" onclick="return confirm('هل أنت متأكد من حذف هذا الإعلان؟')">
                                                        <img alt="Delete" src="images/delete.jpg" border="0">
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="3" align="center" style="padding: 20px; color: #F00;">
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
                    <?php echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring); ?>
                </div>
                <?php endif; ?>
                
                <br clear="all"/>
            </div>
        </div>
    </div>
    
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

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
            null,
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