<?php
/**
 * File: ajax/delTempBuyleadAlertCat.php

 * Description: حذف تصنيف مؤقت من تنبيهات الشراء
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0|Unauthorized";
    exit;
}

// التحقق من وجود معرف التصنيف
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid category ID";
    exit;
}

$user_id = (int)$_SESSION['uid_indm'];
$category_id = (int)$_POST['id'];

global $con;

// حذف التصنيف من الجدول المؤقت
$sql = "DELETE FROM temp_buylead_alert_cat 
        WHERE tbac_usr_id = ? AND tbac_pc_id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ii', $user_id, $category_id);

if (mysqli_stmt_execute($stmt)) {
    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo "1|Category removed successfully";
    } else {
        echo "0|Category not found or already removed";
    }
} else {
    error_log("Remove Temp Buy Alert Error: " . mysqli_error($con) . " | User: $user_id, Cat: $category_id");
    echo "0|Failed to remove category";
}

mysqli_stmt_close($stmt);
?>