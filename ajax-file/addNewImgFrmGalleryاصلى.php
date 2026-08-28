<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../common.php';

// تسجيل البيانات المستلمة
file_put_contents(__DIR__ . '/gallery_debug.log', print_r($_POST, true) . "\n", FILE_APPEND);

$imgArr = isset($_POST['imgArr']) ? $_POST['imgArr'] : [];
$usr = isset($_POST['usr']) ? (int)$_POST['usr'] : 0;
$typ = isset($_POST['typ']) ? $_POST['typ'] : 'product';

if ($usr <= 0 || empty($imgArr)) {
    echo "0|Invalid data";
    exit;
}

$targetFolder = __DIR__ . '/../upload/myproduct/';
$galleryFolder = __DIR__ . '/../upload/image_gallery/';

// تسجيل المسارات
file_put_contents(__DIR__ . '/gallery_debug.log', "Gallery folder: $galleryFolder\n", FILE_APPEND);

$savedImages = [];

foreach ($imgArr as $imgId) {
    $imgId = (int)$imgId;
    if ($imgId <= 0) continue;
    
    // جلب اسم الصورة من قاعدة البيانات
    $sql = "SELECT ph_fileName FROM photo WHERE ph_id = $imgId AND ph_u_id = $usr";
    $result = mysqli_query($con, $sql);
    
    file_put_contents(__DIR__ . '/gallery_debug.log', "SQL: $sql\n", FILE_APPEND);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $fileName = $row['ph_fileName'];
        $sourcePath = $galleryFolder . $fileName;
        $targetPath = $targetFolder . $fileName;
        
        file_put_contents(__DIR__ . '/gallery_debug.log', "Source: $sourcePath\nTarget: $targetPath\n", FILE_APPEND);
        
        if (file_exists($sourcePath)) {
            copy($sourcePath, $targetPath);
            chmod($targetPath, 0644);
            $savedImages[] = $fileName;
            echo "Copied: $fileName<br>";
            file_put_contents(__DIR__ . '/gallery_debug.log', "SUCCESS: Copied $fileName\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . '/gallery_debug.log', "ERROR: File not found - $sourcePath\n", FILE_APPEND);
        }
    } else {
        file_put_contents(__DIR__ . '/gallery_debug.log', "ERROR: No image found for ID $imgId\n", FILE_APPEND);
    }
}

if (!empty($savedImages)) {
    $allImages = implode(',', $savedImages);
    mysqli_query($con, "DELETE FROM temp_product_image WHERE tpi_usr_id = $usr AND tpi_type = '$typ'");
    $sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_image, tpi_type) VALUES ($usr, '$allImages', '$typ')";
    if (mysqli_query($con, $sql)) {
        echo "1|Images added from gallery";
    } else {
        echo "0|DB Error: " . mysqli_error($con);
    }
} else {
    echo "0|No images found - Check log for details";
}
?>