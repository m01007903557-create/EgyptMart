<?php
include "../../common.php";

$pc_id=trim($_POST['id']);
$pc_order=trim($_POST['pc_order']);

	$sql="update product_category
	set
		pc_order='".$pc_order."'
	where
		pc_id='".$pc_id."'";
	mysqli_query($con, $sql);
?>