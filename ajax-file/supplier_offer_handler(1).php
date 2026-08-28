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

// 3. جلب بيانات المشتري
$q = mysqli_query($con, "SELECT br_u_id FROM buy_requirement WHERE br_id = $rfq_id");
$r = mysqli_fetch_assoc($q);
if (!$r) reply(false, 'طلب غير موجود');
$buyer_id = $r['br_u_id'];

// 4. جلب رقم هاتف المشتري
$q2 = mysqli_query($con, "SELECT mobile1 FROM user WHERE usr_id = $buyer_id");
$r2 = mysqli_fetch_assoc($q2);
$phone = $r2 ? $r2['mobile1'] : '';
$phone = preg_replace('/[^0-9]/', '', $phone);
if (substr($phone, 0, 2) != '20') $phone = '20' . ltrim($phone, '0');

// 5. التحقق من وجود عرض سابق لهذا المورد
$check_sql = "SELECT id, update_count FROM offers WHERE rfq_id = $rfq_id AND supplier_id = $supplier_id ORDER BY id DESC LIMIT 1";
$check_res = mysqli_query($con, $check_sql);
$existing_offer = mysqli_fetch_assoc($check_res);

$is_first_offer = false;
$update_count = 0;

if (!$existing_offer) {
    // لا يوجد عرض سابق → أول عرض
    $is_first_offer = true;
    $insert = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
               VALUES ($rfq_id, $supplier_id, $buyer_id, $price, 'USD', $delivery, '$notes', 'pending', NOW(), 0)";
    mysqli_query($con, $insert);
    $offer_id = mysqli_insert_id($con);
    $msg = '✅ تم إرسال عرض السعر بنجاح';
} else {
    // يوجد عرض سابق → تعديل
    $update_count = (int)$existing_offer['update_count'] + 1;
    if ($update_count > 2) {
        reply(false, 'لا يمكن تعديل السعر أكثر من مرتين');
    }
    
    mysqli_query($con, "UPDATE offers SET price=$price, delivery_days=$delivery, notes='$notes', update_count=$update_count, updated_at=NOW() WHERE id={$existing_offer['id']}");
    $offer_id = $existing_offer['id'];
    $msg = '✅ تم تحديث عرض السعر بنجاح';
}

// 6. الرابط السحري
$magic = "https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";
$wa_url = "https://api.whatsapp.com/send?phone=$phone&text=" . urlencode("عرض سعر لطلبك #$rfq_id\nالسعر: $price USD\nالرابط: $magic");

// 7. إرسال الإشعارات
if ($is_first_offer) {
    // العرض الأول: يرسل للأدمن + المشتري
    $admin_msg = "عرض سعر جديد للطلب #$rfq_id\nالمورد: $supplier_id\nالسعر: $price USD";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, 1, 'عرض سعر جديد', '$admin_msg', NOW(), 1, 1, 'admin_notification', $rfq_id)");
    
    $buyer_msg = "📦 عرض سعر جديد لطلبك #$rfq_id\n💰 السعر: $price USD\n🚚 مدة التوصيل: $delivery يوم\n📝 $notes";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, $buyer_id, 'عرض سعر جديد', '$buyer_msg', NOW(), 1, 1, 'offer', $rfq_id)");
} else {
    // التعديلات: ترسل للمشتري فقط
    $buyer_msg = "📦 تم تحديث عرض السعر لطلبك #$rfq_id\n💰 السعر الجديد: $price USD\n🚚 مدة التوصيل: $delivery يوم\n📝 $notes";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($supplier_id, $buyer_id, 'تحديث عرض السعر', '$buyer_msg', NOW(), 1, 1, 'offer_update', $rfq_id)");
}

// 8. الرد
reply(true, $msg, [
    'whatsapp_url' => $wa_url,
    'is_first_offer' => $is_first_offer,
    'update_count' => $update_count
]);
?>