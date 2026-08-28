<?php
/**
 * File: quotation_list.php
 * Version: 2.0.0
 * PHP Version: 8.3
 * 
 * Description: إدارة طلبات عروض الأسعار - عرض، بحث، تصفية، حذف
 * Quotation Requests Management - View, search, filter, delete
 * 
 * Features:
 * - عرض جميع طلبات عروض الأسعار
 * - بحث في الاسم والبريد الإلكتروني
 * - تصفية حسب التاريخ
 * - عرض تفاصيل الطلب
 * - حذف فردي وجماعي
 * - تصفح الصفحات
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
 * Class QuotationManager
 * 
 * Handles quotation requests management
 */
class QuotationManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Base table name */
    private string $table = 'quotation_request';
    
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
     * Get quotation requests with filtering and pagination
     * 
     * @param array $filters Filter criteria
     * @return mysqli_result|false Query result
     */
    public function getQuotations(array $filters = []) {
        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = "";
        
        // Search by name or email
        if (!empty($filters['search'])) {
            $sql .= " AND (qr_name LIKE ? OR qr_email LIKE ? OR qr_contactnumber LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        // Filter by date range
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(qr_updated_date) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(qr_updated_date) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
        }
        
        // Order by date (newest first)
        $sql .= " ORDER BY qr_updated_date DESC";
        
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
     * Get total count of filtered quotations
     * 
     * @param array $filters Filter criteria
     * @return int Total count
     */
    public function getTotalCount(array $filters = []): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];
        $types = "";
        
        // Apply same filters as getQuotations
        if (!empty($filters['search'])) {
            $sql .= " AND (qr_name LIKE ? OR qr_email LIKE ? OR qr_contactnumber LIKE ?)";
            $searchTerm = "%" . $filters['search'] . "%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "sss";
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(qr_updated_date) >= ?";
            $params[] = $filters['date_from'];
            $types .= "s";
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(qr_updated_date) <= ?";
            $params[] = $filters['date_to'];
            $types .= "s";
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
     * Get single quotation by ID
     * 
     * @param int $id Quotation ID
     * @return array|null Quotation data or null if not found
     */
    public function getQuotationById(int $id): ?array {
        if ($id <= 0) {
            $this->errors[] = "Invalid quotation ID";
            return null;
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE qr_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "Database error: " . mysqli_error($this->db);
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if (!$row) {
            $this->errors[] = "Quotation not found";
            return null;
        }
        
        return $row;
    }
    
    /**
     * Delete quotation request
     * 
     * @param int $id Quotation ID
     * @return bool Success status
     */
    public function deleteQuotation(int $id): bool {
        if ($id <= 0) {
            $this->errors[] = "Invalid quotation ID";
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE qr_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "Database error: " . mysqli_error($this->db);
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            $this->success[] = "Quotation deleted successfully";
            $this->logActivity('delete', $id);
        }
        
        return $result;
    }
    
    /**
     * Mark quotation as paid (if needed)
     * 
     * @param int $id Quotation ID
     * @return bool Success status
     */
    public function markAsPaid(int $id): bool {
        if ($id <= 0) {
            $this->errors[] = "Invalid quotation ID";
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET qr_paid = 1, qr_paid_date = NOW() WHERE qr_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "Database error: " . mysqli_error($this->db);
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            $this->success[] = "Quotation marked as paid";
            $this->logActivity('paid', $id);
        }
        
        return $result;
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param int $itemId Item ID
     */
    private function logActivity(string $action, int $itemId): void {
        $userId = $_SESSION['admin_id'] ?? 0;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $sql = "INSERT INTO activity_log (user_id, action, item_type, item_id, ip_address, created_at) 
                VALUES (?, ?, 'quotation', ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isis", $userId, $action, $itemId, $ipAddress);
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
     * Sanitize output string
     * 
     * @param string $str Input string
     * @return string Sanitized string
     */
    public function sanitize(string $str): string {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
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
 * Handles pagination calculations and display
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
            $pagination .= '<li class="prev"><a href="' . $targetpage . $pagestring . $prev . '"><i class="icon-double-angle-right"></i> السابق</a></li>';
        } else {
            $pagination .= '<li class="prev disabled"><a href="#"><i class="icon-double-angle-right"></i> السابق</a></li>';
        }
        
        // Page numbers
        $start_page = max(1, $page - $adjacents);
        $end_page = min($totalpages, $page + $adjacents);
        
        if ($start_page > 1) {
            $pagination .= '<li><a href="' . $targetpage . $pagestring . '1">1</a></li>';
            if ($start_page > 2) {
                $pagination .= '<li class="disabled"><a href="#">...</a></li>';
            }
        }
        
        for ($i = $start_page; $i <= $end_page; $i++) {
            if ($i == $page) {
                $pagination .= '<li class="active"><a href="#">' . $i . '</a></li>';
            } else {
                $pagination .= '<li><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
            }
        }
        
        if ($end_page < $totalpages) {
            if ($end_page < $totalpages - 1) {
                $pagination .= '<li class="disabled"><a href="#">...</a></li>';
            }
            $pagination .= '<li><a href="' . $targetpage . $pagestring . $totalpages . '">' . $totalpages . '</a></li>';
        }
        
        // Next link
        if ($page < $totalpages) {
            $pagination .= '<li class="next"><a href="' . $targetpage . $pagestring . $next . '">التالي <i class="icon-double-angle-left"></i></a></li>';
        } else {
            $pagination .= '<li class="next disabled"><a href="#">التالي <i class="icon-double-angle-left"></i></a></li>';
        }
        
        $pagination .= '</ul></div>';
        
        return $pagination;
    }
}

// Initialize classes
$pagination = new Pagination();
$manager = new QuotationManager($con);

// Get filter parameters
$filters = [
    'search' => trim($_GET['search'] ?? ''),
    'date_from' => $_GET['date_from'] ?? '',
    'date_to' => $_GET['date_to'] ?? ''
];

// Handle single delete
if (isset($_GET['action']) && $_GET['action'] === 'del' && isset($_GET['clid'])) {
    $id = filter_input(INPUT_GET, 'clid', FILTER_VALIDATE_INT);
    if ($id) {
        $manager->deleteQuotation($id);
        
        if (!empty($manager->getErrors())) {
            $_SESSION['quotation_errors'] = $manager->getErrors();
        }
        if (!empty($manager->getSuccess())) {
            $_SESSION['quotation_success'] = $manager->getSuccess();
        }
    }
    header("Location: quotation_list.php" . buildQueryString($filters));
    exit();
}

// Handle paid status (if needed)
if (isset($_GET['action']) && $_GET['action'] === 'paid' && isset($_GET['did'])) {
    $id = filter_input(INPUT_GET, 'did', FILTER_VALIDATE_INT);
    if ($id) {
        $manager->markAsPaid($id);
        
        if (!empty($manager->getErrors())) {
            $_SESSION['quotation_errors'] = $manager->getErrors();
        }
        if (!empty($manager->getSuccess())) {
            $_SESSION['quotation_success'] = $manager->getSuccess();
        }
    }
    header("Location: quotation_list.php" . buildQueryString($filters));
    exit();
}

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    foreach ($_POST['cb'] as $id) {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id && $manager->deleteQuotation($id)) {
            $deleted++;
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['quotation_success'] = ["تم حذف {$deleted} طلب/طلبات بنجاح"];
    }
    
    header("Location: quotation_list.php" . buildQueryString($filters));
    exit();
}

// Helper function to build query string
function buildQueryString(array $filters): string {
    $params = [];
    if (!empty($filters['search'])) $params[] = 'search=' . urlencode($filters['search']);
    if (!empty($filters['date_from'])) $params[] = 'date_from=' . urlencode($filters['date_from']);
    if (!empty($filters['date_to'])) $params[] = 'date_to=' . urlencode($filters['date_to']);
    
    return !empty($params) ? '?' . implode('&', $params) : '';
}

// Setup pagination
$page = $pagination->setpage();
$limit = $pagination->setlimit(10);
$totalitems = $manager->getTotalCount($filters);
$start = $pagination->setstart($page, $limit, $totalitems);
$manager->setPagination($start, $limit);

// Get quotations
$quotations = $manager->getQuotations($filters);

// Calculate display range
$from = $totalitems > 0 ? $start + 1 : 0;
$to = min($start + $limit, $totalitems);
$showitems = "{$from} - {$to} من إجمالي {$totalitems} طلب";

// Get messages from session
$errorMessages = $_SESSION['quotation_errors'] ?? [];
$successMessages = $_SESSION['quotation_success'] ?? [];
unset($_SESSION['quotation_errors'], $_SESSION['quotation_success']);

// Get pagination string
$adjacents = 2;
$targetpage = "quotation_list.php";
$pagestring = "?limit=" . $limit . "&page=";
if (!empty($filters['search'])) $pagestring .= '&search=' . urlencode($filters['search']);
if (!empty($filters['date_from'])) $pagestring .= '&date_from=' . urlencode($filters['date_from']);
if (!empty($filters['date_to'])) $pagestring .= '&date_to=' . urlencode($filters['date_to']);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>لوحة التحكم - إدارة طلبات عروض الأسعار</title>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<script src="js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>

<style>
    /* Custom styles */
    .alert {
        padding: 12px 20px;
        margin: 15px 0;
        border-radius: 4px;
        text-align: right;
    }
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .alert-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }
    .filter-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    .grid-view {
        direction: rtl;
        text-align: right;
    }
    .items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
    }
    .items th {
        background-color: #f2f2f2;
        padding: 10px;
        border: 1px solid #ddd;
        text-align: center;
    }
    .items td {
        padding: 8px;
        border: 1px solid #ddd;
        vertical-align: middle;
    }
    .items tr:hover {
        background-color: #f5f5f5;
    }
    .checkbox {
        text-align: center;
        width: 40px;
    }
    .delete-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 15px;
        border-radius: 3px;
        cursor: pointer;
        font-size: 14px;
    }
    .delete-btn:hover {
        background-color: #c82333;
    }
    .view-link {
        display: inline-block;
        padding: 3px 10px;
        background-color: #17a2b8;
        color: white;
        text-decoration: none;
        border-radius: 3px;
        font-size: 12px;
    }
    .view-link:hover {
        background-color: #138496;
        text-decoration: none;
        color: white;
    }
    .pagination-area {
        text-align: center;
        margin-top: 20px;
    }
    .pagination {
        display: inline-block;
        padding: 0;
        margin: 10px 0;
    }
    .pagination li {
        display: inline;
    }
    .pagination li a {
        color: #333;
        float: left;
        padding: 8px 16px;
        text-decoration: none;
        border: 1px solid #ddd;
        margin: 0 2px;
    }
    .pagination li.active a {
        background-color: #4CAF50;
        color: white;
        border: 1px solid #4CAF50;
    }
    .pagination li.disabled a {
        color: #ccc;
        pointer-events: none;
    }
    .summary {
        color: #666;
        font-size: 13px;
        margin-right: 10px;
    }
    .form select {
        padding: 3px 8px;
        border-radius: 3px;
        border: 1px solid #ddd;
    }
    .table-header {
        margin-bottom: 15px;
    }
    .search-input {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        width: 200px;
    }
    .date-input {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 3px;
        width: 120px;
    }
    .btn {
        padding: 5px 15px;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        font-size: 13px;
    }
    .btn-info {
        background-color: #17a2b8;
        color: white;
    }
    .btn-default {
        background-color: #6c757d;
        color: white;
    }
    .btn:hover {
        opacity: 0.8;
    }
