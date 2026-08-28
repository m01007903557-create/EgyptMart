<?php
/**
 * File: social-edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل إعدادات وسائل التواصل الاجتماعي
 * Edit social media settings
 * 
 * Features:
 * - تعديل قيم إعدادات وسائل التواصل الاجتماعي
 * - رفع شعارات وسائل التواصل الاجتماعي
 * - معالجة الصور وتغيير حجمها
 * - التحقق من صحة المدخلات
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
 * Class SocialMediaEditor
 * 
 * Handles social media settings editing operations
 */
class SocialMediaEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Setting ID */
    private int $smli_id;
    
    /** @var string Setting field name */
    private string $smli_field = '';
    
    /** @var string Setting value */
    private string $smli_value = '';
    
    /** @var array Logo field names */
    private array $logoFields = ['logo'];
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /** @var string Upload directory for logos */
    private string $uploadDir = '../sitelogo/';
    
    /** @var array Logo resize dimensions */
    private array $logoDimensions = [119, 70];
    
    /**
     * Constructor
     * 
     * @param int $smli_id Setting ID
     * @param string $smli_field Setting field name
     */
    public function __construct(int $smli_id, string $smli_field) {
        $this->smli_id = $smli_id;
        $this->smli_field = $smli_field;
        
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
        
        $sql = "SELECT * FROM social_media_login_info WHERE smli_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->smli_id);
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
        return in_array($this->smli_field, $this->logoFields, true);
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // For text fields, check if empty
        if (!$this->isLogoField() && empty($this->smli_value)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> This field cannot be empty.</div>';
            return false;
        }
        
        // For logo fields, validate if file is uploaded
        if ($this->isLogoField() && !empty($_FILES['smli_value']['name'])) {
            return $this->validateFile();
        }
        
        return true;
    }
    
    /**
     * Validate uploaded file
     * 
     * @return bool True if file is valid
     */
    private function validateFile(): bool {
        $file = $_FILES['smli_value'];
        
        // Check if file was uploaded
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            // No new file uploaded, that's OK
            return true;
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
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid image (JPG, PNG, GIF, ICO, SVG)</div>';
            return false;
        }
        
        // Verify image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false && $extension !== 'svg') {
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
        
        if ($this->isLogoField() && !empty($_FILES['smli_value']['name'])) {
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
        
        $sql = "SELECT smli_value FROM social_media_login_info WHERE smli_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->smli_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['smli_value'];
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
        $file = $_FILES['smli_value'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->smli_value = 'social_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        
        try {
            // For SVG files, just copy without resizing
            if ($extension === 'svg') {
                if (!move_uploaded_file($file['tmp_name'], $this->uploadDir . $this->smli_value)) {
                    throw new Exception('Failed to upload SVG file');
                }
            } else {
                // Process and resize image for other formats
                $image = new SimpleImage();
                $image->load($file['tmp_name']);
                $image->resize($this->logoDimensions[0], $this->logoDimensions[1]);
                
                // Save image
                $uploadPath = $this->uploadDir . $this->smli_value;
                $image->save($uploadPath);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Logo processing failed: " . $e->getMessage());
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to process logo: ' . $e->getMessage() . '</div>';
            return false;
        }
    }
    
    /**
     * Update database
     */
    private function updateDatabase(): void {
        global $con;
        
        $sql = "UPDATE social_media_login_info SET smli_value = ?, smli_updated_date = NOW() WHERE smli_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $this->smli_value, $this->smli_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Social media setting updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Format field name for display
     * 
     * @param string $field Field name
     * @return string Formatted field name
     */
    public function formatFieldName(string $field): string {
        if (empty($field)) {
            return '';
        }
        
        $parts = explode('-', $field);
        $formattedParts = array_map('ucfirst', $parts);
        return implode(' ', $formattedParts);
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
    header("Location: social-view.php");
    exit;
}

// Get field from POST or determine from existing
$smli_field = $_POST['smli_field'] ?? '';

// Initialize editor
$editor = new SocialMediaEditor($settingId, $smli_field);
$row = $editor->getDetails();

if (!$row) {
    header("Location: social-view.php");
    exit;
}

// Set field from database if not set
if (empty($smli_field)) {
    $smli_field = $row->smli_field ?? '';
    $editor->smli_field = $smli_field;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    
    if ($editor->isLogoField()) {
        $editor->smli_value = $_FILES['smli_value']['name'] ?? '';
    } else {
        $editor->smli_value = trim($_POST['smli_value'] ?? '');
    }
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: social-edit.php?sid=" . $settingId);
    exit;
}

$fieldDisplayName = $editor->formatFieldName($row->smli_field ?? '');
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
                        <a href="social-view.php">Social Media Settings</a>
                    </li>
                    <li class="active">Edit: <?php echo htmlspecialchars($fieldDisplayName, ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Social Media Setting
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($fieldDisplayName, ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Current Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Value</label>
                                <div class="col-sm-9">
                                    <?php if ($editor->isLogoField()): ?>
                                        <?php if (!empty($row->smli_value) && file_exists("../sitelogo/" . $row->smli_value)): ?>
                                            <div style="border:1px solid #ddd; padding:10px; display:inline-block; background:#f9f9f9; border-radius:4px;">
                                                <img src="../sitelogo/<?php echo htmlspecialchars($row->smli_value, ENT_QUOTES, 'UTF-8'); ?>" 
                                                     style="max-width:200px; max-height:100px;" alt="Current Logo"/>
                                            </div>
                                        <?php else: ?>
                                            <p class="text-muted">No logo uploaded</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="well well-sm" style="min-height:50px; word-break:break-all;">
                                            <?php 
                                            $value = stripslashes($row->smli_value ?? '');
                                            if (filter_var($value, FILTER_VALIDATE_URL)) {
                                                echo '<a href="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" target="_blank">' 
                                                     . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</a>';
                                            } else {
                                                echo nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                    <input type="hidden" name="smli_field" value="<?php echo htmlspecialchars($row->smli_field ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- New Value -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">New Value</label>
                                <div class="col-sm-9">
                                    
                                    <?php if ($editor->isLogoField()): ?>
                                        <!-- File upload for logo -->
                                        <div class="ace-file-input" style="width:400px;">
                                            <input name="smli_value" id="smli_value" type="file" accept="image/*">
                                        </div>
                                        <p class="help-block">
                                            Leave empty to keep current logo. 
                                            Allowed: JPG, PNG, GIF, ICO, SVG (Max 2MB, will be resized to 119x70)
                                        </p>
                                        
                                    <?php else: ?>
                                        <!-- Text input for other settings -->
                                        <input name="smli_value" class="col-xs-10 col-sm-7" type="text" 
                                               value="" placeholder="Enter new value" />
                                        <p class="help-block">Enter the new value for this setting</p>
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
                                    <a href="social-view.php" class="btn btn-primary">
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
        $('#smli_value').ace_file_input({
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
                    var allowedExts = ['gif', 'png', 'jpg', 'jpeg', 'ico', 'svg'];
                    
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
        padding: 15px;
        min-height: 50px;
        border-radius: 4px;
    }
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 8px;
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