<?php
session_start();
require_once "../lib/connect.php";
header('Content-Type: application/json');

$rfq_id = (int)$_POST['rfq_id'];
$buyer_id = $_SESSION['uid_indm'] ?? 0;

if (!$buyer_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

// تحديث حالة الطلب
mysqli_query($con, "UPDATE buy_requirement SET wa_accepted = 1, wa_accepted_date = NOW(), wa_status = 'accepted' WHERE br_id = $rfq_id AND br_u_id = $buyer_id");

// إضافة رسالة في المحادثة
$sql_chat = "INSERT INTO whatsapp_chat_messages (rfq_id, sender_type, sender_id, message, created_at) VALUES ($rfq_id, 'buyer', $buyer_id, '✅ تم قبول عرض السعر، يمكننا التواصل مباشرة خارج المنصة', NOW())";
mysqli_query($con, $sql_chat);

echo json_encode(['success' => true]);
?>