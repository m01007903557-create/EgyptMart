<?php
session_start();
require_once "../../lib/connect.php";
require_once __DIR__ . '/../../includes/rfq_helpers.php'; // ✅ إضافة الملف المساعد

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
$sql = "SELECT o.*, br.br_u_id as buyer_id, br.br_id as rfq_id
        FROM offers o
        LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
        WHERE o.id = $offer_id";
$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    echo json_encode(['success' => false, 'error' => 'العرض غير موجود']);
    exit;
}

$buyer_id = $offer['buyer_id'];
$supplier_id = $offer['supplier_id'];
$rfq_id = $offer['rfq_id'];

// إرسال رسالة للمشتري
$subject = "عرض سعر جديد لطلبك RFQ #$rfq_id";
$message = "تم إرسال عرض سعر جديد.\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "🚚 مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد') . "\n\n";
$message .= "🔗 للاطلاع: https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";

$insert = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
           VALUES ($supplier_id, $buyer_id, '$subject', '$message', NOW(), 1, 1, 'offer', $rfq_id)";
mysqli_query($con, $insert);

// تحديث حالة العرض
mysqli_query($con, "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id");

// ✅ تحديث حالة الطلب إلى in_negotiation
update_rfq_status($rfq_id, 'in_negotiation');

echo json_encode(['success' => true, 'message' => 'تم إرسال العرض']);
?>