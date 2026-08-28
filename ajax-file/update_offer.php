<?php
ob_clean();
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json');

// دالة بسيطة للرد
function sendResponse($success, $message, $extra = []) {
    $response = ['success' => $success, 'message' => $message];
    foreach ($extra as $k => $v) $response[$k] = $v;
    echo json_encode($response);
    exit;
}

// 1. التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm'])) {
    sendResponse(false, 'غير مصرح');
}

$supplier_id = (int)$_SESSION['uid_indm'];

// 2. التحقق من البيانات
if (!isset($_POST['rfq_id']) || !isset($_POST['price'])) {
    sendResponse(false, 'بيانات غير مكتملة');
}

$rfq_id = (int)$_POST['rfq_id'];
$price = (float)$_POST['price'];
$delivery = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 0;
$notes = isset($_POST['notes']) ? addslashes($_POST['notes']) : '';
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;

// 3. جلب buyer_id
$q = mysqli_query($con, "SELECT br_u_id FROM buy_requirement WHERE br_id = $rfq_id");
$r = mysqli_fetch_assoc($q);
if (!$r) sendResponse(false, 'طلب غير موجود');
$buyer_id = $r['br_u_id'];

// 4. حفظ أو تحديث العرض (بدون إرسال إشعارات معقدة)
if ($offer_id == 0) {
    // عرض جديد
    $insert = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at, update_count) 
               VALUES ($rfq_id, $supplier_id, $buyer_id, $price, 'USD', $delivery, '$notes', 'pending', NOW(), 0)";
    mysqli_query($con, $insert);
    $offer_id = mysqli_insert_id($con);
    $msg = '✅ تم إرسال عرض السعر بنجاح';
} else {
    // تحديث عرض
    mysqli_query($con, "UPDATE offers SET price=$price, delivery_days=$delivery, notes='$notes', update_count=update_count+1, updated_at=NOW() WHERE id=$offer_id");
    $msg = '✅ تم تحديث عرض السعر بنجاح';
}

// 5. جلب هاتف المشتري للواتساب (اختياري)
$q2 = mysqli_query($con, "SELECT mobile1 FROM user WHERE usr_id = $buyer_id");
$r2 = mysqli_fetch_assoc($q2);
$phone = $r2 ? $r2['mobile1'] : '';
$phone = preg_replace('/[^0-9]/', '', $phone);
if (substr($phone, 0, 2) != '20') $phone = '20' . ltrim($phone, '0');

// 6. الرابط السحري
$magic = "https://egyptmart.shop/ajax-file/enq-details.php?rfq_id=$rfq_id&offer_id=$offer_id";
$wa_url = "https://api.whatsapp.com/send?phone=$phone&text=" . urlencode("عرض سعر لطلبك #$rfq_id\nالسعر: $price USD\n$magic");

// 7. الرد النهائي (مثل الكود التشخيصي)
sendResponse(true, $msg, [
    'whatsapp_url' => $wa_url,
    'magic_link' => $magic,
    'offer_id' => $offer_id
]);
?>