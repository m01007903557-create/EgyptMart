<?php
/**
 * File: social-view.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: إدارة وسائل التواصل الاجتماعي - النسخة المستقرة
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
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

/**
 * Class listCat
 * 
 * Handles social media listing operations
 */
class listCat {
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
        $result = mysqli_query($con, $this->sqlList);
        return $result ? mysqli_num_rows($result) : 0;
    }
    
    /**
     * Get records for current page
     */
    public function listview() {
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
     * Delete record (soft delete)
     */
    public function deleterecord(int $adid): void {
        global $con;
        $sql = "UPDATE social_media_login_info SET al_status = '0' WHERE al_id = ?";
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
        if ($_SERVER['QUERY_STRING'] == "") {
            $dellink = "?action=del&fid=" . $id;
        } else {
            $dellink = "social-view.php?" . $_SERVER['QUERY_STRING'] . "&action=del&fid=" . $id;
        }
        return $dellink;
    }
}

// Initialize pagination
$pagination = new Pagination();
$page = $pagination->getCurrentPage();

// Initialize list object
$al = new listCat();

/******************** Delete single record *********************/
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $fid = (int)$_GET['fid'];
    if ($fid > 0) {
        $al->deleterecord($fid);
    }
    header("Location: social-view.php");
    exit;
}
/*************************************************/

/******************** Bulk delete *********************/
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    global $con;
    foreach ($_POST['cb'] as $cb) {
        $id = (int)$cb;
        if ($id > 0) {
            $sql = "UPDATE airlines SET al_status = 0 WHERE al_id = ?";
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }
    }
    header("Location: social-view.php");
    exit;
}
/*************************************************/

// Set pagination limits
$al->limit = $pagination->getLimit(20);
$al->setsql("SELECT * FROM social_media_login_info WHERE smli_status = '1'");

$totalitems = $al->totalrecord();
$limit = $al->limit;
$al->start = $pagination->getStart($page, $limit, $totalitems);
$adjacents = 1;
$targetpage = "social-view.php";
$pagestring = "?limit=" . $limit . "&page=";

$recObj = $al->listview();

// Calculate display range
$showitems = ($al->start + 1) . " - ";
if (($al->start + $limit) < $totalitems) {
    $showitems .= ($al->start + $limit);
} else {
    $showitems .= $totalitems;
}
$showitems .= " of " . $totalitems . " items";
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
                        <a href="social-view.php">Manage Social Media</a>
                    </li>
                    <li class="active">View Social Media</li>
                </ul>
            </div>
            
            <div class="page-content">
                <form name="test_view" id="test_view" method="post"> 
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <tbody>
                                        <?php 
                                        $j = 0;
                                        $count = $recObj ? mysqli_num_rows($recObj) : 0;
                                        if ($count > 0): 
                                            while ($row = mysqli_fetch_object($recObj)): 
                                        ?>
                                        <tr>
                                            <td class="center">&nbsp;</td>
                                            <td>
                                                <?php 
                                                $a_c = explode("-", $row->smli_field ?? '');
                                                foreach ($a_c as $sf) {
                                                    echo ucfirst($sf) . "&nbsp;";
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if (($row->smli_field ?? '') == 'logo'): ?>
                                                    <img src="../sitelogo/<?php echo htmlspecialchars($row->smli_value ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="Logo" />
                                                <?php else: ?>
                                                    <?php 
                                                    $value = $row->smli_value ?? '';
                                                    echo htmlspecialchars(substr($value, 0, 55), ENT_QUOTES, 'UTF-8'); 
                                                    if (strlen($value) > 60): 
                                                    ?>
                                                        ...&nbsp;&nbsp;
                                                        <a href="social-details.php?sid=<?php echo (int)($row->smli_id ?? 0); ?>">More</a>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="social-edit.php?sid=<?php echo (int)($row->smli_id ?? 0); ?>" title="edit" style="text-decoration:none;">Change</a>
                                            </td>
                                        </tr>
                                        <?php 
                                            $j++;
                                            endwhile; 
                                        else: 
                                        ?>
                                        <tr>
                                            <td colspan="4" style="text-align:center; color:#EE0000;">No Records.</td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination Info -->
                            <?php if ($totalitems > 0): ?>
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="dataTables_info">
                                        Showing <?php echo $showitems; ?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <?php
                                    $totalPages = ceil($totalitems / $limit);
                                    if ($totalPages > 1) {
                                        echo '<div class="dataTables_paginate paging_bootstrap">';
                                        echo '<ul class="pagination">';
                                        
                                        // Previous button
                                        if ($page > 1) {
                                            echo '<li class="prev"><a href="?page=' . ($page - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
                                        } else {
                                            echo '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
                                        }
                                        
                                        // Page numbers
                                        for ($i = 1; $i <= $totalPages; $i++) {
                                            $activeClass = ($i == $page) ? 'active' : '';
                                            echo '<li class="' . $activeClass . '"><a href="?page=' . $i . '">' . $i . '</a></li>';
                                        }
                                        
                                        // Next button
                                        if ($page < $totalPages) {
                                            echo '<li class="next"><a href="?page=' . ($page + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
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
<script src="assets/js/ace.min.js"></script>

</body>
</html>

<?php ob_end_flush(); ?>