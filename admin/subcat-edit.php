<?php
/**
 * File: subcat-edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل التصنيفات الفرعية
 * Edit subcategories
 * 
 * Features:
 * - تعديل التصنيف الفرعي
 * - تغيير التصنيف الرئيسي
 * - تغيير التصنيف الوسيط
 * - تحديث اسم التصنيف والاسم المختصر
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
 * Class SubCategoryEditor
 * 
 * Handles subcategory editing operations
 */
class SubCategoryEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Main category ID */
    private int $mcat_id = 0;
    
    /** @var int Category ID */
    private int $cat_id = 0;
    
    /** @var string Subcategory ID (MD5) */
    private string $scat_id;
    
    /** @var string Subcategory name */
    private string $scat_name = '';
    
    /** @var string Subcategory sort name */
    private string $scat_sort_name = '';
    
    /**
     * Constructor
     * 
     * @param string $scat_id MD5 hashed subcategory ID
     */
    public function __construct(string $scat_id) {
        $this->scat_id = $scat_id;
    }
    
    /**
     * Get subcategory details
     * 
     * @return array|null Subcategory details with hierarchy
     */
    public function getDetails(): ?array {
        global $con;
        
        $sql = "SELECT 
                    s.pc_id as subcat_id,
                    s.pc_name as subcat_name,
                    s.pc_sort_name as subcat_sort,
                    c.pc_id as cat_id,
                    c.pc_name as cat_name,
                    m.pc_id as maincat_id,
                    m.pc_name as maincat_name
                FROM product_category s
                JOIN product_category c ON s.pc_parent_id = c.pc_id
                JOIN product_category m ON c.pc_parent_id = m.pc_id
                WHERE m.pc_parent_id = '0' 
                    AND m.pc_status = '1' 
                    AND c.pc_status = '1' 
                    AND s.pc_status = '1' 
                    AND md5(s.pc_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->scat_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return [
                'subcat_id' => (int)$row['subcat_id'],
                'subcat_name' => $row['subcat_name'],
                'subcat_sort' => $row['subcat_sort'],
                'cat_id' => (int)$row['cat_id'],
                'cat_name' => $row['cat_name'],
                'maincat_id' => (int)$row['maincat_id'],
                'maincat_name' => $row['maincat_name']
            ];
        }
        
        mysqli_stmt_close($stmt);
        return null;
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
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Sub Category sort name.</div>';
            return false;
        }
        
        if (strlen($this->scat_sort_name) > 24) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Sub Category sort name within 24 characters.</div>';
            return false;
        }
        
        // Check for duplicate name
        if ($this->isDuplicateName()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> A subcategory with this name already exists under the selected category.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if subcategory name already exists (excluding current)
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateName(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM product_category 
                WHERE pc_parent_id = ? AND pc_name = ? AND pc_status = 1 
                AND md5(pc_id) != ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "iss", $this->cat_id, $this->scat_name, $this->scat_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Update subcategory
     */
    public function update(): void {
        global $con;
        
        $sql = "UPDATE product_category SET
                pc_name = ?,
                pc_sort_name = ?,
                pc_parent_id = ?
                WHERE md5(pc_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ssis", $this->scat_name, $this->scat_sort_name, $this->cat_id, $this->scat_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Subcategory updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to update subcategory</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get main categories
     * 
     * @return array List of main categories
     */
    public static function getMainCategories(): array {
        global $con;
        
        $categories = [];
        $sql = "SELECT pc_id, pc_name FROM product_category WHERE pc_parent_id = 0 AND pc_status = 1 ORDER BY pc_name";
        $result = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = [
                'id' => (int)$row['pc_id'],
                'name' => $row['pc_name']
            ];
        }
        
        return $categories;
    }
}

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get token
$fullToken = $_GET['token'] ?? '';
if (empty($fullToken)) {
    header("Location: subcat-view.php");
    exit;
}

$token = substr($fullToken, 4); // Remove first 4 characters (random number)

// Initialize editor
$editor = new SubCategoryEditor($token);
$details = $editor->getDetails();

if (!$details) {
    header("Location: subcat-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->mcat_id = (int)($_POST['mcat_id'] ?? 0);
    $editor->cat_id = (int)($_POST['cat_id'] ?? 0);
    $editor->scat_name = trim($_POST['scat_name'] ?? '');
    $editor->scat_sort_name = trim($_POST['scat_sort_name'] ?? '');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: subcat-edit.php?token=" . $fullToken);
    exit;
}

// Get main categories for dropdown
$mainCategories = SubCategoryEditor::getMainCategories();
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
                } else if (scat_sort_name.value.length > 24) {
                    message = 'Please enter Sub Category Sort name within 24 characters';
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
                    <li class="active">Edit Sub Category</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Sub Category
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($details['subcat_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
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
                                        <?php foreach ($mainCategories as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo ($cat['id'] === $details['maincat_id']) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                        <?php 
                                        $catsql = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id != 0 AND pc_status = 1 AND pc_parent_id = " . $details['maincat_id'] . " ORDER BY pc_name");
                                        while ($catrow = mysqli_fetch_object($catsql)):
                                            $selected = ($catrow->pc_id == $details['cat_id']) ? 'selected="selected"' : '';
                                        ?>
                                            <option value="<?php echo (int)$catrow->pc_id; ?>" <?php echo $selected; ?>>
                                                <?php echo htmlspecialchars($catrow->pc_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
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
                                           value="<?php echo htmlspecialchars($details['subcat_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
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
                                           value="<?php echo htmlspecialchars($details['subcat_sort'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           maxlength="24" placeholder="Enter short name for sorting"/>
                                    <span class="help-block">Maximum 24 characters. A short, unique identifier for this subcategory.</span>
                                    <span id="sort_counter" class="counter">24 characters remaining</span>
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
        
        // Character counter for sort name
        $('#scat_sort_name').on('keyup', function() {
            var maxLength = 24;
            var currentLength = $(this).val().length;
            var remaining = maxLength - currentLength;
            
            if (remaining < 0) {
                $(this).val($(this).val().substring(0, maxLength));
                remaining = 0;
            }
            
            $('#sort_counter').text(remaining + ' characters remaining');
            
            if (remaining < 5) {
                $('#sort_counter').css('color', '#d15b47');
            } else {
                $('#sort_counter').css('color', '#777');
            }
        }).trigger('keyup');
        
        // Real-time validation for required fields
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
    .counter {
        font-size: 11px;
        color: #777;
        margin-left: 10px;
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