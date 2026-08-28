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

$sql = "SELECT br.*, 
               p.pd_title, p.pd_uid as supplier_id,
               u.fname, u.lname, u.mobile1, u.email,
               bp.bnsprof_comp_url
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

$insert_sql = "INSERT INTO whatsapp_rfq_messages (rfq_id, product_id, supplier_id, buyer_id, buyer_phone, quantity_required, notes, status, source, created_date) 
               VALUES ($rfq_id, $product_id, $supplier_id, $buyer_id, '$buyer_phone', '$quantity', '$notes', 'new', 'whatsapp_rfq', NOW())";

if (mysqli_query($con, $insert_sql)) {
    
    // ========== ضع رقم جوالك الشخصي هنا ==========
    // ... (بعد تنفيذ الاستعلام وجلب بيانات الطلب) ...

// --- تحضير رقم الهاتف (نأخذه من قاعدة البيانات) ---
$supplier_phone_raw = $rfq['mobile1'] ?? ''; // جلب الرقم من قاعدة البيانات

// تنظيف الرقم: إزالة كل شيء ما عدا الأرقام وعلامة + (إن وجدت)
$supplier_phone_clean = preg_replace('/[^0-9+]/', '', $supplier_phone_raw);

// إذا كان الرقم يبدأ بـ 0، نحوله إلى الصيغة الدولية (+20)
if (preg_match('/^0/', $supplier_phone_clean)) {
    $supplier_phone_clean = '20' . substr($supplier_phone_clean, 1);
}
// إذا كان الرقم لا يبدأ بـ 20 أو +20، نضيف 20
if (!preg_match('/^(\+20|20)/', $supplier_phone_clean)) {
    $supplier_phone_clean = '20' . $supplier_phone_clean;
}
// نضيف علامة + في البداية إذا لم تكن موجودة
if (!preg_match('/^\+/', $supplier_phone_clean)) {
    $supplier_phone_clean = '+' . $supplier_phone_clean;
}

// --- بناء الرسالة ---
$message_text = "📦 *طلب شراء جديد عبر نظام الـ RFQ*\n\n";
$message_text .= "*المنتج:* " . ($rfq['pd_title'] ?? '') . "\n";
$message_text .= "*الكمية:* " . $quantity . "\n";
$message_text .= "*التفاصيل:* " . substr($notes, 0, 150) . (strlen($notes) > 150 ? '...' : '') . "\n\n";
$message_text .= "*رقم الطلب:* " . $rfq_id . "\n";
$message_text .= "*رابط الطلب:* https://egyptmart.shop/my-enquiries.php \n\n";
$message_text .= "يرجى تقديم عرض سعرك المناسب للمنتج أعلاه.";

// إنشاء رابط واتساب
$whatsapp_url = "https://wa.me/" . ltrim($supplier_phone_clean, '+') . "?text=" . urlencode($message_text);

// ... (باقي الكود الذي يرسل الرد JSON) ...
    echo json_encode([
        'success' => true, 
        'message' => 'تم إرسال الطلب للمورد بنجاح',
        'whatsapp_url' => $whatsapp_url
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]);
}
?>