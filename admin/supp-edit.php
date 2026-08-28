<?php
/**
 * File: supp-edit.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل شعار المورد
 * Edit supplier logo
 * 
 * Features:
 * - تعديل رابط الشعار
 * - تغيير البلدان المستهدفة
 * - تحديث صورة الشعار
 * - معاينة الصورة الحالية
 * - رفع وتغيير حجم الصورة
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class SupplierLogoEditor
 * 
 * Handles supplier logo editing operations
 */
class SupplierLogoEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Logo ID */
    private int $adv_id;
    
    /** @var string Image filename */
    private string $adv_img = '';
    
    /** @var string Link URL */
    private string $adv_link = '';
    
    /** @var int Image width */
    private int $adv_imagewidth = 0;
    
    /** @var int Image height */
    private int $adv_imageheight = 0;
    
    /** @var string Country IDs (comma-separated) */
    private string $adv_country = '';
    
    /** @var int Global setting */
    private int $adv_global = 0;
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /** @var string Upload directory */
    private string $uploadDir = '../upload/supplier_logo/';
    
    /**
     * Constructor
     * 
     * @param int $adv_id Logo ID
     * @param array $adv_country Selected countries
     */
    public function __construct(int $adv_id, array $adv_country) {
        $this->adv_id = $adv_id;
        $this->adv_country = $adv_country;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Get logo details
     * 
     * @return object|null Logo details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM supplier_logo WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
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
        
        // Validate file if uploaded
        if (!empty($_FILES['adv_img']['name']) && !$this->validateFile()) {
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
        $file = $_FILES['adv_img'];
        
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
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid image (JPG, PNG, GIF)</div>';
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
     * Update logo
     */
    public function update(): void {
        global $con;
        
        // Handle image upload if provided
        if (!empty($_FILES['adv_img']['name'])) {
            $this->updateWithImage();
        } else {
            $this->updateWithoutImage();
        }
    }
    
    /**
     * Update with new image
     */
    private function updateWithImage(): void {
        global $con;
        
        // Get existing image to delete
        $existingImage = $this->getExistingImage();
        
        // Process and save new image
        if (!$this->processAndSaveImage()) {
            return;
        }
        
        // Delete old image
        if ($existingImage) {
            $this->deleteImageFile($existingImage);
        }
        
        // Update database
        $sql = "UPDATE supplier_logo SET
                adv_img = ?,
                adv_link = ?,
                adv_imagewidth = ?,
                adv_imageheight = ?,
                adv_country = ?,
                adv_global = ?
                WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        $countryString = implode(",", array_map('intval', $this->adv_country));
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssiisii",
            $this->adv_img,
            $this->adv_link,
            $this->adv_imagewidth,
            $this->adv_imageheight,
            $countryString,
            $this->adv_global,
            $this->adv_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Supplier logo updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update without changing image
     */
    private function updateWithoutImage(): void {
        global $con;
        
        $sql = "UPDATE supplier_logo SET
                adv_link = ?,
                adv_country = ?,
                adv_global = ?
                WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        $countryString = implode(",", array_map('intval', $this->adv_country));
        
        mysqli_stmt_bind_param($stmt, "ssii", $this->adv_link, $countryString, $this->adv_global, $this->adv_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Supplier logo updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get existing image filename
     * 
     * @return string|null Image filename
     */
    private function getExistingImage(): ?string {
        global $con;
        
        $sql = "SELECT adv_img FROM supplier_logo WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['adv_img'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Delete image file
     * 
     * @param string $filename Image filename
     */
    private function deleteImageFile(string $filename): void {
        $path = $this->uploadDir . $filename;
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
        }
    }
    
    /**
     * Process and save uploaded image
     * 
     * @return bool Success status
     */
    private function processAndSaveImage(): bool {
        $file = $_FILES['adv_img'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->adv_img = $this->adv_imagewidth . rand(1000, 9999) . $this->adv_imageheight . '.' . $extension;
        
        try {
            // Process and resize image
            $image = new SimpleImage();
            $image->load($file['tmp_name']);
            $image->resize($this->adv_imagewidth, $this->adv_imageheight);
            
            // Save image
            $uploadPath = $this->uploadDir . $this->adv_img;
            $image->save($uploadPath);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Image processing failed: " . $e->getMessage());
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to process image</div>';
            return false;
        }
    }
    
    /**
     * Magic setter for properties
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function __set(string $name, $value): void {
        if (property_exists($this, $name)) {
            if (in_array($name, ['adv_imagewidth', 'adv_imageheight', 'adv_global', 'adv_id'])) {
                $this->$name = (int)$value;
            } elseif ($name === 'adv_country' && is_array($value)) {
                $this->adv_country = $value;
            } else {
                $this->$name = $value;
            }
        }
    }
}

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get logo ID
$logoId = isset($_GET['aid']) ? (int)$_GET['aid'] : 0;
if ($logoId === 0) {
    header("Location: supp-view.php");
    exit;
}

// Initialize editor
$editor = new SupplierLogoEditor($logoId, []);
$row = $editor->getDetails();

if (!$row) {
    header("Location: supp-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->adv_imagewidth = (int)trim($_POST['adv_imagewidth'] ?? 0);
    $editor->adv_imageheight = (int)trim($_POST['adv_imageheight'] ?? 0);
    $editor->adv_link = trim($_POST['adv_link'] ?? '');
    $editor->adv_country = $_POST['adv_country'] ?? [];
    $editor->adv_global = (int)($_POST['adv_global'] ?? 0);
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: supp-edit.php?aid=" . $logoId);
    exit;
}

// Get selected countries for current logo
$selectedCountries = explode(",", $row->adv_country ?? '');
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
                        <a href="supp-view.php">Supplier Logos</a>
                    </li>
                    <li class="active">Edit Logo</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Supplier Logo
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Update logo information
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            
                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Image Dimensions (Read-only) -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Image Dimensions</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="adv_imagewidth" value="<?php echo (int)$row->adv_imagewidth; ?>" />
                                    <input type="hidden" name="adv_imageheight" value="<?php echo (int)$row->adv_imageheight; ?>" />
                                    <p class="form-control-static"><?php echo (int)$row->adv_imagewidth . " x " . (int)$row->adv_imageheight; ?></p>
                                </div>
                            </div>

                            <!-- Country Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Target Countries:</label>
                                <div class="col-sm-8">
                                    <?php
                                    $sqlCountry = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
                                    $rsCountry = mysqli_query($con, $sqlCountry);
                                    ?>
                                    <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select" style="width:400px;" required>
                                        <?php while ($country = mysqli_fetch_object($rsCountry)): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>" 
                                                <?php echo in_array((string)$country->cn_id, $selectedCountries) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country->cn_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="adv_global" value="0">

                            <!-- Link -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Link URL</label>
                                <div class="col-sm-9">
                                    <input name="adv_link" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->adv_link ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="http://example.com/supplier" required/>
                                    <span class="help-block">Link to supplier's website or profile</span>
                                </div>
                            </div>

                            <!-- Current Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Image</label>
                                <div class="col-sm-9">
                                    <?php if (!empty($row->adv_img) && file_exists("../upload/supplier_logo/" . $row->adv_img)): ?>
                                        <img src="../upload/supplier_logo/<?php echo htmlspecialchars($row->adv_img, ENT_QUOTES, 'UTF-8'); ?>" 
                                             style="max-width:200px; max-height:200px;" alt="Current Logo"/>
                                    <?php else: ?>
                                        <p class="text-muted">No image available</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Upload New Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload New Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="adv_img" id="id-input-file-2" type="file" accept="image/*">
                                    </div>
                                    <span class="help-block">Leave empty to keep current image. Allowed: JPG, PNG, GIF (Max 2MB)</span>
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
                                    <a href="supp-view.php" class="btn btn-primary">
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
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
        
        // Initialize file input with validation
        $('#id-input-file-2').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small',
            whitelist: 'gif|png|jpg|jpeg',
            blacklist: 'exe|php|html|js',
            before_change: function(files, dropped) {
                if (files.length > 0) {
                    var file = files[0];
                    var ext = file.name.split('.').pop().toLowerCase();
                    var allowedExts = ['gif', 'png', 'jpg', 'jpeg'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        alert('Please upload valid image (JPG, PNG, GIF)');
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
    });
</script>

<style>
    .form-control-static {
        padding-top: 7px;
        margin-bottom: 0;
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
</style>

</body>
</html>

<?php ob_end_flush(); ?>