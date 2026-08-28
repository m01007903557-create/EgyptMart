<?php
/**
 * File: ajax/delCompanyBanner.php

 * Description: حذف بانر الشركة مع الملف الفعلي
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

// التحقق من وجود معرف البانر
if (!isset($_POST['cb_id']) || !is_numeric($_POST['cb_id'])) {
    http_response_code(400);
    echo "0|Invalid banner ID";
    exit;
}

$cb_id = (int)$_POST['cb_id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب معلومات البانر مع التحقق من ملكيته
    $select_sql = "SELECT cb_id, cb_image, cb_bnsprof_id 
                   FROM company_banner 
                   WHERE cb_id = ? AND cb_bnsprof_id IN 
                   (SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = ?) 
                   LIMIT 1";
    
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'ii', $cb_id, $current_user);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt_select);

    if (!$row) {
        throw new Exception("Banner not found or you don't have permission to delete it");
    }

    $deleted_files = [];
    $failed_files = [];

    // حذف الملف الفعلي إذا كان موجوداً
    if (!empty($row->cb_image)) {
        $file_name = basename($row->cb_image);
        $file_name = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_name);
        
        if (!empty($file_name)) {
            $file_path = __DIR__ . "/../upload/company_banner/" . $file_name;
            if (file_exists($file_path) && is_file($file_path)) {
                if (unlink($file_path)) {
                    $deleted_files[] = $file_path;
                } else {
                    $failed_files[] = $file_path;
                }
            }
        }
    }

    // حذف سجل البانر من قاعدة البيانات
    $delete_sql = "DELETE FROM company_banner WHERE cb_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $cb_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete banner record: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_delete) == 0) {
        throw new Exception("Banner record not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    // تسجيل النتيجة
    if (!empty($failed_files)) {
        error_log("File could not be deleted for banner ID $cb_id: " . implode(', ', $failed_files));
        echo "1|Banner deleted successfully but file could not be removed";
    } else {
        echo "1|Banner deleted successfully";
    }

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Company Banner Error: " . $e->getMessage() . " | Banner ID: $cb_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>