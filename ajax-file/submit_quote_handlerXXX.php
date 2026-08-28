<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$supplier_id = (int)$_SESSION['uid_indm'];

if (!isset($_POST['rfq_id']) || !isset($_POST['price']) || !isset($_POST['delivery_days'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']);
    exit;
}

$rfq_id = (int)$_POST['rfq_id'];
$price = (float)$_POST['price'];
$delivery_days = (int)$_POST['delivery_days'];
$notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

// جلب buyer_id
$buyer_sql = "SELECT br_u_id FROM buy_requirement WHERE br_id = $rfq_id";
$buyer_res = mysqli_query($con, $buyer_sql);
$buyer_row = mysqli_fetch_assoc($buyer_res);
$buyer_id = $buyer_row ? $buyer_row['br_u_id'] : 0;

// حفظ عرض السعر
$insert_sql = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
               VALUES ($rfq_id, $supplier_id, $buyer_id, $price, 'USD', $delivery_days, '$notes', 'pending', NOW(), 0)";

if (!mysqli_query($con, $insert_sql)) {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
    exit;
}

$offer_id = mysqli_insert_id($con);

// ============================================
// ✅ إنشاء chat_code فوراً بعد حفظ العرض
// ============================================
$chat_code = md5($rfq_id . microtime(true) . rand(1000, 9999));
$insert_chat = "INSERT IGNORE INTO chat_rooms (rfq_id, chat_code) VALUES ($rfq_id, '$chat_code')";
mysqli_query($con, $insert_chat);

error_log("✅ chat_code created: " . $chat_code . " for rfq_id: " . $rfq_id);
// ============================================
// إشعار للأدمن
// ============================================
$admin_msg = "عرض سعر جديد للطلب #$rfq_id\nالمورد: $supplier_id\nالسعر: $price USD";
mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                    VALUES ($supplier_id, 1, 'عرض سعر جديد', '$admin_msg', NOW(), 1, 1, 'admin_notification', $rfq_id)");

// ============================================
// الرد النهائي
// ============================================
echo json_encode([
    'success' => true,
    'message' => '✅ تم إرسال عرض السعر للإدارة',
    'offer_id' => $offer_id,
    'chat_code' => $chat_code,
    'is_first' => true
]);
?>