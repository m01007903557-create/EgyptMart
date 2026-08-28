<?php
/**
 * File: yahooslider-view.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: عرض وإدارة شرائح السلايدر
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

// كلاس إدارة السلايدر
class SliderList {
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
        $sql = "DELETE FROM yahoo_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $adid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    public function updatestatus(int $adid, int $status): void {
        global $con;
        $sql = "UPDATE yahoo_slider SET adv_status = ? WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $status, $adid);
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

$al = new SliderList();

// معالجة الحذف
if (isset($_GET['action']) && $_GET['action'] == "del" && isset($_GET['ad-id'])) {
    $al->deleterecord((int)$_GET['ad-id']);
    header("Location: yahooslider-view.php");
    exit;
}

// معالجة تغيير الحالة
if (isset($_GET['action']) && $_GET['action'] == "status" && isset($_GET['id']) && isset($_GET['status'])) {
    $al->updatestatus((int)$_GET['id'], (int)$_GET['status']);
    header("Location: yahooslider-view.php");
    exit;
}

// إعدادات الصفحة
$al->limit = $pagination->getLimit(10);
$al->setsql("SELECT * FROM yahoo_slider ORDER BY adv_id DESC");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);

// استعلام مع LIMIT
$query = "SELECT * FROM yahoo_slider ORDER BY adv_id DESC LIMIT {$al->start}, {$limit}";
$al->setsql($query);
$recObj = $al->listview();

// حذف متعدد
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("Location: yahooslider-view.php");
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>

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
                    <li><a href="yahooslider-view.php">Yahoo Slider</a></li>
                    <li class="active">View Slider</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" onclick="return confirm('Are you sure?')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                                <button type="button" class="btn btn-xs btn-success" onclick="window.location='yahooslider-add.php'">
                                    <i class="icon-plus bigger-120"></i> Add New
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="slider-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                         <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                                         <th><strong>Image</strong></th>
                                         <th><strong>Info</strong></th>
                                         <th><strong>Width & Height</strong></th>
                                         <th><strong>Status</strong></th>
                                         <th><strong>Change Status</strong></th>
                                         <th><strong>Action</strong></th>
                                    </thead>
                                    <tbody>
                                        <?php if ($recObj && mysqli_num_rows($recObj) > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($recObj)): ?>
                                            <tr id="row_<?php echo (int)$row->adv_id; ?>">
                                                <td class="center">
                                                    <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->adv_id; ?>">
                                                    <span class="lbl"></span>
                                                 </td>
                                                 <td>
                                                    <img src="../upload/yahoo_slider/<?php echo htmlspecialchars($row->adv_img ?? ''); ?>" style="width: 80px; height: 60px; object-fit: cover;">
                                                 </td>
                                                 <td>
                                                    <strong><?php echo htmlspecialchars($row->adv_title ?? ''); ?></strong><br>
                                                    <small><?php echo htmlspecialchars(substr($row->adv_description ?? '', 0, 100)); ?></small>
                                                 </td>
                                                 <td><?php echo htmlspecialchars($row->adv_imagewidth ?? ''); ?> x <?php echo htmlspecialchars($row->adv_imageheight ?? ''); ?></td>
                                                 <td>
                                                    <?php if ($row->adv_status == 1): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php endif; ?>
                                                 </td>
                                                 <td>
                                                    <?php if ($row->adv_status == 1): ?>
                                                        <a href="?action=status&id=<?php echo (int)$row->adv_id; ?>&status=0" class="btn btn-xs btn-warning">Deactivate</a>
                                                    <?php else: ?>
                                                        <a href="?action=status&id=<?php echo (int)$row->adv_id; ?>&status=1" class="btn btn-xs btn-success">Activate</a>
                                                    <?php endif; ?>
                                                 </td>
                                                 <td class="action" style="text-align:center;">
                                                    <a href="yahooslider-edit.php?id=<?php echo (int)$row->adv_id; ?>" title="Edit">
                                                        <img alt="edit" src="images/edit.jpg">
                                                    </a>
                                                    <a href="<?php echo $al->deletelink((int)$row->adv_id); ?>" title="delete" onclick="return confirm('Are you sure?')">
                                                        <img alt="delete" src="images/delete.jpg" border="0">
                                                    </a>
                                                 </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr><td colspan="7" class="text-center">No records found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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
<script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    $('#slider-table').DataTable({
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "order": [[1, 'desc']],
        "language": {
            "search": "Search:",
            "lengthMenu": "Display _MENU_ records",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
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
</script>

<style>
.table-responsive {
    overflow-x: auto;
}
.table {
    min-width: 700px;
}
.table img {
    max-width: 80px;
    height: auto;
}
</style>

</body>
</html>
<?php ob_end_flush(); ?>