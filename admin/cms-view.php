<?php
/**
 * File: admin/cms-view.php
 * Version: PHP 8.3
 * Description: عرض وإدارة صفحات نظام إدارة المحتوى (CMS) في لوحة التحكم
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

// تضمين الملفات الأساسية
require_once dirname(__DIR__) . "/common.php";
require_once "../lib/pagination.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة صفحات CMS
 */
class Cmslist
{
    public string $sqlList = "";
    public int $start = 0;
    public int $limit = 20;
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
    public function setsql(string $sql): void
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
}

// تهيئة Pagination باستخدام الدوال الجديدة
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

// تهيئة كائن القائمة
$al = new Cmslist();

// إعدادات الصفحة - استخدام الدوال الجديدة
$al->limit = $pagination->getLimit(20);
$al->setsql("SELECT * FROM cms WHERE cms_status = 1 ORDER BY cms_title");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "cms-view.php";
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
$showitems .= " من " . $totalitems . " عنصر";
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
                        <a>Manage CMS</a>
                    </li>
                    <li class="active">View CMS</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            
                           <!-- معلومات عدد العناصر -->
                            <div class="table-header" style="padding: 8px; background-color: #f5f5f5; border-bottom: 1px solid #ddd;">
                                <span style="float: left;"><?php echo htmlspecialchars($showitems); ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center" style="width: 30px;">&nbsp;</th>
                                            <th><strong>Page Title</strong></th>
                                            <th><strong>Content</strong></th>
                                            <th style="text-align:center; width: 100px;"><strong>Action</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        if ($count > 0):
                                            while ($row = mysqli_fetch_object($recObj)):
                                        ?>
                                            <tr>
                                                <td class="center"><?php echo ($j + 1); ?></td>
                                                <td><?php echo htmlspecialchars(stripslashes($row->cms_title ?? '')); ?></td>
                                                <td>
                                                    <?php 
                                                    $content = strip_tags($row->cms_content ?? '');
                                                    $short_content = strlen($content) > 70 ? substr($content, 0, 70) . '...' : $content;
                                                    echo htmlspecialchars($short_content);
                                                    ?>
                                                </td>
                                                <td align="center">
                                                    <a href="cms-edit.php?pid=<?php echo (int)$row->cms_id; ?>" title="Edit" class="btn btn-xs btn-info">
                                                        <i class="icon-edit bigger-120"></i> Edit
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="4" align="center" style="padding: 20px; color: #F00;">
                                                    لا توجد صفحات CMS
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
jQuery(function($) {
    var oTable1 = $('#sample-table-2').dataTable({
        "aoColumns": [
            { "bSortable": false },
            null,
            null,
            { "bSortable": false }
        ]
    });

    // تحديد الكل
    $('table th input:checkbox').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
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