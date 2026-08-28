<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../common.php';
require_once __DIR__ . '/../../google-auth-config.php';

function google_auth_fail(string $message): void {
    $_SESSION['msg'] = $message;
    header('Location: /sign-in.php#signupform');
    exit;
}

function google_auth_request(string $url, array $fields = array(), array $headers = array()): array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if (!empty($fields)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        $body = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $context = array('http' => array('timeout' => 20));
        if (!empty($fields)) {
            $context['http']['method'] = 'POST';
            $context['http']['header'] = "Content-Type: application/x-www-form-urlencoded\r\n";
            $context['http']['content'] = http_build_query($fields);
        } elseif (!empty($headers)) {
            $context['http']['header'] = implode("\r\n", $headers) . "\r\n";
        }
        $body = file_get_contents($url, false, stream_context_create($context));
        $code = 200;
    }

    $data = json_decode((string)$body, true);
    return array('code' => $code, 'data' => is_array($data) ? $data : array());
}

function google_auth_default_country(mysqli $con): string {
    $country = '';
    $sql = "SELECT cn_id FROM country WHERE cn_status = '1' AND (LOWER(cn_name) = 'egypt' OR UPPER(cn_code) = 'EG') ORDER BY cn_id ASC LIMIT 1";
    $res = mysqli_query($con, $sql);
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

function google_auth_find_user(mysqli $con, string $email): int {
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

function google_auth_create_user(mysqli $con, array $profile): int {
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

    $country = google_auth_default_country($con);
    $pass = md5(bin2hex(random_bytes(16)));

    $emailEsc = mysqli_real_escape_string($con, $email);
    $fnameEsc = mysqli_real_escape_string($con, $fname);
    $lnameEsc = mysqli_real_escape_string($con, $lname);
    $countryEsc = mysqli_real_escape_string($con, $country);
    $passEsc = mysqli_real_escape_string($con, $pass);

    $sql = "INSERT INTO user SET email = '$emailEsc', fname = '$fnameEsc', lname = '$lnameEsc', country = '$countryEsc', pass = '$passEsc', usr_emailVerify = '1', date = NOW()";
    if (!mysqli_query($con, $sql)) {
        google_auth_fail('Google registration could not be completed. Please try again.');
    }

    $uid = (int)mysqli_insert_id($con);
    $company = mysqli_real_escape_string($con, $name !== '' ? $name : $fname);
    mysqli_query($con, "INSERT INTO business_profile SET bnsprof_uid = '$uid', bnsprof_ceofname = '$fnameEsc', bnsprof_ceolname = '$lnameEsc', bnsprof_compname = '$company', bnsprof_creation_date = NOW()");
    mysqli_query($con, "INSERT INTO website_content SET wc_usr_id = '$uid', wc_updated_date = NOW()");

    return $uid;
}

$request = $_POST + $_GET;

if (!empty($request['error'])) {
    google_auth_fail('Google sign-in was cancelled.');
}

if (empty($request['code']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Google Sign In</title>
</head>
<body>
    <form id="googleTokenForm" method="post" action="/auth/google/token-callback.php">
        <input type="hidden" name="id_token" id="id_token">
        <input type="hidden" name="access_token" id="access_token">
        <input type="hidden" name="state" id="state">
        <input type="hidden" name="error" id="error">
    </form>
    <script>
    (function () {
        var hash = window.location.hash ? window.location.hash.substring(1) : '';
        var params = new URLSearchParams(hash);
        document.getElementById('id_token').value = params.get('id_token') || '';
        document.getElementById('access_token').value = params.get('access_token') || '';
        document.getElementById('state').value = params.get('state') || '';
        document.getElementById('error').value = params.get('error') || '';
        document.getElementById('googleTokenForm').submit();
    })();
    </script>
</body>
</html>
    <?php
    exit;
}

$code = $request['code'] ?? '';
$state = $request['state'] ?? '';
if ($code === '' || $state === '' || empty($_SESSION['google_oauth_state']) || !hash_equals((string)$_SESSION['google_oauth_state'], (string)$state)) {
    google_auth_fail('Google sign-in could not be verified. Please try again.');
}
unset($_SESSION['google_oauth_state']);

$token = google_auth_request('https://oauth2.googleapis.com/token', array(
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code'
));

$accessToken = $token['data']['access_token'] ?? '';
if ($accessToken === '') {
    google_auth_fail('Google sign-in failed. Please try again.');
}

$userinfo = google_auth_request('https://www.googleapis.com/oauth2/v2/userinfo', array(), array('Authorization: Bearer ' . $accessToken));
$profile = $userinfo['data'];
$email = trim((string)($profile['email'] ?? ''));
if ($email === '' || empty($profile['verified_email'])) {
    google_auth_fail('Google account email is not verified.');
}

$uid = google_auth_find_user($con, $email);
if ($uid > 0) {
    $emailEsc = mysqli_real_escape_string($con, $email);
    mysqli_query($con, "UPDATE user SET usr_emailVerify = '1' WHERE usr_id = '$uid' AND email = '$emailEsc'");
} else {
    $uid = google_auth_create_user($con, $profile);
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
