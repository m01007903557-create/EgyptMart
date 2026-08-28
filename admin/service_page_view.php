<?php
/**
 * File: service_page_view.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة محتوى صفحة الخدمات
 * View and manage service page content
 * 
 * Features:
 * - عرض جميع عناصر صفحة الخدمات
 * - تفعيل/تعطيل العناصر
 * - تعديل العناصر
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
 * Class ServicePageManager
 * 
 * Handles service page content management operations
 */
class ServicePageManager {
    
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
     * Delete (deactivate) content
     * 
     * @param int $id Content ID
     * @return bool Success status
     */
    public function deleteContent(int $id): bool {
        $sql = "UPDATE servicepage_content SET spc_status = 0 WHERE spc_id = ?";
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
     * Update content status
     * 
     * @param int $id Content ID
     * @param int $status New status
     * @return bool Success status
     */
    public function updateStatus(int $id, int $status): bool {
        $sql = "UPDATE servicepage_content SET spc_status = ? WHERE spc_id = ?";
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
     * @param int $id Content ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "service_page_view.php?{$queryString}&action=del&fid={$id}";
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
     * Truncate text to specified length
     * 
     * @param string $text Text to truncate
     * @param int $length Maximum length
     * @return string Truncated text
     */
    public function truncateText(string $text, int $length = 100): string {
        // Remove HTML tags
        $text = strip_tags($text);
        
        if (strlen($text) <= $length) {
            return $text;
        }
        
        return substr($text, 0, $length) . '...';
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize service page manager
$pageManager = new ServicePageManager($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $pageManager->deleteContent((int)$_GET['fid']);
    header("Location: service_page_view.php");
    exit;
}

// Set pagination limits
$pageManager->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;

// Build base query
$baseQuery = "SELECT * FROM servicepage_content WHERE 1 ORDER BY spc_id ASC";

// Get total records for pagination
$pageManager->setSql($baseQuery);
$totalRecords = $pageManager->getTotalRecords();

// Set pagination start
$pageManager->start = $pagination->getStart($currentPage, $pageManager->limit, $totalRecords);

// Get records for current page
$records = $pageManager->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $pageManager->start + 1;
$displayEnd = min($pageManager->start + $pageManager->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "service_page_view.php";
$pageString = "?limit=" . $pageManager->limit . "&page=";
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrative Panel - Service Page Content</title>
<link rel="shortcut icon" href="" type="image/x-icon">

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
        
        $.post("ajax-files/changeServiceHeadingStatus.php", 
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
    .content-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .action-icons img {
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
                    
                    <h1>Manage Webpage &nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Service Page Content</h1>
                    
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
                                                    onchange="window.location.href='service_page_view.php?page=<?php echo $currentPage; ?>&limit='+this.value;">
                                                <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo ($i == $pageManager->limit) ? 'selected="selected"' : ''; ?>>
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
                        
                        <!-- Content Table -->
                        <table class="items">
                            <thead>
                                <tr>
                                    <th class="checkbox" style="width:10px;"><!-- Checkbox placeholder --></th>
                                    <th class="usr-name" style="width:200px;"><strong>Heading</strong></th>
                                    <th class="usr-name" style="width:200px;"><strong>Content Preview</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>Status</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>Change Status</strong></th>
                                    <th class="action" style="width:50px;"><strong>Action</strong></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recordCount > 0): ?>
                                    <?php $rowNum = 0; ?>
                                    <?php while ($row = mysqli_fetch_object($records)): ?>
                                        <tr <?php echo ($rowNum % 2 == 1) ? 'class="row-clr"' : ''; ?>>
                                            <td class="checkbox"></td>
                                            
                                            <td class="usr-name">
                                                <b><?php echo htmlspecialchars($row->spc_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></b>
                                            </td>
                                            
                                            <td class="usr-name">
                                                <div class="content-preview">
                                                    <?php echo htmlspecialchars($pageManager->truncateText($row->spc_heading ?? '', 150), ENT_QUOTES, 'UTF-8'); ?>
                                                </div>
                                            </td>
                                            
                                            <td class="usr-name" style="text-align:center">
                                                <?php echo $pageManager->getStatusBadge((int)($row->spc_status ?? 0)); ?>
                                            </td>
                                            
                                            <td class="usr-name" style="text-align:center">
                                                <select onchange="changeStatus(this.value, '<?php echo (int)$row->spc_id; ?>')">
                                                    <option value="">Select</option>
                                                    <?php if ((int)($row->spc_status ?? 0) == 1): ?>
                                                        <option value="0">Deactivate</option>
                                                    <?php else: ?>
                                                        <option value="1">Activate</option>
                                                    <?php endif; ?>
                                                </select>
                                            </td>
                                            
                                            <td class="action" align="center">
                                                <a href="service_page_edit.php?sid=<?php echo (int)$row->spc_id; ?>" title="Edit">
                                                    <img alt="edit" src="images/edit.jpg" border="0">
                                                </a>
                                            </td>
                                        </tr>
                                        <?php $rowNum++; ?>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" style="text-align:center; padding:20px;">
                                            No service page content found.
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
                                $pageManager->limit, 
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
</body>
</html>

<?php ob_end_flush(); ?>