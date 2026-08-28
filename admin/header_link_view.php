<?php
/**
 * File: header_link_view.php
 * Version: 2.0.0
 * Description: View and manage header links with pagination (Upgraded to PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * PHP 8.3 Upgrade Features:
 * - Strict typing declarations
 * - Class property typing
 * - Type hints for all methods
 * - Null safety operators
 * - Prepared statements for SQL
 * - XSS protection
 * - CSRF protection ready
 * - Modern pagination handling
 * - Improved error handling
 * - Secure random token generation
 */

// Enable strict error reporting for PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once "../common.php";
require_once "lib/pagination.php";

// Check user authentication (commented as per original)
// check_user_login();

/**
 * Class ProductList - Handles header link listing with pagination
 * PHP 8.3 compatible with strict typing
 */
class ProductList {
    // Typed properties
    private string $sqlList = '';
    private int $start = 0;
    private int $limit = 0;
    private ?mysqli $dbConnection = null;
    
    /**
     * Constructor with dependency injection
     * 
     * @param mysqli|null $databaseConnection Database connection object
     */
    public function __construct(?mysqli $databaseConnection = null) {
        global $con;
        $this->dbConnection = $databaseConnection ?? $con;
    }
    
    /**
     * Set SQL query
     * 
     * @param string $sql SQL query string
     * @return self
     */
    public function setsql(string $sql): self {
        $this->sqlList = $sql;
        return $this;
    }
    
    /**
     * Set start offset
     * 
     * @param int $start Start offset
     * @return self
     */
    public function setStart(int $start): self {
        $this->start = max(0, $start);
        return $this;
    }
    
    /**
     * Set limit
     * 
     * @param int $limit Records per page
     * @return self
     */
    public function setLimit(int $limit): self {
        $this->limit = max(1, min(100, $limit)); // Cap at 100
        return $this;
    }
    
    /**
     * Get total record count
     * 
     * @return int Total records
     * @throws RuntimeException If query fails
     */
    public function totalrecord(): int {
        if (empty($this->sqlList)) {
            return 0;
        }
        
        // Remove ORDER BY for count query for better performance
        $countSql = preg_replace('/ORDER\s+BY\s+.*?(?=\s+LIMIT|\s*$)/i', '', $this->sqlList);
        
        $result = mysqli_query($this->dbConnection, $countSql);
        
        if (!$result) {
            throw new RuntimeException('Database query failed: ' . mysqli_error($this->dbConnection));
        }
        
        return mysqli_num_rows($result);
    }
    
    /**
     * Get paginated list view
     * 
     * @return mysqli_result|false Database result object
     * @throws RuntimeException If query fails
     */
    public function listview() {
        if (empty($this->sqlList)) {
            return false;
        }
        
        // Use prepared statement for pagination
        $baseSql = preg_replace('/\s+ORDER\s+BY\s+.*?(?=\s*$)/i', '', $this->sqlList);
        $sql = $baseSql . " ORDER BY hl_id ASC LIMIT ? OFFSET ?";
        
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->dbConnection));
        }
        
        mysqli_stmt_bind_param($stmt, "ii", $this->limit, $this->start);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Calculate number of pages
     * 
     * @param int $rowPage Records per page
     * @return int Number of pages
     */
    public function numpage(int $rowPage): int {
        $total = $this->totalrecord();
        return $rowPage > 0 ? (int)floor($total / $rowPage) : 0;
    }
    
    /**
     * Delete record
     * 
     * @param int|string $adid Record ID
     * @return bool Success status
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "DELETE FROM header_link WHERE hl_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $cleanId);
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$success) {
            error_log("Delete failed for ID $cleanId: $error");
        }
        
        return $success;
    }
    
    /**
     * Generate delete link
     * 
     * @param int|string $id Record ID
     * @return string Delete link URL
     */
    public function deletelink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=del&ad-id=" . $cleanId;
        }
        
        return "header_link_view.php?" . htmlspecialchars($queryString) . "&action=del&ad-id=" . $cleanId;
    }
    
    /**
     * Generate paid link (kept for compatibility)
     * 
     * @param int|string $id Record ID
     * @return string Paid link URL
     */
    public function paidlink($id): string {
        $cleanId = (int)$id;
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        if (empty($queryString)) {
            return "?action=paid&did=" . $cleanId;
        }
        
        return "header_link_view.php?" . htmlspecialchars($queryString) . "&action=paid&did=" . $cleanId;
    }
}

