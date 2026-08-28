<?php
/**
 * File: user-edit.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل بيانات المستخدم الأساسية
 * Edit basic user information
 * 
 * Features:
 * - تعديل الاسم (اللقب، الاسم الأول، اسم العائلة)
 * - تعديل البريد الإلكتروني
 * - تعديل الدولة ورمز الهاتف
 * - تعديل رقم الموبايل
 * - تعديل الموقع الإلكتروني
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
 * Class UserEditor
 * 
 * Handles user information editing operations
 */
class UserEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int User ID */
    private int $usr_id;
    
    /** @var string Name prefix */
    private string $name_prefix = '';
    
    /** @var string First name */
    private string $fname = '';
    
    /** @var string Last name */
    private string $lname = '';
    
    /** @var string Email */
    private string $email = '';
    
    /** @var int Country ID */
    private int $country = 0;
    
    /** @var string Country phone code */
    private string $country_ph_code = '';
    
    /** @var string Mobile number */
    private string $mobile1 = '';
    
    /** @var string Website */
    private string $website = '';
    
    /** @var array Allowed name prefixes */
    private array $allowedPrefixes = ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Eng.'];
    
    /**
     * Constructor
     * 
     * @param int $usr_id User ID
     */
    public function __construct(int $usr_id) {
        $this->usr_id = $usr_id;
    }
    
    /**
     * Get user details
     * 
     * @return object|null User details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM user WHERE usr_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->usr_id);
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
        
        // Validate first name
        if (empty($this->fname)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter First Name.</div>';
            return false;
        }
        
        if (is_numeric($this->fname)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid First Name (letters only).</div>';
            return false;
        }
        
        if (strlen($this->fname) < 2) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> First Name must be at least 2 characters.</div>';
            return false;
        }
        
        // Validate last name
        if (empty($this->lname)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Last Name.</div>';
            return false;
        }
        
        if (is_numeric($this->lname)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid Last Name (letters only).</div>';
            return false;
        }
        
        if (strlen($this->lname) < 2) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Last Name must be at least 2 characters.</div>';
            return false;
        }
        
        // Validate email
        if (empty($this->email)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Email Address.</div>';
            return false;
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid Email Address.</div>';
            return false;
        }
        
        // Check for duplicate email
        if ($this->isDuplicateEmail()) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> This email address is already in use by another user.</div>';
            return false;
        }
        
        // Validate country
        if ($this->country <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Country.</div>';
            return false;
        }
        
        // Validate mobile
        if (empty($this->mobile1)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a Mobile Number.</div>';
            return false;
        }
        
        if (!ctype_digit($this->mobile1)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Mobile number must contain only digits.</div>';
            return false;
        }
        
        if (strlen($this->mobile1) < 6 || strlen($this->mobile1) > 15) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Mobile number must be between 6 and 15 digits.</div>';
            return false;
        }
        
        // Validate website if provided
        if (!empty($this->website) && !filter_var($this->website, FILTER_VALIDATE_URL)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a valid Website URL (include http:// or https://).</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if email already exists (excluding current user)
     * 
     * @return bool True if duplicate exists
     */
    private function isDuplicateEmail(): bool {
        global $con;
        
        $sql = "SELECT COUNT(*) as count FROM user WHERE email = ? AND usr_id != ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $this->email, $this->usr_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Validate name prefix
     * 
     * @param string $prefix Name prefix
     * @return bool True if valid
     */
    private function isValidPrefix(string $prefix): bool {
        return in_array($prefix, $this->allowedPrefixes, true);
    }
    
    /**
     * Update user information
     */
    public function update(): void {
        global $con;
        
        // Validate prefix
        if (!$this->isValidPrefix($this->name_prefix)) {
            $this->name_prefix = 'Mr.'; // Default to Mr. if invalid
        }
        
        $sql = "UPDATE user SET
                name_prefix = ?,
                fname = ?,
                lname = ?,
                email = ?,
                country = ?,
                country_ph_code = ?,
                mobile1 = ?,
                website = ?
                WHERE usr_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssssisssi",
            $this->name_prefix,
            $this->fname,
            $this->lname,
            $this->email,
            $this->country,
            $this->country_ph_code,
            $this->mobile1,
            $this->website,
            $this->usr_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> User updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to update user</div>';
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
            if ($name === 'country') {
                $this->$name = (int)$value;
            } else {
                $this->$name = $value;
            }
        }
    }
    
    /**
     * Get all countries for dropdown
     * 
     * @return array List of countries
     */
    public static function getCountries(): array {
        global $con;
        
        $countries = [];
        $sql = "SELECT cn_id, cn_name, cn_ph FROM country WHERE cn_status = '1' ORDER BY cn_name";
        $result = mysqli_query($con, $sql);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $countries[] = [
                'id' => (int)$row['cn_id'],
                'name' => $row['cn_name'],
                'phone_code' => $row['cn_ph']
            ];
        }
        
        return $countries;
    }
}

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get token
$fullToken = $_GET['token'] ?? '';
if (empty($fullToken)) {
    header("Location: user-list.php");
    exit;
}

