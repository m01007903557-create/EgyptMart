<?php
/**
 * File: setting-view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة إعدادات الموقع
 * View and manage site settings
 * 
 * Features:
 * - عرض جميع إعدادات الموقع
 * - عرض الشعارات (صور)
 * - تبديل إعدادات البريد الإلكتروني (تشغيل/إيقاف)
 * - تبديل ترتيب الفئات (يدوي/أبجدي)
 * - روابط تعديل لكل إعداد
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "../common.php";
require_once "../lib/pagination.php";

check_admin_login();
/**
 * Class SiteSettingsManager
 * 
 * Handles site settings management operations
 */
class SiteSettingsManager {
    
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
     * Delete (deactivate) setting (not used, kept for compatibility)
     * 
     * @param int $id Setting ID
     * @return bool Success status
     */
    public function deleteSetting(int $id): bool {
        $sql = "UPDATE site_settings SET st_status = 0 WHERE st_id = ?";
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
     * Update setting value via AJAX
     * 
     * @param int $id Setting ID
     * @param string $value New value
     * @return bool Success status
     */
    public function updateSetting(int $id, string $value): bool {
        $sql = "UPDATE site_settings SET st_value = ?, st_updated_date = NOW() WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $value, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Check if field is a logo field
     * 
     * @param string $field Field name
     * @return bool True if logo field
     */
    public function isLogoField(string $field): bool {
        $logoFields = ['logo', 'small-logo', 'footer-logo', 'left-logo', 'unit-logo-footer'];
        return in_array($field, $logoFields, true);
    }
    
    /**
     * Check if field is email verification
     * 
     * @param string $field Field name
     * @return bool True if email verification
     */
    public function isEmailVerification(string $field): bool {
        return $field === 'email-verification';
    }
    
    /**
     * Check if field is category order
     * 
     * @param string $field Field name
     * @return bool True if category order
     */
    public function isCategoryOrder(string $field): bool {
        return $field === 'category-order';
    }
    
    /**
     * Format field name for display
     * 
     * @param string $field Field name
     * @return string Formatted field name
     */
    public function formatFieldName(string $field): string {
        $parts = explode('-', $field);
        $formatted = array_map('ucfirst', $parts);
        return implode(' ', $formatted);
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
                 style="max-width:100px; max-height:50px;" alt="Logo"/>';
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize settings manager
$settingsManager = new SiteSettingsManager($con);

// Handle delete action (not used, kept for compatibility)
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['aid'])) {
    $settingsManager->deleteSetting((int)$_GET['aid']);
    header("Location: setting-view.php");
    exit;
}

// Set pagination limits
$settingsManager->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Build base query
$baseQuery = "SELECT * FROM site_settings WHERE st_status = '1' ORDER BY st_id ASC";

// Get total records for pagination
$settingsManager->setSql($baseQuery);
$totalRecords = $settingsManager->getTotalRecords();

// Set pagination start
$settingsManager->start = $pagination->getStart($currentPage, $settingsManager->limit, $totalRecords);

// Get records for current page
$records = $settingsManager->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $settingsManager->start + 1;
$displayEnd = min($settingsManager->start + $settingsManager->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "setting-view.php";
$pageString = "?limit=" . $settingsManager->limit . "&page=";
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

        <script type="text/javascript">
            function changeStatus(id, val) {
                // Toggle value
                var newVal = (val == '1') ? '0' : '1';
                $.post("editSiteSettings.php", {id: id, val: newVal}, function(data) {
                    // Optional: Show success message
                }).fail(function() {
                    alert('Failed to update setting');
                });
            }
            
            function updSettings(id, val) {
                $.post("updSiteSettings.php", {id: id, val: val}, function(data) {
                    // Optional: Show success message
                }).fail(function() {
                    alert('Failed to update setting');
                });
            }
        </script>

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
                    <li class="active">Site Settings</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Site Settings
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Manage all site configuration
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
                                        <select name="limit" onchange="window.location.href='setting-view.php?page=<?php echo $currentPage; ?>&limit='+this.value;" style="width:60px;">
                                            <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($i == $settingsManager->limit) ? 'selected="selected"' : ''; ?>>
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
                                                        <strong><?php echo $settingsManager->formatFieldName($row->st_field ?? ''); ?></strong>
                                                    </td>
                                                    
                                                    <td>
                                                        <?php if ($settingsManager->isLogoField($row->st_field ?? '')): ?>
                                                            <!-- Logo display -->
                                                            <?php echo $settingsManager->getLogoHtml($row->st_value ?? ''); ?>
                                                            
                                                        <?php elseif ($settingsManager->isEmailVerification($row->st_field ?? '')): ?>
                                                            <!-- Email verification toggle -->
                                                            <label>
                                                                <input name="switch-field-1" class="ace ace-switch ace-switch-7" 
                                                                       type="checkbox" <?php echo ((int)($row->st_value ?? 0) == 1) ? 'checked="checked"' : ''; ?> 
                                                                       onchange="changeStatus('<?php echo (int)$row->st_id; ?>', '<?php echo (int)$row->st_value; ?>');">
                                                                <span class="lbl"></span>
                                                            </label>
                                                            <span class="help-inline"><?php echo ((int)($row->st_value ?? 0) == 1) ? 'Enabled' : 'Disabled'; ?></span>
                                                            
                                                        <?php elseif ($settingsManager->isCategoryOrder($row->st_field ?? '')): ?>
                                                            <!-- Category order radio buttons -->
                                                            <label style="margin-right:15px;">
                                                                <input type="radio" name="cat_order_<?php echo (int)$row->st_id; ?>" 
                                                                       class="ace" value="alphabetic" 
                                                                       <?php echo ($row->st_value === 'alphabetic') ? 'checked="checked"' : ''; ?> 
                                                                       onchange="updSettings('<?php echo (int)$row->st_id; ?>', 'alphabetic');"/>
                                                                <span class="lbl"> Alphabetic</span>
                                                            </label>
                                                            <label>
                                                                <input type="radio" name="cat_order_<?php echo (int)$row->st_id; ?>" 
                                                                       class="ace" value="manual" 
                                                                       <?php echo ($row->st_value === 'manual') ? 'checked="checked"' : ''; ?> 
                                                                       onchange="updSettings('<?php echo (int)$row->st_id; ?>', 'manual');"/>
                                                                <span class="lbl"> Manual</span>
                                                            </label>
                                                            
                                                        <?php else: ?>
                                                            <!-- Text value -->
                                                            <?php 
                                                            $value = stripslashes($row->st_value ?? '');
                                                            $truncated = $settingsManager->truncateText($value, 55);
                                                            echo nl2br(htmlspecialchars($truncated, ENT_QUOTES, 'UTF-8'));
                                                            
                                                            if (strlen($value) > 55): 
                                                            ?>
                                                                &nbsp;&nbsp;
                                                                <a href="setting-details.php?sid=<?php echo (int)$row->st_id; ?>" class="more-link">
                                                                    More
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                    
                                                    <td class="center">
                                                        <?php if (!$settingsManager->isEmailVerification($row->st_field ?? '') && 
                                                                  !$settingsManager->isCategoryOrder($row->st_field ?? '')): ?>
                                                            <a href="setting-edit.php?sid=<?php echo (int)$row->st_id; ?>" 
                                                               title="Edit" class="btn btn-xs btn-info">
                                                                <i class="icon-edit bigger-120"></i> Edit
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">No edit</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php $rowNum++; ?>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center">
                                                    No settings found.
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
// ===== حساب إجمالي السجلات والتصفح =====
// حساب إجمالي السجلات
$count_sql = "SELECT COUNT(*) as total FROM site_settings";
$count_result = mysqli_query($con, $count_sql);
$count_row = mysqli_fetch_assoc($count_result);
$totalitems = (int)$count_row['total'];

// عدد العناصر في كل صفحة
$limit = 20;

// تحديد الصفحة الحالية
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// حساب نقطة البداية للاستعلام
$start = ($page - 1) * $limit;

// إعادة تعريف متغيرات التصفح
$adjacents = 3;
$targetpage = "setting-view.php";
$pagestring = "?page=";

// حساب عدد الصفحات
$totalPages = ceil($totalitems / $limit);

// عرض أزرار التصفح إذا كان هناك أكثر من صفحة
if ($totalPages > 1) {
    echo '<div class="dataTables_paginate paging_bootstrap">';
    echo '<ul class="pagination">';
    
    // زر الصفحة السابقة
    if ($page > 1) {
        echo '<li class="prev"><a href="' . $targetpage . $pagestring . ($page - 1) . '"><i class="icon-double-angle-left"></i></a></li>';
    } else {
        echo '<li class="prev disabled"><a href="#"><i class="icon-double-angle-left"></i></a></li>';
    }
    
    // أرقام الصفحات
    $start_loop = max(1, $page - $adjacents);
    $end_loop = min($totalPages, $page + $adjacents);
    
    for ($i = $start_loop; $i <= $end_loop; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        echo '<li class="' . $activeClass . '"><a href="' . $targetpage . $pagestring . $i . '">' . $i . '</a></li>';
    }
    
    // زر الصفحة التالية
    if ($page < $totalPages) {
        echo '<li class="next"><a href="' . $targetpage . $pagestring . ($page + 1) . '"><i class="icon-double-angle-right"></i></a></li>';
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
        font-style: italic;
        font-size: 11px;
    }
    .help-inline {
        color: #777;
        font-size: 11px;
        margin-left: 5px;
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
    .ace-switch.ace-switch-7 {
        width: 80px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>