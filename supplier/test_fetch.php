<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'اختبار ناجح',
    'post_data' => $_POST,
    'timestamp' => date('Y-m-d H:i:s')
]);
?>