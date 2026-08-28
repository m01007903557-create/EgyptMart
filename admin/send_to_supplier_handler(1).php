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

// جلب بيانات الطلب والمورد
$sql = "SELECT br.*, 
               u.fname, u.lname, u.mobile1, u.email, u.usr_id as buyer_id,
               c.cn_name as country_name,
               s.state_name as state_name,
               p.pd_title, p.pd_image, p.pd_uid as supplier_id,
               bp.bnsprof_comp_url, bp.bnsprof_email, bp.bnsprof_mobile1
        FROM buy_requirement br
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN country c ON c.cn_id = u.country
        LEFT JOIN state s ON s.state_id = u.state
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN business_profile bp ON p.pd_uid = bp.bnsprof_uid
        WHERE br.br_id = $rfq_id";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

$supplier_id = $rfq['supplier_id'];
$magic_token = md5($supplier_id . $rfq_id . time());
$expiry = date('Y-m-d H:i:s', strtotime('+7 days'));
mysqli_query($con, "UPDATE buy_requirement SET wa_magic_token = '$magic_token', wa_token_expiry = '$expiry' WHERE br_id = $rfq_id");

$secure_link = "https://" . $_SERVER['HTTP_HOST'] . "/supplier/whatsapp_rfq_view.php?token=$magic_token&id=$rfq_id";

// بيانات الطلب للإرسال
$product_name = $rfq['pd_title'];
$product_image = !empty($rfq['pd_image']) ? "https://" . $_SERVER['HTTP_HOST'] . "/upload/myproduct/" . explode(',', $rfq['pd_image'])[0] : "";
$quantity = $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit'];
$details = $rfq['br_requirement'];
$location = ($rfq['country_name'] ?? '') . ' - ' . ($rfq['state_name'] ?? '');
$date = date('Y-m-d', strtotime($rfq['br_posting_date']));

$message_text = "📦 *New RFQ Request*\n\n";
$message_text .= "*Product:* $product_name\n";
$message_text .= "*Quantity:* $quantity\n";
$message_text .= "*Details:* $details\n";
$message_text .= "*RFQ ID:* $rfq_id\n";
$message_text .= "*Date:* $date\n";
$message_text .= "*Location:* $location\n";
$message_text .= "*View Request:* $secure_link\n\n";
$message_text .= "Reply quickly with your quotation to build buyer trust and improve your supplier visibility.";

// ============================================
// بناء رابط واتساب للمورد (ديناميكي)
// ============================================
// ============================================
// بناء رابط واتساب الرسمي (wa.me)
// ============================================
$supplier_phone = $rfq['bnsprof_mobile1']; // رقم المورد من قاعدة البيانات

if (!empty($supplier_phone)) {
    // 1. تنظيف رقم الهاتف
    $cleanPhone = preg_replace('/[^0-9]/', '', $supplier_phone);
    
    // 2. لو الرقم 10 أرقام يبقى مصري ناقصه 20
    if (strlen($cleanPhone) == 10 && substr($cleanPhone, 0, 1) == '0') {
        $cleanPhone = '20' . substr($cleanPhone, 1);
    }
    // لو 11 رقم وبيبدأ بـ 0 برضو
    elseif (strlen($cleanPhone) == 11 && substr($cleanPhone, 0, 1) == '0') {
        $cleanPhone = '20' . substr($cleanPhone, 1);
    }
    
    // 3. بناء الرسالة - استخدم \n للسطر الجديد
    $wa_message = "📦 طلب شراء جديد #$rfq_id\n";
    $wa_message .= "المنتج: " . $rfq['pd_title'] . "\n";
    $wa_message .= "الكمية: " . $rfq['br_estimate_qty'] . ' ' . $rfq['br_estimate_qty_unit'] . "\n";
    $wa_message .= "المشتري: " . $rfq['fname'] . ' ' . $rfq['lname'] . "\n"; // تم إصلاح النقطة
    $wa_message .= "هاتف المشتري: " . $rfq['mobile1'] . "\n\n";
    $wa_message .= "للتقديم عرض سعر: " . $secure_link;
    
    // 4. أهم خطوة: شفر الرسالة + شفر اللينك اللي جواها
    $wa_message = str_replace($secure_link, rawurlencode($secure_link), $wa_message);
    $whatsapp_url = "https://wa.me/" . $cleanPhone . "?text=" . rawurlencode($wa_message);
    
    $whatsapp_sent = true;
}
error_log("WhatsApp URL: " . $whatsapp_url);

