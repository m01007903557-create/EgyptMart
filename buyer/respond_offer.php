<?php
session_start();
require_once "../lib/connect.php";

header('Content-Type: application/json');

if (empty($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$offer_id = isset($_POST['offer_id']) ? (int)$_POST['offer_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($offer_id == 0 || !in_array($action, ['accept', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'بيانات غير صالحة']);
    exit;
}

$new_status = ($action == 'accept') ? 'accepted' : 'rejected';

$update_sql = "UPDATE offers SET status = ? WHERE id = ?";
$stmt = mysqli_prepare($con, $update_sql);
mysqli_stmt_bind_param($stmt, 'si', $new_status, $offer_id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo json_encode(['success' => true, 'message' => 'تم تحديث حالة العرض']);
} else {
    echo json_encode(['success' => false, 'error' => 'فشل تحديث حالة العرض']);
}

mysqli_stmt_close($stmt);
?>