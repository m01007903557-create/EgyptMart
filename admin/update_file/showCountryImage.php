<?php
/**
 * File: admin/ajax-file/showCountryImage.php

 * Version: PHP 8.3
 * Description: حذف علم البلد من المجلد
 * 
 * هذا الملف يستقبل طلب AJAX لحذف ملف علم البلد
 * ويعيد مسار الملف المحذوف للتأكيد
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
    error_log("خطأ في الاتصال بقاعدة البيانات في delete-country-flag.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف البلد في طلب GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$cn_id = (int)$_GET['id'];

if ($cn_id <= 0) {
    echo "0";
    exit();
}

// جلب معلومات العلم من قاعدة البيانات
$sqlImg = "SELECT * FROM country WHERE cn_id = " . $cn_id . " LIMIT 1";
$resImg = mysqli_query($con, $sqlImg);

if (!$resImg) {
    error_log("خطأ في استعلام قاعدة البيانات: " . mysqli_error($con));
    echo "0";
    exit();
}

if (mysqli_num_rows($resImg) == 0) {
    echo "0";
    exit();
}

$rowImg = mysqli_fetch_object($resImg);

// بناء مسار الملف
$path = "../images/country_flag/" . ($rowImg->cn_flag ?? '');

// التحقق من وجود الملف وحذفه
if (!empty($rowImg->cn_flag) && file_exists($path) && is_file($path)) {
    if (unlink($path)) {
        // تحديث قاعدة البيانات لإزالة اسم الملف (اختياري)
        $sqlUpdate = "UPDATE country SET cn_flag = '' WHERE cn_id = " . $cn_id;
        mysqli_query($con, $sqlUpdate);
        
        echo $path; // إرجاع المسار المحذوف
    } else {
        error_log("فشل في حذف الملف: " . $path);
        echo "0";
    }
} else {
    // الملف غير موجود - نعتبره محذوفاً
    echo $path ?: "1";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>