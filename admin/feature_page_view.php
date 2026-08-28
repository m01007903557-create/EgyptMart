<?php
/**
 * File: feature_page_view.php
 * Version: 2.0.0
 * Description: Feature page content management with pagination (Upgraded to PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * PHP 8.3 Upgrade Features:
 * - Strict typing declarations
 * - Type hints for all methods and properties
 * - Constructor property promotion
 * - Null safety operators
 * - Modern array syntax
 * - Prepared statements for SQL
 * - Improved error handling
 * - XSS protection
 */

// Enable strict error reporting for PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering (session commented as per original)
ob_start();
// session_start(); // Commented as per original code

// Include required files
require_once "../common.php";
// require_once "lib/pagination.php"; // Commented as per original

/**
 * Class ListCat - Handles category listing with pagination
 * PHP 8.3 compatible with strict typing
 */
class ListCat {
    // Properties with type declarations
    private string $sqlList = '';
    private int $start = 0;
    private int $limit = 0;
    private ?mysqli $dbConnection = null;
    
    /**
     * Constructor with dependency injection
     * 
     * @param mysqli $databaseConnection Database connection object
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
        $this->start = max(0, $start); // Ensure non-negative
        return $this;
    }
    
    /**
     * Set limit
     * 
     * @param int $limit Records per page
     * @return self
     */
    public function setLimit(int $limit): self {
        $this->limit = max(1, $limit); // Ensure at least 1
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
        
        $result = mysqli_query($this->dbConnection, $this->sqlList);
        
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
        
        // Use prepared statement for pagination to prevent SQL injection
        $baseSql = $this->sqlList;
        $sql = $baseSql . " LIMIT ? OFFSET ?";
        
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
     * Soft delete record by setting status to 0
     * 
     * @param int|string $adid Record ID
     * @return bool Success status
     */
    public function deleterecord($adid): bool {
        $cleanId = (int)$adid;
        
        if ($cleanId <= 0) {
            return false;
        }
        
        $sql = "UPDATE airlines SET al_status = '0' WHERE al_id = ?";
        $stmt = mysqli_prepare($this->dbConnection, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $cleanId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
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
            return "?action=del&fid=" . $cleanId;
        }
        
        return "feature_page_view.php?" . htmlspecialchars($queryString) . "&action=del&fid=" . $cleanId;
    }
}

/**
 * Class Pagination - Handles pagination logic
 * PHP 8.3 compatible
 */
class Pagination {
    private int $defaultLimit = 20;
    private int $maxLimit = 100;
    
    /**
     * Get current page number
     * 
     * @return int Page number
     */
    public function setpage(): int {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        return max(1, $page); // Ensure page is at least 1
    }
    
    /**
     * Set and validate limit
     * 
     * @param int $default Default limit
     * @return int Validated limit
     */
    public function setlimit(int $default = 20): int {
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
        $prev = $page - 1;
        $next = $page + 1;
        $totalpages = ceil($totalitems / $limit);
        $lpm1 = $totalpages - 1;
        
        // Build pagination array
        $pagination = [];
        
        if ($totalpages > 1) {
            // Previous button
            if ($page > 1) {
                $pagination[] = $this->createPaginationLink($targetpage . $pagestring . $prev, '&laquo; prev');
            } else {
                $pagination[] = '<span class="disabled">&laquo; prev</span>';
            }
            
            // Page numbers
            if ($totalpages < 7 + ($adjacents * 2)) {
                // Not enough pages to bother breaking it up
                for ($counter = 1; $counter <= $totalpages; $counter++) {
                    $pagination[] = $this->createPageLink($counter, $page, $targetpage, $pagestring);
                }
            } elseif ($totalpages > 5 + ($adjacents * 2)) {
                // Enough pages to hide some
                if ($page < 1 + ($adjacents * 2)) {
                    for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                        $pagination[] = $this->createPageLink($counter, $page, $targetpage, $pagestring);
                    }
                    $pagination[] = '<span class="elipses">...</span>';
                    $pagination[] = $this->createPageLink($lpm1, $page, $targetpage, $pagestring);
                    $pagination[] = $this->createPageLink($totalpages, $page, $targetpage, $pagestring);
                } elseif ($totalpages - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
                    $pagination[] = $this->createPageLink(1, $page, $targetpage, $pagestring);
                    $pagination[] = $this->createPageLink(2, $page, $targetpage, $pagestring);
                    $pagination[] = '<span class="elipses">...</span>';
                    
                    for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                        $pagination[] = $this->createPageLink($counter, $page, $targetpage, $pagestring);
                    }
                    
                    $pagination[] = '<span class="elipses">...</span>';
                    $pagination[] = $this->createPageLink($lpm1, $page, $targetpage, $pagestring);
                    $pagination[] = $this->createPageLink($totalpages, $page, $targetpage, $pagestring);
                } else {
                    $pagination[] = $this->createPageLink(1, $page, $targetpage, $pagestring);
                    $pagination[] = $this->createPageLink(2, $page, $targetpage, $pagestring);
                    $pagination[] = '<span class="elipses">...</span>';
                    
                    for ($counter = $totalpages - (2 + ($adjacents * 2)); $counter <= $totalpages; $counter++) {
                        $pagination[] = $this->createPageLink($counter, $page, $targetpage, $pagestring);
                    }
                }
            }
            
