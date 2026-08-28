<?php
declare(strict_types=1);

session_start();
require_once "../../common.php";

if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    echo json_encode(['success' => false, 'message' => 'غير مصرح به']);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    global $con;
    $sql = "UPDATE products SET pd_status = '1' WHERE pd_id = '$id'";
    if (mysqli_query($con, $sql)) {
        echo json_encode(['success' => true, 'message' => 'تمت الموافقة بنجاح']);
    } else {
        echo json_encode(['success' => false, 'message' => 'خطأ في قاعدة البيانات']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'معرف المنتج غير صالح']);
}
?>