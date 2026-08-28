<?php
/**
 * File: ajax/pushedtotop.php

 * Description: دفع المنتج إلى الأعلى (تفعيل خاصية التميز)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0|Unauthorized";
    exit;
}

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "0|Invalid product ID";
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];
$product_id = (int)$_GET['id'];

global $con;

// التحقق من وجود المنتج وصلاحيته للدفع
$check_sql = "SELECT pd_id FROM products 
              WHERE pd_id = ? 
              AND pd_pushed_top = '0' 
              AND pd_status = '1'
              AND pd_uid = ? 
              LIMIT 1";

$stmt_check = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($stmt_check, 'ii', $product_id, $current_user);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    mysqli_stmt_close($stmt_check);
    echo "0|Product not found or already pushed to top";
    exit;
}
mysqli_stmt_close($stmt_check);

// تحديث حالة الدفع
$update_sql = "UPDATE products SET pd_pushed_top = '1' WHERE pd_id = ?";
$stmt_update = mysqli_prepare($con, $update_sql);
mysqli_stmt_bind_param($stmt_update, 'i', $product_id);

if (mysqli_stmt_execute($stmt_update)) {
    echo "1|Product pushed to top successfully";
} else {
    error_log("Push Product Error: " . mysqli_error($con) . " | Product ID: $product_id");
    echo "0|Failed to push product to top";
}

mysqli_stmt_close($stmt_update);
?>