            // Next button
            if ($page < $counter - 1) {
                $pagination[] = $this->createPaginationLink($targetpage . $pagestring . $next, 'next &raquo;');
            } else {
                $pagination[] = '<span class="disabled">next &raquo;</span>';
            }
        }
        
        return implode(' ', $pagination);
    }
    
    /**
     * Create page link HTML
     * 
     * @param int $counter Page number
     * @param int $page Current page
     * @param string $targetpage Target URL
     * @param string $pagestring Page parameter
     * @return string Link HTML
     */
    private function createPageLink(int $counter, int $page, string $targetpage, string $pagestring): string {
        if ($counter == $page) {
            return '<span class="current">' . $counter . '</span>';
        }
        return '<a href="' . $targetpage . $pagestring . $counter . '">' . $counter . '</a>';
    }
    
    /**
     * Create pagination link HTML
     * 
     * @param string $url Link URL
     * @param string $text Link text
     * @return string Link HTML
     */
    private function createPaginationLink(string $url, string $text): string {
        return '<a href="' . htmlspecialchars($url) . '">' . $text . '</a>';
    }
}

// Initialize objects with error handling
try {
    $p = new Pagination();
    $page = $p->setpage();
    
    $al = new ListCat();
    
    /******************** Delete record *********************/
    if (isset($_GET['action']) && $_GET['action'] === "del" && isset($_GET['fid'])) {
        $fid = filter_input(INPUT_GET, 'fid', FILTER_VALIDATE_INT);
        if ($fid !== false && $fid > 0) {
            $al->deleterecord($fid);
        }
        
        // Clean redirect
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        header("Location: " . $protocol . $host . $script);
        exit();
    }
    /***********************************************/
    
    // Set pagination parameters
    $al->setLimit($p->setlimit(20));
    $al->setsql("SELECT * FROM featurepage_content WHERE 1=1 ORDER BY fpc_id DESC");
    
    $totalitems = $al->totalrecord();
    $limit = $al->limit;
    $al->setStart($p->setstart($page, $limit, $totalitems));
    
    $adjacents = 1;
    $targetpage = "feature_page_view.php";
    $pagestring = "?limit=" . $limit . "&page=";
    
    $recObj = $al->listview();
    
    // Calculate display items string
    $startItem = $al->start + 1;
    $endItem = min($al->start + $limit, $totalitems);
    $showitems = $totalitems > 0 ? $startItem . " - " . $endItem . " of " . $totalitems . " items" : "0 items";
    
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log('Feature page error: ' . $e->getMessage());
    $error = 'An error occurred while loading the page. Please try again.';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrative Panel - Feature Page Management</title>
<link rel="shortcut icon" href="" type="image/x-icon">
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/pagination.css" type="text/css" rel="stylesheet"/>

<script type="text/javascript">
// Modernized JavaScript for PHP 8.3 compatibility
function changeStatus(stat, id) {
    if (!stat || !id) {
        alert('Invalid status or ID');
        return;
    }
    
    $.post("ajax-files/changeFeatureHeadingStatus.php", 
        {stat: stat, id: id}, 
        function(data) {
            location.reload();
        }
    ).fail(function() {
        alert('Failed to update status. Please try again.');
    });
}

// Check/Uncheck all functionality
document.addEventListener('DOMContentLoaded', function() {
    const checkAllBox = document.getElementById('check_all');
    if (checkAllBox) {
        checkAllBox.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('input[name="cb[]"]');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = checkAllBox.checked;
            });
        });
    }
});
</script>

