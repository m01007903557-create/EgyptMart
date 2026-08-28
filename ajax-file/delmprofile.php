<?php
/**
 * File: ajax/delmprofile.php

 * Description: حذف محتوى "من نحن" مع الصورة المرتبطة به
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المحتوى
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "0|Invalid about us ID";
    exit;
}

$about_id = (int)$_GET['id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب معلومات الصورة قبل الحذف
    $select_sql = "SELECT abtus_image FROM about_us WHERE abtus_id = ? LIMIT 1";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $about_id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt_select);

    if (!$row) {
        throw new Exception("About us content not found");
    }

    $deleted_files = [];
    $failed_files = [];

    // حذف الصورة إذا كانت موجودة
    if (!empty($row->abtus_image)) {
        $image_name = basename($row->abtus_image);
        $image_name = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $image_name);
        
        if (!empty($image_name)) {
            $image_path = __DIR__ . "/../upload/myprofile/" . $image_name;
            if (file_exists($image_path) && is_file($image_path)) {
                if (unlink($image_path)) {
                    $deleted_files[] = $image_path;
                } else {
                    $failed_files[] = $image_path;
                }
            }
        }
    }

    // حذف سجل "من نحن" من قاعدة البيانات
    $delete_sql = "DELETE FROM about_us WHERE abtus_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $about_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete about us record: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_delete) == 0) {
        throw new Exception("About us content not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    // تسجيل النتيجة
    if (!empty($failed_files)) {
        error_log("Some files could not be deleted for about us ID $about_id: " . implode(', ', $failed_files));
        echo "1|About us content deleted successfully but some files could not be removed";
    } else {
        echo "1|About us content deleted successfully";
    }

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete About Us Error: " . $e->getMessage() . " | ID: $about_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>