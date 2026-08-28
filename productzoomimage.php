<?php
/**
 * File: productzoomimage.php
 * Description: عرض صورة المنتج بحجم كبير (Zoom)
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

session_start();

require_once __DIR__ . '/common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;

// التحقق من وجود التوكن
if (!isset($_GET['token'])) {
    die("Invalid request");
}

$token = substr($_GET['token'], 4);

global $con;

// جلب بيانات المنتج
$sql = "SELECT pd_image FROM products WHERE MD5(pd_id) = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, 's', $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    die("Product not found");
}

// تقسيم الصور
$images = explode(',', $row->pd_image ?? '');
$first_image = $images[0] ?? '';

// تنظيف اسم الصورة
$first_image = basename($first_image);
$first_image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $first_image);

// التحقق من وجود الملف
$image_path = "upload/myproduct/" . $first_image;
$full_path = __DIR__ . "/" . $image_path;
$image_exists = !empty($first_image) && file_exists($full_path) && is_file($full_path);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تكبير الصورة</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .image-container {
            text-align: center;
        }
        .image-container img {
            max-width: 100%;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .back-link {
            display: block;
            margin-top: 20px;
            text-align: center;
        }
        .back-link a {
            color: #0066cc;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        .error {
            color: red;
            text-align: center;
            padding: 50px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($image_exists): ?>
        <div class="image-container">
            <img src="<?php echo $image_path; ?>" width="600" height="500" alt="Product Image">
        </div>
        <div class="back-link">
            <a href="javascript:window.close();">إغلاق النافذة</a>
        </div>
        <?php else: ?>
        <div class="error">
            الصورة غير متوفرة
        </div>
        <?php endif; ?>
    </div>
</body>
</html>