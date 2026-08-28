<?php
require_once __DIR__ . '/../common.php';

$usr = isset($_GET['usr']) ? (int)$_GET['usr'] : 0;

if ($usr <= 0) {
    echo '/upload/buy_requirement/no-image.png';
    exit;
}

$sql = "SELECT tbi_image FROM temp_buyrequirement_image WHERE tbi_usr_id = $usr ORDER BY tbi_id DESC LIMIT 1";
$result = mysqli_query($con, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $image = trim($row['tbi_image']);
    
    if (!empty($image) && file_exists(__DIR__ . '/../upload/buy_requirement/' . $image)) {
        echo '/upload/buy_requirement/' . $image;
        exit;
    }
}

echo '/upload/buy_requirement/no-image.png';
?>