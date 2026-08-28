<?php
// api/newsletters/update-status.php
require_once __DIR__ . '/../includes/auth.php';
authenticate();

global $con;

$input = json_decode(file_get_contents('php://input'), true);
$newsletter_id = (int)($input['newsletter_id'] ?? 0);
$status = $input['status'] ?? 'sent';
$error = $input['error'] ?? null;

if ($newsletter_id <= 0) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid newsletter_id']);
    exit;
}

$sql = "UPDATE newsletter_content 
        SET nc_sent = 1, 
            nc_sent_at = NOW(), 
            nc_status = ?, 
            nc_error = ?
        WHERE nc_id = ?";

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'ssi', $status, $error, $newsletter_id);
mysqli_stmt_execute($stmt);

echo json_encode([
    'status' => 'success',
    'updated' => mysqli_affected_rows($con)
]);
?>