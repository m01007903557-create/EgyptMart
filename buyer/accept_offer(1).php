<?php
session_start();
require_once "../lib/connect.php";

header('Content-Type: application/json');

if (empty($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$buyer_id = $_SESSION['uid_indm'];
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;

if ($offer_id == 0) {
    echo json_encode(['success' => false, 'error' => 'offer_id مطلوب']);
    exit;
}

// جلب بيانات العرض
$sql = "SELECT o.*, 
               br.br_u_id as buyer_id, br.br_id as rfq_id,
               s.fname as supplier_fname, s.lname as supplier_lname, s.mobile1 as supplier_phone, s.email as supplier_email,
               bp.bnsprof_compname as supplier_company, bp.bnsprof_comp_url
        FROM offers o
        LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
        LEFT JOIN user s ON o.supplier_id = s.usr_id
        LEFT JOIN business_profile bp ON s.usr_id = bp.bnsprof_uid
        WHERE o.id = $offer_id AND o.buyer_id = $buyer_id";
$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    echo json_encode(['success' => false, 'error' => 'العرض غير موجود']);
    exit;
}

// تحديث حالة العرض
$update_sql = "UPDATE offers SET status = 'accepted', final_price = price, accepted_at = NOW() WHERE id = $offer_id";
mysqli_query($con, $update_sql);

// تحديث حالة الطلب في buy_requirement
mysqli_query($con, "UPDATE buy_requirement SET br_status = 'accepted', wa_status = 'accepted', wa_accepted = 1, wa_accepted_date = NOW() WHERE br_id = {$offer['rfq_id']}");

// إشعار للمورد (في Dashboard)
$supplier_id = $offer['supplier_id'];
$subject = "✅ قبول عرض السعر - RFQ #{$offer['rfq_id']}";
$body = "قام المشتري بقبول عرض سعرك لطلب RFQ #{$offer['rfq_id']}\n\n";
$body .= "السعر النهائي: {$offer['price']} USD\n";
$body .= "مدة التوصيل: {$offer['delivery_days']} يوم\n\n";
$body .= "بيانات المشتري للتواصل:\n";
$body .= "- الاسم: " . ($_SESSION['fname'] ?? '') . " " . ($_SESSION['lname'] ?? '') . "\n";
$body .= "- الجوال: " . ($_SESSION['mobile1'] ?? '') . "\n";
$body .= "- البريد: " . ($_SESSION['email'] ?? '') . "\n\n";
$body .= "يمكنكم الآن التواصل مباشرة لإتمام إجراءات التوريد.";

$subject_clean = mysqli_real_escape_string($con, $subject);
$body_clean = mysqli_real_escape_string($con, $body);

$msg_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
            VALUES ($buyer_id, $supplier_id, '$subject_clean', '$body_clean', NOW(), 1, 1, 'offer_accepted', {$offer['rfq_id']})";
mysqli_query($con, $msg_sql);

// إشعار واتساب للمورد
$supplier_phone = $offer['supplier_phone'];
$cleanPhone = '20' . ltrim(preg_replace('/[^0-9]/', '', $supplier_phone), '0');
$whatsapp_msg = "✅ تم قبول عرض سعرك لطلب RFQ #{$offer['rfq_id']}\n";
$whatsapp_msg .= "السعر النهائي: {$offer['price']} USD\n";
$whatsapp_msg .= "يمكنك الآن التواصل مع المشتري مباشرة.\n";
$whatsapp_msg .= "رابط الطلب: https://egyptmart.shop/my-enquiries.php?rfq_id={$offer['rfq_id']}";
$whatsapp_url = "https://wa.me/$cleanPhone?text=" . urlencode($whatsapp_msg);

// كشف بيانات المورد للمشتري في Dashboard
$buyer_subject = "🔓 تم قبول العرض - بيانات المورد متاحة الآن";
$buyer_body = "تم قبول عرض السعر لطلبك RFQ #{$offer['rfq_id']}\n\n";
$buyer_body .= "بيانات المورد للتواصل:\n";
$buyer_body .= "- الشركة: {$offer['supplier_company']}\n";
$buyer_body .= "- اسم المورد: {$offer['supplier_fname']} {$offer['supplier_lname']}\n";
$buyer_body .= "- الجوال: {$offer['supplier_phone']}\n";
$buyer_body .= "- البريد: {$offer['supplier_email']}\n";
$buyer_body .= "- رابط صفحة الشركة: https://egyptmart.shop/company/index.php?c=" . md5($offer['bnsprof_comp_url'] ?? '') . "\n\n";
$buyer_body .= "يمكنك الآن التواصل مع المورد مباشرة لإتمام إجراءات التوريد.";

$buyer_subject_clean = mysqli_real_escape_string($con, $buyer_subject);
$buyer_body_clean = mysqli_real_escape_string($con, $buyer_body);

$buyer_msg_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                  VALUES ($supplier_id, $buyer_id, '$buyer_subject_clean', '$buyer_body_clean', NOW(), 1, 1, 'offer_accepted', {$offer['rfq_id']})";
mysqli_query($con, $buyer_msg_sql);

echo json_encode([
    'success' => true,
    'message' => 'تم قبول العرض بنجاح',
    'whatsapp_url' => $whatsapp_url,
    'supplier_data' => [
        'company' => $offer['supplier_company'],
        'name' => $offer['supplier_fname'] . ' ' . $offer['supplier_lname'],
        'phone' => $offer['supplier_phone'],
        'email' => $offer['supplier_email'],
        'url' => 'https://egyptmart.shop/company/index.php?c=' . md5($offer['bnsprof_comp_url'] ?? '')
    ]
]);
?>