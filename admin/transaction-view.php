<?php
/**
 * File:transaction-view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض سجل المعاملات المالية والمدفوعات
 * View financial transactions and payment history
 * 
 * Features:
 * - عرض جميع المعاملات المالية
 * - تصفية حسب نوع المعاملة (شراء رصيد / اشتراك سنوي)
 * - عرض تفاصيل المستخدم
 * - عرض المبالغ والعملات
 * - ترقيم الصفحات
 * - بحث متقدم
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once  "../common.php";
require_once "../lib/pagination.php";

// Check if user is logged in
check_admin_login();

/**
 * Class TransactionList
 * 
 * Handles transaction listing operations
 */
class TransactionList {
    
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
     * Get transaction type description
     * 
     * @param int $type Transaction type
     * @return string Description
     */
    public function getTransactionType(int $type): string {
        return match ($type) {
            1 => 'Credits Purchased',
            5 => 'Annual Subscription Payment',
            default => 'Unknown Transaction'
        };
    }
    
    /**
     * Format amount with currency
     * 
     * @param float $amount Amount
     * @param string $currency Currency code
     * @return string Formatted amount
     */
    public function formatAmount(float $amount, string $currency): string {
        return number_format($amount, 2) . ' (' . htmlspecialchars($currency, ENT_QUOTES, 'UTF-8') . ')';
    }
    
    /**
     * Get user full name
     * 
     * @param object $row User row
     * @return string Full name
     */
    public function getUserFullName(object $row): string {
        $parts = [];
        if (!empty($row->name_prefix)) {
            $parts[] = ucfirst($row->name_prefix);
        }
        if (!empty($row->fname)) {
            $parts[] = ucfirst($row->fname);
        }
        if (!empty($row->lname)) {
            $parts[] = ucfirst($row->lname);
        }
        return implode(' ', $parts);
    }
    
