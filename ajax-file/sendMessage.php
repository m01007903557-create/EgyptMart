<?php
declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من البيانات المطلوبة
if (!isset($_POST['msg_from']) || !is_numeric($_POST['msg_from']) ||
    !isset($_POST['msg_to']) || !is_numeric($_POST['msg_to']) ||
    !isset($_POST['msg_message']) || empty(trim($_POST['msg_message']))) {
    http_response_code(400);
    echo "0|Invalid request data";
    exit;
}

$lead_headline = $_POST['lead_headline'] ?? '';
$msg_from = (int)$_POST['msg_from'];
$msg_to = (int)$_POST['msg_to'];
$msg_subject = trim($_POST['msg_subject'] ?? '');
$msg_message = trim($_POST['msg_message'] ?? '');

global $con;

// تنظيف النصوص
$msg_subject = mysqli_real_escape_string($con, $msg_subject);
$msg_message = mysqli_real_escape_string($con, $msg_message);

// جلب بيانات المرسل
$sql_usr = "SELECT u.*, bp.* 
            FROM user u
            INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
            WHERE u.usr_id = ? LIMIT 1";

$stmt_usr = mysqli_prepare($con, $sql_usr);
mysqli_stmt_bind_param($stmt_usr, 'i', $msg_from);
mysqli_stmt_execute($stmt_usr);
$result_usr = mysqli_stmt_get_result($stmt_usr);
$row_usr = mysqli_fetch_object($result_usr);
mysqli_stmt_close($stmt_usr);

if (!$row_usr) {
    echo "0|Sender not found";
    exit;
}

// حفظ الرسالة
$sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date) 
        VALUES (?, ?, ?, ?, NOW())";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'iiss', $msg_from, $msg_to, $msg_subject, $msg_message);

if (mysqli_stmt_execute($stmt)) {
    $msg_id = (int)mysqli_insert_id($con);
    mysqli_stmt_close($stmt);
    
    // نقل المرفقات من الجدول المؤقت إلى الجدول الدائم
    $sql_tma = "SELECT tma_id, tma_file FROM temp_msg_attachment WHERE tma_usr_id = ?";
    $stmt_tma = mysqli_prepare($con, $sql_tma);
    mysqli_stmt_bind_param($stmt_tma, 'i', $msg_from);
    mysqli_stmt_execute($stmt_tma);
    $result_tma = mysqli_stmt_get_result($stmt_tma);
    
    while ($row_tma = mysqli_fetch_object($result_tma)) {
        // إضافة المرفق
        $sql_ma = "INSERT INTO message_attachment (ma_msg_id, ma_file, ma_updated_date) 
                   VALUES (?, ?, NOW())";
        $stmt_ma = mysqli_prepare($con, $sql_ma);
        mysqli_stmt_bind_param($stmt_ma, 'is', $msg_id, $row_tma->tma_file);
        mysqli_stmt_execute($stmt_ma);
        mysqli_stmt_close($stmt_ma);
        
        // حذف من الجدول المؤقت
        $sql_del = "DELETE FROM temp_msg_attachment WHERE tma_id = ?";
        $stmt_del = mysqli_prepare($con, $sql_del);
        mysqli_stmt_bind_param($stmt_del, 'i', $row_tma->tma_id);
        mysqli_stmt_execute($stmt_del);
        mysqli_stmt_close($stmt_del);
    }
    mysqli_stmt_close($stmt_tma);
    
    // إضافة سجلات التقييم إذا لم تكن موجودة
    $sql_chk = "SELECT rr_id FROM review_rating WHERE rr_from_usr = ? AND rr_to_usr = ? LIMIT 1";
    $stmt_chk = mysqli_prepare($con, $sql_chk);
    mysqli_stmt_bind_param($stmt_chk, 'ii', $msg_from, $msg_to);
    mysqli_stmt_execute($stmt_chk);
    $result_chk = mysqli_stmt_get_result($stmt_chk);
    
    if (mysqli_num_rows($result_chk) == 0) {
        mysqli_stmt_close($stmt_chk);
        
        // إضافة تقييم للمرسل
        $sql_rr1 = "INSERT INTO review_rating (rr_from_usr, rr_to_usr) VALUES (?, ?)";
        $stmt_rr1 = mysqli_prepare($con, $sql_rr1);
        mysqli_stmt_bind_param($stmt_rr1, 'ii', $msg_from, $msg_to);
        mysqli_stmt_execute($stmt_rr1);
        mysqli_stmt_close($stmt_rr1);
        
        // إضافة تقييم للمستلم
        $sql_rr2 = "INSERT INTO review_rating (rr_from_usr, rr_to_usr) VALUES (?, ?)";
        $stmt_rr2 = mysqli_prepare($con, $sql_rr2);
        mysqli_stmt_bind_param($stmt_rr2, 'ii', $msg_to, $msg_from);
        mysqli_stmt_execute($stmt_rr2);
        mysqli_stmt_close($stmt_rr2);
    } else {
        mysqli_stmt_close($stmt_chk);
    }
    
    // إرسال البريد الإلكتروني
    $style = "إستفسار شراء من";
    $from_mail = get_adminemail();
    $to_email = user_info($msg_to, 'email');
    $from_name = get_page_settings(4);
    $subject = $row_usr->bnsprof_compname . ' ' . $style;
    
    // تضمين قالب البريد الإلكتروني
    ob_start();
    include __DIR__ . '/../email/sendenquiry_notification.php';
    $message_content = ob_get_clean();
    
    if (!empty($message_content)) {
        $headers = "MIME-Version: 1.0\n";
        $headers .= "Content-type: text/html; charset=UTF-8\n";
        $headers .= "From: " . $from_name . " <" . $from_mail . ">\n";
        
        // محاولة إرسال البريد
        $mail_sent = mail($to_email, $subject, $message_content, $headers);
        
        if (!$mail_sent) {
            error_log("Failed to send email to: " . $to_email);
        }
    }
    
    echo "1";
} else {
    error_log("Send Message Error: " . mysqli_error($con));
    echo "0|Failed to send message";
}
?>