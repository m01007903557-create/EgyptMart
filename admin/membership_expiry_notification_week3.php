<?php
/**
 * ملف إرسال إشعارات انتهاء صلاحية الخطط - الإشعار الثاني (14 يوماً)
 * 
 * @filename    membership_expiry_notification_week3.php

 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن إرسال الإشعار الثاني للمستخدمين
 *              قبل 14 يوماً من انتهاء صلاحية خطط العضوية
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت والجلسة
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// تمكين عرض الأخطاء للتطوير فقط (يجب تعطيله في الإنتاج)
// ini_set("display_errors", 1);
// error_reporting(E_ALL);

/**
 * دالة الحصول على إعدادات البريد الإلكتروني
 * @param int $setting_id
 * @return string
 */
function get_page_settings($setting_id) {
    global $con;
    
    $setting_id = filter_var($setting_id, FILTER_VALIDATE_INT);
    if (!$setting_id) {
        return '';
    }
    
    $sql = "SELECT setting_value FROM settings WHERE setting_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "i", $setting_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    return $row['setting_value'] ?? '';
}

/**
 * دالة الحصول على البريد الإلكتروني للمشرف
 * @return string
 */
function get_adminemail() {
    global $con;
    
    $sql = "SELECT admin_email FROM admin WHERE admin_role = 'super' LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    
    return $row['admin_email'] ?? 'admin@example.com';
}

/**
 * دالة الحصول على معرف المشرف
 * @return int
 */
function getAdminUserId() {
    global $con;
    
    $sql = "SELECT admin_id FROM admin WHERE admin_role = 'super' LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    
    return $row['admin_id'] ?? 1;
}

/**
 * دالة إرسال البريد الإلكتروني
 * @param string $to
 * @param string $subject
 * @param string $message
 * @param string $from_name
 * @param string $from_email
 * @return bool
 */
function sendEmail($to, $subject, $message, $from_name, $from_email) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    return mail($to, $subject, $message, $headers);
}

/**
 * دالة تسجيل الإشعارات المرسلة
 * @param int $b_id
 * @param int $usr_id
 * @param string $subject
 * @param string $message
 * @param string $type
 */
