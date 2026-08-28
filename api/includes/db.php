<?php
// api/includes/db.php
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/connect.php';

$con = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$con) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}
?>