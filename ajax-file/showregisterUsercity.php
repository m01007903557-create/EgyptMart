<?php
/**
 * File: ajax/showregisterUsercity.php
 * Description: البحث عن المدن حسب الدولة واسم المدينة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معاملات البحث
$country = isset($_REQUEST['country']) ? (int)$_REQUEST['country'] : 0;
$q = $_GET['q'] ?? '';

if ($country <= 0 || empty($q)) {
    // لا يوجد نتائج إذا كانت المدخلات غير صالحة
    exit;
}

global $con;

// تنظيف نص البحث
$search_term = trim($q);
$search_term = mysqli_real_escape_string($con, $search_term);

// البحث عن المدن
$sql = "SELECT ct_id, ct_name, ct_state 
        FROM city 
        WHERE ct_cn_id = ? 
        AND ct_name LIKE ? 
        ORDER BY ct_name ASC 
        LIMIT 50"; // تحديد حد أقصى للنتائج

$search_pattern = $search_term . '%';
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'is', $country, $search_pattern);
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