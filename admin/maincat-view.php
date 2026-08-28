<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من تسجيل دخول المشرف (بدلاً من check_user_login)
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/SimpleImage.php";
require_once "../lib/pagination.php";
// التحقق من اتصال قاعدة البيانات
global $con;
if (!isset($con) || !($con instanceof mysqli)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

/**
 * Class ProductList - إدارة قائمة التصنيفات الرئيسية
 * متوافق مع PHP 8.3
 */
class ProductList {
    public string $sqlList = "";
    public int $start = 0;
    public int $limit = 20;
    public $con;
    
    public function __construct() {
        global $con;
        $this->con = $con;
    }
    
    public function setsql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    public function totalrecord(): int {
        $result = mysqli_query($this->con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    public function listview() {
        return mysqli_query($this->con, $this->sqlList);
    }
    
    public function updateOrder(int $id, int $order): bool {
        $sql = "UPDATE product_category SET pc_order = ? WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ii", $order, $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
    
    public function deleteRecord(int $id): bool {
        $sql = "UPDATE product_category SET pc_status = 0 WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $result = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        return false;
    }
}

// تهيئة Pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();
$limit = $pagination->getLimit(20);

// تهيئة الكلاس
$productList = new ProductList();

// معالجة الحذف
if (isset($_GET['action']) && $_GET['action'] === 'del' && isset($_GET['id'])) {
    $productList->deleteRecord((int)$_GET['id']);
    header("Location: maincat-view.php");
    exit;
}

// معالجة تحديث الترتيب
if (isset($_POST['update_order']) && isset($_POST['order']) && is_array($_POST['order'])) {
    foreach ($_POST['order'] as $id => $order) {
        $productList->updateOrder((int)$id, (int)$order);
    }
    header("Location: maincat-view.php");
    exit;
}

// استعلام البيانات
$productList->setsql("SELECT * FROM product_category WHERE pc_parent_id = 0 AND pc_status = 1 ORDER BY pc_order, pc_name");
$totalitems = $productList->totalrecord();
$productList->start = $pagination->getStart($page, $limit, $totalitems);

$query = "SELECT * FROM product_category WHERE pc_parent_id = 0 AND pc_status = 1 ORDER BY pc_order, pc_name LIMIT {$productList->start}, {$limit}";
$productList->setsql($query);
$records = $productList->listview();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// حساب نطاق العرض
$displayStart = $productList->start + 1;
$displayEnd = min($productList->start + $limit, $totalitems);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalitems . " items";
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
                    <li class="active">Main Categories</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <a href="maincat-add.php" class="btn btn-xs btn-primary">
                                    <i class="icon-plus bigger-120"></i> Add New
                                </a>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="maincat-table" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Category Name</th>
                                            <th>Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                          <?php while ($row = mysqli_fetch_assoc($records)): ?>
    <tr>
        <td><?php echo (int)$row['pc_id']; ?></td>
        <td>
            <?php 
            $imagePath = '';
            if (!empty($row['pc_image'])) {
                $imagePath = '../upload/category/' . htmlspecialchars($row['pc_image']);
            } else {
                $imagePath = '../upload/category/default.jpg';
            }
            ?>
            <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['pc_name'] ?? ''); ?>" style="max-width: 50px; max-height: 50px; border: 1px solid #ddd; padding: 2px;">
        </td>
        <td><?php echo htmlspecialchars($row['pc_name'] ?? ''); ?></td>
        <td><?php echo (int)($row['pc_order'] ?? 0); ?></td>
        <td><?php echo ($row['pc_status'] == 1) ? 'Active' : 'Inactive'; ?></td>
        <td>
            <a href="maincat-edit.php?id=<?php echo (int)$row['pc_id']; ?>" class="btn btn-xs btn-info">
                <i class="icon-edit bigger-120"></i> Edit
            </a>
            <a href="?action=del&id=<?php echo (int)$row['pc_id']; ?>" class="btn btn-xs btn-danger" onclick="return confirm('Delete this category?')">
                <i class="icon-trash bigger-120"></i> Delete
            </a>
        </td>
    </tr>
<?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No records found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        <?php echo $displayRange; ?>
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
                    
                    <!-- Pagination Info -->
<div class="row">
    <div class="col-xs-6">
        <div class="dataTables_info">
            Showing <?php echo ($productList->start + 1) . ' to ' . min($productList->start + $limit, $totalitems) . ' of ' . $totalitems . ' entries'; ?>
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
                    
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>

<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'></script>");
</script>
<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'></script>");
</script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

</body>
</html>
<?php ob_end_flush(); ?>