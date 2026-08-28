<?php
ob_start();
session_start();
include "../common.php";

$usr=$_GET['usr'];

$sqlImg="select * from temp_product_image where tpi_usr_id='".$usr."'";
$resImg=mysqli_query($con, $sqlImg);
if(mysqli_num_rows($resImg)>0)
{       
         
	$rowImg=mysqli_fetch_object($resImg);
	$imgone=explode(',',$rowImg->tpi_logo);
	//$oneimg = explode(',', $rowImg->tpi_image);
	$pathB="upload/myproduct/".$imgone[0];
}
else
{
	$pathB="upload/myproduct/add-image.gif";
	
}
echo $pathB;
?>