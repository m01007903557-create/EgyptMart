<?php
/**
 * File: profile_heading.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إدارة عناوين الملف الشخصي للشركات
 * Manage company profile headings
 * 
 * Features:
 * - عرض جميع عناوين الملف الشخصي
 * - إضافة عنوان جديد
 * - تعديل عنوان موجود
 * - حذف عنوان
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_login();

/**
 * Class ProfileHeadingManager
 * 
 * Handles profile heading operations
 */
class ProfileHeadingManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Table name */
    private string $table = 'profile_heading';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Get all profile headings
     * 
     * @return mysqli_result|false Query result
     */
    public function getAllHeadings() {
        $sql = "SELECT * FROM {$this->table} ORDER BY ph_id ASC";
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * Get single profile heading by ID
     * 
     * @param int $id Heading ID
     * @return array|null Heading data or null if not found
     */
    public function getHeadingById(int $id): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE ph_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * Add new profile heading
     * 
     * @param string $title Heading title
     * @return bool Success status
     */
    public function addHeading(string $title): bool {
        if ($this->isEmpty($title)) {
            return false;
        }
        
        // Check for duplicate
        if ($this->isDuplicate($title)) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->table} (ph_title, ph_status) VALUES (?, 1)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $title);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Update profile heading
     * 
     * @param int $id Heading ID
     * @param string $title New heading title
     * @return bool Success status
     */
    public function updateHeading(int $id, string $title): bool {
        if ($this->isEmpty($title) || $id <= 0) {
            return false;
        }
        
        // Check for duplicate excluding current
        if ($this->isDuplicate($title, $id)) {
            return false;
        }
        
        $sql = "UPDATE {$this->table} SET ph_title = ? WHERE ph_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $title, $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Delete profile heading
     * 
     * @param int $id Heading ID
     * @return bool Success status
     */
    public function deleteHeading(int $id): bool {
        if ($id <= 0) {
            return false;
        }
        
        // Check if heading is in use
        if ($this->isInUse($id)) {
            return false;
        }
        
        $sql = "DELETE FROM {$this->table} WHERE ph_id = ?";
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
     * Check if heading title is duplicate
     * 
     * @param string $title Heading title
     * @param int $excludeId Exclude this ID from check
     * @return bool True if duplicate
     */
    private function isDuplicate(string $title, int $excludeId = 0): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE ph_title = ?";
        
        if ($excludeId > 0) {
            $sql .= " AND ph_id != ?";
        }
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        if ($excludeId > 0) {
            mysqli_stmt_bind_param($stmt, "si", $title, $excludeId);
        } else {
            mysqli_stmt_bind_param($stmt, "s", $title);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check if heading is in use by any company
     * 
     * @param int $id Heading ID
     * @return bool True if in use
     */
    private function isInUse(int $id): bool {
        // Check in business_profile or other tables that reference profile headings
        $sql = "SELECT COUNT(*) as count FROM business_profile WHERE bnsprof_heading = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check if string is empty after trimming
     * 
     * @param string $str String to check
     * @return bool True if empty
     */
    private function isEmpty(string $str): bool {
        return trim($str) === '';
    }
    
    /**
     * Sanitize input string
     * 
     * @param string $str Input string
     * @return string Sanitized string
     */
    public function sanitize(string $str): string {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
}

// Initialize manager
$manager = new ProfileHeadingManager($con);

// Handle delete request
if (isset($_GET['delete'])) {
    $id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($id !== false && $id > 0) {
        $manager->deleteHeading($id);
    }
    header("Location: profile_heading.php");
    exit;
}

// Handle add new heading
if (isset($_POST['save_mes'])) {
    $title = trim($_POST['business_type'] ?? '');
    if (!empty($title)) {
        if ($manager->isDuplicate($title)) {
            // Handle duplicate error (optional)
        }
        $manager->addHeading($title);
    }
    header("Location: profile_heading.php");
    exit;
}

// Handle update heading
if (isset($_POST['update_mes'])) {
    $title = trim($_POST['business_type'] ?? '');
    $id = filter_var($_POST['business_type_id'] ?? 0, FILTER_VALIDATE_INT);
    
    if (!empty($title) && $id !== false && $id > 0) {
        $manager->updateHeading($id, $title);
    }
    header("Location: profile_heading.php");
    exit;
}

// Get edit data if in edit mode
$editData = null;
if (isset($_GET['edit'])) {
    $editId = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if ($editId !== false && $editId > 0) {
        $editData = $manager->getHeadingById($editId);
    }
}

// Get all profile headings
$records = $manager->getAllHeadings();
$totalCount = $records ? mysqli_num_rows($records) : 0;
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
                    <li class="active">Profile Headings</li>
                </ul>
            </div>
            
            <div class="page-content">
                <div class="row">
                    <!-- Add/Edit Form -->
                    <div class="col-xs-12">
                        <div class="page-header">
                            <h1>
                                Profile Headings
                                <small>
                                    <i class="icon-double-angle-right"></i>
                                    <?php echo $editData ? 'Edit' : 'Add New'; ?> Heading
                                </small>
                            </h1>
                        </div>
                        
                        <form method="post" class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-2 control-label no-padding-right">
                                    Heading Title
                                </label>
                                <div class="col-sm-8">
                                    <?php if ($editData): ?>
                                        <input type="hidden" name="business_type_id" 
                                               value="<?php echo (int)$editData['ph_id']; ?>">
                                    <?php endif; ?>
                                    
                                    <input type="text" class="form-control" 
                                           placeholder="Enter profile heading title" 
                                           name="business_type" 
                                           value="<?php echo $editData ? $manager->sanitize($editData['ph_title'] ?? '') : ''; ?>" 
                                           required>
                                </div>
                                <div class="col-sm-2">
                                    <?php if ($editData): ?>
                                        <button type="submit" class="btn btn-primary" name="update_mes">
                                            <i class="icon-ok"></i> Update
                                        </button>
                                        <a href="profile_heading.php" class="btn btn-default">
                                            <i class="icon-remove"></i> Cancel
                                        </a>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-success" name="save_mes">
                                            <i class="icon-plus"></i> Add New
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <br>
                    
                    <!-- List of Profile Headings -->
                    <div class="col-xs-12">
                        <div class="page-header">
                            <h1>
                                Profile Headings List
                                <small>
                                    <i class="icon-double-angle-right"></i>
                                    Total: <?php echo $totalCount; ?> headings
                                </small>
                            </h1>
                        </div>
                        
                        <div class="table-responsive">
                            <table id="sample-table-2" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="center">ID</th>
                                        <th>Title</th>
                                        <th class="center">Status</th>
                                        <th class="center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($records && $totalCount > 0): ?>
                                        <?php mysqli_data_seek($records, 0); ?>
                                        <?php while ($row = mysqli_fetch_object($records)): ?>
                                            <tr>
                                                <td class="center"><?php echo (int)$row->ph_id; ?></td>
                                                <td>
                                                    <?php echo $manager->sanitize($row->ph_title ?? ''); ?>
                                                </td>
                                                <td class="center">
                                                    <?php if (isset($row->ph_status) && $row->ph_status == 1): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-default">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="center">
                                                    <div class="btn-group">
                                                        <a href="?edit=<?php echo (int)$row->ph_id; ?>" 
                                                           class="btn btn-xs btn-info" title="Edit">
                                                            <i class="icon-edit bigger-120"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo (int)$row->ph_id; ?>" 
                                                           class="btn btn-xs btn-danger" title="Delete"
                                                           onclick="return confirm('Are you sure you want to delete this heading? This may affect companies using this heading.');">
                                                            <i class="icon-trash bigger-120"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                No profile headings found. 
                                                <a href="#" onclick="document.querySelector('input[name=\"business_type\"]').focus(); return false;">
                                                    Add your first heading
                                                </a>.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br clear="all"/>
            </div>
        </div>
    </div>
    <br clear="all" />
</div>

<?php include "includes/footer.php" ?>

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
<script src="assets/js/ace.min.js?v=2"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize DataTable
        var oTable = $('#sample-table-2').dataTable({
            "aoColumns": [
                { "bSortable": true },
                null,
                { "bSortable": false },
                { "bSortable": false }
            ]
        });
        
        // Focus on input field when page loads
        <?php if (!$editData): ?>
            $('input[name="business_type"]').focus();
        <?php endif; ?>
        
        // Real-time validation
        $('input[name="business_type"]').on('keyup', function() {
            var value = $(this).val().trim();
            if (value === '') {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
            } else {
                $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
            }
        });
        
        // Tooltip placement
        $('[data-rel="tooltip"]').tooltip({
            placement: function(context, source) {
                var $source = $(source);
                var $parent = $source.closest('table');
                var off1 = $parent.offset();
                var w1 = $parent.width();
                var off2 = $source.offset();
                var w2 = $source.width();
                
                if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) {
                    return 'right';
                }
                return 'left';
            }
        });
    });
</script>

<style>
    .btn-group {
        display: flex;
        gap: 5px;
        justify-content: center;
    }
    
    .btn-group .btn {
        border-radius: 3px !important;
        padding: 2px 8px;
    }
    
    .has-error input {
        border-color: #d15b47 !important;
    }
    
    .has-success input {
        border-color: #82af6f !important;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .page-header {
        margin-top: 0;
        padding-bottom: 9px;
        border-bottom: 1px solid #eee;
    }
    
    .btn-primary, .btn-success {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>