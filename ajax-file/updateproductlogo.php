<?php
/**
 * File: ajax/deleteProductImage.php
 * Description: حذف صورة من منتج
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/../common.php';

// التحقق من وجود البيانات المطلوبة
if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
    die("Error: Invalid product ID");
}

if (!isset($_REQUEST['imgname']) || empty($_REQUEST['imgname'])) {
    die("Error: Invalid image name");
}

$product_id = (int)$_REQUEST['id'];
$image_name = trim($_REQUEST['imgname']);

// تنظيف اسم الصورة لمنع هجمات directory traversal
$image_name = basename($image_name);
$image_name = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $image_name);

global $con;

// جلب بيانات المنتج
$sql_proImg = "SELECT pd_imagelogo FROM products WHERE pd_id = ?";
$stmt = mysqli_prepare($con, $sql_proImg);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row_proImg = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row_proImg) {
    die("Error: Product not found");
}

// معالجة الصور
$img = $row_proImg->pd_imagelogo ?? '';
$array = !empty($img) ? explode(',', $img) : [];

// البحث عن الصورة وحذفها
if (($key = array_search($image_name, $array, true)) !== false) {
    unset($array[$key]);
    
    // حذف الملف الفعلي
    $pathLrg = __DIR__ . "/../upload/myproduct/" . $image_name;
    if (file_exists($pathLrg) && is_file($pathLrg)) {
        unlink($pathLrg);
    }
}

// تحديث قاعدة البيانات
$new_img = implode(',', $array);
$sql = "UPDATE products SET pd_imagelogo = ? WHERE pd_id = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'si', $new_img, $product_id);

if (mysqli_stmt_execute($stmt)) {
    echo "1|Image deleted successfully";
} else {
    error_log("Delete Product Image Error: " . mysqli_error($con) . " | Product ID: $product_id");
    echo "0|Failed to delete image";
}

mysqli_stmt_close($stmt);
?>