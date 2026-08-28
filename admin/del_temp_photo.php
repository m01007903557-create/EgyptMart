<?php
/**
 * File: admin/del_temp_photo.php

 * Version: PHP 8.3
 * Description: حذف صورة مميزة من قاعدة البيانات ومن المجلد
 * 
 * هذا الملف يستقبل طلب GET لحذف صورة مميزة
 * ويعيد عدد الصور المتبقية لنفس العنصر
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
    error_log("خطأ في الاتصال بقاعدة البيانات في del_feature_image.php");
    echo "0";
    exit();
}

// التحقق من وجود معرف الصورة
if (!isset($_GET['pi']) || empty($_GET['pi'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$pi = (int)$_GET['pi'];

// التحقق من صحة القيمة
if ($pi <= 0) {
    echo "0";
    exit();
}

// جلب معلومات الصورة قبل حذفها
$select_sql = "SELECT fi_f_id, fi_image FROM feature_images WHERE fi_id = " . $pi . " LIMIT 1";
$select_result = mysqli_query($con, $select_sql);

if (!$select_result) {
    error_log("خطأ في جلب معلومات الصورة: " . mysqli_error($con));
    echo "0";
    exit();
}

if (mysqli_num_rows($select_result) == 0) {
    echo "0";
    exit();
}

$row = mysqli_fetch_object($select_result);
$prop_id = (int)$row->fi_f_id;
$prop_img = $row->fi_image;

// حذف الصورة من قاعدة البيانات
$delete_sql = "DELETE FROM feature_images WHERE fi_id = " . $pi;
$delete_result = mysqli_query($con, $delete_sql);

if (!$delete_result) {
    error_log("خطأ في حذف الصورة من قاعدة البيانات: " . mysqli_error($con));
    echo "0";
    exit();
}

// حذف الملف الفعلي من المجلد
if (!empty($prop_img)) {
    $image_path = '../upload/feature/' . $prop_img;
    if (file_exists($image_path)) {
        if (!unlink($image_path)) {
            error_log("تحذير: فشل في حذف ملف الصورة: " . $image_path);
        }
    } else {
        error_log("تحذير: ملف الصورة غير موجود: " . $image_path);
    }
}

// حساب عدد الصور المتبقية لنفس العنصر
$count_sql = "SELECT COUNT(*) as count FROM feature_images WHERE fi_f_id = " . $prop_id;
$count_result = mysqli_query($con, $count_sql);

if ($count_result && mysqli_num_rows($count_result) > 0) {
    $count_row = mysqli_fetch_object($count_result);
    echo $count_row->count;
} else {
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>