<?php
/**
 * File: Verification-email.php

 * Description: إرسال رسائل التحقق بالبريد الإلكتروني للمستخدمين غير الموثقين
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من أن هذه الصفحة تسمى فقط من خلال CLI أو المستخدمين المصرح لهم
if (php_sapi_name() !== 'cli' && (!isset($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] !== '127.0.0.1')) {
    die("Access denied. This script can only be run from command line or localhost.");
}

$total_sent = 0;

// التحقق من حالة التحقق عبر البريد الإلكتروني
if (getEmailVerificationStatus() == 1) {
    $date_today = date('Y-m-d');
    
    // جلب المستخدمين الذين لم يتم التحقق منهم بعد ولم يتم إرسال بريد لهم اليوم
    $sql = "SELECT usr_id, email, fname, lname 
            FROM user 
            WHERE usr_emailVerify = '0' 
            AND (usr_emailVerify_lastDate < ? OR usr_emailVerify_lastDate IS NULL)";
    
    $stmt = mysqli_prepare($con, $sql);
    $date_start = $date_today . ' 00:00:00';
    mysqli_stmt_bind_param($stmt, 's', $date_start);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $user_count = mysqli_num_rows($result);
    echo "Found $user_count users to process.\n";
    
    while ($row = mysqli_fetch_object($result)) {
        try {
            // تخزين معرف المستخدم في الجلسة مؤقتاً
            $_SESSION['uid_indm'] = $row->usr_id;
            $_SESSION['eml_indm'] = $row->email;
            
            $fullname = $row->fname . ' ' . $row->lname;
            
            // إنشاء رابط التحقق
            $token = rand(1000, 9999) . md5((string)$row->usr_id);
            $server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $verify_link = "<a href='http://" . $server_name . "/verifyUser.php?token=" . $token . "'>Verify</a>";
            
            $to_email = stripslashes(getUserInfo($row->usr_id, 'email'));
            $subject = "! سجل منتجاتك مجانا لزيادة مبيعاتك على أكبر منصة شركات أونلاين " . get_page_settings(4);
            $from_name = get_page_settings(4);
            $from_email = get_adminemail();
            
            // تضمين قالب البريد الإلكتروني
            ob_start();
            include __DIR__ . "/email/emailVerification.php";
            $message = ob_get_clean();
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
            
            // إرسال البريد الإلكتروني
            if (mail($to_email, $subject, $message, $headers)) {
                $total_sent++;
                
                // تحديث تاريخ آخر إرسال
                $update_sql = "UPDATE user SET usr_emailVerify_lastDate = ? WHERE usr_id = ?";
                $stmt_update = mysqli_prepare($con, $update_sql);
                $current_time = date('Y-m-d H:i:s');
                mysqli_stmt_bind_param($stmt_update, 'si', $current_time, $row->usr_id);
                mysqli_stmt_execute($stmt_update);
                mysqli_stmt_close($stmt_update);
                
                echo "Email sent to user ID: {$row->usr_id} ({$row->email})\n";
                
                // الانتظار قليلاً بين الإرسالات لتجنب الحظر
                sleep(1);
            } else {
                echo "Failed to send email to user ID: {$row->usr_id} ({$row->email})\n";
            }
            
            // مسح الجلسة
            unset($_SESSION['uid_indm']);
            unset($_SESSION['eml_indm']);
            
        } catch (Exception $e) {
            error_log("Error sending verification email to user {$row->usr_id}: " . $e->getMessage());
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
    
    mysqli_stmt_close($stmt);
}

echo "Total emails sent: $total_sent\n";
?>