<?php
// ملف تشخيصي - supplier/update_offer_debug.php
session_start();
require_once __DIR__ . '/../lib/connect.php';

// إظهار جميع الأخطاء للتشخيص
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// تنظيف أي مخرجات سابقة
ob_clean();

// دالة مساعدة للرد
function sendDebugResponse($success, $message, $data = []) {
    $response = ['success' => $success, 'message' => $message, 'debug' => $data];
    $json = json_encode($response);
    if ($json === false) {
        $json = json_encode(['success' => false, 'message' => 'JSON encoding failed', 'debug' => ['error' => json_last_error_msg()]]);
    }
    echo $json;
    exit;
}

// تسجيل بداية التنفيذ
sendDebugResponse(false, 'تم تحميل الملف بنجاح، انتظار بيانات POST', [
    'session_exists' => isset($_SESSION['uid_indm']),
    'post_data' => $_POST,
    'request_method' => $_SERVER['REQUEST_METHOD']
]);
?>