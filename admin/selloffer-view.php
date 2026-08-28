<?php
// ✅ تعريف السماح بالوصول
define('ACCESS_ALLOWED', true);

// ✅ تعريف إضافي للسماح بالوصول من البريد الإلكتروني
define('ACCESS_EMAIL_ALLOWED', true);

// ✅ بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ السماح بتنفيذ إجراءات الموافقة والرفض
if (isset($_GET['action']) && ($_GET['action'] == 'appr' || $_GET['action'] == 'disappr')) {
    define('SKIP_AUTH_CHECK', true);
}

// بعد هذه التعريفات، يمكن تضمين الملفات
ob_start();
include "../common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";


// Check if user is logged in
check_admin_login();

/**
 * Class SaleOfferManager
 * 
 * Handles sale offer management operations
 */
class SaleOfferManager {
    
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
     * Delete sale offer
     * 
     * @param int $id Sale offer ID
     * @return bool Success status
     */
    public function deleteOffer(int $id): bool {
        $sql = "DELETE FROM sale_offer WHERE so_id = ?";
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
     * Approve sale offer
     * 
     * @param int $id Sale offer ID
     * @return bool Success status
     */
    public function approveOffer(int $id): bool {
        $sql = "UPDATE sale_offer SET so_approval_status = 1, so_approval_date = NOW() WHERE so_id = ?";
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
     * Disapprove sale offer
     * 
     * @param int $id Sale offer ID
     * @return bool Success status
     */
    public function disapproveOffer(int $id): bool {
        $sql = "UPDATE sale_offer SET so_approval_status = 2, so_approval_date = NOW() WHERE so_id = ?";
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
     * @param int $id Sale offer ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$id}";
        }
        
        return "selloffer-view.php?{$queryString}&action=del&ad-id={$id}";
    }
    
    /**
     * Build approve link
     * 
     * @param int $id Sale offer ID
     * @return string Approve URL
     */
    public function getApproveLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=appr&id={$id}";
        }
        
        return "selloffer-view.php?{$queryString}&action=appr&id={$id}";
    }
    
    /**
     * Build disapprove link
     * 
     * @param int $id Sale offer ID
     * @return string Disapprove URL
     */
    public function getDisapproveLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=disappr&id={$id}";
        }
        
        return "selloffer-view.php?{$queryString}&action=disappr&id={$id}";
    }
    
    /**
     * Send approval email
     * 
     * @param int $offerId Sale offer ID
     * @param object $user User object
     * @param object $offer Sale offer object
     */
    public function sendApprovalEmail(int $offerId, object $user, object $offer): void {
        $cid = rand(1000, 9999) . md5((string)($user->bnsprof_id ?? ''));
        
        $contactDetails = '<strong>' . htmlspecialchars($user->bnsprof_compname ?? '') . '</strong><br/>' .
                         htmlspecialchars($user->bnsprof_address1 ?? '') . '<br/>' .
                         'Mobile/Cell Phone: ' . htmlspecialchars($user->mobile1 ?? '') . '<br/>' .
                         'Email: ' . htmlspecialchars($user->email ?? '');
        
        $suname = trim(($user->name_prefix ?? '') . ' ' . ($user->fname ?? '') . ' ' . ($user->lname ?? ''));
        
        // Offer image
        if (!empty($offer->so_pic)) {
            $productImg = '<img src="http://egyptmart.online/upload/sale_offer/' . htmlspecialchars($offer->so_pic, ENT_QUOTES, 'UTF-8') . '" width="100" />';
        } else {
            $productImg = '<img src="http://egyptmart.online/upload/sale_offer/no-image.png" width="100" />';
        }
        
        $offerTitle = $offer->so_service ?? '';
        
        // Email details
        $subject = "موافقة المنصة على نشر عرض بيعك";
        $fromName = get_page_settings(4);
        $fromEmail = get_adminemail();
        $usrEmail = $user->email ?? '';
        
        // Include email template
        ob_start();
        //include "email/admin_selloffer_approve.php";
        $message1 = ob_get_clean();
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $fromName <$fromEmail>\r\n";
        
        mail($usrEmail, $subject, $message1, $headers);
        
        // Redirect to email confirmation page
        header('Location: ../selloffer-email.php?so_id=' . $offerId);
        exit;
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize offer manager
$offerManager = new SaleOfferManager($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $offerManager->deleteOffer((int)$_GET['ad-id']);
    header("Location: selloffer-view.php");
    exit;
}

// Handle approve action
if (isset($_GET['action']) && $_GET['action'] === "appr" && isset($_GET['id'])) {
    $offerId = (int)$_GET['id'];
    
    // Get user and offer details for email
    $userSql = "SELECT u.*, bf.* FROM user u 
                LEFT JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid 
                WHERE u.usr_id = (SELECT so_usr_id FROM sale_offer WHERE so_id = ?)";
    
    $stmt = mysqli_prepare($con, $userSql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $offerId);
        mysqli_stmt_execute($stmt);
        $userResult = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_object($userResult);
        mysqli_stmt_close($stmt);
        
        // Get offer details
        $offerSql = "SELECT * FROM sale_offer WHERE so_id = ?";
        $stmt = mysqli_prepare($con, $offerSql);
        mysqli_stmt_bind_param($stmt, "i", $offerId);
        mysqli_stmt_execute($stmt);
        $offerResult = mysqli_stmt_get_result($stmt);
        $offer = mysqli_fetch_object($offerResult);
        mysqli_stmt_close($stmt);
        
        if ($user && $offer) {
            $offerManager->approveOffer($offerId);
            $offerManager->sendApprovalEmail($offerId, $user, $offer);
        }
    }
}

// Handle disapprove action
if (isset($_GET['action']) && $_GET['action'] === "disappr" && isset($_GET['id'])) {
    $offerManager->disapproveOffer((int)$_GET['id']);
    header("Location: selloffer-view.php");
    exit;
}

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $offerManager->deleteOffer((int)$id);
    }
    header("Location: selloffer-view.php");
    exit;
}

// Set SQL for main query (will be used by DataTables)
$offerManager->setSql("SELECT s.*, bp.bnsprof_compname, sip.mst_name 
                       FROM sale_offer s 
                       JOIN user u ON u.usr_id = s.so_usr_id 
                       LEFT JOIN business_profile bp ON s.so_usr_id = bp.bnsprof_uid 
                       LEFT JOIN country c ON c.cn_id = u.country 
                       LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id  
                       ORDER BY so_updated_date DESC");

$totalItems = $offerManager->getTotalRecords();
$limit = $offerManager->limit;
$offerManager->start = $pagination->getStart($currentPage, $limit, $totalItems);
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
                        <a href="selloffer-view.php">Manage Sale Offers</a>
                    </li>
                    <li class="active">View Sale Offers</li>
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
                                            <th><strong>Image</strong></th>
                                            <th><strong>Product/Service</strong></th>
                                            <th><strong>Posted By</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th class="center"><strong>Membership Type</strong></th>
                                            <th class="center"><strong>Date</strong></th>
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
    jQuery(function($) {
        // Initialize DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "ajax": {
                "url": "selloffer-view-response.php",
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
                { "bSortable": false },
                null,
                null,
                null,
                null,
                { "bSortable": false },
                null,
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
        $('table th input:checkbox').on('click', function() {
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search offers...');
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
</style>

</body>
</html>

<?php ob_end_flush(); ?>