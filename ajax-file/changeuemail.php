<?php
/**
 * File: ajax/changeuemail.php

 * Description: تحديث البريد الإلكتروني للمستخدم بعد التحقق من عدم وجوده مسبقاً
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "أنت غير مسجل الدخول";
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود البريد الإلكتروني
if (!isset($_POST['emailad'])) {
    http_response_code(400);
    echo "البيانات غير مكتملة";
    exit;
}

$email = trim($_POST['emailad']);
$msg = '';
$em = 1;

global $con;

// التحقق من وجود البريد الإلكتروني لمستخدم آخر
if (!empty($email)) {
    $check_sql = "SELECT usr_id FROM user WHERE email = ? AND usr_id != ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'si', $email, $uid);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        $em = 0;
    }
    mysqli_stmt_close($stmt_check);
}

// التحقق من صحة البريد الإلكتروني
if (empty($email)) {
    $msg = "من فضلك أدخل إيميل";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "... فضلك أدخل بريد الكترونى / إيميل صحيح";
} elseif ($em == 0) {
    $msg = "... هذا الميل مسجل من قبل رجاء إدخال ميل جديد";
} else {
    // تحديث البريد الإلكتروني
    $update_sql = "UPDATE user SET email = ? WHERE usr_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'si', $email, $uid);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $_SESSION['eml_indm'] = $email;
        $msg = "... تم تغيير الميل بنجاح";
    } else {
        error_log("Update Email Error: " . mysqli_error($con) . " | User ID: $uid");
        $msg = "... حدث خطأ أثناء تحديث البريد الإلكتروني";
    }
    mysqli_stmt_close($stmt_update);
}

echo $msg;
?>