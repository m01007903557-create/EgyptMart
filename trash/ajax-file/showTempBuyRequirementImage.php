<?php
ob_start();
session_start();
include "../common.php";

$usr=$_GET['usr'];

$sqlImg="select * from temp_buyrequirement_image where tbi_usr_id='".$usr."'";
$resImg=mysqli_query($con, $sqlImg);
if(mysqli_num_rows($resImg)>0)
{
	$rowImg=mysqli_fetch_object($resImg);
	$pathB="upload/buy_requirement/".$rowImg->tbi_image;
}
else
{
	$pathB="upload/buy_requirement/no-image.png";
	
}
echo $pathB;
?>