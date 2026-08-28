<?php
/**
 * File: suspended_gig-view.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض وإدارة الخدمات المعلقة
 * View and manage suspended gigs
 * 
 * Features:
 * - عرض جميع الخدمات المعلقة (الحالة 4)
 * - حذف الخدمات المعلقة
 * - تغيير حالة الخدمات
 * - عرض تفاصيل الخدمة
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
check_admin_Login();

/**
 * Class SuspendedGigList
 * 
 * Handles suspended gig listing operations
 */
class SuspendedGigList {
    
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
     * Delete (deactivate) gig
     * 
     * @param int $id Gig ID
     * @return bool Success status
     */
    public function deleteGig(int $id): bool {
        $sql = "UPDATE gig SET g_status = 0 WHERE g_id = ?";
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
     * Update gig status
     * 
     * @param int $id Gig ID
     * @param int $status New status
     * @return bool Success status
     */
    public function updateStatus(int $id, int $status): bool {
        $sql = "UPDATE gig SET g_status = ? WHERE g_id = ?";
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
     * @param int $id Gig ID
     * @return string Delete URL
     */
    public function getDeleteLink(int $id): string {
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&fid={$id}";
        }
        
        return "suspended_gig-view.php?{$queryString}&action=del&fid={$id}";
    }
    
    /**
     * Get gig details
     * 
     * @param int $id Gig ID
     * @return array|null Gig details
     */
    public function getGigDetails(int $id): ?array {
        $sql = "SELECT g.*, scat_name, cat_name 
                FROM gig g
                JOIN subcategory s ON g.g_scat_id = s.scat_id
                JOIN category c ON s.scat_cat_id = c.cat_id
                WHERE g.g_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get status badge
     * 
     * @param int $status Status code
     * @return string HTML badge
     */
    public function getStatusBadge(int $status): string {
        return match ($status) {
            1 => '<span class="badge badge-success">Active</span>',
            2 => '<span class="badge badge-info">Pending</span>',
            3 => '<span class="badge badge-warning">Require Modification</span>',
            4 => '<span class="badge badge-danger">Suspended</span>',
            5 => '<span class="badge badge-default">Denied</span>',
            0 => '<span class="badge badge-default">Deleted</span>',
            default => '<span class="badge badge-default">Unknown</span>'
        };
    }
}

// Initialize pagination
$pagination = new Pagination();
$currentPage = $pagination->getCurrentPage();

// Initialize gig list
$gigList = new SuspendedGigList($con);

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
    $gigList->deleteGig((int)$_GET['fid']);
    header("Location: suspended_gig-view.php");
    exit;
}

// Set pagination limits
$gigList->limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

// Build base query for suspended gigs
$baseQuery = "SELECT g.*, s.scat_name, c.cat_name 
              FROM gig g
              JOIN subcategory s ON g.g_scat_id = s.scat_id
              JOIN category c ON s.scat_cat_id = c.cat_id
              WHERE g.g_status = 4
              ORDER BY g.g_id DESC";

// Get total records for pagination
$gigList->setSql($baseQuery);
$totalRecords = $gigList->getTotalRecords();

// Set pagination start
$gigList->start = $pagination->getStart($currentPage, $gigList->limit, $totalRecords);

// Get records for current page
$records = $gigList->getRecords();
$recordCount = $records ? mysqli_num_rows($records) : 0;

// Calculate display range
$displayStart = $gigList->start + 1;
$displayEnd = min($gigList->start + $gigList->limit, $totalRecords);
$displayRange = $displayStart . " - " . $displayEnd . " of " . $totalRecords . " items";

// Build pagination parameters
$adjacents = 1;
$targetPage = "suspended_gig-view.php";
$pageString = "?limit=" . $gigList->limit . "&page=";

// Handle bulk delete
if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
    foreach ($_POST['cb'] as $id) {
        $gigList->deleteGig((int)$id);
    }
    header("Location: suspended_gig-view.php");
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

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
    
    function changeStatus(id, status) {
        if (status === '' || status === 'sel') return;
        
        if (confirm('Are you sure you want to change the status of this gig?')) {
            $.post("change_gig_status.php", {
                id: id,
                stat: status
            }, function(data) {
                location.reload();
            }).fail(function() {
                alert('Failed to update status. Please try again.');
            });
        }
    }
    
    function viewDetails(id) {
        window.location.href = 'gig-details.php?token=' + id;
    }
</script>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <form name="myform" id="myform" method="post">
                
                <h2>&rsaquo;&nbsp;&nbsp;Manage Gigs&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Suspended Gigs</h2>
                
                <div id="whatsNew-grid" class="grid-view">
                    
