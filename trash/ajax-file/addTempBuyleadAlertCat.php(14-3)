<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="insert into temp_buylead_alert_cat
	set
		tbac_usr_id='".$_SESSION['uid_indm']."',
		tbac_pc_id='".$id."',
		tbac_updated_date=now()";
		
mysqli_query($con, $sql);
?>