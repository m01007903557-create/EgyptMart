<?php
/**
 * File: subcat-add.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة تصنيف فرعي جديد
 * Add new subcategory
 * 
 * Features:
 * - إضافة تصنيف فرعي تحت تصنيف رئيسي
 * - اختيار التصنيف الرئيسي
 * - اختيار التصنيف الفرعي
 * - إضافة اسم التصنيف الفرعي والاسم المختصر
 * - التحقق من صحة المدخلات
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
 * Class SubCategoryAdder
 * 
 * Handles subcategory addition operations
 */
class SubCategoryAdder {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Main category ID */
    private int $mcat_id = 0;
    
    /** @var int Category ID */
    private int $cat_id = 0;
    
    /** @var string Subcategory name */
    private string $scat_name = '';
    
    /** @var string Subcategory sort name */
    private string $scat_sort_name = '';
    
    /**
     * Constructor
     * 
     * @param int $mcat_id Main category ID
     * @param int $cat_id Category ID
     * @param string $scat_name Subcategory name
     * @param string $scat_sort_name Subcategory sort name
     */
    public function __construct(
        int $mcat_id,
        int $cat_id,
        string $scat_name,
        string $scat_sort_name
    ) {
        $this->mcat_id = $mcat_id;
        $this->cat_id = $cat_id;
        $this->scat_name = $scat_name;
        $this->scat_sort_name = $scat_sort_name;
        
        // Store in session for form persistence
        $_SESSION['mcat_id'] = $this->mcat_id;
        $_SESSION['cat_id'] = $this->cat_id;
        $_SESSION['scat_name'] = $this->scat_name;
        $_SESSION['scat_sort_name'] = $this->scat_sort_name;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        if ($this->mcat_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose Main Category.</div>';
            return false;
        }
        
        if ($this->cat_id <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose Category.</div>';
            return false;
        }
        
        if (empty($this->scat_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Sub Category name.</div>';
            return false;
        }
        
        if (empty($this->scat_sort_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Sub Category Sort name.</div>';
            return false;
        }
        
        // Check for duplicate subcategory name
        if ($this->isDuplicateName()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> A subcategory with this name already exists under the selected category.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if subcategory name already exists
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM product_category 
                WHERE pc_parent_id = ? AND pc_name = ? AND pc_status = 1";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "is", $this->cat_id, $this->scat_name);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Add subcategory to database
     */
    public function add(): void {
        global $con;
        
        $sql = "INSERT INTO product_category SET
                pc_name = ?,
                pc_sort_name = ?,
                pc_parent_id = ?,
                pc_status = 1";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ssi", $this->scat_name, $this->scat_sort_name, $this->cat_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear session variables
            unset($_SESSION['mcat_id']);
            unset($_SESSION['cat_id']);
            unset($_SESSION['scat_name']);
            unset($_SESSION['scat_sort_name']);
            
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Sub Category added successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add subcategory</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get category name by ID
     * 
     * @param int $categoryId Category ID
     * @return string|null Category name
     */
    public static function getCategoryName(int $categoryId): ?string {
        global $con;
        
        $sql = "SELECT pc_name FROM product_category WHERE pc_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $categoryId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['pc_name'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
}

// Handle session messages and form persistence
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$mcat_id = $_SESSION['mcat_id'] ?? '';
$cat_id = $_SESSION['cat_id'] ?? '';
$scat_name = $_SESSION['scat_name'] ?? '';
$scat_sort_name = $_SESSION['scat_sort_name'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    
    $subcatAdder = new SubCategoryAdder(
        (int)($_POST['mcat_id'] ?? 0),
        (int)($_POST['cat_id'] ?? 0),
        trim($_POST['scat_name'] ?? ''),
        trim($_POST['scat_sort_name'] ?? '')
    );
    
    // Store in session for form persistence
    $_SESSION['mcat_id'] = (int)($_POST['mcat_id'] ?? 0);
    $_SESSION['cat_id'] = (int)($_POST['cat_id'] ?? 0);
    $_SESSION['scat_name'] = trim($_POST['scat_name'] ?? '');
    $_SESSION['scat_sort_name'] = trim($_POST['scat_sort_name'] ?? '');
    
    if ($subcatAdder->validate()) {
        $subcatAdder->add();
    }
    
    $_SESSION['msg'] = $subcatAdder->msg;
    header("Location: subcat-add.php");
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
            function showCategory() {
                var pc_id = document.getElementById('mcat_id').value;
                if (pc_id) {
                    $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
                        $('#cat_id').html(data);
                    }).fail(function() {
                        alert('Failed to load categories');
                    });
                } else {
                    $('#cat_id').html('<option value="">Select Category</option>');
                }
            }
            
            function validateForm() {
                var mcat_id = document.getElementById('mcat_id');
                var cat_id = document.getElementById('cat_id');
                var scat_name = document.getElementById('scat_name');
                var scat_sort_name = document.getElementById('scat_sort_name');
                
                var message = "";
                var valid = true;
                
                if (mcat_id.value === '' || mcat_id.value === '0') {
                    message = 'Please choose Main Category';
                    mcat_id.focus();
                    valid = false;
                } else if (cat_id.value === '' || cat_id.value === '0') {
                    message = 'Please choose Category';
                    cat_id.focus();
                    valid = false;
                } else if (scat_name.value.trim() === '') {
                    message = 'Please enter Sub Category name';
                    scat_name.focus();
                    valid = false;
                } else if (scat_sort_name.value.trim() === '') {
                    message = 'Please enter Sub Category Sort name';
                    scat_sort_name.focus();
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
                        <a href="subcat-view.php">Manage Sub Categories</a>
                    </li>
                    <li class="active">Add Sub Category</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Add New Sub Category
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Create a new subcategory
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" onsubmit="return validateForm();">
                            
                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>

                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Main Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Main Category <span style="color:#F00">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="mcat_id" id="mcat_id" class="chosen-select" onchange="showCategory();" style="width:300px;">
                                        <option value="0">Select Main Category</option>
                                        <?php 
                                        $mcatsql = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id = 0 AND pc_status = 1 ORDER BY pc_name");
                                        while ($mcatrow = mysqli_fetch_object($mcatsql)):
                                            $selected = ((int)$mcat_id === (int)$mcatrow->pc_id) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$mcatrow->pc_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($mcatrow->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Category <span style="color:#F00">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="cat_id" id="cat_id" style="width:300px;">
                                        <option value="0">Select Category</option>
                                        <?php if (!empty($mcat_id) && $mcat_id > 0): ?>
                                            <?php 
                                            $catsql = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id != 0 AND pc_parent_id = " . (int)$mcat_id . " AND pc_status = 1 ORDER BY pc_name");
                                            while ($catrow = mysqli_fetch_object($catsql)):
                                                $selected = ((int)$cat_id === (int)$catrow->pc_id) ? 'selected="selected"' : '';
                                            ?>
                                                <option value="<?php echo (int)$catrow->pc_id; ?>" <?php echo $selected; ?>>
                                                    <?php echo htmlspecialchars($catrow->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Subcategory Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Sub Category Name <span style="color:#F00">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="scat_name" id="scat_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($scat_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Enter subcategory name"/>
                                </div>
                            </div>

                            <!-- Sort Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Sort Name <span style="color:#F00">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="scat_sort_name" id="scat_sort_name" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($scat_sort_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Enter short name for sorting"/>
                                    <span class="help-block">A short, unique identifier for this subcategory</span>
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
                                    <a href="subcat-view.php" class="btn btn-primary">
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
        $(".chosen-select").chosen({width: '300px'});
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Real-time validation
        $('#scat_name, #scat_sort_name').on('keyup', function() {
            if ($(this).val().trim() === '') {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
            } else {
                $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
            }
        });
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