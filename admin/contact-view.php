<?php
/**
 * File: admin/contact-view.php
 * Version: PHP 8.3
 * Description: عرض وإدارة استفسارات الاتصال في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة استفسارات الاتصال مع إمكانية
 * عرض التفاصيل والحذف
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
 * كلاس إدارة قائمة استفسارات الاتصال
 */
class AdminLoginlist
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
     * حذف (تعطيل) استفسار
     * @param int $adid معرف الاستفسار
     */
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        if ($adid > 0) {
            $sql = "UPDATE contact_us SET cu_status = 0 WHERE cu_id = " . $adid;
            mysqli_query($this->con, $sql);
        }
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف الاستفسار
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&fid=" . $id;
        } else {
            $dellink = "contact-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        }
        return $dellink;
    }
}

// تهيئة كائن Pagination
$p = new Pagination();
$page = $p->getCurrentPage();
// تهيئة كائن القائمة
$al = new AdminLoginlist();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $fid = (int)$_GET['fid'];
    if ($fid > 0) {
        $al->deleterecord($fid);
    }
    header("location: contact-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $p->getlimit(20);
$al->setsql("SELECT * FROM contact_us WHERE cu_status = '1' ORDER BY cu_id DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->getstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "contact-view.php";
$pagestring = "?limit=" . $limit . "&page=";

$recObj = $al->listview();
$count = $recObj ? mysqli_num_rows($recObj) : 0;

// عرض عدد العناصر
$showitems = ($al->start + 1) . "-";
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
    header("location: contact-view.php");
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
                        <a href="contact-view.php">Manage Contact Us</a>
                    </li>
                    <li class="active">View Contact Us</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <form name="con_view" id="con_view" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>Delete
                                </button>
                                <span style="float: left; margin-top: 5px;"><?php echo htmlspecialchars($showitems); ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center"><label><input type="checkbox" class="ace" id="check_all"><span class="lbl"></span></label></th>
                                            <th><strong>Date</strong></th>
                                            <th><strong>Name</strong></th>
                                            <th><strong>Email</strong></th>
                                            <th><strong>Mobile Number</strong></th>
                                            <th><strong>Country/State</strong></th>
                                            <th style="text-align:center"><strong>Details</strong></th>
                                            <th style="text-align:center"><strong>Action</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        if ($count > 0):
                                            while ($row = mysqli_fetch_object($recObj)):
                                                $row_class = ($row->opened == 0) ? 'unread-row' : '';
                                                $font_weight = ($row->opened == 0) ? 'bold' : 'normal';
                                        ?>
                                            <tr style="font-weight: <?php echo $font_weight; ?>;" class="<?php echo $row_class; ?>">
                                                <td class="center">
                                                    <label>
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->cu_id; ?>" />
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td><?php echo !empty($row->cu_updated_date) ? date('d/m/Y', strtotime($row->cu_updated_date)) : ''; ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($row->cu_fname ?? '') . ' ' . ucfirst($row->cu_lname ?? '')); ?></td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($row->cu_email ?? ''); ?>">
                                                        <?php echo htmlspecialchars($row->cu_email ?? ''); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($row->cu_contactnumber ?? ''); ?></td>
                                                <td>
                                                    <?php 
                                                    echo htmlspecialchars(ucfirst($row->cu_country ?? '')) . '-' . htmlspecialchars(ucfirst($row->cu_state ?? '')); 
                                                    ?>
                                                </td>
                                                <td style="text-align:center">
                                                    <a href="contact-details.php?fid=<?php echo (int)$row->cu_id; ?>" title="View Details">
                                                        <img src="images/details.png" alt="Details" />
                                                    </a>
                                                    <?php if ($row->replied == 1): ?>
                                                        <i class="icon-reply icon-only" title="Replied" style="color: green; margin-left: 5px;"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td style="text-align:center">
                                                    <a href="<?php echo $al->deletelink((int)$row->cu_id); ?>" title="Delete" onclick="return confirm('هل أنت متأكد من حذف هذا الاستفسار؟')">
                                                        <img src="images/delete.jpg" border="0" alt="Delete" />
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="8" align="center" style="padding: 20px; color: #F00;">
                                                    لا توجد استفسارات
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
                
                <br clear="all" />
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

<style>
    .unread-row {
        background-color: #f5f5f5 !important;
    }
    .icon-reply {
        color: green;
    }
</style>

</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>