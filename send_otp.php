<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/whats360-config.php';

global $con;
egyptmart_otp_table($con);

$mobile = egyptmart_normalize_mobile((string)($_POST['mobile'] ?? ''));
if ($mobile === '' || strlen($mobile) < 10) {
    egyptmart_json(['status' => 'error', 'api_status' => 'invalid_mobile', 'msg' => 'Please enter a valid mobile number.']);
}

$otp = (string) random_int(100000, 999999);
$expires = date('Y-m-d H:i:s', time() + (WHATS360_OTP_TTL_MINUTES * 60));
$now = date('Y-m-d H:i:s');

$stmt = mysqli_prepare($con, "INSERT INTO egyptmart_otp_requests (mobile, otp_code, status, created_at, expires_at) VALUES (?, ?, 'pending', ?, ?)");
mysqli_stmt_bind_param($stmt, 'ssss', $mobile, $otp, $now, $expires);
mysqli_stmt_execute($stmt);
$request_id = mysqli_insert_id($con);
mysqli_stmt_close($stmt);

$_SESSION['otp_request_id'] = $request_id;
$_SESSION['otp_mobile'] = $mobile;

$message = "EgyptMart verification code: {$otp}. Enter this code on EgyptMart to continue. Valid for " . WHATS360_OTP_TTL_MINUTES . " minutes.";
$api_status = 'not_configured';
$api_response = '';

if (WHATS360_API_TOKEN !== '') {
    $curl = curl_init();
    $mobile = preg_replace('/\D+/', '', $mobile);
    $query = http_build_query([
        'token'       => WHATS360_API_TOKEN,
        'instance_id' => WHATS360_INSTANCE_ID,
        'jid'         => $mobile . '@s.whatsapp.net',
        'msg'         => $message,
    ], '', '&', PHP_QUERY_RFC3986);
    $url = rtrim(WHATS360_API_BASE_URL, '/') . '/send-text?' . $query;
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . WHATS360_API_TOKEN,
            'Accept: application/json',
        ],
    ]);
    $api_response = curl_exec($curl);
    $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);
    $decoded_response = is_string($api_response) ? json_decode($api_response, true) : null;
    $provider_success = is_array($decoded_response) && ($decoded_response['success'] ?? false) === true;
    $api_status = ($http_code >= 200 && $http_code < 300 && $curl_error === '' && $provider_success) ? 'sent' : 'failed';
    if ($curl_error !== '') {
        $api_response = $curl_error;
    }
} else {
    $api_response = 'Whats360 API token is missing.';
}

$stmt = mysqli_prepare($con, "UPDATE egyptmart_otp_requests SET api_response = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $api_response, $request_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

$request_status = $api_status === 'sent' ? 'pending' : 'failed';
$stmt = mysqli_prepare($con, "UPDATE egyptmart_otp_requests SET status = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'si', $request_status, $request_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

egyptmart_json([
    'status' => ($api_status === 'sent') ? 'success' : 'error',
    'api_status' => $api_status,
    'request_id' => $request_id,
    'mobile' => $mobile,
    'msg' => $api_status === 'sent'
        ? 'OTP sent on WhatsApp. Please enter the code here to continue.'
        : ($api_status === 'not_configured' ? $api_response : 'WhatsApp OTP could not be sent. Please check the connected device and try again.'),
]);
