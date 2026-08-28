<?php
/**
 * File: employee_range.php
 * Version: 2.0.0
 * Description: Employee range management page (Upgraded to PHP 8.3)
 * Last modified: 2024-01-15
 */

// Enable strict error reporting for PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once "../common.php";
require_once "../lib/pagination.php";


// Check user authentication
check_admin_login();

// Initialize database connection
global $con;

// Handle POST and GET requests
handleRequests($con);

// Fetch employee ranges
$recObj = mysqli_query($con, "SELECT * FROM employee_range ORDER BY emprange_id ASC");

if (!$recObj) {
    die("Database query failed: " . mysqli_error($con));
}

/**
 * Check if string is empty after trimming whitespace
 * 
 * @param string|null $str Input string
 * @return bool True if empty, false otherwise
 */
function checkEmpty(?string $str): bool {
    if ($str === null || $str === '') {
        return true;
    }
    $check_empty = preg_replace('/\s+/', '', $str);
    return $check_empty === "";
}

/**
 * Sanitize input string
 * 
 * @param string $input Raw input
 * @return string Sanitized input
 */
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Handle all requests (POST and GET)
 * 
 * @param mysqli $con Database connection
 * @return void
 */
function handleRequests(mysqli $con): void {
    // Handle delete request
    if (isset($_GET['delete'])) {
        handleDelete($con, $_GET['delete']);
    }
    
    // Handle save request
    if (isset($_POST['save_mes'])) {
        handleSave($con, $_POST);
    }
    
    // Handle update request
    if (isset($_POST['update_mes'])) {
        handleUpdate($con, $_POST);
    }
}

/**
 * Handle delete operation
 * 
 * @param mysqli $con Database connection
 * @param string $id ID to delete
 * @return void
 */
