<?php
/**
 * File: setCountryLocation.php
 * Description: تعيين موقع المستخدم (الدولة) في الكوكيز
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();

require_once __DIR__ . '/common.php';

// التحقق من وجود معرف الدولة
if (!isset($_POST['loc_id']) || !is_numeric($_POST['loc_id'])) {
    http_response_code(400);
    echo "0";
    exit;
}

$country_id = (int)$_POST['loc_id'];

// تعيين كوكيز الموقع (صالحة لمدة ساعة)
setcookie("loc_id", (string)$country_id, time() + 3600, "/");

global $con;

// جلب معلومات الدولة
$sql = "SELECT cn_flag FROM country WHERE cn_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $country_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $flag = htmlspecialchars($row['cn_flag'] ?? '', ENT_QUOTES, 'UTF-8');
    echo $flag;
} else {
    echo "0";
}

mysqli_stmt_close($stmt);
?>