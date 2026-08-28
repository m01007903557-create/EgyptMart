<?php
/**
 * ajax-file/getLastTempImage.php - جلب آخر صورة مرفوعة للمستخدم
 */

session_start();
require_once __DIR__ . '/../lib/connect.php';

header('Content-Type: application/json');

// ✅ التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    die(json_encode(['success' => false, 'error' => 'غير مصرح']));
}

$usr = isset($_GET['usr']) ? (int)$_GET['usr'] : $_SESSION['uid_indm'];

// ✅ جلب آخر صورة للمستخدم
$sql = "SELECT tbi_id, tbi_image FROM temp_buyrequirement_image WHERE tbi_usr_id = $usr ORDER BY tbi_id DESC LIMIT 1";
$res = mysqli_query($con, $sql);

if ($res && mysqli_num_rows($res) > 0) {
    $row = mysqli_fetch_assoc($res);
    echo json_encode(['success' => true, 'id' => $row['tbi_id'], 'image' => $row['tbi_image']]);
} else {
    echo json_encode(['success' => false, 'error' => 'لا توجد صور مرفوعة']);
}
?>