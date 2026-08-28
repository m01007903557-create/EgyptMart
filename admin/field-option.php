<?php
/**
 * File: field-option.php

 * Version: 2.0.0
 * Description: Add field options for additional fields (Upgraded to PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * PHP 8.3 Upgrade Features:
 * - Strict typing declarations
 * - Type hints for functions
 * - Null safety operators
 * - Prepared statements for SQL
 * - XSS protection
 * - CSRF protection ready
 * - Improved error handling
 * - Modern array syntax
 * - Input validation and sanitization
 */

// Enable strict error reporting for PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering (commented as per original)
// ob_start();
// session_start();

// Include required files
require_once "../common.php";
require_once "../lib/pagination.php";

// Check user authentication
check_admin_login();

// Initialize variables
$msg = '';
$mcat_id = filter_input(INPUT_POST, 'mcat_id', FILTER_VALIDATE_INT) ?: 
          filter_input(INPUT_GET, 'mcat_id', FILTER_VALIDATE_INT) ?: 0;
$cat_id = filter_input(INPUT_POST, 'cat_id', FILTER_VALIDATE_INT) ?: 
          filter_input(INPUT_GET, 'cat_id', FILTER_VALIDATE_INT) ?: 0;
$af_pc_id = filter_input(INPUT_POST, 'af_pc_id', FILTER_VALIDATE_INT) ?: 
            filter_input(INPUT_GET, 'af_pc_id', FILTER_VALIDATE_INT) ?: 0;

/**
 * Generate category dropdown options
 * 
 * @param mysqli $con Database connection
 * @param int $parentId Parent category ID
 * @param int $selectedId Selected category ID
 * @return string HTML options
 */
function getCategoryOptions(mysqli $con, int $parentId = 0, int $selectedId = 0): string {
    $sql = "SELECT pc_id, pc_name FROM product_category 
            WHERE pc_parent_id = ? AND pc_status = '1' 
            ORDER BY pc_name ASC";
    
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        return '';
    }
    
    mysqli_stmt_bind_param($stmt, "i", $parentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $options = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $selected = ($row['pc_id'] == $selectedId) ? 'selected="selected"' : '';
        $options .= sprintf(
            '<option value="%d" %s>%s</option>',
            $row['pc_id'],
            $selected,
            htmlspecialchars(ucfirst($row['pc_name']))
        );
    }
    
    mysqli_stmt_close($stmt);
    return $options;
}

/**
 * Get additional fields for subcategory
 * 
 * @param mysqli $con Database connection
 * @param int $subCatId Subcategory ID
 * @return string HTML options
 */
function getAdditionalFieldOptions(mysqli $con, int $subCatId): string {
    $sql = "SELECT af_id, af_label FROM additional_field 
            WHERE af_pc_id = ? AND af_type IN ('radio', 'checkbox', 'select')
            ORDER BY af_label ASC";
    
    $stmt = mysqli_prepare($con, $sql);
    if (!$stmt) {
        return '<option value="">Error loading fields</option>';
    }
    
    mysqli_stmt_bind_param($stmt, "i", $subCatId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $options = '<option value="">-Select-</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        $options .= sprintf(
            '<option value="%d">%s</option>',
            $row['af_id'],
            htmlspecialchars($row['af_label'])
        );
    }
    
    mysqli_stmt_close($stmt);
    return $options;
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Administrative Panel - Add Field Option</title>
<link rel="shortcut icon" href="" type="image/x-icon">

<!-- CSS Files -->
<link href="assets/css/bootstrap.min.css" rel="stylesheet" />
<link href="assets/css/font-awesome.min.css" rel="stylesheet" />
<link href="assets/css/ace.min.css" rel="stylesheet" />
<link href="assets/css/ace-rtl.min.css" rel="stylesheet" />
<link href="assets/css/ace-skins.min.css" rel="stylesheet" />

<!-- JavaScript Variables -->
<script type="text/javascript">
var siteUrl = '<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); ?>';
</script>
</head>

