<?php
require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['uid_indm'] ?? 0;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

if ($action == 'send') {
    $chat_id = (int)$_POST['chat_id'];
    $message = mysqli_real_escape_string($con, $_POST['message']);
    $sender_type = $_POST['sender_type'] == 'supplier' ? 'supplier' : 'buyer';
    
    $sql = "INSERT INTO chat_messages (chat_id, sender_id, sender_type, message, created_at) 
            VALUES ($chat_id, $user_id, '$sender_type', '$message', NOW())";
    mysqli_query($con, $sql);
    
    echo json_encode(['success' => true]);
}

elseif ($action == 'get') {
    $chat_id = (int)$_GET['chat_id'];
    
    $sql = "SELECT * FROM chat_messages WHERE chat_id = $chat_id ORDER BY created_at ASC";
    $res = mysqli_query($con, $sql);
    
    $messages = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $messages[] = [
            'msg_id' => $row['msg_id'],
            'sender_type' => $row['sender_type'],
            'message' => nl2br(htmlspecialchars($row['message'])),
            'time' => date('H:i', strtotime($row['created_at']))
        ];
    }
    
    if ($action == 'send') {
    $chat_id = (int)$_POST['chat_id'];
    $message = mysqli_real_escape_string($con, $_POST['message']);
    $sender_type = $_POST['sender_type'] == 'supplier' ? 'supplier' : 'buyer';
    
    // جلب معلومات الشات
    $chat_info = mysqli_query($con, "SELECT rfq_id, supplier_id, buyer_id FROM chat_rooms WHERE chat_id = $chat_id");
    $chat = mysqli_fetch_assoc($chat_info);
    
    // حفظ الرسالة
    $sql = "INSERT INTO chat_messages (chat_id, sender_id, sender_type, message, created_at) 
            VALUES ($chat_id, $user_id, '$sender_type', '$message', NOW())";
    mysqli_query($con, $sql);
    
    // =============================================
    // إضافة إشعار للمشتري إذا كان المرسل هو المورد
    // =============================================
    if ($sender_type == 'supplier') {
        $subject = "📩 رسالة جديدة من المورد في محادثة RFQ #{$chat['rfq_id']}";
        $body = "لديك رسالة جديدة من المورد:\n\n" . substr($message, 0, 200) . "\n\n";
        $body .= "للرد، اضغط على زر فتح المحادثة.";
        
        $subject_safe = mysqli_real_escape_string($con, $subject);
        $body_safe = mysqli_real_escape_string($con, $body);
        
        $notify_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                       VALUES ({$chat['supplier_id']}, {$chat['buyer_id']}, '$subject_safe', '$body_safe', NOW(), 1, 1, 'chat_message', {$chat['rfq_id']})";
        mysqli_query($con, $notify_sql);
    }
    
    echo json_encode(['success' => true]);
}
    
    echo json_encode(['success' => true, 'messages' => $messages]);
}
?>