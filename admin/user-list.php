<?php
/**
 * File: user-list.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة قائمة المستخدمين
 * View and manage users list
 * 
 * Features:
 * - عرض جميع المستخدمين النشطين
 * - حذف المستخدمين (فردي/متعدد)
 * - عرض الرصيد وبلد المستخدم
 * - عرض خطة العضوية
 * - روابط للتفاصيل والتعديل
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

// Check if user is logged in
check_admin_login();


/**
 * Class UserListManager
 * 
 * Handles user list management operations
 */
class UserListManager {
    
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
     * Delete user
     * 
     * @param int $id User ID
     * @return bool Success status
     */
    public function deleteUser(int $id): bool {
        // Check if user has related records before deleting
        // This is a soft delete by setting status to 0
        $sql = "UPDATE user SET status = 0 WHERE usr_id = ?";
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
     * @param int $id User ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$id}";
        }
        
        return "user-list.php?{$queryString}&action=del&ad-id={$id}";
    }
    
    /**
     * Get user details token
     * 
     * @param int $userId User ID
     * @return string Token
     */
    public function getUserToken(int $userId): string {
        return rand(1000, 9999) . md5((string)$userId);
    }
    
    /**
     * Get edit token
     * 
     * @param int $userId User ID
     * @return string Token
     */
    public function getEditToken(int $userId): string {
        return rand(1000, 9999) . $userId;
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize user list manager
$userManager = new UserListManager($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $userManager->deleteUser((int)$_GET['ad-id']);
    header("Location: user-list.php");
    exit;
}

// Set pagination limits
$userManager->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Build base query
$baseQuery = "SELECT u.*, c.cn_name, bf.bnsprof_compname, sp.mst_name 
              FROM user u
              JOIN country c ON u.country = c.cn_id
              JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
              LEFT JOIN smembership_plan sp ON sp.mp_id = u.usr_mp_id
              WHERE u.status = '1'
              ORDER BY u.usr_id DESC";

// Get total records for pagination
$userManager->setSql($baseQuery);
$totalRecords = $userManager->getTotalRecords();

// Set pagination start
$userManager->start = $pagination->getStart($currentPage, $userManager->limit, $totalRecords);

// Calculate display range
$displayStart = $userManager->start + 1;
$displayEnd = min($userManager->start + $userManager->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "user-list.php";
$pageString = "?limit=" . $userManager->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    $deleted = 0;
    foreach ($_POST['cb'] as $id) {
        if ($userManager->deleteUser((int)$id)) {
            $deleted++;
        }
    }
    
    if ($deleted > 0) {
        $_SESSION['success_msg'] = "{$deleted} user(s) deleted successfully.";
    }
    
    header("Location: user-list.php");
    exit;
}

// Get session messages
$success_msg = $_SESSION['success_msg'] ?? '';
unset($_SESSION['success_msg']);
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
                    <li class="active">User Management</li>
                </ul>
            </div>
            
            <div class="page-content">
                <?php if (!empty($success_msg)): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i> <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <h1>
                        User List
                        <small>
                            <i class="icon-double-angle-right"></i>
                            View and manage all users
                        </small>
                    </h1>
                </div>
                
                <form name="myform" id="myform" method="post">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onclick="return confirm('Are you sure you want to delete the selected users? This action cannot be undone.')">
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
                                            <th><strong>Name</strong></th>
                                            <th><strong>Email</strong></th>
                                            <th class="center"><strong>Credit</strong></th>
                                            <th><strong>Country</strong></th>
                                            <th><strong>Membership Plan</strong></th>
                                            <th class="center"><strong>Details</strong></th>
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

<!-- Inline scripts for DataTables functionality -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize DataTable
        var oTable1 = $('#sample-table-2').dataTable({
            "ajax": {
                "url": "user-list-response.php",
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
                null,
                { "bSortable": false },
                null,
                null,
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
        $('#sample-table-2_filter input').attr('placeholder', 'Search users...');
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
    
    .badge {
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
    
    .badge-info {
        background-color: #5bc0de;
        color: white;
    }
    
    .btn-group {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    
    .btn-xs {
        padding: 2px 8px;
        font-size: 11px;
    }
    
    .alert {
        margin-bottom: 20px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>