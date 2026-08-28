<?php
/**
 * File: del_states.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: حذف ولاية (AJAX)
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "check_admin.php";

$stateId = isset($_GET['hid']) ? (int)$_GET['hid'] : 0;

if ($stateId <= 0) {
    echo 0;
    exit;
}

global $con;

// Soft delete - تحديث الحالة إلى 0
$sql = "UPDATE states SET st_status = 0 WHERE st_id = ?";
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $stateId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

// جلب country_id لإعادة تحميل القائمة
$sql2 = "SELECT st_cn_id FROM states WHERE st_id = ?";
$stmt2 = mysqli_prepare($con, $sql2);

if ($stmt2) {
    mysqli_stmt_bind_param($stmt2, "i", $stateId);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);
    $row = mysqli_fetch_assoc($result);
    echo isset($row['st_cn_id']) ? (int)$row['st_cn_id'] : 0;
    mysqli_stmt_close($stmt2);
} else {
    echo 0;
}

ob_end_flush();
?>