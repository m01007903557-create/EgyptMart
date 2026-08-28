<?php
/**
 * File: common-support.php
 * Version: PHP 8.3
 * Description: الملف الأساسي للموقع - يحتوي على الإعدادات الأساسية والدوال المشتركة
 * تاريخ آخر تحديث: 2024
 */

// ==============================================
// إعدادات الوقت والأخطاء
// ==============================================
date_default_timezone_set("Asia/Kolkata");

// إعدادات عرض الأخطاء (للتطوير فقط - قم بتعطيلها في الإنتاج)
error_reporting(E_ERROR | E_PARSE); // عرض الأخطاء الجسيمة فقط
ini_set("display_errors", 1); // إظهار الأخطاء على الشاشة

// بدء المخزن المؤقت والجلسة
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==============================================
// تحديد المسار الأساسي
// ==============================================
if (preg_match('#^/admin#', $_SERVER['REQUEST_URI'])) {
    define('BASEDIR', $_SERVER['DOCUMENT_ROOT'] . '/admin/');
} else {
    define('BASEDIR', $_SERVER['DOCUMENT_ROOT'] . '/');
}

// ==============================================
// تضمين الملفات الأساسية
// ==============================================
include str_replace('/admin', '', BASEDIR) . 'lib/connect.php';
include BASEDIR . 'lib/function.php';
include str_replace('/admin', '', BASEDIR) . 'lib/website_function.php';
include str_replace('/admin', '', BASEDIR) . 'lib/pagination.php';
include BASEDIR . 'lib/validation.php';
include BASEDIR . 'lib/simpleimage.php';

// إعادة تعيين إعدادات الأخطاء (للتأكد)
error_reporting(E_ERROR | E_PARSE);
ini_set("display_errors", 1);

// ==============================================
// تضمين مكتبة PHPMailer
// ==============================================
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// التحقق من وجود ملفات PHPMailer
$phpmailer_paths = [
    'PHPMailer/src/Exception.php',
    'PHPMailer/src/PHPMailer.php',
    'PHPMailer/src/SMTP.php'
];

foreach ($phpmailer_paths as $path) {
    if (file_exists($path)) {
        require $path;
    } else {
        error_log("ملف PHPMailer غير موجود: " . $path);
    }
}

// ==============================================
// تعريف الدوال المفقودة للتوافق
// ==============================================

/**
 * دالة init_site_settings - للتوافق مع الكود القديم
 * إذا كانت الدالة غير موجودة في website_function.php
 */
if (!function_exists('init_site_settings')) {
    function init_site_settings() {
        global $con;
        $settings = [];
        
        // محاولة جلب الإعدادات من قاعدة البيانات
$sql = "SELECT * FROM site_settings";
$result = mysqli_query($con, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_object($result)) {
                $settings[$row->setting_key] = $row->setting_value;
            }
        }
        
        return $settings;
    }
}

/**
 * دالة get_membership_expired - للتوافق
 */
if (!function_exists('get_membership_expired')) {
    function get_membership_expired() {
        global $con, $uid;
        
        if (!isset($uid) || $uid == 0) {
            return true;
        }
        
        $sql = "SELECT plan_member_id.expiry_date 
                FROM plan_member_id 
                JOIN business_profile ON plan_member_id.b_id = business_profile.bnsprof_id 
                WHERE business_profile.bnsprof_uid = '$uid' 
                ORDER BY plan_member_id.expiry_date DESC 
                LIMIT 1";
        
        $result = mysqli_query($con, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_object($result);
            return ($row->expiry_date < time());
        }
        
        return true;
    }
}

// ==============================================
// إعدادات Memcached (معطلة حالياً)
// ==============================================
// $memcached = new Memcached();
// $memcached->addServer('127.0.0.1', 20159);

$memcached_key = 'site_settings';
$site_settings = ''; // $memcached->get($memcached_key);

if (!$site_settings) {
    if (function_exists('init_site_settings')) {
        $site_settings = init_site_settings();
    } else {
        $site_settings = [];
    }
    // $memcached->set($memcached_key, $site_settings, 60*60*10);
}

// ==============================================
// تحديد اسم الملف الحالي
// ==============================================
$path = $_SERVER['SCRIPT_NAME'];
$pos = strrpos($path, '/');
$file = substr($path, ($pos + 1));

$dotpos = strrpos($file, '.');
$file = substr($file, 0, $dotpos);

// ==============================================
// تعريف الرابط الأساسي
// ==============================================
$base_url = "https://" . $_SERVER['SERVER_NAME'] . dirname($_SERVER["REQUEST_URI"] . '?') . '/';
$baseurl = str_replace("/company/", "", $base_url);
define("BASE_URL", $baseurl);

// ==============================================
// دالة لقراءة محتوى URL باستخدام cURL
// ==============================================
function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // إضافة لتجنب مشاكل SSL

    $data = curl_exec($ch);
    
    if (curl_error($ch)) {
        error_log("cURL Error: " . curl_error($ch));
    }
    
    curl_close($ch);

    return $data;
}

// ==============================================
// دالة الحصول على معلومات الموقع من IP
// ==============================================
function ip_info() {
    global $con;
    
    $ip = $_SERVER["REMOTE_ADDR"] ?? '0.0.0.0';
    
    // دعم الوكيل (Proxy) و Cloudflare
    if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    
    $ipdat = @json_decode(file_get_contents_curl("http://www.geoplugin.net/json.gp?ip=" . $ip));
    
    if ($ipdat && isset($ipdat->geoplugin_countryCode) && strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
        return $ipdat->geoplugin_countryCode;
    }
    
    return '';
}

