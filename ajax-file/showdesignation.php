<?php
/**
 * File: ajax/showdesignation.php

 * Description: البحث عن المسميات الوظيفية (AutoComplete)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود نص البحث
if (!isset($_GET['q']) || empty($_GET['q'])) {
    exit;
}

global $con;

// تنظيف نص البحث
$search_term = trim($_GET['q']);
$search_term = mysqli_real_escape_string($con, $search_term);
$search_pattern = $search_term . '%';

// استعلام البحث عن المسميات الوظيفية
$sql = "SELECT desig_id, desig_title 
        FROM designation 
        WHERE desig_title LIKE ? 
        AND desig_status = '1' 
        ORDER BY desig_title ASC 
        LIMIT 50"; // تحديد حد أقصى للنتائج

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $search_pattern);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_object($result)) {
        $desig_id = (int)$row->desig_id;
        $desig_title = ucfirst($row->desig_title ?? '');
        
        // تنسيق النتيجة: DesignationTitle|DesignationID
        echo htmlspecialchars($desig_title, ENT_QUOTES, 'UTF-8') . '|' . $desig_id . "\n";
    }
}

mysqli_stmt_close($stmt);
?>