// ============================================
// 2. إرسال Email
// ============================================
$email_sent = false;
$supplier_email = $rfq['bnsprof_email'];
if (!empty($supplier_email)) {
    $email_subject = "New RFQ Request #$rfq_id";
    $email_body = "<html><body dir='rtl'>
    <h2>طلب شراء جديد #$rfq_id</h2>
    <p><strong>المنتج:</strong> $product_name</p>
    <p><strong>الكمية:</strong> $quantity</p>
    <p><strong>التفاصيل:</strong> $details</p>
    <p><strong>التاريخ:</strong> $date</p>
    <p><strong>الموقع:</strong> $location</p>
    <p><a href='$secure_link'>عرض الطلب وإرسال عرض السعر</a></p>
    <hr><p>يرجى الرد بعرض سعرك لبناء الثقة مع المشتري</p>
    </body></html>";
    $headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $email_sent = @mail($supplier_email, $email_subject, $email_body, $headers);
}

// 3. حفظ في Dashboard Supplier Inbox (الجدول القديم)
$dashboard_sent = false;
$sql_inbox = "INSERT INTO supplier_messages (supplier_id, rfq_id, message, buyer_name, buyer_phone, buyer_email, product_name, quantity, requirements, created_at, is_read) 
              VALUES ($supplier_id, $rfq_id, 'طلب شراء جديد - يرجى تقديم عرض سعر', '{$rfq['fname']} {$rfq['lname']}', '{$rfq['mobile1']}', '{$rfq['email']}', '$product_name', '$quantity', '$details', NOW(), 0)";
if (mysqli_query($con, $sql_inbox)) {
    $dashboard_sent = true;
}

// ============================================
// 4. إضافة سجل في جدول message (لرسائل المورد)
// ============================================
$admin_id = $_SESSION['ad_id_indm'] ?? 0;
$subject = "طلب شراء جديد RFQ #$rfq_id";
$message_body = "تم إرسال طلب شراء جديد إليك.\n\n";
$message_body .= "المنتج: $product_name\n";
$message_body .= "الكمية: $quantity\n";
$message_body .= "التفاصيل: $details\n";
$message_body .= "المشتري: {$rfq['fname']} {$rfq['lname']}\n";
$message_body .= "هاتف المشتري: {$rfq['mobile1']}\n";
$message_body .= "بريد المشتري: {$rfq['email']}\n\n";
$message_body .= "يرجى تقديم عرض سعرك من خلال الرابط: $secure_link";

$msg_sql = "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
            VALUES ($admin_id, $supplier_id, '$subject', '$message_body', NOW(), 1, 1, 'whatsapp_rfq', $rfq_id)";
mysqli_query($con, $msg_sql);

// ============================================
// 5. إنشاء عرض أولي في جدول offers
// ============================================
$buyer_id = $rfq['buyer_id'];
$check_offer = mysqli_query($con, "SELECT id FROM offers WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id");
if (mysqli_num_rows($check_offer) == 0) {
    $insert_offer = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
                     VALUES ($rfq_id, $supplier_id, $buyer_id, 0, 'USD', 0, '', 'pending', NOW(), 0)";
    mysqli_query($con, $insert_offer);
}

// تحديث حالة الطلب
mysqli_query($con, "UPDATE buy_requirement SET wa_status = 'sent_to_supplier', wa_sent_count = wa_sent_count + 1, wa_last_sent_date = NOW() WHERE br_id = $rfq_id");

echo json_encode([
    'success' => true,
    'whatsapp' => $whatsapp_sent,
    'email' => $email_sent,
    'dashboard' => $dashboard_sent,
    'link' => $secure_link
]);
?>