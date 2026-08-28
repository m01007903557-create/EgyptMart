<?php
/**
 * File: ajax/editSOImg.php

 * Description: تحديث صورة عرض البيع مع إنشاء صورة مصغرة وحذف الصورة القديمة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

// ob_start();
// session_start();
require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف عرض البيع
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("Invalid offer ID");
}

$so_id = (int)$_POST['id'];

global $con;

// التحقق من وجود الملفات المرفوعة
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die("No file uploaded");
}

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/../upload/sale_offer/';
$thumbFolder = __DIR__ . '/../upload/sale_offer/thumb/';
$maxFileSize = 5 * 1024 * 1024; // 5 ميجابايت
$allowedFileTypes = ['jpg', 'jpeg', 'gif', 'png'];
$allowedMimeTypes = [
    'image/jpeg',
    'image/pjpeg',
    'image/gif',
    'image/png',
    'image/x-png'
];

try {
    // التأكد من وجود المجلدات الهدف
    foreach ([$targetFolder, $thumbFolder] as $folder) {
        if (!is_dir($folder)) {
            if (!mkdir($folder, 0755, true)) {
                throw new RuntimeException("Failed to create directory: $folder");
            }
        }
        if (!is_writable($folder)) {
            throw new RuntimeException("Directory is not writable: $folder");
        }
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
        throw new RuntimeException('Invalid file type based on MIME');
    }

    // الحصول على امتداد الملف والتحقق منه
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    if (!in_array($extension, $allowedFileTypes, true)) {
        throw new RuntimeException('Invalid file extension');
    }

    // جلب معلومات عرض البيع الحالي
    $sql_so = "SELECT so_pic FROM sale_offer WHERE so_id = ? LIMIT 1";
    $stmt_so = mysqli_prepare($con, $sql_so);
    mysqli_stmt_bind_param($stmt_so, 'i', $so_id);
    mysqli_stmt_execute($stmt_so);
    $result_so = mysqli_stmt_get_result($stmt_so);
    $row_so = mysqli_fetch_object($result_so);
    mysqli_stmt_close($stmt_so);

    if (!$row_so) {
        throw new RuntimeException("Sale offer not found");
    }

    // إنشاء اسم فريد وآمن للملف
    $timestamp = time();
    $randomString = bin2hex(random_bytes(6));
    $newFileName = sprintf(
        'so-%d-%d-%s.%s',
        $so_id,
        $timestamp,
        $randomString,
        $extension
    );

    // تنظيف اسم الملف
    $newFileName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $newFileName);
    
    $targetPath = $targetFolder . $newFileName;
    $thumbPath = $thumbFolder . $newFileName;

    // نقل الملف إلى المجلد الهدف
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file');
    }

    // تغيير صلاحيات الملف
    chmod($targetPath, 0644);

    // إنشاء صورة مصغرة
    try {
        if (!class_exists('SimpleImage')) {
            throw new RuntimeException('SimpleImage class not found');
        }
        
        $imgSImage = new SimpleImage();
        $imgSImage->load($targetPath);
        $imgSImage->resize(100, 80); // عرض 100، ارتفاع 80
        $imgSImage->save($thumbPath);
        chmod($thumbPath, 0644);
    } catch (Exception $e) {
        // إذا فشل إنشاء الصورة المصغرة، نحذف الملف الأصلي
        unlink($targetPath);
        throw new RuntimeException('Failed to create thumbnail: ' . $e->getMessage());
    }

    // حذف الصورة القديمة إذا كانت موجودة
    $old_image = $row_so->so_pic ?? '';
    if (!empty($old_image)) {
        $old_path_lrg = $targetFolder . $old_image;
        if (file_exists($old_path_lrg) && is_file($old_path_lrg)) {
            unlink($old_path_lrg);
        }
        
        $old_path_thumb = $thumbFolder . $old_image;
        if (file_exists($old_path_thumb) && is_file($old_path_thumb)) {
            unlink($old_path_thumb);
        }
    }

    // تحديث قاعدة البيانات
    $update_sql = "UPDATE sale_offer SET so_pic = ? WHERE so_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'si', $newFileName, $so_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        // إذا فشل التحديث، نحذف الملفات الجديدة
        unlink($targetPath);
        unlink($thumbPath);
        throw new RuntimeException('Failed to update database');
    }

    mysqli_stmt_close($stmt_update);
    
    echo "1|Image updated successfully";

} catch (RuntimeException $e) {
    error_log("Edit Sale Offer Image Error: " . $e->getMessage() . " | Offer ID: $so_id");
    http_response_code(400);
    echo "0|" . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected Edit Sale Offer Image Error: " . $e->getMessage() . " | Offer ID: $so_id");
    http_response_code(500);
    echo "0|An unexpected error occurred";
}
?>