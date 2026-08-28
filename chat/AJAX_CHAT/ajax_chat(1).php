<?php
/**
 * chat/ajax_chat.php - معالج رسائل الشات (مُصحح بالكامل)
 */

require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

header('Content-Type: application/json');

$user_id = $_SESSION['uid_indm'] ?? 0;
if (!$user_id) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$action = $_REQUEST['action'] ?? '';

// ============================================================
// معالج إرسال رسالة جديدة
// ============================================================
if ($action == 'send') {
    $chat_id = (int)($_POST['chat_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $sender_type = ($_POST['sender_type'] ?? 'buyer') == 'supplier' ? 'supplier' : 'buyer';
    
    if ($chat_id <= 0 || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']);
        exit;
    }
    
    // ✅ التحقق من وجود المحادثة وجلب معلوماتها
    $chat_info_sql = "SELECT rfq_id, supplier_id, buyer_id FROM chat_rooms WHERE chat_id = $chat_id LIMIT 1";
    $chat_info_res = mysqli_query($con, $chat_info_sql);
    $chat = mysqli_fetch_assoc($chat_info_res);
    
    if (!$chat) {
        echo json_encode(['success' => false, 'error' => 'المحادثة غير موجودة']);
        exit;
    }
    
    // ✅ التأكد من أن المستخدم الحالي هو Buyer أو Supplier لهذه المحادثة
    if ($user_id != $chat['buyer_id'] && $user_id != $chat['supplier_id']) {
        echo json_encode(['success' => false, 'error' => 'ليس لديك صلاحية']);
        exit;
    }
    
    // ✅ تحديد sender_type الصحيح بناءً على user_id
    $actual_sender_type = ($user_id == $chat['supplier_id']) ? 'supplier' : 'buyer';
    
    // ✅ إذا كان sender_type المرسل غير مطابق، نصححه
    if ($sender_type != $actual_sender_type) {
        $sender_type = $actual_sender_type;
    }
    
    // ✅ تنظيف الرسالة
    $message_clean = mysqli_real_escape_string($con, $message);
    
    // ✅ حفظ الرسالة مع sender_id الصحيح
    $insert_sql = "INSERT INTO chat_messages (chat_id, sender_id, sender_type, message, created_at) 
                   VALUES ($chat_id, $user_id, '$sender_type', '$message_clean', NOW())";
    
    if (mysqli_query($con, $insert_sql)) {
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
        
        echo json_encode(['success' => true, 'message' => 'تم الإرسال بنجاح']);
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    }
    exit;
}

// ============================================================
// معالج جلب الرسائل
// ============================================================
elseif ($action == 'get') {
    $chat_id = (int)($_GET['chat_id'] ?? 0);
    
    if ($chat_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'معرف المحادثة غير صحيح']);
        exit;
    }
    
    // ✅ التحقق من صلاحية المستخدم
    $check_sql = "SELECT buyer_id, supplier_id FROM chat_rooms WHERE chat_id = $chat_id LIMIT 1";
    $check_res = mysqli_query($con, $check_sql);
    $chat_data = mysqli_fetch_assoc($check_res);
    
    if (!$chat_data) {
        echo json_encode(['success' => false, 'error' => 'المحادثة غير موجودة']);
        exit;
    }
    
    if ($user_id != $chat_data['buyer_id'] && $user_id != $chat_data['supplier_id']) {
        echo json_encode(['success' => false, 'error' => 'غير مصرح']);
        exit;
    }
    
    // ✅ جلب الرسائل مع sender_id و sender_type و الوقت المنسق
    // ✅ ملاحظة: is_read ليس مطلوباً لعرض الرسائل، لكن يمكن إضافته إذا أردت
    $sql = "SELECT 
                sender_id, 
                sender_type, 
                message, 
                DATE_FORMAT(created_at, '%H:%i') as time 
            FROM chat_messages 
            WHERE chat_id = $chat_id 
            ORDER BY created_at ASC";
    
    $res = mysqli_query($con, $sql);
    $messages = [];
    
    while ($row = mysqli_fetch_assoc($res)) {
        $messages[] = [
            'sender_id' => (int)$row['sender_id'],  // ✅ هذا هو المفتاح لحل المشكلة!
            'sender_type' => $row['sender_type'],
            'message' => nl2br(htmlspecialchars($row['message'])),
            'time' => $row['time']
        ];
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    exit;
}

// ============================================================
// طلب غير معروف
// ============================================================
echo json_encode(['success' => false, 'error' => 'طلب غير صحيح']);
?>