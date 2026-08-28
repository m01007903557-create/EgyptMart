<?php
// سجل بداية التنفيذ
error_log("=== send_wa_final_working.php started for RFQ ID: $rfq_id ===");

require_once dirname(__DIR__) . '/lib/connect.php';
session_start();

if (empty($_SESSION['ad_id_indm'])) {
    die('غير مصرح بالدخول');
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (!$rfq_id) {
    die('RFQ ID مطلوب');
}

// جلب بيانات الطلب والمورد والمشتري
$sql = "SELECT 
            br.br_id,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            p.pd_title,
            p.pd_uid as supplier_id,
            u.fname, u.lname, u.email,
            u.mobile1 AS buyer_mobile,
            supplier_user.mobile1 AS supplier_mobile
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN user supplier_user ON p.pd_uid = supplier_user.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    die('الطلب غير موجود');
}

// =============================================
// 1️⃣ إدراج رسالة في جدول message (لظهورها في my-enquiries.php)
// =============================================
// إدراج رسالة في جدول message (لظهورها في my-enquiries.php)
$admin_id = $_SESSION['ad_id_indm'] ?? 1;
$supplier_id = $rfq['supplier_id'];
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
                   $supplier_id,
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
// كود التصحيح: التحقق من إدراج الرسالة
// =============================================
$check_sql = "SELECT * FROM message WHERE msg_to = $supplier_id ORDER BY msg_date DESC LIMIT 1";
$check_result = mysqli_query($con, $check_sql);
$check_row = mysqli_fetch_assoc($check_result);
error_log("WhatsApp RFQ - Last message for supplier $supplier_id: " . print_r($check_row, true));
error_log("WhatsApp RFQ - Insert SQL was: " . $insert_sql);
// =============================================





// إدراج رسالة في جدول message
$insert_sql = "INSERT INTO message 
               (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
               VALUES (
                   $admin_id,
                   $supplier_id,
                   '$subject',
                   '$message_body',
                   NOW(),
                   1,
                   1,
                   'whatsapp_rfq',
                   $rfq_id
               )";
$insert_result = mysqli_query($con, $insert_sql);

if ($insert_result) {
    error_log("WhatsApp RFQ: Insert succeeded for RFQ #$rfq_id, supplier_id: $supplier_id");
} else {
    error_log("WhatsApp RFQ: Insert FAILED for RFQ #$rfq_id");
    error_log("WhatsApp RFQ: MySQL Error: " . mysqli_error($con));
}


if ($insert_result) {
    error_log("WhatsApp RFQ message inserted for RFQ #$rfq_id, supplier_id: $supplier_id");
    // تأكد من عدد الصفوف المتأثرة
    error_log("Affected rows: " . mysqli_affected_rows($con));
} else {
    error_log("Failed to insert WhatsApp RFQ message: " . mysqli_error($con));
}

// تأكد من وجود العمود msg_entity
$check_column = mysqli_query($con, "SHOW COLUMNS FROM message LIKE 'msg_entity'");
if (mysqli_num_rows($check_column) == 0) {
    error_log("ERROR: Column 'msg_entity' does not exist in message table");
}


// =============================================
// 2️⃣ رابط واتساب للمورد (إرسال يدوي عبر wa.me)
// =============================================
$phone = $rfq['supplier_mobile'] ?? '';
$phone = preg_replace('/[^0-9]/', '', $phone);
if (substr($phone, 0, 1) == '0') {
    $phone = '20' . substr($phone, 1);
}
if (strlen($phone) == 10) {
    $phone = '20' . $phone;
}


$message = "📦 طلب شراء جديد RFQ #{$rfq['br_id']}\n\n";
$message .= "المنتج: {$rfq['pd_title']}\n";
$message .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
$message .= "التفاصيل: " . substr($rfq['br_requirement'], 0, 150) . "\n\n";
$message .= "يرجى تقديم عرض سعرك عبر المنصة.";

$whatsapp_url = "https://wa.me/" . $phone . "?text=" . urlencode($message);

// =============================================
// 3️⃣ توجيه المستخدم إلى واتساب
// =============================================
// سجل قبل التوجيه
error_log("=== About to redirect to WhatsApp. Insert should have been done. ===");

header('Location: ' . $whatsapp_url);
exit;
?>