    /**
     * Get user token for details link
     * 
     * @param int $userId User ID
     * @return string Token
     */
    public function getUserToken(int $userId): string {
        return rand(1000, 9999) . md5((string)$userId);
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize transaction list
$transactionList = new TransactionList($con);

// Handle delete action (commented out in original)
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    // $transactionList->deleteRecord((int)$_GET['ad-id']);
    header("Location: transaction-view.php");
    exit;
}

// Set pagination limits
$transactionList->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Build base query
$baseQuery = "SELECT bh.*, u.* 
              FROM billing_history bh
              JOIN user u ON bh.bh_usr_id = u.usr_id 
              WHERE (bh.bh_type = 1 OR bh.bh_type = 5) AND bh.bh_status = 1 
              ORDER BY bh.bh_id DESC";

// Get total records for pagination
$transactionList->setSql($baseQuery);
$totalRecords = $transactionList->getTotalRecords();

// Set pagination start
$transactionList->start = $pagination->getStart($currentPage, $transactionList->limit, $totalRecords);

// Get records for current page (without limit in this query - will be used with pagination later)
$records = $transactionList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $transactionList->start + 1;
$displayEnd = min($transactionList->start + $transactionList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "transaction-view.php";
$pageString = "?limit=" . $transactionList->limit . "&page=";

// Handle bulk delete (commented out in original)
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    // foreach ($_POST['cb'] as $id) {
    //     $transactionList->deleteRecord((int)$id);
    // }
    header("Location: transaction-view.php");
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
                    <li class="active">Payment Transactions</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Payment Transactions
                        <small>
                            <i class="icon-double-angle-right"></i>
                            View all financial transactions
                        </small>
                    </h1>
                </div>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            
                            <!-- Toolbar -->
                            <div class="row" style="margin-bottom:15px;">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $displayRange; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="dataTables_paginate paging_bootstrap" style="float:right;">
                                        <select name="limit" onchange="window.location.href='transaction-view.php?page=<?php echo $currentPage; ?>&limit='+this.value;" style="width:60px;">
                                            <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($i == $transactionList->limit) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <span class="lbl"> results per page</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Transactions Table -->
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center" style="width:50px;">#</th>
                                            <th><strong>Date</strong></th>
                                            <th><strong>User</strong></th>
                                            <th><strong>Description</strong></th>
                                            <th><strong>Credits</strong></th>
                                            <th><strong>Amount</strong></th>
                                            <th><strong>Payment Gateway</strong></th>
                                            <th class="center"><strong>Status</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php 
                                            mysqli_data_seek($records, 0);
                                            $counter = 0;
                                            while ($row = mysqli_fetch_object($records)): 
                                                if ($counter >= $transactionList->start && $counter < $transactionList->start + $transactionList->limit):
                                                    $userToken = $transactionList->getUserToken((int)$row->usr_id);
                                            ?>
                                                <tr>
                                                    <td class="center">
                                                        <?php echo $counter + 1; ?>
                                                    </td>
                                                    
                                                    <td>
                                                        <?php echo !empty($row->bh_updated_date) ? date("d-M, Y", strtotime($row->bh_updated_date)) : 'N/A'; ?>
                                                        <br>
                                                        <small class="text-muted">
                                                            <?php echo !empty($row->bh_updated_date) ? date("H:i", strtotime($row->bh_updated_date)) : ''; ?>
                                                        </small>
                                                    </td>
                                                    
                                                    <td>
                                                        <a href="user-details.php?token=<?php echo $userToken; ?>" class="user-link">
                                                            <strong><?php echo htmlspecialchars($transactionList->getUserFullName($row), ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        </a>
                                                        <br>
                                                        <small class="text-muted">ID: <?php echo (int)$row->usr_id; ?></small>
                                                    </td>
                                                    
                                                    <td>
                                                        <?php echo $transactionList->getTransactionType((int)$row->bh_type); ?>
                                                        <?php if (!empty($row->bh_description)): ?>
                                                            <br>
                                                            <small class="text-muted"><?php echo htmlspecialchars($row->bh_description, ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <td class="center">
                                                        <?php if ((int)($row->bh_credit_purchased ?? 0) > 0): ?>
                                                            <span class="badge badge-info"><?php echo (int)$row->bh_credit_purchased; ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <td>
                                                        <strong><?php echo $transactionList->formatAmount((float)$row->bh_amount, $row->bh_currency_code ?? ''); ?></strong>
                                                    </td>
                                                    
                                                    <td>
                                                        <?php if (!empty($row->bh_from)): ?>
                                                            <span class="label label-info"><?php echo htmlspecialchars($row->bh_from, ENT_QUOTES, 'UTF-8'); ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">N/A</span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (!empty($row->bh_transaction_id)): ?>
                                                            <br>
                                                            <small class="text-muted">ID: <?php echo htmlspecialchars($row->bh_transaction_id, ENT_QUOTES, 'UTF-8'); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <td class="center">
                                                        <?php if ((int)($row->bh_status ?? 0) == 1): ?>
                                                            <span class="label label-success">Completed</span>
                                                        <?php elseif ((int)($row->bh_status ?? 0) == 0): ?>
                                                            <span class="label label-warning">Pending</span>
                                                        <?php else: ?>
                                                            <span class="label label-danger">Failed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php 
                                                endif;
                                                $counter++;
                                            endwhile; 
                                            ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">
                                                    No transactions found.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="row">
                                <div class="col-xs-12">
                                    <div class="pager">
                                        <?php 
                                        echo $pagination->getPaginationString(
                                            $currentPage, 
                                            $totalRecords, 
                                            $transactionList->limit, 
                                            $adjacents, 
                                            $targetPage, 
                                            $pageString
                                        ); 
                                        ?>
                                    </div>
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
                null,
                null,
                { "bSortable": false }
            ],
            "bPaginate": false, // We're using custom pagination
            "bInfo": false, // We're using custom info
            "bFilter": true,
            "bSort": true
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search transactions...');
        
        // Format currency display
        $('.currency-amount').each(function() {
            // Currency formatting if needed
        });
    });
</script>

<style>
    .user-link {
        color: #428bca;
        text-decoration: none;
    }
    .user-link:hover {
        text-decoration: underline;
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
    .label {
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: normal;
    }
    .label-success {
        background-color: #5cb85c;
        color: white;
    }
    .label-warning {
        background-color: #f0ad4e;
        color: white;
    }
    .label-danger {
        background-color: #d9534f;
        color: white;
    }
    .label-info {
        background-color: #5bc0de;
        color: white;
    }
    .text-muted {
        color: #999;
        font-size: 11px;
    }
    .pager {
        margin-top: 15px;
        text-align: center;
    }
    .dataTables_info {
        padding-top: 8px;
        color: #666;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>