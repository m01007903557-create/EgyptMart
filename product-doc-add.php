<?php
/**
 * File: product-doc-add.php

 * Description: رفع ملف PDF للمنتج
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من وجود معرف المنتج
if (!isset($_POST['pid']) || !is_numeric($_POST['pid'])) {
    http_response_code(400);
    die("0|Invalid product ID");
}

$product_id = (int)$_POST['pid'];

// التحقق من وجود الملفات المرفوعة
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die("0|No file uploaded");
}

global $con;

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/upload/productdoc/';
$maxFileSize = 10 * 1024 * 1024; // 10 ميجابايت للملفات
$allowedFileTypes = ['pdf'];
$allowedMimeTypes = ['application/pdf'];

try {
    // التأكد من وجود المجلد الهدف
    if (!is_dir($targetFolder)) {
        if (!mkdir($targetFolder, 0755, true)) {
            throw new RuntimeException("Failed to create upload directory");
        }
    }

    // التحقق من صلاحيات الكتابة
    if (!is_writable($targetFolder)) {
        throw new RuntimeException("Upload directory is not writable");
    }

    $file = $_FILES['Filedata'];
    
    // التحقق من أخطاء الرفع
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        $errorMessage = $uploadErrors[$file['error']] ?? 'Unknown upload error';
        throw new RuntimeException($errorMessage);
    }

    // التحقق من حجم الملف
    if ($file['size'] > $maxFileSize) {
        throw new RuntimeException(sprintf(
            'File size exceeds limit of %d MB',
            $maxFileSize / 1024 / 1024
        ));
    }

    // التحقق من نوع الملف باستخدام MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new RuntimeException('Invalid file type. Only PDF files are allowed.');
    }

    // الحصول على امتداد الملف والتحقق منه
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    if (!in_array($extension, $allowedFileTypes, true)) {
        throw new RuntimeException('Invalid file extension. Only PDF files are allowed.');
    }

    // جلب معلومات المنتج
    $select_sql = "SELECT pd_title, pd_pdf_attach FROM products WHERE pd_id = ? LIMIT 1";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $product_id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);
    $row = mysqli_fetch_object($result);
    mysqli_stmt_close($stmt_select);

    if (!$row) {
        throw new RuntimeException("Product not found");
    }

    // حذف الملف القديم إذا كان موجوداً
    if (!empty($row->pd_pdf_attach)) {
        $old_file = $targetFolder . $row->pd_pdf_attach;
        if (file_exists($old_file) && is_file($old_file)) {
            unlink($old_file);
        }
    }

    // إنشاء اسم فريد للملف
    $pd_title = $row->pd_title ?? 'product';
    $pd_title_safe = preg_replace('/[^a-zA-Z0-9\-_]/', '', str_replace(' ', '-', $pd_title));
    $pd_title_safe = substr($pd_title_safe, 0, 50); // تحديد الطول
    
    $timestamp = date("YmdHis");
    $randomString = bin2hex(random_bytes(4));
    
    $fileName = sprintf(
        '%s-%s-%s.%s',
        $pd_title_safe,
        $timestamp,
        $randomString,
        $extension
    );

    $targetPath = $targetFolder . $fileName;

    // نقل الملف إلى المجلد الهدف
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file');
    }

    // تغيير صلاحيات الملف
    chmod($targetPath, 0644);

    // تحديث قاعدة البيانات
    $update_sql = "UPDATE products SET pd_pdf_attach = ? WHERE pd_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'si', $fileName, $product_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        // إذا فشل التحديث، نحذف الملف المرفوع
        unlink($targetPath);
        throw new RuntimeException('Failed to update database: ' . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_update);
    
    echo "1|PDF file uploaded successfully: " . $fileName;

} catch (RuntimeException $e) {
    error_log("Upload Product PDF Error: " . $e->getMessage() . " | Product ID: $product_id");
    http_response_code(400);
    echo "0|" . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected Upload Product PDF Error: " . $e->getMessage() . " | Product ID: $product_id");
    http_response_code(500);
    echo "0|An unexpected error occurred";
}
?>