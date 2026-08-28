<?php
ob_start();
include "common.php";

$cn_id = isset($_POST['loc_id']) ? (int)$_POST['loc_id'] : 0;

if($cn_id > 0) {
    // تعيين البلد المختار
    setcookie("loc_id", $cn_id, time() + (86400 * 30), "/");
    // إزالة GLOBAL
    setcookie("is_global", '', time() - 3600, "/");
    setcookie("geo_redirect_attempted", '', time() - 3600, "/");
    
    // إرجاع علم البلد
    $sql = "SELECT cn_flag FROM country WHERE cn_id = $cn_id LIMIT 1";
    $res = mysqli_query($con, $sql);
    if(mysqli_num_rows($res) > 0) {
        $row = mysqli_fetch_object($res);
        echo $row->cn_flag;
    } else {
        echo 0;
    }
} else {
    echo 0;
}
?>
