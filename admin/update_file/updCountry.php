<?php
/**
 * File: admin/updCountry.php
 * Version: PHP 8.3
 * Description: تحديث بيانات بلد موجود في قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب AJAX لتحديث معلومات البلد
 * (الاسم، العملة، رمز الهاتف) بدون تغيير العلم
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
    error_log("خطأ في الاتصال بقاعدة البيانات في country-edit.php");
    echo "0";
    exit();
}

// التحقق من وجود جميع البيانات المطلوبة
if (!isset($_POST['cn_id']) || !isset($_POST['cn_name']) || !isset($_POST['cn_currency']) || !isset($_POST['cn_ph'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$cn_id = (int)trim($_POST['cn_id']);
$cn_name = trim($_POST['cn_name']);
$cn_currency = trim($_POST['cn_currency']);
$cn_ph = trim($_POST['cn_ph']);

// التحقق من صحة القيم
if ($cn_id <= 0) {
    echo "0";
    exit();
}

if (empty($cn_name) || empty($cn_currency) || empty($cn_ph)) {
    echo "0";
    exit();
}

// الهروب من الأحرف الخاصة لمنع SQL Injection
$cn_name_escaped = mysqli_real_escape_string($con, $cn_name);
$cn_currency_escaped = mysqli_real_escape_string($con, $cn_currency);
$cn_ph_escaped = mysqli_real_escape_string($con, $cn_ph);

// تحديث البيانات في قاعدة البيانات
$sql = "UPDATE country
        SET
            cn_name = '" . $cn_name_escaped . "',
            cn_currency = '" . $cn_currency_escaped . "',
            cn_ph = '" . $cn_ph_escaped . "'
        WHERE cn_id = " . $cn_id;

$result = mysqli_query($con, $sql);

if (!$result) {
    error_log("خطأ في تحديث بيانات البلد: " . mysqli_error($con));
    echo "0";
    exit();
}

// التحقق من عدد الصفوف المتأثرة
if (mysqli_affected_rows($con) > 0) {
    echo "1"; // نجاح التحديث
} else {
    // قد يعني أن البيانات لم تتغير (نفس القيم)
    echo "1"; // نعتبرها نجاحاً أيضاً
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>