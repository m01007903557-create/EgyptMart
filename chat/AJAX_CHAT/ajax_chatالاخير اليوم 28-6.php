<?php
/**
 * chat/ajax_chat.php - معالج رسائل الشات (نسخة مبسطة - بدون session_fix)
 */

// ✅ بدء الجلسة مباشرة
session_start();

// ✅ ثم الاتصال بقاعدة البيانات
require_once dirname(__DIR__) . '/lib/connect.php';

header('Content-Type: application/json');

// ✅ التحقق من الجلسة
$user_id = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح: يرجى تسجيل الدخول']);
    exit;
}

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// ============================================================
// إرسال رسالة
// ============================================================
if ($action == 'send') {
    $chat_id = isset($_POST['chat_id']) ? (int)$_POST['chat_id'] : 0;
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    if ($chat_id <= 0 || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']);
        exit;
    }
    
    $message_clean = mysqli_real_escape_string($con, $message);
    
    $insert_sql = "INSERT INTO chat_messages (chat_id, sender_id, message, created_at) 
                   VALUES ($chat_id, $user_id, '$message_clean', NOW())";
    
    if (mysqli_query($con, $insert_sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
    exit;
}

// ============================================================
// جلب الرسائل
// ============================================================
elseif ($action == 'get') {
    $chat_id = isset($_GET['chat_id']) ? (int)$_GET['chat_id'] : 0;
    
    if ($chat_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'معرف غير صحيح']);
        exit;
    }
    
    $sql = "SELECT 
                sender_id, 
                message, 
                DATE_FORMAT(created_at, '%H:%i') as time 
            FROM chat_messages 
            WHERE chat_id = $chat_id 
            ORDER BY created_at ASC";
    
    $res = mysqli_query($con, $sql);
    $messages = [];
    
    while ($row = mysqli_fetch_assoc($res)) {
        $messages[] = [
            'sender_id' => (int)$row['sender_id'],
            'message' => nl2br(htmlspecialchars($row['message'])),
            'time' => $row['time']
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'طلب غير صحيح']);
?>