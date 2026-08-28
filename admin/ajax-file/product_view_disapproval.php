<?php
declare(strict_types=1);

session_start();
require_once "../../common.php";

// التحقق من تسجيل دخول المشرف
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح به']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    global $con;
    $sql = "UPDATE products SET pd_status = '2' WHERE pd_id = '$id'";
    if (mysqli_query($con, $sql)) {
        echo json_encode(['success' => true, 'message' => 'تم الرفض بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات: ' . mysqli_error($con)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
}
?>