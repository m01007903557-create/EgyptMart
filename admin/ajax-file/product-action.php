<?php
declare(strict_types=1);

ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح به']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_POST['id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'بيانات غير مكتملة']);
    exit;
}

$productId = (int)$_POST['id'];
$action = $_POST['action'];

global $con;

if ($action == 'approve') {
    $sql = "UPDATE products SET pd_status = 1 WHERE pd_id = ?";
} elseif ($action == 'disapprove') {
    $sql = "UPDATE products SET pd_status = 2 WHERE pd_id = ?";
} else {
    echo json_encode(['success' => false, 'message' => 'إجراء غير معروف']);
    exit;
}

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $productId);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true, 'message' => 'تم التحديث بنجاح']);
} else {
    echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . mysqli_error($con)]);
}

mysqli_stmt_close($stmt);
?>