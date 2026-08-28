<?php
/**
 * File: ajax/getSellOfferImage.php
 * Description: جلب صورة عرض البيع للمستخدم
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

// التحقق من وجود معرف المستخدم
if (!isset($_GET['usr']) || empty($_GET['usr'])) {
    http_response_code(400);
    die("User ID is required");
}

// تنظيف معرف المستخدم
$usr = trim($_GET['usr']);
$usr = preg_replace('/[^a-zA-Z0-9_]/', '', $usr);

if (empty($usr)) {
    http_response_code(400);
    die("Invalid user ID");
}

global $con;

// جلب الصورة من قاعدة البيانات
$sqlImg = "SELECT tsi_image FROM temp_selloffer_image WHERE tsi_usr_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sqlImg);
mysqli_stmt_bind_param($stmt, 's', $usr);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$default_image = "upload/sale_offer/no-image.png";

if (mysqli_num_rows($result) > 0) {
    $rowImg = mysqli_fetch_object($result);
    $image_name = $rowImg->tsi_image ?? '';
    
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