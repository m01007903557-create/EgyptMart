<?php
/**
 * File: admin/ajax-file/checkOldCountry.php
 * Version: PHP 8.3
 * Description: التحقق من عدم وجود بلد مكرر عند التعديل
 * 
 * هذا الملف يستقبل طلب AJAX للتحقق مما إذا كان اسم البلد،
 * رمز البلد، العملة، أو رمز الهاتف موجوداً مسبقاً في قاعدة البيانات
 * مع استثناء البلد الحالي (أثناء التعديل)
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
    error_log("خطأ في الاتصال بقاعدة البيانات في check-country-duplicate-edit.php");
    echo "1"; // إرجاع 1 في حالة الخطأ (افتراض وجود تكرار)
    exit();
}

// التحقق من وجود جميع البيانات المطلوبة
if (!isset($_POST['cn_id']) || !isset($_POST['cn_name']) || !isset($_POST['cn_code']) || 
    !isset($_POST['cn_currency']) || !isset($_POST['cn_ph'])) {
    echo "1"; // إرجاع 1 في حالة عدم وجود بيانات
    exit();
}

// تنظيف المدخلات
$cn_id = (int)trim($_POST['cn_id']);
$cn_name = trim($_POST['cn_name']);
$cn_code = trim($_POST['cn_code']);
$cn_currency = trim($_POST['cn_currency']);
$cn_ph = trim($_POST['cn_ph']);

// التحقق من صحة المعرف
if ($cn_id <= 0) {
    echo "1"; // إرجاع 1 في حالة معرف غير صالح
    exit();
}

// التحقق من عدم فراغ البيانات الأساسية
if (empty($cn_name) || empty($cn_code) || empty($cn_currency) || empty($cn_ph)) {
    echo "1"; // إرجاع 1 في حالة بيانات فارغة
    exit();
}

// الهروب من الأحرف الخاصة لمنع SQL Injection
$cn_name_escaped = mysqli_real_escape_string($con, $cn_name);
$cn_code_escaped = mysqli_real_escape_string($con, $cn_code);
$cn_currency_escaped = mysqli_real_escape_string($con, $cn_currency);
$cn_ph_escaped = mysqli_real_escape_string($con, $cn_ph);

// التحقق من وجود اسم البلد (باستثناء البلد الحالي)
$sql_nm = "SELECT * FROM country WHERE cn_name LIKE '{$cn_name_escaped}' AND cn_id != {$cn_id} LIMIT 1";
$res_nm = mysqli_query($con, $sql_nm);

// التحقق من وجود رمز البلد (باستثناء البلد الحالي)
$sql_cd = "SELECT * FROM country WHERE cn_code LIKE '{$cn_code_escaped}' AND cn_id != {$cn_id} LIMIT 1";
$res_cd = mysqli_query($con, $sql_cd);

// التحقق من وجود العملة (باستثناء البلد الحالي)
$sql_cr = "SELECT * FROM country WHERE cn_currency LIKE '{$cn_currency_escaped}' AND cn_id != {$cn_id} LIMIT 1";
$res_cr = mysqli_query($con, $sql_cr);

// التحقق من وجود رمز الهاتف (باستثناء البلد الحالي)
$sql_ph = "SELECT * FROM country WHERE cn_ph LIKE '{$cn_ph_escaped}' AND cn_id != {$cn_id} LIMIT 1";
$res_ph = mysqli_query($con, $sql_ph);

// التحقق من نجاح الاستعلامات
$has_duplicate = false;

if ($res_nm && mysqli_num_rows($res_nm) > 0) {
    $has_duplicate = true;
} elseif ($res_cd && mysqli_num_rows($res_cd) > 0) {
    $has_duplicate = true;
} elseif ($res_cr && mysqli_num_rows($res_cr) > 0) {
    $has_duplicate = true;
} elseif ($res_ph && mysqli_num_rows($res_ph) > 0) {
    $has_duplicate = true;
}

// إرجاع النتيجة
echo $has_duplicate ? "1" : "0";

// إنهاء المخزن المؤقت
ob_end_flush();
?>