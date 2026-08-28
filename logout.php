<?php
/**
 * File: logout.php
 * Version: PHP 8.3
 * Description: تسجيل خروج المستخدم وإزالة جميع بيانات الجلسة
 */

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "common.php";

// إزالة متغيرات الجلسة الخاصة بالمستخدم
unset($_SESSION['eml_indm']);
unset($_SESSION['uid_indm']);
unset($_SESSION['pass']);

/*########  Google Logout  #########*/
unset($_SESSION['token']);
unset($_SESSION['popup']);
unset($_SESSION['last_page']);

// إزالة متغيرات الجلسة الخاصة بـ webcast
for ($i = 0; $i < 20; $i++) {
    unset($_SESSION['id' . $i . '']);
}

// تدمير الجلسة بالكامل (اختياري - يمكن إضافته لمزيد من الأمان)
// session_destroy();

// إعادة التوجيه إلى الصفحة الرئيسية
header("Location: /");
exit();
?>