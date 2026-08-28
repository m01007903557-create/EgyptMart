<?php
/**
 * File: ajax/delBlockUser.php

 * Description: إلغاء حظر مستخدم (حذف سجل الحظر)
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

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // حذف سجل الحظر
    $delete_sql = "DELETE FROM blocked_user WHERE bu_blockBy = ? AND bu_blocked = ?";
    $stmt = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt, 'ii', $block_by, $blocked);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to unblock user: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    $affected_rows = mysqli_stmt_affected_rows($stmt);
    
    if ($affected_rows == 0) {
        throw new Exception("Block record not found");
    }

    mysqli_stmt_close($stmt);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|User unblocked successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Unblock User Error: " . $e->getMessage() . " | BlockBy: $block_by, Blocked: $blocked");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>