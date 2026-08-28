<?php
/**
 * File: states_update.php
 * Description: تحديث ولاية (AJAX)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['state_id']) || !isset($_POST['state_name'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$stateId = (int)$_POST['state_id'];
$stateName = trim($_POST['state_name']);

// التحقق من صحة البيانات
if ($stateId <= 0 || empty($stateName)) {
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

// تحديث الولاية
$result = $statesManager->updateState($stateId, $stateName);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'State updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update state']);
}
?>