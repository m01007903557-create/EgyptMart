<?php
/**
 * File: product-view2.php

 * Version: 2.0.0
 * PHP Version: 8.3
 * 
 * Description: إدارة وعرض المنتجات - عرض، تصفية، تعديل حالة، حذف
 * Product management - View, filter, status update, delete
 * 
 * Features:
 * - عرض جميع المنتجات مع صورها
 * - ترشيح حسب الحالة (Approved/Rejected/Pending)
 * - ترشيح حسب المنتجات المميزة (Leading Products)
 * - بحث في عناوين المنتجات
 * - تغيير حالة المنتج (Approve/Reject)
 * - إضافة/إزالة من المنتجات المميزة
 * - حذف فردي وجماعي
 * - عرض تفاصيل المنتج
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// Check if user is logged in
check_user_login();

/**
 * Class ProductManager
 * 
 * Handles product management operations
 */
class ProductManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Base SQL query */
    private string $baseSql = "SELECT p.*, pc.pc_name 
                               FROM products p 
                               JOIN product_category pc ON p.pd_subcat_id = pc.pc_id";
    
    /** @var int Start offset for pagination */
    private int $start = 0;
    
    /** @var int Items per page */
    private int $limit = 10;
    
    /** @var array Error messages */
    private array $errors = [];
    
    /** @var array Success messages */
    private array $success = [];
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Get products with filtering and pagination
     * 
     * @param array $filters Filter criteria
     * @return mysqli_result|false Query result
     */
    public function getProducts(array $filters = []) {
        $sql = $this->baseSql . " WHERE 1=1";
        $params = [];
        $types = "";
        
        // Filter by status
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'approved') {
                $sql .= " AND p.pd_status = 1";
            } elseif ($filters['status'] === 'rejected') {
                $sql .= " AND p.pd_status = 2";
            } elseif ($filters['status'] === 'pending') {
                $sql .= " AND p.pd_status = 0";
            }
        }
        
        // Filter by leading product
        if (isset($filters['leading']) && $filters['leading'] !== '') {
            $sql .= " AND p.leadingprod_status = ?";
            $params[] = (int)$filters['leading'];
            $types .= "i";
        }
        
        // Search by title
        if (!empty($filters['search'])) {
            $sql .= " AND p.pd_title LIKE ?";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $types .= "s";
        }
        
        // Filter by category
        if (!empty($filters['category'])) {
            $sql .= " AND p.pd_subcat_id = ?";
            $params[] = (int)$filters['category'];
            $types .= "i";
        }
        
        // Order by date (newest first)
        $sql .= " ORDER BY p.pd_date DESC";
        
        // Add pagination
        if ($this->limit > 0) {
            $sql .= " LIMIT ?, ?";
            $params[] = $this->start;
            $params[] = $this->limit;
            $types .= "ii";
        }
        
        // Prepare and execute statement
        if (!empty($params)) {
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) {
                $this->errors[] = "Database error: " . mysqli_error($this->db);
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        }
        
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Get total count of filtered products
     * 
     * @param array $filters Filter criteria
     * @return int Total count
     */
    public function getTotalCount(array $filters = []): int {
        $sql = "SELECT COUNT(*) as total FROM products p WHERE 1=1";
        $params = [];
        $types = "";
        
        // Apply same filters as getProducts
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'approved') {
                $sql .= " AND p.pd_status = 1";
            } elseif ($filters['status'] === 'rejected') {
                $sql .= " AND p.pd_status = 2";
            } elseif ($filters['status'] === 'pending') {
                $sql .= " AND p.pd_status = 0";
            }
        }
        
        if (isset($filters['leading']) && $filters['leading'] !== '') {
            $sql .= " AND p.leadingprod_status = ?";
            $params[] = (int)$filters['leading'];
            $types .= "i";
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND p.pd_title LIKE ?";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $types .= "s";
        }
        
        if (!empty($filters['category'])) {
            $sql .= " AND p.pd_subcat_id = ?";
            $params[] = (int)$filters['category'];
            $types .= "i";
        }
        
        if (!empty($params)) {
            $stmt = mysqli_prepare($this->db, $sql);
            if (!$stmt) {
                return 0;
            }
            
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            
            return (int)($row['total'] ?? 0);
        }
        
        $result = mysqli_query($this->db, $sql);
        $row = mysqli_fetch_assoc($result);
        return (int)($row['total'] ?? 0);
    }
    
    /**
     * Delete a product
     * 
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function deleteProduct(int $productId): bool {
        if ($productId <= 0) {
            $this->errors[] = "Invalid product ID";
            return false;
        }
        
        // First, get product image to delete
        $imageSql = "SELECT pd_image FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $imageSql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        // Delete product from database
        $sql = "DELETE FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result && $product && !empty($product['pd_image'])) {
            // Delete image file
            $imagePath = "../upload/myproduct/" . $product['pd_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        if ($result) {
            $this->success[] = "Product deleted successfully";
            $this->logActivity('delete', $productId);
        }
        
        return $result;
    }
    
    /**
     * Update product status
     * 
     * @param int $productId Product ID
     * @param int $status New status (0=pending, 1=approved, 2=rejected)
     * @return bool Success status
     */
    public function updateStatus(int $productId, int $status): bool {
        if ($productId <= 0 || !in_array($status, [0, 1, 2])) {
            $this->errors[] = "Invalid parameters";
            return false;
        }
        
        $sql = "UPDATE products SET pd_status = ? WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $status, $productId);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            $statusText = $status == 1 ? 'approved' : ($status == 2 ? 'rejected' : 'pending');
            $this->success[] = "Product {$statusText} successfully";
            $this->logActivity('status_change', $productId, $statusText);
        }
        
        return $result;
    }
    
    /**
     * Toggle leading product status
     * 
     * @param int $productId Product ID
     * @return bool Success status
     */
    public function toggleLeadingStatus(int $productId): bool {
        if ($productId <= 0) {
            $this->errors[] = "Invalid product ID";
            return false;
        }
        
        // Get current status
        $sql = "SELECT leadingprod_status FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$product) {
            $this->errors[] = "Product not found";
            return false;
        }
        
        $newStatus = $product['leadingprod_status'] ? 0 : 1;
        
        $updateSql = "UPDATE products SET leadingprod_status = ? WHERE pd_id = ?";
        $updateStmt = mysqli_prepare($this->db, $updateSql);
        mysqli_stmt_bind_param($updateStmt, "ii", $newStatus, $productId);
        $updateResult = mysqli_stmt_execute($updateStmt);
        mysqli_stmt_close($updateStmt);
        
        if ($updateResult) {
            $action = $newStatus ? 'added to' : 'removed from';
            $this->success[] = "Product {$action} leading products";
            $this->logActivity('leading_toggle', $productId, $newStatus ? 'add' : 'remove');
        }
        
        return $updateResult;
    }
    
    /**
     * Get all categories for filter dropdown
     * 
     * @return mysqli_result|false
     */
    public function getCategories() {
        $sql = "SELECT pc_id, pc_name FROM product_category ORDER BY pc_name";
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param int $productId Product ID
     * @param string|null $details Additional details
     */
    private function logActivity(string $action, int $productId, ?string $details = null): void {
        $userId = $_SESSION['admin_id'] ?? 0;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $sql = "INSERT INTO activity_log (user_id, action, item_type, item_id, details, ip_address, created_at) 
                VALUES (?, ?, 'product', ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "issis", $userId, $action, $productId, $details, $ipAddress);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Set pagination parameters
     * 
     * @param int $start Start offset
     * @param int $limit Items per page
     */
    public function setPagination(int $start, int $limit): void {
        $this->start = max(0, $start);
        $this->limit = max(1, min(100, $limit));
    }
    
    /**
     * Get error messages
     * 
     * @return array Error messages
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Get success messages
     * 
     * @return array Success messages
     */
    public function getSuccess(): array {
        return $this->success;
    }
    
    /**
     * Clear messages
     */
    public function clearMessages(): void {
        $this->errors = [];
        $this->success = [];
    }
}

/**
 * Class Pagination
 * 
 * Handles pagination calculations
 */
class Pagination {
    
    /**
     * Get current page number
     * 
     * @return int Current page
     */
    public function setpage(): int {
        $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999]
        ]);
        return $page ?: 1;
    }
    
    /**
     * Calculate start offset
     * 
     * @param int $page Current page
     * @param int $limit Items per page
     * @param int $total Total items
     * @return int Start offset
     */
    public function setstart(int $page, int $limit, int $total): int {
        if ($total <= 0) return 0;
        $start = ($page - 1) * $limit;
        return max(0, min($start, $total - $limit));
    }
    
    /**
     * Get items per page
     * 
     * @param int $default Default value
     * @return int Items per page
     */
    public function setlimit(int $default = 10): int {
        $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 100]
        ]);
        return $limit ?: $default;
    }
    
    /**
     * Generate pagination HTML
     * 
     * @param int $page Current page
     * @param int $totalitems Total items
     * @param int $limit Items per page
     * @param int $adjacents Number of adjacent pages
     * @param string $targetpage Target page URL
     * @param string $pagestring URL parameters
     * @return string Pagination HTML
     */
    public function getPaginationString(int $page, int $totalitems, int $limit, int $adjacents, string $targetpage, string $pagestring): string {
        if ($totalitems <= $limit) {
            return '';
        }
        
        $prev = $page - 1;
        $next = $page + 1;
        $totalpages = ceil($totalitems / $limit);
        
        $pagination = '<div class="pagination-area"><ul class="pagination">';
        
        // Previous link
        if ($page > 1) {
            $pagination .= '<li class="prev"><a href="' . $targetpage . $pagestring . $prev . '"><i class="icon-double-angle-left"></i> Previous</a></li>';
        } else {
            $pagination .= '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i> Previous</a></li>';
        }
        
        // Page numbers
        if ($totalpages <= (2 * $adjacents + 1)) {
            for ($i = 1; $i <= $totalpages; $i++) {
                if ($i == $page) {
                    $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
                } else {
                    $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                }
            }
        } else {
            // First page
            if ($page > 1 + $adjacents) {
                $pagination .= '<li><a href="' . $targetpage . $pagestring . '1">1</a></li>';
                if ($page > 2 + $adjacents) {
                    $pagination .= '<li class="disabled"><a href="#">...</a></li>';
                }
            }
            
            // Middle pages
            $start = max(1, $page - $adjacents);
            $end = min($totalpages, $page + $adjacents);
            
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                    $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
                } else {
                    $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
                }
            }
            
            // Last page
            if ($page < $totalpages - $adjacents) {
                if ($page < $totalpages - $adjacents - 1) {
                    $pagination .= '<li class="disabled"><a href="#">...</a></li>';
                }
                $pagination .= '<li><a href="' . $targetpage . $pagestring . $totalpages . '">' . $totalpages . '</a></li>';
            }
        }
        
        // Next link
        if ($page < $totalpages) {
            $pagination .= '<li class="next"><a href="' . $targetpage . $pagestring . $next . '">Next <i class="icon-double-angle-right"></i></a></li>';
        } else {
            $pagination .= '<li class="next disabled"><a href="#">Next <i class="icon-double-angle-right"></i></a></li>';
        }
        
        $pagination .= '</ul></div>';
        
        return $pagination;
    }
}

