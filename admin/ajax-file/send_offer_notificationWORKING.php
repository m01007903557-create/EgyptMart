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

// تحديث حالة العرض فقط
$update_sql = "UPDATE offers SET status = 'notified', notified_at = NOW() WHERE id = $offer_id";

if (mysqli_query($con, $update_sql)) {
    echo json_encode(['success' => true, 'message' => 'تم تحديث حالة العرض']);
} else {
    echo json_encode(['success' => false, 'error' => mysqli_error($con)]);
}
?>