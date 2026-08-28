<?php
session_start();
require_once "../../lib/connect.php";

header('Content-Type: application/json');

if (empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;

if ($offer_id == 0) {
    echo json_encode(['success' => false, 'error' => 'offer_id مطلوب']);
    exit;
}

// تحديث حالة العرض
$update_sql = "UPDATE offers SET status = 'notified' WHERE id = ?";
$stmt = mysqli_prepare($con, $update_sql);
mysqli_stmt_bind_param($stmt, 'i', $offer_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    // جلب بيانات العرض
    $select_sql = "SELECT o.*, 
                          u.mobile1 as buyer_phone, u.fname as buyer_fname, u.lname as buyer_lname,
                          s.fname as supplier_fname, s.lname as supplier_lname,
                          bp.bnsprof_compname as supplier_company,
                          br.br_pd_name as product_name
                   FROM offers o
                   LEFT JOIN user u ON o.buyer_id = u.usr_id
                   LEFT JOIN user s ON o.supplier_id = s.usr_id
                   LEFT JOIN business_profile bp ON s.usr_id = bp.bnsprof_uid
                   LEFT JOIN buy_requirement br ON o.rfq_id = br.br_id
                   WHERE o.id = ?";
    $stmt2 = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt2, 'i', $offer_id);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);
    $offer = mysqli_fetch_assoc($result);
    
    // =============================================
    // إضافة إشعار داخل المنصة للمشتري (في جدول message)
    // =============================================
    $admin_id = $_SESSION['ad_id_indm'];
    $subject = "📦 عرض سعر جديد - RFQ #" . $offer['rfq_id'];
    $body = "عرض سعر جديد على طلبك RFQ #" . $offer['rfq_id'] . "\n\n";
    $body .= "المنتج: " . $offer['product_name'] . "\n";
    $body .= "المورد: " . ($offer['supplier_company'] ?? $offer['supplier_fname'] . ' ' . $offer['supplier_lname']) . "\n";
    $body .= "السعر المقترح: " . $offer['price'] . " " . $offer['currency'] . "\n";
    $body .= "مدة التوصيل: " . $offer['delivery_days'] . " يوم\n";
    if (!empty($offer['notes'])) {
        $body .= "ملاحظات المورد: " . $offer['notes'] . "\n";
    }
    $body .= "\nللاطلاع على التفاصيل والرد على المورد، يرجى تسجيل الدخول إلى حسابك.\n";
    $body .= "https://egyptmart.shop/my-enquiries.php?rfq_id=" . $offer['rfq_id'];
    
    $subject_clean = mysqli_real_escape_string($con, $subject);
    $body_clean = mysqli_real_escape_string($con, $body);
    
    $insert_sql = "INSERT INTO message 
                   (msg_from, msg_to, msg_subject, msg_message, msg_date, msg_to_status, msg_from_status, msg_entity, msg_entity_id) 
                   VALUES (
                       $admin_id,
                       {$offer['buyer_id']},
                       '$subject_clean',
                       '$body_clean',
                       NOW(),
                       1,
                       1,
                       'offer_notification',
                       {$offer['rfq_id']}
                   )";
    mysqli_query($con, $insert_sql);
    
    echo json_encode([
        'success' => true,
        'offer_id' => $offer_id,
        'buyer_phone' => $offer['buyer_phone'],
        'buyer_name' => $offer['buyer_fname'] . ' ' . $offer['buyer_lname'],
        'supplier_name' => $offer['supplier_company'] ?? ($offer['supplier_fname'] . ' ' . $offer['supplier_lname']),
        'price' => $offer['price'],
        'currency' => $offer['currency'],
        'delivery_days' => $offer['delivery_days'],
        'rfq_id' => $offer['rfq_id']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'فشل تحديث حالة العرض']);
}

mysqli_stmt_close($stmt);
?>