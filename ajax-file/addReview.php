<?php
/**
 * File: ajax/addReview.php

 * Description: تحديث التقييم والمراجعة لمستخدم
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

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    http_response_code(400);
    echo "0|Invalid review ID";
    exit;
}

if (!isset($_POST['r']) || !is_numeric($_POST['r'])) {
    http_response_code(400);
    echo "0|Invalid rating value";
    exit;
}

if (!isset($_POST['rv']) || empty(trim($_POST['rv']))) {
    http_response_code(400);
    echo "0|Review text is required";
    exit;
}

$rr_id = (int)$_POST['id'];
$rr_rating = (int)$_POST['r'];
$rr_review = trim($_POST['rv']);

// التحقق من صحة قيمة التقييم (بين 1 و 5)
if ($rr_rating < 1 || $rr_rating > 5) {
    http_response_code(400);
    echo "0|Rating must be between 1 and 5";
    exit;
}

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // تحديث التقييم
    $update_sql = "UPDATE review_rating 
                   SET rr_rating = ?, rr_review = ?, rr_updated_date = NOW() 
                   WHERE rr_id = ?";
    
    $stmt = mysqli_prepare($con, $update_sql);
    mysqli_stmt_bind_param($stmt, 'isi', $rr_rating, $rr_review, $rr_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Failed to update review: " . mysqli_error($con));
    }

    // التحقق من عدد الصفوف المتأثرة
    if (mysqli_stmt_affected_rows($stmt) == 0) {
        throw new Exception("Review not found or no changes made");
    }

    mysqli_stmt_close($stmt);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Review updated successfully";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Update Review Rating Error: " . $e->getMessage() . " | Review ID: $rr_id");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>