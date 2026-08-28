<?php
/**
 * File: upldtmplarge-news.php

 * Description: رفع صورة كبيرة مؤقتة للأخبار مع تغيير الحجم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من وجود معرف المستخدم
if (!isset($_POST['uid']) || !is_numeric($_POST['uid'])) {
    http_response_code(400);
    die("0|Invalid user ID");
}

$uid = (int)$_POST['uid'];

// التحقق من وجود الملفات المرفوعة
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die("0|No file uploaded");
}

global $con;

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/upload/mynews/large/';
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

    // التحقق من عدد الصور الموجودة (الحد الأقصى صورة واحدة)
    $check_sql = "SELECT COUNT(*) as count FROM temp_newsimage WHERE tmpns_uid = ? AND tmpns_status = '2'";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $uid);
    mysqli_stmt_execute($stmt_check);
    $check_result = mysqli_stmt_get_result($stmt_check);
    $check_row = mysqli_fetch_assoc($check_result);
    $image_count = (int)($check_row['count'] ?? 0);
    mysqli_stmt_close($stmt_check);

    if ($image_count >= 1) {
        throw new RuntimeException("You can only upload one large image");
    }

    // إنشاء اسم فريد للملف
    $timestamp = date("YmdHis");
    $randomString = bin2hex(random_bytes(4));
    $fileName = sprintf(
        '%d-%s-%s.%s',
        $uid,
        $timestamp,
        $randomString,
        $extension
    );

    // تغيير حجم الصورة باستخدام SimpleImage
    try {
        if (!class_exists('SimpleImage')) {
            throw new RuntimeException('SimpleImage class not found');
        }
        
        $imgSImage = new SimpleImage();
        $imgSImage->load($file['tmp_name']);
        $imgSImage->resize(570, 550);
        $imgSImage->save($targetFolder . $fileName);
        
    } catch (Exception $e) {
        throw new RuntimeException('Failed to process image: ' . $e->getMessage());
    }

    // تغيير صلاحيات الملف
    chmod($targetFolder . $fileName, 0644);

    // إدراج السجل في قاعدة البيانات
    $insert_sql = "INSERT INTO temp_newsimage (tmpns_uid, tmpns_image, tmpns_status) VALUES (?, ?, '2')";
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'is', $uid, $fileName);

    if (!mysqli_stmt_execute($stmt_insert)) {
        // إذا فشل الإدراج، نحذف الملف المرفوع
        unlink($targetFolder . $fileName);
        throw new RuntimeException('Failed to insert into database: ' . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_insert);
    
    echo "1|Image uploaded successfully|" . $fileName;

} catch (RuntimeException $e) {
    error_log("Upload Temp News Large Image Error: " . $e->getMessage() . " | User ID: $uid");
    http_response_code(400);
    echo "0|" . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected Upload Temp News Large Image Error: " . $e->getMessage() . " | User ID: $uid");
    http_response_code(500);
    echo "0|An unexpected error occurred";
}
?>