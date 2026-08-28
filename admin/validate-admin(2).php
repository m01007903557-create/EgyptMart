<?php
// سجل محاولة الدخول
$log_file = '/home/u397968200/domains/egyptmart.shop/logs/login_debug.log';
$log_entry = date('Y-m-d H:i:s') . " - POST: " . print_r($_POST, true) . "\n";
file_put_contents($log_file, $log_entry, FILE_APPEND);
/**
 * validate-admin.php - نسخة مبسطة ومضمونة
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين common.php
require_once dirname(__DIR__) . "/common.php";

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    header('Location: index.php');
    exit;
}

global $con;

// البحث عن المستخدم
$sql = "SELECT id, username, password, status FROM admin_user WHERE username = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    header('Location: index.php');
    exit;
}

$user = mysqli_fetch_assoc($result);

// التحقق من كلمة المرور
// سجل كلمة المرور المشفرة
$input_md5 = md5($password);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Input MD5: $input_md5\n", FILE_APPEND);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - DB Password: " . $user['password'] . "\n", FILE_APPEND);

if ($user['password'] !== md5($password)) {
    $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    header('Location: index.php');
    exit;
}

// التحقق من حالة الحساب
if ($user['status'] != 1) {
    $_SESSION['error'] = 'هذا الحساب غير نشط';
    header('Location: index.php');
    exit;
}

// تعيين الجلسة
$_SESSION['ad_id_indm'] = (int)$user['id'];
$_SESSION['ad_username_indm'] = $user['username'];
$_SESSION['admin_logged_in'] = true;

// تسجيل محاولة الدخول
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$logSql = "INSERT INTO admin_login_details (id, last_login_time, ip_address) VALUES (?, NOW(), ?)";
$logStmt = mysqli_prepare($con, $logSql);
if ($logStmt) {
    mysqli_stmt_bind_param($logStmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($logStmt);
    mysqli_stmt_close($logStmt);
}

// التوجيه إلى الصفحة الرئيسية
header('Location: welcome.php');
exit;
?>