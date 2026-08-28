<?php
/**
 * File: admin/country_edit.php
 * Version: PHP 8.3
 * Description: تحديث بيانات بلد في قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب GET لتحديث بيانات بلد موجود
 * ويعيد الاسم والعملة المحدثين كاستجابة
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
    error_log("خطأ في الاتصال بقاعدة البيانات في country_edit.php");
    echo "0";
    exit();
}

// التحقق من وجود المعلمات المطلوبة
if (!isset($_GET['hid']) || !isset($_GET['country_inp'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$hid = (int)$_GET['hid'];
$country_inp = trim($_GET['country_inp']);
$currency_inp = isset($_GET['currency_inp']) ? trim($_GET['currency_inp']) : '';

// التحقق من صحة القيم
if ($hid <= 0 || empty($country_inp)) {
    echo "0";
    exit();
}

// الهروب من الأحرف الخاصة لمنع SQL Injection
$country_inp_escaped = mysqli_real_escape_string($con, $country_inp);
$currency_inp_escaped = mysqli_real_escape_string($con, $currency_inp);

// تحديث بيانات البلد
$recObj_sql = "UPDATE country SET 
               cn_name = '{$country_inp_escaped}', 
               cn_currency = '{$currency_inp_escaped}' 
               WHERE cn_id = {$hid}";

$recObj = mysqli_query($con, $recObj_sql);

if ($recObj) {
    // إرجاع الاسم والعملة المحدثين
    echo htmlspecialchars($country_inp) . " - " . htmlspecialchars($currency_inp);
} else {
    error_log("خطأ في تحديث البلد: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>