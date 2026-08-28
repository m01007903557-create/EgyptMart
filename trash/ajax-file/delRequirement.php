<?php
include "../common.php";

$br_id=$_POST['id'];

$sql="update buy_requirement
	set
		br_status='0'
	where
		br_id='".$br_id."'";
		
mysqli_query($con, $sql);
?>