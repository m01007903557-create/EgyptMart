<?php

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_login();

/**
 * Class MembershipPlanEditor
 * 
 * Handles membership plan editing operations
 */
class MembershipPlanEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Plan ID */
    private int $mp_id;
    
    /** @var string Plan name */
    private string $mst_name = '';
    
    /** @var string Icon filename */
    private string $mst_icon = '';
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /** @var string Upload directory */
    private string $uploadDir = './images/';
    
    /**
     * Constructor
     * 
     * @param int $mp_id Plan ID
     */
    public function __construct(int $mp_id) {
        $this->mp_id = $mp_id;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Get plan details
     * 
     * @return object|null Plan details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM smembership_plan WHERE mp_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->mp_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // Validate plan name
        if (empty($this->mst_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Plan Name</div>';
            return false;
        }
        
        // Check for duplicate plan name (excluding current)
        if ($this->isDuplicateName()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Plan name already exists</div>';
            return false;
        }
        
        // Validate file if uploaded
        if (!empty($_FILES['mst_icon']['name']) && !$this->validateFile()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for duplicate plan name
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM smembership_plan 
                WHERE mst_name = ? AND mp_id != ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $this->mst_name, $this->mp_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Validate uploaded file
     * 
     * @return bool True if file is valid
     */
    private function validateFile(): bool {
        $file = $_FILES['mst_icon'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> ' . $errorMsg . '</div>';
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> File size must be less than 2MB</div>';
            return false;
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid image (JPG, PNG, GIF, ICO, SVG)</div>';
            return false;
        }
        
        // Verify image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false && !in_array($extension, ['ico', 'svg'])) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> File is not a valid image</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update plan
     */
    public function update(): void {
        global $con;
        
        // Handle icon upload
        $fileName = $this->handleIconUpload();
        if ($fileName === false) {
            return;
        }
        
        // Update database
        $sql = "UPDATE smembership_plan SET
                mst_name = ?,
                mst_icon = ?,
                mp_updated_date = NOW()
                WHERE mp_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ssi", $this->mst_name, $fileName, $this->mp_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Plan updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Handle icon upload
     * 
     * @return string|false Filename on success, false on failure
     */
    private function handleIconUpload() {
        // If no new file uploaded, keep existing icon
        if (empty($_FILES['mst_icon']['name'])) {
            return $_POST['hidden_image'] ?? '';
        }
        
        // Delete old icon if exists
        $oldIcon = $_POST['hidden_image'] ?? '';
        if (!empty($oldIcon) && file_exists($this->uploadDir . $oldIcon)) {
            @unlink($this->uploadDir . $oldIcon);
        }
        
        // Generate new filename
        $extension = strtolower(pathinfo($_FILES['mst_icon']['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        
        // Upload file
        $uploadPath = $this->uploadDir . $fileName;
        if (move_uploaded_file($_FILES['mst_icon']['tmp_name'], $uploadPath)) {
            return $fileName;
        }
        
        $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to upload icon</div>';
        return false;
    }
    
    /**
     * Magic setter for properties
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function __set(string $name, $value): void {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
}

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get plan ID
$planId = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
if ($planId === 0) {
    header("Location: splan-view.php");
    exit;
}

// Initialize editor
$editor = new MembershipPlanEditor($planId);
$row = $editor->getDetails();

if (!$row) {
    header("Location: splan-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->mst_name = trim($_POST['mst_name'] ?? '');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: splan-edit.php?fid=" . $planId);
    exit;
}
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
                    <li>
                        <a href="splan-view.php">Manage Membership Plans</a>
                    </li>
                    <li class="active">Edit Plan</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Membership Plan
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($row->mst_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Plan Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Plan Name <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="mst_name" id="mst_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->mst_name ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Enter plan name" required/>
                                    <input type="hidden" name="mp_id" value="<?php echo (int)$row->mp_id; ?>" />
                                </div>
                            </div>

                            <!-- Icon Upload -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Membership Icon
                                </label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="mst_icon" id="mst_icon" type="file" accept="image/*">
                                    </div>
                                    <span class="help-block">Leave empty to keep current icon. Allowed: JPG, PNG, GIF, ICO, SVG (Max 2MB)</span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="hidden_image" value="<?php echo htmlspecialchars($row->mst_icon ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            
                            <!-- Current Icon Preview -->
                            <?php if (!empty($row->mst_icon)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Current Icon
                                </label>
                                <div class="col-sm-9">
                                    <div style="border:1px solid #ddd; padding:10px; display:inline-block; background:#f9f9f9; border-radius:4px;">
                                        <img src="./images/<?php echo htmlspecialchars($row->mst_icon, ENT_QUOTES, 'UTF-8'); ?>" 
                                             style="max-width:50px; max-height:50px;" 
                                             alt="<?php echo htmlspecialchars($row->mst_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> Reset
                                    </button>
                                    <a href="splan-view.php" class="btn btn-primary">
                                        <i class="icon-arrow-left bigger-110"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <br clear="all" />
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript includes and initialization -->
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

<!--[if lte IE 8]>
<script src="assets/js/excanvas.min.js"></script>
<![endif]-->

<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
<script src="assets/js/date-time/moment.min.js"></script>
<script src="assets/js/date-time/daterangepicker.min.js"></script>
<script src="assets/js/bootstrap-colorpicker.min.js"></script>
<script src="assets/js/jquery.knob.min.js"></script>
<script src="assets/js/jquery.autosize.min.js"></script>
<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/bootstrap-tag.min.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize file input with validation
        $('#mst_icon').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small',
            whitelist: 'gif|png|jpg|jpeg|ico|svg',
            blacklist: 'exe|php|html|js',
            before_change: function(files, dropped) {
                if (files.length > 0) {
                    var file = files[0];
                    var ext = file.name.split('.').pop().toLowerCase();
                    var allowedExts = ['gif', 'jpg', 'jpeg', 'png', 'ico', 'svg'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        alert('Please upload valid image (JPG, PNG, GIF, ICO, SVG)');
                        return false;
                    }
                    
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        return false;
                    }
                }
                return files;
            }
        });
        
        // Character counter for plan name
        $('#mst_name').on('keyup', function() {
            var maxLength = 100;
            var currentLength = $(this).val().length;
            if (currentLength > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
            }
        });
        
        // Preview image on selection
        $('#mst_icon').on('change', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    // Optional: Show preview
                    console.log('Image selected');
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
    });
</script>

<style>
    .text-danger {
        color: #d15b47;
    }
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }
    .alert {
        margin-bottom: 20px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>