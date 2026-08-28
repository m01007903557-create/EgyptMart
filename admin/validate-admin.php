<?php
/**
 * File: validate-admin.php
 * Version: 2.1.0 (PHP 8.3)
 * Description: Admin login validation
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء الجلسة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
require_once dirname(__DIR__) . "/common.php";

// التحقق من أن الطلب POST (بدون التحقق من login)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// تسجيل البيانات للتحقق (يمكنك إزالته بعد التأكد)
error_log("validate-admin.php called with POST: " . print_r($_POST, true));

// تنظيف المدخلات
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// التحقق من الحقول الفارغة
if (empty($username)) {
    $_SESSION['err_msg'] = "Please enter username";
    header("Location: index.php");
    exit;
}

if (empty($password)) {
    $_SESSION['err_msg'] = "Please enter password";
    header("Location: index.php");
    exit;
}

// تشفير كلمة المرور
$encrypted_pass = md5($password);

// البحث عن المستخدم باستخدام prepared statement
global $con;
$sql = "SELECT * FROM admin_user WHERE username = ? AND password = ? AND status = '1' LIMIT 1";
$stmt = mysqli_prepare($con, $sql);

if (!$stmt) {
    error_log("Failed to prepare statement: " . mysqli_error($con));
    $_SESSION['err_msg'] = "Database error";
    header("Location: index.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "ss", $username, $encrypted_pass);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) != 1) {
    mysqli_stmt_close($stmt);
    $_SESSION['err_msg'] = "Username or Password Incorrect";
    header("Location: index.php");
    exit;
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// تسجيل محاولة الدخول الناجحة
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$logSql = "INSERT INTO admin_login_details (id, last_login_time, user_ip) VALUES (?, NOW(), ?)";
$logStmt = mysqli_prepare($con, $logSql);

if ($logStmt) {
    mysqli_stmt_bind_param($logStmt, "is", $user['id'], $ip);
    mysqli_stmt_execute($logStmt);
    mysqli_stmt_close($logStmt);
}

// تعيين متغيرات الجلسة
$_SESSION['ad_username_indm'] = $username;
$_SESSION['ad_email_indm'] = $user['email'] ?? '';
$_SESSION['ad_id_indm'] = (int)$user['id'];
$_SESSION['admin_logged_in'] = true;

// تسجيل نجاح الدخول
error_log("Login successful for user: $username, redirecting to welcome.php");

// التوجيه إلى الصفحة الرئيسية
header("Location: welcome.php");
exit;
?>