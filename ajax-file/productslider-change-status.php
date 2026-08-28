<?php
// السماح بطلبات POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo '0';
    exit;
}

session_start();

require_once dirname(__DIR__) . "/../common.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    echo '0';
    exit;
}

$stat = isset($_POST['stat']) ? (int)$_POST['stat'] : -1;
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0 || ($stat != 0 && $stat != 1)) {
    echo '0';
    exit;
}

global $con;
$sql = "UPDATE prodservice_slider SET adv_status = $stat WHERE adv_id = $id";
if (mysqli_query($con, $sql)) {
    echo '1';
} else {
    echo '0';
}
?>