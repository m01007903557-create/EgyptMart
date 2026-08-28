<?php
/**
 * File: advcathome-view.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: عرض وإدارة إعلانات التصنيفات في الصفحة الرئيسية
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

// ===== كود تغيير الحالة =====
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] == 'change_status') {
    $stat = isset($_POST['stat']) ? (int)$_POST['stat'] : -1;
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    
    if ($id > 0 && ($stat == 0 || $stat == 1)) {
        $sql = "UPDATE advertisementcathome SET adv_status = $stat WHERE adv_id = $id";
        if (mysqli_query($con, $sql)) {
            echo '1';
        } else {
            echo '0';
        }
    } else {
        echo '0';
    }
    exit;
}
// ===== نهاية كود تغيير الحالة =====

/**
 * Class AdvList - إدارة إعلانات التصنيفات
 */
class AdvList {
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
    
    public function deleterecord(int $adid): void {
        global $con;
        
        $chquesql = "SELECT adv_img FROM advertisementcathome WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $chquesql);
        mysqli_stmt_bind_param($stmt, "i", $adid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row && !empty($row['adv_img'])) {
            $path = "../upload/advertisementcathome/" . $row['adv_img'];
            if (is_file($path)) {
                unlink($path);
            }
        }
        
        $sql_del = "DELETE FROM advertisementcathome WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql_del);
        mysqli_stmt_bind_param($stmt, "i", $adid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    
    public function deletelink(int $id): string {
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&fid=" . $id
            : "advcathome-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        return $dellink;
    }
}

// تهيئة Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

$al = new AdvList();

/******************** حذف سجل *********************/
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['fid'])) {
    $al->deleterecord((int)$_GET['fid']);
    header("Location: advcathome-view.php");
    exit;
}
/*************************************************/

// إعدادات الصفحة
$al->limit = $pagination->getLimit(20);
$al->setsql("SELECT * FROM advertisementcathome ORDER BY adv_id DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);

$query = "SELECT * FROM advertisementcathome ORDER BY adv_id DESC LIMIT {$al->start}, {$limit}";
$al->setsql($query);
$recObj = $al->listview();

$showitems = ($al->start + 1) . " - ";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " of " . $totalitems . " items";

if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("Location: advcathome-view.php");
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>

<style>
.table-header { margin-bottom: 10px; }
.dataTables_filter { float: right; margin-bottom: 10px; }
.dataTables_length { float: left; margin-bottom: 10px; }
.dataTables_info { float: left; margin-top: 10px; }
.dataTables_paginate { float: right; margin-top: 10px; }
.table img { max-width: 100px; max-height: 80px; }
.table td, .table th { vertical-align: middle; }
</style>

<div class="main-container" id="main-container">
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php"; ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li><i class="icon-home home-icon"></i><a href="welcome.php">Home</a></li>
                    <li class="active">Category Advertisements</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('Are you sure?')">
                                    <i class="icon-trash bigger-120"></i> Delete
                                </button>
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='advcathome-add.php'">
                                    <i class="icon-plus bigger-120"></i> Add New
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="adv-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                         <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                                         <th>Image</th>
                                         <th>Category Info</th>
                                         <th>Link</th>
                                         <th>Position</th>
                                         <th>Status</th>
                                         <th>Change Status</th>
                                         <th>Action</th>
                                    </thead>
                                    <tbody>
                                        <?php if ($recObj && mysqli_num_rows($recObj) > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($recObj)): 
                                                // جلب اسم التصنيف
                                                $cat_name = '';
                                                if (!empty($row->adv_cat_id)) {
                                                    $cat_result = mysqli_query($con, "SELECT pc_name FROM product_category WHERE pc_id = " . (int)$row->adv_cat_id);
                                                    if ($cat_result && $cat_row = mysqli_fetch_assoc($cat_result)) {
                                                        $cat_name = $cat_row['pc_name'];
                                                    }
                                                }
                                                if (!empty($row->adv_subcat_id)) {
                                                    $sub_result = mysqli_query($con, "SELECT pc_name FROM product_category WHERE pc_id = " . (int)$row->adv_subcat_id);
                                                    if ($sub_result && $sub_row = mysqli_fetch_assoc($sub_result)) {
                                                        $cat_name .= ' / ' . $sub_row['pc_name'];
                                                    }
                                                }
                                                if (!empty($row->adv_subsub_cat_id)) {
                                                    $subsub_result = mysqli_query($con, "SELECT pc_name FROM product_category WHERE pc_id = " . (int)$row->adv_subsub_cat_id);
                                                    if ($subsub_result && $subsub_row = mysqli_fetch_assoc($subsub_result)) {
                                                        $cat_name .= ' / ' . $subsub_row['pc_name'];
                                                    }
                                                }
                                            ?>
                                            <tr id="row_<?php echo $row->adv_id; ?>">
                                                <td class="center">
                                                    <input name="cb[]" class="ace" type="checkbox" value="<?php echo $row->adv_id; ?>">
                                                    <span class="lbl"></span>
                                                </td>
                                                <td class="center">
                                                    <?php if (!empty($row->adv_img)): ?>
                                                        <img src="../upload/advertisementcathome/<?php echo htmlspecialchars($row->adv_img); ?>" style="max-width:100px; max-height:80px;">
                                                    <?php else: ?>
                                                        <span class="text-muted">No image</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <strong><?php echo htmlspecialchars($cat_name ?: 'All Categories'); ?></strong>
                                                    <?php if (!empty($row->adv_supplier_id)): ?>
                                                        <br><small>Supplier ID: <?php echo $row->adv_supplier_id; ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <a href="<?php echo htmlspecialchars($row->adv_link); ?>" target="_blank">
                                                        <?php echo substr($row->adv_link, 0, 40); ?>
                                                    </a>
                                                </td>
                                                <td class="center"><?php echo htmlspecialchars($row->adv_position ?? 'Home'); ?></td>
                                                <td class="center">
                                                    <?php if ($row->adv_status == 1): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <select onchange="changeStatus(this.value, '<?php echo $row->adv_id; ?>')">
                                                        <option value="">Select</option>
                                                        <option value="1" <?php echo ($row->adv_status == 1) ? 'selected' : ''; ?>>Activate</option>
                                                        <option value="0" <?php echo ($row->adv_status == 0) ? 'selected' : ''; ?>>Deactivate</option>
                                                    </select>
                                                </td>
                                                <td class="center">
                                                    <a href="advcathome-edit.php?aid=<?php echo $row->adv_id; ?>" class="btn btn-xs btn-info">Edit</a>
                                                    <a href="<?php echo $al->deletelink((int)$row->adv_id); ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete?')">Delete</a>
                                                </td>
                                              </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No records found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">Showing <?php echo $showitems; ?></div>
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
                                                <li class="<?php echo ($i == $page) ? 'active' : ''; ?>"><a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
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
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<script>
$(document).ready(function() {
    $('#adv-table').DataTable({
        "pageLength": 20,
        "language": {
            "search": "Search:",
            "lengthMenu": "Display _MENU_ records",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
    
    $('#selectAll').on('click', function() {
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
            .each(function() {
                this.checked = that.checked;
                $(this).closest('tr').toggleClass('selected');
            });
    });
});

function changeStatus(stat, id) {
    if (stat != '') {
        $.post(window.location.href, {ajax_action: 'change_status', stat: stat, id: id}, function(data) {
            if (data.trim() == '1') location.reload();
        });
    }
}
</script>

</body>
</html>
<?php ob_end_flush(); ?>