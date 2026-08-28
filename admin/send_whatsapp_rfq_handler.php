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

// جلب بيانات الطلب والمورد والمشتري
$sql = "SELECT br.*, 
               p.pd_title, p.pd_uid as supplier_id,
               u.fname, u.lname, u.mobile1, u.email,
               bp.bnsprof_comp_url, bp.bnsprof_email
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
        WHERE br.br_id = $rfq_id AND br.communication_type = 'whatsapp'";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

$supplier_id = $rfq['supplier_id'];
$buyer_id = $rfq['br_u_id'];
$buyer_phone = $rfq['mobile1'];
$product_id = $rfq['br_pc_id'];
$quantity = $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit'];
$notes = $rfq['br_requirement'];

// 1️⃣ حفظ الطلب في جدول whatsapp_rfq_messages
$insert_sql = "INSERT INTO whatsapp_rfq_messages 
               (rfq_id, product_id, supplier_id, buyer_id, buyer_phone, quantity_required, notes, status, source, created_date) 
               VALUES ($rfq_id, $product_id, $supplier_id, $buyer_id, '$buyer_phone', '$quantity', '$notes', 'new', 'whatsapp_rfq', NOW())";

if (!mysqli_query($con, $insert_sql)) {
    echo json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]);
    exit;
}

// --------------------------------------------------------
// 2️⃣ إرسال إلى Dashboard Supplier (my-enquiries.php)
// --------------------------------------------------------
$supplier_msg_sql = "INSERT INTO supplier_messages 
                     (supplier_id, rfq_id, message, buyer_name, buyer_phone, buyer_email, product_name, quantity, requirements, source, created_at) 
                     VALUES (
                         $supplier_id,
                         $rfq_id,
                         'طلب شراء جديد عبر WhatsApp RFQ',
                         '{$rfq['fname']} {$rfq['lname']}',
                         '{$rfq['mobile1']}',
                         '{$rfq['email']}',
                         '{$rfq['pd_title']}',
                         '$quantity',
                         '$notes',
                         'whatsapp_rfq',
                         NOW()
                     )";
mysqli_query($con, $supplier_msg_sql);

// --------------------------------------------------------
// 3️⃣ إرسال إيميل للمورد (مع إشارة واضحة بمصدر WhatsApp)
// --------------------------------------------------------
$supplier_email = $rfq['bnsprof_email'] ?? '';
if (!empty($supplier_email)) {
    $email_subject = "📱 طلب شراء جديد عبر WhatsApp | RFQ #$rfq_id";
    $email_message = "مرحباً بالمورد،\n\n";
    $email_message .= "✅ تم استلام طلب شراء جديد من خلال نظام WhatsApp RFQ.\n";
    $email_message .= "🔹 المنتج: " . ($rfq['pd_title'] ?? '') . "\n";
    $email_message .= "🔹 الكمية: $quantity\n";
    $email_message .= "🔹 التفاصيل: $notes\n\n";
    $email_message .= "🔹 بيانات المشتري (للمراسلة بعد القبول):\n";
    $email_message .= "   - الاسم: {$rfq['fname']} {$rfq['lname']}\n";
    $email_message .= "   - الجوال: {$rfq['mobile1']}\n";
    $email_message .= "   - البريد: {$rfq['email']}\n\n";
    $email_message .= "🔗 رابط الطلب في لوحة التحكم الخاصة بك:\n";
    $email_message .= "https://egyptmart.shop/my-enquiries.php?source=whatsapp\n\n";
    $email_message .= "يرجى تقديم عرض سعرك للمنتج أعلاه.";
    
    $headers = "Content-Type: text/plain; charset=UTF-8";
    @mail($supplier_email, $email_subject, $email_message, $headers);
}

// --------------------------------------------------------
// 4️⃣ فتح واتساب المورد يدوياً (بنفس طريقة المشتري)
// --------------------------------------------------------
$supplier_phone = $rfq['mobile1'] ?? '';

// تنظيف الرقم للاستخدام في wa.me
$supplier_phone_clean = preg_replace('/[^0-9]/', '', $supplier_phone);
if (substr($supplier_phone_clean, 0, 2) == '20') {
    $supplier_phone_clean = substr($supplier_phone_clean, 2);
}
$supplier_phone_clean = '20' . $supplier_phone_clean;

$whatsapp_message = "📦 طلب شراء جديد عبر المنصة (RFQ #$rfq_id)\n\n";
$whatsapp_message .= "المنتج: " . ($rfq['pd_title'] ?? '') . "\n";
$whatsapp_message .= "الكمية: $quantity\n";
$whatsapp_message .= "التفاصيل: " . substr($notes, 0, 150) . "\n\n";
$whatsapp_message .= "🔗 رابط الطلب (للمراسلة والعرض):\n";
$whatsapp_message .= "https://egyptmart.shop/my-enquiries.php?source=whatsapp\n\n";
$whatsapp_message .= "يرجى تقديم عرض سعرك للمنتج أعلاه.";

$whatsapp_url = "https://wa.me/" . $supplier_phone_clean . "?text=" . urlencode($whatsapp_message);

// ✅ الرد النهائي
echo json_encode([
    'success' => true,
    'message' => 'تم إرسال الطلب إلى المورد (Dashboard + إيميل + رابط واتساب)',
    'whatsapp_url' => $whatsapp_url,
    'supplier_phone' => $supplier_phone_clean
]);