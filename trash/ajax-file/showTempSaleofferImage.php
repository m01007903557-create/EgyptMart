<?php
ob_start();
session_start();
include "../common.php";

$usr=$_GET['usr'];

$sqlImg="select * from temp_selloffer_image where tsi_usr_id='".$usr."'";
$resImg=mysqli_query($con, $sqlImg);
if(mysqli_num_rows($resImg)>0)
{
	$rowImg=mysqli_fetch_object($resImg);
	$pathB="upload/sale_offer/".$rowImg->tsi_image;
}
else
{
	$pathB="upload/sale_offer/no-image.png";
	
}
echo $pathB;
?>