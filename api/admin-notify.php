<?php
// api/admin-notify.php
require_once __DIR__ . '/includes/auth.php';

authenticate();

$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);
$segments = explode('/', trim($path, '/'));

if ($method === 'POST' && end($segments) === 'notify') {
    $input = json_decode(file_get_contents('php://input'), true);
    $supplier_id = (int)($input['supplier_id'] ?? 0);
    $brand_name = $input['brand_name'] ?? '';
    $products_count = (int)($input['products_count'] ?? 0);
    $action_url = $input['action_url'] ?? '';
    
    // 🔮 هنا سيتم إرسال إشعار إلى الأدمن (سجله في قاعدة البيانات أو أرسل بريد)
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin notified'
    ]);
    exit;
}
?>