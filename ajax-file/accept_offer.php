<?php
session_start();
require_once __DIR__ . '/../lib/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'يرجى تسجيل الدخول']);
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

// جلب بيانات العرض والمورد
$sql = "SELECT o.*, u.usr_id as supplier_id, u.fname, u.lname, u.email as supplier_email, u.mobile1 as supplier_phone,
               bp.bnsprof_compname as company_name, bp.bnsprof_website as company_website
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
    mysqli_query($con, "UPDATE chat_rooms SET status = 'active', expiry_date = DATE_ADD(NOW(), INTERVAL 7 DAY), accepted_at = NOW() WHERE rfq_id = $rfq_id");
    
    // إشعار للمورد
    $msg = "تم قبول عرض سعرك من قبل المشتري.";
    mysqli_query($con, "INSERT INTO message (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_entity, msg_entity_id) 
                        VALUES ($buyer_id, {$offer['supplier_id']}, '✅ تم قبول عرض السعر', '$msg', NOW(), 'offer_accepted', $rfq_id)");
    
    $supplier_data = [
        'company_name' => $offer['company_name'] ?? ($offer['fname'] . ' ' . $offer['lname']),
        'phone' => $offer['supplier_phone'] ?? 'غير متوفر',
        'email' => $offer['supplier_email'] ?? 'غير متوفر',
        'website' => $offer['company_website'] ?? '#'
    ];
    
    echo json_encode(['success' => true, 'message' => 'تم قبول العرض', 'supplier_data' => $supplier_data]);
} elseif ($action == 'reject') {
    mysqli_query($con, "UPDATE offers SET status = 'rejected' WHERE id = $offer_id");
    echo json_encode(['success' => true, 'message' => 'تم رفض العرض']);
} else {
    echo json_encode(['success' => false, 'error' => 'إجراء غير صالح']);
}
?>