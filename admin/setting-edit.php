<?php
/**
 * File: setting-edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل إعدادات الموقع (نصوص وصور)
 * Edit site settings (text and images)
 * 
 * Features:
 * - تعديل النصوص (عنوان الموقع، كلمات مفتاحية، وصف)
 * - رفع وتغيير الشعارات (logo, small-logo, footer-logo, left-logo, unit-logo-footer)
 * - تغيير إعدادات Manual/Auto
 * - معالجة الصور وتغيير حجمها
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
 * Class SiteSettingsEditor
 * 
 * Handles site settings editing operations
 */
class SiteSettingsEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Setting ID */
    private int $st_id;
    
    /** @var string Setting field name */
    private string $st_field = '';
    
    /** @var string Setting value */
    private string $st_value = '';
    
    /** @var array Logo field names */
    private array $logoFields = ['logo', 'small-logo', 'footer-logo', 'left-logo', 'unit-logo-footer'];
    
    /** @var array Radio button field IDs */
    private array $radioFields = [12, 13];
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'ico'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /** @var string Upload directory for logos */
    private string $uploadDir = '../sitelogo/';
    
    /** @var array Logo resize dimensions */
    private array $logoDimensions = [
        'default' => [229, 66],
        'small-logo' => [146, 41]
    ];
    
    /**
     * Constructor
     * 
     * @param int $st_id Setting ID
     * @param string $st_field Setting field name
     */
    public function __construct(int $st_id, string $st_field) {
        $this->st_id = $st_id;
        $this->st_field = $st_field;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Get setting details
     * 
     * @return object|null Setting details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM site_settings WHERE st_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->st_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Check if field is a logo field
     * 
     * @return bool True if logo field
     */
    public function isLogoField(): bool {
        return in_array($this->st_field, $this->logoFields, true);
    }
    
    /**
     * Check if field is a radio button field
     * 
     * @return bool True if radio field
     */
    public function isRadioField(): bool {
        return in_array($this->st_id, $this->radioFields, true);
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        if ($this->isLogoField()) {
            // For logo fields, validation happens during file upload
            return true;
        }
        
        // For text fields, check if empty
        if (empty($this->st_value)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> This field cannot be empty</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate uploaded file
     * 
     * @return bool True if file is valid
     */
    private function validateFile(): bool {
        $file = $_FILES['st_value'];
        
        // Check if file was uploaded
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a file to upload</div>';
            return false;
        }
        
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
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid image (JPG, PNG, GIF, ICO)</div>';
            return false;
        }
        
        // Verify image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> File is not a valid image</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update setting
     */
    public function update(): void {
        global $con;
        
        if ($this->isLogoField()) {
            $this->updateLogo();
        } else {
            $this->updateText();
        }
    }
    
    /**
     * Update logo setting
     */
    private function updateLogo(): void {
        global $con;
        
        // Validate file
        if (!$this->validateFile()) {
            return;
        }
        
        // Get existing image to delete
        $existingImage = $this->getExistingValue();
        
        // Process and save new image
        if (!$this->processAndSaveLogo()) {
            return;
        }
        
        // Delete old image
        if ($existingImage && file_exists($this->uploadDir . $existingImage)) {
            @unlink($this->uploadDir . $existingImage);
        }
        
        // Update database
        $this->updateDatabase();
    }
    
    /**
     * Update text setting
     */
    private function updateText(): void {
        $this->updateDatabase();
    }
    
    /**
     * Get existing value
     * 
     * @return string|null Existing value
     */
    private function getExistingValue(): ?string {
        global $con;
        
        $sql = "SELECT st_value FROM site_settings WHERE st_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->st_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['st_value'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Process and save logo image
     * 
     * @return bool Success status
     */
    private function processAndSaveLogo(): bool {
        $file = $_FILES['st_value'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->st_value = 'logo_' . $this->st_field . '_' . time() . '.' . $extension;
        
        try {
            // Get resize dimensions
            $dimensions = $this->logoDimensions['default'];
            if ($this->st_field === 'small-logo') {
                $dimensions = $this->logoDimensions['small-logo'];
            }
            
            // Process and resize image
            $image = new SimpleImage();
            $image->load($file['tmp_name']);
            $image->resize($dimensions[0], $dimensions[1]);
            
            // Save image
            $uploadPath = $this->uploadDir . $this->st_value;
            $image->save($uploadPath);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Logo processing failed: " . $e->getMessage());
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to process logo</div>';
            return false;
        }
    }
    
    /**
     * Update database
     */
    private function updateDatabase(): void {
        global $con;
        
        $sql = "UPDATE site_settings SET st_value = ?, st_updated_date = NOW() WHERE st_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $this->st_value, $this->st_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Setting updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get field display name
     * 
     * @return string Display name
     */
    public function getFieldDisplayName(): string {
        return match ($this->st_field) {
            'title' => 'Website Title',
            'meta-keywords' => 'Meta Keywords',
            'meta-description' => 'Meta Description',
            'logo' => 'Main Logo',
            'small-logo' => 'Small Logo',
            'footer-logo' => 'Footer Logo',
            'left-logo' => 'Left Logo',
            'unit-logo-footer' => 'Unit Logo Footer',
            default => ucfirst(str_replace('-', ' ', $this->st_field))
        };
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

// Get setting ID
$settingId = isset($_GET['sid']) ? (int)$_GET['sid'] : 0;
if ($settingId === 0) {
    header("Location: setting-view.php");
    exit;
}

// Get field from POST or determine from existing
$st_field = $_POST['st_field'] ?? '';

// Initialize editor
$editor = new SiteSettingsEditor($settingId, $st_field);
$row = $editor->getDetails();

if (!$row) {
    header("Location: setting-view.php");
    exit;
}

// Set field from database if not set
if (empty($st_field)) {
    $st_field = $row->st_field ?? '';
    $editor->st_field = $st_field;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    
    if ($editor->isLogoField()) {
        $editor->st_value = $_FILES['st_value']['name'] ?? '';
    } else {
        $editor->st_value = trim($_POST['st_value'] ?? '');
    }
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: setting-edit.php?sid=" . $settingId);
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
                        <a href="setting-view.php">Site Settings</a>
                    </li>
                    <li class="active">
                        <?php echo $editor->getFieldDisplayName(); ?> Edit
                    </li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Site Setting
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo $editor->getFieldDisplayName(); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form action="" class="form-horizontal" method="post" enctype="multipart/form-data">
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Current Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Value</label>
                                <div class="col-sm-9">
                                    <?php if ($editor->isLogoField()): ?>
                                        <?php if (!empty($row->st_value) && file_exists("../sitelogo/" . $row->st_value)): ?>
                                            <img src="../sitelogo/<?php echo htmlspecialchars($row->st_value, ENT_QUOTES, 'UTF-8'); ?>" 
                                                 style="max-width:300px; max-height:150px; border:1px solid #ddd; padding:5px;" 
                                                 alt="Current Logo"/>
                                        <?php else: ?>
                                            <p class="text-muted">No logo uploaded</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="well well-sm" style="min-height:50px;">
                                            <?php echo nl2br(htmlspecialchars(stripslashes($row->st_value ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="hidden" name="st_field" value="<?php echo htmlspecialchars($row->st_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- New Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">New Value</label>
                                <div class="col-sm-9">
                                    
                                    <?php if ($editor->isRadioField()): ?>
                                        <!-- Radio buttons for Manual/Auto -->
                                        <div class="radio">
                                            <label style="margin-right:20px;">
                                                <input type="radio" class="ace" name="st_value" value="Manual" 
                                                       <?php echo ($row->st_value === 'Manual') ? 'checked="checked"' : ''; ?> />
                                                <span class="lbl">Manual</span>
                                            </label>
                                            <label>
                                                <input type="radio" class="ace" name="st_value" value="Auto" 
                                                       <?php echo ($row->st_value === 'Auto') ? 'checked="checked"' : ''; ?> />
                                                <span class="lbl">Auto</span>
                                            </label>
                                        </div>
                                        
                                    <?php elseif ($editor->isLogoField()): ?>
                                        <!-- File upload for logos -->
                                        <div class="ace-file-input" style="width:400px;">
                                            <input name="st_value" id="id-input-file-2" type="file" accept="image/*">
                                        </div>
                                        <?php if ($editor->st_field === 'small-logo'): ?>
                                            <p class="help-block">Image dimensions should be 146px width and 41px height (will be resized automatically)</p>
                                        <?php else: ?>
                                            <p class="help-block">Image dimensions should be 229px width and 66px height (will be resized automatically)</p>
                                        <?php endif; ?>
                                        
                                    <?php else: ?>
                                        <!-- Text input for other settings -->
                                        <input name="st_value" class="col-xs-10 col-sm-5" type="text" 
                                               value="" placeholder="Enter new value" />
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> Reset
                                    </button>
                                    <a href="setting-view.php" class="btn btn-primary">
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
        $('#id-input-file-2').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small',
            whitelist: 'gif|png|jpg|jpeg|ico',
            blacklist: 'exe|php|html|js',
            before_change: function(files, dropped) {
                if (files.length > 0) {
                    var file = files[0];
                    var ext = file.name.split('.').pop().toLowerCase();
                    var allowedExts = ['gif', 'png', 'jpg', 'jpeg', 'ico'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        alert('Please upload valid image (JPG, PNG, GIF, ICO)');
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
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
    });
</script>

<style>
    .well-sm {
        background-color: #f9f9f9;
        border: 1px solid #e0e5ec;
        padding: 10px;
        min-height: 50px;
    }
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }
    .btn {
        margin-right: 5px;
    }
    .text-muted {
        color: #999;
        font-style: italic;
    }
    img {
        max-width: 100%;
        height: auto;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>