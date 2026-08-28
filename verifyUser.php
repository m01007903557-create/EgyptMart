<?php
/**
 * File: verifyUser.php
 * Description: التحقق من حساب المستخدم عبر البريد الإلكتروني
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

// التحقق من وجود التوكن
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: index.php");
    exit;
}

$token = substr($_GET['token'], 4);

global $con;

// بدء المعاملة
mysqli_begin_transaction($con);

try {
    // التحقق من وجود المستخدم
    $check_sql = "SELECT usr_id, usr_emailVerify FROM user WHERE MD5(usr_id) = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 's', $token);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $user = mysqli_fetch_assoc($result_check);
    mysqli_stmt_close($stmt_check);

    if (!$user) {
        throw new Exception("User not found");
    }

    // إذا كان المستخدم لم يتم التحقق منه بعد
    if ($user['usr_emailVerify'] == 0) {
        $_SESSION['uid_indm'] = (int)$user['usr_id'];
        
        // تعيين كوكيز المستخدم (صالحة لمدة 300 يوم)
        setcookie('cook_usr_id', (string)$user['usr_id'], time() + (86400 * 300), "/");
        
        // حذف كوكيز المنتجات
        setcookie("productids", "", time() - 3600, "/");
        setcookie("productids", "", time() - 3600, "/");
    }

    // تحديث حالة التحقق
    $update_sql = "UPDATE user SET usr_emailVerify = '1' WHERE MD5(usr_id) = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 's', $token);
    
    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception("Failed to update user verification status");
    }
    
    mysqli_stmt_close($stmt_update);

    // تأكيد المعاملة
    mysqli_commit($con);

    // التوجيه إلى لوحة التحكم
    header("Location: my-dashboard.php?verifySuccess=1");
    exit;

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("User Verification Error: " . $e->getMessage());
    
    // التوجيه إلى صفحة الخطأ
    header("Location: index.php?error=verification_failed");
    exit;
}
?>