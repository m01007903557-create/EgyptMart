<?php
/**
 * File: ajax/showSaleofferImage.php

 * Description: جلب صورة عرض البيع
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف عرض البيع
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    die("Invalid offer ID");
}

$so_id = (int)$_GET['id'];

global $con;

// جلب صورة عرض البيع من قاعدة البيانات
$sqlImg = "SELECT so_pic FROM sale_offer WHERE so_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sqlImg);
mysqli_stmt_bind_param($stmt, 'i', $so_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$rowImg = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

$default_image = "upload/sale_offer/no-image.png";

if ($rowImg && !empty($rowImg->so_pic)) {
    $image_name = $rowImg->so_pic;
    
    // تنظيف اسم الصورة
    $image_name = basename($image_name);
    $image_name = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $image_name);
    
    if (!empty($image_name)) {
        $image_path = "upload/sale_offer/" . $image_name;
        
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