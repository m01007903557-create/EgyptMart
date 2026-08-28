<?php
declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../common.php';

if (!isset($_GET['usr']) || empty($_GET['usr'])) {
    echo "upload/myproduct/add-image.gif";
    exit;
}

$usr = trim($_GET['usr']);
$usr = preg_replace('/[^a-zA-Z0-9_]/', '', $usr);

if (empty($usr)) {
    echo "upload/myproduct/add-image.gif";
    exit;
}

global $con;

// ✅ جلب صورة المنتج (بدون tpi_type = logo)
$sqlImg = "SELECT tpi_image FROM temp_product_image WHERE tpi_usr_id = ? AND (tpi_type IS NULL OR tpi_type = '' OR tpi_type = 'product') ORDER BY tpi_id DESC LIMIT 1";
$stmt = mysqli_prepare($con, $sqlImg);
mysqli_stmt_bind_param($stmt, 's', $usr);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

$default_image = "upload/myproduct/add-image.gif";

if (mysqli_num_rows($result) > 0) {
    $rowImg = mysqli_fetch_object($result);
    $tpi_image = $rowImg->tpi_image ?? '';
    
    if (!empty($tpi_image)) {
        $images = explode(',', $tpi_image);
        $first_image = $images[0] ?? '';
        $first_image = basename($first_image);
        $first_image = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $first_image);
        
        if (!empty($first_image)) {
            $image_path = "upload/myproduct/" . $first_image;
            $full_path = __DIR__ . "/../" . $image_path;
            if (file_exists($full_path) && is_file($full_path)) {
                echo $image_path;
                exit;
            }
        }
    }
}

echo $default_image;
?>