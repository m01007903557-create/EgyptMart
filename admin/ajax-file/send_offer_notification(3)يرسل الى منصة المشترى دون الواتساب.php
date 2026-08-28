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
               u.fname, u.lname, u.mobile1 as buyer_phone,
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
$supplier_name = $offer['supplier_fname'] . ' ' . $offer['supplier_lname'];
$subject = "عرض سعر جديد لطلبك RFQ #{$offer['rfq_id']}";
$message = "تم إرسال عرض سعر جديد من الإدارة.\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "🚚 مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد') . "\n\n";
$message .= "🔗 للاطلاع على العرض والرد: https://egyptmart.shop/ajax-file/enq-details.php?rfq_id={$offer['rfq_id']}&offer_id={$offer_id}";

$insert = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
           VALUES (0, {$offer['buyer_id']}, '$subject', '$message', NOW(), 1, 1, 'offer', {$offer['rfq_id']})";

if (!mysqli_query($con, $insert)) {
    echo json_encode(['success' => false, 'error' => 'فشل إرسال الرسالة: ' . mysqli_error($con)]);
    exit;
}

// ============================================
// 2. تحديث حالة العرض
// ============================================
mysqli_query($con, "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id");

// ============================================
// 3. الرد بنجاح
// ============================================
echo json_encode([
    'success' => true,
    'message' => 'تم إرسال العرض للمشتري',
    'offer_id' => $offer_id,
    'buyer_id' => $offer['buyer_id'],
    'rfq_id' => $offer['rfq_id']
]);
?>