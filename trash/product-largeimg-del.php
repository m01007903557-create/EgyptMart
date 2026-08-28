<?php
include "common.php";
$sqlk="select * from products where pd_id='".$_GET['pid']."'";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
$path="upload/myproduct_large/".$rowk->pd_large_image;
unlink($path);
$sql="update products set pd_large_image='' where pd_id ='".$_GET['pid']."'";
mysqli_query($con, $sql);
?>