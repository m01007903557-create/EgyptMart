<?php
ob_start();
session_start();
include "../common.php";

$br_id=$_GET['id'];

$sqlImg="select * from buy_requirement where br_id='".$br_id."'";
$resImg=mysqli_query($con, $sqlImg);
$rowImg=mysqli_fetch_object($resImg);
				
$pathB="upload/buy_requirement/".$rowImg->br_pic;

echo $pathB;
?>