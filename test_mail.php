<?php
$to = "your-email@gmail.com";      // غيّر إلى بريدك الإلكتروني
$subject = "PHP Mail Test";
$message = "هذه رسالة اختبار من خادم الموقع.";
$headers = "From: webmaster@egyptmart.shop\r\n";
$headers .= "Reply-To: webmaster@egyptmart.shop\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo "✅ تم إرسال البريد بنجاح إلى MTA المحلي.";
} else {
    echo "❌ فشل إرسال البريد. يرجى التحقق من إعدادات الخادم.";
}
?>