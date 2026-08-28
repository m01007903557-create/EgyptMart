<?php
session_start();
require_once "../common.php";

header('Content-Type: application/json');

if (empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'error' => 'غير مصرح']);
    exit;
}

$rfq_id = isset($_GET['rfq_id']) ? (int)$_GET['rfq_id'] : 0;

if ($rfq_id == 0) {
    echo json_encode(['success' => false, 'error' => 'RFQ ID مطلوب']);
    exit;
}

// جلب بيانات الطلب والمورد
$sql = "SELECT 
            br.br_id,
            br.br_estimate_qty,
            br.br_estimate_qty_unit,
            br.br_requirement,
            p.pd_title,
            p.pd_image,
            p.pd_uid as supplier_id,
            u.mobile1 as supplier_mobile
        FROM buy_requirement br
        LEFT JOIN products p ON br.br_pc_id = p.pd_id
        LEFT JOIN user u ON p.pd_uid = u.usr_id
        WHERE br.br_id = $rfq_id
        LIMIT 1";

$result = mysqli_query($con, $sql);
$rfq = mysqli_fetch_assoc($result);

if (!$rfq) {
    echo json_encode(['success' => false, 'error' => 'الطلب غير موجود']);
    exit;
}

// تنظيف رقم الجوال
$supplier_phone = $rfq['supplier_mobile'] ?? '';
$supplier_phone = preg_replace('/[^0-9]/', '', $supplier_phone);
if (substr($supplier_phone, 0, 1) == '0') {
    $supplier_phone = '20' . substr($supplier_phone, 1);
}
if (strlen($supplier_phone) == 10) {
    $supplier_phone = '20' . $supplier_phone;
}

// وحدة القياس
$unit_name = '';
if (!empty($rfq['br_estimate_qty_unit'])) {
    $unit_sql = mysqli_query($con, "SELECT mu_name FROM measurement_unit WHERE mu_id = " . (int)$rfq['br_estimate_qty_unit']);
    $unit_row = mysqli_fetch_assoc($unit_sql);
    $unit_name = $unit_row['mu_name'] ?? '';
}

// بناء رسالة واتساب
$message = "📦 طلب شراء جديد #" . $rfq['br_id'] . "\n\n";
$message .= "المنتج: " . ($rfq['pd_title'] ?? '') . "\n";
$message .= "الكمية: " . $rfq['br_estimate_qty'] . " " . $unit_name . "\n";
$message .= "التفاصيل: " . substr($rfq['br_requirement'], 0, 200) . "\n\n";
$message .= "يرجى تقديم عرض سعرك.";

echo json_encode([
    'success' => true,
    'phone' => $supplier_phone,
    'message' => $message,
    'rfq_id' => $rfq_id
]);
?>