</style>

<script type="text/javascript">
// Function to select/deselect all checkboxes
function checkedAll() {
    var checkboxes = document.getElementsByName('cb[]');
    var checkAll = document.getElementById('check_all');
    
    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = checkAll.checked;
    }
}

// Function to confirm bulk delete
function confirmBulkDelete() {
    return confirm('هل أنت متأكد من حذف الطلبات المحددة؟');
}

$(document).ready(function() {
    // Update limit on change
    $('#limit').on('change', function() {
        var baseUrl = 'quotation_list.php?page=<?php echo $page; ?>&limit=' + this.value;
        <?php if (!empty($filters['search'])): ?>
            baseUrl += '&search=<?php echo urlencode($filters['search']); ?>';
        <?php endif; ?>
        <?php if (!empty($filters['date_from'])): ?>
            baseUrl += '&date_from=<?php echo urlencode($filters['date_from']); ?>';
        <?php endif; ?>
        <?php if (!empty($filters['date_to'])): ?>
            baseUrl += '&date_to=<?php echo urlencode($filters['date_to']); ?>';
        <?php endif; ?>
        window.location.href = baseUrl;
    });
    
    // Live search with debounce
    var searchTimer;
    $('#search-input').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function() {
            $('#filter-form').submit();
        }, 500);
    });
    
    // Date validation
    $('#date_from, #date_to').on('change', function() {
        var dateFrom = $('#date_from').val();
        var dateTo = $('#date_to').val();
        
        if (dateFrom && dateTo && dateFrom > dateTo) {
            alert('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
            $(this).val('');
        }
    });
    
    // Tooltips
    $('[title]').tooltip();
});
</script>
</head>

