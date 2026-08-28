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

// ✅ دعم GET و POST للاختبار
$rfq_id = isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0;
if ($rfq_id == 0 && isset($_GET['rfq_id'])) {
    $rfq_id = (int)$_GET['rfq_id'];
}

if (!$rfq_id) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// ✅ جلب بيانات الطلب
$sql = "SELECT br.*, 
               br.source_platform,
               br.br_pc_id,
               br.br_pd_name,
               br.br_requirement
        FROM buy_requirement br
        WHERE br.br_id = $rfq_id";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// ✅ ============================================================
// ✅ تحديد رقم المورد
// ✅ ============================================================
$supplier_phone = '';
$supplier_id = 0;

if ($rfq['source_platform'] == 'saleoffer') {
    // ✅ عروض البيع: استخدام so_usr_id
    $offer_id = (int)($rfq['br_pc_id'] ?? 0);
    if ($offer_id > 0) {
        $offer_sql = "SELECT so_usr_id FROM sale_offer WHERE so_id = $offer_id LIMIT 1";
        $offer_res = mysqli_query($con, $offer_sql);
        $offer_data = mysqli_fetch_assoc($offer_res);
        
        if ($offer_data && !empty($offer_data['so_usr_id']) && $offer_data['so_usr_id'] > 0) {
            $supplier_id = (int)$offer_data['so_usr_id'];
            // ✅ جلب رقم الهاتف من جدول user
            $user_sql = "SELECT mobile1 FROM user WHERE usr_id = $supplier_id LIMIT 1";
            $user_res = mysqli_query($con, $user_sql);
            $user_data = mysqli_fetch_assoc($user_res);
            
            if ($user_data && !empty($user_data['mobile1'])) {
                $supplier_phone = $user_data['mobile1'];
            } else {
                // ✅ استخدام الرقم الافتراضي لإيهاب
                $supplier_phone = '1007903557';
                error_log("⚠️ mobile1 is empty for user_id: $supplier_id, using default: 1007903557");
            }
        } else {
            error_log("❌ so_usr_id is missing or zero for offer_id: $offer_id");
            echo json_encode(['success' => false, 'error' => 'العرض غير مرتبط بمورد. يرجى تحديث العرض في لوحة التحكم.']);
            exit;
        }
    } else {
        error_log("❌ offer_id is zero for RFQ $rfq_id");
        echo json_encode(['success' => false, 'error' => 'معرف العرض غير صالح']);
        exit;
    }
} else {
    // ✅ المنتجات العادية: استخدام pd_uid
    $product_sql = "SELECT pd_uid FROM products WHERE pd_id = " . (int)$rfq['br_pc_id'] . " LIMIT 1";
    $product_res = mysqli_query($con, $product_sql);
    $product_data = mysqli_fetch_assoc($product_res);
    if ($product_data && !empty($product_data['pd_uid'])) {
        $supplier_id = (int)$product_data['pd_uid'];
        $user_sql = "SELECT mobile1 FROM user WHERE usr_id = $supplier_id LIMIT 1";
        $user_res = mysqli_query($con, $user_sql);
        $user_data = mysqli_fetch_assoc($user_res);
        if ($user_data && !empty($user_data['mobile1'])) {
            $supplier_phone = $user_data['mobile1'];
        }
    }
}

// ✅ التحقق النهائي من رقم الهاتف
if (empty($supplier_phone)) {
    error_log("❌ No supplier phone found for RFQ $rfq_id, using platform number");
    $supplier_phone = '201104832811'; // رقم المنصة كـ fallback
}

// ✅ بناء رسالة الطلب
$product_name = $rfq['br_pd_name'] ?? 'غير محدد';
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

// ✅ ============================================================
// ✅ استخدام api.whatsapp.com لتحميل الرسالة
// ✅ ============================================================
// ✅ استخدام wa.me لفتح المحادثة مباشرة
$wa_url = "https://wa.me/" . $cleanPhone . "?text=" . urlencode($message);

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

error_log("✅ WhatsApp sent: RFQ=$rfq_id, supplier_id=$supplier_id, phone=$supplier_phone, url=$wa_url");


// ✅ إرجاع النتيجة مع phone و message
echo json_encode([
    'success' => true,
    'whatsapp_url' => $wa_url,
    'phone' => $cleanPhone,
    'message' => $message
]);
exit;
?>