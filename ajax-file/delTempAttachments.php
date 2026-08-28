<?php
/**
 * File: ajax/delTempAttachments.php

 * Description: حذف مرفق مؤقت للرسائل من المجلد ومن قاعدة البيانات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المرفق
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid attachment ID";
    exit;
}

$tma_id = (int)$_POST['id'];

global $con;

// جلب معلومات المرفق
$sql_tma = "SELECT tma_file FROM temp_msg_attachment WHERE tma_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql_tma);
mysqli_stmt_bind_param($stmt, 'i', $tma_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row_tma = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row_tma) {
    http_response_code(404);
    echo "0|Attachment not found";
    exit;
}

// تنظيف اسم الملف
$file_name = basename($row_tma->tma_file ?? '');
$file_name = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $file_name);

// حذف الملف الفعلي إذا كان موجوداً
if (!empty($file_name)) {
    $file_path = __DIR__ . "/../upload/message_attachment/" . $file_name;
    if (file_exists($file_path) && is_file($file_path)) {
        if (!unlink($file_path)) {
            error_log("Failed to delete file: $file_path");
        }
    }
}

// حذف السجل من قاعدة البيانات
$delete_sql = "DELETE FROM temp_msg_attachment WHERE tma_id = ?";
$stmt_delete = mysqli_prepare($con, $delete_sql);
mysqli_stmt_bind_param($stmt_delete, 'i', $tma_id);

if (mysqli_stmt_execute($stmt_delete)) {
    echo "1|Attachment deleted successfully";
} else {
    error_log("Delete Temp Attachment Error: " . mysqli_error($con) . " | ID: $tma_id");
    echo "0|Failed to delete attachment record";
}

mysqli_stmt_close($stmt_delete);
?>