<?php
/**
 * File: ajax/additionaldet-edit.php

 * Description: تحديث تفاصيل المنتج الإضافية (العلامة التجارية، شروط الدفع، التغليف، إلخ)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0||Unauthorized";
    exit;
}

// التحقق من وجود معرف المنتج
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    http_response_code(400);
    echo "0||Invalid product ID";
    exit;
}

$current_user = (int)$_SESSION['uid_indm'];
$product_id = (int)$_GET['pid'];

// تنظيف وتحضير البيانات
$pd_hot = (int)($_GET['pd_hot'] ?? 0);
$pd_brand_name = trim($_GET['pd_brand'] ?? '');
$pd_payment = trim($_GET['pd_payment'] ?? '');
$pd_pod = trim($_GET['pd_pod'] ?? '');
$pd_pn_capct = trim($_GET['pd_pn_capct'] ?? '');
$pd_dlv_time = trim($_GET['pd_dlv_time'] ?? '');
$pd_pck_dets = trim($_GET['pd_pck_dets'] ?? '');

$msg = '';
$e = 0;

global $con;

// التحقق من طول تفاصيل التغليف
if (strlen($pd_pck_dets) > 2000) {
    $msg = "Packaging Details cannot have more than 2000 characters";
    $e = 0;
    echo $msg . "||" . $e;
    exit;
}

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من ملكية المنتج
    $check_sql = "SELECT pd_id FROM products WHERE pd_id = ? AND pd_uid = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'ii', $product_id, $current_user);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        throw new Exception("Product not found or you don't have permission to update it");
    }
    mysqli_stmt_close($stmt_check);

    // تحديث بيانات المنتج
    $update_sql = "UPDATE products SET 
                    pd_hot = ?,
                    brand_name = ?,
                    pd_payment = ?,
                    pd_pod = ?,
                    pd_pn_capct = ?,
                    pd_dlv_time = ?,
                    pd_pck_dets = ?
                   WHERE pd_id = ?";

    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'issssssi', 
        $pd_hot,
        $pd_brand_name,
        $pd_payment,
        $pd_pod,
        $pd_pn_capct,
        $pd_dlv_time,
        $pd_pck_dets,
        $product_id
    );

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception("Failed to update product details: " . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_update);

    // تأكيد المعاملة
    mysqli_commit($con);

    $msg = "Product details updated successfully";
    $e = 1;

} catch (Exception $ex) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Update Product Details Error: " . $ex->getMessage() . " | Product ID: $product_id");
    $msg = $ex->getMessage();
    $e = 0;
}

echo $msg . "||" . $e;
?>