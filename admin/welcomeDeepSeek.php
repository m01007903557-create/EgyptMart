<?php
/**
 * File: validate-admin.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: معالجة تسجيل دخول المشرفين
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once dirname(__DIR__) . "/common.php";
require_once dirname(__DIR__) . "/lib/pagination.php";

// التحقق من أن الطلب هو POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    $_SESSION['error'] = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    header('Location: index.php');
    exit;
}

$username = trim($_POST['username']);
$password = $_POST['password'];

// التحقق من أن الحقول ليست فارغة
if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    header('Location: index.php');
    exit;
}

try {
    // البحث عن المستخدم في قاعدة البيانات
    $sql = "SELECT id, username, password, email, status FROM admin_user WHERE username = ?";
    $stmt = mysqli_prepare($GLOBALS['con'], $sql);
    
    if (!$stmt) {
        throw new Exception('Database prepare error: ' . mysqli_error($GLOBALS['con']));
    }
    
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        // تأخير لمنع هجمات التخمين
        sleep(1);
        $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        header('Location: index.php');
        exit;
    }
    
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // التحقق من حالة الحساب
    if ((int)($user['status'] ?? 0) !== 1) {
        $_SESSION['error'] = 'هذا الحساب غير نشط';
        header('Location: index.php');
        exit;
    }
    
    // التحقق من كلمة المرور (MD5)
    $encryptedPassword = md5($password);
    
    if ($user['password'] !== $encryptedPassword) {
        // تأخير لمنع هجمات التخمين
        sleep(1);
        $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
        header('Location: index.php');
        exit;
    }
    
    // تسجيل الدخول ناجح - تعيين متغيرات الجلسة
    $_SESSION['ad_id_indm'] = (int)$user['id'];
    $_SESSION['ad_username_indm'] = $user['username'];
    $_SESSION['admin_logged_in'] = true;
    
    // تسجيل محاولة الدخول الناجحة
    $logSql = "INSERT INTO admin_login_details (id, last_login_time, ip_address) VALUES (?, NOW(), ?)";
    $logStmt = mysqli_prepare($GLOBALS['con'], $logSql);
    if ($logStmt) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        mysqli_stmt_bind_param($logStmt, "is", $user['id'], $ip);
        mysqli_stmt_execute($logStmt);
        mysqli_stmt_close($logStmt);
    }
    
    // التوجيه إلى الصفحة الرئيسية للإدارة
    header('Location: welcome.php');
    exit;
    
} catch (Exception $e) {
    error_log("validate-admin.php: " . $e->getMessage());
    $_SESSION['error'] = 'حدث خطأ داخلي. الرجاء المحاولة مرة أخرى.';
    header('Location: index.php');
    exit;
}
?>