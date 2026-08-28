<?php
/**
 * File: splan_icon_view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة أيقونات خطط العضوية
 * View and manage membership plan icons
 * 
 * Features:
 * - عرض جميع أيقونات الخطط النشطة
 * - إضافة أيقونات جديدة
 * - تعديل الأيقونات الموجودة
 * - حذف الأيقونات (تعطيل)
 * - حذف متعدد للأيقونات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";
require_once "../lib/pagination.php";


// Check if user is logged in
check_admin_login();

/**
 * Class MembershipIconManager
 * 
 * Handles membership plan icons operations
 */
class MembershipIconManager {
    
    /** @var string SQL query */
    private string $sqlList = '';
    
    /** @var int Start offset for pagination */
    public int $start = 0;
    
    /** @var int Limit per page */
    public int $limit = 20;
    
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
     * Calculate number of pages
     * 
     * @param int $rowsPerPage Rows per page
     * @return int Number of pages
     */
    public function getPageCount(int $rowsPerPage): int {
        $total = $this->getTotalRecords();
        return (int)ceil($total / $rowsPerPage);
    }
    
    /**
     * Delete (deactivate) membership icon
     * 
     * @param int $id Icon ID
     * @return bool Success status
     */
    public function deleteIcon(int $id): bool {
        $sql = "UPDATE smembership_icon_plan SET mp_status = 0 WHERE mp_id = ?";
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
     * @param int $id Icon ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "splan_icon-view.php?{$queryString}&action=del&fid={$id}";
    }
    
    /**
     * Get icon name by ID
     * 
     * @param int $id Icon ID
     * @return string|null Icon name
     */
    public function getIconName(int $id): ?string {
        $sql = "SELECT mst_name FROM smembership_icon_plan WHERE mp_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['mst_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get total active icons count
     * 
     * @return int Total active icons
     */
    public function getTotalActiveCount(): int {
        $sql = "SELECT COUNT(*) as count FROM smembership_icon_plan WHERE mp_status = 1";
        $result = mysqli_query($this->db, $sql);
        
        if (!$result) {
            return 0;
        }
        
        $row = mysqli_fetch_assoc($result);
        return (int)($row['count'] ?? 0);
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize icon manager
$iconManager = new MembershipIconManager($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $iconId = (int)$_GET['fid'];
    $iconManager->deleteIcon($iconId);
    header("Location: splan_icon-view.php");
    exit;
}

// Set pagination limits
$iconManager->limit = $pagination->getLimit(20);

// Build base query for active icons
$baseQuery = "SELECT * FROM smembership_icon_plan WHERE mp_status = 1 ORDER BY mp_id DESC";

// Get total records for pagination
$iconManager->setSql($baseQuery);
$totalRecords = $iconManager->getTotalRecords();

// Set pagination start
$iconManager->start = $pagination->getStart($currentPage, $iconManager->limit, $totalRecords);

// Get records for current page with pagination
$paginatedQuery = $baseQuery . " LIMIT {$iconManager->start}, {$iconManager->limit}";
$iconManager->setSql($paginatedQuery);
$records = $iconManager->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $iconManager->start + 1;
$displayEnd = min($iconManager->start + $iconManager->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $iconManager->deleteIcon((int)$id);
    }
    header("Location: splan_icon-view.php");
    exit;
}

// Get total active count for display
$totalActive = $iconManager->getTotalActiveCount();
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
                        <a href="#">Manage Membership Plan</a>
                    </li>
                    <li class="active">Plan Icons</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Membership Plan Icons
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Total Active: <?php echo $totalActive; ?>
                        </small>
                    </h1>
                </div>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected records?')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                                <button type="button" class="btn btn-xs btn-success" 
                                        onclick="window.location='splan_icon-add.php'">
                                    <i class="icon-plus-sign bigger-120"></i> Add New Icon
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
                                            <th><strong>Plan Name / Icon</strong></th>
                                            <th><strong>Preview</strong></th>
                                            <th class="center"><strong>Status</strong></th>
                                            <th class="center"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($records)): ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" 
                                                                   value="<?php echo (int)$row->mp_id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($row->mst_name ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <?php if (!empty($row->mp_description)): ?>
                                                            <br>
                                                            <small><?php echo htmlspecialchars($row->mp_description ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="center">
                                                        <?php if (!empty($row->mp_icon)): ?>
                                                            <img src="../images/membership/<?php echo htmlspecialchars($row->mp_icon, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                 style="max-width:50px; max-height:50px;" 
                                                                 alt="<?php echo htmlspecialchars($row->mst_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php else: ?>
                                                            <span class="label label-default">No Icon</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="center">
                                                        <?php if ($row->mp_status == 1): ?>
                                                            <span class="label label-success">Active</span>
                                                        <?php else: ?>
                                                            <span class="label label-default">Inactive</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="center">
                                                        <div class="btn-group">
                                                            <a href="splan_icon-edit.php?fid=<?php echo (int)$row->mp_id; ?>" 
                                                               class="btn btn-xs btn-info" title="Edit">
                                                                <i class="icon-edit bigger-120"></i>
                                                            </a>
                                                            <a href="<?php echo $iconManager->getDeleteLink((int)$row->mp_id); ?>" 
                                                               class="btn btn-xs btn-danger" title="Delete"
                                                               onclick="return confirm('Are you sure you want to delete this plan icon?')">
                                                                <i class="icon-trash bigger-120"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    No membership plan icons found. 
                                                    <a href="splan_icon-add.php">Add your first icon</a>.
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
                                    $totalPages = ceil($totalRecords / $iconManager->limit);
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
        
        // Add hover effect to table rows
        $('#sample-table-2 tbody tr').hover(
            function() {
                $(this).addClass('hover');
            },
            function() {
                $(this).removeClass('hover');
            }
        );
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
    
    .table td {
        vertical-align: middle;
    }
    
    .table tbody tr.hover {
        background-color: #f5f5f5;
        cursor: pointer;
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
</style>

</body>
</html>

<?php ob_end_flush(); ?>