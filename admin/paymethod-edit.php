<?php
/**
 * File: paymethod-edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة تعديل بوابات الدفع
 * Payment gateway edit page
 * 
 * Features:
 * - تعديل بيانات بوابة الدفع
 * - تغيير شعار البوابة
 * - التحقق من صحة المدخلات
 * - رفع الصور
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
 * Class PaymentMethodEditor
 * 
 * Handles payment gateway editing operations
 */
class PaymentMethodEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Gateway ID */
    private int $id;
    
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
     * Constructor
     * 
     * @param int $id Gateway ID
     */
    public function __construct(int $id) {
        $this->id = $id;
    }
    
    /**
     * Get gateway details
     * 
     * @return object|null Gateway details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM payment_gateway WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->id);
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
        
        // Validate gateway name
        if (empty($this->pg_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Gateway name</div>';
            return false;
        }
        
        // Validate gateway ID
        if (empty($this->pg_id)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Gateway ID</div>';
            return false;
        }
        
        // Validate file if uploaded
        if (!empty($_FILES['pg_logo']['name'])) {
            if (!$this->validateFile()) {
                return false;
            }
        }
        
        return true;
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
     * Update payment gateway
     */
    public function update(): void {
        global $con;
        
        // Check if new logo is uploaded
        if (!empty($_FILES['pg_logo']['name'])) {
            $this->updateWithLogo();
        } else {
            $this->updateWithoutLogo();
        }
    }
    
    /**
     * Update with new logo
     */
    private function updateWithLogo(): void {
        global $con;
        
        // Get existing logo to delete
        $existingLogo = $this->getExistingLogo();
        
        // Delete old logo if exists
        if ($existingLogo) {
            $this->deleteLogoFile($existingLogo);
        }
        
        // Generate new filename
        $extension = strtolower(pathinfo($_FILES['pg_logo']['name'], PATHINFO_EXTENSION));
        $this->pg_logo = 'pg_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        
        // Upload new logo
        $uploadPath = __DIR__ . "/../images/payment-gateway/" . $this->pg_logo;
        if (!move_uploaded_file($_FILES['pg_logo']['tmp_name'], $uploadPath)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to upload logo</div>';
            return;
        }
        
        // Update database
        $sql = "UPDATE payment_gateway SET
                pg_name = ?,
                pg_id = ?,
                pg_logo = ?,
                pg_updated_date = NOW()
                WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "sssi", $this->pg_name, $this->pg_id, $this->pg_logo, $this->id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Payment method updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update without changing logo
     */
    private function updateWithoutLogo(): void {
        global $con;
        
        $sql = "UPDATE payment_gateway SET
                pg_name = ?,
                pg_id = ?,
                pg_updated_date = NOW()
                WHERE id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ssi", $this->pg_name, $this->pg_id, $this->id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Payment method updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get existing logo filename
     * 
     * @return string|null Logo filename
     */
    private function getExistingLogo(): ?string {
        global $con;
        
        $sql = "SELECT pg_logo FROM payment_gateway WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['pg_logo'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Delete logo file
     * 
     * @param string $filename Logo filename
     */
    private function deleteLogoFile(string $filename): void {
        $path = __DIR__ . "/../images/payment-gateway/" . $filename;
        if (file_exists($path) && is_file($path)) {
            @unlink($path);
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

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get gateway ID
$gatewayId = isset($_GET['aid']) ? (int)$_GET['aid'] : 0;
if ($gatewayId === 0) {
    header("Location: paymethod-view.php");
    exit;
}

// Initialize editor
$editor = new PaymentMethodEditor($gatewayId);
$row = $editor->getDetails();

if (!$row) {
    header("Location: paymethod-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->pg_name = trim($_POST['pg_name'] ?? '');
    $editor->pg_id = trim($_POST['pg_id'] ?? '');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: paymethod-edit.php?aid=" . $gatewayId);
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
                // Validate file if uploaded
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
                    <li class="active">Edit Payment Method</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Payment Method
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars(ucfirst($row->pg_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
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
                                <label class="col-sm-3 control-label no-padding-right">Method Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input name="pg_name" id="pg_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars(ucfirst($row->pg_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="e.g., PayPal, Credit Card" required/>
                                </div>
                            </div>
                            
                            <!-- Gateway ID -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Gateway ID <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input name="pg_id" id="pg_id" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->pg_id ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="e.g., paypal_express, stripe" required/>
                                    <span class="help-block">Unique identifier for the payment gateway</span>
                                </div>
                            </div>
                            
                            <!-- Current Logo -->
                            <?php if (!empty($row->pg_logo)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Logo</label>
                                <div class="col-sm-9">
                                    <div style="border:1px solid #ddd; padding:10px; display:inline-block; background:#f9f9f9; border-radius:4px;">
                                        <img src="../images/payment-gateway/<?php echo htmlspecialchars($row->pg_logo, ENT_QUOTES, 'UTF-8'); ?>" 
                                             style="max-width:150px; max-height:80px;" 
                                             alt="<?php echo htmlspecialchars($row->pg_name ?? '', ENT_QUOTES, 'UTF-8'); ?> Logo"/>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- New Logo Upload -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">New Logo</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="pg_logo" id="pg_logo" type="file" accept="image/*">
                                    </div>
                                    <span class="help-block">Leave empty to keep current logo. Allowed: GIF, JPG, PNG (Max 2MB)</span>
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
        $('#pg_logo').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small',
            whitelist: 'gif|png|jpg|jpeg',
            blacklist: 'exe|php|html|js'
        });
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Character counter for inputs
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
</style>

</body>
</html>

<?php ob_end_flush(); ?>