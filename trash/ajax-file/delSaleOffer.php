<?php
include "../common.php";

$so_id=$_POST['id'];

$sql="update sale_offer
	set
		so_status='0'
	where
		so_id='".$so_id."'";
		
mysqli_query($con, $sql);
?>