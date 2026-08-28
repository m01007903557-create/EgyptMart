<?php
include "../common.php";

$st_id=trim($_POST['id']);
$val=trim($_POST['val']);

	$sql="update site_settings
		set
			st_value ='".$val."',
			st_updated_date=now()
		where
			st_id='".$st_id."'";
			
	mysqli_query($con, $sql);
?>