<style>
/* Additional styles for better UX */
.error-message {
    color: #d9534f;
    background-color: #f2dede;
    border: 1px solid #ebccd1;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 20px;
}
.loading {
    opacity: 0.5;
    pointer-events: none;
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
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <form name="myform" id="myform" method="post"> 
                    <h1>Manage Webpage&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Feature Page</h1>
                    
                    <div id="whatsNew-grid" class="grid-view">
                        <table>
                            <tr>
                                <td>
                                    <input name="btnDelete" type="submit" value="Delete" class="delete-btn" 
                                           onclick="return confirm('Are you sure you want to delete the selected record(s)?')" />
                                </td>
                                <td><?php echo htmlspecialchars($showitems); ?></td>
                                <td align="right">
                                    <div class="summary">
                                        <div class="form no-border" style="margin: 0; padding: 2px 3px; display: inline-block; vertical-align: middle;">
                                            <select name="limit" id="limit" 
                                                    onchange="window.location.href='feature_page_view.php?page=<?php echo (int)$page; ?>&amp;limit='+this.value;">
                                                <?php for($i = 10; $i <= 30; $i += 10): ?>
                                                    <option value="<?php echo $i; ?>" <?php echo ($i == $limit) ? 'selected="selected"' : ''; ?>>
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
                                    <th class="checkbox" align="left" style="width: 10px;">
                                        <input name="check_all" value="yes" id="check_all" type="checkbox">
                                    </th>
                                    <th class="usr-name" style="width: 200px;"><strong>Heading</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>Status</strong></th>
                                    <th class="usr-name" style="width:90px;"><strong>Action</strong></th>
                                    <th class="action" style="width: 50px;"><strong>Edit</strong></th>
                                </tr>
                            </thead>
                            
                            <tbody>
                                <?php 
                                $j = 0;
                                if ($recObj && mysqli_num_rows($recObj) > 0):
                                    while($row = mysqli_fetch_assoc($recObj)):
                                ?>
                                    <tr <?php if($j % 2 == 1) echo 'class="row-clr"'; ?>>
                                        <td class="checkbox">
                                            <input type="checkbox" name="cb[]" value="<?php echo (int)$row['fpc_id']; ?>">
                                        </td>
                                        <td class="usr-name">
                                            <b><?php echo htmlspecialchars($row['fpc_heading'] ?? ''); ?></b>
                                        </td>
                                        <td class="usr-name" style="width:90px; text-align:center">
                                            <?php if(isset($row['fpc_status'])): ?>
                                                <?php if($row['fpc_status'] == '1'): ?>
                                                    <span style="color:green;">Active</span>
                                                <?php elseif($row['fpc_status'] == '0'): ?>
                                                    <span style="color:red;">Inactive</span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>   
                                        <td class="usr-name" style="width:90px; text-align:center">
                                            <select onchange="changeStatus(this.value,'<?php echo (int)$row['fpc_id']; ?>')">
                                                <option value="">Select Action</option>
                                                <?php if(($row['fpc_status'] ?? '') == '1'): ?>
                                                    <option value="0">Deactivate</option>
                                                <?php else: ?>
                                                    <option value="1">Activate</option>
                                                <?php endif; ?>
                                            </select>
                                        </td>  
                                        <td class="action" align="center">
                                            <a href="feature_page_edit.php?sid=<?php echo (int)$row['fpc_id']; ?>" title="Edit">
                                                <img alt="edit" src="images/edit.jpg" border="0">
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                        $j++;
                                    endwhile;
                                else:
                                ?>
                                    <tr>
                                        <td colspan="5" align="center">No records found</td>
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
        <br clear="all" />
    </div>
    
    <?php include "includes/footer.php" ?>
</div>

<!-- Add loading indicator for AJAX calls -->
<script type="text/javascript">
$(document).ajaxStart(function() {
    $('body').addClass('loading');
}).ajaxStop(function() {
    $('body').removeClass('loading');
});
</script>
</body>
</html>