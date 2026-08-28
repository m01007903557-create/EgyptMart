<?php
/**
 * File: admin/del_cus_faq.php
 * Version: PHP 8.3
 * Description: حذف سؤال مخصص من الأسئلة الشائعة
 * 
 * هذا الملف يستقبل طلب GET لحذف سؤال مخصص
 * ويعيد معرف الفئة التي ينتمي إليها السؤال المحذوف
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
    error_log("خطأ في الاتصال بقاعدة البيانات في del_custom_faq.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف السؤال
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

// جلب معرف الفئة المرتبطة بالسؤال قبل حذفه
$select_sql = "SELECT cf_fc_id FROM custom_faq WHERE cf_id = " . $hid . " LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    error_log("خطأ في جلب معلومات السؤال: " . mysqli_error($con));
    echo "0";
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo "0";
    exit();
}

$row = mysqli_fetch_array($select_result);
$caid = (int)$row['cf_fc_id'];

// حذف السؤال
$delete_sql = "DELETE FROM custom_faq WHERE cf_id = " . $hid;
$delete_result = mysqli_query($con, $delete_sql);

if ($delete_result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        // إرجاع معرف الفئة المرتبطة بالسؤال المحذوف
        echo $caid;
    } else {
        echo "0";
    }
} else {
    error_log("خطأ في حذف السؤال: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>