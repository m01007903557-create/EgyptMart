<?php
/**
 * File: paymethod-view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة عرض وإدارة بوابات الدفع
 * Payment gateway management view page
 * 
 * Features:
 * - عرض جميع بوابات الدفع النشطة
 * - تفعيل/تعطيل بوابات الدفع
 * - حذف متعدد
 * - إضافة بوابات جديدة
 * - تعديل بوابات موجودة
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Include required files
include "../common.php";
include "../lib/pagination.php";  // تم التعديل: إضافة ../ أمام المسار

// Check if user is logged in
checkUserLogin();

/**
 * Class PaymentMethodList
 * 
 * Handles payment gateway listing operations
 */
class PaymentMethodList {
    
    /** @var string SQL query */
    private string $sqlList = '';
    
    /** @var int Start offset for pagination */
    public int $start = 0;
    
    /** @var int Limit per page */
    public int $limit = 10;
    
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
        global $con;
        
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * Get records for current page
     * 
     * @return mysqli_result|false Query result
     */
    public function getRecords() {
        global $con;
        return mysqli_query($con, $this->sqlList);
    }
    
    /**
     * Deactivate payment gateway
     * 
     * @param int $id Gateway ID
     * @return bool Success status
     */
    public function deactivateGateway(int $id): bool {
        global $con;
        
        $sql = "UPDATE payment_gateway SET pg_status = 0 WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Activate payment gateway
     * 
     * @param int $id Gateway ID
     * @return bool Success status
     */
    public function activateGateway(int $id): bool {
        global $con;
        
        $sql = "UPDATE payment_gateway SET pg_status = 1 WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
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
     * @param int $id Gateway ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&aid={$id}";
        }
        
        return "paymethod-view.php?{$queryString}&action=del&aid={$id}";
    }
    
    /**
     * Build activate link
     * 
     * @param int $id Gateway ID
     * @return string Activate URL
     */
    public function getActivateLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=upd&aid={$id}";
        }
        
        return "paymethod-view.php?{$queryString}&action=upd&aid={$id}";
    }
    
    /**
     * Get gateway status badge
     * 
     * @param int $status Status (0/1)
     * @return string HTML badge
     */
    public function getStatusBadge(int $status): string {
        if ($status === 1) {
            return '<span class="label label-success">Active</span>';
        }
        return '<span class="label label-default">Inactive</span>';
    }
    
    /**
     * Get gateway logo path
     * 
     * @param string|null $logo Logo filename
     * @return string Logo URL
     */
    public function getLogoPath(?string $logo): string {
        if (empty($logo)) {
            return '../images/payment-gateway/default.png';
        }
        return '../images/payment-gateway/' . htmlspecialchars($logo, ENT_QUOTES, 'UTF-8');
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize payment method list
$paymentList = new PaymentMethodList();

// Handle deactivate action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['aid'])) {
    $paymentList->deactivateGateway((int)$_GET['aid']);
    header("Location: paymethod-view.php");
    exit;
}

// Handle activate action
if (isset($_GET['action']) && $_GET['action'] === "upd" && isset($_GET['aid'])) {
    $paymentList->activateGateway((int)$_GET['aid']);
    header("Location: paymethod-view.php");
    exit;
}

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $paymentList->deactivateGateway((int)$id);
    }
    header("Location: paymethod-view.php");
    exit;
}

// Set SQL for active gateways
$paymentList->setSql("SELECT * FROM payment_gateway WHERE pg_status = 1 ORDER BY pg_name");

// Get total records for pagination
$totalRecords = $paymentList->getTotalRecords();
$limit = $paymentList->limit;
$paymentList->start = $pagination->getStart($currentPage, $limit, $totalRecords);

// Get records with pagination
$paymentList->setSql("SELECT * FROM payment_gateway WHERE pg_status = 1 ORDER BY pg_name LIMIT {$paymentList->start}, {$limit}");
$records = $paymentList->getRecords();

// Calculate display range
$displayStart = $paymentList->start + 1;
$displayEnd = min($paymentList->start + $limit, $totalRecords);
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
                    <li class="active">Manage Payment Methods</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Payment Methods
                        <small>
                            <i class="icon-double-angle-right"></i>
                            List
                        </small>
                    </h1>
                </div>
                
                <form name="payment_view" id="payment_view" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to deactivate the selected payment methods?')">
                                    <i class="icon-trash bigger-120"></i> Deactivate
                                </button>
                                <a href="paymethod-add.php" class="btn btn-xs btn-success">
                                    <i class="icon-plus-sign bigger-120"></i> Add New Payment Method
                                </a>
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
                                            <th><strong>Logo</strong></th>
                                            <th><strong>Name</strong></th>
                                            <th><strong>Gateway ID</strong></th>
                                            <th style="text-align:center"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($records && mysqli_num_rows($records) > 0): ?>
                                            <?php while ($row = mysqli_fetch_object($records)): ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row->id; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td align="center">
                                                        <img src="<?php echo $paymentList->getLogoPath($row->pg_logo ?? ''); ?>" 
                                                             style="max-width:50px; max-height:50px;" 
                                                             alt="<?php echo htmlspecialchars($row->pg_name ?? '', ENT_QUOTES, 'UTF-8'); ?> Logo"/>
                                                    </td>
                                                    <td>
                                                        <?php echo htmlspecialchars(stripslashes($row->pg_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                                        <br>
                                                        <small><?php echo $paymentList->getStatusBadge((int)($row->pg_status ?? 1)); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row->pg_id ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                    <td align="center">
                                                        <div class="btn-group">
                                                            <a href="paymethod-edit.php?aid=<?php echo (int)$row->id; ?>" 
                                                               class="btn btn-xs btn-info" title="Edit">
                                                                <i class="icon-edit bigger-120"></i>
                                                            </a>
                                                            <a href="<?php echo $paymentList->getDeleteLink((int)$row->id); ?>" 
                                                               class="btn btn-xs btn-danger" 
                                                               title="Deactivate"
                                                               onclick="return confirm('Are you sure you want to deactivate this payment method?')">
                                                                <i class="icon-trash bigger-120"></i>
                                                            </a>
                                                            <?php if ((int)($row->pg_status ?? 1) === 0): ?>
                                                                <a href="<?php echo $paymentList->getActivateLink((int)$row->id); ?>" 
                                                                   class="btn btn-xs btn-success" 
                                                                   title="Activate"
                                                                   onclick="return confirm('Are you sure you want to activate this payment method?')">
                                                                    <i class="icon-ok bigger-120"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">
                                                    No payment methods found. 
                                                    <a href="paymethod-add.php">Add your first payment method</a>.
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
        $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
        
        function tooltip_placement(context, source) {
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
        
        // Search functionality enhancement
        $('#sample-table-2_filter input').attr('placeholder', 'Search payment methods...');
    });
</script>

<style>
    /* Additional styling for payment method logos */
    .table td img {
        border-radius: 4px;
        border: 1px solid #ddd;
        padding: 2px;
        background: #fff;
        transition: transform 0.2s;
    }
    
    .table td img:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }
    
    .btn-group {
        display: flex;
        gap: 3px;
        justify-content: center;
    }
    
    .btn-group .btn {
        border-radius: 3px !important;
        margin: 0;
    }
    
    .label {
        font-size: 11px;
        padding: 3px 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>