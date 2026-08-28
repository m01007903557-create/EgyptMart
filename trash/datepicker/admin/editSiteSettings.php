<?php
include "../common.php";

$st_id=trim($_POST['id']);
$val=trim($_POST['val']);
$st_value=1;
if($val==1)
{
	$st_value=0;
}

	$sql="update site_settings_arabyos
		set
			st_value ='".$st_value."',
			st_updated_date=now()
		where
			st_id='".$st_id."'";
	mysqli_query($con, $sql);
?>