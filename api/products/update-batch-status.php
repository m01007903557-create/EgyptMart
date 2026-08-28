<?php
// api/products/update-batch-status.php
require_once __DIR__ . '/includes/auth.php';
authenticate();

$input = json_decode(file_get_contents('php://input'), true);
$batch_id = $input['batch_id'] ?? '';
$new_status = $input['status'] ?? '';

if (empty($batch_id) || empty($new_status)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing batch_id or status']);
    exit;
}

$sql = "UPDATE products SET processing_status = ? WHERE batch_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $new_status, $batch_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

echo json_encode([
    'status' => 'success',
    'batch_id' => $batch_id,
    'processing_status' => $new_status,
    'message' => 'Batch status updated'
]);
?>