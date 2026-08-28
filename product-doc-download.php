<?php
/**
 * File: product-doc-download.php

 * Description: تحميل ملف PDF الخاص بالمنتج
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

require_once __DIR__ . '/common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    http_response_code(401);
    die("Unauthorized");
}

// التحقق من وجود معرف المنتج
if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
    http_response_code(400);
    die("Invalid product ID");
}

$product_id = (int)$_GET['pid'];
$user_id = (int)$_SESSION['uid_indm'];

global $con;

// جلب معلومات الملف مع التحقق من ملكية المنتج
$sql = "SELECT pd_pdf_attach, pd_uid FROM products WHERE pd_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 'i', $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    http_response_code(404);
    die("Product not found");
}

// التحقق من ملكية المنتج (اختياري - يمكن إزالته إذا أردت السماح للجميع بالتحميل)
if ((int)$row->pd_uid !== $user_id) {
    http_response_code(403);
    die("You don't have permission to download this file");
}

// التحقق من وجود الملف
if (empty($row->pd_pdf_attach)) {
    http_response_code(404);
    die("No file attached to this product");
}

$file_path = __DIR__ . "/upload/productdoc/" . $row->pd_pdf_attach;

if (!file_exists($file_path) || !is_file($file_path)) {
    http_response_code(404);
    die("File not found on server");
}

// الحصول على اسم الملف للعرض
$file_name = basename($file_path);
$file_size = filesize($file_path);

// تعيين رؤوس التحميل
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . $file_size);

// تنظيف المخازن المؤقتة
ob_clean();
flush();

// قراءة الملف وإرساله
readfile($file_path);
exit;
?>