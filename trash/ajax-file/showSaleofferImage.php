<?php
ob_start();
session_start();
include "../common.php";

$so_id=$_GET['id'];

$sqlImg="select * from sale_offer where so_id='".$so_id."'";
$resImg=mysqli_query($con, $sqlImg);
$rowImg=mysqli_fetch_object($resImg);
				
$pathB="upload/sale_offer/".$rowImg->so_pic;

echo $pathB;
?>