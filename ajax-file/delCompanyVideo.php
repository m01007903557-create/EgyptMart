<?php
/**
 * File: ajax/delCompanyVideo.php

 * Description: حذف فيديو الشركة
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

// التحقق من وجود معرف الفيديو
if (!isset($_POST['cv_id']) || !is_numeric($_POST['cv_id'])) {
    http_response_code(400);
    echo "0|Invalid video ID";
    exit;
}

$cv_id = (int)$_POST['cv_id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من ملكية الفيديو قبل الحذف
    $check_sql = "SELECT cv_id FROM company_video 
                  WHERE cv_id = ? AND cv_bnsprof_id IN 
                  (SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = ?) 
                  LIMIT 1";
    
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'ii', $cv_id, $current_user);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        throw new Exception("Video not found or you don't have permission to delete it");
    }
    mysqli_stmt_close($stmt_check);

    // حذف الفيديو
    $delete_sql = "DELETE FROM company_video WHERE cv_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $cv_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete video: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    $affected_rows = mysqli_stmt_affected_rows($stmt_delete);
    
    if ($affected_rows == 0) {
        throw new Exception("Video not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Video deleted successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Company Video Error: " . $e->getMessage() . " | Video ID: $cv_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>