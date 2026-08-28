<?php
session_start();
require_once "../lib/connect.php";

header('Content-Type: application/json');

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if ($rfq_id == 0) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// جلب بيانات الطلب والمورد
$sql = "SELECT br.*, 
               u.fname, u.lname, u.mobile1, u.email,
               p.pd_title, p.pd_uid as supplier_id,
               su.mobile1 as supplier_phone
        FROM buy_requirement br
        LEFT JOIN user u ON br.br_u_id = u.usr_id
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user su ON p.pd_uid = su.usr_id
        WHERE br.br_id = $rfq_id";
$result = mysqli_query($con, $sql);
$row = mysqli_fetch_assoc($result);

if (!$row) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

$supplierPhone = $row['supplier_phone'] ?? '';
if (empty($supplierPhone)) {
    $supplierPhone = $row['mobile1'] ?? '';
}

$cleanPhone = preg_replace('/[^0-9]/', '', $supplierPhone);
if (substr($cleanPhone, 0, 2) != '20') {
    $cleanPhone = '20' . ltrim($cleanPhone, '0');
}

$message = "📦 طلب شراء جديد #" . $rfq_id . "\n\n";
$message .= "المنتج: " . ($row['pd_title'] ?? 'غير محدد') . "\n";
$message .= "الكمية: " . ($row['br_estimate_qty'] ?? 0) . ' ' . ($row['br_estimate_qty_unit'] ?? '') . "\n";
$message .= "المشتري: " . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? '') . "\n";
$message .= "هاتف المشتري: " . ($row['mobile1'] ?? '') . "\n\n";
$message .= "للتقديم: https://egyptmart.shop/supplier/whatsapp_rfq_view.php?id=" . $rfq_id;

echo json_encode([
    'success' => true,
    'phone' => $cleanPhone,
    'message' => $message
]);
?>