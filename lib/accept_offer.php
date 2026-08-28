<?php
session_start();
require_once __DIR__ . '/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$buyer_id = (int)$_SESSION['uid_indm'];
$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
$rfq_id = isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($offer_id == 0 || $action == '') {
    echo json_encode(['success' => false, 'error' => 'بيانات غير مكتملة']);
    exit;
}

$sql = "SELECT o.*, u.usr_id as supplier_id, u.fname, u.lname, u.email, u.mobile1,
               bp.bnsprof_compname as company_name
        FROM offers o
        LEFT JOIN user u ON o.supplier_id = u.usr_id
        LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
        WHERE o.id = $offer_id";
$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

if (!$offer) {
    echo json_encode(['success' => false, 'error' => 'العرض غير موجود']);
    exit;
}

if ($action == 'accept') {
    mysqli_query($con, "UPDATE offers SET status = 'accepted' WHERE id = $offer_id");
    mysqli_query($con, "UPDATE offers SET status = 'rejected' WHERE rfq_id = $rfq_id AND id != $offer_id");
    
    $msg = "🎉 تم قبول عرض سعرك للطلب #$rfq_id من قبل المشتري.";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($buyer_id, {$offer['supplier_id']}, '✅ تم قبول عرض السعر', '$msg', NOW(), 1, 1, 'offer_accepted', $rfq_id)");
    
    $msg2 = "🎉 تم قبول عرض السعر للطلب #$rfq_id. يمكنك الآن التواصل مع المورد.";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($buyer_id, $buyer_id, '✅ تم قبول عرض السعر', '$msg2', NOW(), 1, 1, 'offer_accepted', $rfq_id)");
    
    $supplier_data = [
        'company_name' => $offer['company_name'] ?? ($offer['fname'] . ' ' . $offer['lname']),
        'phone' => $offer['mobile1'] ?? 'غير متوفر',
        'email' => $offer['email'] ?? 'غير متوفر'
    ];
    
    echo json_encode(['success' => true, 'message' => '🎉 تم قبول العرض بنجاح', 'supplier_data' => $supplier_data]);
    
  // تحديث صلاحية المحادثة (7 أيام من القبول)
mysqli_query($con, "UPDATE chat_rooms SET expiry_date = DATE_ADD(NOW(), INTERVAL 7 DAY), status = 'accepted' WHERE rfq_id = $rfq_id");
  
  
  
    // تحديث حالة الطلب إلى accepted
update_rfq_status($rfq_id, 'accepted');
    
} elseif ($action == 'reject') {
    mysqli_query($con, "UPDATE offers SET status = 'rejected' WHERE id = $offer_id");
    
    $msg = "❌ تم رفض عرض سعرك للطلب #$rfq_id من قبل المشتري.";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                        VALUES ($buyer_id, {$offer['supplier_id']}, '❌ تم رفض العرض', '$msg', NOW(), 1, 1, 'offer_rejected', $rfq_id)");
    // تحديث حالة الطلب إلى rejected
update_rfq_status($rfq_id, 'rejected');
    echo json_encode(['success' => true, 'message' => 'تم رفض العرض']);
} else {
    echo json_encode(['success' => false, 'error' => 'إجراء غير صالح']);
}
?>