// Initialize classes
$pagination = new Pagination();
$manager = new ProductManager($con);

// Get filter parameters
$filters = [
    'status' => $_GET['status'] ?? '',
    'leading' => isset($_GET['leading']) && $_GET['leading'] !== '' ? (int)$_GET['leading'] : '',
    'search' => trim($_GET['search'] ?? ''),
    'category' => filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: ''
];

// Handle single delete
if (isset($_GET['action']) && $_GET['action'] === 'del' && isset($_GET['ad-id'])) {
    $id = filter_input(INPUT_GET, 'ad-id', FILTER_VALIDATE_INT);
    if ($id) {
        $manager->deleteProduct($id);
        
        if (!empty($manager->getErrors())) {
            $_SESSION['product_errors'] = $manager->getErrors();
        }
        if (!empty($manager->getSuccess())) {
            $_SESSION['product_success'] = $manager->getSuccess();
        }
    }
    header("Location: product-view.php" . buildQueryString($filters));
    exit();
}

// Handle approve
if (isset($_GET['action']) && $_GET['action'] === 'appr' && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $manager->updateStatus($id, 1);
        
        if (!empty($manager->getErrors())) {
            $_SESSION['product_errors'] = $manager->getErrors();
        }
        if (!empty($manager->getSuccess())) {
            $_SESSION['product_success'] = $manager->getSuccess();
        }
    }
    header("Location: product-view.php" . buildQueryString($filters));
    exit();
}

