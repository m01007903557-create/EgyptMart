<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/google-auth-config.php';

$state = bin2hex(random_bytes(16));
$nonce = bin2hex(random_bytes(16));
$_SESSION['google_oauth_state'] = $state;
$_SESSION['google_oauth_nonce'] = $nonce;

if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) !== false) {
    $_SESSION['google_redirect_after_login'] = $_SERVER['HTTP_REFERER'];
}

$params = array(
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'token id_token',
    'scope' => 'openid email profile',
    'state' => $state,
    'nonce' => $nonce,
    'prompt' => 'select_account'
);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
exit;
?>
