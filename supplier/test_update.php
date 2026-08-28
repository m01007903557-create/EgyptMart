<?php
session_start();
$_SESSION['uid_indm'] = 123; // ضع معرف مستخدم موجود مؤقتاً للاختبار

require_once __DIR__ . '/../lib/connect.php';

header('Content-Type: application/json');

$sql = "SELECT id, supplier_id, price FROM offers LIMIT 1";
$result = mysqli_query($con, $sql);
$offer = mysqli_fetch_assoc($result);

echo json_encode([
    'success' => true,
    'offer' => $offer,
    'session' => $_SESSION['uid_indm']
]);
?>