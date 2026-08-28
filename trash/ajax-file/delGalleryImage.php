<?php
include "../common.php";

$ph_id=$_POST['id'];

$sql="select * from photo where ph_id='".$ph_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$pathImg="../upload/image_gallery/".$row->ph_fileName;
if(is_file($pathImg))
{
	unlink($pathImg);
}

mysqli_query($con, "delete from photo where ph_id='".$row->ph_id."'");


?>