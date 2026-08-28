<?php
session_start();
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$user_id = (int)$_SESSION['uid_indm'];
$product_id = (int)$_POST['product_id'];
$qty_from = (int)$_POST['qty_from'];
$qty_to = (int)$_POST['qty_to'];
$requirement = mysqli_real_escape_string($conn, $_POST['requirement_details']);

$product = getProductDetails($product_id);
$company_name = user_info($user_id, 'company_name') ?? 'العميل';
$user_email = user_info($user_id, 'email');

// إدراج الطلب
$sql = "INSERT INTO buy_requirement (
    br_pc_id, br_u_id, br_pd_name, br_requirement, 
    br_estimate_qty, br_estimate_qty_unit, br_description,
    br_posting_date, br_approval_status, br_display_status,
    communication_type, whatsapp_sent, whatsapp_sent_date
) VALUES (
    '$product_id', '$user_id', '{$product['name']}', '$requirement',
    '$qty_to', 'piece', 'Qty from $qty_from to $qty_to',
    NOW(), 'pending', 'active',
    'whatsapp', 1, NOW()
)";

if (mysqli_query($conn, $sql)) {
    $insert_id = mysqli_insert_id($conn);
    
    // رابط المنتج
    $product_url = "https://{$_SERVER['HTTP_HOST']}/product.php?id=$product_id";
    
    // رسالة واتساب
    $whatsapp_message = "مرحبا شركة $company_name أريد الحصول على أفضل سعر ل {$product['name']}\n\n";
    $whatsapp_message .= "*رقم الطلب RFQ ID:* $insert_id\n";
    $whatsapp_message .= "*عنوان المنتج:* {$product['name']}\n";
    $whatsapp_message .= "*التفاصيل:* $requirement\n";
    $whatsapp_message .= "*الكمية:* من $qty_from إلى $qty_to\n";
    $whatsapp_message .= "*رابط المنتج:* $product_url";
    
    $encoded_message = urlencode($whatsapp_message);
    $whatsapp_url = "https://wa.me/201104832811?text=$encoded_message";
    
    // إرسال بريد إلكتروني احتياطي
    $email_subject = "تم استلام طلب RFQ رقم $insert_id";
    $email_message = "Your RFQ requirements has been noted, Suppliers will contact you soon.\n\n";
    $email_message .= "RFQ ID: $insert_id\n";
    $email_message .= "المنتج: {$product['name']}\n";
    $email_message .= "الكمية: من $qty_from إلى $qty_to\n";
    $email_message .= "التفاصيل: $requirement\n\n";
    $email_message .= "سيتم إرسال العروض من الموردين قريبًا.";
    
    mail($user_email, $email_subject, $email_message);
    
    echo json_encode([
        'success' => true,
        'whatsapp_url' => $whatsapp_url,
        'rfq_id' => $insert_id,
        'simulation_message' => "Your RFQ requirements has been noted, Suppliers will contact you soon."
    ]);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
}
?>