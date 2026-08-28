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
 * Class MembershipPlanAdder
 * 
 * Handles membership plan addition operations
 */
class MembershipPlanAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Plan name (legacy) */
    private string $mp_name = '';
    
    /** @var string Credits (legacy) */
    private string $mp_credits = '';
    
    /** @var string Amount (legacy) */
    private string $mp_amount = '';
    
    /** @var string Plan display name */
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
     * @param string $mp_name Plan name
     * @param string $mp_credits Credits
     * @param string $mp_amount Amount
     */
    public function __construct(string $mp_name, string $mp_credits, string $mp_amount) {
        $this->mp_name = $mp_name;
        $this->mp_credits = $mp_credits;
        $this->mp_amount = $mp_amount;
        
        // Store in session for form persistence
        $_SESSION['mp_name'] = $this->mp_name;
        $_SESSION['mp_credits'] = $this->mp_credits;
        $_SESSION['mp_amount'] = $this->mp_amount;
        
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
        
        // Validate plan name
        if (empty($this->mst_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Plan Name</div>';
            return false;
        }
        
        // Check for duplicate plan name
        if ($this->isDuplicateName()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Plan name already exists</div>';
            return false;
        }
        
        // Validate file presence
        if (empty($_FILES['mst_icon']['name'])) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload an icon</div>';
            return false;
        }
        
        // Validate file
        if (!$this->validateFile()) {
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
        
        $sql = "SELECT COUNT(*) as count FROM smembership_plan WHERE mst_name = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->mst_name);
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
     * Add new plan
     */
    public function add(): void {
        global $con;
        
        // Handle icon upload
        $fileName = $this->handleIconUpload();
        if ($fileName === false) {
            return;
        }
        
        // Insert into database
        $sql = "INSERT INTO smembership_plan SET
                mst_name = ?,
                mst_icon = ?,
                mp_status = 1,
                mp_updated_date = NOW()";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ss", $this->mst_name, $fileName);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variables
            unset($_SESSION['mp_name']);
            unset($_SESSION['mp_credits']);
            unset($_SESSION['mp_amount']);
            
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Plan added successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add plan</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Handle icon upload
     * 
     * @return string|false Filename on success, false on failure
     */
    private function handleIconUpload() {
        $file = $_FILES['mst_icon'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = time() . '_' . uniqid() . '.' . $extension;
        
        // Upload file
        $uploadPath = $this->uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
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

// Handle session messages and form persistence
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$mp_name = $_SESSION['mp_name'] ?? '';
$mp_credits = $_SESSION['mp_credits'] ?? '';
$mp_amount = $_SESSION['mp_amount'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $planAdder = new MembershipPlanAdder(
        trim($_POST['mp_name'] ?? ''),
        trim($_POST['mp_credits'] ?? ''),
        trim($_POST['mp_amount'] ?? '')
    );
    
    $planAdder->mst_name = trim($_POST['mst_name'] ?? '');
    
    if ($planAdder->validate()) {
        $planAdder->add();
    }
    
    $_SESSION['msg'] = $planAdder->msg;
    header("Location: splan-add.php");
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
            function validateForm() {
                var mst_name = document.getElementById('mst_name');
                var fileInput = document.getElementById('mst_icon');
                
                var message = "";
                var valid = true;
                
                // Validate plan name
                if (mst_name.value.trim() === '') {
                    message = 'Please enter Plan Name';
                    mst_name.focus();
                    valid = false;
                }
                // Validate file presence
                else if (fileInput.files.length === 0) {
                    message = 'Please upload an icon';
                    fileInput.focus();
                    valid = false;
                }
                // Validate file type
                else if (fileInput.files.length > 0) {
                    var fileName = fileInput.value;
                    var ext = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
                    var allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'ico', 'svg'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        message = 'Please upload valid image (JPG, PNG, GIF, ICO, SVG)';
                        fileInput.value = '';
                        fileInput.focus();
                        valid = false;
                    } else if (fileInput.files[0].size > 2 * 1024 * 1024) {
                        message = 'File size must be less than 2MB';
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
                        <a href="splan-view.php">Manage Membership Plans</a>
                    </li>
                    <li class="active">Add Plan</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Add New Membership Plan
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Create a new plan
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" 
                              onsubmit="return validateForm();">
                            
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Plan Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Plan Name <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="mst_name" id="mst_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($mp_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Enter plan name" required/>
                                </div>
                            </div>

                            <!-- Icon Upload -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Membership Icon <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="mst_icon" id="mst_icon" type="file" accept="image/*" required>
                                    </div>
                                    <span class="help-block">Allowed: JPG, PNG, GIF, ICO, SVG (Max 2MB)</span>
                                </div>
                            </div>
                            
                            <!-- Hidden legacy fields (kept for compatibility) -->
                            <input type="hidden" name="mp_name" value="<?php echo htmlspecialchars($mp_name, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="mp_credits" value="0">
                            <input type="hidden" name="mp_amount" value="0">
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd">
                                        <i class="icon-ok bigger-110"></i> Add
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