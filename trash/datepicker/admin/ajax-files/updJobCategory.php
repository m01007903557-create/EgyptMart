<?php
include "../common.php";

$jc_id=$_GET['jc'];
$jc_name=$_GET['jc_name'];

$sql="update job_category
	set
		jc_name='".$jc_name."'
	where
		jc_id='".$jc_id."'";
mysqli_query($con, $sql);

$sql_ret="select * from job_category where jc_id='".$jc_id."'";
$res_ret=mysqli_query($con, $sql_ret);
$row_ret=mysqli_fetch_object($res_ret);
echo $row_ret->jc_name;
?>