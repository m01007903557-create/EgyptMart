<?php
/**
 * File: del_temp_product_image.php
 * Version: PHP 8.3
 * Description: حذف صورة منتج مؤقتة
 * 
 * هذا الملف يقوم بحذف صورة منتج مؤقتة من مجلد upload/myproduct
 * ومن قاعدة البيانات temp_products_image
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "common.php";

// تعيين نوع المحتوى إلى JSON (للاستجابة)
header('Content-Type: application/json');

// التحقق من وجود معرف الصورة في طلب GET
if (!isset($_GET['imid']) || empty($_GET['imid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الصورة مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$image_id = (int)$_GET['imid'];

// التحقق من صحة القيمة
if ($image_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف الصورة غير صالح'
    ]);
    exit();
}

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في الاتصال بقاعدة البيانات'
    ]);
    exit();
}

// جلب معلومات الصورة قبل الحذف
$select_sql = "SELECT * FROM temp_products_image WHERE tmpimg_id = {$image_id} LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في جلب بيانات الصورة: ' . mysqli_error($con)
    ]);
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'الصورة غير موجودة في قاعدة البيانات'
    ]);
    exit();
}

$rowk = mysqli_fetch_object($select_result);
$image_path = "";

// حذف الملف الفعلي من السيرفر
if (!empty($rowk->tmpimg_image)) {
    $image_path = "upload/myproduct/" . $rowk->tmpimg_image;
    
    if (file_exists($image_path)) {
        if (!unlink($image_path)) {
            // فشل في حذف الملف - لكن نستمر في حذف سجل قاعدة البيانات
            error_log("فشل في حذف ملف الصورة: " . $image_path);
        }
    } else {
        error_log("ملف الصورة غير موجود: " . $image_path);
    }
}

// حذف السجل من قاعدة البيانات
$delete_sql = "DELETE FROM temp_products_image WHERE tmpimg_id = {$image_id}";
$delete_result = mysqli_query($con, $delete_sql);

if ($delete_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف الصورة المؤقتة للمنتج بنجاح',
            'action' => 'deleted',
            'image_path' => $image_path,
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'لم يتم العثور على الصورة في قاعدة البيانات',
            'action' => 'not_found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'فشل في حذف الصورة: ' . mysqli_error($con),
        'error' => mysqli_error($con)
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>