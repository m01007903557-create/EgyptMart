<?php
declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";
require_once  "../lib/pagination.php";

// Check if user is logged in
check_admin_login();


/**
 * Class SupportCategoryList
 * 
 * Handles support category listing operations
 */
class SupportCategoryList {
    
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
     * Delete category and associated FAQs
     * 
     * @param int $id Category ID
     * @return bool Success status
     */
    public function deleteCategory(int $id): bool {
        // First, check if category has FAQs
        $checkSql = "SELECT COUNT(*) as count FROM custom_faq WHERE cf_fc_id = ?";
        $checkStmt = mysqli_prepare($this->db, $checkSql);
        
        $faqCount = 0;
        if ($checkStmt) {
            mysqli_stmt_bind_param($checkStmt, "i", $id);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);
            $row = mysqli_fetch_assoc($result);
            $faqCount = (int)($row['count'] ?? 0);
            mysqli_stmt_close($checkStmt);
        }
        
        // Delete FAQs first (if any)
        if ($faqCount > 0) {
            $deleteFaqSql = "DELETE FROM custom_faq WHERE cf_fc_id = ?";
            $deleteFaqStmt = mysqli_prepare($this->db, $deleteFaqSql);
            
            if ($deleteFaqStmt) {
                mysqli_stmt_bind_param($deleteFaqStmt, "i", $id);
                mysqli_stmt_execute($deleteFaqStmt);
                mysqli_stmt_close($deleteFaqStmt);
            }
        }
        
