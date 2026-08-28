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
$buyer_id = $offer['buyer_id'];
$supplier_id = $offer['supplier_id'];
$rfq_id = $offer['rfq_id'];

$subject = "عرض سعر جديد لطلبك RFQ #$rfq_id";
$message = "تم إرسال عرض سعر جديد.\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "🚚 مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد') . "\n\n";
$message .= "🔗 للاطلاع: https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";

// ✅ msg_from = buyer_id (المشتري), msg_to = supplier_id (المورد)
$insert = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
           VALUES ($buyer_id, $supplier_id, '$subject', '$message', NOW(), 1, 1, 'offer', $rfq_id)";
mysqli_query($con, $insert);

// ============================================
// 1. إرسال رسالة للمشتري في المنصة
// ============================================
$subject = "عرض سعر جديد لطلبك RFQ #$rfq_id";
$message = "تم إرسال عرض سعر جديد.\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "🚚 مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد') . "\n\n";
$message .= "🔗 للاطلاع: https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";

// ✅ إدراج في جدول message (msg_from = supplier_id, msg_to = buyer_id)
$insert = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
           VALUES ($supplier_id, $buyer_id, '$subject', '$message', NOW(), 1, 1, 'offer', $rfq_id)";
$result_msg = mysqli_query($con, $insert);

if (!$result_msg) {
    error_log("فشل إدراج رسالة المشتري: " . mysqli_error($con));
}

// ============================================
// 2. تحديث حالة العرض
// ============================================
mysqli_query($con, "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id");

// ============================================
// 3. الرد
// ============================================
echo json_encode([
    'success' => true,
    'message' => 'تم إرسال العرض للمشتري',
    'buyer_id' => $buyer_id,
    'rfq_id' => $rfq_id
]);
?>