<?php
/**
 * File: ajax/forgotPassword.php
 * Description: معالجة طلب استعادة كلمة المرور وإرسال رابط التغيير
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود البريد الإلكتروني
if (!isset($_POST['email'])) {
    http_response_code(400);
    echo "من فضلك أدخل عنوانك البريدى|0";
    exit;
}

$email = trim($_POST['email']);
$msg = "";
$valid = 1;

// التحقق من صحة البريد الإلكتروني
if (empty($email)) {
    $msg = "من فضلك أدخل عنوانك البريدى";
    $valid = 0;
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = "صيغة البريد الإلكتروني غير صحيحة";
    $valid = 0;
} else {
    global $con;
    
    // التحقق من وجود مستخدم آخر بنفس البريد (للتأكد من عدم وجود تكرار)
    $check_other_sql = "SELECT usr_id FROM user WHERE email != ? AND status = 1 LIMIT 1";
    $stmt_other = mysqli_prepare($con, $check_other_sql);
    mysqli_stmt_bind_param($stmt_other, 's', $email);
    mysqli_stmt_execute($stmt_other);
    $result_other = mysqli_stmt_get_result($stmt_other);
    $other_exists = mysqli_num_rows($result_other) > 0;
    mysqli_stmt_close($stmt_other);
    
    // التحقق من وجود المستخدم بهذا البريد
    $user_check_sql = "SELECT usr_id, name_prefix, fname, lname FROM user WHERE email = ? AND status = 1 LIMIT 1";
    $stmt_user = mysqli_prepare($con, $user_check_sql);
    mysqli_stmt_bind_param($stmt_user, 's', $email);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    
    if (mysqli_num_rows($result_user) == 0) {
        $msg = "No user exists with this Email Id";
        $valid = 0;
    } else {
        $rowchk = mysqli_fetch_object($result_user);
        
        // إنشاء كلمة مرور جديدة عشوائية
        $newpass = (string)random_int(100000, 999999);
        $md5_pass = md5($newpass);
        
        // إنشاء رابط تغيير كلمة المرور
        $password_link = "https://egyptmart.online/login.php?email=" . urlencode($email) . 
                        "&pass=" . urlencode($newpass) . 
                        "&changepass=1&login=1&redirect=" . 
                        urlencode("https://egyptmart.online/change-password.php?current_pass=" . urlencode($newpass));
        
        // تحديث قاعدة البيانات
        $update_sql = "UPDATE user SET pass = ?, password_email = '1', password_link = ? WHERE email = ?";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'sss', $md5_pass, $password_link, $email);
        
        if (mysqli_stmt_execute($stmt_update)) {
            $valid = 1;
            $msg = '<font color="#009900" font-size="18px">.. تم إرسال تفاصيل الدخول الى بريدك رجاء الدخول وتعيين كلمة مرور جديدة</font>';
            
            // تجهيز بيانات المستخدم للبريد الإلكتروني
            $fullname = htmlspecialchars(
                trim(($rowchk->name_prefix ?? '') . ' ' . ($rowchk->fname ?? '') . ' ' . ($rowchk->lname ?? '')),
                ENT_QUOTES, 
                'UTF-8'
            );
            
            // إرسال البريد الإلكتروني
            $to = $email;
            $subject = "تغيير كلمة المرور في " . get_page_settings(4);
            $from_email = get_adminemail();
            $from_name = get_page_settings(4);
            
            $message = " السادة : " . "&nbsp;" . $fullname . "<br>";
            $message .= "  إستجابة لطلبك تغيير كلمة المرور الخاصة بك فهذه هى كلمة المرور الجديدة" . "<br>";
            $message .= "<div style='max-width:302px;padding:7px;border:1px solid #e2e0e0;background:#f4f3f3;color:#444444'>";
            $message .= "<h1 style='font-family:Arial,Helvetica,sans-serif;font-size:16px;margin:0;color:#2f66a7'>" . "&nbsp;" . get_page_settings(4) . "&nbsp;" . ": إيميل الدخول الى </h1>";
            $message .= "<div style='font-size:13px;margin-top:7px;margin-bottom:10px;line-height:20px'><strong> العنوان البريدى: </strong>";
            $message .= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "<br>";
            $message .= "</div>";
            $message .= "<a style='display:inline-block;border:1px solid #3079ed;padding:6px;text-align:center;color:#fff;font-size:16px;border-radius:2px;background-color:#4d90fe;background-image:linear-gradient(top,#4d90fe,#4787ed);text-decoration:none' alt='change your password' title='change your password' href='" . htmlspecialchars($password_link, ENT_QUOTES, 'UTF-8') . "' target='_blank'>";
            $message .= "غير كلمة المرور </a>";
            $message .= "</div>";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
            
            // إرسال البريد الإلكتروني
            $mail_sent = sendSMTPMail($to, $subject, $message, $headers);
            
            if (!$mail_sent) {
                error_log("Failed to send password reset email to: " . $email);
            }
        } else {
            error_log("Password reset error: " . mysqli_error($con) . " | Email: " . $email);
            $msg = "حدث خطأ في تحديث كلمة المرور";
            $valid = 0;
        }
        
        mysqli_stmt_close($stmt_update);
    }
    
    mysqli_stmt_close($stmt_user);
}

echo $msg . "|" . $valid;
?>