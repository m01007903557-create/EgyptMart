<?php
/**
 * admin/send_to_supplier_handler.php - معالج إرسال الطلب للمورد
 */

session_start();
require_once '../lib/connect.php';
require_once "../common.php";

header('Content-Type: application/json');

// ✅ التحقق من تسجيل الدخول
check_admin_login();

if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$rfq_id = isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0;
if (!$rfq_id) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// ✅ جلب بيانات الطلب
$sql = "SELECT br.*, 
               u.fname, u.lname, u.mobile1, u.email, u.usr_id as buyer_id,
               p.pd_title, p.pd_uid as supplier_id,
               su.mobile1 as supplier_phone,
               br.source_platform
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

// ✅ تحديد رقم المورد حسب المصدر (بدون أي رقم افتراضي)
$supplier_phone = '';
$supplier_id = 0;

if ($rfq['source_platform'] == 'saleoffer') {
    // ✅ عروض البيع: استخدام so_usr_id
    $offer_id = (int)($rfq['br_pc_id'] ?? 0);
    if ($offer_id > 0) {
        $offer_sql = "SELECT so_usr_id FROM sale_offer WHERE so_id = $offer_id LIMIT 1";
        $offer_res = mysqli_query($con, $offer_sql);
        $offer_data = mysqli_fetch_assoc($offer_res);
        if ($offer_data && !empty($offer_data['so_usr_id'])) {
            $supplier_id = (int)$offer_data['so_usr_id'];
            $user_sql = "SELECT mobile1 FROM user WHERE usr_id = $supplier_id LIMIT 1";
            $user_res = mysqli_query($con, $user_sql);
            $user_data = mysqli_fetch_assoc($user_res);
            if ($user_data && !empty($user_data['mobile1'])) {
                $supplier_phone = $user_data['mobile1'];
            }
        }
    }
} else {
    // ✅ المنتجات العادية: استخدام pd_uid
    $supplier_id = $rfq['pd_uid'] ?? 0;
    if (empty($supplier_id)) {
        $supplier_id = $rfq['supplier_id'] ?? 0;
    }
    $supplier_phone = $rfq['supplier_phone'] ?? '';
    if (empty($supplier_phone)) {
        $supplier_phone = $rfq['mobile1'] ?? '';
    }
}

// ✅ التحقق من وجود رقم الهاتف (بدون افتراضي)
if (empty($supplier_phone)) {
    error_log("❌ No supplier phone found for RFQ $rfq_id");
    echo json_encode(['success' => false, 'error' => 'رقم هاتف المورد غير موجود']);
    exit;
}

// ✅ بناء رسالة الطلب
$product_name = $rfq['pd_title'] ?? $rfq['br_pd_name'] ?? 'غير محدد';
$message = "📦 طلب شراء جديد #$rfq_id\n";
$message .= "🛒 المنتج: $product_name\n";
$message .= "📝 التفاصيل: " . ($rfq['br_requirement'] ?? '') . "\n";
$message .= "🔗 رابط الطلب: https://" . $_SERVER['HTTP_HOST'] . "/admin/whatsapp_rfq.php";

// ✅ تنظيف رقم الهاتف
$cleanPhone = preg_replace('/[^0-9]/', '', $supplier_phone);
if (substr($cleanPhone, 0, 1) == '0') {
    $cleanPhone = '20' . substr($cleanPhone, 1);
}
if (!substr($cleanPhone, 0, 2) == '20') {
    $cleanPhone = '20' . $cleanPhone;
}

// ✅ بناء رابط واتساب
$wa_url = "https://api.whatsapp.com/send?phone=" . $cleanPhone . "&text=" . urlencode($message);

// ✅ تحديث حالة الطلب
$update_sql = "UPDATE buy_requirement SET 
    wa_status = 'sent_to_supplier',
    wa_sent_count = wa_sent_count + 1,
    wa_last_sent_date = NOW()
WHERE br_id = $rfq_id";
mysqli_query($con, $update_sql);

// ✅ إرسال إشعار للمورد
if ($supplier_id > 0) {
    $subject = "طلب شراء جديد #$rfq_id";
    $body = "لديك طلب شراء جديد:\n\n";
    $body .= "المنتج: $product_name\n";
    $body .= "التفاصيل: " . ($rfq['br_requirement'] ?? '') . "\n";
    $body .= "رابط الطلب: https://" . $_SERVER['HTTP_HOST'] . "/admin/whatsapp_rfq.php";
    
    $subject_safe = mysqli_real_escape_string($con, $subject);
    $body_safe = mysqli_real_escape_string($con, $body);
    
    $notify_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                   VALUES (0, $supplier_id, '$subject_safe', '$body_safe', NOW(), 1, 1, 'whatsapp_rfq', $rfq_id)";
    mysqli_query($con, $notify_sql);
}

// ✅ سجل للتصحيح
error_log("✅ WhatsApp sent: RFQ=$rfq_id, source=" . ($rfq['source_platform'] ?? 'regular') . ", phone=$supplier_phone");

echo json_encode(['success' => true, 'whatsapp_url' => $wa_url]);
exit;
?>