<body>
<div class="main">
    <?php include "includes/admin-top.php" ?>
    
    <div class="control_Panel">
        <?php include "includes/admin-left-con.php" ?>
        
        <div id="content-container">
            <div id="content">
                
                <!-- Page Header -->
                <h2>
                    <i class="icon-file-text"></i> 
                    إدارة عروض الأسعار › قائمة الطلبات
                </h2>
                
                <!-- Display messages -->
                <?php if (!empty($errorMessages)): ?>
                    <div class="alert alert-danger">
                        <i class="icon-remove"></i>
                        <ul style="margin:5px 0 0 20px;">
                            <?php foreach ($errorMessages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($successMessages)): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i>
                        <ul style="margin:5px 0 0 20px;">
                            <?php foreach ($successMessages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="get" id="filter-form" class="form-inline">
                        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                            <div>
                                <label>بحث:</label>
                                <input type="text" name="search" id="search-input" 
                                       class="search-input" 
                                       value="<?php echo htmlspecialchars($filters['search']); ?>" 
                                       placeholder="اسم - بريد - هاتف">
                            </div>
                            <div>
                                <label>من تاريخ:</label>
                                <input type="date" name="date_from" id="date_from" 
                                       class="date-input" 
                                       value="<?php echo htmlspecialchars($filters['date_from']); ?>">
                            </div>
                            <div>
                                <label>إلى تاريخ:</label>
                                <input type="date" name="date_to" id="date_to" 
                                       class="date-input" 
                                       value="<?php echo htmlspecialchars($filters['date_to']); ?>">
                            </div>
                            <div>
                                <button type="submit" class="btn btn-info">
                                    <i class="icon-filter"></i> بحث
                                </button>
                                <a href="quotation_list.php" class="btn btn-default">
                                    <i class="icon-refresh"></i> إعادة ضبط
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Main Form -->
                <form name="myform" id="myform" method="post" onsubmit="return confirmBulkDelete();">
                    
                    <div id="whatsNew-grid" class="grid-view">
                        
                        <!-- Toolbar -->
                        <table style="width: 100%; margin-bottom: 15px;">
                            <tr>
                                <td style="width: 100px;">
                                    <button type="submit" name="btnDelete" class="delete-btn">
                                        <i class="icon-trash"></i> حذف
                                    </button>
                                </td>
                                <td>
                                    <span class="summary"><?php echo $showitems; ?></span>
                                </td>
                                <td align="left">
                                    <div class="summary">
                                        <span>عرض:</span>
                                        <select name="limit" id="limit" style="margin: 0 5px;">
                                            <?php for ($i = 10; $i <= 40; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo $i == $limit ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <span>نتيجة في الصفحة</span>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Data Table -->
                        <table class="items">
                            <thead>
                                <tr>
                                    <th class="checkbox">
                                        <input name="check_all" value="yes" id="check_all" type="checkbox" onClick="checkedAll();">
                                    </th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>رقم الاتصال</th>
                                    <th>التفاصيل</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $j = 0;
                                if ($quotations && mysqli_num_rows($quotations) > 0):
                                    while ($row = mysqli_fetch_object($quotations)):       
                                ?>
                                    <tr class="<?php echo ($j % 2 == 1) ? 'row-clr' : ''; ?>">
                                        <td class="checkbox">
                                            <input name="cb[]" type="checkbox" value="<?php echo (int)$row->qr_id; ?>">
                                        </td>
                                        <td style="text-align:center;">
                                            <?php echo htmlspecialchars(ucwords($row->qr_name ?? '')); ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <a href="mailto:<?php echo htmlspecialchars($row->qr_email ?? ''); ?>">
                                                <?php echo htmlspecialchars($row->qr_email ?? ''); ?>
                                            </a>
                                        </td>
                                        <td style="text-align:center;">
                                            <?php echo htmlspecialchars($row->qr_contactnumber ?? ''); ?>
                                        </td>
                                        <td style="text-align:center;">
                                            <a href="quotation_details.php?token=<?php echo rand(1000, 9999) . md5((string)$row->qr_id); ?>" 
                                               class="view-link">
                                                <i class="icon-search"></i> عرض
                                            </a>
                                        </td>
                                        <td style="text-align:center;">
                                            <i class="icon-calendar"></i>
                                            <?php echo date('Y-m-d', strtotime($row->qr_updated_date)); ?>
                                        </td>
                                        <td class="action" style="text-align:center;">
                                            <a href="<?php echo $al->deletelink($row->qr_id); ?>" 
                                               title="حذف" 
                                               onclick="return confirm('هل أنت متأكد من حذف هذا الطلب؟')"
                                               style="margin:0 5px;">
                                                <img src="images/delete.jpg" alt="حذف" border="0">
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $j++;
                                    endwhile; 
                                else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:30px;">
                                            <div class="alert alert-warning" style="margin:0;">
                                                <i class="icon-info-sign"></i> 
                                                <?php if (!empty(array_filter($filters))): ?>
                                                    لا توجد طلبات تطابق معايير البحث
                                                <?php else: ?>
                                                    لا توجد طلبات عروض أسعار حتى الآن
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <?php if ($totalitems > $limit): ?>
                            <div class="pager">
                                <?php echo $pagination->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring); ?>
                            </div>
                        <?php endif; ?>
                        
                    </div>  
                    
                    <br clear="all"/>
                </form>
                
            </div>
        </div>
    </div>
    
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- Activity Log Table Creation if needed -->
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
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        KEY `user_id` (`user_id`),
        KEY `item_type` (`item_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $createTable);
}
?>

<!-- نهاية ملف quotation_list.php - الإصدار 2.0.0 -->
</body>
</html>

<?php ob_end_flush(); ?>