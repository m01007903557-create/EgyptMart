<?php
/**
 * File: ajax/showProductImagelogo.php

 * Description: جلب شعار المنتج (الصورة الرئيسية)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المنتج
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die("Invalid product ID");
}

$pd_id = (int)$_GET['id'];

global $con;

// جلب صورة المنتج من قاعدة البيانات
$sqlImg = "SELECT pd_imagelogo FROM products WHERE pd_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sqlImg);
mysqli_stmt_bind_param($stmt, 'i', $pd_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rowImg = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

$default_image = "upload/myproduct/no-image.png";

if ($rowImg && !empty($rowImg->pd_imagelogo)) {
    // تقسيم الصور وفصلها
    $images = explode(',', $rowImg->pd_imagelogo);
    
    // الحصول على أول صورة (الصورة الرئيسية)
    $first_image = $images[0] ?? '';
    
    // تنظيف اسم الصورة
    $first_image = basename($first_image);
    $first_image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $first_image);
    
    if (!empty($first_image)) {
        $image_path = "upload/myproduct/" . $first_image;
        
        // التحقق من وجود الملف فعلياً
        $full_path = __DIR__ . "/../" . $image_path;
        if (file_exists($full_path) && is_file($full_path)) {
            echo $image_path;
            exit;
        }
    }
}

// إذا لم يتم العثور على صورة، إرجاع الصورة الافتراضية
echo $default_image;
?>