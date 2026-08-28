<?php
session_start();
require_once "../common.php";

header('Content-Type: text/plain; charset=utf-8');

$uid = isset($_POST['usr']) ? (int)$_POST['usr'] : (int)($_SESSION['uid_indm'] ?? 0);
if ($uid <= 0) {
    http_response_code(400);
    echo "User ID is required";
    exit;
}

$fileKey = null;
foreach (array('Filedata', 'file_upload', 'so_pic') as $key) {
    if (isset($_FILES[$key]) && is_uploaded_file($_FILES[$key]['tmp_name'])) {
        $fileKey = $key;
        break;
    }
}

if ($fileKey === null) {
    http_response_code(400);
    echo "No image uploaded";
    exit;
}

$originalName = basename((string)$_FILES[$fileKey]['name']);
$originalName = preg_replace('/[^a-zA-Z0-9\-_.]/', '', $originalName);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
$allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

if (!in_array($extension, $allowed, true)) {
    http_response_code(400);
    echo "Unsupported image type";
    exit;
}

$targetDir = __DIR__ . "/../upload/sale_offer";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

$imageName = "so-" . $uid . "-" . time() . "-" . mt_rand(1000, 9999) . "." . $extension;
$targetPath = $targetDir . "/" . $imageName;

if (!move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo "Upload failed";
    exit;
}

$checkSql = "SELECT tsi_id FROM temp_selloffer_image WHERE tsi_usr_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $checkSql);
mysqli_stmt_bind_param($stmt, 'i', $uid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $sql = "UPDATE temp_selloffer_image SET tsi_image = ? WHERE tsi_usr_id = ?";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $imageName, $uid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
} else {
    $sql = "INSERT INTO temp_selloffer_image (tsi_usr_id, tsi_image) VALUES (?, ?)";
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'is', $uid, $imageName);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

echo $imageName;
?>
