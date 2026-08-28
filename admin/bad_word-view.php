<?php
/**
 * File: admin/bad_word-view.php
 * Version: PHP 8.3
 * Description: عرض وإدارة الكلمات المحظورة في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة الكلمات المحظورة مع إمكانية
 * الحذف والإضافة والتعديل
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من تسجيل دخول المستخدم
check_user_login();

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة الكلمات المحظورة
 */
class categorylist
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
     * حذف كلمة محظورة
     * @param int $did معرف الكلمة
     */
    public function deleterecord(int $did): void
    {
        $did = (int)$did;
        if ($did > 0) {
            $sql = "DELETE FROM bad_word WHERE bd_id = " . $did;
            mysqli_query($this->con, $sql);
        }
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف الكلمة
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&pid=" . $id;
        } else {
            $dellink = "bad_word-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&pid=" . $id;
        }
        return $dellink;
    }
}

// تهيئة كائن Pagination
$p = new Pagination();
$page = $p->setpage();

// تهيئة كائن القائمة
$al = new categorylist();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['pid'])) {
    $pid = (int)$_GET['pid'];
    if ($pid > 0) {
        $al->deleterecord($pid);
    }
    header("location: bad_word-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $p->setlimit(20);
$al->setsql("SELECT * FROM bad_word ORDER BY bd_id DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->setstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "bad_word-view.php";
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

/******************** حذف متعدد (ملاحظة: هذا الكود يستخدم جدول faq_categories - يبدو خطأ) *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $id = (int)$id;
        if ($id > 0) {
            // هذا الكود يحذف من جدول faq_categories - قد يكون خطأ!
            // mysqli_query($con, "DELETE FROM faq_categories WHERE fc_id = " . $id);
            $al->deleterecord($id); // استخدم دالة الحذف الصحيحة
        }
    }
    header("location: bad_word-view.php");
    exit();
}
/*************************************************/
?>
<?php include "includes/admin-top.php" ?>

<script type="text/javascript">
    try{ace.settings.check('main-container' , 'fixed')}catch(e){}
</script>

<div class="main-container" id="main-container">
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
                        <a>Manage Bad Words</a>
                    </li>
                    <li class="active">Bad Word List</li>
                </ul><!-- .breadcrumb -->
            </div>
            
            <div class="page-content">
                <form name="test_view" id="test_view" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('هل أنت متأكد من حذف السجلات المحددة؟')">
                                    <i class="icon-trash bigger-120"></i>Delete
                                </button>
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='bad_word-add.php'">
                                    <i class="icon-plus-sign"></i> Add Bad Word
                                </button>
                                <span style="float: left; margin-top: 5px;"><?php echo htmlspecialchars($showitems); ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center"><label><input type="checkbox" class="ace" id="check_all"><span class="lbl"></span></label></th>
                                            <th style="text-align:center"><strong>#</strong></th>
                                            <th><strong>Words</strong></th>
                                            <th style="text-align:center"><strong>Action</strong></th>
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
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->bd_id; ?>" />
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td style="text-align:center"><?php echo "#" . (int)$row->bd_id; ?></td>
                                                <td><?php echo htmlspecialchars(ucfirst($row->bd_word ?? '')); ?></td>
                                                <td style="text-align:center">
                                                    <a href="bad_word-edit.php?cid=<?php echo (int)$row->bd_id; ?>" title="Edit" class="btn btn-xs btn-info">
                                                        <i class="icon-edit bigger-120"></i>
                                                    </a>
                                                    <a href="<?php echo $al->deletelink((int)$row->bd_id); ?>" title="Delete" onclick="return confirm('هل أنت متأكد من حذف هذه الكلمة؟')" class="btn btn-xs btn-danger">
                                                        <i class="icon-trash bigger-120"></i>
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
                                                    لا توجد كلمات محظورة
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