<?php
/**
 * File: ajax/about-us-edit.php

 * Description: تحديث محتوى "من نحن" (العنوان والوصف)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "||Unauthorized";
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف المحتوى
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "||Invalid content ID";
    exit;
}

$id = (int)$_GET['id'];
$abtusheading = trim($_GET['abtusheading'] ?? '');
$abtusdesc = trim($_GET['abtusdesc'] ?? '');
$totaldesc = strlen($abtusdesc);

$msg = '';
$msg1 = '';

global $con;

// التحقق من صحة البيانات
if (empty($abtusheading)) {
    $msg = "Please check that Profile Heading cannot be blank";
} elseif (empty($abtusdesc)) {
    $msg = "Please check that Profile Description cannot be blank";
} elseif ($totaldesc > 4000) {
    $msg = "Please check that Profile Description cannot have more than 4000 characters.";
} else {
    // بدء المعاملة (Transaction) لضمان التكامل
    mysqli_begin_transaction($con);
    
    try {
        // التحقق من ملكية المحتوى
        $check_sql = "SELECT abtus_id FROM about_us 
                      WHERE abtus_id = ? AND abtus_usr_id = ? 
                      LIMIT 1";
        
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'ii', $id, $uid);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) == 0) {
            throw new Exception("Content not found or you don't have permission to edit it");
        }
        mysqli_stmt_close($stmt_check);

        // تحديث المحتوى
        $update_sql = "UPDATE about_us SET abtus_ph_id = ?, abtus_desc = ? WHERE abtus_id = ?";
        $stmt_update = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt_update, 'ssi', $abtusheading, $abtusdesc, $id);

        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception("Failed to update content: " . mysqli_error($con));
        }

        mysqli_stmt_close($stmt_update);

        // تأكيد المعاملة
        mysqli_commit($con);

        $msg1 = "Content Saved Successfully!";

    } catch (Exception $e) {
        // تراجع عن المعاملة في حالة الخطأ
        mysqli_rollback($con);
        error_log("Update About Us Error: " . $e->getMessage() . " | ID: $id, User: $uid");
        $msg = $e->getMessage();
    }
}

echo $msg . "||" . $msg1;
?>