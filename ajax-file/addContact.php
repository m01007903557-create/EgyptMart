<?php
/**
 * File: ajax/addContact.php

 * Description: إضافة جهة اتصال جديدة للشركة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "0|Unauthorized";
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود البيانات الأساسية
if (!isset($_GET['fname']) || empty(trim($_GET['fname']))) {
    http_response_code(400);
    echo "0|First name is required";
    exit;
}

// تنظيف وتحضير البيانات
$division = trim($_GET['division'] ?? '');
$prefix = trim($_GET['prefix'] ?? '');
$fname = trim($_GET['fname'] ?? '');
$lname = trim($_GET['lname'] ?? '');
$address = trim($_GET['address'] ?? '');
$address1 = trim($_GET['address1'] ?? '');
$country = (int)($_GET['country'] ?? 0);
$phareacode = trim($_GET['phareacode'] ?? '');
$telephone = trim($_GET['telephone'] ?? '');
$mobile = trim($_GET['mobile'] ?? '');
$faxareacode = trim($_GET['faxareacode'] ?? '');
$fax = trim($_GET['fax'] ?? '');
$email = trim($_GET['email'] ?? '');
$phcode = trim($_GET['phcode'] ?? '');

global $con;

// بدء المعاملة (Transaction) لضمان التكامل
mysqli_begin_transaction($con);

try {
    // التحقق من عدم تكرار البريد الإلكتروني إذا كان موجوداً
    if (!empty($email)) {
        $check_sql = "SELECT comp_cnt_id FROM company_contact 
                      WHERE comp_cnt_email = ? AND comp_cnt_user = ? 
                      LIMIT 1";
        $stmt_check = mysqli_prepare($con, $check_sql);
        mysqli_stmt_bind_param($stmt_check, 'si', $email, $uid);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            throw new Exception("Email already exists for this user");
        }
        mysqli_stmt_close($stmt_check);
    }

    // إدراج جهة الاتصال الجديدة
    $insert_sql = "INSERT INTO company_contact SET
                    comp_cnt_division = ?,
                    comp_cnt_user = ?,
                    comp_cnt_prefix = ?,
                    comp_cnt_fname = ?,
                    comp_cnt_lname = ?,
                    comp_cnt_address = ?,
                    comp_cnt_address1 = ?,
                    comp_cnt_country = ?,
                    comp_cnt_phcntode = ?,
                    comp_cnt_phareacode = ?,
                    comp_cnt_telephone = ?,
                    comp_cnt_mobile = ?,
                    comp_cnt_faxareacode = ?,
                    comp_cnt_fax = ?,
                    comp_cnt_email = ?";

    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'sisssssisssssss', 
        $division,
        $uid,
        $prefix,
        $fname,
        $lname,
        $address,
        $address1,
        $country,
        $phcode,
        $phareacode,
        $telephone,
        $mobile,
        $faxareacode,
        $fax,
        $email
    );

    if (!mysqli_stmt_execute($stmt_insert)) {
        throw new Exception("Failed to add contact: " . mysqli_error($con));
    }

    $new_id = mysqli_insert_id($con);
    mysqli_stmt_close($stmt_insert);

    // تأكيد المعاملة
    mysqli_commit($con);

    echo "1|Contact added successfully|$new_id";

} catch (Exception $e) {
    // تراجع عن المعاملة في حالة الخطأ
    mysqli_rollback($con);
    error_log("Add Company Contact Error: " . $e->getMessage() . " | User: $uid");
    http_response_code(500);
    echo "0|" . $e->getMessage();
}
?>