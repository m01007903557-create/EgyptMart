<?php
/**
 * File: admin/uploadifive/uploadifive.php
 * Version: PHP 8.3
 * Description: معالج رفع الملفات البسيط لمكتبة UploadiFive
 * 
 * هذا الملف يتعامل مع رفع الملفات عبر مكتبة UploadiFive
 * مع التحقق الأساسي من نوع الملف
 * 
 * ملاحظة: هذا إصدار مبسط لا يتضمن التحقق من كون الملف صورة صالحة
 */

// تعطيل عرض الأخطاء في الإنتاج (يمكن تفعيلها للتصحيح)
// error_reporting(0);
// ini_set('display_errors', 0);

/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

// تعيين مسار رفع الملفات (يتم تعديله حسب الحاجة)
$uploadDir = '/uploads/';

// معالجة رفع الملف
if (!empty($_FILES) && isset($_FILES['Filedata'])) {
    
    $fileData = $_FILES['Filedata'];
    
    // التحقق من وجود بيانات الملف
    if (isset($fileData['tmp_name']) && isset($fileData['name']) && isset($fileData['error'])) {
        
        // التحقق من عدم وجود أخطاء في رفع الملف
        if ($fileData['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            ];
            
            $errorMessage = isset($errorMessages[$fileData['error']]) 
                ? $errorMessages[$fileData['error']] 
                : 'Unknown upload error';
            
            echo $errorMessage;
            error_log("Upload error in uploadifive-simple.php: " . $errorMessage);
            exit();
        }
        
        $tempFile   = $fileData['tmp_name'];
        
        // بناء المسار الكامل لحفظ الملف
        $uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        $targetFile = $uploadDir . basename($fileData['name']);
        
        // التحقق من وجود مجلد الرفع، وإنشائه إذا لم يكن موجوداً
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                echo "Error creating upload directory.";
                error_log("فشل في إنشاء مجلد الرفع: " . $uploadDir);
                exit();
            }
        }
        
        // التحقق من قابلية الكتابة في المجلد
        if (!is_writable($uploadDir)) {
            echo "Upload directory is not writable.";
            error_log("مجلد الرفع غير قابل للكتابة: " . $uploadDir);
            exit();
        }
        
        // التحقق من حجم الملف (اختياري - حد 5 ميجابايت)
        $maxFileSize = 5 * 1024 * 1024; // 5 MB
        if ($fileData['size'] > $maxFileSize) {
            echo "File size exceeds limit (5MB).";
            error_log("حجم الملف يتجاوز الحد المسموح: " . $fileData['size'] . " بايت");
            exit();
        }
        
        // أنواع الملفات المسموح بها
        $fileTypes = array('jpg', 'jpeg', 'gif', 'png');
        
        // الحصول على امتداد الملف
        $fileParts = pathinfo($fileData['name']);
        $fileExtension = isset($fileParts['extension']) ? strtolower($fileParts['extension']) : '';
        
        // التحقق من نوع الملف
        if (in_array($fileExtension, $fileTypes)) {
            
            // التأكد من عدم وجود ملف بنفس الاسم
            if (file_exists($targetFile)) {
                // إضافة رقم للملف إذا كان موجوداً
                $filename = $fileParts['filename'];
                $counter = 1;
                while (file_exists($uploadDir . $filename . '_' . $counter . '.' . $fileExtension)) {
                    $counter++;
                }
                $targetFile = $uploadDir . $filename . '_' . $counter . '.' . $fileExtension;
            }
            
            // حفظ الملف
            if (move_uploaded_file($tempFile, $targetFile)) {
                echo "1"; // رمز النجاح
            } else {
                echo "Error moving uploaded file.";
                error_log("خطأ في نقل الملف المرفوع: " . $fileData['name']);
            }
            
        } else {
            // نوع الملف غير مسموح به
            echo 'Invalid file type. Allowed: ' . implode(', ', $fileTypes);
            error_log("نوع ملف غير مسموح به: " . ($fileExtension ?: 'unknown'));
        }
        
    } else {
        echo "Invalid file data.";
        error_log("بيانات ملف غير صالحة في uploadifive-simple.php");
    }
    
} else {
    echo "No file uploaded.";
    error_log("محاولة رفع ملف بدون بيانات في uploadifive-simple.php");
}
?>