<?php
/**
 * File: serviceslider_view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة عرض وإدارة سلايدر الخدمات
 * Service slider management view page
 * 
 * Features:
 * - عرض جميع صور سلايدر الخدمات
 * - تفعيل/تعطيل الصور
 * - حذف متعدد
 * - إضافة صور جديدة
 * - تعديل الصور
 * - عرض تفاصيل كل صورة
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";
include "lib/pagination.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class ServiceSliderViewList
 * 
 * Handles service slider data operations
 */
class ServiceSliderViewList {
    
    /** @var string SQL query */
    private string $sqlList = '';
    
    /** @var int Start offset for pagination */
    public int $start = 0;
    
    /** @var int Limit per page */
    public int $limit = 20;
    
    /** @var array Currency symbols */
    private array $currencySymbols = [];
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        $this->currencySymbols = $GLOBALS['currency_symbols'] ?? [];
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
     * Delete record by ID
     * 
     * @param int $id Record ID
     * @return bool Success status
     */
    public function deleteRecord(int $id): bool {
        // Get image path first
        $selectSql = "SELECT adv_img FROM prodservice_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $selectSql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                // Delete image file
                $imagePath = __DIR__ . "/../upload/service_slider/" . $row['adv_img'];
                if (file_exists($imagePath) && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
            mysqli_stmt_close($stmt);
        }
        
        // Delete database record
        $deleteSql = "DELETE FROM prodservice_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $deleteSql);
        
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
     * @param int $id Record ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "serviceslider-view.php?{$queryString}&action=del&fid={$id}";
    }
    
