<?php
session_start();
require_once dirname(__DIR__) . '/lib/connect.php';

// قبول rfq_id من GET أو POST
$token = isset($_GET['token']) ? $_GET['token'] : (isset($_POST['token']) ? $_POST['token'] : '');
$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : (isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0);

if (empty($token) || $rfq_id == 0) {
    die("خطأ: الرابط غير صالح (بيانات ناقصة).");
}

$sql = "SELECT supplier_id, wa_token_expiry FROM buy_requirement WHERE br_id = $rfq_id AND wa_magic_token = '$token'";
$result = mysqli_query($con, $sql);

if (!$result) {
    die("خطأ في قاعدة البيانات: " . mysqli_error($con));
}

$row = mysqli_fetch_assoc($result);

if (!$row) {
    die("خطأ: الرابط غير صالح (لم يتم العثور على الطلب).");
}

$expiry = strtotime($row['wa_token_expiry']);
$now = time();

if ($now > $expiry) {
    die("خطأ: الرابط منتهي الصلاحية.");
}

// تسجيل دخول المورد
$_SESSION['uid_indm'] = (int)$row['supplier_id'];

// التوجيه إلى لوحة التحكم
header("Location: /my-enquiries.php");
exit;
?>