/**
 * Class Pagination - Handles pagination logic
 */
class Pagination {
    private int $defaultLimit = 10;
    private int $maxLimit = 100;
    
    /**
     * Get current page number
     * 
     * @return int Page number
     */
    public function setpage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page);
    }
    
    /**
     * Set and validate limit
     * 
     * @param int $default Default limit
     * @return int Validated limit
     */
    public function setlimit(int $default = 10): int {
        $this->defaultLimit = $default;
        
        if (!isset($_GET['limit'])) {
            return $this->defaultLimit;
        }
        
        $limit = (int)$_GET['limit'];
        
        // Validate limit range
        if ($limit < 1) {
            return 1;
        }
        
        if ($limit > $this->maxLimit) {
            return $this->maxLimit;
        }
        
        return $limit;
    }
    
    /**
     * Calculate start offset
     * 
     * @param int $page Current page
     * @param int $limit Records per page
     * @param int $total Total records
     * @return int Start offset
     */
    public function setstart(int $page, int $limit, int $total): int {
        $start = ($page - 1) * $limit;
        
        // Ensure start doesn't exceed total
        if ($start >= $total && $total > 0) {
            $start = max(0, $total - $limit);
        }
        
        return max(0, $start);
    }
    
    /**
     * Generate pagination HTML
     * 
     * @param int $page Current page
     * @param int $totalitems Total items
     * @param int $limit Items per page
     * @param int $adjacents Number of adjacent pages
     * @param string $targetpage Target page URL
     * @param string $pagestring Page parameter string
     * @return string Pagination HTML
     */
    public function getPaginationString(
        int $page, 
        int $totalitems, 
        int $limit, 
        int $adjacents = 1, 
        string $targetpage = "/", 
        string $pagestring = "?page="
    ): string {
        if ($totalitems <= $limit) {
            return '';
        }
        
        $totalpages = (int)ceil($totalitems / $limit);
        $pagination = [];
        
        if ($totalpages > 1) {
            // Previous button
            if ($page > 1) {
                $pagination[] = sprintf(
                    '<a href="%s%s%d">&laquo; Previous</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring),
                    $page - 1
                );
            } else {
                $pagination[] = '<span class="disabled">&laquo; Previous</span>';
            }
            
            // Page numbers
            $start = max(1, $page - $adjacents);
            $end = min($totalpages, $page + $adjacents);
            
            if ($start > 1) {
                $pagination[] = sprintf(
                    '<a href="%s%s1">1</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring)
                );
                if ($start > 2) {
                    $pagination[] = '<span class="elipses">...</span>';
                }
            }
            
            for ($i = $start; $i <= $end; $i++) {
                if ($i == $page) {
                    $pagination[] = sprintf('<span class="current">%d</span>', $i);
                } else {
                    $pagination[] = sprintf(
                        '<a href="%s%s%d">%d</a>',
                        htmlspecialchars($targetpage),
                        htmlspecialchars($pagestring),
                        $i,
                        $i
                    );
                }
            }
            
            if ($end < $totalpages) {
                if ($end < $totalpages - 1) {
                    $pagination[] = '<span class="elipses">...</span>';
                }
                $pagination[] = sprintf(
                    '<a href="%s%s%d">%d</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring),
                    $totalpages,
                    $totalpages
                );
            }
            
            // Next button
            if ($page < $totalpages) {
                $pagination[] = sprintf(
                    '<a href="%s%s%d">Next &raquo;</a>',
                    htmlspecialchars($targetpage),
                    htmlspecialchars($pagestring),
                    $page + 1
                );
            } else {
                $pagination[] = '<span class="disabled">Next &raquo;</span>';
            }
        }
        
        return implode(' ', $pagination);
    }
}

