<?php
/**
 * File: ajax/isEmailExist.php

 * Description: التحقق من وجود البريد الإلكتروني في قاعدة البيانات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود البريد الإلكتروني
if (!isset($_POST['eml']) || empty(trim($_POST['eml']))) {
    http_response_code(400);
    echo "0|Email is required";
    exit;
}

$email = trim($_POST['eml']);

// التحقق من صحة البريد الإلكتروني
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "0|Invalid email format";
    exit;
}

global $con;

// التحقق من وجود البريد الإلكتروني في قاعدة البيانات
$sql_chk = "SELECT usr_id FROM user WHERE email = ? AND status = '1' LIMIT 1";
$stmt = mysqli_prepare($con, $sql_chk);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo "1";
} else {
    echo "0";
}

mysqli_stmt_close($stmt);
?>