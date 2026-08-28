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

// 1. التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm'])) {
    reply(false, 'غير مصرح');
}

$supplier_id = (int)$_SESSION['uid_indm'];

// 2. التحقق من البيانات
if (!isset($_POST['rfq_id']) || !isset($_POST['price'])) {
    reply(false, 'بيانات غير مكتملة');
}

$rfq_id = (int)$_POST['rfq_id'];
$price = (float)$_POST['price'];
$delivery = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 0;
$notes = isset($_POST['notes']) ? addslashes($_POST['notes']) : '';

// 3. جلب المشتري
$q = mysqli_query($con, "SELECT br_u_id FROM buy_requirement WHERE br_id = $rfq_id");
$r = mysqli_fetch_assoc($q);
if (!$r) reply(false, 'طلب غير موجود');
$buyer_id = $r['br_u_id'];

// 4. التحقق من وجود عرض سابق (تجاهل العروض التي سعرها 0)
$check = mysqli_query($con, "SELECT id, update_count FROM offers WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id AND price > 0");
$existing = mysqli_fetch_assoc($check);

// 5. عرض جديد أو تحديث
if (!$existing) {
    // ✅ عرض جديد (أول مرة)
    $insert = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
               VALUES ($rfq_id, $supplier_id, $buyer_id, $price, 'USD', $delivery, '$notes', 'pending', NOW(), 0)";
    mysqli_query($con, $insert);
    $offer_id = mysqli_insert_id($con);
    $msg = '✅ تم إرسال عرض السعر للإدارة';
    $is_first = true;
    $whatsapp_url = '';
    
    // إشعار للأدمن
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, 1, 'عرض سعر جديد', 'طلب #$rfq_id بسعر $price USD', NOW(), 1, 1, 'admin_notification', $rfq_id)");
    
    // إنشاء غرفة محادثة
    $chat_code = 'CHAT_' . time() . '_' . $rfq_id;
    mysqli_query($con, "INSERT INTO chat_rooms (rfq_id, supplier_id, buyer_id, chat_code, created_at, status, expiry_date) 
                        VALUES ($rfq_id, $supplier_id, $buyer_id, '$chat_code', NOW(), 'active', DATE_ADD(NOW(), INTERVAL 7 DAY))");
    
} else {
    // ❌ تحديث عرض (تم تعديله من قبل)
    $offer_id = $existing['id'];
    $update_count = (int)$existing['update_count'] + 1;
    
    if ($update_count > 2) {
        reply(false, 'لا يمكن تعديل السعر أكثر من مرتين');
    }
    
    $update = "UPDATE offers SET price=$price, delivery_days=$delivery, notes='$notes', update_count=$update_count WHERE id=$offer_id";
    mysqli_query($con, $update);
    $msg = '✅ تم تحديث عرض السعر';
    $is_first = false;
    
    // جلب رقم المشتري للواتساب
    $q2 = mysqli_query($con, "SELECT mobile1 FROM user WHERE usr_id = $buyer_id");
    $r2 = mysqli_fetch_assoc($q2);
    $phone = $r2 ? $r2['mobile1'] : '';
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (substr($phone, 0, 2) != '20') $phone = '20' . ltrim($phone, '0');
    
    $magic = "https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";
    $wa_msg = "تم تحديث عرض السعر لطلبك #$rfq_id\nالسعر الجديد: $price USD\n$magic";
    $whatsapp_url = "https://wa.me/$phone?text=" . urlencode($wa_msg);
    
    // إشعار للمشتري
    $buyer_msg = "تم تحديث عرض السعر لطلبك #$rfq_id\nالسعر الجديد: $price USD";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, $buyer_id, 'تحديث عرض السعر', '$buyer_msg', NOW(), 1, 1, 'offer_update', $rfq_id)");
}

// 6. الرد النهائي
if ($is_first) {
    reply(true, $msg, ['whatsapp_url' => '', 'offer_id' => $offer_id, 'is_first' => true]);
} else {
    reply(true, $msg, ['whatsapp_url' => $whatsapp_url, 'offer_id' => $offer_id, 'is_first' => false]);
}
?>