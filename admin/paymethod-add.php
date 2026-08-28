<?php
/**
 * File: paymethod-add.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة إضافة بوابة دفع جديدة
 * Add new payment gateway page
 * 
 * Features:
 * - إضافة بوابة دفع جديدة
 * - رفع شعار البوابة
 * - التحقق من صحة المدخلات
 * - منع إضافة بوابات مكررة
 */

declare(strict_types=1);

// Start output buffering
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class PaymentGatewayAdder
 * 
 * Handles payment gateway addition operations
 */
class PaymentGatewayAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Gateway name */
    private string $pg_name = '';
    
    /** @var string Gateway identifier */
    private string $pg_id = '';
    
    /** @var string Gateway logo filename */
    private string $pg_logo = '';
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['gif', 'jpg', 'jpeg', 'png'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // Validate gateway name
        if (empty($this->pg_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Gateway Name.</div>';
            return false;
        }
        
        // Check for duplicate gateway name
        if ($this->isDuplicateName()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Gateway name already exists.</div>';
            return false;
        }
        
        // Validate gateway ID
        if (empty($this->pg_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Gateway ID.</div>';
            return false;
        }
        
        // Check for duplicate gateway ID
        if ($this->isDuplicateId()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Gateway ID already exists.</div>';
            return false;
        }
        
        // Validate logo
        if (empty($this->pg_logo)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload Logo.</div>';
            return false;
        }
        
        // Validate file
        if (!$this->validateFile()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for duplicate gateway name
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM payment_gateway WHERE pg_name = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->pg_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check for duplicate gateway ID
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateId(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM payment_gateway WHERE pg_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->pg_id);
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
        $file = $_FILES['pg_logo'];
        
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
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid image (GIF, JPG, PNG)</div>';
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
     * Add payment gateway
     */
    public function add(): void {
        global $con;
        
        // Generate unique filename
        $extension = strtolower(pathinfo($_FILES['pg_logo']['name'], PATHINFO_EXTENSION));
        $this->pg_logo = 'pg_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        
        // Upload logo
        $uploadPath = __DIR__ . "/../images/payment-gateway/" . $this->pg_logo;
        if (!move_uploaded_file($_FILES['pg_logo']['tmp_name'], $uploadPath)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to upload logo</div>';
            return;
        }
        
        // Insert into database
        $sql = "INSERT INTO payment_gateway SET
                pg_name = ?,
                pg_id = ?,
                pg_logo = ?,
                pg_status = 1,
                pg_updated_date = NOW()";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "sss", $this->pg_name, $this->pg_id, $this->pg_logo);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variables
            unset($_SESSION['pg_name']);
            unset($_SESSION['pg_id']);
            
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Payment method added successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add payment method</div>';
        }
        
        mysqli_stmt_close($stmt);
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

$pg_name = $_SESSION['pg_name'] ?? '';
$pg_id = $_SESSION['pg_id'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $gatewayAdder = new PaymentGatewayAdder();
    
    $gatewayAdder->pg_name = trim($_POST['pg_name'] ?? '');
    $gatewayAdder->pg_id = trim($_POST['pg_id'] ?? '');
    $gatewayAdder->pg_logo = $_FILES['pg_logo']['name'] ?? '';
    
    // Store in session for form persistence
    $_SESSION['pg_name'] = $gatewayAdder->pg_name;
    $_SESSION['pg_id'] = $gatewayAdder->pg_id;
    
    if ($gatewayAdder->validate()) {
        $gatewayAdder->add();
    }
    
    $_SESSION['msg'] = $gatewayAdder->msg;
    header("Location: paymethod-add.php");
    exit;
}
?>

<?php include "includes/admin-top.php"; ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>

        <script type="text/javascript">
            function validatePaymentMethod() {
                var pg_name = document.getElementById('pg_name');
                var pg_id = document.getElementById('pg_id');
                var fileInput = document.getElementById('pg_logo');
                
                var message = "";
                var valid = true;
                
                // Validate gateway name
                if (pg_name.value.trim() === '') {
                    message = 'Please enter Gateway Name.';
                    pg_name.focus();
                    valid = false;
                }
                // Validate gateway ID
                else if (pg_id.value.trim() === '') {
                    message = 'Please enter Gateway ID.';
                    pg_id.focus();
                    valid = false;
                }
                // Validate file presence
                else if (fileInput.files.length === 0) {
                    message = 'Please upload Logo.';
                    fileInput.focus();
                    valid = false;
                }
                // Validate file type
                else if (fileInput.files.length > 0) {
                    var fileName = fileInput.value;
                    var ext = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
                    var allowedExts = ['gif', 'jpg', 'jpeg', 'png'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        message = 'Please upload valid image (GIF, JPG, PNG).';
                        fileInput.value = '';
                        fileInput.focus();
                        valid = false;
                    } else if (fileInput.files[0].size > 2 * 1024 * 1024) { // 2MB
                        message = 'File size must be less than 2MB.';
                        fileInput.value = '';
                        fileInput.focus();
                        valid = false;
                    }
                }
                
                if (!valid) {
                    var msgDiv = document.getElementById('msg');
                    msgDiv.innerHTML = "<i class='icon-remove'></i> " + message;
                    msgDiv.className = "alert alert-danger";
                }
                
                return valid;
            }
        </script>

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
                        <a href="paymethod-view.php">Payment Methods</a>
                    </li>
                    <li class="active">Add Payment Method</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Add New Payment Method
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Create a new payment gateway
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" 
                              onsubmit="return validatePaymentMethod();">
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Gateway Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Gateway Name <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="pg_name" id="pg_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($pg_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="e.g., PayPal, Stripe" required/>
                                    <span class="help-block">Enter the display name for the payment gateway</span>
                                </div>
                            </div>
                            
                            <!-- Gateway ID -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Gateway ID <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="pg_id" id="pg_id" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($pg_id, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="e.g., paypal_express, stripe" required/>
                                    <span class="help-block">Unique identifier for the payment gateway (lowercase, underscores allowed)</span>
                                </div>
                            </div>
                            
                            <!-- Logo Upload -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Logo <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="pg_logo" id="pg_logo" type="file" accept="image/*" required>
                                    </div>
                                    <span class="help-block">Upload gateway logo (GIF, JPG, PNG. Max 2MB)</span>
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
                                    <a href="paymethod-view.php" class="btn btn-primary">
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

<?php include "includes/footer.php"; ?>

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
        $('#pg_logo').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small',
            whitelist: 'gif|png|jpg|jpeg',
            blacklist: 'exe|php|html|js',
            before_change: function(files, dropped) {
                // Additional client-side validation
                if (files.length > 0) {
                    var file = files[0];
                    var ext = file.name.split('.').pop().toLowerCase();
                    var allowedExts = ['gif', 'jpg', 'jpeg', 'png'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        alert('Please upload valid image (GIF, JPG, PNG)');
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
        
        // Real-time slug generation for gateway ID
        $('#pg_name').on('keyup', function() {
            var name = $(this).val();
            var slug = name.toLowerCase()
                .replace(/[^a-z0-9]+/g, '_')
                .replace(/^_+|_+$/g, '');
            
            // Only suggest if pg_id is empty
            if ($('#pg_id').val().trim() === '') {
                $('#pg_id').val(slug);
            }
        });
        
        // Character counter
        $('#pg_name, #pg_id').on('keyup', function() {
            var maxLength = 100;
            var currentLength = $(this).val().length;
            if (currentLength > maxLength) {
                $(this).val($(this).val().substring(0, maxLength));
            }
        });
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
</style>

</body>
</html>

<?php ob_end_flush(); ?>