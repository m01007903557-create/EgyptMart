<?php
/**
 * File: favourite_add.php
 * Version: PHP 8.3
 * Description: إضافة منتج إلى قائمة المفضلة للمستخدم
 * 
 * هذا الملف يستقبل طلب AJAX لإضافة منتج إلى قائمة المفضلة
 * ويعيد true إذا تمت الإضافة بنجاح
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

// التحقق مما إذا كان المنتج موجود بالفعل في قائمة المفضلة
$check_sql = "SELECT * FROM favourites_table 
              WHERE user_id = {$user_id} 
              AND pro_id = {$product_id} 
              LIMIT 1";

$check_result = mysqli_query($con, $check_sql);

if (!$check_result) {
    echo json_encode([
        'success' => false,
        'message' => 'خطأ في قاعدة البيانات: ' . mysqli_error($con)
    ]);
    exit();
}

// إذا لم يكن المنتج موجوداً في المفضلة، قم بإضافته
if (mysqli_num_rows($check_result) == 0) {
    
    $insert_sql = "INSERT INTO favourites_table 
                   (user_id, pro_id, created_datetime) 
                   VALUES ({$user_id}, {$product_id}, NOW())";
    
    $insert_result = mysqli_query($con, $insert_sql);
    
    if ($insert_result) {
        // تمت الإضافة بنجاح
        echo json_encode([
            'success' => true,
            'message' => 'تمت إضافة المنتج إلى المفضلة',
            'action' => 'added'
        ]);
    } else {
        // فشل في الإضافة
        echo json_encode([
            'success' => false,
            'message' => 'فشل في إضافة المنتج إلى المفضلة: ' . mysqli_error($con)
        ]);
    }
} else {
    // المنتج موجود بالفعل في المفضلة
    // يمكنك اختيارياً حذفه إذا أردت (toggle functionality)
    echo json_encode([
        'success' => true,
        'message' => 'المنتج موجود بالفعل في المفضلة',
        'action' => 'already_exists'
    ]);
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>