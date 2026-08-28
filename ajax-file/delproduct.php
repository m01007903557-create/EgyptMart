<?php
/**
 * File: ajax/delproduct.php

 * Description: حذف منتج مع الصور المرتبطة به
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "0|Invalid product ID";
    exit;
}

$product_id = (int)$_GET['id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب معلومات الصور قبل الحذف
    $select_sql = "SELECT pd_image, pd_imagelogo FROM products WHERE pd_id = ? LIMIT 1";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $product_id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt_select);

    if (!$row) {
        throw new Exception("Product not found");
    }

    // حذف الصور الرئيسية
    $images_to_delete = [];
    
    // إضافة pd_image إذا كانت موجودة
    if (!empty($row->pd_image)) {
        $images = explode(',', $row->pd_image);
        $images_to_delete = array_merge($images_to_delete, $images);
    }
    
    // إضافة pd_imagelogo إذا كانت موجودة
    if (!empty($row->pd_imagelogo)) {
        $logo_images = explode(',', $row->pd_imagelogo);
        $images_to_delete = array_merge($images_to_delete, $logo_images);
    }

    // حذف الملفات الفعلية
    $deleted_files = [];
    $failed_files = [];

    foreach ($images_to_delete as $image) {
        $image = trim($image);
        if (empty($image)) continue;
        
        // تنظيف اسم الصورة
        $image = basename($image);
        $image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $image);
        
        // مسارات الصور
        $main_path = __DIR__ . "/../upload/myproduct/" . $image;
        $thumb_path = __DIR__ . "/../upload/myproduct/thumb/" . $image;
        
        // حذف الصورة الرئيسية
        if (file_exists($main_path) && is_file($main_path)) {
            if (unlink($main_path)) {
                $deleted_files[] = $main_path;
            } else {
                $failed_files[] = $main_path;
            }
        }
        
        // حذف الصورة المصغرة
        if (file_exists($thumb_path) && is_file($thumb_path)) {
            if (unlink($thumb_path)) {
                $deleted_files[] = $thumb_path;
            } else {
                $failed_files[] = $thumb_path;
            }
        }
    }

    // حذف سجل المنتج من قاعدة البيانات
    $delete_sql = "DELETE FROM products WHERE pd_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $product_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete product record: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_delete) == 0) {
        throw new Exception("Product not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    // تسجيل النتيجة
    if (!empty($failed_files)) {
        error_log("Some files could not be deleted for product ID $product_id: " . implode(', ', $failed_files));
        echo "1|Product deleted successfully but some files could not be removed";
    } else {
        echo "1|Product deleted successfully";
    }

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Product Error: " . $e->getMessage() . " | Product ID: $product_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>