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
	//$pathB="upload/myproduct/".$rowImg->tpi_image;
	$oneimg = explode(',', $rowImg->tpi_image);
    //print_r($oneimg[0]);
	
	$pathB="upload/myproduct/".$oneimg[0];
}
else
{
	$pathB="upload/myproduct/add-image.gif";
	
}
echo $pathB;
?>