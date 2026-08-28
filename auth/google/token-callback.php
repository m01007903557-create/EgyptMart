<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../google-auth-config.php';

function google_token_fail(string $message): void {
    $_SESSION['msg'] = $message;
    header('Location: /sign-in.php#signupform');
    exit;
}

function google_token_request(string $url): array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $body = curl_exec($ch);
        curl_close($ch);
    } else {
        $body = file_get_contents($url, false, stream_context_create(array('http' => array('timeout' => 20))));
    }
    $data = json_decode((string)$body, true);
    return is_array($data) ? $data : array();
}

function google_token_default_country(mysqli $con): string {
    $country = '';
    $res = mysqli_query($con, "SELECT cn_id FROM country WHERE cn_status = '1' AND (LOWER(cn_name) = 'egypt' OR UPPER(cn_code) = 'EG') ORDER BY cn_id ASC LIMIT 1");
    if ($res && ($row = mysqli_fetch_assoc($res))) {
        $country = (string)$row['cn_id'];
    }
    if ($country === '') {
        $res = mysqli_query($con, "SELECT cn_id FROM country WHERE cn_status = '1' ORDER BY cn_id ASC LIMIT 1");
        if ($res && ($row = mysqli_fetch_assoc($res))) {
            $country = (string)$row['cn_id'];
        }
    }
    return $country;
}

function google_token_find_user(mysqli $con, string $email): int {
    $uid = 0;
    $stmt = mysqli_prepare($con, "SELECT usr_id FROM user WHERE email = ? AND status = '1' LIMIT 1");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $uid);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
    }
    return (int)$uid;
}

function google_token_create_user(mysqli $con, array $profile): int {
    $email = trim((string)($profile['email'] ?? ''));
    $name = trim((string)($profile['name'] ?? ''));
    $fname = trim((string)($profile['given_name'] ?? ''));
    $lname = trim((string)($profile['family_name'] ?? ''));

    if ($fname === '' && $name !== '') {
        $parts = preg_split('/\s+/', $name, 2);
        $fname = $parts[0] ?? '';
        $lname = $lname !== '' ? $lname : ($parts[1] ?? '');
    }
    if ($fname === '') {
        $fname = strstr($email, '@', true) ?: 'Member';
    }

    $country = google_token_default_country($con);
    $pass = md5(bin2hex(random_bytes(16)));

    $emailEsc = mysqli_real_escape_string($con, $email);
    $fnameEsc = mysqli_real_escape_string($con, $fname);
    $lnameEsc = mysqli_real_escape_string($con, $lname);
    $countryEsc = mysqli_real_escape_string($con, $country);
    $passEsc = mysqli_real_escape_string($con, $pass);

    $sql = "INSERT INTO user SET email = '$emailEsc', fname = '$fnameEsc', lname = '$lnameEsc', country = '$countryEsc', pass = '$passEsc', usr_emailVerify = '1', date = NOW()";
    if (!mysqli_query($con, $sql)) {
        google_token_fail('Google registration could not be completed. Please try again.');
    }

    $uid = (int)mysqli_insert_id($con);
    $company = mysqli_real_escape_string($con, $name !== '' ? $name : $fname);
    mysqli_query($con, "INSERT INTO business_profile SET bnsprof_uid = '$uid', bnsprof_ceofname = '$fnameEsc', bnsprof_ceolname = '$lnameEsc', bnsprof_compname = '$company', bnsprof_creation_date = NOW()");
    mysqli_query($con, "INSERT INTO website_content SET wc_usr_id = '$uid', wc_updated_date = NOW()");

    return $uid;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    google_token_fail('Google sign-in could not be verified. Please try again.');
}

if (!empty($_POST['error'])) {
    google_token_fail('Google sign-in was cancelled.');
}

$state = $_POST['state'] ?? '';
$idToken = $_POST['id_token'] ?? '';
if ($state === '' || $idToken === '' || empty($_SESSION['google_oauth_state']) || !hash_equals((string)$_SESSION['google_oauth_state'], (string)$state)) {
    google_token_fail('Google sign-in could not be verified. Please try again.');
}
unset($_SESSION['google_oauth_state']);

$profile = google_token_request('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
if (($profile['aud'] ?? '') !== GOOGLE_CLIENT_ID) {
    google_token_fail('Google sign-in failed. Please try again.');
}
if (!empty($_SESSION['google_oauth_nonce']) && !hash_equals((string)$_SESSION['google_oauth_nonce'], (string)($profile['nonce'] ?? ''))) {
    google_token_fail('Google sign-in could not be verified. Please try again.');
}
unset($_SESSION['google_oauth_nonce']);

$email = trim((string)($profile['email'] ?? ''));
$verified = (string)($profile['email_verified'] ?? '') === 'true' || $profile['email_verified'] === true;
if ($email === '' || !$verified) {
    google_token_fail('Google account email is not verified.');
}

$uid = google_token_find_user($con, $email);
if ($uid > 0) {
    $emailEsc = mysqli_real_escape_string($con, $email);
    mysqli_query($con, "UPDATE user SET usr_emailVerify = '1' WHERE usr_id = '$uid' AND email = '$emailEsc'");
} else {
    $uid = google_token_create_user($con, $profile);
}

$_SESSION['uid_indm'] = $uid;
$_SESSION['eml_indm'] = $email;
$_SESSION['msg'] = 'Successfully signed in with Google.';

$redirect = $_SESSION['google_redirect_after_login'] ?? '/index.php';
unset($_SESSION['google_redirect_after_login']);
if (strpos($redirect, 'egyptmart.shop') === false && strpos($redirect, '/') !== 0) {
    $redirect = '/index.php';
}

header('Location: ' . $redirect);
exit;
?>
