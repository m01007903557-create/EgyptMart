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
               p.pd_title, p.pd_uid as supplier_id, p.pd_id as product_id,
               u.fname, u.lname, u.mobile1, u.email,
               bp.bnsprof_comp_url, bp.bnsprof_email
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
// 1️⃣ حفظ الطلب في جدول whatsapp_rfq_messages (لظهوره في my-enquiries.php)
// =============================================
$quantity = mysqli_real_escape_string($con, $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit']);
$notes = mysqli_real_escape_string($con, $rfq['br_requirement']);

// إنشاء الجدول إذا لم يكن موجوداً
mysqli_query($con, "CREATE TABLE IF NOT EXISTS whatsapp_rfq_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfq_id INT,
    product_id INT,
    supplier_id INT,
    buyer_id INT,
    buyer_phone VARCHAR(50),
    quantity_required VARCHAR(100),
    notes TEXT,
    status VARCHAR(50) DEFAULT 'new',
    source VARCHAR(50) DEFAULT 'whatsapp_rfq',
    created_date DATETIME
)");

$insert_sql = "INSERT INTO whatsapp_rfq_messages 
               (rfq_id, product_id, supplier_id, buyer_id, buyer_phone, quantity_required, notes, status, source, created_date) 
               VALUES (
                   {$rfq['br_id']}, 
                   {$rfq['product_id']}, 
                   {$rfq['supplier_id']}, 
                   {$rfq['br_u_id']},
                   '{$rfq['mobile1']}', 
                   '$quantity', 
                   '$notes', 
                   'new', 
                   'whatsapp_rfq', 
                   NOW()
               )";
@mysqli_query($con, $insert_sql);

// =============================================
// 2️⃣ إرسال إيميل للمورد (نسخة احتياطية)
// =============================================
$supplier_email = $rfq['bnsprof_email'] ?? $rfq['email'] ?? '';
if (!empty($supplier_email)) {
    $subject = "📱 طلب شراء جديد عبر WhatsApp | RFQ #{$rfq['br_id']}";
    $body = "مرحباً بالمورد,\n\n";
    $body .= "✅ تم استلام طلب شراء جديد من خلال نظام WhatsApp RFQ.\n";
    $body .= "───────────────────\n";
    $body .= "المنتج: {$rfq['pd_title']}\n";
    $body .= "الكمية: $quantity\n";
    $body .= "التفاصيل: $notes\n";
    $body .= "───────────────────\n\n";
    $body .= "📋 بيانات المشتري:\n";
    $body .= "- الاسم: {$rfq['fname']} {$rfq['lname']}\n";
    $body .= "- الجوال: {$rfq['mobile1']}\n";
    $body .= "- البريد: {$rfq['email']}\n\n";
    $body .= "🔗 رابط الطلب: https://egyptmart.shop/my-enquiries.php\n\n";
    $body .= "يرجى تقديم عرض سعرك المناسب للمنتج أعلاه.";
    
    $headers = "Content-Type: text/plain; charset=UTF-8";
    @mail($supplier_email, $subject, $body, $headers);
}

// =============================================
// 3️⃣ رابط واتساب للمورد (الوظيفة الأصلية)
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
$message .= "المنتج: " . ($rfq['pd_title'] ?? '') . "\n";
$message .= "الكمية: $quantity\n";
$message .= "التفاصيل: " . substr($notes, 0, 150) . "\n\n";
$message .= "يرجى تقديم عرض سعرك عبر المنصة.";

$whatsapp_url = "https://wa.me/" . $phone . "?text=" . urlencode($message);

echo json_encode([
    'success' => true,
    'whatsapp_url' => $whatsapp_url,
    'rfq_id' => $rfq_id,
    'phone' => $phone,
    'saved_to_db' => true,
    'email_sent' => !empty($supplier_email)
], JSON_UNESCAPED_UNICODE);
?>