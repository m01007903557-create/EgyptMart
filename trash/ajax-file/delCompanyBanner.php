<?php
ob_start();
session_start();
include "../common.php";

$cb_id=trim($_POST['cb_id']);

$sql="select * from company_banner where cb_id='".$cb_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$path="../upload/company_banner/".$row->cb_image;	//old path
if(is_file($path))
{
	unlink($path);
}

$sql_del="delete from company_banner
	where
		cb_id='".$cb_id."'";
mysqli_query($con, $sql_del);
?>