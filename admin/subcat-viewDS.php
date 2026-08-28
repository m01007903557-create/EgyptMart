<?php
/**
 * File: subcat-view.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة التصنيفات الفرعية
 * View and manage subcategories
 * 
 * Features:
 * - عرض جميع التصنيفات الفرعية مع التصنيف الرئيسي والوسيط
 * - حذف التصنيفات الفرعية
 * - تعديل التصنيفات الفرعية
 * - ترقيم الصفحات
 * - بحث متقدم
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";
require_once "../lib/pagination.php";

$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Check if user is logged in
checkUserLogin();

/**
 * Class SubCategoryList
 * 
 * Handles subcategory listing operations
 */
class SubCategoryList {
    
    /** @var string SQL query */
    private string $sqlList = '';
    
    /** @var int Start offset for pagination */
    public int $start = 0;
    
    /** @var int Limit per page */
    public int $limit = 10;
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Set SQL query
     * 
     * @param string $sql SQL query
     */
    public function setSql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    /**
     * Get total records count
     * 
     * @return int Total records
     */
    public function getTotalRecords(): int {
        $result = mysqli_query($this->db, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * Get records for current page
     * 
     * @return mysqli_result|false Query result
     */
    public function getRecords() {
        return mysqli_query($this->db, $this->sqlList);
    }
    
    /**
     * Delete subcategory
     * 
     * @param int $id Subcategory ID
     * @return bool Success status
     */
    public function deleteSubcategory(int $id): bool {
        // Check if subcategory has products
        $checkSql = "SELECT COUNT(*) as count FROM products WHERE pd_subcat_id = ?";
        $checkStmt = mysqli_prepare($this->db, $checkSql);
        
        if ($checkStmt) {
            mysqli_stmt_bind_param($checkStmt, "i", $id);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($checkStmt);
            
            if (($row['count'] ?? 0) > 0) {
                return false; // Cannot delete subcategory with products
            }
        }
        
        $sql = "DELETE FROM product_category WHERE pc_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Build delete link
     * 
     * @param int $id Subcategory ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$id}";
        }
        
        return "subcat-view.php?{$queryString}&action=del&ad-id={$id}";
    }
    