    /**
     * Format country list for display
     * 
     * @param string|null $countryList Comma-separated country IDs
     * @return string Formatted country names
     */
    public function formatCountryList(?string $countryList): string {
        if (empty($countryList)) {
            return '';
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
     * Get currency symbol
     * 
     * @param string|null $currency Currency code
     * @return string Currency symbol
     */
    public function getCurrencySymbol(?string $currency): string {
        return $this->currencySymbols[$currency ?? 'USD'] ?? '$';
    }
    
    /**
     * Update status via AJAX
     * 
     * @param int $status New status
     * @param int $id Record ID
     * @return bool Success status
     */
    public function updateStatus(int $status, int $id): bool {
        $sql = "UPDATE prodservice_slider SET adv_status = ? WHERE adv_id = ?";
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

// Initialize slider view
$sliderView = new ServiceSliderViewList($con);
$sliderView->limit = $pagination->getLimit(20);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $sliderView->deleteRecord((int)$_GET['fid']);
    header("Location: serviceslider-view.php");
    exit;
}

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $sliderView->deleteRecord((int)$id);
    }
    header("Location: serviceslider-view.php");
    exit;
}

// Set SQL query
$sliderView->setSql("SELECT * FROM prodservice_slider WHERE adv_type='2' ORDER BY adv_updated_date DESC");

// Get total records for pagination
$totalRecords = $sliderView->getTotalRecords();
$limit = $sliderView->limit;
$start = $pagination->getStart($currentPage, $limit, $totalRecords);

// Build pagination string
$targetPage = "serviceslider-view.php";
$pageString = "?limit=" . $limit . "&page=";

// Get records for current page
$sliderView->setSql("SELECT * FROM prodservice_slider WHERE adv_type='2' ORDER BY adv_updated_date DESC LIMIT {$start}, {$limit}");
$records = $sliderView->getRecords();

// Calculate display range
$displayStart = $start + 1;
$displayEnd = min($start + $limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";
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
                        <a href="serviceslider-view.php">Manage Service Slider</a>
                    </li>
                    <li class="active">View Slider</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected records?')">
                                    <i class="icon-trash bigger-120"></i> Delete Selected
                                </button>
                                <button type="button" class="btn btn-xs btn-success" 
                                        onclick="window.location='serviceslider-add.php'">
                                    <i class="icon-plus-sign bigger-120"></i> Add New Slider
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
                                            <th><strong>Width & Height</strong></th>
                                            <th><strong>Status</strong></th>
                                            <th><strong>Change Status</strong></th>
                                            <th><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($records && mysqli_num_rows($records) > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($records)): ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->adv_id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td style="text-align:center;">
                                                        <?php if (!empty($row->adv_img) && file_exists("../upload/service_slider/" . $row->adv_img)): ?>
                                                            <img src="../upload/service_slider/<?php echo htmlspecialchars($row->adv_img, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                 style="max-width:200px; max-height:150px;" alt="Slider Image"/>
                                                        <?php else: ?>
                                                            <span class="text-muted">No Image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align:left;">
                                                        <strong>Link:</strong> 
                                                        <a href="<?php echo htmlspecialchars($row->adv_link ?? '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                                            <?php echo htmlspecialchars(substr($row->adv_link ?? '', 0, 50) . (strlen($row->adv_link ?? '') > 50 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                        <br/><br/>
                                                        
                                                        <strong>Heading:</strong> 
                                                        <?php echo htmlspecialchars($row->adv_title ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                        <br/>
                                                        
                                                        <strong>MOQ Data:</strong> 
                                                        <?php echo htmlspecialchars(substr($row->adv_description ?? '', 0, 100) . (strlen($row->adv_description ?? '') > 100 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        <br/>
                                                        
                                                        <?php 
                                                        $countryList = $sliderView->formatCountryList($row->adv_country ?? '');
                                                        if (!empty($countryList)): 
                                                        ?>
                                                            <strong>Country:</strong> 
                                                            <?php echo htmlspecialchars($countryList, ENT_QUOTES, 'UTF-8'); ?>
                                                            <br/>
                                                        <?php endif; ?>
                                                        
                                                        <strong>FOB Price:</strong> 
                                                        <?php 
                                                        echo $sliderView->getCurrencySymbol($row->adv_currency ?? 'USD');
                                                        echo number_format((float)($row->adv_price ?? 0), 2);
                                                        ?>
                                                        <br/><br/>
                                                        
                                                        <strong>MOQ Unit:</strong> 
                                                        <?php echo (int)($row->adv_piece ?? 0); ?>
                                                        <br/>
                                                        
                                                        <strong>Unit Type:</strong> 
                                                        <?php echo htmlspecialchars($row->unit_type ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                                        <br/><br/>
                                                        
                                                        <strong>Membership Icon:</strong> 
                                                        <?php if (!empty($row->adv_icon) && file_exists("../images/" . $row->adv_icon)): ?>
                                                            <img src="../images/<?php echo htmlspecialchars($row->adv_icon, ENT_QUOTES, 'UTF-8'); ?>" 
                                                                 style="max-width:20px; max-height:20px;" alt="Icon">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="text-align:center">
                                                        <?php 
                                                        echo (int)($row->adv_imagewidth ?? 0) . " x " . (int)($row->adv_imageheight ?? 0);
                                                        ?>
                                                    </td>
                                                    <td style="text-align:center">
                                                        <?php echo $sliderView->getStatusBadge((int)($row->adv_status ?? 0)); ?>
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
                                                            <a href="serviceslider-edit.php?aid=<?php echo (int)$row->adv_id; ?>" 
                                                               class="btn btn-xs btn-info" title="Edit">
                                                                <i class="icon-edit bigger-120"></i>
                                                            </a>
                                                            <a href="<?php echo $sliderView->getDeleteLink((int)$row->adv_id); ?>" 
                                                               class="btn btn-xs btn-danger" title="Delete"
                                                               onclick="return confirm('Are you sure you want to delete this record?')">
                                                                <i class="icon-trash bigger-120"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    No service slider items found. 
                                                    <a href="serviceslider-add.php">Add your first item</a>.
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
                                    $totalPages = ceil($totalRecords / $limit);
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search service slider...');
        
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
    
    // Status change function
    function changeStatus(status, id) {
        if (status === '') return;
        
        $.post("ajax-file/productslider-change-status.php", {
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
    
    .text-muted {
        color: #999;
        font-style: italic;
    }
    
    img[src*="edit.jpg"], img[src*="delete.jpg"] {
        width: 16px;
        height: 16px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>