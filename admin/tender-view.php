<?php
/**
 * File: tender-view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة عرض وإدارة المناقصات النشطة مع دعم DataTables و AJAX
 * Active tenders management view page with DataTables and AJAX support
 * 
 * Features:
 * - عرض جميع المناقصات النشطة
 * - الموافقة/رفض المناقصات
 * - حذف المناقصات
 * - بحث متقدم وتصفية
 * - ترقيم صفحات ديناميكي
 * - إشعارات البريد الإلكتروني
 */

declare(strict_types=1);

// Start output buffering
ob_start();
session_start();

// Include required files
include "../common.php";
require_once "../lib/pagination.php";

// Check if user is logged in
check_admin_login();

/**
 * Class TenderManager
 * 
 * Handles tender management operations
 */
class TenderManager {
    
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
     * Delete tender
     * 
     * @param int $id Tender ID
     * @return bool Success status
     */
    public function deleteTender(int $id): bool {
        $sql = "DELETE FROM tender WHERE tnd_id = ?";
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
     * Approve tender
     * 
     * @param int $id Tender ID
     * @return bool Success status
     */
    public function approveTender(int $id): bool {
        $sql = "UPDATE tender SET tnd_approval_status = 1 WHERE tnd_id = ?";
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
     * Disapprove tender
     * 
     * @param int $id Tender ID
     * @return bool Success status
     */
    public function disapproveTender(int $id): bool {
        $sql = "UPDATE tender SET tnd_approval_status = 2 WHERE tnd_id = ?";
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
     * @param int $id Tender ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$id}";
        }
        
        return "tender-view.php?{$queryString}&action=del&ad-id={$id}";
    }
    
