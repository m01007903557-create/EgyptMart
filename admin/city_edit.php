<?php
/**
 * File: admin/city_edit.php
 * Version: PHP 8.3
 * Description: تحديث بيانات مدينة في قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب AJAX لتحديث بيانات مدينة موجودة
 * ويعيد اسم المدينة المحدثة كاستجابة
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
    error_log("خطأ في الاتصال بقاعدة البيانات في city_edit.php");
    echo "0";
    exit();
}

// التحقق من وجود المعلمات المطلوبة
if (!isset($_GET['hid']) || !isset($_GET['city_inp']) || !isset($_GET['state_inp'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$hid = (int)$_GET['hid'];
$city_inp = trim($_GET['city_inp']);
$state_inp = (int)$_GET['state_inp'];
$metro_inp = isset($_GET['metro_inp']) ? (int)$_GET['metro_inp'] : 0;

// التحقق من صحة القيم
if ($hid <= 0 || empty($city_inp) || $state_inp <= 0) {
    echo "0";
    exit();
}

// الهروب من الأحرف الخاصة لمنع SQL Injection
$city_inp_escaped = mysqli_real_escape_string($con, $city_inp);

// تحديث بيانات المدينة
$recObj_sql = "UPDATE city SET 
               ct_name = '{$city_inp_escaped}', 
               ct_metro = {$metro_inp}, 
               ct_state = {$state_inp} 
               WHERE ct_id = {$hid}";

$recObj = mysqli_query($con, $recObj_sql);

if ($recObj) {
    // إرجاع اسم المدينة المحدثة
    echo $city_inp;
} else {
    error_log("خطأ في تحديث المدينة: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>