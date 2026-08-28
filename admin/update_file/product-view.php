<?php
/**
 * File: admin/product-view.php
 * Version: PHP 8.3
 * Description: عرض وإدارة المنتجات في لوحة التحكم
 * 
 * تعرض هذه الصفحة قائمة المنتجات مع إمكانية
 * الموافقة والرفض والحذف وعرض التفاصيل
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * كلاس إدارة قائمة المنتجات
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
     * حذف منتج
     * @param int $adid معرف المنتج
     */
    public function deleterecord(int $adid): void
    {
        $adid = (int)$adid;
        if ($adid > 0) {
            $sql = "DELETE FROM products WHERE pd_id = " . $adid;
            mysqli_query($this->con, $sql);
        }
    }
    
    /**
     * الموافقة على منتج
     * @param int $aid معرف المنتج
     */
    public function approve_record(int $aid): void
    {
        $aid = (int)$aid;
        if ($aid > 0) {
            $sql = "UPDATE products SET pd_status = '1' WHERE pd_id = " . $aid;
            mysqli_query($this->con, $sql);
        }
    }
    
    /**
     * رفض منتج
     * @param int $aid معرف المنتج
     */
    public function disapprove_record(int $aid): void
    {
        $aid = (int)$aid;
        if ($aid > 0) {
            $sql = "UPDATE products SET pd_status = '2' WHERE pd_id = " . $aid;
            mysqli_query($this->con, $sql);
        }
    }
    
    /**
     * إنشاء رابط الحذف
     * @param int $id معرف المنتج
     * @return string رابط الحذف
     */
    public function deletelink(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&ad-id=" . $id;
        } else {
            $dellink = "product-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $id;
        }
        return $dellink;
    }
    
    /**
     * إنشاء رابط الموافقة
     * @param int $id معرف المنتج
     * @return string رابط الموافقة
     */
    public function approve(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $plink = "?action=appr&id=" . $id;
        } else {
            $plink = "product-view.php?" . $_SERVER['QUERY_STRING'] . "&action=appr&id=" . $id;
        }
        return $plink;
    }
    
    /**
     * إنشاء رابط الرفض
     * @param int $id معرف المنتج
     * @return string رابط الرفض
     */
    public function disapprove(int $id): string
    {
        if ($_SERVER['QUERY_STRING'] == "") {
            $plink = "?action=disappr&id=" . $id;
        } else {
            $plink = "product-view.php?" . $_SERVER['QUERY_STRING'] . "&action=disappr&id=" . $id;
        }
        return $plink;
    }
}

// تهيئة كائن Pagination
$p = new Pagination();
$page = $p->setpage();

// تهيئة كائن القائمة
$al = new AdminLoginlist();

/******************** حذف سجل مفرد *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['ad-id'])) {
    $adid = (int)$_GET['ad-id'];
    if ($adid > 0) {
        $al->deleterecord($adid);
    }
    header("location: product-view.php");
    exit();
}
/*************************************************/

/******************** الموافقة على منتج *********************/
if (isset($_GET['action']) && $_GET['action'] == "appr" && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $al->approve_record($id);
    }
    header("location: product-view.php");
    exit();
}
/*************************************************/

/******************** رفض منتج *********************/
if (isset($_GET['action']) && $_GET['action'] == "disappr" && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id > 0) {
        $al->disapprove_record($id);
    }
    header("location: product-view.php");
    exit();
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $p->setlimit(10);
$al->setsql("SELECT * FROM products, product_category WHERE pd_subcat_id = pc_id ORDER BY pd_date DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $p->setstart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "product-view.php";
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
    header("location: product-view.php");
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
                        <a href="product-view.php">Manage Products</a>
                    </li>
                    <li class="active">Product View</li>
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
                                <span style="float: left; margin-top: 5px;"><?php echo htmlspecialchars($showitems); ?></span>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center"><label><input type="checkbox" class="ace" id="check_all"><span class="lbl"></span></label></th>
                                            <th align="center"><strong>Date</strong></th>
                                            <th><strong>Image</strong></th>
                                            <th><strong>Title</strong></th>
                                            <th><strong>Category</strong></th>
                                            <th><strong>Price</strong></th>
                                            <th>&nbsp;</th>
                                            <th style="text-align:center"><strong>Status</strong></th>
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
                                                        <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->pd_id; ?>" />
                                                        <span class="lbl"></span>
                                                    </label>
                                                </td>
                                                <td><?php echo !empty($row->pd_date) ? date('d M, y', strtotime($row->pd_date)) : ''; ?></td>
                                                <td>
                                                    <?php 
                                                    $image = (!empty($row->pd_image)) ? $row->pd_image : 'noimage.jpg';
                                                    $image_path = "../upload/myproduct/" . $image;
                                                    ?>
                                                    <img src="<?php echo $image_path; ?>" width="80px" height="auto" alt="Product" style="border: 1px solid #ddd; padding: 3px;" />
                                                </td>
                                                <td><?php echo htmlspecialchars(ucwords(stripslashes($row->pd_title ?? ''))); ?></td>
                                                <td><?php echo htmlspecialchars(ucwords(stripslashes($row->pc_name ?? ''))); ?></td>
                                                <td><?php 
                                                    $currency = get_product_detail((int)$row->pd_id, 'pd_currency');
                                                    echo htmlspecialchars($currency . " " . ($row->pd_fob_price ?? ''));
                                                ?></td>
                                                <td>
                                                    <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5((string)$row->pd_id); ?>" title="View Details">
                                                        <img src="images/details.png" alt="Details" />
                                                    </a>
                                                </td>
                                                <td class="center" style="text-align:center">
                                                    <?php if ($row->pd_status == '0'): ?>
                                                        <a href="<?php echo $al->approve((int)$row->pd_id); ?>" onclick="return confirm('هل أنت متأكد من الموافقة على هذا المنتج؟')" title="Approve">
                                                            <img alt="Approve" src="images/active.jpg">
                                                        </a>&nbsp;
                                                        <a href="<?php echo $al->disapprove((int)$row->pd_id); ?>" onclick="return confirm('هل أنت متأكد من رفض هذا المنتج؟')" title="Disapprove">
                                                            <img alt="Disapprove" src="images/reject.png" width="19" height="19" border="0">
                                                        </a>
                                                    <?php elseif ($row->pd_status == '1'): ?>
                                                        <span style="color:#009933; font-weight:800;">Approved</span>
                                                    <?php elseif ($row->pd_status == '2'): ?>
                                                        <span style="color:#CC0000; font-weight:800;">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="action" style="text-align:center">
                                                    <a href="product-edit.php?fid=<?php echo (int)$row->pd_id; ?>" title="Edit">
                                                        <img src="images/edit.jpg" alt="Edit" />
                                                    </a>
                                                    <a href="<?php echo $al->deletelink((int)$row->pd_id); ?>" onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')" title="Delete">
                                                        <img src="images/delete.jpg" alt="Delete" />
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php 
                                                $j++;
                                            endwhile;
                                        else:
                                        ?>
                                            <tr>
                                                <td colspan="9" align="center" style="padding: 20px; color: #F00;">
                                                    لا توجد منتجات
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
            { "bSortable": false },
            null,
            null,
            null,
            { "bSortable": false },
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