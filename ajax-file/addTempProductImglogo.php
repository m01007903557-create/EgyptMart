<?php
require_once __DIR__ . '/../common.php';

$usr = isset($_POST['usr']) ? (int)$_POST['usr'] : 0;
if ($usr <= 0) {
    die("0|Invalid user ID");
}

if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    die("0|No file uploaded");
}

$file = $_FILES['Filedata'];
$targetFolder = __DIR__ . '/../upload/myproduct/';

if (!is_dir($targetFolder)) mkdir($targetFolder, 0777, true);

$allowed = ['jpg', 'jpeg', 'gif', 'png'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    die("0|Invalid file type");
}

$newFileName = 'logo_' . $usr . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
$targetPath = $targetFolder . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    chmod($targetPath, 0644);
    
    // حذف السجل القديم
    mysqli_query($con, "DELETE FROM temp_product_image WHERE tpi_usr_id = $usr AND tpi_type = 'logo'");
    
    // إدراج السجل الجديد
    $sql = "INSERT INTO temp_product_image (tpi_usr_id, tpi_image, tpi_type) VALUES ($usr, '$newFileName', 'logo')";
    if (mysqli_query($con, $sql)) {
        echo "1|Logo uploaded successfully";
    } else {
        echo "0|Database error: " . mysqli_error($con);
    }
} else {
    echo "0|Upload failed";
}
?>