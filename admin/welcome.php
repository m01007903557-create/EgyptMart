<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل الدخول
check_admin_login();

/**
 * Class AdminLoginlist
 * 
 * Handles admin login history operations
 */
class AdminLoginlist {
    public string $sqlList = '';
    public int $start = 0;
    public int $limit = 20;
    
    /**
     * Set SQL query
     */
    public function setsql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    /**
     * Get total records count
     */
    public function totalrecord(): int {
        global $con;
        
        error_log("=== تشخيص استعلام welcome.php ===");
        error_log("السطر 46 - قيمة الاستعلام: " . ($this->sqlList ?? 'NULL'));
        error_log("السطر 46 - نوع المتغير: " . gettype($this->sqlList ?? 'NULL'));
        
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * Get records for current page
     */
    public function listview(): mysqli_result|false {
        global $con;
        return mysqli_query($con, $this->sqlList);
    }
    
    /**
     * Calculate number of pages
     */
    public function numpage(int $rowPage): int {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
    /**
     * Delete login record
     */
    public function deleterecord(int $adid): void {
        global $con;
        $sql = "DELETE FROM admin_login_details WHERE admin_login_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $adid);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Build delete link
     */
    public function deletelink(int $id): string {
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&ad-id=" . $id
            : $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $id;
        return $dellink;
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize login list
$al = new AdminLoginlist();

/******************** Delete single record *********************/
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $adId = (int)$_GET['ad-id'];
    if ($adId > 0) {
        $al->deleterecord($adId);
    }
    header("Location: welcome.php");
    exit;
}
/*************************************************/

/******************** Bulk delete *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("Location: welcome.php");
    exit;
}
/*************************************************/

// تعريف الاستعلام
$sql = "SELECT au.*, ald.admin_login_id, ald.last_login_time 
        FROM admin_user au
        JOIN admin_login_details ald ON au.id = ald.id 
        WHERE ald.admin_login_id != (
            SELECT MAX(ald2.admin_login_id) 
            FROM admin_login_details ald2 
            WHERE ald2.id = au.id
        )
        ORDER BY ald.last_login_time DESC";

// تمرير الاستعلام إلى الكلاس
$al->setsql($sql);

// Set pagination limits
$al->limit = $pagination->getLimit(20);

// الآن يمكن حساب العدد الإجمالي (بعد تمرير الاستعلام)
$totalItems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($currentPage, $limit, $totalItems);
$adjacents = 1;
$targetpage = "welcome.php";
$pagestring = "?limit=" . $limit . "&page=";

// جلب السجلات
$records = $al->listview();

// Calculate display range
$displayStart = $al->start + 1;
$displayEnd = min($al->start + $limit, $totalItems);
$displayRange = $displayStart . "-" . $displayEnd . " of " . $totalItems . " items";
?>

<?php include "includes/admin-top.php" ?>

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
                    <li class="active">View Admin</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post"> 
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onClick="return confirm('Are you sure to delete the selected records?')">
                                    <i class="icon-trash bigger-120"></i> Delete
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center">
                                                <label><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></label>
                                            </th>
                                            <th><strong>Username</strong></th>
                                            <th><strong>Email ID</strong></th>
                                            <th><strong>Last Login</strong></th>
                                            <th style="text-align:center"><strong>Status</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($records && mysqli_num_rows($records) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($records)): ?>
                                                <tr>
                                                    <td class="center">
                                                        <label>
                                                            <input name="cb[]" class="ace" type="checkbox" 
                                                                   value="<?php echo (int)$row['admin_login_id']; ?>">
                                                            <span class="lbl"></span>
                                                        </label>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['username'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($row['email'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($row['last_login_time'] ?? ''); ?></td>
                                                    <td align="center">
                                                        <?php if (($row['status'] ?? 0) == 1): ?>
                                                            <img alt="Active" src="images/active.jpg">
                                                        <?php else: ?>
                                                            <img alt="Inactive" src="images/inactive.jpg">
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No records found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <?php if ($totalItems > 0): ?>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $displayRange; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    $totalPages = ceil($totalItems / $limit);
                                    if ($totalPages > 1) {
                                        echo '<div class="dataTables_paginate paging_bootstrap">';
                                        echo '<ul class="pagination">';
                                        
                                        // Previous button
                                        if ($currentPage > 1) {
                                            echo '<li class="prev"><a href="?page=' . ((int)$currentPage) . '"><i class="icon-double-angle-left"></i></a></li>';
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
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php"; ?>

<!-- JavaScript includes -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

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
<script src="assets/js/ace.min.js?v=2"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        var oTable1 = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": false },
                null, null, null,
                { "bSortable": false }
            ],
            "bPaginate": false,
            "bInfo": false
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
    });
</script>

<!-- DataTables JS -->
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/dataTables.bootstrap.min.js"></script>
</body>
</html>

<?php ob_end_flush(); ?>