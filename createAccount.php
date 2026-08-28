<?php
ob_start();
session_start();
file_put_contents("debug_log.txt", date("Y-m-d H:i:s") . " - POST received: " . print_r($_POST, true) . "\n", FILE_APPEND);
include "common.php";
require_once __DIR__ . '/whats360-config.php';

if (!class_exists('validate')) {
    class validate {
        public static function is_email($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        }
        
        public static function is_weblink($url) {
            return filter_var($url, FILTER_VALIDATE_URL) !== false;
        }
    }
}

// ============================================
// التحقق من وجود طلب POST فقط
// ============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['fname'])) {
    // إذا لم يكن هناك بيانات POST، أخرج رسالة خطأ واضحة
    echo "0|طلب غير صالح";
    exit;
}

// ============================================
// تعريف جميع المتغيرات مسبقاً لمنع أخطاء PHP 8.3
// ============================================
$name_prefix = $_POST['name_prefix'] ?? '';
$fname = $_POST['fname'] ?? '';
$lname = $_POST['lname'] ?? '';
$email = $_POST['email'] ?? '';
$country = $_POST['country'] ?? '';
$ph_country = $_POST['ph_country'] ?? '';
$mobile1 = $_POST['mobile1'] ?? '';
$website = $_POST['website'] ?? '';
$city = $_POST['city'] ?? '';
$state = $_POST['state'] ?? '';
$city_others = $_POST['city_others'] ?? '';
$state_others = $_POST['state_others'] ?? '';
$postal_code = $_POST['postal_code'] ?? '';
$businessname = $_POST['businessname'] ?? '';
$authority = $_POST['authority'] ?? '';
$authority1 = $_POST['authority1'] ?? '';
$perposition = $_POST['perposition'] ?? '';
$profileimage = $_POST['profileimage'] ?? '';
$comapnyimage = $_POST['comapnyimage'] ?? '';
$pass = $_POST['pass'] ?? '';
$accept = $_POST['accept'] ?? '';

if (!function_exists('egyptmart_account_otp_verified')) {
    function egyptmart_account_otp_verified(mysqli $con, string $mobile): bool {
        $mobile = egyptmart_normalize_mobile($mobile);
        $sessionMobile = egyptmart_normalize_mobile((string)($_SESSION['otp_mobile'] ?? ''));
        if (!empty($_SESSION['otp_verified']) && $mobile !== '' && $sessionMobile !== '' && $mobile === $sessionMobile) {
            return true;
        }
        if ($mobile === '') {
            return false;
        }
        egyptmart_otp_table($con);
        $stmt = mysqli_prepare($con, "SELECT id FROM egyptmart_otp_requests WHERE mobile = ? AND status = 'verified' AND verified_at >= DATE_SUB(NOW(), INTERVAL 30 MINUTE) ORDER BY id DESC LIMIT 1");
        mysqli_stmt_bind_param($stmt, 's', $mobile);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $verifiedId);
        $verified = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if ($verified) {
            $_SESSION['otp_verified'] = true;
            $_SESSION['otp_mobile'] = $mobile;
        }
        return (bool)$verified;
    }
}

$submittedMobile = egyptmart_normalize_mobile($mobile1);
if (!egyptmart_account_otp_verified($con, $submittedMobile)) {
    echo "0|Please verify your WhatsApp OTP before creating an account.";
    exit;
}

