<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../common.php';

$usr = isset($_POST['usr']) ? (int)$_POST['usr'] : 0;
if ($usr <= 0) {
    die("0|Invalid user ID");
}

if (empty($_FILES) || !isset($_FILES['Filedata'])) {
    die("0|No file uploaded");
}

$file = $_FILES['Filedata'];
$targetFolder = __DIR__ . '/../upload/buy_requirement/';
$thumbFolder = __DIR__ . '/../upload/buy_requirement/thumb/';

if (!is_dir($targetFolder)) mkdir($targetFolder, 0777, true);
if (!is_dir($thumbFolder)) mkdir($thumbFolder, 0777, true);

$allowed = ['jpg', 'jpeg', 'gif', 'png'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed)) {
    die("0|Invalid file type. Allowed: JPG, PNG, GIF");
}

$newFileName = 'br_' . $usr . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
$targetPath = $targetFolder . $newFileName;
$thumbPath = $thumbFolder . $newFileName;

if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    chmod($targetPath, 0644);
    
    // ==============================================
    // إنشاء صورة مصغرة (Thumbnail)
    // ==============================================
    $thumbCreated = false;
    
    if (function_exists('imagecreatefromjpeg')) {
        list($width, $height) = getimagesize($targetPath);
        $thumbWidth = 100;
        $thumbHeight = 80;
        $thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        if ($ext == 'jpg' || $ext == 'jpeg') {
            $src = imagecreatefromjpeg($targetPath);
            if ($src) {
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                imagejpeg($thumb, $thumbPath, 90);
                imagedestroy($src);
                $thumbCreated = true;
            }
        } elseif ($ext == 'png') {
            $src = imagecreatefrompng($targetPath);
            if ($src) {
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                imagepng($thumb, $thumbPath, 9);
                imagedestroy($src);
                $thumbCreated = true;
            }
        } elseif ($ext == 'gif') {
            $src = imagecreatefromgif($targetPath);
            if ($src) {
                imagecopyresampled($thumb, $src, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
                imagegif($thumb, $thumbPath);
                imagedestroy($src);
                $thumbCreated = true;
            }
        }
        imagedestroy($thumb);
    }
    
    if (!$thumbCreated) {
        // إذا فشل إنشاء الصورة المصغرة، انسخ الملف الأصلي
        copy($targetPath, $thumbPath);
    }
    chmod($thumbPath, 0644);
    // ==============================================
    
    // حذف الصورة القديمة من قاعدة البيانات والملفات
    $sql = "SELECT tbi_image FROM temp_buyrequirement_image WHERE tbi_usr_id = $usr LIMIT 1";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $oldImage = $row['tbi_image'];
        if ($oldImage && file_exists($targetFolder . $oldImage)) {
            unlink($targetFolder . $oldImage);
        }
        if ($oldImage && file_exists($thumbFolder . $oldImage)) {
            unlink($thumbFolder . $oldImage);
        }
    }
    
    mysqli_query($con, "DELETE FROM temp_buyrequirement_image WHERE tbi_usr_id = $usr");
    $sql2 = "INSERT INTO temp_buyrequirement_image (tbi_usr_id, tbi_image) VALUES ($usr, '$newFileName')";
    
    if (mysqli_query($con, $sql2)) {
        echo "1|Image uploaded successfully";
    } else {
        echo "0|Database error: " . mysqli_error($con);
    }
} else {
    echo "0|Failed to move uploaded file";
}
?>