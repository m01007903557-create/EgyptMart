<?php
ob_start();
session_start();
include "../common.php";

$pd_id=$_GET['id'];

$sqlImg="select * from products where pd_id='".$pd_id."'";
$resImg=mysqli_query($con, $sqlImg);
$rowImg=mysqli_fetch_object($resImg);
	$oneimg=explode(',',$rowImg->pd_image);
$pathB="upload/myproduct/".$oneimg[0];

echo $pathB;
?>