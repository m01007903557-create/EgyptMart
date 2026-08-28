<?php
/**
 * File: ajax/deleteInbox.php

 * Description: حذف رسائل متعددة من صندوق الوارد (تعطيلها) للمستخدم الحالي
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرفات الرسائل
if (!isset($_POST['msg']) || empty($_POST['msg'])) {
    http_response_code(400);
    echo "0|No messages selected";
    exit;
}

$msg_string = $_POST['msg'];
$msg_ids = explode(',', $msg_string);

if (empty($msg_ids)) {
    http_response_code(400);
    echo "0|Invalid message IDs";
    exit;
}

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    $success_count = 0;
    $failed_ids = [];

    foreach ($msg_ids as $mid) {
        $mid = trim($mid);
        if (!is_numeric($mid)) {
            $failed_ids[] = $mid;
            continue;
        }
        
        $message_id = (int)$mid;
        
        $update_sql = "UPDATE message SET msg_to_status = '0' WHERE msg_id = ?";
        $stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt, 'i', $message_id);
        
        if (mysqli_stmt_execute($stmt)) {
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                $success_count++;
            } else {
                $failed_ids[] = $mid;
            }
        } else {
            $failed_ids[] = $mid;
            error_log("Failed to delete inbox message ID $mid: " . mysqli_error($con));
        }
        
        mysqli_stmt_close($stmt);
    }

    // تأكيد المعاملة
    mysqli_commit($con);

    // إعداد رسالة النتيجة
    $total = count($msg_ids);
    if ($success_count == $total) {
        echo "1|All messages deleted successfully";
    } elseif ($success_count > 0) {
        echo "1|$success_count out of $total messages deleted successfully";
    } else {
        echo "0|Failed to delete any messages";
    }

    // تسجيل الفاشلة للتصحيح
    if (!empty($failed_ids)) {
        error_log("Failed to delete inbox message IDs: " . implode(', ', $failed_ids));
    }

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Multiple Inbox Messages Error: " . $e->getMessage());
    http_response_code(500);
    echo "0|An error occurred while deleting messages";
}
?>