$userId = (int)substr($fullToken, 4); // Remove first 4 characters (random number)

if ($userId <= 0) {
    header("Location: user-list.php");
    exit;
}

// Initialize editor
$editor = new UserEditor($userId);
$row = $editor->getDetails();

if (!$row) {
    header("Location: user-list.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editor->name_prefix = trim($_POST['name_prefix'] ?? 'Mr.');
    $editor->fname = trim($_POST['fname'] ?? '');
    $editor->lname = trim($_POST['lname'] ?? '');
    $editor->email = trim($_POST['email'] ?? '');
    $editor->country = (int)($_POST['country'] ?? 0);
    $editor->country_ph_code = trim($_POST['country_ph_code'] ?? '');
    $editor->mobile1 = trim($_POST['mobile1'] ?? '');
    $editor->website = trim($_POST['website'] ?? '');
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: user-edit.php?token=" . rand(1000, 9999) . $userId);
    exit;
}

// Get all countries for dropdown
$countries = UserEditor::getCountries();
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
                var fname = document.getElementById('fname');
                var lname = document.getElementById('lname');
                var email = document.getElementById('email');
                var country = document.getElementById('country');
                var mobile1 = document.getElementById('mobile1');
                var website = document.getElementById('website');

                var message = "";
                var valid = true;
                var emailPattern = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
                var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;

                if (fname.value.trim() === '') {
                    message = "Please enter First Name";
                    fname.focus();
                    valid = false;
                } else if (!isNaN(fname.value.trim())) {
                    message = "Please enter a valid First Name (letters only)";
                    fname.value = '';
                    fname.focus();
                    valid = false;
                } else if (lname.value.trim() === '') {
                    message = "Please enter Last Name";
                    lname.focus();
                    valid = false;
                } else if (!isNaN(lname.value.trim())) {
                    message = "Please enter a valid Last Name (letters only)";
                    lname.value = '';
                    lname.focus();
                    valid = false;
                } else if (email.value.trim() === '') {
                    message = "Please enter Email Address";
                    email.focus();
                    valid = false;
                } else if (!emailPattern.test(email.value.trim())) {
                    message = "Please enter a valid Email Address";
                    email.value = '';
                    email.focus();
                    valid = false;
                } else if (country.value === '' || country.value === '0') {
                    message = "Please select Country";
                    country.focus();
                    valid = false;
                } else if (mobile1.value.trim() === '') {
                    message = "Please enter Mobile Number";
                    mobile1.focus();
                    valid = false;
                } else if (isNaN(mobile1.value.trim())) {
                    message = "Mobile number must contain only digits";
                    mobile1.focus();
                    valid = false;
                } else if (website.value.trim() !== '' && !urlPattern.test(website.value.trim())) {
                    message = 'Please enter a valid Website URL (e.g., http://example.com)';
                    website.focus();
                    valid = false;
                }

                if (!valid) {
                    document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                    document.getElementById('msg').className = "alert alert-danger";
                }
                
                return valid;
            }
            
            function getCountryPhCode(id) {
                if (id && id !== '0') {
                    $.post("ajax-file/getCountryPhCode.php", {id: id}, function(data) {
                        $("#country_ph_code").val(data);
                    }).fail(function() {
                        alert('Failed to get country phone code');
                    });
                } else {
                    $("#country_ph_code").val('');
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
                        <a href="user-list.php">Manage Users</a>
                    </li>
                    <li class="active">Edit User</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit User
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars(trim(($row->fname ?? '') . ' ' . ($row->lname ?? '')), ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" onsubmit="return validateForm();">
                            
                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Name Fields -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Name:</label>
                                <div class="col-sm-9">
                                    <select name="name_prefix" id="name_prefix" class="col-sm-1" style="width:80px;">
                                        <?php 
                                        $prefixes = ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Eng.'];
                                        foreach ($prefixes as $prefix): 
                                        ?>
                                            <option value="<?php echo $prefix; ?>" <?php echo ($prefix === ($row->name_prefix ?? 'Mr.')) ? 'selected="selected"' : ''; ?>>
                                                <?php echo $prefix; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input name="fname" id="fname" class="col-xs-10 col-sm-4" type="text" 
                                           value="<?php echo htmlspecialchars($row->fname ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="First Name"/>
                                    <input name="lname" id="lname" class="col-xs-10 col-sm-4" type="text" 
                                           value="<?php echo htmlspecialchars($row->lname ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Last Name"/>
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Email:</label>
                                <div class="col-sm-9">
                                    <input name="email" id="email" class="col-xs-10 col-sm-5" type="email" 
                                           value="<?php echo htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                                </div>
                            </div>
                            
                            <!-- Country -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Country:</label>
                                <div class="col-sm-9">
                                    <select id="country" name="country" class="chosen-select" onchange="getCountryPhCode(this.value);" style="width:300px;">
                                        <option value="0">Select Country</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo $country['id']; ?>" 
                                                    data-phone="<?php echo $country['phone_code']; ?>"
                                                    <?php echo ($country['id'] === (int)($row->country ?? 0)) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Country Phone Code -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Country Phone Code:</label>
                                <div class="col-sm-9">
                                    <input name="country_ph_code" id="country_ph_code" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->country_ph_code ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           readonly="readonly"/>
                                </div>
                            </div>
                            
                            <!-- Mobile Number -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Mobile / Cell Phone:</label>
                                <div class="col-sm-9">
                                    <input name="mobile1" id="mobile1" class="col-xs-10 col-sm-5" type="tel" 
                                           value="<?php echo htmlspecialchars($row->mobile1 ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                                </div>
                            </div>
                            
                            <!-- Website -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Website:</label>
                                <div class="col-sm-9">
                                    <input name="website" id="website" class="col-xs-10 col-sm-5" type="url" 
                                           value="<?php echo htmlspecialchars($row->website ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="http://example.com"/>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <a href="user-details.php?token=<?php echo rand(1000, 9999) . md5((string)$userId); ?>" 
                                       class="btn btn-primary">
                                        <i class="icon-eye-open bigger-110"></i> View Details
                                    </a>
                                    <a href="user-list.php" class="btn">
                                        <i class="icon-arrow-left bigger-110"></i> Back to List
                                    </a>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                    <br clear="all"/>
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
        
        // Auto-load phone code on page load if country is selected
        var countrySelect = $('#country');
        if (countrySelect.val() && countrySelect.val() !== '0') {
            getCountryPhCode(countrySelect.val());
        }
        
        // Real-time validation
        $('#fname, #lname, #email, #mobile1').on('keyup', function() {
            if ($(this).val().trim() === '') {
                $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
            } else {
                $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
            }
        });
    });
</script>

<style>
    .has-error input, .has-error select {
        border-color: #d15b47 !important;
    }
    .has-success input, .has-success select {
        border-color: #82af6f !important;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>