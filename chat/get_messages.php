<?php
session_start();
require_once "../lib/connect.php";
header('Content-Type: application/json');

$rfq_id = (int)$_GET['rfq_id'];
$user_id = $_SESSION['uid_indm'] ?? $_SESSION['ad_id_indm'] ?? 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

// تحديث حالة القراءة للرسائل المرسلة للطرف الآخر
$user_type = isset($_SESSION['uid_indm']) ? 'buyer' : (isset($_SESSION['ad_id_indm']) ? 'admin' : 'supplier');
$other_type = $user_type == 'buyer' ? 'supplier' : ($user_type == 'supplier' ? 'buyer' : 'both');

if ($user_type != 'admin') {
    $update_sql = "UPDATE whatsapp_chat_messages SET is_read = 1 WHERE rfq_id = $rfq_id AND sender_type != '$user_type'";
    mysqli_query($con, $update_sql);
}

$sql = "SELECT * FROM whatsapp_chat_messages WHERE rfq_id = $rfq_id ORDER BY created_at ASC";
$result = mysqli_query($con, $sql);

$messages = [];
while ($row = mysqli_fetch_assoc($result)) {
    $messages[] = [
        'id' => $row['msg_id'],
        'sender_type' => $row['sender_type'],
        'message' => $row['message'],
        'created_at' => $row['created_at'],
        'is_read' => $row['is_read']
    ];
}

echo json_encode(['success' => true, 'messages' => $messages, 'user_type' => $user_type]);
?>