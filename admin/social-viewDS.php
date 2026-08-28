<?php
/**
 * File: social-view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة إعدادات وسائل التواصل الاجتماعي
 * View and manage social media settings
 * 
 * Features:
 * - عرض جميع إعدادات وسائل التواصل الاجتماعي النشطة
 * - عرض الشعارات (صور)
 * - عرض النصوص مع إمكانية عرض المزيد
 * - روابط تعديل لكل إعداد
 * - ترقيم الصفحات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "check_admin.php";
require_once "../lib/pagination.php"; 
// Check if user is logged in
checkUserLogin();

/**
 * Class SocialMediaManager
 * 
 * Handles social media settings management operations
 */
class SocialMediaManager {
    
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
     * Delete (deactivate) setting
     * 
     * @param int $id Setting ID
     * @return bool Success status
     */
    public function deleteSetting(int $id): bool {
        $sql = "UPDATE social_media_login_info SET smli_status = 0 WHERE smli_id = ?";
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
     * @param int $id Setting ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "social-view.php?{$queryString}&action=del&fid={$id}";
    }
    
    /**
     * Check if field is a logo field
     * 
     * @param string $field Field name
     * @return bool True if logo field
     */
    public function isLogoField(string $field): bool {
        return $field === 'logo';
    }
    
    /**
     * Format field name for display
     * 
     * @param string $field Field name
     * @return string Formatted field name
     */
    public function formatFieldName(string $field): string {
        if (empty($field)) {
            return '';
        }
        
        $parts = explode('-', $field);
        $formattedParts = array_map('ucfirst', $parts);
        return implode(' ', $formattedParts);
    }
    