    /**
     * Build approve link
     * 
     * @param int $id Tender ID
     * @return string Approve URL
     */
    public function getApproveLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=appr&id={$id}";
        }
        
        return "tender-view.php?{$queryString}&action=appr&id={$id}";
    }
    
    /**
     * Build disapprove link
     * 
     * @param int $id Tender ID
     * @return string Disapprove URL
     */
    public function getDisapproveLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=disappr&id={$id}";
        }
        
        return "tender-view.php?{$queryString}&action=disappr&id={$id}";
    }
    
    /**
     * Send approval email
     * 
     * @param int $tenderId Tender ID
     * @return void
     */
    public function sendApprovalEmail(int $tenderId): void {
        global $con;
        
        // Get user and tender details
        $query = "SELECT u.*, t.*, bf.*, c.cn_name, s.state_name, ct.ct_name 
          FROM user u
          JOIN tender t ON u.usr_id = t.tnd_usr_id
          LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
          LEFT JOIN country c ON u.country = c.cn_id
          LEFT JOIN states s ON bf.bnsprof_state = s.state_id
          LEFT JOIN city ct ON bf.bnsprof_city = ct.ct_id
          WHERE t.tnd_id = ?";
        
        $stmt = mysqli_prepare($con, $query);
        
        if (!$stmt) {
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $tenderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        if (!$user) {
            return;
        }
        
        $cid = rand(1000, 9999) . md5((string)($user->bnsprof_id ?? ''));
        
        // Build contact details
        $contactDetails = '<strong>' . htmlspecialchars($user->bnsprof_compname ?? '') . '</strong><br/>' .
                         htmlspecialchars($user->bnsprof_address1 ?? '') . '<br/>' .
                         'Mobile/Cell Phone: ' . htmlspecialchars($user->mobile1 ?? '') . '<br/>' .
                         'Email: ' . htmlspecialchars($user->email ?? '');
        
        $fullName = trim(($user->name_prefix ?? '') . ' ' . ($user->fname ?? '') . ' ' . ($user->lname ?? ''));
        
        // Get tender details
        $tenderSql = "SELECT * FROM tender WHERE tnd_id = ?";
        $stmt = mysqli_prepare($con, $tenderSql);
        mysqli_stmt_bind_param($stmt, "i", $tenderId);
        mysqli_stmt_execute($stmt);
        $tenderResult = mysqli_stmt_get_result($stmt);
        $tender = mysqli_fetch_object($tenderResult);
        mysqli_stmt_close($stmt);
        
        // Get unit name if quantity exists
        $unitName = '';
        if (!empty($tender->tnd_qty_mu_id)) {
            $unitSql = "SELECT mu_name FROM measurement_unit WHERE mu_id = ?";
            $stmt = mysqli_prepare($con, $unitSql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $tender->tnd_qty_mu_id);
                mysqli_stmt_execute($stmt);
                $unitResult = mysqli_stmt_get_result($stmt);
                if ($unitRow = mysqli_fetch_object($unitResult)) {
                    $unitName = $unitRow->mu_name;
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        $tenderTitle = $tender->tnd_heading ?? '';
        
        // Email details
        $subject = "Tender Approved From " . get_page_settings(4);
        $fromName = get_page_settings(4);
        $fromEmail = get_adminemail();
        $userEmail = $user->email ?? '';
        
        // Include email template
        ob_start();
        include "email/admin_tender_approve.php";
        $emailMessage = ob_get_clean();
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $fromName <$fromEmail>\r\n";
        
        mail($userEmail, $subject, $emailMessage, $headers);
        
        // Redirect to email confirmation page
        header('Location: ../tender-email.php?tnd_id=' . $tenderId);
        exit;
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize tender manager
$tenderManager = new TenderManager($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $tenderManager->deleteTender((int)$_GET['ad-id']);
    header("Location: tender-view.php");
    exit;
}

// Handle approve action
if (isset($_GET['action']) && $_GET['action'] === "appr" && isset($_GET['id'])) {
    $tenderId = (int)$_GET['id'];
    $tenderManager->approveTender($tenderId);
    $tenderManager->sendApprovalEmail($tenderId);
    exit;
}

// Handle disapprove action
if (isset($_GET['action']) && $_GET['action'] === "disappr" && isset($_GET['id'])) {
    $tenderManager->disapproveTender((int)$_GET['id']);
    header("Location: tender-view.php");
    exit;
}

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $tenderManager->deleteTender((int)$id);
    }
    header("Location: tender-view.php");
    exit;
}

// Set SQL for main query (will be used by DataTables)
$tenderManager->setSql("SELECT * FROM tender WHERE tnd_status='1' AND tender.tnd_due_date >= CURDATE() ORDER BY tnd_updated_date DESC");

$totalItems = $tenderManager->getTotalRecords();
$limit = $tenderManager->limit;
$tenderManager->start = $pagination->getStart($currentPage, $limit, $totalItems);
?>

<?php include "includes/admin-top.php" ?>

<!-- jQuery and DataTables initialization -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>

<!-- DataTables scripts -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' ,'fixed')}catch(e){}
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
                    <li class="active">Manage Tenders</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Active Tenders
                        <small>
                            <i class="icon-double-angle-right"></i>
                            View and manage active tenders
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
                                <p style="display: inline-block; float: right;">
                                    Go to Page No : 
                                    <input type="number" name="page_no" id="page_no" class="page_no" min="1" style="width:60px;" />
                                </p>
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
                                            <th><strong>Tender Heading</strong></th>
                                            <th><strong>Posted By</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th class="center"><strong>Publish Date</strong></th>
                                            <th class="center"><strong>Due Date</strong></th>
                                            <th class="center"><strong>Details</strong></th>
                                            <th class="center"><strong>Status</strong></th>
                                            <th class="center"><strong>Actions</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
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

<!-- Inline scripts for DataTables functionality -->
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "ajax": {
                "url": "tender-view-response.php",
                "type": "post",
                "error": function() {
                    $("#sample-table-2").css("display", "none");
                }
            },
            "lengthMenu": [10, 25, 50, 100],
            "bProcessing": true,
            "serverSide": true,
            "aoColumns": [
                { "bSortable": false },
                null,
                { "bSortable": false },
                null,
                null,
                null,
                { "bSortable": false },
                { "bSortable": false },
                { "bSortable": false }
            ],
            "drawCallback": function(settings) {
                $("#overlay").hide();
            }
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
        
        // Select all checkbox
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search tenders...');
    });
</script>

<style>
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
    
    .page_no {
        width: 60px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 3px;
        padding: 3px;
    }
    
    .dataTables_filter {
        margin-bottom: 10px;
    }
    
    #sample-table-2 tbody tr {
        cursor: pointer;
    }
    
    #sample-table-2 tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    .label {
        display: inline-block;
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: normal;
    }
    
    .label-success {
        background-color: #5cb85c;
        color: white;
    }
    
    .label-danger {
        background-color: #d9534f;
        color: white;
    }
    
    .label-default {
        background-color: #777;
        color: white;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>