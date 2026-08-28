<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

// سجل وصول الملف
file_put_contents(__DIR__ . '/debug.txt', date('Y-m-d H:i:s') . ' - submit_quote.php called' . PHP_EOL, FILE_APPEND);
file_put_contents(__DIR__ . '/debug.txt', 'POST: ' . print_r($_POST, true) . PHP_EOL, FILE_APPEND);
file_put_contents(__DIR__ . '/debug.txt', 'SESSION: ' . print_r($_SESSION, true) . PHP_EOL, FILE_APPEND);

// ... باقي الكود ...
?>


header('Content-Type: application/json');

$supplier_id = $_SESSION['uid_indm'] ?? 0;
if (!$supplier_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$rfq_id = (int)$_POST['rfq_id'];
$unit_price = (float)$_POST['unit_price'];
$moq = (int)$_POST['moq'];
$delivery_time = mysqli_real_escape_string($con, $_POST['delivery_time']);
$supplier_message = mysqli_real_escape_string($con, $_POST['supplier_message']);

console.log("Sending to: /supplier/submit_quote.php");
console.log("Form data:", Object.fromEntries(formData));



// جلب بيانات الطلب
$sql = "SELECT br.br_u_id as buyer_id, br.br_pc_id, p.pd_title 
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        WHERE br.br_id = $rfq_id";
$res = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($res);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

$buyer_id = $rfq['buyer_id'];

// 1️⃣ حفظ عرض السعر في جدول quotes
$insert_quote = "INSERT INTO quotes (rfq_id, supplier_id, buyer_id, unit_price, moq, delivery_time, supplier_message, created_at) 
                 VALUES ($rfq_id, $supplier_id, $buyer_id, $unit_price, $moq, '$delivery_time', '$supplier_message', NOW())";
mysqli_query($con, $insert_quote);
$quote_id = mysqli_insert_id($con);

// 2️⃣ إنشاء شات (إذا لم يكن موجوداً)
$chat_code = 'CHAT-' . time() . rand(100, 999);
$insert_chat = "INSERT INTO chat_rooms (chat_code, rfq_id, supplier_id, buyer_id, quote_id, created_at) 
                VALUES ('$chat_code', $rfq_id, $supplier_id, $buyer_id, $quote_id, NOW())";
mysqli_query($con, $insert_chat);
$chat_id = mysqli_insert_id($con);

// 3️⃣ حفظ أول رسالة في الشات (عرض السعر)
$first_msg = "📦 عرض سعر للمنتج\n";
$first_msg .= "السعر: $unit_price USD\n";
$first_msg .= "أقل كمية: $moq\n";
$first_msg .= "مدة التوصيل: $delivery_time\n";
$first_msg .= "رسالة المورد: $supplier_message";

$insert_msg = "INSERT INTO chat_messages (chat_id, sender_id, sender_type, message, created_at) 
               VALUES ($chat_id, $supplier_id, 'supplier', '$first_msg', NOW())";
mysqli_query($con, $insert_msg);

// 4️⃣ إشعار للأدمن
$admin_subject = "عرض سعر جديد من المورد - RFQ #$rfq_id";
$admin_body = "تم استلام عرض سعر جديد من المورد.\n";
$admin_body .= "المنتج: {$rfq['pd_title']}\n";
$admin_body .= "السعر: $unit_price USD\n";
$admin_body .= "أقل كمية: $moq\n";
$admin_body .= "مدة التوصيل: $delivery_time\n";
$admin_body .= "رابط الطلب: /admin/whatsapp_rfq.php";

// إدراج في جدول رسائل الأدمن (message)
$admin_id = 1;
$insert_admin_msg = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                     VALUES ($supplier_id, $admin_id, '$admin_subject', '$admin_body', NOW(), 1, 1, 'supplier_quote', $rfq_id)";
mysqli_query($con, $insert_admin_msg);

// 5️⃣ إشعار للمشتري في Dashboard
$buyer_subject = "📦 رد المورد على طلبك - عرض سعر جديد";
$buyer_body = "قام المورد بتقديم عرض سعر لطلبك رقم #$rfq_id.\n\n";
$buyer_body .= "السعر: $unit_price USD\n";
$buyer_body .= "أقل كمية: $moq\n";
$buyer_body .= "مدة التوصيل: $delivery_time\n\n";
$buyer_body .= "للرد على المورد، اضغط على زر 'فتح المحادثة'.";

$insert_buyer_msg = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                     VALUES ($supplier_id, $buyer_id, '$buyer_subject', '$buyer_body', NOW(), 1, 1, 'supplier_quote', $rfq_id)";
mysqli_query($con, $insert_buyer_msg);

// 6️⃣ إشعار واتساب للمشتري (بدون كشف بيانات المورد)
$buyer_phone = mysqli_fetch_assoc(mysqli_query($con, "SELECT mobile1 FROM user WHERE usr_id = $buyer_id"))['mobile1'];
$wa_message = "You received a new quotation for your RFQ on EgyptMart.\n";
$wa_message .= "Login to view supplier offer and continue chat.\n";
$wa_message .= "https://egyptmart.shop/my-enquiries.php";
$wa_url = "https://wa.me/20" . ltrim($buyer_phone, '0') . "?text=" . urlencode($wa_message);

echo json_encode([
    'success' => true,
    'message' => 'تم إرسال عرض السعر بنجاح',
    'chat_id' => $chat_id,
    'chat_code' => $chat_code,
    'whatsapp_url' => $wa_url
]);
?>