// Handle disapprove
if (isset($_GET['action']) && $_GET['action'] === 'disappr' && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $manager->updateStatus($id, 2);
        
        if (!empty($manager->getErrors())) {
            $_SESSION['product_errors'] = $manager->getErrors();
        }
        if (!empty($manager->getSuccess())) {
            $_SESSION['product_success'] = $manager->getSuccess();
        }
    }
    header("Location: product-view.php" . buildQueryString($filters));
    exit();
}

// Handle leading product toggle
if (isset($_GET['action']) && $_GET['action'] === 'Leading' && isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        $manager->toggleLeadingStatus($id);
        
        if (!empty($manager->getErrors())) {
            $_SESSION['product_errors'] = $manager->getErrors();
        }
        if (!empty($manager->getSuccess())) {
            $_SESSION['product_success'] = $manager->getSuccess();
        }
    }
    header("Location: product-view.php" . buildQueryString($filters));
    exit();
}

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    foreach ($_POST['cb'] as $id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id && $manager->deleteProduct($id)) {
            $deleted++;
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['product_success'] = ["Successfully deleted {$deleted} product(s)"];
    }
    
    header("Location: product-view.php" . buildQueryString($filters));
    exit();
}

// Helper function to build query string
function buildQueryString(array $filters): string {
    $params = [];
    if (!empty($filters['status'])) $params[] = 'status=' . urlencode($filters['status']);
    if (isset($filters['leading']) && $filters['leading'] !== '') $params[] = 'leading=' . $filters['leading'];
    if (!empty($filters['search'])) $params[] = 'search=' . urlencode($filters['search']);
    if (!empty($filters['category'])) $params[] = 'category=' . $filters['category'];
    
    return !empty($params) ? '?' . implode('&', $params) : '';
}