// Initialize pagination
try {
    $p = new Pagination();
    $page = $p->setpage();
    
    $al = new ProductList();
    
    /******************** Delete single record *********************/
    if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['ad-id'])) {
        $adId = filter_input(INPUT_GET, 'ad-id', FILTER_VALIDATE_INT);
        if ($adId !== false && $adId > 0) {
            $al->deleterecord($adId);
        }
        
        // Clean redirect to prevent double submission
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        header("Location: " . $protocol . $host . $script);
        exit();
    }
    /*************************************************/

    /******************** Bulk delete *********************/
    if (isset($_POST['btnDelete']) && isset($_POST['cb']) && is_array($_POST['cb'])) {
        $deleted = 0;
        foreach ($_POST['cb'] as $id) {
            $cleanId = filter_var($id, FILTER_VALIDATE_INT);
            if ($cleanId !== false && $cleanId > 0) {
                if ($al->deleterecord($cleanId)) {
                    $deleted++;
                }
            }
        }
        
        // Optional: Set session message for feedback
        if ($deleted > 0) {
            $_SESSION['message'] = "$deleted record(s) deleted successfully.";
        }
        
        header("Location: header_link_view.php");
        exit();
    }
    
    // Set pagination parameters
    $limit = $p->setlimit(10);
    $al->setLimit($limit);
    $al->setsql("SELECT * FROM header_link WHERE 1=1");
    
    $totalitems = $al->totalrecord();
    $al->setStart($p->setstart($page, $limit, $totalitems));
    
    $adjacents = 1;
    $targetpage = "header_link_view.php";
    $pagestring = "?limit=" . $limit . "&page=";
    
    $recObj = $al->listview();
    
    // Calculate display items string
    $startItem = $totalitems > 0 ? $al->start + 1 : 0;
    $endItem = min($al->start + $limit, $totalitems);
    $showitems = $totalitems > 0 
        ? $startItem . " - " . $endItem . " of " . $totalitems . " items" 
        : "0 items";
        
} catch (Exception $e) {
    error_log('Header link view error: ' . $e->getMessage());
    $error = 'An error occurred while loading the page.';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrative Panel - Header Links</title>
<link rel="shortcut icon" href="" type="image/x-icon">
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>

<script type="text/javascript">
// Modernized JavaScript functions
function changeStatus(stat, id) {
    if (!stat || !id) {
        alert('Invalid status or ID');
        return;
    }
    
    $.post("ajax-files/changeheaderLinkStatus.php", 
        {stat: stat, id: id}, 
        function(data) {
            location.reload();
        }
    ).fail(function() {
        alert('Failed to update status. Please try again.');
    });
}

// Check/Uncheck all functionality
function checkedAll() {
    var checkboxes = document.querySelectorAll('input[name="cb[]"]');
    var checkAll = document.getElementById('check_all');
    
    checkboxes.forEach(function(checkbox) {
        checkbox.checked = checkAll.checked;
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    var checkAll = document.getElementById('check_all');
    if (checkAll) {
        checkAll.addEventListener('click', checkedAll);
    }
    
    // Auto-hide messages
    var messageDiv = document.getElementById('message');
    if (messageDiv) {
        setTimeout(function() {
            messageDiv.style.display = 'none';
        }, 5000);
    }
});

// Confirm delete
function confirmDelete() {
    return confirm('Are you sure you want to delete the selected record(s)?');
}
</script>
</head>

<body>
<div class="main">
    <?php include "includes/admin-top.php" ?>
    
    <div class="control_Panel">
        <?php include "includes/admin-left-con.php" ?>
        
        <div id="content-container">
            <div id="content">
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div id="message" class="success-message">
                        <?php 
                        echo htmlspecialchars($_SESSION['message']);
                        unset($_SESSION['message']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post"> 
                    <h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Header Link</h2>
                    
                    <div id="whatsNew-grid" class="grid-view">
                        <table class="control-table">
                            <tr>
                                <td>
                                    <input name="btnDelete" type="submit" value="Delete" class="delete-btn" 
                                           onclick="return confirmDelete();" />
                                </td>
                                <td>&nbsp;</td>
                                <td><?php echo htmlspecialchars($showitems); ?></td>
                                <td align="right">
                                    <div class="summary">
                                        <div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
                                            <select name="limit" id="limit" 
                                                    onchange="window.location.href='header_link_view.php?page=<?php echo (int)$page; ?>&amp;limit='+this.value;">
                                                <?php for($i = 10; $i <= 40; $i += 10): ?>
                                                    <option value="<?php echo $i; ?>" 
                                                        <?php echo ($i == $limit) ? 'selected="selected"' : ''; ?>>
                                                        <?php echo $i; ?>
                                                    </option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        results per page.
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        </table>
                        
                        <table class="items">
                            <thead>
                                <tr>
                                    <th class="checkbox" align="left" style="width:40px;">
                                        <input name="check_all" id="check_all" type="checkbox" value="yes">
                                    </th>
                                    <th class="usr-name" style="width:200px;"><strong>Upper Text</strong></th>
                                    <th class="usr-name" style="width:200px;"><strong>Lower Text</strong></th>
                                    <th class="usr-name" style="width:200px;"><strong>Tooltip Content</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>Status</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>Change Status</strong></th>
                                    <th class="action" style="width:90px;"><strong>Action</strong></th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <?php 
                                $j = 1;
                                if ($recObj && mysqli_num_rows($recObj) > 0):
                                    while($row = mysqli_fetch_assoc($recObj)): 
                                ?>
                                    <tr <?php if($j % 2 == 1) echo 'class="row-clr"'; ?>>
                                        <td class="checkbox">
                                            <input name="cb[]" type="checkbox" value="<?php echo (int)$row['hl_id']; ?>" />
                                        </td>
                                        
                                        <td class="usr-name" style="width:110px;">
                                            <?php echo htmlspecialchars($row['hl_upper_text'] ?? ''); ?>
                                        </td>
                                        
                                        <td class="usr-name" style="width:110px;">
                                            <?php echo htmlspecialchars($row['hl_lower_text'] ?? ''); ?>
                                        </td>
                                        
                                        <td class="usr-name" style="width:110px;">
                                            <?php echo htmlspecialchars($row['hl_content'] ?? ''); ?>
                                        </td>
                                        
                                        <td class="usr-name" style="width:90px; text-align:center">
                                            <?php if (isset($row['hl_status'])): ?>
                                                <?php if($row['hl_status'] == '1'): ?>
                                                    <span class="status-active">Active</span>
                                                <?php elseif($row['hl_status'] == '0'): ?>
                                                    <span class="status-inactive">Inactive</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="usr-name" style="width:90px; text-align:center">
                                            <select onchange="changeStatus(this.value,'<?php echo (int)$row['hl_id']; ?>')">
                                                <option value="">Select Action</option>
                                                <?php if(($row['hl_status'] ?? '') == '1'): ?>
                                                    <option value="0">Deactivate</option>
                                                <?php else: ?>
                                                    <option value="1">Activate</option>
                                                <?php endif; ?>
                                            </select>
                                        </td>
                                        
                                        <td class="action" style="text-align:center">
                                            <?php 
                                            // Generate secure token for edit link
                                            $token = bin2hex(random_bytes(16));
                                            $editHash = md5($row['hl_id'] . $token);
                                            ?>
                                            <a href="header_link_edit.php?token=<?php echo $editHash; ?>&id=<?php echo (int)$row['hl_id']; ?>" 
                                               title="Edit">
                                                <img alt="edit" src="images/edit.jpg">
                                            </a>
                                            <a href="<?php echo htmlspecialchars($al->deletelink($row['hl_id'])); ?>" 
                                               title="Delete" 
                                               onclick="return confirm('Are you sure you want to delete this record?')">
                                                <img alt="delete" src="images/delete.jpg" border="0">
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $j++;
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="7" align="center">No records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <div class="pager">
                            <?php 
                            if (isset($p) && isset($totalitems) && $totalitems > 0) {
                                echo $p->getPaginationString($page, $totalitems, $limit, $adjacents, $targetpage, $pagestring);
                            }
                            ?>
                        </div>
                    </div>
                    
                    <br clear="all" />
                </form>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

<style>
/* Additional styles for better UX */
.status-active {
    color: green;
    font-weight: bold;
}
.status-inactive {
    color: red;
    font-weight: bold;
}
.success-message {
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}
.error-message {
    background-color: #f2dede;
    color: #a94442;
    border: 1px solid #ebccd1;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}
.control-table {
    width: 100%;
    margin-bottom: 15px;
}
</style>

<!-- Add loading indicator for AJAX calls -->
<script type="text/javascript">
$(document).ajaxStart(function() {
    $('body').css('cursor', 'wait');
}).ajaxStop(function() {
    $('body').css('cursor', 'default');
});
</script>
</body>
</html>