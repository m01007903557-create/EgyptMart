<?php
/**
 * File: upload-image-edit.php

 * Description: رفع صورة "من نحن" (About Us)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من وجود معرف "من نحن"
if (!isset($_POST['abtid']) || !is_numeric($_POST['abtid'])) {
    http_response_code(400);
    die("0|Invalid about us ID");
}

$abtid = (int)$_POST['abtid'];

// التحقق من وجود الملفات المرفوعة
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die("0|No file uploaded");
}

global $con;

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/upload/myprofile/';
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
        throw new RuntimeException('Invalid file type based on MIME');
    }

    // الحصول على امتداد الملف والتحقق منه
    $fileInfo = pathinfo($file['name']);
    $extension = strtolower($fileInfo['extension'] ?? '');
    
    if (!in_array($extension, $allowedFileTypes, true)) {
        throw new RuntimeException('Invalid file extension');
    }

    // التحقق من وجود السجل
    $check_sql = "SELECT abtus_id FROM about_us WHERE abtus_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $abtid);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($check_result) == 0) {
        throw new RuntimeException("About us record not found");
    }
    mysqli_stmt_close($stmt_check);

    // إنشاء اسم فريد للملف
    $timestamp = date("YmdHis");
    $randomString = bin2hex(random_bytes(4));
    $fileName = sprintf(
        '%d-%s-%s.%s',
        $abtid,
        $timestamp,
        $randomString,
        $extension
    );

    $targetFile = $targetFolder . $fileName;

    // نقل الملف إلى المجلد الهدف
    if (!move_uploaded_file($file['tmp_name'], $targetFile)) {
        throw new RuntimeException('Failed to move uploaded file');
    }

    // تغيير صلاحيات الملف
    chmod($targetFile, 0644);

    // تحديث قاعدة البيانات
    $update_sql = "UPDATE about_us SET abtus_image = ? WHERE abtus_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'si', $fileName, $abtid);

    if (!mysqli_stmt_execute($stmt_update)) {
        // إذا فشل التحديث، نحذف الملف المرفوع
        unlink($targetFile);
        throw new RuntimeException('Failed to update database: ' . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_update);
    
    echo "1|Image uploaded successfully|" . $fileName;

} catch (RuntimeException $e) {
    error_log("Upload About Us Image Error: " . $e->getMessage() . " | About Us ID: $abtid");
    http_response_code(400);
    echo "0|" . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected Upload About Us Image Error: " . $e->getMessage() . " | About Us ID: $abtid");
    http_response_code(500);
    echo "0|An unexpected error occurred";
}
?>