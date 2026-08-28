<?php
/**
 * File: sendVerifyLink.php

 * Description: إرسال بريد إلكتروني للتحقق من حساب المستخدم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit;
}

$user_id = (int)$_SESSION['uid_indm'];

// إنشاء رابط التحقق
$token = rand(1000, 9999) . md5((string)$user_id);
$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
$verify_link = "http://" . $server_name . "/verifyUser.php?token=" . $token;

// الحصول على معلومات المستخدم
$to_email = stripslashes(user_info($user_id, 'email'));
$user_name = user_info($user_id, 'name_prefix') . ' ' . user_info($user_id, 'fname') . ' ' . user_info($user_id, 'lname');
$user_name = htmlspecialchars(trim($user_name), ENT_QUOTES, 'UTF-8');

// إعدادات البريد
$subject = "تحقيق حسابى على إيجبت مارت";
$from_name = get_page_settings(4);
$from_email = get_adminemail();

// تخزين معرف المستخدم في الجلسة للتحقق
$_SESSION['email_verify_for'] = $user_id;

// تضمين قالب البريد الإلكتروني
ob_start();
include __DIR__ . "/email/emailVerification.php";
$message = ob_get_clean();

// إعداد رؤوس البريد
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";

// إرسال البريد الإلكتروني
if (sendSMTPMail($to_email, $subject, $message, $headers)) {
    header("Location: my-dashboard.php?verifylinksend=1");
} else {
    // في حالة فشل الإرسال، يمكن التوجيه إلى صفحة خطأ
    header("Location: my-dashboard.php?verifylinksend=0");
}
exit;
?>