// Setup pagination
$page = $pagination->setpage();
$limit = $pagination->setlimit(10);
$totalitems = $manager->getTotalCount($filters);
$start = $pagination->setstart($page, $limit, $totalitems);
$manager->setPagination($start, $limit);

// Get products
$products = $manager->getProducts($filters);

// Get categories for filter
$categories = $manager->getCategories();

// Calculate display range
$from = $totalitems > 0 ? $start + 1 : 0;
$to = min($start + $limit, $totalitems);
$showitems = "{$from} - {$to} of {$totalitems} items";

// Get messages from session
$errorMessages = $_SESSION['product_errors'] ?? [];
$successMessages = $_SESSION['product_success'] ?? [];
unset($_SESSION['product_errors'], $_SESSION['product_success']);
?>

<?php include "includes/admin-top.php" ?>

<style>
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .filter-section .form-group {
        margin-right: 10px;
    }
    .status-badge {
        padding: 5px 10px;
        border-radius: 3px;
        font-weight: bold;
        display: inline-block;
    }
    .status-approved {
        background-color: #dff0d8;
        color: #3c763d;
    }
    .status-rejected {
        background-color: #f2dede;
        color: #a94442;
    }
    .status-pending {
        background-color: #fcf8e3;
        color: #8a6d3b;
    }
    .leading-badge {
        background-color: #f0ad4e;
        color: white;
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 5px;
    }
    .product-image {
        width: 80px;
        height: auto;
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 2px;
    }
    .action-buttons {
        white-space: nowrap;
    }
    .action-buttons a {
        margin: 0 2px;
    }
    .pagination-area {
        text-align: center;
        margin-top: 20px;
    }
    .summary-info {
        color: #666;
        font-size: 12px;
        margin-top: 10px;
    }
    .clear-filters {
        margin-left: 10px;
    }
