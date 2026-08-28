<?php
/**
 * File: ajax/closeRequirement.php

 * Description: إلغاء تفعيل (إخفاء) طلب الشراء
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

// التحقق من وجود معرف طلب الشراء
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid requirement ID";
    exit;
}

$br_id = (int)$_POST['id'];
$current_user = (int)$_SESSION['uid_indm'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من ملكية طلب الشراء قبل الإلغاء
    $check_sql = "SELECT br_id FROM buy_requirement WHERE br_id = ? AND br_u_id = ? LIMIT 1";
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'ii', $br_id, $current_user);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);

    if (mysqli_num_rows($result_check) === 0) {
        throw new Exception("Buy requirement not found or you don't have permission to deactivate it");
    }
    mysqli_stmt_close($stmt_check);

    // تحديث حالة طلب الشراء
    $update_sql = "UPDATE buy_requirement SET br_display_status = '0' WHERE br_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'i', $br_id);

    if (!mysqli_stmt_execute($stmt_update)) {
        throw new Exception("Failed to deactivate requirement: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt_update) == 0) {
        throw new Exception("Requirement not found or already deactivated");
    }

    mysqli_stmt_close($stmt_update);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Buy requirement deactivated successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Deactivate Buy Requirement Error: " . $e->getMessage() . " | ID: $br_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>