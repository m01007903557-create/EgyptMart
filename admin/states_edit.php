<?php
/**
 * File: states_edit.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: تعديل اسم ولاية (AJAX)
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
$stateName = isset($_GET['states_inp']) ? trim($_GET['states_inp']) : '';

if ($stateId <= 0 || empty($stateName)) {
    exit;
}

global $con;
$sql = "UPDATE states SET st_name = ? WHERE st_id = ?";
$stmt = mysqli_prepare($con, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $stateName, $stateId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

echo htmlspecialchars($stateName, ENT_QUOTES, 'UTF-8');
ob_end_flush();
?>