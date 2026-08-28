<?php
require_once __DIR__ . '/../common.php';

$usr = isset($_GET['usr']) ? (int)$_GET['usr'] : 0;

if ($usr <= 0) {
    echo '/upload/myproduct/logo_upload.jpg';
    exit;
}

$sql = "SELECT tpi_image FROM temp_product_image WHERE tpi_usr_id = $usr AND tpi_type = 'logo' ORDER BY tpi_id DESC LIMIT 1";
$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $image = trim($row['tpi_image']);
    
    if (!empty($image) && file_exists(__DIR__ . '/../upload/myproduct/' . $image)) {
        echo '/upload/myproduct/' . $image;
        exit;
    }
}

echo '/upload/myproduct/logo_upload.jpg';
?>