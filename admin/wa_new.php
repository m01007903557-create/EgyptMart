<?php
require_once dirname(__DIR__) . '/lib/connect.php';

session_start();

if (empty($_SESSION['ad_id_indm'])) {
    die('غير مصرح بالدخول');
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (!$rfq_id) {
    die('RFQ ID مطلوب');
}

// جلب رقم المورد الصحيح من جدول user
$sql = "SELECT u.mobile1 as supplier_mobile
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON p.pd_uid = u.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

$phone = $row['supplier_mobile'] ?? '';

if (empty($phone)) {
    die('رقم جوال المورد غير موجود');
}

// تنظيف رقم الجوال
$phone = preg_replace('/[^0-9]/', '', $phone);
if (substr($phone, 0, 1) == '0') {
    $phone = '20' . substr($phone, 1);
}
if (strlen($phone) == 9) {
    $phone = '20' . $phone;
}

$message = urlencode("📦 طلب شراء جديد RFQ #$rfq_id من إدارة المنصة. الرجاء تقديم عرض سعرك.");
$url = "https://wa.me/$phone?text=$message";


// =============================================
// إدراج رسالة في جدول message (لظهورها في my-enquiries.php)
// =============================================
session_start();
$admin_id = $_SESSION['ad_id_indm'] ?? 1;

// جلب بيانات الطلب والمورد (نفس الاستعلام الموجود)
$sql = "SELECT 
            br.br_id,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            p.pd_title,
            p.pd_uid as supplier_id,
            u.fname, u.lname, u.email
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if ($rfq) {
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
}
// =============================================


header('Location: ' . $url);
exit;
?>