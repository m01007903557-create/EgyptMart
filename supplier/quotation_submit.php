<?php
session_start();
require_once "../lib/connect.php";
header('Content-Type: application/json');

$rfq_id = (int)$_POST['rfq_id'];
$supplier_id = $_SESSION['uid_indm'] ?? 0;

if (!$supplier_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$price = (float)$_POST['price'];
$moq = (int)$_POST['moq'];
$delivery = mysqli_real_escape_string($con, $_POST['delivery_time']);
$payment = mysqli_real_escape_string($con, $_POST['payment_terms']);
$message = mysqli_real_escape_string($con, $_POST['message']);

// حفظ عرض السعر
$sql = "INSERT INTO whatsapp_quotations (rfq_id, supplier_id, price, moq, delivery_time, payment_terms, message_to_buyer, created_at) 
        VALUES ($rfq_id, $supplier_id, $price, $moq, '$delivery', '$payment', '$message', NOW())";
mysqli_query($con, $sql);

// تحديث جدول الطلب
mysqli_query($con, "UPDATE buy_requirement SET wa_supplier_price = $price, wa_supplier_moq = $moq, wa_supplier_delivery = '$delivery', wa_supplier_payment = '$payment', wa_supplier_message = '$message', wa_quoted_date = NOW(), wa_quoted_by = $supplier_id, wa_status = 'quoted' WHERE br_id = $rfq_id");

// إضافة رسالة في المحادثة
$chat_msg = "تم تقديم عرض سعر: $price USD\nالكمية الدنيا: $moq\nمدة التوصيل: $delivery\nشروط الدفع: $payment\n\n$message";
$sql_chat = "INSERT INTO whatsapp_chat_messages (rfq_id, sender_type, sender_id, message, created_at) VALUES ($rfq_id, 'supplier', $supplier_id, '$chat_msg', NOW())";
mysqli_query($con, $sql_chat);

echo json_encode(['success' => true]);
?>