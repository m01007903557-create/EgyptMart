<?php
/**
 * File: ajax/getProductImage.php
 * Description: جلب الصورة الرئيسية للمنتج المؤقت للمستخدم
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
$sqlImg = "SELECT tpi_logo FROM temp_product_image WHERE tpi_usr_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sqlImg);
mysqli_stmt_bind_param($stmt, 's', $usr);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$default_image = "upload/myproduct/add-image.gif";

if (mysqli_num_rows($result) > 0) {
    $rowImg = mysqli_fetch_object($result);
    $tpi_logo = $rowImg->tpi_logo ?? '';
    
    if (!empty($tpi_logo)) {
        // تقسيم الصور وفصلها
        $images = explode(',', $tpi_logo);
        
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
}

// إذا لم يتم العثور على صورة، إرجاع الصورة الافتراضية
echo $default_image;
?>