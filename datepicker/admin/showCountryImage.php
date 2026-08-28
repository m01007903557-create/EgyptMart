<?php
//ob_start();
//session_start();
include "../common.php";

$cn_id=$_GET['id'];

$sqlImg="select * from country where cn_id='".$cn_id."'";
$resImg=mysqli_query($con, $sqlImg);
$rowImg=mysqli_fetch_object($resImg);
				
$path="../images/country_flag/".$rowImg->cn_flag;

echo $path;
?>