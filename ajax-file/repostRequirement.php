<?php
/**
 * File: ajax/repostRequirement.php

 * Description: تفعيل عرض طلب الشراء
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

// التحقق من ملكية طلب الشراء قبل التفعيل
$check_sql = "SELECT br_id FROM buy_requirement WHERE br_id = ? AND br_u_id = ? LIMIT 1";
$check_stmt = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($check_stmt, 'ii', $br_id, $current_user);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (mysqli_num_rows($check_result) === 0) {
    mysqli_stmt_close($check_stmt);
    http_response_code(403);
    echo "0|You don't have permission to activate this requirement";
    exit;
}
mysqli_stmt_close($check_stmt);

// تحديث حالة العرض
$sql = "UPDATE buy_requirement SET br_display_status = '1' WHERE br_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $br_id);

if (mysqli_stmt_execute($stmt)) {
    echo "1|Requirement activated successfully";
} else {
    error_log("Activate Buy Requirement Error: " . mysqli_error($con) . " | ID: $br_id");
    echo "0|Failed to activate requirement";
}

mysqli_stmt_close($stmt);
?>