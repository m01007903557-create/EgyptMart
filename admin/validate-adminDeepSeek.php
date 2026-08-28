<?php
session_start();

require_once dirname(__DIR__) . "/common.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'الرجاء إدخال اسم المستخدم وكلمة المرور';
    header('Location: index.php');
    exit;
}

global $con;
$sql = "SELECT id, username, password, status FROM admin_user WHERE username = '$username'";
$result = mysqli_query($con, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    header('Location: index.php');
    exit;
}

// تحقق من كلمة المرور
if ($user['password'] !== md5($password)) {
    $_SESSION['error'] = 'اسم المستخدم أو كلمة المرور غير صحيحة';
    header('Location: index.php');
    exit;
}

if ($user['status'] != 1) {
    $_SESSION['error'] = 'هذا الحساب غير نشط';
    header('Location: index.php');
    exit;
}

$_SESSION['ad_id_indm'] = $user['id'];
$_SESSION['ad_username_indm'] = $user['username'];
$_SESSION['admin_logged_in'] = true;

header('Location: welcome.php');
exit;
?>