    /**
     * Truncate text for display
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @return string Truncated text
     */
    public function truncateText(string $text, int $length = 55): string {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
    
    /**
     * Get logo HTML
     * 
     * @param string $filename Logo filename
     * @return string HTML img tag
     */
    public function getLogoHtml(string $filename): string {
        if (empty($filename) || !file_exists("../sitelogo/" . $filename)) {
            return '<span class="text-muted">No logo</span>';
        }
        
        return '<img src="../sitelogo/' . htmlspecialchars($filename, ENT_QUOTES, 'UTF-8') . '" 
                 style="max-width:100px; max-height:50px;" alt="Social Logo"/>';
    }
    
    /**
     * Check if value is a URL
     * 
     * @param string $value Value to check
     * @return bool True if URL
     */
    public function isUrl(string $value): bool {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize social media manager
$socialManager = new SocialMediaManager($con);

// Handle delete action (commented out in original)
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $socialManager->deleteSetting((int)$_GET['fid']);
    header("Location: social-view.php");
    exit;
}

// Set pagination limits
$socialManager->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Build base query
$baseQuery = "SELECT * FROM social_media_login_info WHERE smli_status = '1' ORDER BY smli_id ASC";

// Get total records for pagination
$socialManager->setSql($baseQuery);
$totalRecords = $socialManager->getTotalRecords();

// Set pagination start
$socialManager->start = $pagination->getStart($currentPage, $socialManager->limit, $totalRecords);

// Get records for current page
$records = $socialManager->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $socialManager->start + 1;
$displayEnd = min($socialManager->start + $socialManager->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "social-view.php";
$pageString = "?limit=" . $socialManager->limit . "&page=";
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
                    <li class="active">Social Media Settings</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Social Media Settings
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Manage all social media configurations
                        </small>
                    </h1>
                </div>

                <form name="test_view" id="test_view" method="post">
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
                                        <select name="limit" onchange="window.location.href='social-view.php?page=<?php echo $currentPage; ?>&limit='+this.value;" style="width:60px;">
                                            <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($i == $socialManager->limit) ? 'selected="selected"' : ''; ?>>
                                                    <?php echo $i; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <span class="lbl"> results per page</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Settings Table -->
                            <div class="table-responsive">
                                <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th class="center" style="width:50px;">#</th>
                                            <th><strong>Setting Name</strong></th>
                                            <th><strong>Value</strong></th>
                                            <th class="center" style="width:100px;"><strong>Action</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($recordCount > 0): ?>
                                            <?php $rowNum = 1; ?>
                                            <?php while ($row = mysqli_fetch_object($records)): ?>
                                                <tr>
                                                    <td class="center">
                                                        <?php echo $rowNum; ?>
                                                    </td>
                                                    
                                                    <td>
                                                        <strong><?php echo $socialManager->formatFieldName($row->smli_field ?? ''); ?></strong>
                                                        <br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($row->smli_field ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                                                    </td>
                                                    
                                                    <td>
                                                        <?php if ($socialManager->isLogoField($row->smli_field ?? '')): ?>
                                                            <!-- Logo display -->
                                                            <?php echo $socialManager->getLogoHtml($row->smli_value ?? ''); ?>
                                                            
                                                        <?php else: ?>
                                                            <!-- Text value -->
                                                            <?php 
                                                            $value = stripslashes($row->smli_value ?? '');
                                                            $truncated = $socialManager->truncateText($value, 55);
                                                            
                                                            if ($socialManager->isUrl($value)) {
                                                                echo '<a href="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" target="_blank">' 
                                                                     . htmlspecialchars($truncated, ENT_QUOTES, 'UTF-8') . '</a>';
                                                            } else {
                                                                echo htmlspecialchars($truncated, ENT_QUOTES, 'UTF-8');
                                                            }
                                                            
                                                            if (strlen($value) > 55): 
                                                            ?>
                                                                &nbsp;&nbsp;
                                                                <a href="social-details.php?sid=<?php echo (int)$row->smli_id; ?>" class="more-link">
                                                                    More
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <td class="center">
                                                        <a href="social-edit.php?sid=<?php echo (int)$row->smli_id; ?>" 
                                                           class="btn btn-xs btn-info" title="Edit">
                                                            <i class="icon-edit bigger-120"></i> Change
                                                        </a>
                                                        
                                                        <?php if (isset($row->smli_status)): ?>
                                                            <br>
                                                            <small>
                                                                <?php if ((int)$row->smli_status === 1): ?>
                                                                    <span class="label label-success">Active</span>
                                                                <?php else: ?>
                                                                    <span class="label label-danger">Inactive</span>
                                                                <?php endif; ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php $rowNum++; ?>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    No social media settings found.
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
// ===== حساب إجمالي السجلات وإعدادات التصفح =====
// استعلام لحساب إجمالي السجلات (عدّل اسم الجدول حسب ما هو موجود)
$count_sql = "SELECT COUNT(*) as total FROM social_media"; // تأكد من اسم الجدول الصحيح
$count_result = mysqli_query($con, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$totalitems = (int)$count_row['total'];

$totalitems

// يمكنك الآن استخدام الدالة
echo buildPagination($totalitems, $limit, $page, $adjacents, $targetpage, $pagestring);
?>
                                        echo buildPagination($totalitems, $limit, $page, $adjacents, $targetpage, $pagestring);
                                       
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

<?php include "includes/footer.php"; ?>

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

<style>
    .more-link {
        color: #428bca;
        text-decoration: none;
        font-size: 11px;
    }
    .more-link:hover {
        text-decoration: underline;
    }
    .text-muted {
        color: #999;
        font-size: 11px;
    }
    .pager {
        margin-top: 15px;
        text-align: center;
    }
    .btn-xs {
        padding: 2px 8px;
        font-size: 11px;
    }
    img {
        max-width: 100px;
        max-height: 50px;
        border: 1px solid #ddd;
        padding: 2px;
        border-radius: 3px;
        background: #fff;
    }
    .label {
        font-size: 10px;
        padding: 2px 5px;
    }
    .dataTables_info {
        padding-top: 8px;
        color: #666;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>