<?php
/**
 * File: ajax/addBlockUser.php

 * Description: حظر مستخدم (إضافة سجل حظر)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرفات المستخدمين
if (!isset($_POST['blockBy']) || !is_numeric($_POST['blockBy'])) {
    http_response_code(400);
    echo "0|Invalid blocker ID";
    exit;
}

if (!isset($_POST['blocked']) || !is_numeric($_POST['blocked'])) {
    http_response_code(400);
    echo "0|Invalid blocked user ID";
    exit;
}

$block_by = (int)$_POST['blockBy'];
$blocked = (int)$_POST['blocked'];

// التحقق من عدم محاولة حظر النفس
if ($block_by === $blocked) {
    http_response_code(400);
    echo "0|You cannot block yourself";
    exit;
}

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من وجود المستخدمين
    $check_users_sql = "SELECT usr_id FROM user WHERE usr_id IN (?, ?) AND status = '1'";
    $stmt_check_users = mysqli_prepare($con, $check_users_sql);
    mysqli_stmt_bind_param($stmt_check_users, 'ii', $block_by, $blocked);
    mysqli_stmt_execute($stmt_check_users);
    $result_check_users = mysqli_stmt_get_result($stmt_check_users);
    
    if (mysqli_num_rows($result_check_users) < 2) {
        throw new Exception("One or both users not found or inactive");
    }
    mysqli_stmt_close($stmt_check_users);

    // التحقق من عدم وجود سجل حظر مسبق
    $check_sql = "SELECT bu_id FROM blocked_user 
                  WHERE bu_blockBy = ? AND bu_blocked = ? 
                  LIMIT 1";
    
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'ii', $block_by, $blocked);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        throw new Exception("User already blocked");
    }
    mysqli_stmt_close($stmt_check);

    // إدراج سجل الحظر
    $insert_sql = "INSERT INTO blocked_user (bu_blockBy, bu_blocked, bu_updated_date) 
                   VALUES (?, ?, NOW())";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'ii', $block_by, $blocked);

    if (!mysqli_stmt_execute($stmt_insert)) {
        throw new Exception("Failed to block user: " . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_insert);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|User blocked successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Block User Error: " . $e->getMessage() . " | BlockBy: $block_by, Blocked: $blocked");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>