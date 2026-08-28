<?php
/**
 * File: ajax/delRequirement.php

 * Description: تعطيل (حذف) طلب الشراء بتغيير الحالة إلى 0
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف طلب الشراء
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid requirement ID";
    exit;
}

$br_id = (int)$_POST['id'];

global $con;

// تحديث حالة طلب الشراء
$sql = "UPDATE buy_requirement SET br_status = '0' WHERE br_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $br_id);

if (mysqli_stmt_execute($stmt)) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "1|Buy requirement deleted successfully";
    } else {
        echo "0|Buy requirement not found or already deleted";
    }
} else {
    error_log("Delete Buy Requirement Error: " . mysqli_error($con) . " | ID: $br_id");
    echo "0|Failed to delete buy requirement";
}

mysqli_stmt_close($stmt);
?>