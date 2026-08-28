<?php
/**
 * File: ajax/delGalleryImage.php

 * Description: حذف صورة من معرض الصور مع الملف الفعلي
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف الصورة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid image ID";
    exit;
}

$ph_id = (int)$_POST['id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب معلومات الصورة قبل الحذف
    $select_sql = "SELECT ph_id, ph_fileName FROM photo WHERE ph_id = ? LIMIT 1";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $ph_id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt_select);

    if (!$row) {
        throw new Exception("Image not found");
    }

    $deleted_files = [];
    $failed_files = [];

    // حذف الملف الفعلي إذا كان موجوداً
    if (!empty($row->ph_fileName)) {
        $file_name = basename($row->ph_fileName);
        $file_name = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_name);
        
        if (!empty($file_name)) {
            $file_path = __DIR__ . "/../upload/image_gallery/" . $file_name;
            if (file_exists($file_path) && is_file($file_path)) {
                if (unlink($file_path)) {
                    $deleted_files[] = $file_path;
                } else {
                    $failed_files[] = $file_path;
                }
            }
        }
    }

    // حذف سجل الصورة من قاعدة البيانات
    $delete_sql = "DELETE FROM photo WHERE ph_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $ph_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete image record: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_delete) == 0) {
        throw new Exception("Image record not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    // تسجيل النتيجة
    if (!empty($failed_files)) {
        error_log("Some files could not be deleted for gallery image ID $ph_id: " . implode(', ', $failed_files));
        echo "1|Gallery image deleted successfully but file could not be removed";
    } else {
        echo "1|Gallery image deleted successfully";
    }

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Gallery Image Error: " . $e->getMessage() . " | Image ID: $ph_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>