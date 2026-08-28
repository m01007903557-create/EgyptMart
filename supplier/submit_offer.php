<?php
session_start();
require_once "../lib/connect.php";

header('Content-Type: application/json');

// التحقق من تسجيل دخول المورد
if (empty($_SESSION['uid_indm'])) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول كمورد']);
    exit;
}

$supplier_id = $_SESSION['uid_indm'];

// التحقق من البيانات
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'طريقة غير صحيحة']);
    exit;
}

$rfq_id = isset($_POST['rfq_id']) ? (int)$_POST['rfq_id'] : 0;
$price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
$currency = isset($_POST['currency']) ? $_POST['currency'] : 'EGP';
$delivery_days = isset($_POST['delivery_days']) ? (int)$_POST['delivery_days'] : 0;
$notes = isset($_POST['notes']) ? mysqli_real_escape_string($con, $_POST['notes']) : '';

if ($rfq_id == 0 || $price == 0 || $delivery_days == 0) {
    echo json_encode(['success' => false, 'error' => 'جميع الحقول المطلوبة غير مكتملة']);
    exit;
}

// جلب معرف المشتري
$buyer_sql = "SELECT br_u_id FROM buy_requirement WHERE br_id = $rfq_id";
$buyer_res = mysqli_query($con, $buyer_sql);
$buyer_row = mysqli_fetch_assoc($buyer_res);
$buyer_id = $buyer_row['br_u_id'] ?? 0;

if ($buyer_id == 0) {
    echo json_encode(['success' => false, 'error' => 'لم يتم العثور على المشتري']);
    exit;
}

// حفظ العرض
$insert_sql = "INSERT INTO offers (rfq_id, supplier_id, buyer_id, price, currency, delivery_days, notes, status, created_at) 
               VALUES ($rfq_id, $supplier_id, $buyer_id, $price, '$currency', $delivery_days, '$notes', 'pending', NOW())";

if (mysqli_query($con, $insert_sql)) {
    echo json_encode(['success' => true, 'message' => 'تم إرسال عرض سعرك بنجاح']);
} else {
    echo json_encode(['success' => false, 'error' => 'خطأ في الحفظ: ' . mysqli_error($con)]);
}
?>