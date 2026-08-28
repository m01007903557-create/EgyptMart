<?php
/**
 * File: ajax/addCompanyVideo.php

 * Description: إضافة فيديو جديد للشركة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0|Unauthorized";
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['cv_bnsprof_id']) || !is_numeric($_POST['cv_bnsprof_id'])) {
    http_response_code(400);
    echo "0|Invalid company profile ID";
    exit;
}

if (!isset($_POST['vlink']) || empty(trim($_POST['vlink']))) {
    http_response_code(400);
    echo "0|Video link is required";
    exit;
}

$cv_bnsprof_id = (int)$_POST['cv_bnsprof_id'];
$vlink = trim($_POST['vlink']);

// التحقق من ملكية ملف الشركة
if (!checkCompanyOwnership($current_user, $cv_bnsprof_id)) {
    http_response_code(403);
    echo "0|You don't have permission to add videos to this company";
    exit;
}

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من صحة رابط الفيديو (اختياري - يمكنك تفعيله إذا أردت)
    // if (!filter_var($vlink, FILTER_VALIDATE_URL)) {
    //     throw new Exception("Invalid video URL");
    // }

    // إدراج الفيديو الجديد
    $insert_sql = "INSERT INTO company_video (cv_bnsprof_id, cv_video_link, cv_updated_date) 
                   VALUES (?, ?, NOW())";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'is', $cv_bnsprof_id, $vlink);

    if (!mysqli_stmt_execute($stmt_insert)) {
        throw new Exception("Failed to add video: " . mysqli_error($con));
    }

    $new_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_insert);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Video added successfully|$new_id";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Add Company Video Error: " . $e->getMessage() . " | Company: $cv_bnsprof_id, User: $current_user");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>