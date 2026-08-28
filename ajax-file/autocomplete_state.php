<?php
/**
 * File: ajax/autocomplete_state.php
 * Description: البحث عن الولايات/المحافظات (AutoComplete) حسب الدولة
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود معاملات البحث
$country = isset($_GET['country']) ? (int)$_GET['country'] : 0;
$q = $_GET['q'] ?? '';

if ($country <= 0 || empty($q)) {
    // لا يوجد نتائج إذا كانت المدخلات غير صالحة
    exit;
}

global $con;

// تنظيف نص البحث
$search_term = trim($q);
$search_term = mysqli_real_escape_string($con, $search_term);
$search_pattern = $search_term . '%';

// البحث عن الولايات
$sql = "SELECT state_id, state_name 
        FROM states 
        WHERE state_status = '1' 
        AND state_name LIKE ? 
        AND state_cn_id = ? 
        ORDER BY state_name ASC 
        LIMIT 50"; // تحديد حد أقصى للنتائج

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'si', $search_pattern, $country);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_object($result)) {
        $state_id = (int)$row->state_id;
        $state_name = ucfirst($row->state_name ?? '');
        
        // تنسيق النتيجة: StateName|StateID
        echo htmlspecialchars($state_name, ENT_QUOTES, 'UTF-8') . '|' . $state_id . "\n";
    }
}

mysqli_stmt_close($stmt);
?>