<?php
/**
 * File: ajax/delete-contact.php

 * Description: حذف جهة اتصال الشركة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف جهة الاتصال
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "0|Invalid contact ID";
    exit;
}

$contact_id = (int)$_GET['id'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // حذف جهة الاتصال
    $delete_sql = "DELETE FROM company_contact WHERE comp_cnt_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $contact_id);

    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete contact: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    $affected_rows = mysqli_stmt_affected_rows($stmt_delete);
    
    if ($affected_rows == 0) {
        throw new Exception("Contact not found or already deleted");
    }

    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Contact deleted successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Delete Company Contact Error: " . $e->getMessage() . " | Contact ID: $contact_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>