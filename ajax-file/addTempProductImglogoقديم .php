<?php
/**
 * File: ajax/addTempProductImglogo.php

 * Description: رفع صورة شعار منتج مؤقتة مع إنشاء صورة مصغرة وحذف الصورة القديمة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

// ob_start();
// session_start();
require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المستخدم
if (!isset($_POST['usr']) || !is_numeric($_POST['usr'])) {
    http_response_code(400);
    die("Invalid user ID");
}

$usr = (int)$_POST['usr'];

// التحقق من وجود الملفات المرفوعة
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die("No file uploaded");
}

// إعدادات رفع الملفات
$targetFolder = __DIR__ . '/../upload/myproduct/';
$thumbFolder = __DIR__ . '/../upload/myproduct/thumb/';
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

    // إنشاء اسم فريد وآمن للملف
    $timestamp = time();
    $randomString = bin2hex(random_bytes(6));
    $newFileName = sprintf(
        'prd-%d-%d-%s.%s',
        $usr,
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

    global $con;

    // جلب الصورة القديمة من الجدول المؤقت
    $select_sql = "SELECT tpi_logo FROM temp_product_image WHERE tpi_usr_id = ? LIMIT 1";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $usr);
    mysqli_stmt_execute($stmt_select);
    $result_select = mysqli_stmt_get_result($stmt_select);
    $row_tpi = mysqli_fetch_object($result_select);
    mysqli_stmt_close($stmt_select);

    // حذف الصورة القديمة إذا كانت موجودة
    if ($row_tpi && !empty($row_tpi->tpi_logo)) {
        $old_image = $row_tpi->tpi_logo;
        
        $old_path_lrg = $targetFolder . $old_image;
        if (file_exists($old_path_lrg) && is_file($old_path_lrg)) {
            unlink($old_path_lrg);
        }
        
        $old_path_thumb = $thumbFolder . $old_image;
        if (file_exists($old_path_thumb) && is_file($old_path_thumb)) {
            unlink($old_path_thumb);
        }
    }

    // التحقق من وجود سجل للمستخدم
    $check_sql = "SELECT tpi_id FROM temp_product_image WHERE tpi_usr_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'i', $usr);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $record_exists = mysqli_num_rows($result_check) > 0;
    mysqli_stmt_close($stmt_check);

    if ($record_exists) {
        // تحديث السجل الموجود
        $update_sql = "UPDATE temp_product_image SET tpi_logo = ? WHERE tpi_usr_id = ?";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'si', $newFileName, $usr);
        
        if (!mysqli_stmt_execute($stmt_update)) {
            // إذا فشل التحديث، نحذف الملفات الجديدة
            unlink($targetPath);
            unlink($thumbPath);
            throw new RuntimeException('Failed to update database');
        }
        mysqli_stmt_close($stmt_update);
    } else {
        // حذف أي سجلات قديمة (للأمان)
        $delete_sql = "DELETE FROM temp_product_image WHERE tpi_usr_id = ?";
        $stmt_delete = mysqli_prepare($con, $delete_sql);
        mysqli_stmt_bind_param($stmt_delete, 'i', $usr);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        // إدراج سجل جديد
        $insert_sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_logo, tpi_upload_date) VALUES (?, ?, NOW())";
        $stmt_insert = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($stmt_insert, 'is', $usr, $newFileName);
        
        if (!mysqli_stmt_execute($stmt_insert)) {
            // إذا فشل الإدراج، نحذف الملفات الجديدة
            unlink($targetPath);
            unlink($thumbPath);
            throw new RuntimeException('Failed to insert into database');
        }
        mysqli_stmt_close($stmt_insert);
    }
    
    echo "1|Product logo uploaded successfully";

} catch (RuntimeException $e) {
    error_log("Upload Temp Product Logo Error: " . $e->getMessage() . " | User ID: $usr");
    http_response_code(400);
    echo "0|" . $e->getMessage();
} catch (Exception $e) {
    error_log("Unexpected Upload Temp Product Logo Error: " . $e->getMessage() . " | User ID: $usr");
    http_response_code(500);
    echo "0|An unexpected error occurred";
}
?>