<?php
ob_start();
include "common.php";

$cn_id = isset($_POST['loc_id']) ? (int)$_POST['loc_id'] : 0;

if (isset($_POST['loc_id'])) {
    setcookie("loc_id", $cn_id, time() + 3600, "/");
} else {
    setcookie("loc_id", $cn_id, time() - 60, "/");
}

// استعلام آمن باستخدام mysqli
$sql = "SELECT cn_flag FROM country WHERE cn_id = ? LIMIT 1";
$stmt = mysqli_prepare($con, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $cn_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_object($result);
        echo $row->cn_flag;
    } else {
        echo 0;
    }
    mysqli_stmt_close($stmt);
} else {
    echo 0;
}
?>