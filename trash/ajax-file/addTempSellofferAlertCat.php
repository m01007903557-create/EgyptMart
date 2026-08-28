<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="insert into temp_selloffer_alert_cat
	set
		tsac_usr_id='".$_SESSION['uid_indm']."',
		tsac_pc_id='".$id."',
		tsac_updated_date=now()";
		
mysqli_query($con, $sql);
?>