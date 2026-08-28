<?php
session_start();
require_once "../lib/connect.php";

$token = $_GET['token'] ?? '';
$rfq_id = (int)($_GET['rfq_id'] ?? 0);

if (empty($token) || $rfq_id == 0) {
    die('رابط غير صالح');
}

// التحقق من صحة التوكن
$sql = "SELECT br_id, supplier_id, wa_magic_token, wa_token_expiry 
        FROM buy_requirement 
        WHERE br_id = $rfq_id AND wa_magic_token = '$token' AND wa_token_expiry > NOW()";
$res = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($res);

if (!$rfq) {
    die('الرابط منتهي الصلاحية أو غير صالح');
}

// تسجيل دخول المورد تلقائياً (بدون كلمة مرور)
$_SESSION['uid_indm'] = $rfq['supplier_id'];

// جلب معرف الرسالة من جدول message
$msg_sql = "SELECT msg_id FROM message WHERE msg_entity_id = $rfq_id AND msg_entity = 'whatsapp_rfq' LIMIT 1";
$msg_res = mysqli_query($con, $msg_sql);
$msg_row = mysqli_fetch_assoc($msg_res);
$msg_id = $msg_row['msg_id'] ?? 0;

if ($msg_id > 0) {
    // التوجيه إلى صفحة my-enquiries.php مع معرف الرسالة
    header("Location: my-enquiries.php?msg_id=" . $msg_id);
} else {
    // إذا لم يتم العثور على الرسالة، اذهب إلى صفحة my-enquiries.php العادية
    header("Location: my-enquiries.php");
}
exit;
?>