// ==============================================
// تحديد بلد المستخدم وتخزينه في الكوكيز
// ==============================================
if (!isset($_SESSION['user_country'])) {
    $_SESSION['user_country'] = ip_info();
}

$user_country = $_SESSION['user_country'] ?? '';

if (!isset($_COOKIE['loc_id']) && !isset($_COOKIE['is_global']) && !empty($user_country)) {
    
    // استخدم MySQLi بدلاً من MySQL
    $sql = "SELECT * FROM country WHERE cn_status = '1' AND cn_name LIKE '" . mysqli_real_escape_string($con, $user_country) . "%' ORDER BY cn_id ASC LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_object($result);
        
        if ($row->cn_id == 243) { // Global
            setcookie("is_global", "1", [
                'expires' => time() + 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        } else {
            setcookie("loc_id", (string)$row->cn_id, [
                'expires' => time() + 3600,
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }
}

// ==============================================
// دالة إرسال البريد الإلكتروني باستخدام SMTP
// ==============================================
function sendSMTPMail($email, $subject, $message, $headers = '') {
    
    $mail = new PHPMailer();
    $mail->CharSet = 'UTF-8';
    
    // إعدادات SMTP
    $mail->isSMTP();
    $mail->SMTPDebug = 0; // 0 = off, 1 = client messages, 2 = client and server messages
    $mail->Host = 'mail.egyptmart.shop';
    $mail->Port = 465;
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'ssl';
    $mail->Username = "info@egyptmart.shop";
    $mail->Password = "info@egyptmart.shop";
    
    // إعدادات المرسل والمستقبل
    $mail->setFrom('info@egyptmart.shop', 'EgyptMART');
    $mail->addAddress($email);
    $mail->addReplyTo($email);
    
    // عنوان البريد (مع دعم UTF-8)
    $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    
    // محتوى البريد
    $mail->msgHTML($message);
    
    // إرسال البريد
    try {
        if ($mail->send()) {
            return true;
        } else {
            error_log("فشل إرسال البريد: " . $mail->ErrorInfo);
            return false;
        }
    } catch (Exception $e) {
        error_log("استثناء PHPMailer: " . $e->getMessage());
        return false;
    }
}

// ==============================================
// دوال إضافية للتوافق مع الإصدارات السابقة
// ==============================================

/**
 * دالة getLocationInfoByIp - للتوافق مع الكود القديم
 * @return array معلومات الموقع
 */
function getLocationInfoByIp() {
    $result = ['country' => '', 'city' => '', 'countryCode' => ''];
    
    $ip = $_SERVER["REMOTE_ADDR"] ?? '0.0.0.0';
    
    $ipdat = @json_decode(file_get_contents_curl("http://www.geoplugin.net/json.gp?ip=" . $ip));
    
    if ($ipdat) {
        $result['country'] = $ipdat->geoplugin_countryName ?? '';
        $result['countryCode'] = $ipdat->geoplugin_countryCode ?? '';
        $result['city'] = $ipdat->geoplugin_city ?? '';
    }
    
    return $result;
}

// ==============================================
// متغيرات إضافية للتوافق مع الملفات القديمة
// ==============================================

// تعريف متغير $usr_mp_id إذا لم يكن موجوداً
if (!isset($usr_mp_id) && isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
    $current_uid = (int)$_SESSION['uid_indm'];
    $sql = "SELECT usr_mp_id FROM user WHERE usr_id = '$current_uid'";
    $query = mysqli_query($con, $sql);
    if ($query && mysqli_num_rows($query) > 0) {
        $user_details = mysqli_fetch_object($query);
        $usr_mp_id = $user_details->usr_mp_id;
    } else {
        $usr_mp_id = 0;
    }
} elseif (!isset($usr_mp_id)) {
    $usr_mp_id = 0;
}

// تعريف متغير $uid إذا لم يكن موجوداً
if (!isset($uid)) {
    $uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
}


// ==============================================
// دوال التحقق من دخول المسؤول
// ==============================================

/**
 * دالة التحقق من تسجيل دخول المسؤول
 * @return bool
 */
function check_admin_login() {
    // التحقق من وجود جلسة نشطة للمسؤول
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }
    
    // التحقق من وجود كوكيز للمسؤول
    if (isset($_COOKIE['admin_auth']) && !empty($_COOKIE['admin_auth'])) {
        // يمكن إضافة تحقق إضافي هنا إذا لزم الأمر
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    
    // محاولة التحقق من الجلسة القديمة
    if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    
    if (isset($_SESSION['admin_uid']) && !empty($_SESSION['admin_uid'])) {
        $_SESSION['admin_logged_in'] = true;
        return true;
    }
    
    return false;
}

/**
 * دالة التحقق من صلاحيات المسؤول
 * @param string $required_level المستوى المطلوب من الصلاحيات
 * @return bool
 */
function check_admin_permission($required_level = 'admin') {
    if (!check_admin_login()) {
        return false;
    }
    
    // التحقق من مستوى الصلاحية إذا كان مخزناً في الجلسة
    if (isset($_SESSION['admin_level'])) {
        $levels = ['viewer' => 1, 'editor' => 2, 'admin' => 3, 'super_admin' => 4];
        $user_level = $levels[$_SESSION['admin_level']] ?? 0;
        $required = $levels[$required_level] ?? 0;
        
        return $user_level >= $required;
    }
    
    // إذا لم يتم تحديد مستوى، نعتبر أن المسؤول لديه صلاحيات كاملة
    return true;
}
?>