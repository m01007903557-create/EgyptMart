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

// جلب بيانات العرض
$sql = "SELECT o.*, br.br_u_id as buyer_id, br.br_id as rfq_id,
               u.fname, u.lname, u.mobile1 as buyer_phone, u.email as buyer_email,
               s.fname as supplier_fname, s.lname as supplier_lname
        FROM offers o
        LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN user s ON o.supplier_id = s.usr_id
        WHERE o.id = $offer_id";
$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    echo json_encode(['success' => false, 'error' => 'العرض غير موجود']);
    exit;
}

// ============================================
// 1. إرسال رسالة للمشتري في المنصة
// ============================================
$subject = "عرض سعر جديد لطلبك RFQ #{$offer['rfq_id']}";
$message = "تم إرسال عرض سعر جديد.\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "🚚 مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد') . "\n\n";
$message .= "🔗 للاطلاع على العرض والرد: https://egyptmart.shop/ajax-file/enq-details.php?rfq_id={$offer['rfq_id']}&offer_id={$offer_id}";

$insert = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
           VALUES (0, {$offer['buyer_id']}, '$subject', '$message', NOW(), 1, 1, 'offer', {$offer['rfq_id']})";
mysqli_query($con, $insert);

// ============================================
// 2. إرسال واتساب للمشتري
// ============================================
$buyer_phone = $offer['buyer_phone'];
$cleanPhone = preg_replace('/[^0-9]/', '', $buyer_phone);
if (substr($cleanPhone, 0, 2) != '20' && strlen($cleanPhone) > 9) {
    $cleanPhone = '20' . ltrim($cleanPhone, '0');
}

$supplier_name = $offer['supplier_fname'] . ' ' . $offer['supplier_lname'];
$magic_link = "https://egyptmart.shop/ajax-file/enq-details.php?rfq_id={$offer['rfq_id']}&offer_id={$offer_id}";

$whatsapp_msg = "📦 *عرض سعر جديد لطلبك RFQ #{$offer['rfq_id']}*\n\n";
$whatsapp_msg .= "*المورد:* {$supplier_name}\n";
$whatsapp_msg .= "*السعر المقترح:* {$offer['price']} {$offer['currency']}\n";
$whatsapp_msg .= "*مدة التوصيل:* {$offer['delivery_days']} يوم\n\n";
$whatsapp_msg .= "✨ *للقبول أو الرفض، اضغط على الرابط:*\n";
$whatsapp_msg .= $magic_link;

$whatsapp_url = "https://api.whatsapp.com/send?phone=" . $cleanPhone . "&text=" . urlencode($whatsapp_msg);

// ============================================
// 3. تحديث حالة العرض
// ============================================
mysqli_query($con, "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id");

// ============================================
// 4. الرد
// ============================================
echo json_encode([
    'success' => true,
    'message' => 'تم إرسال العرض إلى المشتري',
    'whatsapp_url' => $whatsapp_url,
    'magic_link' => $magic_link
]);
?>