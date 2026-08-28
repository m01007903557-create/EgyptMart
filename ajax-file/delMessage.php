<?php
/**
 * File: ajax/delMessage.php

 * Description: حذف رسالة (تعطيلها) من صندوق الوارد أو الصادر للمستخدم الحالي
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

$current_user = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف الرسالة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid message ID";
    exit;
}

$msg_id = (int)$_POST['id'];

global $con;

// جلب بيانات الرسالة
$sql = "SELECT msg_id, msg_to, msg_from, msg_to_status, msg_from_status 
        FROM message 
        WHERE msg_id = ? 
        LIMIT 1";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $msg_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    http_response_code(404);
    echo "0|Message not found";
    exit;
}

$flag = 0;
$field = '';

// تحديد ما إذا كانت الرسالة في صندوق الوارد أو الصادر للمستخدم الحالي
if ($row->msg_to == $current_user) {
    $field = 'to';
} elseif ($row->msg_from == $current_user) {
    $field = 'from';
} else {
    http_response_code(403);
    echo "0|You don't have permission to delete this message";
    exit;
}

// تحديث حالة الرسالة بناءً على الصندوق
if ($field == 'to') {
    $update_sql = "UPDATE message SET msg_to_status = '0' WHERE msg_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'i', $msg_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $flag = 1;
    } else {
        error_log("Delete Message (inbox) Error: " . mysqli_error($con) . " | Message ID: $msg_id");
    }
    mysqli_stmt_close($stmt_update);
    
} elseif ($field == 'from') {
    $update_sql = "UPDATE message SET msg_from_status = '0' WHERE msg_id = ?";
    $stmt_update = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt_update, 'i', $msg_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        $flag = 1;
    } else {
        error_log("Delete Message (sent) Error: " . mysqli_error($con) . " | Message ID: $msg_id");
    }
    mysqli_stmt_close($stmt_update);
}

echo $flag;
?>