        // Delete category
        $sql = "DELETE FROM faq_categories WHERE fc_id = ?";
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
     * @param int $id Category ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$id}";
        }
        
        return "support-category-list.php?{$queryString}&action=del&ad-id={$id}";
    }
    
    /**
     * Count FAQs in category
     * 
     * @param int $categoryId Category ID
     * @return int Number of FAQs
     */
    public function countFaqs(int $categoryId): int {
        $sql = "SELECT COUNT(*) as count FROM custom_faq WHERE cf_fc_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return (int)($row['count'] ?? 0);
    }
    
    /**
     * Get category details
     * 
     * @param int $id Category ID
     * @return array|null Category details
     */
    public function getCategoryDetails(int $id): ?array {
        $sql = "SELECT * FROM faq_categories WHERE fc_id = ?";
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
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize category list
$categoryList = new SupportCategoryList($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $categoryList->deleteCategory((int)$_GET['ad-id']);
    header("Location: support-category-list.php");
    exit;
}

// Set pagination limits
$categoryList->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Build base query
$baseQuery = "SELECT * FROM faq_categories ORDER BY fc_id ASC";

// Get total records for pagination
$categoryList->setSql($baseQuery);
$totalRecords = $categoryList->getTotalRecords();

// Set pagination start
$categoryList->start = $pagination->getStart($currentPage, $categoryList->limit, $totalRecords);

// Get records for current page (without limit in this query - will be used with pagination later)
$records = $categoryList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $categoryList->start + 1;
$displayEnd = min($categoryList->start + $categoryList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "support-category-list.php";
$pageString = "?limit=" . $categoryList->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    $errors = [];
    
    foreach ($_POST['cb'] as $id) {
        $faqCount = $categoryList->countFaqs((int)$id);
        if ($faqCount > 0) {
            $details = $categoryList->getCategoryDetails((int)$id);
            $errors[] = "Category '{$details['fc_name']}' has {$faqCount} FAQ(s) - deleted with FAQs";
        }
        if ($categoryList->deleteCategory((int)$id)) {
            $deleted++;
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['success_msg'] = "{$deleted} categor" . ($deleted > 1 ? 'ies' : 'y') . " deleted successfully.";
    }
    if (!empty($errors)) {
        $_SESSION['warning_msg'] = implode("<br>", $errors);
    }
    
    header("Location: support-category-list.php");
    exit;
}

// Get session messages
$success_msg = $_SESSION['success_msg'] ?? '';
$warning_msg = $_SESSION['warning_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['warning_msg']);
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
                    <li class="active">Support Categories</li>
                </ul>
            </div>
            
            <div class="page-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($warning_msg)): ?>
                    <div class="alert alert-warning">
                        <i class="icon-warning-sign"></i> <?php echo $warning_msg; ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <h1>
                        Support Categories
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Manage FAQ categories
                        </small>
                    </h1>
                </div>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected categories? This will also delete all FAQs in these categories.')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                                <button type="button" class="btn btn-xs btn-success" 
                                        onClick="window.location='support-category-add.php'">
                                    <i class="icon-plus-sign bigger-120"></i> Add New Category
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
                                            <th><strong>Category Name</strong></th>
                                            <th class="center"><strong>FAQs Count</strong></th>
                                            <th class="center"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php 
                                            mysqli_data_seek($records, 0);
                                            $counter = 0;
                                            while ($row = mysqli_fetch_object($records)): 
                                                if ($counter >= $categoryList->start && $counter < $categoryList->start + $categoryList->limit):
                                                    $faqCount = $categoryList->countFaqs((int)$row->fc_id);
                                            ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->fc_id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars(ucwords($row->fc_name ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    </td>
                                                    <td class="center">
                                                        <?php if ($faqCount > 0): ?>
                                                            <a href="support_change.php?cat=<?php echo (int)$row->fc_id; ?>" 
                                                               class="badge badge-info" title="View FAQs">
                                                                <?php echo $faqCount; ?> FAQ<?php echo $faqCount > 1 ? 's' : ''; ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="badge badge-default">0 FAQs</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="center">
                                                        <div class="btn-group">
                                                            <a href="support-category-edit.php?token=<?php echo rand(1000, 9999) . md5((string)$row->fc_id); ?>" 
                                                               class="btn btn-xs btn-info" title="Edit Category">
                                                                <i class="icon-edit bigger-120"></i>
                                                            </a>
                                                            <a href="support_change.php?cat=<?php echo (int)$row->fc_id; ?>" 
                                                               class="btn btn-xs btn-success" title="Manage FAQs">
                                                                <i class="icon-question-sign bigger-120"></i>
                                                            </a>
                                                            <a href="<?php echo $categoryList->getDeleteLink((int)$row->fc_id); ?>" 
                                                               class="btn btn-xs btn-danger" title="Delete Category"
                                                               onclick="return confirm('Are you sure you want to delete this category? This will also delete all FAQs in this category.')">
                                                                <i class="icon-trash bigger-120"></i>
                                                            </a>
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
                                                <td colspan="4" class="text-center">
                                                    No support categories found. 
                                                    <a href="support-category-add.php">Add your first category</a>.
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
    // تحويل القيم إلى int لتجنب أخطاء PHP 8.3
    $totalRecords = (int)$totalRecords;
    $limit = (int)$categoryList->limit;
    $currentPage = (int)$currentPage;
    
    $totalPages = $pagination->getTotalPages($totalRecords, $limit);
    
    if ($totalPages > 1) {
        echo '<div class="dataTables_paginate paging_bootstrap">';
        echo '<ul class="pagination">';
        
        // Previous button
        if ($currentPage > 1) {
            echo '<li class="prev"><a href="?page=' . ($currentPage - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
        } else {
            echo '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
        }
        
        // Page numbers
        for ($i = 1; $i <= $totalPages; $i++) {
            $activeClass = ($i == $currentPage) ? 'active' : '';
            echo '<li class="' . $activeClass . '"><a href="?page=' . $i . '">' . $i . '</a></li>';
        }
        
        // Next button
        if ($currentPage < $totalPages) {
            echo '<li class="next"><a href="?page=' . ($currentPage + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
        } else {
            echo '<li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>';
        }
        
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
                { "bSortable": false },
                { "bSortable": false }
            ],
            "bPaginate": false, // We're using custom pagination
            "bInfo": false, // We're using custom info
            "bFilter": true,
            "bSort": true
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search categories...');
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
    
    .badge {
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 11px;
        text-decoration: none;
    }
    
    .badge-info {
        background-color: #5bc0de;
        color: white;
    }
    
    .badge-info:hover {
        background-color: #31b0d5;
        color: white;
        text-decoration: none;
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
    
    .table-header button {
        margin-right: 5px;
    }
    
    .pagination {
        margin: 0;
        float: right;
    }
    
    .dataTables_info {
        padding-top: 8px;
        color: #666;
    }
    
    .alert {
        margin-bottom: 20px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>