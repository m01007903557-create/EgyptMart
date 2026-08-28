<?php
include "../common.php";

$dept_name=$_POST['dept_name'];
$dept_under=$_POST['dept_under'];

$sql="insert into department
	set
		dept_under='".$dept_under."',
		dept_name='".$dept_name."',
		dept_updated_date=now()";
	
mysqli_query($con, $sql);

?>