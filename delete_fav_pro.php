<?php
/**
 * File: delete_fav_pro.php
 * Version: PHP 8.3
 * Description: حذف منتج من قائمة المفضلة للمستخدم
 * 
 * هذا الملف يستقبل طلب AJAX لحذف منتج من قائمة المفضلة
 * ويعيد true إذا تم الحذف بنجاح
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

// التحقق من وجود معرف المستخدم في الجلسة
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    echo json_encode([
        'success' => false,
        'message' => 'يجب تسجيل الدخول أولاً'
    ]);
    exit();
}

// التحقق من وجود معرف المنتج في طلب POST
if (!isset($_POST['pro_id']) || empty($_POST['pro_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'معرف المنتج مطلوب'
    ]);
    exit();
}

// تنظيف المدخلات
$user_id = (int)$_SESSION['uid_indm'];
$product_id = (int)$_POST['pro_id'];

// التحقق من صحة القيم
if ($user_id <= 0 || $product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'بيانات غير صالحة'
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

// تنفيذ عملية الحذف
$sql = "DELETE FROM favourites_table 
        WHERE user_id = {$user_id} 
        AND pro_id = {$product_id}";

$result = mysqli_query($con, $sql);

if ($result) {
    // التحقق من عدد الصفوف المتأثرة (هل تم حذف صف فعلاً)
    if (mysqli_affected_rows($con) > 0) {
        // تم الحذف بنجاح
        echo json_encode([
            'success' => true,
            'message' => 'تم حذف المنتج من المفضلة بنجاح',
            'action' => 'deleted',
            'affected_rows' => mysqli_affected_rows($con)
        ]);
    } else {
        // لم يتم العثور على المنتج في المفضلة
        echo json_encode([
            'success' => false,
            'message' => 'المنتج غير موجود في قائمة المفضلة',
            'action' => 'not_found'
        ]);
    }
} else {
    // فشل في الحذف بسبب خطأ في قاعدة البيانات
    echo json_encode([
        'success' => false,
        'message' => 'فشل في حذف المنتج: ' . mysqli_error($con),
        'error' => mysqli_error($con)
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>