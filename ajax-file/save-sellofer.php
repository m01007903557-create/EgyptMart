<?php
/**
 * File: ajax/save-sellofer.php

 * Description: حفظ تصنيف تنبيهات طلبات الشراء للمستخدم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "You are not authorized to do this";
    exit;
}

// التحقق من وجود معرف التصنيف
if (!isset($_POST['cat_id']) || !is_numeric($_POST['cat_id'])) {
    http_response_code(400);
    echo "Invalid Request";
    exit;
}

$user_id = (int)$_SESSION['uid_indm'];
$category_id = (int)$_POST['cat_id'];

global $con;

// التحقق من عدم وجود التصنيف مسبقاً
$check_query = "SELECT bac_id FROM buylead_alert_category 
                WHERE bac_pc_id = ? AND bac_usr_id = ? 
                LIMIT 1";

$stmt_check = mysqli_prepare($con, $check_query);
mysqli_stmt_bind_param($stmt_check, 'ii', $category_id, $user_id);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$exists = mysqli_num_rows($result_check) > 0;
mysqli_stmt_close($stmt_check);

if (!$exists) {
    // حفظ التصنيف
    $insert_query = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) 
                     VALUES (?, ?, NOW())";
    
    $stmt_insert = mysqli_prepare($con, $insert_query);
    mysqli_stmt_bind_param($stmt_insert, 'ii', $user_id, $category_id);
    
    if (mysqli_stmt_execute($stmt_insert)) {
        echo "1|Category saved successfully";
    } else {
        error_log("Save Buy Alert Error: " . mysqli_error($con));
        echo "0|Error while saving category";
    }
    
    mysqli_stmt_close($stmt_insert);
} else {
    echo "1|Category already exists";
}
?>