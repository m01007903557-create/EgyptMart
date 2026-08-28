<?php
/**
 * File: UpdateTheSession.php

 * Description: حفظ معرف النافذة المنبثقة في الجلسة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

session_start();

// التحقق من وجود المعرف
if (!isset($_POST['id'])) {
    http_response_code(400);
    echo "0|ID is required";
    exit;
}

$id = trim($_POST['id']);

// تخزين المعرف في الجلسة
$_SESSION["popup"] = $id;

echo "1|Session saved successfully";
?>