                    <!-- Toolbar -->
                    <table style="width:100%; margin-bottom:10px;">
                        <tr>
                            <td style="width:100px;">
                                <input name="btnDelete" type="submit" value="Delete" class="delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete the selected records?')" />
                            </td>
                            <td style="width:120px;">
                                <input type="button" class="delete-btn" onClick="window.location ='gig-add.php'" value="Add New Gig">
                            </td>
                            <td style="width:200px;">
                                <?php echo $displayRange; ?>
                            </td>
                            <td align="right">
                                <div class="summary">
                                    <div class="form no-border" style="margin:0; padding:2px 3px; display:inline-block; vertical-align:middle;">
                                        <select name="limit" id="limit" onchange="window.location.href='suspended_gig-view.php?page=<?php echo $currentPage; ?>&limit='+this.value;">
                                            <?php for($i = 10; $i <= 40; $i += 10): ?>
                                                <option value="<?php echo $i; ?>" <?php echo ($i == $gigList->limit) ? 'selected="selected"' : ''; ?>>
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
                    
                    <!-- Gigs Table -->
                    <table class="items">
                        <thead>
                            <tr>
                                <th class="checkbox" style="width:40px;">
                                    <input name="check_all" value="yes" id="check_all" type="checkbox" onClick="checkedAll();">
                                </th>
                                <th style="width:180px;"><strong>Title</strong></th>
                                <th style="width:250px;"><strong>Category</strong></th>
                                <th style="width:400px;"><strong>Description</strong></th>
                                <th style="width:100px;"><strong>Status</strong></th>
                                <th class="action" style="width:150px;"><strong>Actions</strong></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recordCount > 0): ?>
                                <?php $rowNum = 1; ?>
                                <?php while ($row = mysqli_fetch_object($records)): ?>
                                    <tr <?php echo ($rowNum % 2 == 1) ? 'class="row-clr"' : ''; ?>>
                                        <td class="checkbox">
                                            <input name="cb[]" type="checkbox" value="<?php echo (int)$row->g_id; ?>" />
                                        </td>
                                        
                                        <td>
                                            <strong><?php echo htmlspecialchars($row->g_title ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <br>
                                            <small class="text-muted">ID: <?php echo (int)$row->g_id; ?></small>
                                        </td>
                                        
                                        <td>
                                            <?php 
                                            echo htmlspecialchars(stripslashes($row->scat_name ?? ''), ENT_QUOTES, 'UTF-8');
                                            echo '<br><small>(' . htmlspecialchars(stripslashes($row->cat_name ?? ''), ENT_QUOTES, 'UTF-8') . ')</small>';
                                            ?>
                                        </td>
                                        
                                        <td>
                                            <?php 
                                            $description = strip_tags($row->g_description ?? '');
                                            echo htmlspecialchars(substr($description, 0, 150) . (strlen($description) > 150 ? '...' : ''), ENT_QUOTES, 'UTF-8');
                                            ?>
                                        </td>
                                        
                                        <td>
                                            <?php echo $gigList->getStatusBadge((int)($row->g_status ?? 4)); ?>
                                        </td>
                                        
                                        <td class="action">
                                            <div class="btn-group">
                                                <a href="gig-details.php?token=<?php echo md5((string)$row->g_id); ?>" 
                                                   class="btn btn-xs btn-info" title="View Details">
                                                    <i class="icon-eye-open bigger-120"></i> Details
                                                </a>
                                                
                                                <select onchange="changeStatus('<?php echo (int)$row->g_id; ?>', this.value)" 
                                                        style="width:120px; margin-left:5px;">
                                                    <option value="">Change Status</option>
                                                    <option value="1">Activate</option>
                                                    <option value="2">Pending</option>
                                                    <option value="3">Require Modification</option>
                                                    <option value="5">Deny</option>
                                                    <option value="0">Delete</option>
                                                </select>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php $rowNum++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding:20px;">
                                        No suspended gigs found.
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
                            $gigList->limit, 
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
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<!-- Additional styles -->
<style>
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
        vertical-align: middle;
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
    .btn-group {
        display: flex;
        gap: 5px;
        align-items: center;
    }
    .btn-xs {
        padding: 3px 8px;
        font-size: 11px;
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
    .badge-info {
        background-color: #5bc0de;
        color: white;
    }
    .badge-warning {
        background-color: #f0ad4e;
        color: white;
    }
    .badge-danger {
        background-color: #d9534f;
        color: white;
    }
    .badge-default {
        background-color: #777;
        color: white;
    }
    .text-muted {
        color: #999;
        font-size: 11px;
    }
    select {
        padding: 3px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-size: 11px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>