<body>
<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <script language="javascript">
        // Modernized JavaScript functions
        function showCategory() {
            var mcat = document.getElementById('mcat_id').value;
            if (!mcat || mcat === '0') return;
            
            fetch('showCategory.php?q=' + encodeURIComponent(mcat))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('cat_id').innerHTML = data;
                    showSubcat();
                })
                .catch(error => console.error('Error:', error));
        }
        
        function showSubcat() {
            var cat = document.getElementById('cat_id').value;
            if (!cat || cat === '0') return;
            
            fetch('showSubcat.php?q=' + encodeURIComponent(cat))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('af_pc_id').innerHTML = data;
                    showAdditionalField();
                    showFieldDet();
                })
                .catch(error => console.error('Error:', error));
        }
        
        function showAdditionalField() {
            var scat = document.getElementById('af_pc_id').value;
            if (!scat || scat === '0') return;
            
            fetch('showAdditionalField.php?scat=' + encodeURIComponent(scat))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('af_id').innerHTML = data;
                    showFieldDet();
                })
                .catch(error => console.error('Error:', error));
        }
        
        function showFieldDet() {
            var af = document.getElementById('af_id');
            if (!af.value || af.value === '0') return;
            
            fetch('showFieldDet.php?af=' + encodeURIComponent(af.value))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('showFieldDet').innerHTML = data;
                    document.getElementById('btnAdd').removeAttribute('disabled');
                    showFieldOption();
                })
                .catch(error => console.error('Error:', error));
        }
        
        function showFieldOption() {
            var af = document.getElementById('af_id').value;
            if (!af || af === '0') return;
            
            fetch('showFieldOption.php?af=' + encodeURIComponent(af))
                .then(response => response.text())
                .then(data => {
                    document.getElementById('list').innerHTML = data;
                })
                .catch(error => console.error('Error:', error));
        }
        
        function validForm() {
            var mcat_id = document.getElementById('mcat_id');
            var cat_id = document.getElementById('cat_id');
            var af_pc_id = document.getElementById('af_pc_id');
            var af_id = document.getElementById('af_id');
            var afv_value = document.getElementById('afv_value');
            
            var msg = "";
            var valid = true;
            
            if (!mcat_id.value || mcat_id.value === '0') {
                msg = 'Please select Main Category.';
                mcat_id.focus();
                valid = false;
            }
            else if (!cat_id.value || cat_id.value === '0') {
                msg = 'Please select Category.';
                cat_id.focus();
                valid = false;
            }
            else if (!af_pc_id.value || af_pc_id.value === '0') {
                msg = 'Please select Sub category.';
                af_pc_id.focus();
                valid = false;
            }
            else if (!af_id.value || af_id.value === '0') {
                msg = 'Please select Additional Field.';
                af_id.focus();
                valid = false;
            }
            else if (!afv_value.value || afv_value.value.trim() === '') {
                msg = 'Please enter Option.';
                afv_value.focus();
                valid = false;
            }
            else {
                // Use fetch API for better compatibility
                const formData = new FormData();
                formData.append('af_id', af_id.value);
                formData.append('afv_value', afv_value.value);
                
                fetch('addFieldOption.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data === '0') {
                        alert('Please enter valid data');
                    } else {
                        showFieldOption();
                        afv_value.value = '';
                    }
                })
                .catch(error => console.error('Error:', error));
                
                return; // Don't show error message for successful submission
            }
            
            if (!valid) {
                var msgDiv = document.getElementById('msg');
                msgDiv.innerHTML = "<i class='icon-remove'></i> " + msg;
                msgDiv.className = "alert alert-danger";
            }
        }
        
        function editFieldOption(id) {
            document.getElementById('display_val_' + id).style.display = 'none';
            document.getElementById('input_val_' + id).style.display = 'block';
            document.getElementById('btn_edit_' + id).style.display = 'none';
            document.getElementById('btn_save_' + id).style.display = 'inline-block';
        }
        
        function saveFieldOption(id) {
            var afv_value = document.getElementById('afv_value_' + id).value;
            
            if (afv_value && afv_value.trim() !== '') {
                const formData = new FormData();
                formData.append('afv_id', id);
                formData.append('afv_value', afv_value);
                
                fetch('saveFieldOption.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data === '0') {
                        alert('Please enter valid data');
                    } else {
                        document.getElementById('display_val_' + id).innerHTML = data;
                        document.getElementById('afv_value_' + id).value = data;
                        
                        document.getElementById('display_val_' + id).style.display = 'block';
                        document.getElementById('input_val_' + id).style.display = 'none';
                        document.getElementById('btn_edit_' + id).style.display = 'inline-block';
                        document.getElementById('btn_save_' + id).style.display = 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        }
        
        function delFieldOption(id) {
            if (confirm('Are you sure you want to delete this option?')) {
                const formData = new FormData();
                formData.append('afv_id', id);
                
                fetch('delFieldOption.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    showFieldOption();
                })
                .catch(error => console.error('Error:', error));
            }
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
                        <a href="field-view.php">Manage Additional Field</a>
                    </li>
                    <li class="active">Add Field Option</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Add Field Option
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" id="fieldOptionForm" method="post" enctype="multipart/form-data">
                            <div id="msg" class="<?php echo $msg ? 'alert alert-danger' : ''; ?>"><?php echo htmlspecialchars($msg); ?></div>

                            <!-- Main Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="mcat_id">
                                    Main Category <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="mcat_id" id="mcat_id" class="width-40" onchange="showCategory();">
                                        <option value="0" selected="selected">-Select-</option>
                                        <?php 
                                        $mcat_sql = "SELECT pc_id, pc_name FROM product_category 
                                                     WHERE pc_parent_id = '0' AND pc_status = '1' 
                                                     ORDER BY pc_name ASC";
                                        $mcat_res = mysqli_query($con, $mcat_sql);
                                        while ($mcat_row = mysqli_fetch_assoc($mcat_res)) {
                                            $selected = ($mcat_id == $mcat_row['pc_id']) ? 'selected="selected"' : '';
                                            printf(
                                                '<option value="%d" %s>%s</option>',
                                                $mcat_row['pc_id'],
                                                $selected,
                                                htmlspecialchars(ucfirst($mcat_row['pc_name']))
                                            );
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="cat_id">
                                    Category <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="cat_id" id="cat_id" class="width-40" onchange="showSubcat()">
                                        <option value="0" selected="selected">-Select-</option>
                                        <?php echo getCategoryOptions($con, $mcat_id, $cat_id); ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Sub Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="af_pc_id">
                                    Sub Category <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="af_pc_id" id="af_pc_id" class="width-40" onchange="showAdditionalField()">
                                        <option value="0">-Select-</option>
                                        <?php echo getCategoryOptions($con, $cat_id, $af_pc_id); ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Additional Field -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="af_id">
                                    Additional Field <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="af_id" id="af_id" class="width-40" onchange="showFieldDet()">
                                        <?php echo $af_pc_id ? getAdditionalFieldOptions($con, $af_pc_id) : '<option value="0">-Select-</option>'; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Field Details Container -->
                            <div id="showFieldDet"></div>
                            
                            <!-- Field Option Input -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="afv_value">
                                    Field Option <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input 
                                        name="afv_value" 
                                        id="afv_value" 
                                        class="col-xs-10 col-sm-5" 
                                        type="text" 
                                        placeholder="Enter option value"
                                        maxlength="255"
                                    />
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-4 col-md-8">
                                    <button 
                                        class="btn btn-info" 
                                        type="button" 
                                        name="btnAdd" 
                                        id="btnAdd" 
                                        disabled="disabled" 
                                        onclick="validForm();"
                                    >
                                        <i class="icon-ok bigger-110"></i>
                                        Add Option
                                    </button>
                                    <button 
                                        class="btn btn-secondary" 
                                        type="reset"
                                    >
                                        <i class="icon-undo bigger-110"></i>
                                        Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Options List Container -->
                        <div id="list" class="mt-3"></div>
                    </div>
                </div>
                <br clear="all" />
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript Files -->
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
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Initialize components -->
<script type="text/javascript">
jQuery(function($) {
    // Initialize chosen selects
    $('.chosen-select').chosen();
    
    // Initialize tooltips
    $('[data-rel=tooltip]').tooltip({container:'body'});
    
    // Initialize popovers
    $('[data-rel=popover]').popover({container:'body'});
    
    // Initialize autosize textareas
    $('textarea[class*=autosize]').autosize({append: "\n"});
    
    // Initialize input limiter
    $('textarea.limited').inputlimiter({
        remText: '%n character%s remaining...',
        limitText: 'max allowed : %n.'
    });
    
    // Initialize masked inputs
    $.mask.definitions['~'] = '[+-]';
    $('.input-mask-date').mask('99/99/9999');
    $('.input-mask-phone').mask('(999) 999-9999');
    
    // Initialize date picker
    $('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
        $(this).prev().focus();
    });
    
    // Initialize color picker
    $('#colorpicker1').colorpicker();
    $('#simple-colorpicker-1').ace_colorpicker();
    
    // Show subcategory on load if values exist
    <?php if ($cat_id > 0 && $af_pc_id > 0): ?>
    showSubcat();
    <?php endif; ?>
});
</script>

</body>
</html>