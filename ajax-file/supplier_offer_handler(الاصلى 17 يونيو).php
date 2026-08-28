<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

header('Content-Type: application/json');

function reply($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message];
    foreach ($data as $k => $v) $response[$k] = $v;
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['uid_indm'])) reply(false, 'غير مصرح');

$supplier_id = (int)$_SESSION['uid_indm'];

if (!isset($_POST['rfq_id']) || !isset($_POST['price'])) {
    reply(false, 'بيانات غير مكتملة');
}

$rfq_id = (int)$_POST['rfq_id'];
$price = (float)$_POST['price'];
$delivery = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 0;
$notes = isset($_POST['notes']) ? addslashes($_POST['notes']) : '';

// جلب بيانات المشتري
$q = mysqli_query($con, "SELECT br_u_id FROM buy_requirement WHERE br_id = $rfq_id");
$r = mysqli_fetch_assoc($q);
if (!$r) reply(false, 'طلب غير موجود');
$buyer_id = $r['br_u_id'];

// جلب هاتف المشتري
$q2 = mysqli_query($con, "SELECT mobile1 FROM user WHERE usr_id = $buyer_id");
$r2 = mysqli_fetch_assoc($q2);
$phone = $r2 ? $r2['mobile1'] : '';
$phone = preg_replace('/[^0-9]/', '', $phone);
if (substr($phone, 0, 2) != '20') $phone = '20' . ltrim($phone, '0');

// التحقق من وجود عرض سابق
$check = mysqli_query($con, "SELECT id, update_count FROM offers WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id");
$existing = mysqli_fetch_assoc($check);

$is_first = false;
$offer_id = 0;

if (!$existing) {
    // العرض الأول
    $is_first = true;
    $insert = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
               VALUES ($rfq_id, $supplier_id, $buyer_id, $price, 'USD', $delivery, '$notes', 'pending', NOW(), 0)";
    mysqli_query($con, $insert);
    $offer_id = mysqli_insert_id($con);
    $msg = '✅ تم إرسال عرض السعر بنجاح';
} else {
    // تعديل
    $update_count = $existing['update_count'] + 1;
    if ($update_count > 2) reply(false, 'لا يمكن تعديل السعر أكثر من مرتين');
    mysqli_query($con, "UPDATE offers SET price=$price, delivery_days=$delivery, notes='$notes', update_count=$update_count WHERE id={$existing['id']}");
    $offer_id = $existing['id'];
    $msg = '✅ تم تحديث عرض السعر بنجاح';
}

// الرابط السحري
$magic = "https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";
$wa_url = "https://api.whatsapp.com/send?phone=$phone&text=" . urlencode("عرض سعر لطلبك #$rfq_id\nالسعر: $price USD\nالرابط: $magic");

// إرسال الإشعارات
if ($is_first) {
    // العرض الأول: للأدمن + المشتري
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, 1, 'عرض سعر جديد', 'عرض سعر للطلب #$rfq_id بسعر $price USD', NOW(), 1, 1, 'admin_notification', $rfq_id)");
    
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, $buyer_id, 'عرض سعر جديد', 'السعر: $price USD\nمدة التوصيل: $delivery يوم', NOW(), 1, 1, 'offer', $rfq_id)");
} else {
    // التعديلات: للمشتري فقط
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, $buyer_id, 'تحديث عرض السعر', 'السعر الجديد: $price USD\nمدة التوصيل: $delivery يوم', NOW(), 1, 1, 'offer_update', $rfq_id)");
}

reply(true, $msg, ['whatsapp_url' => $wa_url]);
?>