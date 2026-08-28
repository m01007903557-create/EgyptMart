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

// التوجيه إلى صفحة my-enquiries.php لعرض الرسالة
header("Location: my-enquiries.php?msg_id=" . getMessageIdByRfqId($rfq_id));
exit;

function getMessageIdByRfqId($rfq_id) {
    global $con;
    $sql = "SELECT msg_id FROM message WHERE msg_entity_id = $rfq_id AND msg_entity = 'whatsapp_rfq' LIMIT 1";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row['msg_id'] ?? 0;
}
?>