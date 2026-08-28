<?php
/**
 * File: create_account.php

 * Description: صفحة تسجيل المستخدم الجديد مع التحقق من البيانات وإرسال البريد الإلكتروني
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول - إذا كان المستخدم مسجلاً بالفعل، يتم تحويله
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    header("Location: index.php");
    exit;
}

global $con;

// =============================================
// تحديد الدولة الافتراضية بناءً على IP
// =============================================
$user_country = ip_info("Visitor", "Country") ?? '';
$phone_code = '';
$default_country_id = 0;
$default_country = $user_country;

if (!empty($user_country)) {
    $sql = "SELECT cn_id, cn_ph, cn_name FROM country 
            WHERE cn_status = '1' AND cn_name LIKE ? 
            ORDER BY cn_id ASC LIMIT 1";
    
    $search_term = $user_country . '%';
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 's', $search_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_object($result)) {
        $phone_code = $row->cn_ph ?? '';
        $default_country_id = (int)$row->cn_id;
        $default_country = $row->cn_name ?? '';
    }
    mysqli_stmt_close($stmt);
}

// =============================================
// استرجاع بيانات الجلسة (إذا وجدت)
// =============================================
$session_data = [
    'msg' => $_SESSION['msg'] ?? '',
    'name_prefix' => $_SESSION['name_prefix'] ?? '',
    'fname' => $_SESSION['fname'] ?? '',
    'lname' => $_SESSION['lname'] ?? '',
    'email' => $_SESSION['email'] ?? '',
    'country' => $_SESSION['country'] ?? '',
    'ph_country' => $_SESSION['ph_country'] ?? '',
    'mobile1' => $_SESSION['mobile1'] ?? '',
    'website' => $_SESSION['website'] ?? '',
    'pass' => $_SESSION['pass'] ?? '',
    'accept' => $_SESSION['accept'] ?? ''
];

// مسح بيانات الجلسة بعد استرجاعها
foreach (array_keys($session_data) as $key) {
    if ($key !== 'msg') {
        unset($_SESSION[$key]);
    }
}
unset($_SESSION['msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    
    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/template.css" rel="stylesheet">
    <link href="css/font-awesome.css" rel="stylesheet">
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
    <link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
    <link rel="stylesheet" type="text/css" href="css/msdropdown/dd.css" />
    <link rel="stylesheet" type="text/css" href="css/msdropdown/flags.css" />
    
    <!-- Google Fonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300' rel='stylesheet' type='text/css'>
    
    <style>
        .divider { display:none; }
        .ddTitleText { background: white; }
        .dd .ddTitle .ddTitleText {
            padding: 8px 45px 7px 6px;
            border: 1px solid #cccccc42;
            border-radius: 4px;
            height: 32px;
        }
        .ddChild { overflow: scroll!important; }
    </style>
</head>
<body>
    <header>
        <div id="res-mob1">
            <?php include __DIR__ . "/includes/header_new.php"; ?>
            <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName(), ENT_QUOTES, 'UTF-8'); ?>" width="1" height="1"></div>
        </div>
    </header>
    
    <div id="middle">
        <div class="container">
            <div class="row">
                <div class="top-btn">
                    <div class="col-sm-4">
                        <div class="first-btn active"><span>1</span>Register Your Business Profile</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="first-btn"><span>2</span>Select Membership Type</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="first-btn"><span>3</span>Create Your Account on EgyptMART</div>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
            </div>
            
            <div class="row">
                <!-- القسم الأيسر - المزايا -->
                <div class="mid-left col-sm-5">
                    <div class="arobeb">
                        <h3>The EgyptMART Advantage</h3>
                        <ul>
                            <li>Create Your FREE Catalog</li>
                            <li>Got listing in relevant products categories</li>
                            <li>Find new markets by promoting your products 24×7</li>
                            <li>Get business enquiries from buyers across the world</li>
                        </ul>
                        <div class="clr"></div>
                    </div>
                    
                    <div class="mid-image">
                        <center><img src="images/left-image.jpg" alt="Advantage"></center>
                    </div>
                    
                    <div class="aloud">
                        <div class="col-sm-6 s1"><span>1</span> All Over the World</div>
                        <div class="col-sm-6 s1"><span>2</span> In Arab Countries</div>
                        <div class="col-sm-6 s1"><span>3</span> In Your Country</div>
                        <div class="col-sm-6 s1"><span>4</span> In Your City Only</div>
                        <div class="clr"></div>
                    </div>
                </div>
                
                <!-- القسم الأيمن - نموذج التسجيل -->
                <div class="mid-right col-sm-7">
                    <div class="warning-new">
                        <img style="float:left; padding-right:10px;" src="images/warning.jpg" alt="Warning">
                        <div>EgyptMART hereby requests the members authentic information towards their business statutory details.</div>
                    </div>
                    
                    <div class="account-from">
                        <h2><strong>Create Your Account on EgyptMART</strong></h2>
                        <div class="warning">Authenticated company info will help EgyptMART to promote your Products / Services successfully!</div>
                        
                        <form class="form-horizontal" action="createAccount.php" method="post" name="ModReg" id="registrationForm">
                            <div id="message" class="sbox nt bnr fw" style="text-align:left; width:389px; padding:1% 1% 1% 5%; display:none; margin-left:87px;"></div>
                            
                            <?php if (!empty($session_data['msg'])): ?>
                            <div style="text-align:left; width:389px; padding:1% 1% 1% 5%; display:block; margin-left:87px;" class="sbox nt bnr fw" id="message">
                                <?php echo $session_data['msg']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Contact mobile -->
                            <div class="form-group">
                                <label for="ph_country" class="col-sm-4 control-label">* Contact mobile/ Cell Phone</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control pull-left" maxlength="6" name="ph_country" id="ph_country" 
                                           placeholder="Code" style="width:20%; background:white" 
                                           value="<?php echo htmlspecialchars($phone_code, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="text" class="form-control pull-right" name="mobile1" id="mobile1" 
                                           value="<?php echo htmlspecialchars($session_data['mobile1'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Mobile Number" style="width:80%;">
                                </div>
                            </div>
                            
                            <!-- Email -->
                            <div class="form-group">
                                <label for="email" class="col-sm-4 control-label">* Your Business Mail</label>
                                <div class="col-sm-7">
                                    <input type="email" class="form-control" name="email" id="email" 
                                           value="<?php echo htmlspecialchars($session_data['email'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="email@example.com">
                                    <input type="hidden" name="email_exists" id="email_exists" value="0">
                                </div>
                            </div>
                            
                            <!-- Country -->
                            <div class="form-group">
                                <label for="nncountrynn" class="col-sm-4 control-label">* Country</label>
                                <div class="col-sm-7">
                                    <input name="country" value="<?php echo (int)$default_country_id; ?>" id="country" type="hidden">
                                    <select name="nncountrynn" id="nncountrynn" style="width:100%;">
                                        <?php
                                        $sql = "SELECT cn_id, cn_ph, cn_name, cn_flag FROM country 
                                                WHERE cn_status = '1' ORDER BY cn_id ASC";
                                        $result = mysqli_query($con, $sql);
                                        while ($row = mysqli_fetch_object($result)):
                                            $selected = ((int)$row->cn_id === $default_country_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo (int)$row->cn_id; ?>" 
                                                data-zipcode="<?php echo htmlspecialchars($row->cn_ph ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-image="images/country_flag/<?php echo htmlspecialchars($row->cn_flag ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-imagecss="flag ad"
                                                data-title="<?php echo htmlspecialchars(ucfirst($row->cn_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars(ucfirst($row->cn_name ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- State / City -->
                            <div class="form-group">
                                <label for="city_others" class="col-sm-4 control-label">* State / City</label>
                                <div class="col-sm-7">
                                    <input name="city" value="" id="city" type="hidden">
                                    <input type="text" class="form-control" id="city_others" name="city_others" 
                                           placeholder="City" style="width:30%; display:inline-block;">
                                    <input name="b_state" value="" id="b_state" type="hidden">
                                    <input type="text" class="form-control" name="state" id="state" 
                                           placeholder="State" style="width:34%; display:inline-block;">
                                    <input type="text" class="form-control" style="width:34%; display:inline-block;" 
                                           name="postal_code" id="postal_code" placeholder="Postal/Zip Code">
                                </div>
                            </div>
                            
                            <!-- Contact Person -->
                            <div class="form-group">
                                <label for="name_prefix" class="col-sm-4 control-label">* Contact Person</label>
                                <div class="col-sm-7">
                                    <select class="form-control" style="width:30%; display:inline-block;" name="name_prefix" id="name_prefix">
                                        <?php
                                        $prefixes = ["Mr.", "Ms.", "Mrs.", "Dr.", "Eng."];
                                        foreach ($prefixes as $prefix):
                                            $selected = ($prefix === $session_data['name_prefix']) ? 'selected="selected"' : '';
                                        ?>
                                        <option value="<?php echo htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($prefix, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" class="form-control" name="fname" id="fname" 
                                           value="<?php echo htmlspecialchars($session_data['fname'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="First Name" style="width:34%; display:inline-block;">
                                    <input type="text" class="form-control" name="lname" id="lname" 
                                           value="<?php echo htmlspecialchars($session_data['lname'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Last Name" style="width:34%; display:inline-block;">
                                </div>
                            </div>
                            
                            <!-- Designation -->
                            <div class="form-group">
                                <label for="designation" class="col-sm-4 control-label">* Designation / Job Title</label>
                                <div class="col-sm-7">
                                    <input name="designation" id="designation" class="form-control" type="text" 
                                           placeholder="Type Designation / Job Title">
                                    <input type="hidden" name="userdesignation" id="userdesignation">
                                </div>
                            </div>
                            
                            <!-- Business Name -->
                            <div class="form-group">
                                <label for="business_name" class="col-sm-4 control-label">* Business Name</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" placeholder="" name="business_name" 
                                           value="" id="business_name">
                                </div>
                            </div>
                            
                            <!-- Registration Authority -->
                            <div class="form-group">
                                <label for="authority" class="col-sm-4 control-label">* Registration Authority No.</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" value="" size="30" name="authority" 
                                           id="authority" placeholder="">
                                </div>
                            </div>
                            
                            <!-- Service Tax -->
                            <div class="form-group">
                                <label for="authority1" class="col-sm-4 control-label">* Service Tax. No.</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" value="" size="30" name="authority1" 
                                           id="authority1" placeholder="">
                                </div>
                            </div>
                            
                            <!-- Company Documents -->
                            <div class="form-group">
                                <label for="business_documents" class="col-sm-4 control-label">* Company Documents</label>
                                <div class="col-sm-7 upload_div">
                                    <input type="hidden" name="business_documents" id="business_documents">
                                    <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/CompanyImage.jpg" 
                                         id="business_doc" alt="Document">
                                    <input id="fileupload" type="file" name="files" style="cursor:pointer;">
                                    <span class="file_input">Add File</span>
                                    e.g Registration Authority, Service Tax and Business Card Documents, etc.
                                </div>
                            </div>
                            
                            <!-- Contact Person Photo -->
                            <div class="form-group">
                                <label for="profile_photo" class="col-sm-4 control-label">Contact Person Photo</label>
                                <div class="col-sm-7 upload_div">
                                    <input type="hidden" name="profile_photo" id="profile_photo">
                                    <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/upload.png" 
                                         id="profilephoto" alt="Profile">
                                    <input id="profileupload" type="file" name="files" style="cursor:pointer;">
                                    <span class="file_input">Add image</span>
                                    Add contact person photo to enhance your business visual impact.
                                </div>
                            </div>
                            
                            <!-- Website -->
                            <div class="form-group">
                                <label for="website" class="col-sm-4 control-label">Website Url</label>
                                <div class="col-sm-7">
                                    <input type="url" class="form-control" value="<?php echo htmlspecialchars($session_data['website'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           id="website" name="website" placeholder="http://example.com">
                                    <span id="helpBlock" class="help-block">http://example.com</span>
                                </div>
                            </div>
                            
                            <!-- Password -->
                            <div class="form-group">
                                <label for="pass" class="col-sm-4 control-label">* Create Password</label>
                                <div class="col-sm-7">
                                    <input name="pass" id="pass" type="password" class="form-control" 
                                           value="<?php echo htmlspecialchars($session_data['pass'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="Min. 6 characters">
                                </div>
                            </div>
                            
                            <!-- Terms -->
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-7">
                                    <div class="new_checkbox">
                                        <label>
                                            <input value="yes" name="accept" id="accept" type="checkbox">
                                            Yes I Agree <a href="terms.php" target="_blank">Terms of Use</a>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Submit -->
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-7">
                                    <input type="button" name="register" value="Select Membership Type >>" 
                                           class="btn btn-danger" onclick="checkvalid();">
                                    <span class="pull-right text-center">
                                        Already Member?<br>
                                        <a href="sign-in.php" style="font-weight:bold; color:#00F; font-size:20px;">Sign in</a>
                                    </span>
                                </div>
                            </div>
                        </form>
                        
                        <div class="clr"></div>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
            </div>
            <div class="clr"></div>
        </div>
        <div class="clr"></div>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/jquery.autocomplete.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
    <script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
    <script src="js/msdropdown/jquery.dd.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // تفعيل msDropdown
        $("#nncountrynn").msDropdown();
        
        // Autocomplete للدول
        $("#country_name").autocomplete("ajax-file/autocomplete_country.php", {
            selectFirst: true
        }).result(function(event, data, formatted) {
            $("#country").val(data[1]);
            $("#ph_country").val(data[2]);
            $("#reset").show();
            $("input#country_name").attr('disabled', 'disabled');
            
            // تفعيل autocomplete للولايات بعد اختيار الدولة
            $("#state").autocomplete("ajax-file/autocomplete_state.php?country=" + $("#country").val(), {
                selectFirst: true
            }).result(function(event, data, formatted) {
                $("#b_state").val(data[1]);
                $("#reset").show();
                $("input#state").attr('disabled', 'disabled');
            });
            
            // تفعيل autocomplete للمدن
            $("#city_others").autocomplete("ajax-file/showregisterUsercity.php", {
                selectFirst: true,
                extraParams: {country: $('#country').val()}
            }).result(function(event, data, formatted) {
                var dm = data[0].split(">>");
                $("#city_others").val(dm[0]);
                $("#state").val(dm[1]);
                $("#city").val(data[1]);
                $("#b_state").val(data[2]);
                $("#reset").show();
            });
        });
        
        // Autocomplete للولايات
        $("#state").autocomplete("ajax-file/autocomplete_state.php?country=" + $("#country").val(), {
            selectFirst: true
        }).result(function(event, data, formatted) {
            $("#b_state").val(data[1]);
            $("#reset").show();
            $("input#state").attr('disabled', 'disabled');
        });
        
        // Autocomplete للمدن
        $("#city_others").autocomplete("ajax-file/showregisterUsercity.php", {
            selectFirst: true,
            extraParams: {country: $('#country').val()}
        }).result(function(event, data, formatted) {
            var dm = data[0].split(">>");
            $("#city_others").val(dm[0]);
            $("#state").val(dm[1]);
            $("#city").val(data[1]);
            $("#b_state").val(data[2]);
            $("#reset").show();
        });
    });
    
    // تفعيل كود الدولة عند تغيير الدولة
    function thisisnowcode() {
        var necc = document.getElementById("nncountrynn").value;
        document.getElementById("country").value = necc;
        document.getElementById("ph_country").value = "+" + $("#nncountrynn").find(':selected').data("zipcode");
    }
    
    // تفعيل حقل الدولة
    function mable() {
        $("input#country_name").removeAttr('disabled');
        $("input#country_name").val('');
        $("#ph_country").val('');
        $("input#country").val('');
        $("#reset").hide();
    }
    
    // تفعيل حقل المدينة
    function blankcity() {
        $("#city_others").val('');
        $("#city").val('');
        $("#stateid").val('');
        $("#state").val('');
    }
    
    // تفعيل حقل المسمى الوظيفي
    function blankdesignation() {
        $("#designation").val('');
        $("#userdesignation").val('');
    }
    
    // التحقق من وجود البريد الإلكتروني
    function checkExistingEmail(eml) {
        $.post("ajax-file/isEmailExist.php", {eml: eml}, function(data) {
            $('#email_exists').val($.trim(data));
        });
    }
    
    // التحقق من صحة النموذج
    function checkvalid() {
        var businessname = document.getElementById('business_name');
        var email = document.getElementById('email');
        var authority = document.getElementById('authority');
        var authority1 = document.getElementById('authority1');
        var comapnyimage = document.getElementById('business_documents');
        var name_prefix = document.getElementById('name_prefix');
        var fname = document.getElementById('fname');
        var lname = document.getElementById('lname');
        var perposition = document.getElementById('userdesignation');
        var designation = document.getElementById('designation');
        var profileimage = document.getElementById('profile_photo');
        var ph_country = document.getElementById('ph_country');
        var country = document.getElementById('country');
        var mobile1 = document.getElementById('mobile1');
        var state = document.getElementById('b_state');
        var city = document.getElementById('city');
        var postal_code = document.getElementById('postal_code');
        var website = document.getElementById('website');
        var pass = document.getElementById('pass');
        var accept = document.getElementById('accept');
        
        var message = "";
        var valid = true;
        var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        
        // التحقق من البريد الإلكتروني إذا كان موجوداً
        if (email.value != '') {
            checkExistingEmail(email.value);
        }
        
        // التحقق من رقم الموبايل
        if (mobile1.value == '') {
            message = "Please enter Mobile Number";
            mobile1.focus();
            valid = false;
        } else if (isNaN(mobile1.value)) {
            message = "Mobile number must be numeric";
            mobile1.focus();
            valid = false;
        }
        // التحقق من البريد الإلكتروني
        else if (email.value == "" || email.value == null) {
            message = "Please enter Email";
            email.focus();
            valid = false;
        } else if (!email.value.match(is_email)) {
            message = "Please enter valid email";
            email.value = "";
            email.focus();
            valid = false;
        } else if (document.getElementById('email_exists').value == '1') {
            message = "Email already exists.";
            email.focus();
            valid = false;
        }
        // التحقق من الدولة
        else if (country.value == '') {
            message = "Please select country";
            country.focus();
            valid = false;
        } else if (ph_country.value == '') {
            message = "Country ISD Code Must Not Blank";
            ph_country.focus();
            valid = false;
        } else if (city.value == '') {
            message = "Please enter city";
            city.focus();
            valid = false;
        } else if (state.value == '') {
            message = "Please enter State";
            state.focus();
            valid = false;
        } else if (postal_code.value == '') {
            message = "Please enter postal Code";
            postal_code.focus();
            valid = false;
        } else if (fname.value == '') {
            message = "Please enter First Name";
            fname.focus();
            valid = false;
        } else if (!isNaN(fname.value)) {
            message = "Please enter valid First Name";
            fname.value = '';
            fname.focus();
            valid = false;
        } else if (lname.value == '') {
            message = "Please enter Last Name";
            lname.focus();
            valid = false;
        } else if (!isNaN(lname.value)) {
            message = "Please enter valid Last Name";
            lname.value = '';
            lname.focus();
            valid = false;
        } else if (designation.value == '') {
            message = "Please enter Job title.";
            designation.focus();
            valid = false;
        } else if (businessname.value == '') {
            message = "Please enter Business Name";
            businessname.focus();
            valid = false;
        } else if (authority.value == '') {
            message = "Please enter Registration Authority No.";
            authority.focus();
            valid = false;
        } else if (authority1.value == '') {
            message = "Please enter Service Tax No.";
            authority1.focus();
            valid = false;
        } else if (comapnyimage.value == '') {
            message = "Please Add Business Document";
            valid = false;
        } else if (website.value != '' && !website.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/)) {
            message = 'Please Enter Valid website Link';
            website.focus();
            valid = false;
        } else if (pass.value == '') {
            message = "Please enter password";
            pass.focus();
            valid = false;
        } else if (pass.value.length < 6) {
            message = "Password must be 6 characters long";
            pass.value = "";
            pass.focus();
            valid = false;
        } else if (!accept.checked) {
            message = "You must agree to the Terms of Use for your registration.";
            accept.focus();
            valid = false;
        } else {
            // إرسال البيانات
            $.post("createAccount.php", {
                name_prefix: name_prefix.value,
                fname: fname.value,
                lname: lname.value,
                email: email.value,
                ph_country: ph_country.value,
                country: country.value,
                mobile1: mobile1.value,
                website: website.value,
                pass: pass.value,
                city: city.value,
                city_others: document.getElementById('city_others').value,
                state: state.value,
                state_others: document.getElementById('state').value,
                postal_code: postal_code.value,
                businessname: businessname.value,
                authority: authority.value,
                perposition: designation.value,
                profileimage: profileimage.value,
                comapnyimage: comapnyimage.value,
                authority1: authority1.value
            }, function(data) {
                console.log(data);
                data = data.trim();
                var dt = data.split("|");
                if (dt[0] == '0') {
                    document.getElementById('message').style.display = "block";
                    document.getElementById('message').style.color = "red";
                    document.getElementById('message').innerHTML = dt[1];
                } else {
                    alert(dt[1]);
                    window.location = "membership_plans.php?from=1";
                }
            });
        }
        
        if (!valid) {
            document.getElementById('message').style.display = "block";
            document.getElementById('message').style.color = "red";
            document.getElementById('message').innerHTML = message;
        }
        
        return valid;
    }
    
    // رفع ملفات متعددة
    $(function() {
        var url = 'http://egyptmart.online/server/php/';
        
        $('#profileupload').fileupload({
            url: url,
            maxNumberOfFiles: 1,
            dataType: 'json',
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    $('#profile_photo').val(file.name);
                    $('#profilephoto').attr('src', file.thumbnailUrl);
                });
            }
        });
        
        $('#fileupload').fileupload({
            url: url,
            maxNumberOfFiles: 1,
            dataType: 'json',
            done: function(e, data) {
                $.each(data.result.files, function(index, file) {
                    $('#business_documents').val(file.name);
                    $('#business_doc').attr('src', file.thumbnailUrl);
                });
            }
        });
    });
    </script>
</body>
</html>