<?php
/**
 * File: testi-view.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: عرض وإدارة شهادات التقدير (Testimonials)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * Class productlist - إدارة شهادات التقدير
 */
class productlist {
    public string $sqlList = "";
    public int $start = 0;
    public int $limit = 20;
    
    public function setsql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    public function totalrecord(): int {
        global $con;
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    public function listview() {
        global $con;
        return mysqli_query($con, $this->sqlList);
    }
    
    public function numpage(int $rowPage): int {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
    public function deleterecord(int $adid): void {
        global $con;
        $sql = "DELETE FROM testimonials WHERE testi_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $adid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    public function deletelink(int $id): string {
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&ad-id=" . $id
            : $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $id;
        return $dellink;
    }
}

// تهيئة Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

$al = new productlist();

/******************** حذف سجل *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['ad-id'])) {
    $al->deleterecord((int)$_GET['ad-id']);
    header("Location: testi-view.php");
    exit;
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $pagination->getLimit(10);
$al->setsql("SELECT * FROM testimonials WHERE testi_status = '1' ORDER BY testi_id ASC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "testi-view.php";
$pagestring = "?limit=" . $limit . "&page=";

// إضافة LIMIT إلى الاستعلام
$query = "SELECT * FROM testimonials WHERE testi_status = '1' ORDER BY testi_id ASC LIMIT {$al->start}, {$limit}";
$al->setsql($query);
$recObj = $al->listview();

// حساب نطاق العرض
$showitems = ($al->start + 1) . "-";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " of " . $totalitems . " items";

// حذف متعدد
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("Location: testi-view.php");
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php"; ?>
        
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
                        <a>Manage Testimonials</a>
                    </li>
                    <li class="active">View Testimonials</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post"> 
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('Are you sure to delete the record?')">
                                    <i class="icon-trash bigger-120"></i> Delete
                                </button>
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='testi-add.php'">
                                    <i class="icon-pencil align-top bigger-120"></i> Add Testimonial
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                         <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                                         <th><strong>Date</strong></th>
                                         <th><strong>Image</strong></th>
                                         <th><strong>Type</strong></th>
                                         <th><strong>By</strong></th>
                                         <th><strong>Company</strong></th>
                                         <th><strong>Email</strong></th>
                                         <th><strong>Mobile</strong></th>
                                         <th><strong>Country</strong></th>
                                         <th><strong>Action</strong></th>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $count = $recObj ? mysqli_num_rows($recObj) : 0;
                                        if ($count > 0):
                                            while ($row = mysqli_fetch_object($recObj)): 
                                        ?>
                                         <tr>
                                            <td class="center">
                                                <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->testi_id; ?>">
                                                <span class="lbl"></span>
                                            </td>
                                            <td><?php echo date('d M, y', strtotime($row->testi_updated_date ?? 'now')); ?></td>
                                            <td><img src="../upload/testimonial_img/<?php echo htmlspecialchars($row->testi_image ?? ''); ?>" width="90" height="90" alt="Testimonial Image"></td>
                                            <td><?php echo htmlspecialchars(ucfirst($row->testi_type ?? '')); ?></td>
                                            <td><?php echo htmlspecialchars($row->testi_name ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row->testi_business_name ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row->testi_email ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row->testi_mobile ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars(get_country_name((int)($row->testi_cn_id ?? 0))); ?></td>
                                            <td class="action" style="text-align:center;">
                                                <a href="testi-edit.php?token=<?php echo rand(1000, 9999) . md5((string)$row->testi_id); ?>" title="Edit">
                                                    <img alt="edit" src="images/edit.jpg">
                                                </a>
                                                <a href="<?php echo $al->deletelink((int)$row->testi_id); ?>" title="delete" onclick="return confirm('Are you sure to delete the record?')">
                                                    <img alt="delete" src="images/delete.jpg" border="0">
                                                </a>
                                            </td>
                                         </tr>
                                        <?php 
                                            endwhile;
                                        endif; 
                                        ?>
                                    </tbody>
                                 </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $showitems; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    $totalPages = ceil($totalitems / $limit);
                                    if ($totalPages > 1):
                                    ?>
                                    <div class="dataTables_paginate paging_bootstrap">
                                        <ul class="pagination">
                                            <?php if ($page > 1): ?>
                                                <li class="prev"><a href="?page=<?php echo ($page - 1); ?>"><i class="icon-double-angle-left"></i></a></li>
                                            <?php else: ?>
                                                <li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                <li class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($page < $totalPages): ?>
                                                <li class="next"><a href="?page=<?php echo ($page + 1); ?>"><i class="icon-double-angle-right"></i></a></li>
                                            <?php else: ?>
                                                <li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <?php endif; ?>
                                </div>
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

<?php include "includes/footer.php"; ?>

<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'></script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'></script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        // تهيئة DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": false },
                null, null, null, null, null, null, null, null,
                { "bSortable": false }
            ],
            "bPaginate": false,
            "bInfo": false
        });
        
        // تحديد الكل
        $('#selectAll').on('click', function() {
            var that = this;
            $(this).closest('table').find('tr > td:first-child input:checkbox')
                .each(function() {
                    this.checked = that.checked;
                    $(this).closest('tr').toggleClass('selected');
                });
        });
        
        // أداة المساعدة
        $('[data-rel="tooltip"]').tooltip({
            placement: function(context, source) {
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
    });
</script>

</body>
</html>
<?php ob_end_flush(); ?>