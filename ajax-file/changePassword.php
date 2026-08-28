<?php
/**
 * File: ajax/changePassword.php

 * Description: تغيير كلمة المرور للمستخدم مع التحقق وإرسال إشعار بالبريد الإلكتروني
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['curr_pass']) || !isset($_POST['new_pass']) || 
    !isset($_POST['conf_pass']) || !isset($_POST['usid'])) {
    http_response_code(400);
    echo "البيانات غير مكتملة|0";
    exit;
}

$curr_pass = trim($_POST['curr_pass']);
$new_pass = trim($_POST['new_pass']);
$conf_pass = trim($_POST['conf_pass']);
$uid = (int)trim($_POST['usid']);

global $con;

/**
 * التحقق من صحة كلمة المرور الحالية
 * @param string $password كلمة المرور المدخلة
 * @param int $user_id معرف المستخدم
 * @return bool
 */
function validPassword(string $password, int $user_id, mysqli $con): bool {
    $hashed_password = md5($password);
    $sql = "SELECT usr_id FROM user WHERE pass = ? AND usr_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $hashed_password, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $count = mysqli_num_rows($result);
    mysqli_stmt_close($stmt);
    return $count > 0;
}

$msg = "";
$valid = 1;

// التحقق من كلمة المرور الحالية
if (empty($curr_pass)) {
    $msg = 'Please enter your Current password.';
    $valid = 0;
} elseif (!validPassword($curr_pass, $uid, $con)) {
    $msg = 'Please enter your correct password.';
    $valid = 0;
} 
// التحقق من كلمة المرور الجديدة
elseif (empty($new_pass)) {
    $msg = 'Please enter your New password.';
    $valid = 0;
} elseif (strlen($new_pass) < 6) {
    $msg = 'New password must be at least 6 characters long.';
    $valid = 0;
} 
// التحقق من تأكيد كلمة المرور
elseif (empty($conf_pass)) {
    $msg = 'Please enter your Confirm password.';
    $valid = 0;
} elseif ($new_pass !== $conf_pass) {
    $msg = 'New password and confirm password do not match.';
    $valid = 0;
} 
// تحديث كلمة المرور
else {
    $hashed_new_pass = md5($new_pass);
    $update_sql = "UPDATE user SET pass = ? WHERE usr_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'si', $hashed_new_pass, $uid);
    
    if (mysqli_stmt_execute($stmt_update)) {
        mysqli_stmt_close($stmt_update);
        
        // الحصول على معلومات المستخدم للبريد الإلكتروني
        $user_info_sql = "SELECT email, name_prefix, fname, lname FROM user WHERE usr_id = ? LIMIT 1";
        $stmt_info = mysqli_prepare($con, $user_info_sql);
        mysqli_stmt_bind_param($stmt_info, 'i', $uid);
        mysqli_stmt_execute($stmt_info);
        $result_info = mysqli_stmt_get_result($stmt_info);
        $user_data = mysqli_fetch_assoc($result_info);
        mysqli_stmt_close($stmt_info);
        
        if ($user_data) {
            // إرسال البريد الإلكتروني للإشعار
            $to = $user_data['email'];
            $subject = "Password Change Alert on " . getWebSiteName();
            $from_email = get_adminemail();
            $from_name = get_page_settings(4);
            
            $message = "Dear " . ($user_data['name_prefix'] ?? '') . " " . 
                       ($user_data['fname'] ?? '') . " " . ($user_data['lname'] ?? '') . "<br><br>";
            $message .= "Your new password has been updated successfully. Kindly use your new password for signing in now onwards.<br>";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: $from_name <$from_email>\r\n";
            
            // محاولة إرسال البريد الإلكتروني (لا نعتمد على نجاحه)
            mail($to, $subject, $message, $headers);
        }
        
        $valid = 1;
        $msg = 'Your new password has been updated successfully.';
    } else {
        error_log("Password Change Error: " . mysqli_error($con) . " | User ID: $uid");
        $msg = 'Failed to update password. Please try again.';
        $valid = 0;
    }
}

echo $msg . "|" . $valid;
?>