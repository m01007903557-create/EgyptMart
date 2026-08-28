<?php
/**
 * File: ajax/markhot-add.php

 * Description: تبديل حالة المنتج المميز (HOT) بين مفعل/غير مفعل
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

// التحقق من وجود المنتج وصلاحيته
$check_sql = "SELECT pd_id, pd_hot FROM products 
              WHERE pd_id = ? AND pd_uid = ? AND pd_status = '1' 
              LIMIT 1";

$stmt_check = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($stmt_check, 'ii', $product_id, $current_user);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    mysqli_stmt_close($stmt_check);
    echo "0|Product not found or access denied";
    exit;
}

$row = mysqli_fetch_assoc($result_check);
$current_hot_status = (int)($row['pd_hot'] ?? 0);
mysqli_stmt_close($stmt_check);

// تبديل الحالة (إذا كان 0 يصبح 1، إذا كان 1 يصبح 0)
$new_hot_status = ($current_hot_status == 0) ? 1 : 0;

$update_sql = "UPDATE products SET pd_hot = ? WHERE pd_id = ?";
$stmt_update = mysqli_prepare($con, $update_sql);
mysqli_stmt_bind_param($stmt_update, 'ii', $new_hot_status, $product_id);

if (mysqli_stmt_execute($stmt_update)) {
    echo "1|Product hot status toggled successfully|" . $new_hot_status;
} else {
    error_log("Toggle Hot Product Error: " . mysqli_error($con) . " | Product ID: $product_id");
    echo "0|Failed to toggle product hot status";
}

mysqli_stmt_close($stmt_update);
?>