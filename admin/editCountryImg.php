<?php
/**
 * ملف رفع وتحديث علم الدولة
 * 
 * @filename    editCountryImg.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن رفع صورة علم الدولة وتحديثها في قاعدة البيانات
 *              مع تغيير حجم الصورة إلى 30x20 بكسل وحذف الصورة القديمة
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل الجلسة - تم التعليق عليها لأنها غير مستخدمة حالياً
// ob_start();
// session_start();

include "../common.php";

// التحقق من صحة الإدخال باستخدام filter_input المتوفر في PHP 8.3
$cn_id = filter_input(INPUT_POST, 'cn_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 999999]
]);

// التحقق من صحة المعرف
if ($cn_id === false || $cn_id === null) {
    http_response_code(400);
    die(json_encode(['error' => 'معرف الدولة غير صحيح']));
}

// استخدام الاستعلام المحضر (Prepared Statement) لمنع حقن SQL
$stmt_cn = mysqli_prepare($con, "SELECT * FROM country WHERE cn_id = ?");
mysqli_stmt_bind_param($stmt_cn, "i", $cn_id);
mysqli_stmt_execute($stmt_cn);
$res_cn = mysqli_stmt_get_result($stmt_cn);
$row_cn = mysqli_fetch_object($res_cn);

// التحقق من وجود الدولة
if (!$row_cn) {
    http_response_code(404);
    die(json_encode(['error' => 'الدولة غير موجودة']));
}

// تعريف المسار الكامل والمجلد
$targetFolder = '../images/country_flag/';
$fullTargetPath = realpath(dirname(__FILE__) . '/../images/country_flag/') . DIRECTORY_SEPARATOR;

// التأكد من وجود المجلد
if (!is_dir($fullTargetPath)) {
    if (!mkdir($fullTargetPath, 0755, true)) {
        http_response_code(500);
        die(json_encode(['error' => 'لا يمكن إنشاء مجلد الرفع']));
    }
}

// التحقق من وجود ملف مرفوع
if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    http_response_code(400);
    die(json_encode(['error' => 'لم يتم رفع أي ملف']));
}

// تنظيف اسم الملف من الأحرف غير المسموح بها
$cleanCnName = preg_replace('/[^a-zA-Z0-9\-_]/', '', $row_cn->cn_name);
$cleanCnCurrency = preg_replace('/[^a-zA-Z0-9\-_]/', '', $row_cn->cn_currency);
$cleanFileName = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $_FILES['Filedata']['name']);

// إنشاء اسم الملف الجديد
$newFileName = $cleanCnName . $cleanCnCurrency . '_' . time() . '_' . uniqid() . '.' . 'png';
$targetPath = $targetFolder . $newFileName;
$fullTargetPath = $fullTargetPath . $newFileName;

// التحقق من نوع الملف باستخدام MIME type و Fileinfo
$fileTypes = ['image/png']; // MIME types المسموح بها
$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
$uploadedFileMime = finfo_file($fileInfo, $_FILES['Filedata']['tmp_name']);
finfo_close($fileInfo);

// التحقق من الامتداد
$fileParts = pathinfo($_FILES['Filedata']['name']);
$fileExtension = strtolower($fileParts['extension'] ?? '');

if (!in_array($uploadedFileMime, $fileTypes) || $fileExtension !== 'png') {
    http_response_code(400);
    die(json_encode(['error' => 'نوع الملف غير مسموح به. يرجى رفع ملف PNG فقط']));
}

// التحقق من حجم الملف (الحد الأقصى 2 ميجابايت)
$maxFileSize = 2 * 1024 * 1024; // 2MB
if ($_FILES['Filedata']['size'] > $maxFileSize) {
    http_response_code(400);
    die(json_encode(['error' => 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت']));
}

// التحقق من عدم وجود أخطاء في الرفع
if ($_FILES['Filedata']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'الملف أكبر من الحد المسموح به في السيرفر',
        UPLOAD_ERR_FORM_SIZE => 'الملف أكبر من الحد المسموح به في النموذج',
        UPLOAD_ERR_PARTIAL => 'تم رفع جزء فقط من الملف',
        UPLOAD_ERR_NO_FILE => 'لم يتم رفع أي ملف',
        UPLOAD_ERR_NO_TMP_DIR => 'مجلد مؤقت غير موجود',
        UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص',
        UPLOAD_ERR_EXTENSION => 'امتداد الملف غير مسموح به'
    ];
    $errorMessage = $uploadErrors[$_FILES['Filedata']['error']] ?? 'خطأ غير معروف في الرفع';
    http_response_code(400);
    die(json_encode(['error' => $errorMessage]));
}

// نقل الملف
if (move_uploaded_file($_FILES["Filedata"]["tmp_name"], $fullTargetPath)) {
    try {
        // تغيير حجم الصورة
        $imgSImage = new SimpleImage();
        $imgSImage->load($fullTargetPath);
        $imgSImage->resize(30, 20); // width, height
        
        // حفظ الصورة بعد تغيير الحجم
        $imgSImage->save($fullTargetPath);
        
        // حذف العلم القديم إذا وجد
        if (!empty($row_cn->cn_flag)) {
            $oldPath = $targetFolder . $row_cn->cn_flag;
            $fullOldPath = realpath(dirname(__FILE__) . '/../images/country_flag/' . $row_cn->cn_flag);
            
            // التأكد من أن المسار ضمن المجلد المسموح به (الأمان)
            if ($fullOldPath && strpos($fullOldPath, $fullTargetPath) === 0 && is_file($fullOldPath)) {
                unlink($fullOldPath);
            }
        }
        
        // تحديث قاعدة البيانات باستخدام استعلام محضر
        $sql = "UPDATE country SET cn_flag = ? WHERE cn_id = ?";
        $stmt_update = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt_update, "si", $newFileName, $cn_id);
        
        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            
            // إرجاع استجابة نجاح
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'تم رفع وتحديث العلم بنجاح',
                'filename' => $newFileName,
                'path' => $targetPath
            ]);
        } else {
            // فشل تحديث قاعدة البيانات - حذف الملف المرفوع
            if (is_file($fullTargetPath)) {
                unlink($fullTargetPath);
            }
            http_response_code(500);
            echo json_encode(['error' => 'فشل في تحديث قاعدة البيانات']);
        }
        
    } catch (Exception $e) {
        // فشل في معالجة الصورة - حذف الملف المرفوع
        if (is_file($fullTargetPath)) {
            unlink($fullTargetPath);
        }
        http_response_code(500);
        echo json_encode(['error' => 'فشل في معالجة الصورة: ' . $e->getMessage()]);
    }
} else {
    http_response_code(500);
    echo json_encode(['error' => 'فشل في نقل الملف إلى المجلد المطلوب']);
}

// إغلاق الاتصال بقاعدة البيانات
mysqli_stmt_close($stmt_cn);
mysqli_close($con);
?>