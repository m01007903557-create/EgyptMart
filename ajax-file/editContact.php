<?php
/**
 * File: ajax/editContact.php

 * Description: تحديث بيانات جهة اتصال الشركة
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

// التحقق من وجود معرف جهة الاتصال
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "0|Invalid contact ID";
    exit;
}

$contact_id = (int)$_GET['id'];
$current_user = (int)$_SESSION['uid_indm'];

// تنظيف وتحضير البيانات
$division = trim($_GET['division'] ?? '');
$prefix = trim($_GET['prefix'] ?? '');
$fname = trim($_GET['fname'] ?? '');
$lname = trim($_GET['lname'] ?? '');
$address = trim($_GET['address'] ?? '');
$address1 = trim($_GET['address1'] ?? '');
$phareacode = trim($_GET['phareacode'] ?? '');
$telephone = trim($_GET['telephone'] ?? '');
$mobile = trim($_GET['mobile'] ?? '');
$faxareacode = trim($_GET['faxareacode'] ?? '');
$fax = trim($_GET['fax'] ?? '');
$email = trim($_GET['email'] ?? '');

global $con;

// التحقق من وجود جهة الاتصال وصلاحية المستخدم
$check_sql = "SELECT comp_cnt_id FROM company_contact 
              WHERE comp_cnt_id = ? AND comp_cnt_usr_id = ? 
              LIMIT 1";

$stmt_check = mysqli_prepare($con, $check_sql);
mysqli_stmt_bind_param($stmt_check, 'ii', $contact_id, $current_user);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);

if (mysqli_num_rows($result_check) == 0) {
    mysqli_stmt_close($stmt_check);
    http_response_code(403);
    echo "0|Contact not found or access denied";
    exit;
}
mysqli_stmt_close($stmt_check);

// تحديث بيانات جهة الاتصال
$update_sql = "UPDATE company_contact SET 
                comp_cnt_division = ?,
                comp_cnt_prefix = ?,
                comp_cnt_fname = ?,
                comp_cnt_lname = ?,
                comp_cnt_address = ?,
                comp_cnt_address1 = ?,
                comp_cnt_phareacode = ?,
                comp_cnt_telephone = ?,
                comp_cnt_mobile = ?,
                comp_cnt_faxareacode = ?,
                comp_cnt_fax = ?,
                comp_cnt_email = ?
                WHERE comp_cnt_id = ?";

$stmt_update = mysqli_prepare($con, $update_sql);
mysqli_stmt_bind_param($stmt_update, 'ssssssssssssi', 
    $division,
    $prefix,
    $fname,
    $lname,
    $address,
    $address1,
    $phareacode,
    $telephone,
    $mobile,
    $faxareacode,
    $fax,
    $email,
    $contact_id
);

if (mysqli_stmt_execute($stmt_update)) {
    echo "1|Contact updated successfully";
} else {
    error_log("Update Company Contact Error: " . mysqli_error($con) . " | Contact ID: $contact_id");
    echo "0|Failed to update contact";
}

mysqli_stmt_close($stmt_update);
?>