<?php
session_start();
require_once "../common.php";
check_admin_login();

header('Content-Type: application/json');

$rfq_id = isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0;
if (!$rfq_id) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// جلب بيانات الطلب والمورد
// جلب بيانات الطلب والمورد
$sql = "SELECT br.*, 
               u.fname, u.lname, u.mobile1, u.email, u.usr_id as buyer_id,
               p.pd_title, p.pd_uid as supplier_id,  -- ✅ هذا هو معرف المورد
               su.mobile1 as supplier_phone
        FROM buy_requirement br
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user su ON p.pd_uid = su.usr_id
        WHERE br.br_id = $rfq_id";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// ✅ استخدام pd_uid كمصدر لمعرف المورد
$supplier_id = $rfq['pd_uid'] ?? 0;
if (empty($supplier_id)) {
    $supplier_id = $rfq['supplier_id'] ?? 0;
}
$buyer_id = $rfq['br_u_id'];

error_log("✅ supplier_id (pd_uid): " . $supplier_id);
// الرابط السحري
$magic_token = md5($supplier_id . $rfq_id . time());
$expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
mysqli_query($con, "UPDATE buy_requirement SET wa_magic_token = '$magic_token', wa_token_expiry = '$expiry' WHERE br_id = $rfq_id");
$secure_link = "https://" . $_SERVER['HTTP_HOST'] . "/supplier/whatsapp_rfq_view.php?token=$magic_token&id=$rfq_id";

// بيانات الطلب
$product_name = $rfq['pd_title'] ?? 'غير محدد';
$quantity = ($rfq['br_estimate_qty'] ?? 0) . ' ' . ($rfq['br_estimate_qty_unit'] ?? '');
$details = $rfq['br_requirement'] ?? '';

// ============================================
// 1. بناء رابط واتساب
// ============================================
$supplier_phone = $rfq['supplier_phone'] ?? '';
if (empty($supplier_phone)) {
    $supplier_phone = $rfq['mobile1'] ?? '';
}
$cleanPhone = preg_replace('/[^0-9]/', '', $supplier_phone);
if (substr($cleanPhone, 0, 2) != '20') {
    $cleanPhone = '20' . ltrim($cleanPhone, '0');
}

$wa_message = "📦 طلب شراء جديد #$rfq_id\n\n";
$wa_message .= "المنتج: $product_name\n";
$wa_message .= "الكمية: $quantity\n";
$wa_message .= "التفاصيل: $details\n";
$wa_message .= "المشتري: {$rfq['fname']} {$rfq['lname']}\n";
$wa_message .= "هاتف المشتري: {$rfq['mobile1']}\n\n";
$wa_message .= "للتقديم: $secure_link";

$whatsapp_url = "https://wa.me/" . $cleanPhone . "?text=" . rawurlencode($wa_message);

// ============================================
// 2. إضافة سجل في جدول message (لرسائل المورد)
// ============================================
$admin_id = $_SESSION['ad_id_indm'] ?? 0;
$subject = "طلب شراء جديد RFQ #$rfq_id";
$message_body = "تم إرسال طلب شراء جديد إليك.\n\n";
$message_body .= "المنتج: $product_name\n";
$message_body .= "الكمية: $quantity\n";
$message_body .= "التفاصيل: $details\n";
$message_body .= "المشتري: {$rfq['fname']} {$rfq['lname']}\n";
$message_body .= "هاتف المشتري: {$rfq['mobile1']}\n";
$message_body .= "بريد المشتري: {$rfq['email']}\n\n";
$message_body .= "يرجى تقديم عرض سعرك من خلال الرابط: $secure_link";

$msg_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
            VALUES ($admin_id, $supplier_id, '$subject', '$message_body', NOW(), 1, 1, 'whatsapp_rfq', $rfq_id)";

if (mysqli_query($con, $msg_sql)) {
    error_log("✅ تم إدراج رسالة للمورد: supplier_id=$supplier_id, rfq_id=$rfq_id");
} else {
    error_log("❌ فشل إدراج رسالة للمورد: " . mysqli_error($con));
}

// ============================================
// 3. إنشاء عرض أولي في جدول offers
// ============================================
//$check_offer = mysqli_query($con, "SELECT id FROM offers WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id");
//if (mysqli_num_rows($check_offer) == 0) {
    //$insert_offer = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
                     //VALUES ($rfq_id, $supplier_id, $buyer_id, 0, 'USD', 0, '', 'pending', NOW(), 0)";
    //mysqli_query($con, $insert_offer);
//}

// ============================================
// 4. تحديث حالة الطلب
// ============================================
mysqli_query($con, "UPDATE buy_requirement SET wa_status = 'sent_to_supplier', wa_sent_count = wa_sent_count + 1, wa_last_sent_date = NOW() WHERE br_id = $rfq_id");

echo json_encode([
    'success' => true,
    'whatsapp_url' => $whatsapp_url,
    'supplier_id' => $supplier_id,
    'rfq_id' => $rfq_id
]);
?>