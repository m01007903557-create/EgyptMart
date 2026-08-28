<?php
/**
 * File: ajax/showcity.php

 * Description: البحث عن المدن حسب دولة المستخدم (AutoComplete)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    exit;
}

// التحقق من وجود نص البحث
if (!isset($_GET['q']) || empty($_GET['q'])) {
    exit;
}

$uid = (int)$_SESSION['uid_indm'];
$search_term = trim($_GET['q']);

global $con;

// تنظيف نص البحث
$search_term = mysqli_real_escape_string($con, $search_term);
$search_pattern = $search_term . '%';

// جلب دولة المستخدم
$sqlcn = "SELECT country FROM user WHERE usr_id = ? AND status = '1' LIMIT 1";
$stmt_cn = mysqli_prepare($con, $sqlcn);
mysqli_stmt_bind_param($stmt_cn, 'i', $uid);
mysqli_stmt_execute($stmt_cn);
$result_cn = mysqli_stmt_get_result($stmt_cn);
$rowcn = mysqli_fetch_assoc($result_cn);
mysqli_stmt_close($stmt_cn);

if (!$rowcn || empty($rowcn['country'])) {
    exit;
}

$country_id = (int)$rowcn['country'];

// البحث عن المدن
$sql = "SELECT ct_id, ct_name, ct_state 
        FROM city 
        WHERE ct_cn_id = ? 
        AND ct_name LIKE ? 
        AND ct_status = '1' 
        ORDER BY ct_name ASC 
        LIMIT 50";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'is', $country_id, $search_pattern);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_object($result)) {
        $city_id = (int)$row->ct_id;
        $city_name = ucfirst($row->ct_name ?? '');
        $state_id = (int)$row->ct_state;
        
        // جلب اسم المنطقة/الولاية
        $sqlstate = "SELECT state_name 
                     FROM states 
                     WHERE state_id = ? 
                     AND state_status = '1' 
                     LIMIT 1";
        
        $stmt_state = mysqli_prepare($con, $sqlstate);
        mysqli_stmt_bind_param($stmt_state, 'i', $state_id);
        mysqli_stmt_execute($stmt_state);
        $result_state = mysqli_stmt_get_result($stmt_state);
        $rowstate = mysqli_fetch_object($result_state);
        mysqli_stmt_close($stmt_state);
        
        $state_name = $rowstate ? ucfirst($rowstate->state_name ?? '') : '';
        
        // تنسيق النتيجة: CityName>>StateName|CityID|StateID
        echo htmlspecialchars($city_name, ENT_QUOTES, 'UTF-8') . '>>' . 
             htmlspecialchars($state_name, ENT_QUOTES, 'UTF-8') . '|' . 
             $city_id . '|' . $state_id . "\n";
    }
}

mysqli_stmt_close($stmt);
?>