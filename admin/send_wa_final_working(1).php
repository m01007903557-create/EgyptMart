<?php
header('Content-Type: application/json');
error_reporting(0);

$host = 'localhost';
$user = 'u397968200_arabuser';
$pass = 'Ryham@80';
$db = 'u397968200_egmart';

$con = mysqli_connect($host, $user, $pass, $db);
if (!$con) {
    echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات']);
    exit;
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (!$rfq_id) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// جلب بيانات الطلب والمورد والمشتري
$sql = "SELECT br.*, 
               p.pd_title, p.pd_uid as supplier_id,
               u.fname, u.lname, u.mobile1, u.email,
               bp.bnsprof_comp_url
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
        WHERE br.br_id = $rfq_id AND br.communication_type = 'whatsapp'
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// =============================================
// 1️⃣ إدراج الرسالة في جدول message (لظهورها في Inbox المورد)
// =============================================
$admin_id = $_SESSION['ad_id_indm'] ?? 1; // معرف الأدمن
$supplier_id = $rfq['supplier_id'];
$subject = "📦 طلب شراء جديد عبر WhatsApp RFQ #{$rfq['br_id']}";
$body = "المنتج: {$rfq['pd_title']}\n";
$body .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
$body .= "التفاصيل: {$rfq['br_requirement']}\n\n";
$body .= "بيانات المشتري:\n";
$body .= "- الاسم: {$rfq['fname']} {$rfq['lname']}\n";
$body .= "- الجوال: {$rfq['mobile1']}\n";
$body .= "- البريد: {$rfq['email']}\n\n";
$body .= "يرجى تقديم عرض سعرك عبر الرد على هذه الرسالة.";

$insert_sql = "INSERT INTO message 
               (msg_from, msg_to, msg_subject, msg_body, msg_date, msg_to_status, msg_read) 
               VALUES (
                   $admin_id,
                   $supplier_id,
                   '$subject',
                   '$body',
                   NOW(),
                   1,
                   0
               )";
@mysqli_query($con, $insert_sql);

// =============================================
// 2️⃣ رابط واتساب للمورد
// =============================================
$phone = preg_replace('/[^0-9]/', '', $rfq['mobile1'] ?? '');
if (strlen($phone) == 10 && substr($phone, 0, 2) == '10') {
    $phone = '20' . $phone;
} elseif (strlen($phone) == 11 && substr($phone, 0, 1) == '0') {
    $phone = '20' . substr($phone, 1);
} else {
    $phone = '201033921400';
}

$message = "📦 طلب شراء جديد RFQ #{$rfq['br_id']}\n\n";
$message .= "المنتج: {$rfq['pd_title']}\n";
$message .= "الكمية: {$rfq['br_estimate_qty']} {$rfq['br_estimate_qty_unit']}\n";
$message .= "التفاصيل: " . substr($rfq['br_requirement'], 0, 150) . "\n\n";
$message .= "يرجى تقديم عرض سعرك عبر المنصة.";

$whatsapp_url = "https://wa.me/" . $phone . "?text=" . urlencode($message);

echo json_encode([
    'success' => true,
    'whatsapp_url' => $whatsapp_url,
    'rfq_id' => $rfq_id,
    'message_saved' => true
], JSON_UNESCAPED_UNICODE);
?>