function handleDelete(mysqli $con, string $id): void {
    $clean_id = mysqli_real_escape_string($con, $id);
    
    if (!checkEmpty($clean_id) && is_numeric($clean_id)) {
        $delete_query = "DELETE FROM employee_range WHERE emprange_id = ?";
        $stmt = mysqli_prepare($con, $delete_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $clean_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    redirectToSelf();
}

/**
 * Handle save operation
 * 
 * @param mysqli $con Database connection
 * @param array $postData POST data
 * @return void
 */
function handleSave(mysqli $con, array $postData): void {
    $name = isset($postData['business_type']) ? trim($postData['business_type']) : '';
    $clean_name = mysqli_real_escape_string($con, $name);
    
    if (!checkEmpty($clean_name)) {
        $insert_query = "INSERT INTO employee_range(emprange_type, emprange_status) VALUES(?, '1')";
        $stmt = mysqli_prepare($con, $insert_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $clean_name);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    redirectToSelf();
}

/**
 * Handle update operation
 * 
 * @param mysqli $con Database connection
 * @param array $postData POST data
 * @return void
 */
function handleUpdate(mysqli $con, array $postData): void {
    $name = isset($postData['business_type']) ? trim($postData['business_type']) : '';
    $id = isset($postData['business_type_id']) ? trim($postData['business_type_id']) : '';
    
    $clean_name = mysqli_real_escape_string($con, $name);
    $clean_id = mysqli_real_escape_string($con, $id);
    
    if (!checkEmpty($clean_name) && !checkEmpty($clean_id) && is_numeric($clean_id)) {
        $update_query = "UPDATE employee_range SET emprange_type = ? WHERE emprange_id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $clean_name, $clean_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    redirectToSelf();
}

/**
 * Redirect to current page
 * 
 * @return void
 */
function redirectToSelf(): void {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $redirect_url = $protocol . $host . $script;
    
    header("Location: " . $redirect_url);
    exit();
}

/**
 * Get edit data if edit parameter exists
 * 
 * @param mysqli $con Database connection
 * @return array|null Edit data or null
 */
function getEditData(mysqli $con): ?array {
    if (isset($_GET['edit'])) {
        $id = mysqli_real_escape_string($con, $_GET['edit']);
        
        if (is_numeric($id)) {
            $query = "SELECT * FROM employee_range WHERE emprange_id = ?";
            $stmt = mysqli_prepare($con, $query);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $data = mysqli_fetch_assoc($result);
                mysqli_stmt_close($stmt);
                
                return $data ?: null;
            }
        }
    }
    
    return null;
}

$editData = getEditData($con);
?>

<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' ,'fixed')}catch(e){}
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
                    <li class="active">Employee Range Management</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="row">
                    <div class="col-xs-12">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">
                                Operation completed successfully!
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php elseif (isset($_GET['error'])): ?>
                            <div class="alert alert-danger">
                                An error occurred. Please try again.
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="post"> 
                            <div class="input-group" style="width: 100%; margin-bottom: 20px;">
                                <?php if ($editData): ?>
                                    <input type="hidden" name="business_type_id" value="<?php echo (int)$editData['emprange_id']; ?>">
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="Input employee range name" 
                                           name="business_type" 
                                           value="<?php echo htmlspecialchars($editData['emprange_type']); ?>"
                                           required>
                                    <input type="submit" class="form-control btn btn-primary" value="Update" name="update_mes">
                                <?php else: ?>
                                    <input type="text" 
                                           class="form-control" 
                                           placeholder="Input employee range name" 
                                           name="business_type"
                                           required>
                                    <input type="submit" class="form-control btn btn-primary" value="Add New" name="save_mes">
                                <?php endif ?>
                            </div>
                        </form>
                    </div>

                    <br>
                    
                    <div class="col-xs-12">
                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th><strong>ID</strong></th>
                                        <th><strong>Name</strong></th>
                                        <th style="text-align:center"><strong>Status</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $j = 0;
                                    $count = mysqli_num_rows($recObj);
                                    
                                    if ($count > 0) {
                                        while ($row = mysqli_fetch_assoc($recObj)) {
                                    ?>
                                        <tr>
                                            <td><?php echo (int)$row['emprange_id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['emprange_type']); ?></td>
                                            <td align="center">
                                                <a href="?edit=<?php echo (int)$row['emprange_id']; ?>" 
                                                   class="btn btn-success btn-sm"
                                                   onclick="return confirm('Are you sure you want to edit this item?');">
                                                    <i class="icon-edit"></i> Edit
                                                </a>
                                                <a href="?delete=<?php echo (int)$row['emprange_id']; ?>" 
                                                   class="btn btn-danger btn-sm"
                                                   onclick="return confirm('Are you sure you want to delete this item?');">
                                                    <i class="icon-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                            $j++;
                                        }
                                    } else {
                                    ?>
                                        <tr>
                                            <td colspan="3" align="center">No records found</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all"/>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript files -->
<script type="text/javascript">
    window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>
<script type="text/javascript">
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery.dataTables.min.js"></script>
<script src="assets/js/jquery.dataTables.bootstrap.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
    jQuery(function($) {
        // Initialize dataTable
        $('#sample-table-2').dataTable({
            "aoColumns": [
                null, null, { "bSortable": false }
            ]
        });
        
        // Handle checkbox selection
        $('table th input:checkbox').on('click', function() {
            var that = this;
            $(this).closest('table').find('tr > td:first-child input:checkbox')
                .each(function() {
                    this.checked = that.checked;
                    $(this).closest('tr').toggleClass('selected');
                });
        });
        
        // Initialize tooltips
        $('[data-rel="tooltip"]').tooltip({placement: tooltip_placement});
        
        function tooltip_placement(context, source) {
            var $source = $(source);
            var $parent = $source.closest('table');
            var off1 = $parent.offset();
            var w1 = $parent.width();
            var off2 = $source.offset();
            var w2 = $source.width();
            
            if(parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) return 'right';
            return 'left';
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>

</body>
</html>