<?php
include "../common.php";

$la_id=$_POST['la_id'];
$status=$_POST['status'];

$sql="update leave_assign
	set
		la_status='".$status."'
	where
		la_id='".$la_id."'";
mysqli_query($con, $sql);
			 
?>