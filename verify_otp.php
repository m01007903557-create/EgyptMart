<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/common.php';
require_once __DIR__ . '/whats360-config.php';

global $con;
egyptmart_otp_table($con);

$request_id = (int)($_POST['request_id'] ?? ($_SESSION['otp_request_id'] ?? 0));
$mobile = egyptmart_normalize_mobile((string)($_POST['mobile'] ?? ($_SESSION['otp_mobile'] ?? '')));
$code = preg_replace('/[^0-9]/', '', (string)($_POST['code'] ?? ''));

if ($request_id <= 0 && $mobile === '') {
    egyptmart_json(['status' => 'error', 'msg' => 'OTP request not found.']);
}

if ($code !== '') {
    $stmt = mysqli_prepare($con, "UPDATE egyptmart_otp_requests SET status = 'verified', verified_at = NOW() WHERE status = 'pending' AND expires_at >= NOW() AND otp_code = ? AND (id = ? OR mobile = ?) LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'sis', $code, $request_id, $mobile);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$stmt = mysqli_prepare($con, "SELECT id, status, mobile FROM egyptmart_otp_requests WHERE (id = ? OR mobile = ?) ORDER BY id DESC LIMIT 1");
mysqli_stmt_bind_param($stmt, 'is', $request_id, $mobile);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $rowId, $rowStatus, $rowMobile);
$row = mysqli_stmt_fetch($stmt) ? array('id' => $rowId, 'status' => $rowStatus, 'mobile' => $rowMobile) : null;
mysqli_stmt_close($stmt);

if (!$row) {
    egyptmart_json(['status' => 'error', 'msg' => 'OTP request not found or expired.']);
}

if ($row['status'] === 'verified') {
    $_SESSION['otp_verified'] = true;
    $_SESSION['otp_mobile'] = $row['mobile'];
    $verifiedMobile = egyptmart_normalize_mobile((string)$row['mobile']);
    $localTen = strlen($verifiedMobile) > 10 ? substr($verifiedMobile, -10) : $verifiedMobile;
    $localWithZero = '0' . ltrim($localTen, '0');
    $stmt = mysqli_prepare($con, "SELECT usr_id, email FROM user WHERE mobile1 IN (?, ?, ?) OR REPLACE(REPLACE(country_ph_code, '+', ''), ' ', '') = LEFT(?, LENGTH(REPLACE(REPLACE(country_ph_code, '+', ''), ' ', ''))) AND mobile1 = ? ORDER BY usr_id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, 'sssss', $verifiedMobile, $localTen, $localWithZero, $verifiedMobile, $localTen);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $userId, $userEmail);
    $existingUser = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    if ($existingUser && (int)$userId > 0) {
        $_SESSION['uid_indm'] = (int)$userId;
        $_SESSION['eml_indm'] = (string)$userEmail;
        setcookie('cook_usr_id', (string)$userId, time() + (86400 * 300), '/');
        egyptmart_json([
            'status' => 'success',
            'logged_in' => true,
            'redirect' => 'my-dashboard.php',
            'msg' => 'Mobile number verified. Login successful.'
        ]);
    }
    egyptmart_json([
        'status' => 'success',
        'logged_in' => false,
        'redirect' => 'create_account.php?mobile=' . rawurlencode($verifiedMobile) . '#signupform',
        'msg' => 'Mobile number verified. Complete your account details.'
    ]);
}

egyptmart_json(['status' => 'error', 'msg' => 'Invalid or expired OTP code.']);
