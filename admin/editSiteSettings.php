<?php
/**
 * File: admin/editSiteSettings.php

 * Version: PHP 8.3
 * Description: تغيير حالة إعدادات الموقع (تفعيل/تعطيل)
 * 
 * هذا الملف يستقبل طلب POST لتحديث قيمة إعداد معين في الموقع
 * ويقوم بتبديل القيمة بين 0 و 1
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
    error_log("خطأ في الاتصال بقاعدة البيانات في change_site_settings.php");
    echo "0";
    exit();
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['id']) || !isset($_POST['val'])) {
    echo "0";
    exit();
}

// تنظيف المدخلات
$st_id = (int)$_POST['id'];
$val = (int)$_POST['val'];

// التحقق من صحة القيم
if ($st_id <= 0) {
    echo "0";
    exit();
}

// تحديد القيمة الجديدة (تبديل: 1 -> 0, 0 -> 1)
$st_value = ($val == 1) ? 0 : 1;

// تحديث إعداد الموقع
$sql = "UPDATE site_settings
        SET
            st_value = '" . mysqli_real_escape_string($con, $st_value) . "',
            st_updated_date = NOW()
        WHERE
            st_id = " . $st_id;

$result = mysqli_query($con, $sql);

if ($result) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_affected_rows($con) > 0) {
        echo "1"; // نجاح
    } else {
        echo "0"; // لم يتم تحديث أي صف
    }
} else {
    error_log("خطأ في تحديث إعدادات الموقع: " . mysqli_error($con));
    echo "0";
}

// إنهاء المخزن المؤقت
ob_end_flush();
?>