// تنظيف البيانات باستخدام mysqli_real_escape_string وليس addslashes
$name_prefix = mysqli_real_escape_string($con, trim($name_prefix));
$fname = mysqli_real_escape_string($con, trim($fname));
$lname = mysqli_real_escape_string($con, trim($lname));
$email = mysqli_real_escape_string($con, trim($email));
$country = mysqli_real_escape_string($con, trim($country));
$ph_country = mysqli_real_escape_string($con, trim($ph_country));
$mobile1 = mysqli_real_escape_string($con, trim($mobile1));
$website = mysqli_real_escape_string($con, trim($website));
$city = mysqli_real_escape_string($con, trim($city));
$state = mysqli_real_escape_string($con, trim($state));
$city_others = mysqli_real_escape_string($con, trim($city_others));
$state_others = mysqli_real_escape_string($con, trim($state_others));
$postal_code = mysqli_real_escape_string($con, trim($postal_code));
$businessname = mysqli_real_escape_string($con, trim($businessname));
$authority = mysqli_real_escape_string($con, trim($authority));
$authority1 = mysqli_real_escape_string($con, trim($authority1));
$perposition = mysqli_real_escape_string($con, trim($perposition));
$profileimage = mysqli_real_escape_string($con, trim($profileimage));
$comapnyimage = mysqli_real_escape_string($con, trim($comapnyimage));
$pass = trim($pass);
$npass = md5($pass);

$data = array();
$valid = true;

// التحقق من صحة البيانات
$em = 1;
$sql_chk = "SELECT * FROM user WHERE email = '" . mysqli_real_escape_string($con, $email) . "' AND status = 1";
$res_chk = mysqli_query($con, $sql_chk);
if (mysqli_num_rows($res_chk) > 0) {	
    $em = 0;		
}

if ($fname == '') {
    $data[0] = "0";
    $data[1] = "<font color=red>Please enter first name</font>";
    $valid = false;	
} else if ($email == "") {
    $data[0] = "0";
    $data[1] = "<font color='#CC0000'>Please enter email</font>";
    $valid = false;	
} else if (!validate::is_email($email)) {
    $data[0] = "0";
    $data[1] = "<font color='#CC0000'>Please enter valid email</font>";
    $valid = false;		
} else if ($em == 0) {	
    $data[0] = "0";
    $data[1] = "<font color=red>Please enter another Email Id. User already exist with this ID.</font>";
    $valid = false;				
} else if ($country == "") {
    $data[0] = "0";
    $data[1] = "<font color=red>Please select country.</font>";
    $valid = false;	
} else if ($ph_country == "") {
    $data[0] = "0";
    $data[1] = "<font color=red>Country ISD Code Must Not Blank.</font>";
    $valid = false;	
} else if ($mobile1 == "") {
    $data[0] = "0";
    $data[1] = "<font color=red>Please Enter Mobile.</font>";
    $valid = false;	
} else if ($website != '' && !(validate::is_weblink($website))) {
    $data[0] = "0";
    $data[1] = "<font color=red>Please Enter a Valid Web Link</font>";
    $valid = false;
} else if ($pass == "") {
    $data[0] = "0";
    $data[1] = "<font color=red>Please enter password</font>";
    $valid = false;	
} else {
    $valid = true;	
}

