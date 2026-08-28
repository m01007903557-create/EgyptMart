<?php
header('Content-Type: application/json');
error_reporting(0);

// بيانات الاتصال الصحيحة
$host = 'localhost';
$user = 'u397968200_arabuser';
$pass = 'Ryham@80';
$db = 'u397968200_egmart';

$con = mysqli_connect($host, $user, $pass, $db);
if (!$con) {
    echo json_encode(['success' => false, 'error' => 'فشل الاتصال بقاعدة البيانات']);
    exit;
}

// استقبال rfq_id من GET (الأسهل والأضمن)
$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if (!$rfq_id) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// استعلام بسيط ومباشر
$sql = "SELECT br.br_id, br.br_estimate_qty, br.br_estimate_qty_unit, br.br_requirement,
               p.pd_title,
               u.mobile1, u.fname, u.lname
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON p.pd_uid = u.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);

if (!$result || mysqli_num_rows($result) == 0) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

$row = mysqli_fetch_assoc($result);

// تنسيق رقم الجوال
// تنسيق رقم الجوال
$phone = $row['mobile1'] ?? '';
$phone = preg_replace('/[^0-9]/', '', $phone);

// إذا كان الرقم 10 أرقام ويبدأ بـ 10 (مثل 1033921400)
if (strlen($phone) == 10 && substr($phone, 0, 2) == '10') {
    $phone = '20' . $phone;  // 201033921400
}
// إذا كان الرقم يبدأ بـ 0
elseif (strlen($phone) == 11 && substr($phone, 0, 1) == '0') {
    $phone = '20' . substr($phone, 1);
}
// إذا كان الرقم 9 أرقام
elseif (strlen($phone) == 9) {
    $phone = '20' . $phone;
}
// إذا كان الرقم بالفعل بالصيغة الدولية
elseif (strlen($phone) == 12 && substr($phone, 0, 2) == '20') {
    $phone = $phone;
}
// في حالة عدم وجود رقم
else {
    $phone = '201033921400'; // رقم تجريبي (استخدم رقم Ahmed)
}

// بناء الرسالة
$message = "📦 طلب شراء جديد RFQ #{$row['br_id']}\n";
$message .= "المنتج: " . ($row['pd_title'] ?? '') . "\n";
$message .= "الكمية: " . ($row['br_estimate_qty'] ?? 0) . ' ' . ($row['br_estimate_qty_unit'] ?? '') . "\n";
$message .= "التفاصيل: " . substr($row['br_requirement'] ?? '', 0, 100);

$whatsapp_url = "https://wa.me/" . $phone . "?text=" . urlencode($message);

echo json_encode([
    'success' => true,
    'whatsapp_url' => $whatsapp_url,
    'rfq_id' => $rfq_id,
    'phone' => $phone
], JSON_UNESCAPED_UNICODE);
?>