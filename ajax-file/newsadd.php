<?php
/**
 * File: ajax/newsadd.php

 * Description: حفظ خبر أو بيان صحفي مع الصور المصغرة والكبيرة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    echo "Please login first||0";
    exit;
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود معرف المستخدم في GET
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || (int)$_GET['id'] !== $uid) {
    echo "Invalid user ID||0";
    exit;
}

// تنظيف وتحضير البيانات
$id = $uid; // استخدام معرف المستخدم من الجلسة
$nws_postdate = trim($_GET['nws_postdate'] ?? '');
$nws_medianm = trim($_GET['nws_medianm'] ?? '');
$nws_mediatyp = trim($_GET['nws_mediatyp'] ?? '');
$nws_headline = trim($_GET['nws_headline'] ?? '');
$nws_covgurl = trim($_GET['nws_covgurl'] ?? '');
$nws_covgdet = trim($_GET['nws_covgdet'] ?? '');

$msg = '';
$error = 1;

global $con;

// التحقق من صحة الرابط إذا كان موجوداً
if (!empty($nws_covgurl) && !class_exists('validate')) {
    // دالة بسيطة للتحقق من الرابط إذا لم تكن كلاس validate موجوداً
    function is_valid_url(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    $is_valid = is_valid_url($nws_covgurl);
} elseif (!empty($nws_covgurl)) {
    $is_valid = validate::is_weblink($nws_covgurl);
} else {
    $is_valid = true;
}

if (!empty($nws_covgurl) && !$is_valid) {
    $msg = "Please Enter a Valid url link";
    $error = 0;
} elseif (empty($nws_covgdet)) {
    $msg = "News / Press Release Detail cannot be blank.";
    $error = 0;
} elseif (strlen($nws_covgdet) > 4000) {
    $msg = "News / Press Release Detail cannot have more than 4000 characters.";
    $error = 0;
} else {
    // جلب الصورة المصغرة
    $tmpsml_sql = "SELECT tmpns_image FROM temp_newsimage 
                   WHERE tmpns_uid = ? AND tmpns_status = '1' 
                   ORDER BY tmpns_id DESC LIMIT 1";
    $stmt_sml = mysqli_prepare($con, $tmpsml_sql);
    mysqli_stmt_bind_param($stmt_sml, 'i', $id);
    mysqli_stmt_execute($stmt_sml);
    $result_sml = mysqli_stmt_get_result($stmt_sml);
    $tmpsmlimgrow = mysqli_fetch_object($result_sml);
    mysqli_stmt_close($stmt_sml);
    
    // جلب الصورة الكبيرة
    $tmplrg_sql = "SELECT tmpns_image FROM temp_newsimage 
                   WHERE tmpns_uid = ? AND tmpns_status = '2' 
                   ORDER BY tmpns_id DESC LIMIT 1";
    $stmt_lrg = mysqli_prepare($con, $tmplrg_sql);
    mysqli_stmt_bind_param($stmt_lrg, 'i', $id);
    mysqli_stmt_execute($stmt_lrg);
    $result_lrg = mysqli_stmt_get_result($stmt_lrg);
    $tmplrgimgrow = mysqli_fetch_object($result_lrg);
    mysqli_stmt_close($stmt_lrg);
    
    $small_image = $tmpsmlimgrow ? ($tmpsmlimgrow->tmpns_image ?? '') : '';
    $large_image = $tmplrgimgrow ? ($tmplrgimgrow->tmpns_image ?? '') : '';
    
    // إدراج الخبر
    $insert_sql = "INSERT INTO news SET
                   nws_uid = ?,
                   nws_medianm = ?,
                   nws_mediatyp = ?,
                   nws_headline = ?,
                   nws_covgurl = ?,
                   nws_covgdet = ?,
                   nws_smallimg = ?,
                   nws_largeimg = ?,
                   nws_postdate = ?";
    
    $stmt_insert = mysqli_prepare($con, $insert_sql);
    mysqli_stmt_bind_param($stmt_insert, 'issssssss', 
        $id,
        $nws_medianm,
        $nws_mediatyp,
        $nws_headline,
        $nws_covgurl,
        $nws_covgdet,
        $small_image,
        $large_image,
        $nws_postdate
    );
    
    if (mysqli_stmt_execute($stmt_insert)) {
        mysqli_stmt_close($stmt_insert);
        
        // حذف الصور المؤقتة
        $delete_sql = "DELETE FROM temp_newsimage WHERE tmpns_uid = ?";
        $stmt_delete = mysqli_prepare($con, $delete_sql);
        mysqli_stmt_bind_param($stmt_delete, 'i', $id);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
        
        $msg = "News saved successfully";
        $error = 1;
    } else {
        error_log("Save News Error: " . mysqli_error($con) . " | User: $id");
        $msg = "Failed to save news";
        $error = 0;
    }
}

echo $msg . "||" . $error;
?>