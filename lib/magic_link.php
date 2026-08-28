<?php
session_start();
require_once "connect.php";

$token = $_GET['token'] ?? '';
$rfq_id = (int)($_GET['rfq_id'] ?? 0);

if (empty($token) || $rfq_id === 0) {
    die("بيانات ناقصة: الرابط غير صالح.");
}

$sql = "SELECT supplier_id FROM buy_requirement WHERE br_id = $rfq_id AND wa_magic_token = '$token' AND wa_token_expiry > NOW()";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("الرابط غير صالح أو منتهي الصلاحية.");
}

$_SESSION['uid_indm'] = (int)$row['supplier_id'];

// توجيه إلى صفحة المورد
header("Location: /my-enquiries.php");
exit;
?>