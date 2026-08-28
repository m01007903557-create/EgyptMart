<?php
/**
 * File: supp-view.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة شعارات الموردين
 * View and manage supplier logos
 * 
 * Features:
 * - عرض جميع شعارات الموردين
 * - تفعيل/تعطيل الشعارات
 * - إضافة شعارات جديدة
 * - تعديل الشعارات
 * - حذف الشعارات مع الصور
 * - ترقيم الصفحات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "../common.php";
require_once "../lib/pagination.php";


// Check if user is logged in
check_admin_login();

/**
 * Class SupplierLogoList
 * 
 * Handles supplier logo listing operations
 */
class SupplierLogoList {
    
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
     * Delete logo and associated image
     * 
     * @param int $id Logo ID
     * @return bool Success status
     */
    public function deleteLogo(int $id): bool {
        // Get image path first to delete file
        $imageSql = "SELECT adv_img FROM supplier_logo WHERE adv_id = ?";
        $imageStmt = mysqli_prepare($this->db, $imageSql);
        
        if ($imageStmt) {
            mysqli_stmt_bind_param($imageStmt, "i", $id);
            mysqli_stmt_execute($imageStmt);
            $imageResult = mysqli_stmt_get_result($imageStmt);
            
            if ($imageRow = mysqli_fetch_assoc($imageResult)) {
                $imagePath = __DIR__ . "/../upload/supplier_logo/" . $imageRow['adv_img'];
                if (file_exists($imagePath) && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
            mysqli_stmt_close($imageStmt);
        }
        
        // Delete record
        $sql = "DELETE FROM supplier_logo WHERE adv_id = ?";
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
     * Update logo status
     * 
     * @param int $id Logo ID
     * @param int $status New status
     * @return bool Success status
     */
    public function updateStatus(int $id, int $status): bool {
        $sql = "UPDATE supplier_logo SET adv_status = ? WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Build delete link
     * 
     * @param int $id Logo ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "supp-view.php?{$queryString}&action=del&fid={$id}";
    }
    
    /**
     * Format country list for display
     * 
     * @param string|null $countryList Comma-separated country IDs
     * @return string Formatted country names
     */
    public function formatCountryList(?string $countryList): string {
        if (empty($countryList)) {
            return 'All Countries';
        }
        
        $countryIds = explode(",", $countryList);
        $countryNames = [];
        
        foreach ($countryIds as $id) {
            $countryName = get_country_name((int)$id);
            if (!empty($countryName)) {
                $countryNames[] = $countryName;
            }
        }
        
        return implode(", ", $countryNames);
    }
    
    /**
     * Get status badge
     * 
     * @param int $status Status value
     * @return string HTML badge
     */
    public function getStatusBadge(int $status): string {
        if ($status == 1) {
            return '<span class="label label-success">Active</span>';
        }
        return '<span class="label label-danger">Inactive</span>';
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize logo list
$logoList = new SupplierLogoList($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $logoList->deleteLogo((int)$_GET['fid']);
    header("Location: supp-view.php");
    exit;
}

// Set pagination limits
$logoList->limit = $pagination->getLimit(20);

// Build base query
$baseQuery = "SELECT * FROM supplier_logo ORDER BY adv_updated_date DESC";

// Get total records for pagination
$logoList->setSql($baseQuery);
$totalRecords = $logoList->getTotalRecords();

// Set pagination start
$logoList->start = $pagination->getStart($currentPage, $logoList->limit, $totalRecords);

// Get records for current page
$records = $logoList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $logoList->start + 1;
$displayEnd = min($logoList->start + $logoList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "supp-view.php";
$pageString = "?limit=" . $logoList->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $logoList->deleteLogo((int)$id);
    }
    header("Location: supp-view.php");
    exit;
}
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
                    <li class="active">Supplier Logos</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Supplier Logos
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Manage supplier logos
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
                                        onclick="window.location='supp-add.php'">
                                    <i class="icon-plus-sign bigger-120"></i> Add New Logo
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
                                            <th><strong>Image</strong></th>
                                            <th><strong>Info</strong></th>
                                            <th><strong>Dimensions</strong></th>
                                            <th><strong>Status</strong></th>
                                            <th><strong>Change Status</strong></th>
                                            <th><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($records)): ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->adv_id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <?php if (!empty($row->adv_img) && file_exists("../upload/supplier_logo/" . $row->adv_img)): ?>
                                                            <img src="../upload/supplier_logo/<?php echo htmlspecialchars($row->adv_img, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                 style="max-width:80px; max-height:80px;" alt="Supplier Logo"/>
                                                        <?php else: ?>
                                                            <span class="text-muted">No Image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <strong>Link:</strong> 
                                                        <?php if (!empty($row->adv_link)): ?>
                                                            <a href="<?php echo htmlspecialchars($row->adv_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                                                <?php echo htmlspecialchars(substr($row->adv_link, 0, 30) . (strlen($row->adv_link) > 30 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No link</span>
                                                        <?php endif; ?>
                                                        
                                                        <?php 
                                                        $countryList = $logoList->formatCountryList($row->adv_country ?? '');
                                                        if (!empty($countryList)): 
                                                        ?>
                                                            <br><strong>Country:</strong> <?php echo htmlspecialchars($countryList, ENT_QUOTES, 'UTF-8'); ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align:center">
                                                        <?php echo (int)($row->adv_imagewidth ?? 0) . " x " . (int)($row->adv_imageheight ?? 0); ?>
                                                    </td>
                                                    <td style="text-align:center">
                                                        <?php echo $logoList->getStatusBadge((int)($row->adv_status ?? 0)); ?>
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <select onchange="changeStatus(this.value, '<?php echo (int)$row->adv_id; ?>')">
                                                            <option value="">Select</option>
                                                            <?php if ((int)($row->adv_status ?? 0) == 1): ?>
                                                                <option value="0">Deactivate</option>
                                                            <?php else: ?>
                                                                <option value="1">Activate</option>
                                                            <?php endif; ?>
                                                        </select>
                                                    </td>
                                                    <td align="center">
                                                        <div class="btn-group">
                                                            <a href="supp-edit.php?aid=<?php echo (int)$row->adv_id; ?>" 
                                                               class="btn btn-xs btn-info" title="Edit">
                                                                <i class="icon-edit bigger-120"></i>
                                                            </a>
                                                            <a href="<?php echo $logoList->getDeleteLink((int)$row->adv_id); ?>" 
                                                               class="btn btn-xs btn-danger" title="Delete"
                                                               onclick="return confirm('Are you sure you want to delete this logo?')">
                                                                <i class="icon-trash bigger-120"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    No supplier logos found. 
                                                    <a href="supp-add.php">Add your first logo</a>.
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
                                    $totalPages = ceil($totalRecords / $logoList->limit);
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
                { "bSortable": false },
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search supplier logos...');
    });
    
    // Status change function
    function changeStatus(status, id) {
        if (status === '') return;
        
        $.post("ajax-file/supp-change-status.php", {
            stat: status,
            id: id
        }, function(data) {
            location.reload();
        }).fail(function() {
            alert('Failed to update status. Please try again.');
        });
    }
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
    
    .text-muted {
        color: #999;
        font-style: italic;
    }
    
    .label {
        font-size: 11px;
        padding: 3px 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>