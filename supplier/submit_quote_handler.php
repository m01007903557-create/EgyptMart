<?php
session_start();
require_once "../lib/connect.php";
require_once "../includes/whatsapp_enquiries_functions.php";
header('Content-Type: application/json');

$supplier_id = $_SESSION['uid_indm'] ?? 0;
if (!$supplier_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$rfq_id = (int)$_POST['rfq_id'];
$unit_price = (float)$_POST['unit_price'];
$moq = (int)$_POST['moq'];
$delivery_time = mysqli_real_escape_string($con, $_POST['delivery_time']);
$supplier_message = mysqli_real_escape_string($con, $_POST['supplier_message']);

// جلب بيانات الطلب
$sql = "SELECT * FROM whatsapp_rfq_messages WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id";
$res = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($res);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// حفظ عرض السعر
$insert_sql = "INSERT INTO whatsapp_quotes (rfq_id, supplier_id, buyer_id, unit_price, moq, delivery_time, supplier_message, status, created_date) 
               VALUES ($rfq_id, $supplier_id, {$rfq['buyer_id']}, $unit_price, $moq, '$delivery_time', '$supplier_message', 'pending', NOW())";
mysqli_query($con, $insert_sql);

// تحديث حالة الطلب
update_whatsapp_rfq_status($rfq_id, 'quoted');

// إشعار واتساب للمشتري (يفتح واتساب المنصة)
$buyer_phone = $rfq['buyer_phone'];
$notification = "You received a quotation for your RFQ #$rfq_id. Please login to EgyptMart to view supplier offer.";
$wa_link = send_whatsapp_notification($buyer_phone, $notification);

echo json_encode(['success' => true, 'whatsapp_link' => $wa_link]);
?>