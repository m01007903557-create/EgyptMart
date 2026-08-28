<?php
/**
 * File: supp-add.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة شعار مورد جديد
 * Add new supplier logo
 * 
 * Features:
 * - إضافة شعار مورد جديد
 * - تحديد أبعاد الصورة
 * - اختيار البلدان المستهدفة
 * - رفع وتغيير حجم الصورة
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_Login();

/**
 * Class SupplierLogoAdder
 * 
 * Handles supplier logo addition operations
 */
class SupplierLogoAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Image size/dimensions */
    private string $imgsize;
    
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
     * @param string $imgsize Image dimensions
     * @param string $adv_img Image filename
     * @param string $adv_link Link URL
     * @param array $adv_country Selected countries
     * @param int $adv_global Global setting
     */
    public function __construct(
        string $imgsize,
        string $adv_img,
        string $adv_link,
        array $adv_country,
        int $adv_global
    ) {
        $this->imgsize = $imgsize;
        $this->adv_img = $adv_img;
        $this->adv_link = $adv_link;
        $this->adv_country = implode(",", array_map('intval', $adv_country));
        $this->adv_global = $adv_global;
        
        // Store in session for form persistence
        $_SESSION['imgsize'] = $this->imgsize;
        $_SESSION['adv_link'] = $this->adv_link;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // Validate image size
        if (empty($this->imgsize) || $this->imgsize === "0") {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select image size</div>';
            return false;
        }
        
        // Validate link
        if (empty($this->adv_link)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a link</div>';
            return false;
        }
        
        // Validate image presence
        if (empty($this->adv_img)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload an image</div>';
            return false;
        }
        
        // Validate file
        if (!$this->validateFile()) {
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
     * Add supplier logo
     */
    public function add(): void {
        global $con;
        
        // Parse dimensions from size string (e.g., "90x90")
        $dimensions = explode('x', $this->imgsize);
        $this->adv_imagewidth = (int)($dimensions[0] ?? 90);
        $this->adv_imageheight = (int)($dimensions[1] ?? 90);
        
        // Process and save image
        if (!$this->processAndSaveImage()) {
            return;
        }
        
        // Insert into database
        $sql = "INSERT INTO supplier_logo SET
                adv_link = ?,
                adv_img = ?,
                adv_imagewidth = ?,
                adv_imageheight = ?,
                adv_country = ?,
                adv_global = ?,
                adv_updated_date = NOW(),
                adv_status = 1";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssiisi",
            $this->adv_link,
            $this->adv_img,
            $this->adv_imagewidth,
            $this->adv_imageheight,
            $this->adv_country,
            $this->adv_global
        );
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variables
            unset($_SESSION['imgsize']);
            unset($_SESSION['adv_link']);
            
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Supplier logo added successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add supplier logo</div>';
        }
        
        mysqli_stmt_close($stmt);
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
            $this->$name = $value;
        }
    }
}

// Handle session messages and form persistence
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$imgsize = $_SESSION['imgsize'] ?? '';
$adv_link = $_SESSION['adv_link'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $logoAdder = new SupplierLogoAdder(
        trim($_POST['imgsize'] ?? '90x90'),
        $_FILES['adv_img']['name'] ?? '',
        trim($_POST['adv_link'] ?? ''),
        $_POST['adv_country'] ?? [],
        (int)($_POST['adv_global'] ?? 0)
    );
    
    if ($logoAdder->validate()) {
        $logoAdder->add();
    }
    
    $_SESSION['msg'] = $logoAdder->msg;
    header("Location: supp-add.php");
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
                        <a href="supp-view.php">Supplier Logos</a>
                    </li>
                    <li class="active">Add Logo</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Add Supplier Logo
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Upload new supplier logo
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            
                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Image Dimensions -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Width & Height of Image</label>
                                <div class="col-sm-9">
                                    <select name="imgsize" class="chosen-select" style="width:200px;">
                                        <option value="90x90" <?php echo $imgsize === "90x90" ? 'selected="selected"' : ''; ?>>
                                            90 x 90
                                        </option>
                                    </select>
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
                                        <option value="">Select Countries</option>
                                        <?php while ($country = mysqli_fetch_object($rsCountry)): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>">
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
                                           value="<?php echo htmlspecialchars($adv_link, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="http://example.com/supplier" required/>
                                    <span class="help-block">Link to supplier's website or profile</span>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="adv_img" id="id-input-file-2" type="file" accept="image/*" required>
                                    </div>
                                    <span class="help-block">Allowed: JPG, PNG, GIF (Max 2MB, will be resized to 90x90)</span>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd">
                                        <i class="icon-ok bigger-110"></i> Add
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
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>