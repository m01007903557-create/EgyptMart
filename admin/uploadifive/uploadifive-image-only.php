<?php
/**
 * File: admin/uploadifive/uploadifive-image-only.php
 * Version: PHP 8.3
 * Description: معالج رفع الملفات لمكتبة UploadiFive
 * 
 * هذا الملف يتعامل مع رفع الملفات عبر مكتبة UploadiFive
 * مع التحقق من نوع الملف وحجمه والتأكد من أنه صورة صالحة
 */

// تعطيل عرض الأخطاء في الإنتاج (يمكن تفعيلها للتصحيح)
// error_reporting(0);
// ini_set('display_errors', 0);

/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

/*
IMPORTANT: This script requires the PHP GD library
*/

// تعيين مسار رفع الملفات (يتم تعديله حسب الحاجة)
$uploadDir = '/uploads/';

/**
 * معالج الأخطاء المخصص
 * يقوم بتجاهل الأخطاء الناتجة عن دالة getimagesize
 * 
 * @param int $errno رقم الخطأ
 * @param string $errstr نص الخطأ
 * @param string $errfile اسم الملف
 * @param int $errline رقم السطر
 * @return bool true لمنع تنفيذ معالج الأخطاء الداخلي
 */
function errorHandler($errno, $errstr, $errfile, $errline) {
    // في هذا السكريبت، نقوم بتجاهل أي أخطاء ناتجة عن getimagesize
    // والتي ستظهر إذا كان الملف ليس صورة (ليس له أبعاد صالحة)
    
    /* عدم تنفيذ معالج الأخطاء الداخلي لـ PHP */
    return true;
}

// تعيين معالج الأخطاء المخصص
$old_error_handler = set_error_handler("errorHandler");

/**
 * التحقق مما إذا كان الملف صورة صالحة
 * 
 * @param string $tempFile المسار المؤقت للملف
 * @return bool true إذا كان الملف صورة صالحة
 */
function isImage($tempFile) {
    
    // الحصول على أبعاد الصورة
    $size = @getimagesize($tempFile);
    
    if (isset($size) && isset($size[0]) && isset($size[1]) && $size[0] > 0 && $size[1] > 0) {
        return true;
    } else {
        return false;
    }
}

// معالجة رفع الملف
if (!empty($_FILES) && isset($_FILES['Filedata'])) {
    
    $fileData = $_FILES['Filedata'];
    
    if ($fileData && isset($fileData['tmp_name']) && isset($fileData['name']) && isset($fileData['size'])) {
        
        $tempFile   = $fileData['tmp_name'];
        
        // بناء المسار الكامل لحفظ الملف
        $uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        $targetFile = $uploadDir . basename($fileData['name']);
        
        // التحقق من وجود مجلد الرفع، وإنشائه إذا لم يكن موجوداً
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // أنواع الملفات المسموح بها
        $fileTypes = array('jpg', 'jpeg', 'gif', 'png');
        $fileParts = pathinfo($fileData['name']);
        $fileExtension = isset($fileParts['extension']) ? strtolower($fileParts['extension']) : '';
        
        // التحقق من نوع الملف
        $isValidType = in_array($fileExtension, $fileTypes);
        
        // التحقق من حجم الملف (اختياري - يمكن إضافة حد أقصى)
        $isValidSize = $fileData['size'] > 0 && $fileData['size'] < 5 * 1024 * 1024; // أقل من 5 ميجابايت
        
        // التحقق من أن الملف صورة صالحة
        $isValidImage = isImage($tempFile);
        
        if ($isValidType && $isValidSize && $isValidImage) {
            
            // حفظ الملف
            if (move_uploaded_file($tempFile, $targetFile)) {
                echo "1";
            } else {
                echo "Error moving uploaded file.";
                error_log("خطأ في نقل الملف المرفوع: " . $fileData['name']);
            }
            
        } else {
            
            // تحديد سبب الرفض
            $errorMessage = '';
            if (!$isValidType) {
                $errorMessage = 'Invalid file type. Allowed: ' . implode(', ', $fileTypes);
            } elseif (!$isValidSize) {
                $errorMessage = 'File size exceeds limit (5MB).';
            } elseif (!$isValidImage) {
                $errorMessage = 'File is not a valid image.';
            }
            
            echo $errorMessage;
            error_log("رفض رفع الملف: " . $errorMessage . " - " . ($fileData['name'] ?? 'unknown'));
        }
    } else {
        echo "Invalid file data.";
        error_log("بيانات ملف غير صالحة في uploadifive.php");
    }
} else {
    echo "No file uploaded.";
    error_log("محاولة رفع ملف بدون بيانات في uploadifive.php");
}

// استعادة معالج الأخطاء القديم (اختياري)
// restore_error_handler();
?>