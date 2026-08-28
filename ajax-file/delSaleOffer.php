<?php
/**
 * File: ajax/delSaleOffer.php

 * Description: تعطيل (حذف) عرض البيع بتغيير الحالة إلى 0
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف عرض البيع
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid offer ID";
    exit;
}

$so_id = (int)$_POST['id'];

global $con;

// تحديث حالة عرض البيع
$sql = "UPDATE sale_offer SET so_status = '0' WHERE so_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $so_id);

if (mysqli_stmt_execute($stmt)) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "1|Sale offer deleted successfully";
    } else {
        echo "0|Sale offer not found or already deleted";
    }
} else {
    error_log("Delete Sale Offer Error: " . mysqli_error($con) . " | ID: $so_id");
    echo "0|Failed to delete sale offer";
}

mysqli_stmt_close($stmt);
?>