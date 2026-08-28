<?php
/**
 * File: ajax/delSellofferAlertCat.php

 * Description: حذف تصنيف من تنبيهات البيع (الدائمة)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف التصنيف
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid category ID";
    exit;
}

$sac_id = (int)$_POST['id'];

global $con;

// حذف التصنيف من الجدول الدائم
$sql = "DELETE FROM selloffer_alert_category WHERE sac_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $sac_id);

if (mysqli_stmt_execute($stmt)) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "1|Category deleted successfully";
    } else {
        echo "0|Category not found or already deleted";
    }
} else {
    error_log("Delete Sell Alert Category Error: " . mysqli_error($con) . " | ID: $sac_id");
    echo "0|Failed to delete category";
}

mysqli_stmt_close($stmt);
?>