    /**
     * Get subcategory details by ID
     * 
     * @param int $id Subcategory ID
     * @return array|null Subcategory details
     */
    public function getSubcategoryDetails(int $id): ?array {
        $sql = "SELECT 
                    s.pc_id, 
                    s.pc_name, 
                    s.pc_sort_name,
                    c.pc_id as cat_id,
                    c.pc_name as cat_name,
                    m.pc_id as maincat_id,
                    m.pc_name as maincat_name
                FROM product_category s
                JOIN product_category c ON s.pc_parent_id = c.pc_id
                JOIN product_category m ON c.pc_parent_id = m.pc_id
                WHERE s.pc_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Count products in subcategory
     * 
     * @param int $subcatId Subcategory ID
     * @return int Number of products
     */
    public function countProducts(int $subcatId): int {
        $sql = "SELECT COUNT(*) as count FROM products WHERE pd_subcat_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $subcatId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize subcategory list
$subcatList = new SubCategoryList($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $subcatId = (int)$_GET['ad-id'];
    
    // Check if subcategory has products before deleting
    $productCount = $subcatList->countProducts($subcatId);
    
    if ($productCount > 0) {
        // Set error message in session
        $_SESSION['error_msg'] = "Cannot delete subcategory because it has {$productCount} product(s) associated with it.";
    } else {
        $subcatList->deleteSubcategory($subcatId);
    }
    
    header("Location: subcat-view.php");
    exit;
}

// Set pagination limits
$subcatList->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Build base query
$baseQuery = "SELECT 
                s.pc_id, 
                s.pc_name, 
                s.pc_sort_name, 
                c.pc_name as cat_name, 
                m.pc_name as maincat_name 
              FROM product_category s
              JOIN product_category c ON s.pc_parent_id = c.pc_id
              JOIN product_category m ON c.pc_parent_id = m.pc_id
              WHERE m.pc_parent_id = '0' 
                AND m.pc_status = '1' 
                AND c.pc_status = '1' 
                AND s.pc_status = '1' 
              ORDER BY s.pc_id DESC";

// Get total records for pagination
$subcatList->setSql($baseQuery);
$totalRecords = $subcatList->getTotalRecords();

// Set pagination start
$subcatList->start = $pagination->getStart($currentPage, $subcatList->limit, $totalRecords);

// Get records for current page (without limit in this query - will be used with pagination later)
$records = $subcatList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $subcatList->start + 1;
$displayEnd = min($subcatList->start + $subcatList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "subcat-view.php";
$pageString = "?limit=" . $subcatList->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $errors = [];
    $deleted = 0;
    
    foreach ($_POST['cb'] as $id) {
        $productCount = $subcatList->countProducts((int)$id);
        if ($productCount > 0) {
            $details = $subcatList->getSubcategoryDetails((int)$id);
            $errors[] = "Subcategory '{$details['pc_name']}' has {$productCount} product(s)";
        } else {
            if ($subcatList->deleteSubcategory((int)$id)) {
                $deleted++;
            }
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['success_msg'] = "{$deleted} subcategor" . ($deleted > 1 ? 'ies' : 'y') . " deleted successfully.";
    }
    if (!empty($errors)) {
        $_SESSION['error_msg'] = implode("<br>", $errors);
    }
    
    header("Location: subcat-view.php");
    exit;
}

// Get session messages
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
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
                        <a href="#">Manage Categories</a>
                    </li>
                    <li class="active">Subcategories</li>
                </ul>
            </div>
            
            <div class="page-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error_msg)): ?>
                    <div class="alert alert-danger">
                        <i class="icon-remove"></i> <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <h1>
                        Subcategories
                        <small>
                            <i class="icon-double-angle-right"></i>
                            View all subcategories
                        </small>
                    </h1>
                </div>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <p style="display: inline-block; float: right;">
                                    Go to Page No : 
                                    <input type="number" name="page_no" id="page_no" class="page_no" min="1" style="width:60px;" />
                                </p>
                                
                                <button type="button" class="btn btn-xs btn-success" onClick="window.location='subcat-add.php'">
                                    <i class="icon-plus-sign bigger-120"></i> Add Subcategory
                                </button>
                                
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected records? This will fail for subcategories with products.')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label>
                                                    <input type="checkbox" class="ace" id="selectAll">
                                                    <span class="lbl"></span>
                                                </label>
                                            </th>
                                            <th><strong>Subcategory Name</strong></th>
                                            <th><strong>Sort Name</strong></th>
                                            <th><strong>Category</strong></th>
                                            <th><strong>Main Category</strong></th>
                                            <th class="center"><strong>Products</strong></th>
                                            <th class="center"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php 
                                            // Reset pointer and fetch records with pagination
                                            mysqli_data_seek($records, 0);
                                            $counter = 0;
                                            while ($row = mysqli_fetch_row($records)): 
                                                if ($counter >= $subcatList->start && $counter < $subcatList->start + $subcatList->limit):
                                                    $productCount = $subcatList->countProducts((int)$row[0]);
                                            ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row[0]; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($row[1] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row[2] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row[3] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars($row[4] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                    </td>
                                                    <td class="center">
                                                        <?php if ($productCount > 0): ?>
                                                            <span class="badge badge-info"><?php echo $productCount; ?></span>
                                                        <?php else: ?>
                                                            <span class="badge badge-default">0</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="center">
                                                        <div class="btn-group">
                                                            <a href="subcat-edit.php?token=<?php echo rand(1000, 9999) . md5((string)$row[0]); ?>" 
                                                               class="btn btn-xs btn-info" title="Edit">
                                                                <i class="icon-edit bigger-120"></i>
                                                            </a>
                                                            <?php if ($productCount == 0): ?>
                                                                <a href="<?php echo $subcatList->getDeleteLink((int)$row[0]); ?>" 
                                                                   class="btn btn-xs btn-danger" title="Delete"
                                                                   onclick="return confirm('Are you sure you want to delete this subcategory?')">
                                                                    <i class="icon-trash bigger-120"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-xs btn-grey disabled" 
                                                                        title="Cannot delete - has <?php echo $productCount; ?> product(s)">
                                                                    <i class="icon-trash bigger-120"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endif;
                                                $counter++;
                                            endwhile; 
                                            ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    No subcategories found. 
                                                    <a href="subcat-add.php">Add your first subcategory</a>.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $displayRange; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    // Generate pagination links
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                   
                                        
                                        echo '</ul>';
                                        echo '</div>';
                                    }
                                    ?>
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

<!-- DataTables -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": false },
                null,
                null,
                null,
                null,
                { "bSortable": false },
                { "bSortable": false }
            ],
            "bPaginate": false, // We're using custom pagination
            "bInfo": false, // We're using custom info
            "bFilter": true,
            "bSort": true
        });
        
        // Page number input handler
        $("#page_no").on('keyup', function() {
            var pageVal = $(this).val();
            if (pageVal !== '') {
                oTable1.fnPageChange(parseInt(pageVal) - 1);
            } else {
                oTable1.fnPageChange(0);
            }
        });
        
        // Select all checkbox functionality
        $('#selectAll').on('click', function() {
            var that = this;
            $(this).closest('table').find('tr > td:first-child input:checkbox')
                .each(function() {
                    this.checked = that.checked;
                    $(this).closest('tr').toggleClass('selected');
                });
        });
        
        // Tooltip placement
        $('[data-rel="tooltip"]').tooltip({
            placement: function(context, source) {
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
            }
        });
        
        // Search enhancement
        $('#sample-table-2_filter input').attr('placeholder', 'Search subcategories...');
    });
</script>

<style>
    .btn-group {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    
    .btn-group .btn {
        border-radius: 3px !important;
        padding: 2px 8px;
    }
    
    .btn-grey {
        background-color: #aaa;
        color: white;
        border: none;
        cursor: not-allowed;
    }
    
    .btn-grey:hover {
        background-color: #999;
    }
    
    .badge {
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
    
    .badge-info {
        background-color: #5bc0de;
        color: white;
    }
    
    .badge-default {
        background-color: #777;
        color: white;
    }
    
    .table-header {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fa;
        border: 1px solid #e0e5ec;
        border-radius: 4px;
    }
    
    .page_no {
        width: 60px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 3px;
    }
    
    .alert {
        margin-bottom: 20px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>