</style>

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
                    <li class="active">Manage Products</li>
                </ul>
            </div>
            
            <div class="page-content">
                
                <!-- Display messages -->
                <?php if (!empty($errorMessages)): ?>
                    <div class="alert alert-danger">
                        <i class="icon-remove"></i>
                        <ul style="margin:0; padding-right:20px;">
                            <?php foreach ($errorMessages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($successMessages)): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i>
                        <ul style="margin:0; padding-right:20px;">
                            <?php foreach ($successMessages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="get" class="form-inline">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status:</label>
                                    <select name="status" class="form-control">
                                        <option value="">All</option>
                                        <option value="pending" <?php echo $filters['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $filters['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $filters['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Leading:</label>
                                    <select name="leading" class="form-control">
                                        <option value="">All</option>
                                        <option value="1" <?php echo $filters['leading'] === 1 ? 'selected' : ''; ?>>Leading Products</option>
                                        <option value="0" <?php echo $filters['leading'] === 0 ? 'selected' : ''; ?>>Regular Products</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Category:</label>
                                    <select name="category" class="form-control">
                                        <option value="">All Categories</option>
                                        <?php if ($categories): ?>
                                            <?php mysqli_data_seek($categories, 0); ?>
                                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>
                                                <option value="<?php echo $cat['pc_id']; ?>" 
                                                    <?php echo $filters['category'] == $cat['pc_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($cat['pc_name']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Search:</label>
                                    <input type="text" name="search" class="form-control" 
                                           value="<?php echo htmlspecialchars($filters['search']); ?>" 
                                           placeholder="Search by title...">
                                </div>
                            </div>
                        </div>
                        <div class="row" style="margin-top:10px;">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-info">
                                    <i class="icon-filter"></i> Apply Filters
                                </button>
                                <a href="product-view.php" class="btn btn-default clear-filters">
                                    <i class="icon-refresh"></i> Clear Filters
                                </a>
                                <span class="summary-info pull-right">
                                    <i class="icon-info-sign"></i> <?php echo $showitems; ?>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Products Table -->
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected products?')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                                <a href="product-add.php" class="btn btn-xs btn-success" style="margin-left:10px;">
                                    <i class="icon-plus"></i> Add New Product
                                </a>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center" width="30">
                                                <label>
                                                    <input type="checkbox" class="ace" id="select-all">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th width="80">Date</th>
                                            <th width="100">Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th width="40">Details</th>
                                            <th width="120">Status</th>
                                            <th width="200">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($products && mysqli_num_rows($products) > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($products)): ?>
                                                <?php 
                                                $leadingStatus = $row->leadingprod_status ?? 0;
                                                $productStatus = $row->pd_status ?? 0;
                                                $statusClass = $productStatus == 1 ? 'approved' : ($productStatus == 2 ? 'rejected' : 'pending');
                                                $statusText = $productStatus == 1 ? 'Approved' : ($productStatus == 2 ? 'Rejected' : 'Pending');
                                                ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->pd_id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td nowrap><?php echo date('d M, y', strtotime($row->pd_date)); ?></td>
                                                    <td>
                                                        <img src="../upload/myproduct/<?php echo !empty($row->pd_image) ? htmlspecialchars($row->pd_image) : 'noimage.jpg'; ?>" 
                                                             class="product-image" alt="Product">
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars(ucwords(stripslashes($row->pd_title))); ?>
                                                        <?php if ($leadingStatus == 1): ?>
                                                            <span class="leading-badge">Leading</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars(ucwords(stripslashes($row->pc_name))); ?></td>
                                                    <td>
                                                        <?php 
                                                        $currency = get_product_detail($row->pd_id, 'pd_currency') ?? '$';
                                                        echo $currency . ' ' . number_format((float)($row->pd_fob_price ?? 0), 2); 
                                                        ?>
                                                    </td>
                                                    <td class="center">
                                                        <a href="product-details.php?token=<?php echo rand(1000, 9999) . md5((string)$row->pd_id); ?>" 
                                                           class="btn btn-xs btn-default" title="View Details">
                                                            <i class="icon-search"></i>
                                                        </a>
                                                    </td>
                                                    <td class="center">
                                                        <?php if ($productStatus == 0): ?>
                                                            <div class="btn-group">
                                                                <a href="?action=appr&id=<?php echo $row->pd_id; ?><?php echo !empty($filters) ? '&' . http_build_query($filters) : ''; ?>" 
                                                                   class="btn btn-xs btn-success" 
                                                                   onclick="return confirm('Approve this product?')"
                                                                   title="Approve">
                                                                    <i class="icon-ok"></i>
                                                                </a>
                                                                <a href="?action=disappr&id=<?php echo $row->pd_id; ?><?php echo !empty($filters) ? '&' . http_build_query($filters) : ''; ?>" 
                                                                   class="btn btn-xs btn-danger" 
                                                                   onclick="return confirm('Reject this product?')"
                                                                   title="Reject">
                                                                    <i class="icon-remove"></i>
                                                                </a>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="status-badge status-<?php echo $statusClass; ?>">
                                                                <?php echo $statusText; ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="action-buttons center">
                                                        <a href="?action=Leading&id=<?php echo $row->pd_id; ?>&leadingsts=<?php echo $leadingStatus ? 0 : 1; ?><?php echo !empty($filters) ? '&' . http_build_query($filters) : ''; ?>" 
                                                           class="btn btn-xs <?php echo $leadingStatus ? 'btn-warning' : 'btn-info'; ?>"
                                                           onclick="return confirm('<?php echo $leadingStatus ? 'Remove from' : 'Add to'; ?> leading products?')"
                                                           title="<?php echo $leadingStatus ? 'Remove from Leading' : 'Add to Leading'; ?>">
                                                            <i class="icon-<?php echo $leadingStatus ? 'star' : 'star-empty'; ?>"></i>
                                                        </a>
                                                        <a href="product-edit.php?fid=<?php echo $row->pd_id; ?>" 
                                                           class="btn btn-xs btn-info" title="Edit">
                                                            <i class="icon-edit"></i>
                                                        </a>
                                                        <a href="?action=del&ad-id=<?php echo $row->pd_id; ?><?php echo !empty($filters) ? '&' . http_build_query($filters) : ''; ?>" 
                                                           class="btn btn-xs btn-danger" 
                                                           onclick="return confirm('Are you sure you want to delete this product?')"
                                                           title="Delete">
                                                            <i class="icon-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i> 
                                                        <?php if (!empty(array_filter($filters))): ?>
                                                            No products found matching your criteria.
                                                        <?php else: ?>
                                                            No products found. <a href="product-add.php">Add your first product</a>.
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php 
                            $adjacents = 2;
                            $targetpage = "product-view.php";
                            $pagestring = "?limit=" . $limit . "&page=";
                            if (!empty($filters)) {
                                $pagestring .= '&' . http_build_query(array_filter($filters));
                            }
                            echo $pagination->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring);
                            ?>
                            
                        </div>
                    </div>
                </form>
                
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript includes -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        // Initialize DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": false },
                { "sType": "date" },
                { "bSortable": false },
                null,
                null,
                { "sType": "numeric" },
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": false }
            ],
            "language": {
                "search": "Search:",
                "info": "Showing _START_ to _END_ of _TOTAL_ products",
                "infoEmpty": "Showing 0 to 0 of 0 products",
                "infoFiltered": "(filtered from _MAX_ total products)",
                "emptyTable": "No products found",
                "zeroRecords": "No matching products found"
            },
            "paging": false, // Using custom pagination
            "info": false
        });
        
        // Select/Deselect all
        $('#select-all').on('click', function() {
            var that = this;
            $(this).closest('table').find('tr > td:first-child input:checkbox')
                .each(function() {
                    this.checked = that.checked;
                    $(this).closest('tr').toggleClass('selected');
                });
        });
        
        // Tooltips
        $('[data-rel="tooltip"]').tooltip({placement: function(context, source) {
            var $source = $(source);
            var $parent = $source.closest('table');
            var off1 = $parent.offset();
            var w1 = $parent.width();
            var off2 = $source.offset();
            var w2 = $source.width();
            
            if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) {
                return 'right';
            }
            return 'left';
        }});
        
        // AJAX filter form submission
        $('.filter-section form').on('submit', function(e) {
            e.preventDefault();
            window.location.href = 'product-view.php?' + $(this).serialize();
        });
        
        // Highlight rows on checkbox select
        $('table tr input:checkbox').on('change', function() {
            $(this).closest('tr').toggleClass('selected', this.checked);
        });
    });
</script>

<!-- Database schema for activity_log if not exists -->
<?php
// Create activity_log table if it doesn't exist
$checkTable = mysqli_query($con, "SHOW TABLES LIKE 'activity_log'");
if (mysqli_num_rows($checkTable) == 0) {
    $createTable = "CREATE TABLE IF NOT EXISTS `activity_log` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(50) NOT NULL,
        `item_type` varchar(50) NOT NULL,
        `item_id` int(11) DEFAULT NULL,
        `details` varchar(255) DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        KEY `user_id` (`user_id`),
        KEY `item_type` (`item_type`),
        KEY `created_at` (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $createTable);
}
?>

</body>
</html>

<?php ob_end_flush(); ?>