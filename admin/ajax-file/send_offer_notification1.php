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

// ============================================
// 1. جلب بيانات العرض والمشتري
// ============================================
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
// 2. إرسال عرض السعر إلى رسائل المشتري (جدول message)
// ============================================
$supplier_name = $offer['supplier_company'] ?? ($offer['supplier_fname'] . ' ' . $offer['supplier_lname']);
$subject = "📦 عرض سعر جديد لطلبك RFQ #{$offer['rfq_id']}";
$message = "تم إرسال عرض سعر جديد من المورد: {$supplier_name}\n\n";
$message .= "💰 السعر: {$offer['price']} {$offer['currency']}\n";
$message .= "⏱ مدة التوصيل: {$offer['delivery_days']} يوم\n";
$message .= "📝 ملاحظات المورد: " . ($offer['notes'] ?? 'لا توجد ملاحظات') . "\n\n";
$message .= "🔗 للاطلاع على العرض والرد: https://egyptmart.shop/my-enquiries.php?rfq_id={$offer['rfq_id']}";

$insert_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
               VALUES ({$offer['supplier_id']}, {$offer['buyer_id']}, '$subject', '$message', NOW(), 1, 1, 'offer', {$offer['rfq_id']})";

if (!mysqli_query($con, $insert_sql)) {
    echo json_encode(['success' => false, 'error' => 'خطأ في إرسال الرسالة: ' . mysqli_error($con)]);
    exit;
}

// ============================================
// 3. تحديث حالة العرض إلى 'notified'
// ============================================
$update_sql = "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id";
mysqli_query($con, $update_sql);

// ============================================
// 4. إرجاع البيانات للواجهة (واتساب)
// ============================================
echo json_encode([
    'success' => true,
    'offer_id' => $offer_id,
    'buyer_phone' => $offer['buyer_phone'],
    'buyer_name' => $offer['buyer_fname'] . ' ' . $offer['buyer_lname'],
    'supplier_name' => $supplier_name,
    'price' => $offer['price'],
    'currency' => $offer['currency'],
    'delivery_days' => $offer['delivery_days'],
    'rfq_id' => $offer['rfq_id']
]);
?>