if ($valid == true) {	
    $filePath = dirname(__FILE__) . '/server/php/files/' . $profileimage;
    $thumbfilePath = dirname(__FILE__) . '/server/php/files/thumbnail/' . $profileimage;
    $image_data = '';
    
    if (file_exists($filePath)) {
        $image_data = addslashes(file_get_contents($filePath));
        unlink($filePath);
        unlink($thumbfilePath);
    }
    
    $insert1 = "INSERT INTO user SET
                email = '" . mysqli_real_escape_string($con, $email) . "',
                name_prefix = '" . mysqli_real_escape_string($con, $name_prefix) . "',
                fname = '" . mysqli_real_escape_string($con, $fname) . "', 
                lname = '" . mysqli_real_escape_string($con, $lname) . "',
                country_ph_code = '" . mysqli_real_escape_string($con, $ph_country) . "',
                country = '" . mysqli_real_escape_string($con, $country) . "',
                mobile1 = '" . mysqli_real_escape_string($con, $mobile1) . "',
                pass = '" . mysqli_real_escape_string($con, $npass) . "',
                website = '" . mysqli_real_escape_string($con, $website) . "',
                image = '" . mysqli_real_escape_string($con, $profileimage) . "',
                profileImage = '" . mysqli_real_escape_string($con, $image_data) . "',
                date = NOW()";
    
    mysqli_query($con, $insert1);
    $id = mysqli_insert_id($con);
    
    if (getEmailVerificationStatus() == 0) {
        $sql_veify_upd = "UPDATE user SET usr_emailVerify = '1' WHERE usr_id = '" . (int)$id . "'";
        mysqli_query($con, $sql_veify_upd);
    }
    
    $sql_bpf = "INSERT INTO business_profile SET
                bnsprof_uid = '" . (int)$id . "',
                bnsprof_designation = '" . mysqli_real_escape_string($con, $perposition) . "',
                bnsprof_ceoprefix = '" . mysqli_real_escape_string($con, $name_prefix) . "',
                bnsprof_ceofname = '" . mysqli_real_escape_string($con, $fname) . "',
                bnsprof_ceolname = '" . mysqli_real_escape_string($con, $lname) . "',
                bnsprof_compname = '" . mysqli_real_escape_string($con, $businessname) . "',
                bnsprof_doc = '" . mysqli_real_escape_string($con, $comapnyimage) . "',
                bnsprof_city = '" . mysqli_real_escape_string($con, $city) . "',
                bnsprof_state = '" . mysqli_real_escape_string($con, $state) . "',
                bnsprof_zipcode = '" . mysqli_real_escape_string($con, $postal_code) . "',
                bnsprof_website_alt = '" . mysqli_real_escape_string($con, $website) . "',
                bnsprof_regauthority = '" . mysqli_real_escape_string($con, $authority) . "',
                bnsprof_svtax_no = '" . mysqli_real_escape_string($con, $authority1) . "',
                bnsprof_creation_date = NOW()";
    mysqli_query($con, $sql_bpf);
    
    $sql_webst = "INSERT INTO website_content SET
                  wc_usr_id = '" . (int)$id . "',
                  wc_updated_date = NOW()";
    mysqli_query($con, $sql_webst);
    
    $_SESSION['uid_indm'] = $id;
    $_SESSION['eml_indm'] = $email;
    $_SESSION['msg'] = $msg ?? '';
    
    $fullname = $fname . ' ' . $lname;
    
    if (getEmailVerificationStatus() == 1) {
        $link = "<a href=http://" . $_SERVER['SERVER_NAME'] . "/verifyUser.php?token=" . rand(1000, 9999) . md5($_SESSION['uid_indm']) . ">Verify</a>";
        $to = stripslashes(getUserInfo($_SESSION['uid_indm'], 'email'));
        $subject = "مجانا إعرض منتجاتك على 5000 مشترى " . get_page_settings(4);
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();
        
        include "email/emailVerification.php";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        
        sendSMTPMail($to, $subject, $message1, $headers);
    } else {
        $to = stripslashes(getUserInfo($_SESSION['uid_indm'], 'email'));
        $subject = "Welcome to " . get_page_settings(4);
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();
        
        include "email/emailVerification.php";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        
        mail($to, $subject, $message2, $headers);
    }
    
    // إشعار الأدمن
    $sql_cn = "SELECT * FROM country WHERE cn_id = '" . mysqli_real_escape_string($con, $country) . "'";
    $res_cn = mysqli_query($con, $sql_cn);
    $row_cn = mysqli_fetch_object($res_cn);
    
    $to = get_adminemail();
    $subject = "User Creation Notification";
    $from_name = get_page_settings(4);
    $from_email = get_adminemail();
    
    include "email/emailVerification.php";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: $from_name <$from_email>\r\n";
    
    mail($to, $subject, $message_admin, $headers);
    
    $data[0] = "1";
    $data[1] = "تم إرسال ميل على بريدك للتحقق .. شكرا لإنشائك حساب على منصتنا للتجارة الالكترونية";
}

// إخراج النتيجة
echo $data[0] . "|" . $data[1];
?>
