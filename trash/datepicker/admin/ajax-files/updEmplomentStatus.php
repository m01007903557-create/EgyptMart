<?php
include "../common.php";

$es_id=$_GET['es'];
$es_name=$_GET['es_name'];

$sql="update employment_status
	set
		es_name='".$es_name."'
	where
		es_id='".$es_id."'";
mysqli_query($con, $sql);

$sql_ret="select * from employment_status where es_id='".$es_id."'";
$res_ret=mysqli_query($con, $sql_ret);
$row_ret=mysqli_fetch_object($res_ret);
echo $row_ret->es_name;
?>