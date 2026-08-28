<?php
/**
 * File: admin/del_city.php
 * Version: PHP 8.3
 * Description: حذف مدينة من قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب GET لحذف مدينة
 * ويعيد معرف البلد الذي تنتمي إليه المدينة المحذوفة
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
    error_log("خطأ في الاتصال بقاعدة البيانات في del_city.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف المدينة
if (!isset($_GET['hid']) || empty($_GET['hid'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$hid = (int)$_GET['hid'];

// التحقق من صحة القيمة
if ($hid <= 0) {
    echo "0";
    exit();
}

// جلب معرف البلد المرتبط بالمدينة قبل حذفها
$select_sql = "SELECT ct_cn_id FROM city WHERE ct_id = " . $hid . " LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    error_log("خطأ في جلب معلومات المدينة: " . mysqli_error($con));
    echo "0";
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo "0";
    exit();
}

$row = mysqli_fetch_array($select_result);
$ct_cn_id = (int)$row['ct_cn_id'];

// حذف المدينة
$delete_sql = "DELETE FROM city WHERE ct_id = " . $hid;
$delete_result = mysqli_query($con, $delete_sql);

if ($delete_result) {
    // إرجاع معرف البلد المرتبط بالمدينة المحذوفة
    echo $ct_cn_id;
} else {
    error_log("خطأ في حذف المدينة: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>