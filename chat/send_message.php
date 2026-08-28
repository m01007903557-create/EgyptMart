<?php
session_start();
require_once "../lib/connect.php";
header('Content-Type: application/json');

$rfq_id = (int)$_POST['rfq_id'];
$message = mysqli_real_escape_string($con, $_POST['message']);
$sender_type = $_POST['sender_type'] ?? 'buyer';
$sender_id = $_SESSION['uid_indm'] ?? $_SESSION['ad_id_indm'] ?? 0;

if (!$sender_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$sql = "INSERT INTO whatsapp_chat_messages (rfq_id, sender_type, sender_id, message, created_at) VALUES ($rfq_id, '$sender_type', $sender_id, '$message', NOW())";
mysqli_query($con, $sql);

echo json_encode(['success' => true]);
?>