function logNotification($b_id, $usr_id, $subject, $message, $type = 'email') {
    global $con;
    
    $sql = "INSERT INTO notification_log 
            (notification_type, notification_subject, notification_message, 
             notification_user_id, notification_business_id, notification_date) 
            VALUES (?, ?, ?, ?, ?, NOW())";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, "sssii", $type, $subject, $message, $usr_id, $b_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// التحقق من تشغيل الملف عبر CRON فقط
if (php_sapi_name() !== 'cli' && !isset($_GET['cron_key'])) {
    die('Access denied. This script can only be run via CLI or with valid cron key.');
}

// إذا كان هناك مفتاح CRON، تحقق منه
if (isset($_GET['cron_key'])) {
    $cron_key = get_page_settings(100); // افترض أن المفتاح مخزن في الإعدادات
    if ($_GET['cron_key'] !== $cron_key) {
        die('Invalid cron key');
    }
}

// استعلام لجلب الخطط التي تحتاج إشعار ثاني (قبل 14 يوم)
$sql = "SELECT pm.*, b.*, u.*, sp.* 
        FROM plan_member_id pm 
        JOIN business_profile b ON pm.b_id = b.bnsprof_id 
        JOIN user u ON u.usr_id = b.bnsprof_uid 
        JOIN smembership_plan sp ON sp.mp_id = pm.p_id 
        WHERE pm.notification_email_week3_status = 0 
          AND pm.expiry_date IS NOT NULL 
          AND pm.expiry_date > 0";

$result = mysqli_query($con, $sql);

if (!$result) {
    // تسجيل الخطأ في ملف السجل
    error_log("خطأ في استعلام الخطط (الإشعار الثاني): " . mysqli_error($con));
    die("Database error occurred");
}

$sent_count = 0;
$error_count = 0;
$current_time = time();

while ($row = mysqli_fetch_object($result)) {
    
    // حساب تاريخ انتهاء الصلاحية
    $expiry_date = $row->expiry_date;
    $days_difference = floor(($expiry_date - $current_time) / (60 * 60 * 24));
    
    // التحقق من أن تاريخ اليوم يسبق تاريخ انتهاء الصلاحية بـ 14 يوم بالضبط
    // أو ضمن نطاق 14-15 يوم (للتسامح مع اختلافات التوقيت)
    if ($days_difference >= 13 && $days_difference <= 15) {
        
        try {
            // تجهيز البيانات
            $fullname = trim($row->name_prefix ?? '') . ' ' . 
                       trim($row->fname ?? '') . ' ' . 
                       trim($row->lname ?? '');
            $fullname = htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8');
            
            $expiry_date_formatted = date("Y-m-d", $row->expiry_date);
            $start_date_formatted = date("Y-m-d", $row->start_date);
            $plan_name = htmlspecialchars($row->mst_name ?? '', ENT_QUOTES, 'UTF-8');
            
            // الحصول على تفاصيل الفاتورة
            $billing_sql = "SELECT * FROM billing_history 
                           WHERE bh_type = 5 AND bh_usr_id = ? 
                           ORDER BY bh_updated_date DESC LIMIT 1";
            
            $billing_stmt = mysqli_prepare($con, $billing_sql);
            mysqli_stmt_bind_param($billing_stmt, "i", $row->usr_id);
            mysqli_stmt_execute($billing_stmt);
            $billing_result = mysqli_stmt_get_result($billing_stmt);
            $billing_detail = mysqli_fetch_object($billing_result);
            mysqli_stmt_close($billing_stmt);
            
            // إعداد البريد الإلكتروني
            $subject = "تذكير: خطتك " . $plan_name . " ستنتهي قريباً في " . $expiry_date_formatted;
            
            // تضمين تصميم البريد الإلكتروني
            $message1 = '';
            if (file_exists("email/plan_expiry_notification.php")) {
                ob_start();
                include "email/plan_expiry_notification.php";
                $message1 = ob_get_clean();
            } else {
                // قالب افتراضي إذا لم يكن الملف موجوداً
                $message1 = "<html><body style='font-family: Arial, sans-serif; direction: ltr;'>";
                $message1 .= "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>";
                $message1 .= "<h2 style='color: #e74c3c;'>تذكير بانتهاء الخطة</h2>";
                $message1 .= "<p>عزيزي <strong>$fullname</strong>،</p>";
                $message1 .= "<p>نود تذكيرك بأن خطتك <strong>$plan_name</strong> ستنتهي في تاريخ <strong>$expiry_date_formatted</strong>.</p>";
                $message1 .= "<p>لم يتبق سوى <strong style='color: #e74c3c;'>14 يوماً</strong> على انتهاء صلاحية خطتك.</p>";
                $message1 .= "<p>يرجى تجديد عضويتك لتستمر في الاستفادة من خدماتنا المتميزة.</p>";
                $message1 .= "<p>للتجديد، يرجى <a href='https://egyptmart.online/membership-renewal.php' style='background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;'>الضغط هنا</a></p>";
                $message1 .= "<p>شكراً لاستخدامك خدماتنا.</p>";
                $message1 .= "<hr>";
                $message1 .= "<p style='color: #777; font-size: 12px;'>هذا البريد إلكتروني تلقائي، يرجى عدم الرد عليه.</p>";
                $message1 .= "</div>";
                $message1 .= "</body></html>";
            }
            
            $from_name = get_page_settings(4);
            $from_email = get_adminemail();
            $to = $row->email;
            
            // إرسال البريد الإلكتروني
            if (sendEmail($to, $subject, $message1, $from_name, $from_email)) {
                
                // تحديث حالة الإشعار
                $update_sql = "UPDATE plan_member_id 
                              SET notification_email_week3_status = 1,
                                  notification_email_week3_date = NOW()
                              WHERE b_id = ?";
                
                $update_stmt = mysqli_prepare($con, $update_sql);
                mysqli_stmt_bind_param($update_stmt, "i", $row->b_id);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);
                
                // تسجيل الإشعار
                logNotification($row->b_id, $row->usr_id, $subject, $message1, 'email_week3');
                
                // إرسال رسالة داخلية
                $message_text = 'تذكير: خطتك ' . str_replace(['Plan', 'plan'], [''], $plan_name) . 
                               ' ستنتهي في ' . $expiry_date_formatted . 
                               '. يتبقى 14 يوماً فقط. يرجى تجديد عضويتك.';
                
                $message_sql = "INSERT INTO message 
                               (msg_from, msg_to, msg_subject, msg_message, 
                                msg_entity, msg_entity_id, msg_date) 
                               VALUES (?, ?, ?, ?, 'membership_plan', ?, NOW())";
                
                $message_stmt = mysqli_prepare($con, $message_sql);
                $admin_id = getAdminUserId();
                mysqli_stmt_bind_param($message_stmt, "iissi", 
                    $admin_id, 
                    $row->usr_id, 
                    $subject, 
                    $message_text, 
                    $row->b_id
                );
                
                if (mysqli_stmt_execute($message_stmt)) {
                    logNotification($row->b_id, $row->usr_id, $subject, $message_text, 'message_week3');
                }
                
                mysqli_stmt_close($message_stmt);
                
                $sent_count++;
                
                // تسجيل نجاح الإرسال
                error_log("تم إرسال الإشعار الثاني للمستخدم: " . $row->email . " - الخطة: " . $plan_name);
                
            } else {
                error_log("فشل إرسال البريد الإلكتروني للمستخدم (الإشعار الثاني): " . $row->email);
                $error_count++;
            }
            
        } catch (Exception $e) {
            error_log("استثناء في معالجة المستخدم (الإشعار الثاني): " . $e->getMessage());
            $error_count++;
        }
    }
}

// تسجيل النتائج في ملف السجل
$log_message = date('Y-m-d H:i:s') . " - الإشعار الثاني: تم إرسال $sent_count إشعار، فشل $error_count إشعار" . PHP_EOL;
file_put_contents('cron_log.txt', $log_message, FILE_APPEND);

// إخراج النتيجة (للتشغيل اليدوي)
if (php_sapi_name() !== 'cli') {
    echo "<h2>نتائج تشغيل الإشعارات - الإشعار الثاني (14 يوم)</h2>";
    echo "<p>تم إرسال: $sent_count إشعار</p>";
    echo "<p>فشل: $error_count إشعار</p>";
    
    if ($sent_count > 0 || $error_count > 0) {
        echo "<p>تم تسجيل النتائج في ملف cron_log.txt</p>";
    }
} else {
    echo "الإشعار الثاني (14 يوم): تم إرسال $sent_count إشعار، فشل $error_count إشعار\n";
}

// إغلاق اتصال قاعدة البيانات
mysqli_close($con);

// إنهاء المخزن المؤقت
ob_end_flush();

/**
 * تحديث جدول notification_log لإضافة نوع الإشعار الثاني
 * 
ALTER TABLE `notification_log` 
MODIFY COLUMN `notification_type` varchar(50) NOT NULL;
 */

?>