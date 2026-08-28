<?php
/**
 * File: software_version_list.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة إصدارات البرامج
 * View and manage software versions
 * 
 * Features:
 * - عرض جميع إصدارات البرامج
 * - تفعيل/تعطيل الإصدارات
 * - تعديل الإصدارات
 * - حذف الإصدارات مع الصور
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
 * Class SoftwareVersionList
 * 
 * Handles software version list management operations
 */
class SoftwareVersionList {
    
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
     * Get records for current page with pagination
     * 
     * @return mysqli_result|false Query result
     */
    public function getRecords() {
        $sql = $this->sqlList . " LIMIT " . $this->start . ", " . $this->limit;
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Delete version and associated image
     * 
     * @param int $id Version ID
     * @return bool Success status
     */
    public function deleteVersion(int $id): bool {
        // Get image path first to delete file
        $imageSql = "SELECT isv_image FROM index_software_version WHERE isv_id = ?";
        $imageStmt = mysqli_prepare($this->db, $imageSql);
        
        if ($imageStmt) {
            mysqli_stmt_bind_param($imageStmt, "i", $id);
            mysqli_stmt_execute($imageStmt);
            $imageResult = mysqli_stmt_get_result($imageStmt);
            
            if ($imageRow = mysqli_fetch_assoc($imageResult)) {
                $imagePath = __DIR__ . "/../image/" . $imageRow['isv_image'];
                if (file_exists($imagePath) && is_file($imagePath)) {
                    @unlink($imagePath);
                }
            }
            mysqli_stmt_close($imageStmt);
        }
        
        // Delete record
        $sql = "DELETE FROM index_software_version WHERE isv_id = ?";
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
     * Update version status
     * 
     * @param int $id Version ID
     * @param int $status New status
     * @return bool Success status
     */
    public function updateStatus(int $id, int $status): bool {
        $sql = "UPDATE index_software_version SET isv_status = ? WHERE isv_id = ?";
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
     * @param int $id Version ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "software_version_list.php?{$queryString}&action=del&fid={$id}";
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
     * Truncate link for display
     * 
     * @param string $link Link URL
     * @param int $length Maximum length
     * @return string Truncated link
     */
    public function truncateLink(string $link, int $length = 30): string {
        if (strlen($link) <= $length) {
            return $link;
        }
        return substr($link, 0, $length) . '...';
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize version list
$versionList = new SoftwareVersionList($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $versionList->deleteVersion((int)$_GET['fid']);
    header("Location: software_version_list.php");
    exit;
}

// Set pagination limits
$versionList->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Build base query
$baseQuery = "SELECT * FROM index_software_version ORDER BY isv_id DESC";

// Get total records for pagination
$versionList->setSql($baseQuery);
$totalRecords = $versionList->getTotalRecords();

// Set pagination start
$versionList->start = $pagination->getStart($currentPage, $versionList->limit, $totalRecords);

// Get records for current page
$records = $versionList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $versionList->start + 1;
$displayEnd = min($versionList->start + $versionList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "software_version_list.php";
$pageString = "?limit=" . $versionList->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $versionList->deleteVersion((int)$id);
    }
    header("Location: software_version_list.php");
    exit;
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrative Panel - Software Versions</title>
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
        
        $.post("ajax-files/changesoftversionStatus.php", 
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
    .version-image {
        max-width: 150px;
        max-height: 40px;
        border: 1px solid #ddd;
        padding: 2px;
        border-radius: 3px;
        background: #fff;
    }
    .link-cell {
        max-width: 200px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .link-cell a {
        color: #428bca;
        text-decoration: none;
    }
    .link-cell a:hover {
        text-decoration: underline;
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
                    
                    <h2>Manage Software Versions &nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Version List</h2>
                    
                    <div id="whatsNew-grid" class="grid-view">
                        
                        <!-- Toolbar -->
                        <table style="width:100%; margin-bottom:10px;">
                            <tr>
                                <td style="width:100px;">
                                    <input name="btnDelete" type="submit" value="Delete" class="delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete the selected records?')" />
                                </td>
                                <td style="width:200px;">
                                    <?php echo $displayRange; ?>
                                </td>
                                <td align="right">
                                    <div class="summary">
                                        <div class="form no-border" style="margin:0; padding:2px 3px; display:inline-block; vertical-align:middle;">
                                            <select name="limit" id="limit" 
                                                    onchange="window.location.href='software_version_list.php?page=<?php echo $currentPage; ?>&limit='+this.value;">
                                                <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo ($i == $versionList->limit) ? 'selected="selected"' : ''; ?>>
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
                        
                        <!-- Versions Table -->
                        <table class="items">
                            <thead>
                                <tr>
                                    <th class="checkbox" style="width:40px;">
                                        <input name="check_all" value="yes" id="check_all" type="checkbox" onClick="checkedAll();">
                                    </th>
                                    <th style="width:120px;"><strong>Image</strong></th>
                                    <th style="width:200px;"><strong>Link</strong></th>
                                    <th style="width:90px;"><strong>Status</strong></th>
                                    <th style="width:90px;"><strong>Change Status</strong></th>
                                    <th class="action" style="width:50px;"><strong>Actions</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recordCount > 0): ?>
                                    <?php $rowNum = 0; ?>
                                    <?php while ($row = mysqli_fetch_object($records)): ?>
                                        <tr <?php echo ($rowNum % 2 == 1) ? 'class="row-clr"' : ''; ?>>
                                            <td class="checkbox">
                                                <input name="cb[]" type="checkbox" value="<?php echo (int)$row->isv_id; ?>" />
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <?php if (!empty($row->isv_image) && file_exists("../image/" . $row->isv_image)): ?>
                                                    <img src="../image/<?php echo htmlspecialchars($row->isv_image, ENT_QUOTES, 'UTF-8'); ?>" 
                                                         class="version-image" alt="Version Image"/>
                                                <?php else: ?>
                                                    <span class="text-muted">No Image</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td class="link-cell">
                                                <?php if (!empty($row->isv_link)): ?>
                                                    <a href="<?php echo htmlspecialchars($row->isv_link, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" title="<?php echo htmlspecialchars($row->isv_link, ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlspecialchars($versionList->truncateLink($row->isv_link), ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No link</span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <?php echo $versionList->getStatusBadge((int)($row->isv_status ?? 0)); ?>
                                            </td>
                                            
                                            <td style="text-align:center">
                                                <select onchange="changeStatus(this.value, '<?php echo (int)$row->isv_id; ?>')">
                                                    <option value="">Select</option>
                                                    <?php if ((int)($row->isv_status ?? 0) == 1): ?>
                                                        <option value="0">Deactivate</option>
                                                    <?php else: ?>
                                                        <option value="1">Activate</option>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            
                                            <td class="action" align="center">
                                                <a href="software_version_edit.php?token=<?php echo rand(1000, 9999) . md5((string)$row->isv_id); ?>" 
                                                   title="Edit">
                                                    <img alt="edit" src="images/edit.jpg" border="0">
                                                </a>
                                                <a href="<?php echo $versionList->getDeleteLink((int)$row->isv_id); ?>" 
                                                   title="Delete" 
                                                   onclick="return confirm('Are you sure you want to delete this version? This will also delete the associated image.')">
                                                    <img alt="delete" src="images/delete.jpg" border="0">
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $rowNum++; ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:20px;">
                                            No software versions found. 
                                            <a href="software_version_add.php">Add your first version</a>.
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
                                $versionList->limit, 
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
        font-style: italic;
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