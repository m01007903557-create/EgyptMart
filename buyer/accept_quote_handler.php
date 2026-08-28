<?php
session_start();
require_once "../lib/connect.php";
header('Content-Type: application/json');

$buyer_id = $_SESSION['uid_indm'] ?? 0;
if (!$buyer_id) {
    echo json_encode(['success' => false, 'error' => 'يجب تسجيل الدخول']);
    exit;
}

$quote_id = (int)$_POST['quote_id'];
$rfq_id = (int)$_POST['rfq_id'];

// تحديث حالة عرض السعر
mysqli_query($con, "UPDATE whatsapp_quotes SET status = 'accepted' WHERE wq_id = $quote_id");

// تحديث حالة الطلب
mysqli_query($con, "UPDATE whatsapp_rfq_messages SET status = 'accepted' WHERE rfq_id = $rfq_id");

// جلب بيانات المورد والمشتري
$sql = "SELECT w.*, 
               u.fname as buyer_fname, u.lname as buyer_lname, u.mobile1 as buyer_phone, u.email as buyer_email,
               bp.bnsprof_comp_url as supplier_company, bp.bnsprof_mobile1 as supplier_phone, bp.bnsprof_email as supplier_email
        FROM whatsapp_quotes w
        LEFT JOIN whatsapp_rfq_messages wr ON w.rfq_id = wr.rfq_id
        LEFT JOIN user u ON wr.buyer_id = u.usr_id
        LEFT JOIN business_profile bp ON w.supplier_id = bp.bnsprof_uid
        WHERE w.wq_id = $quote_id";
$res = mysqli_query($con, $sql);
$data = mysqli_fetch_assoc($res);

// حفظ بيانات التواصل في جلسة أو جدول مؤقت
$_SESSION['accepted_quote_data'] = $data;

echo json_encode(['success' => true, 'data' => $data]);
?>