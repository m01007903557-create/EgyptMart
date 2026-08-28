<?php
session_start();
require_once "../lib/connect.php";
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';
$user_id = $_SESSION['uid_indm'] ?? 0;
$user_type = 'buyer';

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

if ($action == 'send') {
    $rfq_id = (int)$_POST['rfq_id'];
    $message = mysqli_real_escape_string($con, $_POST['message']);
    
    // تحديد نوع المرسل
    $sql_check = "SELECT * FROM whatsapp_rfq_messages WHERE rfq_id = $rfq_id";
    $res = mysqli_query($con, $sql_check);
    $rfq = mysqli_fetch_assoc($res);
    
    if ($rfq['buyer_id'] == $user_id) {
        $sender_type = 'buyer';
    } else {
        $sender_type = 'supplier';
    }
    
    $insert = "INSERT INTO whatsapp_chat (rfq_id, sender_id, sender_type, message, created_date) 
               VALUES ($rfq_id, $user_id, '$sender_type', '$message', NOW())";
    mysqli_query($con, $insert);
    
    echo json_encode(['success' => true]);
}

elseif ($action == 'get') {
    $rfq_id = (int)$_GET['rfq_id'];
    
    $sql = "SELECT * FROM whatsapp_chat WHERE rfq_id = $rfq_id ORDER BY created_date ASC";
    $res = mysqli_query($con, $sql);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $messages[] = [
            'id' => $row['wc_id'],
            'sender_type' => $row['sender_type'],
            'message' => nl2br(htmlspecialchars($row['message'])),
            'time' => date('H:i', strtotime($row['created_date']))
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
}
?>