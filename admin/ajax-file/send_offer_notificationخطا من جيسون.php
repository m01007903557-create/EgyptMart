<?php
session_start();
require_once "../../lib/connect.php";

header('Content-Type: application/json');

if (empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;

if ($offer_id == 0) {
    echo json_encode(['success' => false, 'error' => 'offer_id مطلوب']);
    exit;
}

// جلب بيانات العرض والمشتري
$sql = "SELECT o.*, 
               br.br_u_id as buyer_id,
               br.br_id as rfq_id,
               u.fname as buyer_fname, u.lname as buyer_lname, u.email as buyer_email, u.mobile1 as buyer_phone,
               s.fname as supplier_fname, s.lname as supplier_lname,
               bp.bnsprof_compname as supplier_company
        FROM offers o
        LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN user s ON o.supplier_id = s.usr_id
        LEFT JOIN business_profile bp ON s.usr_id = bp.bnsprof_uid
        WHERE o.id = $offer_id";

$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    echo json_encode(['success' => false, 'error' => 'العرض غير موجود']);
    exit;
}

// ============================================
// الحصول على msg_id لإنشاء الرابط السحري
// ============================================
$msg_id = 0;
$get_msg_sql = "SELECT msg_id FROM message WHERE msg_entity_id = {$offer['rfq_id']} AND msg_entity = 'whatsapp_rfq' ORDER BY msg_id DESC LIMIT 1";
$msg_result = mysqli_query($con, $get_msg_sql);
$msg_row = mysqli_fetch_assoc($msg_result);
if ($msg_row) {
    $msg_id = $msg_row['msg_id'];
}

// ============================================
// إرسال عرض السعر إلى رسائل المشتري (جدول message)
// ============================================
$supplier_name = $offer['supplier_company'] ?? ($offer['supplier_fname'] . ' ' . $offer['supplier_lname']);
$subject = "📦 عرض سعر جديد لطلبك RFQ #{$offer['rfq_id']}";
$message = "تم إرسال عرض سعر جديد من المورد: {$supplier_name}\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "⏱ مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد ملاحظات') . "\n\n";

// الرابط السحري داخل رسالة المنصة أيضاً
$magic_link = "ajax-file/enq-details.php?id=" . $msg_id . "&type=inbox&offer_id=" . $offer_id;
$message .= "🔗 للاطلاع على العرض والرد: " . $magic_link;

$insert_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
               VALUES ({$offer['supplier_id']}, {$offer['buyer_id']}, '$subject', '$message', NOW(), 1, 1, 'offer', {$offer['rfq_id']})";

if (!mysqli_query($con, $insert_sql)) {
    echo json_encode(['success' => false, 'error' => 'خطأ في إرسال الرسالة: ' . mysqli_error($con)]);
    exit;
}

// ============================================
// تحديث حالة العرض إلى 'notified'
// ============================================
$update_sql = "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id";
mysqli_query($con, $update_sql);

// ============================================
// تجهيز رسالة الواتساب مع الرابط السحري
// ============================================
$buyer_phone = $offer['buyer_phone'];
$cleanPhone = preg_replace('/[^0-9]/', '', $buyer_phone);
if (substr($cleanPhone, 0, 2) != '20' && strlen($cleanPhone) > 9) {
    $cleanPhone = '20' . ltrim($cleanPhone, '0');
}

$whatsapp_msg = "📦 *عرض سعر جديد لطلبك RFQ #{$offer['rfq_id']}*\n\n";
$whatsapp_msg .= "*المورد:* {$supplier_name}\n";
$whatsapp_msg .= "*السعر المقترح:* {$offer['price']} {$offer['currency']}\n";
$whatsapp_msg .= "*مدة التوصيل:* {$offer['delivery_days']} يوم\n\n";
$whatsapp_msg .= "✨ *للقبول أو الرفض، اضغط على الرابط:*\n";
$whatsapp_msg .= $magic_link . "\n\n";
$whatsapp_msg .= "يمكنك التواصل مع المورد مباشرة بعد القبول.";

// ============================================
// إرجاع البيانات للواجهة (واتساب)
// ============================================
echo json_encode([
    'success' => true,
    'offer_id' => $offer_id,
    'buyer_phone' => $cleanPhone,
    'buyer_name' => $offer['buyer_fname'] . ' ' . $offer['buyer_lname'],
    'supplier_name' => $supplier_name,
    'price' => $offer['price'],
    'currency' => $offer['currency'],
    'delivery_days' => $offer['delivery_days'],
    'rfq_id' => $offer['rfq_id'],
    'whatsapp_message' => $whatsapp_msg,
    'magic_link' => $magic_link
]);
?>