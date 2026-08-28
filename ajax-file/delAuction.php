<?php
include "../common.php";

$auc_id=$_POST['id'];

$sql="update auction
	set
		auc_status='0'
	where
		auc_id='".$auc_id."'";
		
mysqli_query($con, $sql);
?>