<?php
/**
 * File: del_product_image.php
 * Version: PHP 8.3
 * Description: حذف صورة منتج رئيسية
 * 
 * هذا الملف يقوم بحذف صورة منتج رئيسية من مجلد upload/myproduct
 * وتحديث حقل pd_image في قاعدة البيانات ليكون فارغاً
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

// التحقق من وجود معرف المنتج في طلب GET
if (!isset($_GET['imid']) || empty($_GET['imid'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المنتج مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$product_id = (int)$_GET['imid'];

// التحقق من صحة القيمة
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المنتج غير صالح'
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

// التحقق من صلاحيات المستخدم (اختياري - حسب احتياجات النظام)
// يمكن إضافة التحقق من أن المستخدم الحالي هو مالك المنتج
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $user_id = (int)$_SESSION['uid_indm'];
    
    // التحقق من أن المنتج يخص المستخدم الحالي
    $check_owner_sql = "SELECT pd_uid FROM products WHERE pd_id = {$product_id} LIMIT 1";
    $check_owner_result = mysqli_query($con, $check_owner_sql);
    
    if ($check_owner_result && mysqli_num_rows($check_owner_result) > 0) {
        $owner_row = mysqli_fetch_assoc($check_owner_result);
        if ($owner_row['pd_uid'] != $user_id) {
            echo json_encode([
                'success' => false,
                'message' => 'ليس لديك صلاحية لحذف هذه الصورة'
            ]);
            exit();
        }
    }
}

// جلب معلومات الصورة قبل الحذف
$select_sql = "SELECT pd_image FROM products WHERE pd_id = {$product_id} LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في جلب بيانات المنتج: ' . mysqli_error($con)
    ]);
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'المنتج غير موجود في قاعدة البيانات'
    ]);
    exit();
}

$rowk = mysqli_fetch_object($select_result);
$image_path = "";

// حذف الملف الفعلي من السيرفر إذا كان موجوداً
if (!empty($rowk->pd_image)) {
    $image_path = "upload/myproduct/" . $rowk->pd_image;
    
    if (file_exists($image_path)) {
        if (!unlink($image_path)) {
            // فشل في حذف الملف - لكن نستمر في تحديث قاعدة البيانات
            error_log("فشل في حذف ملف الصورة: " . $image_path);
        }
    } else {
        error_log("ملف الصورة غير موجود: " . $image_path);
    }
}

// تحديث حقل الصورة في قاعدة البيانات ليكون فارغاً
$update_sql = "UPDATE products SET pd_image = '' WHERE pd_id = {$product_id}";
$update_result = mysqli_query($con, $update_sql);

if ($update_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف صورة المنتج بنجاح',
            'action' => 'deleted',
            'image_path' => $image_path,
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        // لم يتم تحديث أي صف (ربما الصورة كانت فارغة أصلاً)
        echo json_encode([
            'success' => true,
            'message' => 'لم يتم العثور على صورة للمنتج',
            'action' => 'already_empty',
            'image_path' => $image_path
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'فشل في تحديث بيانات المنتج: ' . mysqli_error($con),
        'error' => mysqli_error($con)
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>