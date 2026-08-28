<?php
/**
 * File: create_account.php

 * Description: صفحة تسجيل المستخدم الجديد (نسخة عربية محدثة)
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
    
    if ($row = mysqli_fetch_assoc($result)) {
        $phone_code = $row['cn_ph'] ?? '';
        $default_country_id = (int)$row['cn_id'];
        $default_country = $row['cn_name'] ?? '';
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
<html lang="ar" dir="ltr">
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
    <link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css">
    <link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
    <link rel="stylesheet" type="text/css" href="css/msdropdown/dd.css">
    <link rel="stylesheet" type="text/css" href="css/msdropdown/flags.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600,700,800,300" rel="stylesheet" type="text/css">
    
    <style>
        .divider { display: none; }
        .ddTitleText { background: white; }
        .dd .ddTitle .ddTitleText {
            padding: 8px 45px 7px 6px;
            border: 1px solid #cccccc42;
            border-radius: 4px;
            height: 32px;
        }
        .ddChild { overflow: scroll !important; }
    </style>
</head>
<body>
    <header>
        <div id="res-mob1">
            <?php include __DIR__ . "/includes/header_login.php"; ?>
        </div>
    </header>
    
    <div id="middle">
        <div class="container" id="signupform">
            <div class="row">
                <div class="top-btn">
                    <div class="col-sm-4">
                        <div class="first-btn active" title="Register Your Business Profile">
                            <span>1</span> سجل مجانا الآن فى ثلاث خطوات
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="first-btn"><span>2</span>إختار خطة مجانية للبدء</div>
                    </div>
                    <div class="col-sm-4">
                        <div class="first-btn"><span>3</span> إبدأ العمل والتجارة</div>
                    </div>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
            </div>
            
            <div class="row">
                <!-- القسم الأيسر - المزايا -->
                <div class="mid-left col-sm-5" title="The EgyptMART Advantage">
                    <div class="arobeb">
                        <h3>مزايا الإنضمام الى بوابة التجارة</h3>
                        <ul>
                            <li>إنشىء كتالوج منتجاتك التجارية</li>
                            <li>تلقى طلبات تسعير وشراء</li>
                            <li>إبحث عن إحتياجات أعمالك</li>
                            <li>تلقى إشعارات تجارية فى بريدك</li>
                        </ul>
                        <div class="clr"></div>
                    </div>
                    
                    <div class="mid-image">
                        <center><img src="images/left-image.jpg" alt="مزايا"></center>
                    </div>
                    
                    <div class="aloud">
                        <div class="col-sm-6 s1">تجارة داخل بلدى فقط <span>1</span></div>
                        <div class="col-sm-6 s1">تجارة داخل وخارج بلدى <span>2</span></div>
                        <div class="col-sm-6 s1">تجارة حول مدينتى فقط <span>3</span></div>
                        <div class="col-sm-6 s1">تجارة خارج بلدى فقط <span>4</span></div>
                        <div class="clr"></div>
                    </div>
                </div>
                
                <!-- القسم الأيمن - نموذج التسجيل -->
                <div class="mid-right col-sm-7">
                    <div class="warning-new">
                        <img style="float:right; padding-right:10px;" title="hereby requests the members authentic information towards their business statutory details." 
                             src="images/warning.jpg" alt="تحذير">
                        يساعد الأدمن المستخدم فى تسجيل حسابه عند تواصله
                    </div>
                    
                    <script>
                    function thisisnowcode() {
                        var necc = document.getElementById("nncountrynn").value;
                        document.getElementById("country").value = necc;
                        document.getElementById("ph_country").value = "+" + $("#nncountrynn").find(':selected').data("zipcode");
                    }
                    </script>
                    <input name="country" value="<?php echo (int)$default_country_id; ?>" id="country" type="hidden">
                    
                    <div class="account-from" title="Create Your Business Account on EgyptMART">
                        <h2><strong>إنشىء حسابك على بوابة التجارة</strong></h2>
                        <div class="warning">إنشىء حسابك فى ثلاث خطوات سهلة</div>
                        
                        <form class="form-horizontal" action="" method="post" name="ModReg">
                            <div id="message" class="sbox nt bnr fw" style="text-align:left; width:389px; padding:1% 1% 1% 5%; display:none; margin-left:87px;"></div>
                            
                            <?php if (!empty($session_data['msg'])): ?>
                            <div style="text-align:left; width:389px; padding:1% 1% 1% 5%; display:block; margin-left:87px;" class="sbox nt bnr fw" id="message">
                                <?php echo $session_data['msg']; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- رقم المحمول -->
                            <div class="form-group" title="Contact Mobile/ Cell Phone">
                                <label for="ph_country" class="col-sm-4 control-label">رقم المحمـول أو الهاتف الجوال *</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control pull-left" maxlength="6" name="ph_country" id="ph_country" 
                                           placeholder="مفتاح" style="width:20%; background:white" 
                                           value="<?php echo htmlspecialchars($phone_code, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="text" class="form-control pull-right" name="mobile1" id="mobile1" 
                                           value="<?php echo htmlspecialchars($session_data['mobile1'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="رقم المحمول" style="width:80%;">
                                </div>
                            </div>
                            
                            <!-- البريد الإلكتروني -->
                            <div class="form-group" title="Your Business Mail">
                                <label for="email" class="col-sm-4 control-label">البريد الألكترونى للشركة *</label>
                                <div class="col-sm-7">
                                    <input type="email" class="form-control" name="email" id="email" 
                                           value="<?php echo htmlspecialchars($session_data['email'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="email@example.com">
                                    <input type="hidden" name="email_exists" id="email_exists" value="0">
                                </div>
                            </div>
                            
                            <!-- الدولة -->
                            <div class="form-group" title="Country">
                                <label for="nncountrynn" class="col-sm-4 control-label">إختار البـلد *</label>
                                <div class="col-sm-7">
                                    <select name="nncountrynn" id="nncountrynn" style="width:100%;" onChange="thisisnowcode(); CheckIfChanged();">
                                        <?php
                                        $sql = "SELECT cn_id, cn_ph, cn_name, cn_flag FROM country 
                                                WHERE cn_status = '1' ORDER BY cn_id ASC";
                                        $result = mysqli_query($con, $sql);
                                        while ($row = mysqli_fetch_assoc($result)):
                                            $selected = ((int)$row['cn_id'] === $default_country_id) ? 'selected' : '';
                                        ?>
                                        <option value="<?php echo (int)$row['cn_id']; ?>" 
                                                data-zipcode="<?php echo htmlspecialchars(ucfirst($row['cn_ph'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                data-image="images/country_flag/<?php echo htmlspecialchars($row['cn_flag'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                                data-imagecss="flag ad"
                                                data-title="<?php echo htmlspecialchars(ucfirst($row['cn_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars(ucfirst($row['cn_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- المدينة والمحافظة -->
                            <div class="form-group" title="إختار مدينتك من القائمة">
                                <label for="city_others" class="col-sm-4 control-label">إكتب المدينة باللغة الإنجليزية *</label>
                                <div class="col-sm-7">
                                    <input name="city" value="" id="city" type="hidden">
                                    <input type="text" class="form-control" id="city_others" name="city_others" 
                                           placeholder="المدينة" style="width:30%; display:inline-block;">
                                    <input name="b_state" value="" id="b_state" type="hidden">
                                    <input type="text" class="form-control" name="state" id="state" 
                                           placeholder="المحافظة" style="width:34%; display:inline-block;">
                                    <input type="text" class="form-control" style="width:34%; display:inline-block;" 
                                           name="postal_code" id="postal_code" placeholder="الرمز البريدى">
                                </div>
                            </div>
                            
                            <!-- شخص الاتصال -->
                            <div class="form-group" title="Contact Person">
                                <label for="name_prefix" class="col-sm-4 control-label">شخص الإتصال المفوض *</label>
                                <div class="col-sm-7">
                                    <select class="form-control" style="width:30%; display:inline-block;" name="name_prefix" id="name_prefix">
                                        <?php
                                        $prefixes = ["السيد", "الأنسة", "السيدة", "دكتور", "مهندس"];
                                        foreach ($prefixes as $p):
                                            $selected = ($p === $session_data['name_prefix']) ? 'selected="selected"' : '';
                                        ?>
                                        <option value="<?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($p, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" class="form-control" name="fname" id="fname" 
                                           value="<?php echo htmlspecialchars($session_data['fname'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="الإسم الثانى" style="width:34%; display:inline-block;">
                                    <input type="text" class="form-control" name="lname" id="lname" 
                                           value="<?php echo htmlspecialchars($session_data['lname'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="الإسم الأول" style="width:34%; display:inline-block;">
                                </div>
                            </div>
                            
                            <!-- المسمى الوظيفي -->
                            <div class="form-group" title="المسمى الوظيفى">
                                <label for="designation" class="col-sm-4 control-label">وظيفة مسئول الإتصال *</label>
                                <div class="col-sm-7">
                                    <input name="designation" id="designation" class="form-control" type="text" 
                                           placeholder="أكتب المسمى الوظيفى" onClick="blankdesignation()">
                                    <input type="hidden" name="userdesignation" id="userdesignation">
                                </div>
                            </div>
                            
                            <!-- الاسم التجاري -->
                            <div class="form-group" title="Company Business Name">
                                <label for="business_name" class="col-sm-4 control-label">الإسم التجارى للشركة *</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" placeholder="" name="business_name" 
                                           value="" id="business_name">
                                </div>
                            </div>
                            
                            <!-- السجل التجاري -->
                            <div class="form-group" title="Registration Authority No.">
                                <label for="authority" class="col-sm-4 control-label">رقم السجل التجارى *</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" value="" size="30" name="authority" 
                                           id="authority" placeholder="">
                                </div>
                            </div>
                            
                            <!-- الرقم الضريبي -->
                            <div class="form-group" title="Service Tax. No.">
                                <label for="authority1" class="col-sm-4 control-label">رقم التسجيل الضريبى *</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control" value="" size="30" name="authority1" 
                                           id="authority1" placeholder="">
                                </div>
                            </div>
                            
                            <!-- مستندات الشركة -->
                            <div class="form-group" title="One Company Documents">
                                <label for="business_documents" class="col-sm-4 control-label">حمل صورة مستند صحة الشركة *</label>
                                <div class="col-sm-7 upload_div">
                                    <input type="hidden" name="business_documents" id="business_documents">
                                    <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/CompanyImage.jpg" 
                                         id="business_doc" alt="Company Document">
                                    <input id="fileupload" type="file" name="files" style="cursor:pointer;">
                                    <span class="file_input">حمل المستند</span>
                                    مثال صورة السجل التجارى أو البطاقة الضريبية أو كارت الشركة أو أى مستند دال الخ
                                </div>
                            </div>
                            
                            <!-- صورة مسئول الاتصال -->
                            <div class="form-group" title="Contact Person Image">
                                <label for="profile_photo" class="col-sm-4 control-label">صورة مسئول إتصال الشركة</label>
                                <div class="col-sm-7 upload_div">
                                    <input type="hidden" name="profile_photo" id="profile_photo">
                                    <img style="float:left; margin-right:10px;" src="<?php echo BASE_URL; ?>/images/uploadd.png" 
                                         id="profilephoto" alt="Profile Photo">
                                    <input id="profileupload" type="file" name="files" style="cursor:pointer;">
                                    <span class="file_input">إضافة صورة</span>
                                    (إختيارى) صورة مسئول اتصال الشركة
                                </div>
                            </div>
                            
                            <!-- الموقع الإلكتروني -->
                            <div class="form-group" title="Website Url">
                                <label for="website" class="col-sm-4 control-label">الموقع الألكترونى للشركة</label>
                                <div class="col-sm-7">
                                    <input type="url" class="form-control" value="<?php echo htmlspecialchars($session_data['website'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           id="website" name="website" placeholder="http://example.com">
                                    <span id="helpBlock" class="help-block">مثال: http://example.com</span>
                                </div>
                            </div>
                            
                            <!-- كلمة المرور -->
                            <div class="form-group" title="Create Password">
                                <label for="pass" class="col-sm-4 control-label">رجاء إنشاء كلمة مرور خاصة بك *</label>
                                <div class="col-sm-7">
                                    <input name="pass" id="pass" type="password" class="form-control" 
                                           value="<?php echo htmlspecialchars($session_data['pass'], ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="كلمة المرور">
                                </div>
                            </div>
                            
                            <!-- الموافقة على الشروط -->
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-7">
                                    <div class="new_checkbox">
                                        <label>
                                            <input value="yes" name="accept" id="accept" type="checkbox">
                                            <a href="terms.php" target="_blank">نعـم أوافـق عـلى شـروط الإستخـدام</a>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- زر التسجيل -->
                            <div class="form-group">
                                <div class="col-sm-offset-4 col-sm-7">
                                    <input type="button" name="register" title="إختار خطة العضوية" 
                                           value="<< إختار نوع عضويتك المجانية على المنصة" class="btn btn-danger" onclick="checkvalid();">
                                    <br><br>
                                    <span class="pull-right text-center" title="Already Member ?">
                                        هل أنت عضو من قبل ؟<br>
                                        <a href="sign-in.php" style="font-weight:bold; color:#00F; font-size:18px;" title="Sign in">سجل دخول</a>
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
        $("#nncountrynn").msDropdown();
        
        $("#country_name").autocomplete("ajax-file/autocomplete_country.php", {
            selectFirst: true
        }).result(function(event, data, formatted) {
            $("#country").val(data[1]);
            $("#ph_country").val(data[2]);
            $("#reset").show();
            $("input#country_name").attr('disabled', 'disabled');
        });
        
        $("#state").autocomplete("ajax-file/autocomplete_state.php?country=" + $("#country").val(), {
            selectFirst: true
        }).result(function(event, data, formatted) {
            $("#b_state").val(data[1]);
            $("#reset").show();
            $("input#state").attr('disabled', 'disabled');
        });
        
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
    
    function CheckIfChanged() {
        $("#state").autocomplete("ajax-file/autocomplete_state.php?country=" + $("#country").val(), {
            selectFirst: true
        }).result(function(event, data, formatted) {
            $("#b_state").val(data[1]);
            $("#reset").show();
            $("input#state").attr('disabled', 'disabled');
        });
        
        $("#city_others").autocomplete("ajax-file/showregisterUsercity.php", {
            selectFirst: true,
            extraParams: {country: document.getElementById("country").value}
        }).result(function(event, data, formatted) {
            var dm = data[0].split(">>");
            $("#city_others").val(dm[0]);
            $("#state").val(dm[1]);
            $("#city").val(data[1]);
            $("#b_state").val(data[2]);
            $("#reset").show();
        });
    }
    
    setInterval(function() { CheckIfChanged(); }, 1000);
    
    function mable() {
        $("input#country_name").removeAttr('disabled');
        $("input#country_name").val('');
        $("#ph_country").val('');
        $("input#country").val('');
        $("#reset").hide();
    }
    
    function checkExistingEmail(eml) {
        $.post("ajax-file/isEmailExist.php", {eml: eml}, function(data) {
            $('#email_exists').val($.trim(data));
        });
    }
    
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
        
        if (email.value != '') {
            checkExistingEmail(email.value);
        }
        
        if (mobile1.value == '') {
            message = "من فضلك أدخل رقم المحمول";
            mobile1.focus();
            valid = false;
        } else if (isNaN(mobile1.value)) {
            message = "رقم المحمول يجب أن يكون أرقام فقط";
            mobile1.focus();
            valid = false;
        } else if (mobile1.value.length != 10) {
            message = "رقم المحمول يجب أن يكون 10 أرقام";
            mobile1.focus();
            valid = false;
        } else if (email.value == "" || email.value == null) {
            message = "من فضلك أدخل البريد الإلكتروني";
            email.focus();
            valid = false;
        } else if (!email.value.match(is_email)) {
            message = "من فضلك أدخل إيميل صالح";
            email.value = "";
            email.focus();
            valid = false;
        } else if (document.getElementById('email_exists').value == '1') {
            message = "هذا الميل مسجل من قبل";
            email.focus();
            valid = false;
        } else if (country.value == '') {
            message = "من فضلك إختار البلد";
            country.focus();
            valid = false;
        } else if (ph_country.value == '') {
            message = "من فضلك أدخل مفتاح البلد";
            ph_country.focus();
            valid = false;
        } else if (city.value == '') {
            message = "من فضلك إختار المدينة";
            city.focus();
            valid = false;
        } else if (state.value == '') {
            message = "من فضلك أدخل المحافظة";
            state.focus();
            valid = false;
        } else if (postal_code.value == '') {
            message = "من فضلك أدخل الرمز البريدى";
            postal_code.focus();
            valid = false;
        } else if (fname.value == '') {
            message = "من فضلك أدخل الإسم الأول";
            fname.focus();
            valid = false;
        } else if (!isNaN(fname.value)) {
            message = "من فضلك أدخل إسم أول صالح";
            fname.value = '';
            fname.focus();
            valid = false;
        } else if (lname.value == '') {
            message = "من فضلك أدخل الإسم الأخير";
            lname.focus();
            valid = false;
        } else if (!isNaN(lname.value)) {
            message = "من فضلك أدخل إسم أخير صالح";
            lname.value = '';
            lname.focus();
            valid = false;
        } else if (designation.value == '') {
            message = "من فضلك أدخل المسمى الوظيفى";
            designation.focus();
            valid = false;
        } else if (businessname.value == '') {
            message = "من فضلك أدخل الإسم التجارى للشركة";
            businessname.focus();
            valid = false;
        } else if (authority.value == '') {
            message = "من فضلك أدخل رقم السجل التجارى";
            authority.focus();
            valid = false;
        } else if (authority1.value == '') {
            message = "من فضلك أدخل رقم البطاقة الضريبية";
            authority1.focus();
            valid = false;
        } else if (comapnyimage.value == '') {
            message = "من فضلك أضف أوراق الشركة مثل كارت الشركة";
            valid = false;
        } else if (website.value != '' && !website.value.match(/^(ht|f)tps?:\/\/[a-z0-9-\.]+\.[a-z]{2,4}\/?([^\s<>\#%"\,\{\}\\|\\\^\[\]`]+)?$/)) {
            message = 'من فضلك أدخل عنوان ويب صالح';
            website.focus();
            valid = false;
        } else if (pass.value == '') {
            message = "من فضلك أكتب كلمة مرور";
            pass.focus();
            valid = false;
        } else if (pass.value.length < 6) {
            message = "كلمة المرور لا ينبغى أن تقل عن 6 حروف";
            pass.value = "";
            pass.focus();
            valid = false;
        } else if (!accept.checked) {
            message = "يجب الموافقة على شروط الإستخدام";
            accept.focus();
            valid = false;
        } else {
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
    
    function blankcity() {
        $("#city_others").val('');
        $("#city").val('');
        $("#stateid").val('');
        $("#state").val('');
    }
    
    function blankdesignation() {
        $("#designation").val('');
        $("#userdesignation").val('');
    }
    
    $(function() {
        var url = 'https://egyptmart.shop/server/php/';
        
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