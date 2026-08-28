<?php
/**
 * File: ajax/addTempEnquiryFile.php

 * Description: رفع مرفق مؤقت للرسائل
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المستخدم
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    die("Invalid user ID");
}

$usr_id = (int)$_POST['id'];

// التحقق من وجود الملفات المرفوعة
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die("No file uploaded");
}

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/../upload/message_attachment/';
$maxFileSize = 10 * 1024 * 1024; // 10 ميجابايت للمرفقات
$allowedFileTypes = ['jpg', 'jpeg', 'gif', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'];
$allowedMimeTypes = [
    'image/jpeg',
    'image/pjpeg',
    'image/gif',
    'image/png',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'text/plain',
    'application/zip',
    'application/x-zip-compressed'
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

    // إنشاء اسم فريد وآمن للملف
    $timestamp = time();
    $randomString = bin2hex(random_bytes(4));
    $originalName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $fileInfo['filename'] ?? 'file');
    $originalName = substr($originalName, 0, 50); // تحديد طول اسم الملف
    
    $newFileName = sprintf(
        '%d-%d-%s-%s.%s',
        $usr_id,
        $timestamp,
        $randomString,
        $originalName,
        $extension
    );

    // تنظيف اسم الملف بشكل إضافي
    $newFileName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $newFileName);
    
    $targetPath = $targetFolder . $newFileName;

    // نقل الملف إلى المجلد الهدف
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new RuntimeException('Failed to move uploaded file');
    }

    // تغيير صلاحيات الملف
    chmod($targetPath, 0644);

    global $con;

    // إدراج السجل في قاعدة البيانات
    $insert_sql = "INSERT INTO temp_msg_attachment (tma_usr_id, tma_file, tma_upload_date) VALUES (?, ?, NOW())";
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'is', $usr_id, $newFileName);

    if (!mysqli_stmt_execute($stmt_insert)) {
        // إذا فشل الإدراج، نحذف الملف
        unlink($targetPath);
        throw new RuntimeException('Failed to insert into database: ' . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_insert);
    
    echo "1|File uploaded successfully";

} catch (RuntimeException $e) {
    error_log("Upload Temp Message Attachment Error: " . $e->getMessage() . " | User ID: $usr_id");
    http_response_code(400);
    echo "0|" . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected Upload Temp Message Attachment Error: " . $e->getMessage() . " | User ID: $usr_id");
    http_response_code(500);
    echo "0|An unexpected error occurred";
}
?>