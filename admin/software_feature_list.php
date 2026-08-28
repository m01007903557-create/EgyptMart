<?php
/**
 * File: software_feature_list.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة قائمة الميزات البرمجية
 * View and manage software features list
 * 
 * Features:
 * - عرض جميع الميزات البرمجية
 * - تفعيل/تعطيل الميزات
 * - تعديل الميزات
 * - حذف الميزات مع الصور المرتبطة
 * - ترقيم الصفحات
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";
include "lib/pagination.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class SoftwareFeatureList
 * 
 * Handles software feature list management operations
 */
class SoftwareFeatureList {
    
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
     * Get records for current page with pagination
     * 
     * @return mysqli_result|false Query result
     */
    public function getRecords() {
        $sql = $this->sqlList . " LIMIT " . $this->start . ", " . $this->limit;
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Delete feature and associated images
     * 
     * @param int $id Feature ID
     * @return bool Success status
     */
    public function deleteFeature(int $id): bool {
        // First, get associated images to delete files
        $imageSql = "SELECT fi_image FROM feature_images WHERE fi_f_id = ?";
        $imageStmt = mysqli_prepare($this->db, $imageSql);
        
        if ($imageStmt) {
            mysqli_stmt_bind_param($imageStmt, "i", $id);
            mysqli_stmt_execute($imageStmt);
            $imageResult = mysqli_stmt_get_result($imageStmt);
            
            // Delete image files
            while ($imageRow = mysqli_fetch_assoc($imageResult)) {
                $imagePath = __DIR__ . "/../uploads/features/" . $imageRow['fi_image'];
                if (file_exists($imagePath) && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
            mysqli_stmt_close($imageStmt);
        }
        
        // Delete feature (cascade will delete images from database)
        $sql = "DELETE FROM features WHERE f_id = ?";
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
     * Update feature status
     * 
     * @param int $id Feature ID
     * @param int $status New status
     * @return bool Success status
     */
    public function updateStatus(int $id, int $status): bool {
        $sql = "UPDATE features SET f_status = ? WHERE f_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $status, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Build delete link
     * 
     * @param int $id Feature ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id={$id}";
        }
        
        return "software_feature_list.php?{$queryString}&action=del&ad-id={$id}";
    }
    
    /**
     * Get status badge
     * 
     * @param int $status Status (0/1)
     * @return string HTML badge
     */
    public function getStatusBadge(int $status): string {
        if ($status == 1) {
            return '<span class="status-active">Active</span>';
        }
        return '<span class="status-inactive">Inactive</span>';
    }
    
    /**
     * Get yes/no display
     * 
     * @param string $value Value ('1' or '0')
     * @return string HTML display
     */
    public function getYesNo(string $value): string {
        if ($value == '1') {
            return '<span class="badge badge-success">Yes</span>';
        }
        return '<span class="badge badge-default">No</span>';
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize feature list
$featureList = new SoftwareFeatureList($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
    $featureList->deleteFeature((int)$_GET['ad-id']);
    header("Location: software_feature_list.php");
    exit;
}

// Set pagination limits
$featureList->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Build base query
$baseQuery = "SELECT * FROM features WHERE 1 ORDER BY f_id ASC";

// Get total records for pagination
$featureList->setSql($baseQuery);
$totalRecords = $featureList->getTotalRecords();

// Set pagination start
$featureList->start = $pagination->getStart($currentPage, $featureList->limit, $totalRecords);

// Get records for current page
$records = $featureList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $featureList->start + 1;
$displayEnd = min($featureList->start + $featureList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "software_feature_list.php";
$pageString = "?limit=" . $featureList->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $featureList->deleteFeature((int)$id);
    }
    header("Location: software_feature_list.php");
    exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrative Panel - Software Features</title>
<link rel="shortcut icon" href="" type="image/x-icon">

<!-- jQuery -->
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<script type="text/javascript">
    var checked = false;
    
    function checkedAll() {
        var aa = document.getElementById('myform');
        if (checked == false) {
            checked = true;
        } else {
            checked = false;
        }
        for (var i = 0; i < aa.elements.length; i++) {
            aa.elements[i].checked = checked;
        }
    }
    
    function changeStatus(stat, id) {
        if (stat === '') return;
        
        $.post("ajax-files/changeFeatureStatus.php", 
            {stat: stat, id: id}, 
            function(data) {
                location.reload();
            }
        ).fail(function() {
            alert('Failed to update status. Please try again.');
        });
    }
</script>

<link href="style/pagination.css" type="text/css" rel="stylesheet"/>
<link href="style/style.css" type="text/css" rel="stylesheet"/>

<style>
    .status-active {
        color: #009900;
        font-weight: bold;
    }
    .status-inactive {
        color: #CC0000;
        font-weight: bold;
    }
    .badge {
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 11px;
    }
    .badge-success {
        background-color: #5cb85c;
        color: white;
    }
    .badge-default {
        background-color: #777;
        color: white;
    }
    .delete-btn {
        background: #d15b47;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        margin-right: 5px;
    }
    .delete-btn:hover {
        background: #b74635;
    }
    .summary {
        margin: 10px 0;
        padding: 5px;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 3px;
    }
    .items {
        width: 100%;
        border-collapse: collapse;
    }
    .items th {
        background: #2c3e50;
        color: white;
        padding: 8px;
        font-weight: normal;
    }
    .items td {
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }
    .items tr:hover {
        background: #f5f5f5;
    }
    .row-clr {
        background: #f9f9f9;
    }
    .pager {
        margin-top: 15px;
        text-align: center;
    }
    .action img {
        margin: 0 2px;
    }
</style>
</head>

<body>
<div class="main">
    <?php include "includes/admin-top.php" ?>
    
    <div class="control_Panel">
        <?php include "includes/admin-left-con.php" ?>
        
        <div id="content-container">
            <div id="content">
                <form name="myform" id="myform" method="post">
                    
                    <h2>&rsaquo;&nbsp;&nbsp;Manage Software Features&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Feature List</h2>
                    
                    <div id="whatsNew-grid" class="grid-view">
                        
                        <!-- Toolbar -->
                        <table style="width:100%; margin-bottom:10px;">
                            <tr>
                                <td style="width:100px;">
                                    <input name="btnDelete" type="submit" value="Delete" class="delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete the selected records?')" />
                                </td>
                                <td style="width:150px;">
                                    <input type="button" class="delete-btn" onClick="window.location ='software_feature_add.php'" value="Add Feature">
                                </td>
                                <td style="width:200px;">
                                    <?php echo $displayRange; ?>
                                </td>
                                <td align="right">
                                    <div class="summary">
                                        <div class="form no-border" style="margin:0; padding:2px 3px; display:inline-block; vertical-align:middle;">
                                            <select name="limit" id="limit" onchange="window.location.href='software_feature_list.php?page=<?php echo $currentPage; ?>&limit='+this.value;">
                                                <?php for($i = 10; $i <= 40; $i += 10): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo ($i == $featureList->limit) ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        results per page.
                                    </div>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Features Table -->
                        <table class="items">
                            <thead>
                                <tr>
                                    <th class="checkbox" style="width:40px;">
                                        <input name="check_all" value="yes" id="check_all" type="checkbox" onClick="checkedAll();">
                                    </th>
                                    <th style="width:200px;"><strong>Heading</strong></th>
                                    <th style="width:90px;"><strong>Main Feature</strong></th>
                                    <th style="width:90px;"><strong>Has Images</strong></th>
                                    <th style="width:90px;"><strong>Status</strong></th>
                                    <th style="width:90px;"><strong>Change Status</strong></th>
                                    <th class="action" style="width:90px;"><strong>Actions</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recordCount > 0): ?>
                                    <?php $rowNum = 1; ?>
                                    <?php while ($row = mysqli_fetch_object($records)): ?>
                                        <tr <?php echo ($rowNum % 2 == 1) ? 'class="row-clr"' : ''; ?>>
                                            <td class="checkbox">
                                                <input name="cb[]" type="checkbox" value="<?php echo (int)$row->f_id; ?>" />
                                            </td>
                                            
                                            <td>
                                                <strong><?php echo htmlspecialchars($row->f_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <?php 
                                                $contentPreview = strip_tags($row->f_content ?? '');
                                                if (!empty($contentPreview)): 
                                                ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars(substr($contentPreview, 0, 50) . (strlen($contentPreview) > 50 ? '...' : ''), ENT_QUOTES, 'UTF-8'); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <?php echo $featureList->getYesNo($row->f_main_feature ?? '0'); ?>
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <?php echo $featureList->getYesNo($row->f_image ?? '0'); ?>
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <?php echo $featureList->getStatusBadge((int)($row->f_status ?? 0)); ?>
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <select onchange="changeStatus(this.value, '<?php echo (int)$row->f_id; ?>')">
                                                    <option value="">Select</option>
                                                    <?php if ((int)($row->f_status ?? 0) == 1): ?>
                                                        <option value="0">Deactivate</option>
                                                    <?php else: ?>
                                                        <option value="1">Activate</option>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            
                                            <td class="action" style="text-align:center">
                                                <a href="software_feature_edit.php?token=<?php echo rand(1000, 9999) . md5((string)$row->f_id); ?>" 
                                                   title="Edit">
                                                    <img alt="edit" src="images/edit.jpg">
                                                </a>
                                                <a href="<?php echo $featureList->getDeleteLink((int)$row->f_id); ?>" 
                                                   title="Delete" onclick="return confirm('Are you sure you want to delete this feature? This will also delete all associated images.')">
                                                    <img alt="delete" src="images/delete.jpg" border="0">
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $rowNum++; ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align:center; padding:20px;">
                                            No software features found. 
                                            <a href="software_feature_add.php">Add your first feature</a>.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <div class="pager">
                            <?php 
                            echo $pagination->getPaginationString(
                                $currentPage, 
                                $totalRecords, 
                                $featureList->limit, 
                                $adjacents, 
                                $targetPage, 
                                $pageString
                            ); 
                            ?>
                        </div>
                    </div>
                    
                    <br clear="all"/>
                </form>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- Additional inline styles -->
<style>
    .text-muted {
        color: #999;
        font-size: 11px;
    }
    select {
        padding: 2px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 11px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>