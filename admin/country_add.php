<?php
/**
 * File: admin/country_add.php
 * Version: PHP 8.3
 * Description: إضافة بلد جديد إلى قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب GET لإضافة بلد جديد
 * مع إمكانية إضافة اسم البلد والعملة
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
    error_log("خطأ في الاتصال بقاعدة البيانات في country_add.php");
    echo "0";
    exit();
}

// التحقق من وجود المعلمات المطلوبة
if (!isset($_GET['country_add']) || empty($_GET['country_add'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$country_add = trim($_GET['country_add']);
$currency_add = isset($_GET['currency_add']) ? trim($_GET['currency_add']) : '';

// التحقق من صحة القيمة
if (empty($country_add)) {
    echo "0";
    exit();
}

// الهروب من الأحرف الخاصة لمنع SQL Injection
$country_add_escaped = mysqli_real_escape_string($con, $country_add);
$currency_add_escaped = mysqli_real_escape_string($con, $currency_add);

// إدراج البلد في قاعدة البيانات
$sql = "INSERT INTO country SET 
        cn_name = '{$country_add_escaped}', 
        cn_currency = '{$currency_add_escaped}'";

$result = mysqli_query($con, $sql);

if ($result) {
    // الحصول على معرف البلد المضاف
    $new_id = mysqli_insert_id($con);
    echo $new_id; // إرجاع معرف البلد الجديد
} else {
    error_log("خطأ في إضافة البلد: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>