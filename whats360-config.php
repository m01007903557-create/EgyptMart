<?php
declare(strict_types=1);

const WHATS360_API_BASE_URL = 'https://whats360.live/api/v1';
const WHATS360_API_TOKEN = '6f18bcc1a5475ffb1e6496bbd7a6da49c649fbb449ff78929dfc7c6ef95e738a';
const WHATS360_INSTANCE_ID = 'egyptmart-otp';
const WHATS360_OTP_TTL_MINUTES = 5;
const WHATS360_WEBHOOK_URL = 'https://egyptmart.shop/webhook.php';

function egyptmart_normalize_mobile(string $mobile): string
{
    $mobile = preg_replace('/[^0-9+]/', '', trim($mobile));
    if (strpos($mobile, '+') === 0) {
        $mobile = substr($mobile, 1);
    }
    if (strpos($mobile, '00') === 0) {
        $mobile = substr($mobile, 2);
    }
    if (strpos($mobile, '0') === 0) {
        $mobile = '20' . substr($mobile, 1);
    }
    return $mobile;
}

function egyptmart_otp_table(mysqli $con): void
{
    mysqli_query($con, "CREATE TABLE IF NOT EXISTS egyptmart_otp_requests (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        mobile VARCHAR(32) NOT NULL,
        otp_code VARCHAR(12) NOT NULL,
        status ENUM('pending','verified','expired','failed') NOT NULL DEFAULT 'pending',
        api_response TEXT NULL,
        webhook_payload TEXT NULL,
        created_at DATETIME NOT NULL,
        expires_at DATETIME NOT NULL,
        verified_at DATETIME NULL,
        PRIMARY KEY (id),
        KEY idx_mobile_status (mobile, status),
        KEY idx_expires_at (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
}

function egyptmart_json(array $payload): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}
