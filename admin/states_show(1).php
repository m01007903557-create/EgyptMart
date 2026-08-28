<?php
/**
 * File: states_show.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: عرض الولايات بناءً على اختيار الدولة (AJAX)
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من وجود معرف الدولة
if (!isset($_GET['cid']) || !is_numeric($_GET['cid'])) {
    echo '<div class="alert alert-warning">Please select a valid country.</div>';
    exit;
}

$countryId = (int)$_GET['cid'];

// اتصال مباشر بقاعدة البيانات (مؤقت للتشخيص)
$db_host = 'localhost';
$db_user = 'u397968200_arabuser';
$db_pass = 'ANAehab@64';
$db_name = 'u397968200_egmart';

$con = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$con) {
    error_log("states_show.php: Connection failed - " . mysqli_connect_error());
    echo '<div class="alert alert-danger">Database connection failed.</div>';
    exit;
}

// استعلام بسيط بدون prepared statement للتأكد
$sql = "SELECT st_id, st_name FROM states WHERE st_cn_id = $countryId AND st_status = 1 ORDER BY st_name";
$result = mysqli_query($con, $sql);

if (!$result) {
    error_log("states_show.php: Query failed - " . mysqli_error($con));
    echo '<div class="alert alert-danger">Query error: ' . mysqli_error($con) . '</div>';
    exit;
}

if (mysqli_num_rows($result) == 0) {
    echo '<div class="alert alert-info">No states found for this country.</div>';
} else {
    echo '<h4>States List</h4>';
    echo '<div id="states_container">';
    while ($row = mysqli_fetch_assoc($result)) {
        $stateId = (int)$row['st_id'];
        $stateName = htmlspecialchars($row['st_name'], ENT_QUOTES, 'UTF-8');
        echo '<div class="state-row">' . $stateName . '</div>';
    }
    echo '</div>';
}

mysqli_close($con);
ob_end_flush();
?>