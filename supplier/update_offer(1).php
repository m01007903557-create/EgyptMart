<?php
session_start();
require_once "../lib/connect.php";

header('Content-Type: application/json');

if (empty($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$supplier_id = $_SESSION['uid_indm'];
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
$new_price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$new_delivery = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 0;
$new_notes = isset($_POST['notes']) ? mysqli_real_escape_string($con, $_POST['notes']) : '';

if ($offer_id == 0 || $new_price == 0) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير صالحة']);
    exit;
}

// جلب العرض الحالي
$sql = "SELECT o.*, br.br_u_id as buyer_id, br.br_id as rfq_id 
        FROM offers o
        LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
        WHERE o.id = $offer_id AND o.supplier_id = $supplier_id";
$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    echo json_encode(['success' => false, 'error' => 'العرض غير موجود']);
    exit;
}

$old_price = $offer['price'];
$old_delivery = $offer['delivery_days'];
$old_notes = $offer['notes'];
$update_count = $offer['update_count'] + 1;

// التحقق من عدد التعديلات
if ($update_count > 2) {
    echo json_encode(['success' => false, 'error' => 'لا يمكن تعديل السعر أكثر من مرتين. يرجى تقديم طلب شراء جديد.']);
    exit;
}

// تحديث العرض
$update_sql = "UPDATE offers SET 
                price = $new_price, 
                delivery_days = $new_delivery, 
                notes = '$new_notes',
                update_count = $update_count,
                status = 'negotiation'
              WHERE id = $offer_id";
mysqli_query($con, $update_sql);

// تسجيل التعديل في offer_logs
$log_sql = "INSERT INTO offer_logs (offer_id, rfq_id, old_price, new_price, old_delivery_days, new_delivery_days, old_notes, new_notes, updated_by, created_at) 
            VALUES ($offer_id, {$offer['rfq_id']}, $old_price, $new_price, $old_delivery, $new_delivery, '$old_notes', '$new_notes', 'supplier', NOW())";
mysqli_query($con, $log_sql);

// إرسال إشعار للمشتري (في Dashboard)
$buyer_id = $offer['buyer_id'];
$subject = "📦 تعديل عرض سعر - RFQ #{$offer['rfq_id']}";
$body = "قام المورد بتعديل عرض السعر لطلبك RFQ #{$offer['rfq_id']}\n\n";
$body .= "السعر الجديد: $new_price USD\n";
$body .= "مدة التوصيل الجديدة: $new_delivery يوم\n";
$body .= "ملاحظات المورد: $new_notes\n\n";
$body .= "للاطلاع على العرض الجديد، يرجى تسجيل الدخول إلى حسابك.";

$subject_clean = mysqli_real_escape_string($con, $subject);
$body_clean = mysqli_real_escape_string($con, $body);

$msg_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
            VALUES ($supplier_id, $buyer_id, '$subject_clean', '$body_clean', NOW(), 1, 1, 'offer_update', {$offer['rfq_id']})";
mysqli_query($con, $msg_sql);

// إشعار واتساب للمشتري (رابط سحري)
$buyer_phone = mysqli_fetch_assoc(mysqli_query($con, "SELECT mobile1 FROM user WHERE usr_id = $buyer_id"))['mobile1'];
$cleanPhone = '20' . ltrim(preg_replace('/[^0-9]/', '', $buyer_phone), '0');
$whatsapp_msg = "📦 تم تعديل عرض السعر لطلبك RFQ #{$offer['rfq_id']}\n";
$whatsapp_msg .= "السعر الجديد: $new_price USD\n";
$whatsapp_msg .= "مدة التوصيل: $new_delivery يوم\n";
$whatsapp_msg .= "رابط الطلب: https://egyptmart.shop/my-enquiries.php?rfq_id={$offer['rfq_id']}";
$whatsapp_url = "https://wa.me/$cleanPhone?text=" . urlencode($whatsapp_msg);

echo json_encode([
    'success' => true,
    'message' => 'تم تحديث عرض السعر بنجاح',
    'whatsapp_url' => $whatsapp_url,
    'update_count' => $update_count
]);
?>