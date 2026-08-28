<?php
// mail.php - نسخة PHP 8.3 مع تحسينات
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// التحقق من وجود مكتبات PEAR Mail
if (!@include_once('Mail.php')) {
    die('مكتبة PEAR Mail غير موجودة');
}
if (!@include_once('Mail/Mime.php')) {
    die('مكتبة Mail_Mime غير موجودة');
}

/**
 * إرسال بريد إلكتروني مع مرفقات
 * 
 * @param string $to البريد الإلكتروني للمستلم
 * @param string $from البريد الإلكتروني للمرسل
 * @param string $subject عنوان الرسالة
 * @param string $text نص الرسالة
 * @param string|array|null $attachment مسار الملف المرفق أو مصفوفة من المسارات
 * @param array $additional_headers رؤوس إضافية (اختياري)
 * @return bool|PEAR_Error
 */
function sendMailWithAttachment(
    string $to, 
    string $from, 
    string $subject, 
    string $text, 
    $attachment = null,
    array $additional_headers = []
) {
    try {
        // إنشاء كائن Mail_Mime
        $message = new Mail_mime();
        
        // تعيين نص الرسالة
        $message->setTXTBody($text);
        
        // إضافة المرفقات
        if (!empty($attachment)) {
            if (is_array($attachment)) {
                foreach ($attachment as $file) {
                    if (file_exists($file) && is_file($file)) {
                        $message->addAttachment($file);
                    }
                }
            } elseif (is_string($attachment) && file_exists($attachment) && is_file($attachment)) {
                $message->addAttachment($attachment);
            }
        }
        
        // تجهيز الرؤوس
        $headers = [
            'From' => $from,
            'Subject' => $subject,
            'Reply-To' => $from,
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        // دمج الرؤوس الإضافية
        if (!empty($additional_headers)) {
            $headers = array_merge($headers, $additional_headers);
        }
        
        // الحصول على الجسم والرؤوس
        $body = $message->get();
        $final_headers = $message->headers($headers);
        
        // إنشاء كائن Mail
        $mail = Mail::factory('mail');
        
        // إرسال البريد
        $result = $mail->send($to, $final_headers, $body);
        
        return $result;
        
    } catch (Exception $e) {
        error_log('خطأ في إرسال البريد: ' . $e->getMessage());
        return false;
    }
}

/**
 * إرسال بريد إلكتروني بسيط (بدون مرفقات)
 * 
 * @param string $to البريد الإلكتروني للمستلم
 * @param string $from البريد الإلكتروني للمرسل
 * @param string $subject عنوان الرسالة
 * @param string $text نص الرسالة
 * @param array $additional_headers رؤوس إضافية (اختياري)
 * @return bool|PEAR_Error
 */
function sendSimpleMail(
    string $to, 
    string $from, 
    string $subject, 
    string $text,
    array $additional_headers = []
) {
    return sendMailWithAttachment($to, $from, $subject, $text, null, $additional_headers);
}

/**
 * إرسال بريد إلكتروني HTML
 * 
 * @param string $to البريد الإلكتروني للمستلم
 * @param string $from البريد الإلكتروني للمرسل
 * @param string $subject عنوان الرسالة
 * @param string $html نص HTML للرسالة
 * @param string|null $text نص بديل (اختياري)
 * @param string|array|null $attachment مسار الملف المرفق
 * @return bool|PEAR_Error
 */
function sendHtmlMail(
    string $to, 
    string $from, 
    string $subject, 
    string $html, 
    ?string $text = null,
    $attachment = null
) {
    try {
        $message = new Mail_mime();
        
        // تعيين نص HTML
        $message->setHTMLBody($html);
        
        // تعيين نص بديل إذا وجد
        if (!empty($text)) {
            $message->setTXTBody($text);
        }
        
        // إضافة المرفقات
        if (!empty($attachment)) {
            if (is_array($attachment)) {
                foreach ($attachment as $file) {
                    if (file_exists($file) && is_file($file)) {
                        $message->addAttachment($file);
                    }
                }
            } elseif (is_string($attachment) && file_exists($attachment) && is_file($attachment)) {
                $message->addAttachment($attachment);
            }
        }
        
        // تجهيز الرؤوس
        $headers = [
            'From' => $from,
            'Subject' => $subject,
            'Reply-To' => $from,
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/html; charset=UTF-8'
        ];
        
        // الحصول على الجسم والرؤوس
        $body = $message->get();
        $final_headers = $message->headers($headers);
        
        // إنشاء كائن Mail
        $mail = Mail::factory('mail');
        
        // إرسال البريد
        return $mail->send($to, $final_headers, $body);
        
    } catch (Exception $e) {
        error_log('خطأ في إرسال البريد: ' . $e->getMessage());
        return false;
    }
}

/**
 * التحقق من صحة البريد الإلكتروني
 * 
 * @param string $email البريد الإلكتروني للتحقق
 * @return bool
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * تسجيل محاولات إرسال البريد للتصحيح
 * 
 * @param string $to المستلم
 * @param string $subject الموضوع
 * @param bool $status حالة الإرسال
 * @return void
 */
function logMailAttempt(string $to, string $subject, bool $status): void {
    $log_entry = date('Y-m-d H:i:s') . " - To: {$to}, Subject: {$subject}, Status: " . ($status ? 'SUCCESS' : 'FAILED') . PHP_EOL;
    error_log($log_entry, 3, __DIR__ . '/mail_log.txt');
}

// مثال للاستخدام:
/*
// إرسال بريد بسيط
$to = "recipient@example.com";
$from = "sender@example.com";
$subject = "Test Email";
$body = "This is a test email.";

if (sendSimpleMail($to, $from, $subject, $body)) {
    echo "Email sent successfully";
} else {
    echo "Failed to send email";
}

// إرسال بريد مع مرفق
$attachment = "/path/to/file.pdf";
if (sendMailWithAttachment($to, $from, $subject, $body, $attachment)) {
    echo "Email with attachment sent successfully";
}

// إرسال بريد HTML
$html = "<h1>Welcome!</h1><p>This is an HTML email.</p>";
$text = "Welcome! This is a plain text version.";
if (sendHtmlMail($to, $from, $subject, $html, $text)) {
    echo "HTML email sent successfully";
}
*/
?>