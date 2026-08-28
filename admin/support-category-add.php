<?php
/**
 * File: support-category-add.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة تصنيف دعم جديد
 * Add new support category
 * 
 * Features:
 * - إضافة تصنيف جديد للدعم الفني
 * - التحقق من صحة المدخلات
 * - منع التكرار
 * - رسائل نجاح/خطأ
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
 * Class SupportCategoryAdder
 * 
 * Handles support category addition operations
 */
class SupportCategoryAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Category name */
    private string $fc_name = '';
    
    /**
     * Constructor
     * 
     * @param string $fc_name Category name
     */
    public function __construct(string $fc_name) {
        $this->fc_name = $fc_name;
        
        // Store in session for form persistence
        $_SESSION['fc_name'] = $this->fc_name;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // Validate category name
        if (empty($this->fc_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter category name</div>';
            return false;
        }
        
        if (strlen($this->fc_name) < 2) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Category name must be at least 2 characters</div>';
            return false;
        }
        
        if (strlen($this->fc_name) > 100) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Category name must not exceed 100 characters</div>';
            return false;
        }
        
        // Check for duplicate category name
        if ($this->isDuplicateName()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> A category with this name already exists</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if category name already exists
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM faq_categories WHERE fc_name = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->fc_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Add support category
     */
    public function add(): void {
        global $con;
        
        $sql = "INSERT INTO faq_categories SET fc_name = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->fc_name);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variable
            unset($_SESSION['fc_name']);
            
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Category added successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add category</div>';
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

$fc_name = $_SESSION['fc_name'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $categoryAdder = new SupportCategoryAdder(
        trim($_POST['fc_name'] ?? '')
    );
    
    // Store in session for form persistence
    $_SESSION['fc_name'] = trim($_POST['fc_name'] ?? '');
    
    if ($categoryAdder->validate()) {
        $categoryAdder->add();
    }
    
    $_SESSION['msg'] = $categoryAdder->msg;
    header("Location: support-category-add.php");
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
                var fc_name = document.getElementById('fc_name');
                var message = "";
                var valid = true;
                
                if (fc_name.value.trim() === '') {
                    message = 'Please enter category name';
                    fc_name.focus();
                    valid = false;
                }
                
                if (!valid) {
                    document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                    document.getElementById('msg').className = "alert alert-danger";
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
                        <a href="support-category-list.php">Manage Support Categories</a>
                    </li>
                    <li class="active">Add Category</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Add Support Category
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Create a new support category
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" onsubmit="return validateForm();">
                            
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>

                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Category Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Category Name <span style="color:#F00">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="fc_name" id="fc_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($fc_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Enter category name"/>
                                    <span class="help-block">e.g., Technical Support, Billing, General Inquiry</span>
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
                                    <a href="support-category-list.php" class="btn btn-primary">
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
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Real-time validation
        $('#fc_name').on('keyup', function() {
            if ($(this).val().trim() === '') {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
            } else {
                $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
            }
        });
        
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
    });
</script>

<style>
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }
    .has-error input {
        border-color: #d15b47 !important;
    }
    .has-success input {
        border-color: #82af6f !important;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>