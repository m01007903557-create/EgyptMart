<?php
/**
 * File: welcome.php
 * Version: 2.0.0 (PHP 8.3)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من تسجيل دخول المشرف
check_admin_login();

/**
 * Class AdminLoginlist
 */
class AdminLoginlist {
    public string $sqlList = '';
    public int $start = 0;
    public int $limit = 20;
    
    public function setsql(string $sql): void {
        $this->sqlList = $sql;
    }
    
    public function totalrecord(): int {
        global $con;
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    public function listview(): mysqli_result|false {
        global $con;
        return mysqli_query($con, $this->sqlList);
    }
    
    public function numpage(int $rowPage): int {
        return (int)floor($this->totalrecord() / $rowPage);
    }
    
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
    
    public function deletelink(int $id): string {
        $dellink = $_SERVER['QUERY_STRING'] == "" 
            ? "?action=del&ad-id=" . $id
            : $_SERVER['QUERY_STRING'] . "&action=del&ad-id=" . $id;
        return $dellink;
    }
}

$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

$al = new AdminLoginlist();

if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $adId = (int)$_GET['ad-id'];
    if ($adId > 0) {
        $al->deleterecord($adId);
    }
    header("Location: welcome.php");
    exit;
}

if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $al->deleterecord((int)$id);
    }
    header("Location: welcome.php");
    exit;
}

$sql = "SELECT au.*, ald.admin_login_id, ald.last_login_time 
        FROM admin_user au
        JOIN admin_login_details ald ON au.id = ald.id 
        WHERE ald.admin_login_id != (
            SELECT MAX(ald2.admin_login_id) 
            FROM admin_login_details ald2 
            WHERE ald2.id = au.id
        )
        ORDER BY ald.last_login_time DESC";

$al->setsql($sql);
$al->limit = $pagination->getLimit(20);

$totalItems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($currentPage, $limit, $totalItems);
$adjacents = 1;
$targetpage = "welcome.php";
$pagestring = "?limit=" . $limit . "&page=";

$records = $al->listview();

$displayStart = $al->start + 1;
$displayEnd = min($al->start + $limit, $totalItems);
$displayRange = $displayStart . "-" . $displayEnd . " of " . $totalItems . " items";
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <?php include "includes/admin-left-con.php" ?>
        
        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li><i class="icon-home home-icon"></i><a href="welcome.php">Home</a></li>
                    <li class="active">View Admin</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="myform" id="myform" method="post"> 
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-header">
                                <button class="btn btn-xs btn-danger" name="btnDelete" type="submit" 
                                        onClick="return confirm('Are you sure?')">
                                    <i class="icon-trash bigger-120"></i> Delete
                                </button>
                            </div>
                            
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                         <th class="center"><input type="checkbox" class="ace" id="selectAll"><span class="lbl"></span></th>
                                         <th><strong>Username</strong></th>
                                         <th><strong>Email ID</strong></th>
                                         <th><strong>Last Login</strong></th>
                                         <th style="text-align:center"><strong>Status</strong></th>
                                    </thead>
                                    <tbody>
                                        <?php if ($records && mysqli_num_rows($records) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($records)): ?>
                                              <tr id="row_<?php echo $row['admin_login_id']; ?>">
                                                <td class="center">
                                                    <input name="cb[]" class="ace" type="checkbox" value="<?php echo (int)$row['admin_login_id']; ?>">
                                                    <span class="lbl"></span>
                                                  \(td)
                                                <td class="center"><?php echo htmlspecialchars($row['username'] ?? ''); ?>\(td)
                                                <td class="center"><?php echo htmlspecialchars($row['email'] ?? ''); ?>\(td)
                                                <td class="center"><?php echo htmlspecialchars($row['last_login_time'] ?? ''); ?>\(td)
                                                <td class="center">
                                                    <?php if (($row['status'] ?? 0) == 1): ?>
                                                        <img alt="Active" src="images/active.jpg">
                                                    <?php else: ?>
                                                        <img alt="Inactive" src="images/inactive.jpg">
                                                    <?php endif; ?>
                                                  \(td)
                                               </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                              <tr><td colspan="5" class="text-center">No records found</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                 </table>
                            </div>
                            
                            <?php if ($totalItems > 0): ?>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">Showing <?php echo $displayRange; ?></div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    $totalPages = ceil($totalItems / $limit);
                                    if ($totalPages > 1) {
                                        echo '<div class="dataTables_paginate paging_bootstrap"><ul class="pagination">';
                                        if ($currentPage > 1) echo '<li class="prev"><a href="?page=' . ($currentPage - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
                                        else echo '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
                                        
                                        for ($i = 1; $i <= $totalPages; $i++) {
                                            echo '<li class="' . ($i == $currentPage ? 'active' : '') . '"><a href="?page=' . $i . '">' . $i . '</a></li>';
                                        }
                                        
                                        if ($currentPage < $totalPages) echo '<li class="next"><a href="?page=' . ($currentPage + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
                                        else echo '<li class="next disabled"><a href="#"><i class="icon-double-angle-right"></i></a></li>';
                                        echo '</ul></div>';
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
</div>

<?php include "includes/footer.php"; ?>

</body>
</html>
<?php ob_end_flush(); ?>