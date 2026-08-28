<?php
ob_start();
include "common.php";

$cn_id=$_POST['loc_id'];

if(isset($_POST['loc_id']))
{
	setcookie("loc_id",$cn_id, time()+3600); 
}
else
{
	setcookie("loc_id",$cn_id, time()-60); 
}

$sql="select * from country where cn_id='".$cn_id."'";
$res=mysql_query($sql);
if(mysql_num_rows($res))
{
	$row=mysql_fetch_object($res);
	echo $row->cn_flag;
}
else
{
	echo 0;
}
?>