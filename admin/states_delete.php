<?php
/**
 * File: states_delete.php
 * Description: حذف ولاية (AJAX)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود معرف الولاية
if (!isset($_POST['state_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing state ID']);
    exit;
}

$stateId = (int)$_POST['state_id'];

if ($stateId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid state ID']);
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

// حذف الولاية
$result = $statesManager->deleteState($stateId);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'State deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete state']);
}
?>