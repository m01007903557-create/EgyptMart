<?php
session_start();
require_once "../common.php";

header('Content-Type: application/json');

if (empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if ($rfq_id == 0) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// جلب بيانات الطلب والمورد والمشتري
$sql = "SELECT 
            br.br_id,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            br.br_u_id,
            p.pd_title,
            p.pd_image,
            p.pd_uid as supplier_id,
            u.mobile1 as supplier_mobile,
            u.fname as supplier_fname,
            u.lname as supplier_lname
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON p.pd_uid = u.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// تنظيف رقم الجوال
$supplier_phone = $rfq['supplier_mobile'] ?? '';
$supplier_phone = preg_replace('/[^0-9]/', '', $supplier_phone);
if (substr($supplier_phone, 0, 1) == '0') {
    $supplier_phone = '20' . substr($supplier_phone, 1);
}
if (strlen($supplier_phone) == 10) {
    $supplier_phone = '20' . $supplier_phone;
}

// وحدة القياس
$unit_name = '';
if (!empty($rfq['br_estimate_qty_unit'])) {
    $unit_sql = mysqli_query($con, "SELECT mu_name FROM measurement_unit WHERE mu_id = " . (int)$rfq['br_estimate_qty_unit']);
    $unit_row = mysqli_fetch_assoc($unit_sql);
    $unit_name = $unit_row['mu_name'] ?? '';
}

// صورة المنتج
$product_image = '';
if (!empty($rfq['pd_image'])) {
    $images = explode(',', $rfq['pd_image']);
    $product_image = 'https://egyptmart.shop/upload/myproduct/' . trim($images[0]);
}

// =============================================
// إدراج رسالة في Dashboard المورد (ببيانات المشتري)
// =============================================

// جلب بيانات المشتري
$buyer_sql = mysqli_query($con, "SELECT u.fname, u.lname, u.mobile1, u.email, u.country, bp.bnsprof_compname 
                                  FROM user u 
                                  LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid 
                                  WHERE u.usr_id = " . (int)$rfq['br_u_id']);
$buyer_data = mysqli_fetch_assoc($buyer_sql);

$buyer_company = $buyer_data['bnsprof_compname'] ?? '';
$buyer_name = ($buyer_data['fname'] ?? '') . ' ' . ($buyer_data['lname'] ?? '');
$buyer_mobile = $buyer_data['mobile1'] ?? '';
$buyer_email = $buyer_data['email'] ?? '';
$buyer_country = get_country_name($buyer_data['country'] ?? 0);

// بناء رسالة منظمة للمورد
$subject = "طلب شراء جديد - RFQ #" . $rfq['br_id'];

$body = "";
$body .= "طلب شراء جديد RFQ #" . $rfq['br_id'] . "\n";
$body .= "----------------------------------------\n";
$body .= "المنتج: " . ($rfq['pd_title'] ?? '') . "\n";
$body .= "الكمية: " . $rfq['br_estimate_qty'] . " " . $unit_name . "\n";
$body .= "تفاصيل الطلب: " . substr($rfq['br_requirement'], 0, 200) . "\n";
$body .= "----------------------------------------\n";
$body .= "بيانات المشتري:\n";
$body .= "• الشركة: " . $buyer_company . "\n";
$body .= "• شخص الاتصال: " . $buyer_name . "\n";
$body .= "• الجوال: " . $buyer_mobile . "\n";
$body .= "• البريد الإلكتروني: " . $buyer_email . "\n";
$body .= "• البلد: " . $buyer_country . "\n";
$body .= "----------------------------------------\n";
$body .= "تنبيه: يرجى الرد على المشتري في وقت قصير.\n";
$body .= "----------------------------------------";

$subject_clean = mysqli_real_escape_string($con, $subject);
$body_clean = mysqli_real_escape_string($con, $body);

$admin_id = $_SESSION['ad_id_indm'];
$supplier_id = $rfq['supplier_id'];

$insert_sql = "INSERT INTO message 
               (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
               VALUES (
                   $admin_id,
                   $supplier_id,
                   '$subject_clean',
                   '$body_clean',
                   NOW(),
                   1,
                   1,
                   'whatsapp_rfq',
                   {$rfq['br_id']}
               )";
@mysqli_query($con, $insert_sql);

// =============================================
// بناء رسالة واتساب (كاملة بالرابط والصورة)
// =============================================

$magic_link = "https://egyptmart.shop/my-enquiries.php?rfq_id=" . $rfq['br_id'];

$wa_message = "📦 *طلب شراء جديد #" . $rfq['br_id'] . "*\n\n";
$wa_message .= "*المنتج:* " . ($rfq['pd_title'] ?? '') . "\n";
if (!empty($product_image)) {
    $wa_message .= "*رابط صورة المنتج:* " . $product_image . "\n";
}
$wa_message .= "*التفاصيل:* " . substr($rfq['br_requirement'], 0, 200) . "\n";
$wa_message .= "*الكمية:* " . $rfq['br_estimate_qty'] . " " . $unit_name . "\n\n";
$wa_message .= "*للاستفسار عن هذا الطلب، يرجى تسجيل الدخول إلى حسابك ثم فتح الرابط:*\n";
$wa_message .= $magic_link . "\n\n";
$wa_message .= "يمكنك أيضاً الرد على هذه الرسالة للتواصل المباشر.";

echo json_encode([
    'success' => true,
    'phone' => $supplier_phone,
    'message' => $wa_message,
    'rfq_id' => $rfq_id,
    'product_image' => $product_image,
    'magic_link' => $magic_link
]);
?>