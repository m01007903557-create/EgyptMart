<?php
/**
 * File: admin/del_states.php
 * Version: PHP 8.3
 * Description: حذف ولاية/محافظة من قاعدة البيانات
 * 
 * هذا الملف يستقبل طلب GET لحذف ولاية
 * ويعيد معرف البلد الذي تنتمي إليه الولاية المحذوفة
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
    error_log("خطأ في الاتصال بقاعدة البيانات في del_state.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف الولاية
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

// جلب معرف البلد المرتبط بالولاية قبل حذفها
$select_sql = "SELECT state_cn_id FROM states WHERE state_id = " . $hid . " LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    error_log("خطأ في جلب معلومات الولاية: " . mysqli_error($con));
    echo "0";
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo "0";
    exit();
}

$row = mysqli_fetch_array($select_result);
$state_cn_id = (int)$row['state_cn_id'];

// حذف الولاية
$delete_sql = "DELETE FROM states WHERE state_id = " . $hid;
$delete_result = mysqli_query($con, $delete_sql);

if ($delete_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        // إرجاع معرف البلد المرتبط بالولاية المحذوفة
        echo $state_cn_id;
    } else {
        echo "0";
    }
} else {
    error_log("خطأ في حذف الولاية: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>