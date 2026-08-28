<?php
/**
 * File: states_add.php
 * Description: إضافة ولاية جديدة (AJAX)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['country_id']) || !isset($_POST['state_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$countryId = (int)$_POST['country_id'];
$stateName = trim($_POST['state_name']);

// التحقق من صحة البيانات
if ($countryId <= 0 || empty($stateName)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

// ========================================
// استخدام الاتصال من ملف connect.php
// ========================================

require_once __DIR__ . '/../lib/connect.php';

if (!isset($con) || !$con) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// ========================================
// استخدام الكلاس
// ========================================

require_once __DIR__ . '/CountryStatesManager.php';

$statesManager = new CountryStatesManager($con);

// إضافة الولاية
$result = $statesManager->addState($countryId, $stateName);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'State added successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add state']);
}
?>