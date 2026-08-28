<?php
/**
 * File: updateList.php

 * Description: تحديث ترتيب عناصر "من نحن" (About Us)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['update']) || $_POST['update'] !== 'update' || !isset($_POST['arrayorder'])) {
    http_response_code(400);
    echo '<font color="red">Invalid request</font>';
    exit;
}

$arrayorder = $_POST['arrayorder'];

if (!is_array($arrayorder) || empty($arrayorder)) {
    http_response_code(400);
    echo '<font color="red">No items to update</font>';
    exit;
}

global $con;

// بدء المعاملة
mysqli_begin_transaction($con);

try {
    $count = 1;
    $updated_count = 0;
    
    foreach ($arrayorder as $idval) {
        $idval = (int)$idval;
        if ($idval <= 0) continue;
        
        $query = "UPDATE about_us SET abtus_order = ? WHERE abtus_id = ?";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 'ii', $count, $idval);
        
        if (mysqli_stmt_execute($stmt)) {
            $updated_count++;
        }
        mysqli_stmt_close($stmt);
        $count++;
    }
    
    // تأكيد المعاملة
    mysqli_commit($con);
    
    echo '<font color="green">Titles has been changed successfully (' . $updated_count . ' items updated)</font>';
    
} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Update About Us Order Error: " . $e->getMessage());
    echo '<font color="red">Failed to update order</font>';
}
?>