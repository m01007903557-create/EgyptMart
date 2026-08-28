<?php
require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

// التحقق من أن المستخدم أدمن
if (empty($_SESSION['ad_id_indm'])) {
    die(json_encode(['success' => false, 'error' => 'غير مصرح بالدخول']));
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (!$rfq_id) {
    die(json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']));
}

// جلب بيانات الطلب والمورد
$sql = "SELECT 
            br.br_id,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            p.pd_title,
            p.pd_uid as supplier_id,
            u.fname, u.lname, u.email,
            supplier_user.mobile1 AS supplier_mobile
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN user supplier_user ON p.pd_uid = supplier_user.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq || empty($rfq['supplier_mobile'])) {
    die(json_encode(['success' => false, 'error' => 'رقم المورد غير موجود']));
}

// تنظيف رقم الجوال (للاستخدام مع API)
$supplier_phone = preg_replace('/[^0-9]/', '', $rfq['supplier_mobile']);
if (substr($supplier_phone, 0, 1) == '0') {
    $supplier_phone = substr($supplier_phone, 1);
}
$supplier_phone = '20' . $supplier_phone;

// إدراج رسالة في جدول message (لظهورها في my-enquiries.php)
$admin_id = $_SESSION['ad_id_indm'];
$subject = "📦 طلب شراء جديد عبر WhatsApp RFQ #{$rfq['br_id']}";
$message_body = "المنتج: {$rfq['pd_title']}\n";
$message_body .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
$message_body .= "التفاصيل: {$rfq['br_requirement']}\n\n";
$message_body .= "بيانات المشتري:\n";
$message_body .= "- الاسم: {$rfq['fname']} {$rfq['lname']}\n";
$message_body .= "- البريد: {$rfq['email']}";

$insert_sql = "INSERT INTO message 
               (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
               VALUES (
                   $admin_id,
                   {$rfq['supplier_id']},
                   '$subject',
                   '$message_body',
                   NOW(),
                   1,
                   1,
                   'whatsapp_rfq',
                   {$rfq['br_id']}
               )";
mysqli_query($con, $insert_sql);

// =============================================
// إرسال رسالة واتساب تلقائيًا عبر API
// =============================================
$access_token = 'EAASvKRi9KocBRrybrypjfyEjDMYKgafmEiSSELZCHKpyABComZAmJA9aB7M1BIl1IEd6PLTF2p5t7mBa9s1OUV6VYWkwdQZBGAApxHXfz28AcZCAZCghfskcITVAvK8WNKKiC03rkypn9a7a0qFiZBIQLyNHkSW7pcCPBZAATuLc82NUK3mtpExrAMfYJluN89BJj42kjgIrbZAKVQjZCUEJ1ZBhZAhzEW2sqkZAyfN7MgNHuDRF2mxCcyB0B2ThF9kZC6Yc4NUMY7GS3uG7zrPJSH2WAZAqe4';
$phone_number_id = '1203497699502465';

// بناء نص الرسالة
$message = "📦 طلب شراء جديد عبر المنصة\n\n";
$message .= "RFQ #: {$rfq['br_id']}\n";
$message .= "المنتج: {$rfq['pd_title']}\n";
$message .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
$message .= "التفاصيل: " . substr($rfq['br_requirement'], 0, 200) . "\n\n";
$message .= "يرجى تقديم عرض سعرك عبر المنصة.";

// إرسال عبر WhatsApp Cloud API
$url = "https://graph.facebook.com/v20.0/{$phone_number_id}/messages";

$data = [
    'messaging_product' => 'whatsapp',
    'to' => $supplier_phone,
    'type' => 'text',
    'text' => ['body' => $message]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// الرد النهائي
if ($http_code == 200) {
    echo json_encode(['success' => true, 'message' => 'تم إرسال الطلب للمورد عبر واتساب بنجاح', 'api_response' => json_decode($response)]);
} else {
    echo json_encode(['success' => false, 'error' => 'فشل إرسال واتساب', 'api_response' => json_decode($response)]);
}
?>