<?php
/**
 * File: ajax/addTempSellofferAlertCat.php

 * Description: إضافة تصنيف مؤقت لتنبيهات البيع
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

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من عدم وجود التصنيف مسبقاً
    $check_sql = "SELECT tsac_id FROM temp_selloffer_alert_cat 
                  WHERE tsac_usr_id = ? AND tsac_pc_id = ? 
                  LIMIT 1";
    
    $stmt_check = mysqli_prepare($con, $check_sql);
    mysqli_stmt_bind_param($stmt_check, 'ii', $user_id, $category_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        mysqli_stmt_close($stmt_check);
        echo "1|Category already exists in temporary list";
        exit;
    }
    mysqli_stmt_close($stmt_check);

    // إدراج التصنيف
    $insert_sql = "INSERT INTO temp_selloffer_alert_cat (tsac_usr_id, tsac_pc_id, tsac_updated_date) 
                   VALUES (?, ?, NOW())";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'ii', $user_id, $category_id);

    if (!mysqli_stmt_execute($stmt_insert)) {
        throw new Exception("Failed to insert category: " . mysqli_error($con));
    }

    mysqli_stmt_close($stmt_insert);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Category added to temporary list successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Add Temp Sell Alert Error: " . $e->getMessage() . " | User: $user_id, Cat: $category_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>