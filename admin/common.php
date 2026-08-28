<?php
/**
 * File: admin/common.php
 * Version: PHP 8.3
 * Description: الملف المشترك لصفحات الإدارة - يحتوي على الإعدادات الأساسية وتضمين المكتبات
 * 
 * هذا الملف يتم تضمينه في جميع صفحات الإدارة ويتضمن
 * المكتبات والدوال الأساسية اللازمة لعمل لوحة التحكم
 */

// بدء المخزن المؤقت (اختياري - يفضل تفعيله في كل صفحة على حدة)
// ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// إعداد تقارير الأخطاء (يمكن تعديلها حسب البيئة)
if ($_SERVER['SERVER_NAME'] == 'localhost' || $_SERVER['SERVER_NAME'] == '127.0.0.1') {
    // بيئة التطوير - عرض جميع الأخطاء
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // بيئة الإنتاج - إخفاء الأخطاء
    error_reporting(0);
    ini_set('display_errors', 0);
}

// تعريف الثوابت (إذا لزم الأمر)
/*
if ($_SERVER['SERVER_NAME'] == 'phpserver') {
    define('SRNAME', 'http://' . $_SERVER['SERVER_NAME'] . '/cupcake/');
} else if ($_SERVER['SERVER_NAME'] == '64.191.66.18') {
    define('SRNAME', 'http://' . $_SERVER['SERVER_NAME'] . '/cupcake/');
} else {
    define('SRNAME', 'http://' . $_SERVER['SERVER_NAME'] . '/');
}
*/

// تضمين الملفات الأساسية
include 'lib/connect.php';      // اتصال قاعدة البيانات
include 'lib/function.php';      // الدوال العامة
include 'lib/validation.php';    // دوال التحقق من الصحة
include 'lib/pagination.php';    // دوال الترقيم

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    error_log("خطأ في الاتصال بقاعدة البيانات في admin/common.php");
    die('خطأ في الاتصال بقاعدة البيانات');
}

// متغير رمز الأمان لنموذج الاتصال (إذا كان مستخدماً)
$c_form_scode = $_SESSION['security_code_contact_form'] ?? '';

// ملاحظة: لا نغلق المخزن المؤقت هنا لأن هذا الملف يتم تضمينه في صفحات أخرى
// ob_end_flush(); // لا تستخدم هنا
?>