<?php
/**
 * File: admin/del_country.php
 * Version: PHP 8.3
 * Description: حذف بلد من قاعدة البيانات مع المدن المرتبطة به
 * 
 * هذا الملف يستقبل طلب GET لحذف بلد
 * ويقوم بحذف جميع المدن المرتبطة به أولاً
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include "../common.php";

// تعيين نوع المحتوى إلى نص عادي
header('Content-Type: text/plain; charset=UTF-8');

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    error_log("خطأ في الاتصال بقاعدة البيانات في del_country.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف البلد
if (!isset($_GET['hid']) || empty($_GET['hid'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$cn_id = (int)$_GET['hid'];

// التحقق من صحة القيمة
if ($cn_id <= 0) {
    echo "0";
    exit();
}

// بدء معاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // أولاً: حذف جميع المدن المرتبطة بالبلد
    $delete_cities_sql = "DELETE FROM city WHERE ct_cn_id = " . $cn_id;
    $delete_cities_result = mysqli_query($con, $delete_cities_sql);
    
    if (!$delete_cities_result) {
        throw new Exception("خطأ في حذف المدن: " . mysqli_error($con));
    }
    
    // ثانياً: حذف البلد نفسه
    $delete_country_sql = "DELETE FROM country WHERE cn_id = " . $cn_id;
    $delete_country_result = mysqli_query($con, $delete_country_sql);
    
    if (!$delete_country_result) {
        throw new Exception("خطأ في حذف البلد: " . mysqli_error($con));
    }
    
    // التحقق من أن البلد تم حذفه بالفعل
    if (mysqli_affected_rows($con) == 0) {
        throw new Exception("البلد غير موجود");
    }
    
    // تأكيد المعاملة (Commit)
    mysqli_commit($con);
    
    echo "1"; // نجاح
    
} catch (Exception $e) {
    // التراجع عن المعاملة (Rollback) في حالة الخطأ
    mysqli_rollback($con);
    error_log("خطأ في حذف البلد: " . $e->getMessage());
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>