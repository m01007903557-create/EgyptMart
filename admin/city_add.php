<?php
/**
 * File: admin/city_add.php
 * Version: PHP 8.3
 * Description: إضافة مدينة جديدة إلى قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب AJAX لإضافة مدينة جديدة
 * مع ربطها بالبلد المحدد
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
    error_log("خطأ في الاتصال بقاعدة البيانات في add-city.php");
    echo "0";
    exit();
}

// التحقق من وجود جميع البيانات المطلوبة
if (!isset($_GET['city_add']) || !isset($_GET['state_inp']) || !isset($_GET['cun'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$city_add = trim($_GET['city_add']);
$state_inp = trim($_GET['state_inp']);
$cun = (int)$_GET['cun'];

// التحقق من صحة البيانات
if (empty($city_add)) {
    echo "0";
    exit();
}

if ($cun <= 0) {
    echo "0";
    exit();
}

// الهروب من الأحرف الخاصة لمنع SQL Injection
$city_add_escaped = mysqli_real_escape_string($con, $city_add);
$state_inp_escaped = mysqli_real_escape_string($con, $state_inp);

// إدراج المدينة في قاعدة البيانات
$sql = "INSERT INTO city 
        SET ct_cn_id = " . $cun . ", 
            ct_name = '" . $city_add_escaped . "', 
            ct_state = '" . $state_inp_escaped . "'";

$result = mysqli_query($con, $sql);

if (!$result) {
    error_log("خطأ في إضافة المدينة: " . mysqli_error($con));
    echo "0";
    exit();
}

// إرجاع معرف المدينة المضافة
$city_id = mysqli_insert_id($con);
echo $city_id > 0 ? (string)$city_id : "1";

// إنهاء المخزن المؤقت
ob_end_flush();
?>