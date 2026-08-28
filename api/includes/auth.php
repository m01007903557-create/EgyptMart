<?php
// api/includes/auth.php
require_once __DIR__ . '/db.php';

define('API_TOKEN', 'EhabFawzy@64'); // غير هذا في الإنتاج

function authenticate() {
    $headers = getallheaders();
    $auth_header = $headers['Authorization'] ?? '';
    
    if (strpos($auth_header, 'Bearer ') !== 0) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Missing or invalid Authorization header']);
        exit;
    }
    
    $token = substr($auth_header, 7);
    if ($token !== API_TOKEN) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Invalid Bearer Token']);
        exit;
    }
    
    return true;
}
?>