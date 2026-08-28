<?php
/**
 * File: ajax/addBuyleadAlertCat.php

 * Description: نقل التصنيفات المؤقتة لتنبيهات الشراء إلى الجدول الدائم
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

$user_id = (int)$_SESSION['uid_indm'];

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // جلب التصنيفات المؤقتة
    $select_sql = "SELECT tbac_pc_id FROM temp_buylead_alert_cat WHERE tbac_usr_id = ?";
    $stmt_select = mysqli_prepare($con, $select_sql);
    mysqli_stmt_bind_param($stmt_select, 'i', $user_id);
    mysqli_stmt_execute($stmt_select);
    $result = mysqli_stmt_get_result($stmt_select);

    $insert_count = 0;
    
    while ($row = mysqli_fetch_object($result)) {
        $pc_id = (int)$row->tbac_pc_id;
        
        // التحقق من عدم وجود التصنيف مسبقاً في الجدول الدائم
        $check_sql = "SELECT bac_id FROM buylead_alert_category 
                      WHERE bac_usr_id = ? AND bac_pc_id = ? 
                      LIMIT 1";
        
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'ii', $user_id, $pc_id);
        mysqli_stmt_execute($stmt_check);
        $check_result = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($check_result) == 0) {
            // إدراج التصنيف في الجدول الدائم
            $insert_sql = "INSERT INTO buylead_alert_category (bac_usr_id, bac_pc_id, bac_updated_date) 
                           VALUES (?, ?, NOW())";
            
            $stmt_insert = mysqli_prepare($con, $insert_sql);
            mysqli_stmt_bind_param($stmt_insert, 'ii', $user_id, $pc_id);
            
            if (mysqli_stmt_execute($stmt_insert)) {
                $insert_count++;
            }
            mysqli_stmt_close($stmt_insert);
        }
        
        mysqli_stmt_close($stmt_check);
    }
    
    mysqli_stmt_close($stmt_select);

    // حذف التصنيفات المؤقتة
    $delete_sql = "DELETE FROM temp_buylead_alert_cat WHERE tbac_usr_id = ?";
    $stmt_delete = mysqli_prepare($con, $delete_sql);
    mysqli_stmt_bind_param($stmt_delete, 'i', $user_id);
    
    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Failed to delete temporary categories");
    }
    
    mysqli_stmt_close($stmt_delete);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|$insert_count categories saved successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Save Buy Alert Categories Error: " . $e->getMessage() . " | User ID: $user_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>