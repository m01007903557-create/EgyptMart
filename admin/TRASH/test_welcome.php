<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>🔍 اختبار ملف welcome.php</h1>";

// محاكاة جلسة مستخدم مسجل الدخول
session_start();
$_SESSION['ad_id_indm'] = 1;
$_SESSION['ad_username_indm'] = 'ARABYOS';
$_SESSION['admin_logged_in'] = true;

echo "✅ تم تعيين الجلسة للاختبار<br><br>";

// محاولة تضمين common.php
require_once dirname(__DIR__) . "/common.php";
echo "✅ common.php تم تضمينه<br>";

// محاولة تضمين pagination.php
require_once dirname(__DIR__) . "/lib/pagination.php";
echo "✅ pagination.php تم تضمينه<br>";

// محاولة استدعاء checkUserLogin
if (function_exists('checkUserLogin')) {
    echo "✅ checkUserLogin موجودة<br>";
    try {
        checkUserLogin();
        echo "✅ checkUserLogin تم تنفيذها بنجاح<br>";
    } catch (Exception $e) {
        echo "❌ خطأ في checkUserLogin: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ checkUserLogin غير موجودة<br>";
}

// محاولة إنشاء كائن Pagination
try {
    $pagination = new Pagination();
    echo "✅ تم إنشاء كائن Pagination<br>";
} catch (Exception $e) {
    echo "❌ خطأ في إنشاء Pagination: " . $e->getMessage() . "<br>";
}

echo "<br>✅ الاختبار اكتمل";
?>