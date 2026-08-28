<?php
declare(strict_types=1);

require_once __DIR__ . '/common.php';
require_once __DIR__ . '/whats360-config.php';

global $con;
egyptmart_otp_table($con);

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$message = (string)($data['message'] ?? $data['body'] ?? $data['text'] ?? $data['msg'] ?? $data['content'] ?? '');
$from = (string)($data['from'] ?? $data['mobile'] ?? $data['phone'] ?? $data['sender'] ?? $data['jid'] ?? $data['remoteJid'] ?? '');
if ($message === '' && !empty($data['data']) && is_array($data['data'])) {
    $message = (string)($data['data']['message'] ?? $data['data']['body'] ?? $data['data']['text'] ?? $data['data']['msg'] ?? $data['data']['content'] ?? '');
    $from = (string)($data['data']['from'] ?? $data['data']['mobile'] ?? $data['data']['phone'] ?? $data['data']['sender'] ?? $data['data']['jid'] ?? $data['data']['remoteJid'] ?? $from);
}
$mobile = egyptmart_normalize_mobile($from);
preg_match('/\b(\d{6})\b/', $message, $matches);
$code = $matches[1] ?? '';

if ($mobile === '' || $code === '') {
    egyptmart_json(['status' => 'ignored', 'msg' => 'No mobile or OTP code found.']);
}

$stmt = mysqli_prepare($con, "UPDATE egyptmart_otp_requests SET status = 'verified', verified_at = NOW(), webhook_payload = ? WHERE status = 'pending' AND expires_at >= NOW() AND mobile = ? AND otp_code = ? ORDER BY id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, 'sss', $raw, $mobile, $code);
mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

egyptmart_json([
    'status' => $affected > 0 ? 'success' : 'not_matched',
    'verified' => $affected > 0,
]);
