<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="insert into temp_tender_alert_cat
	set
		ttac_usr_id='".$_SESSION['uid_indm']."',
		ttac_pc_id='".$id."',
		ttac_updated_date=now()";
		
mysqli_query($con, $sql);
?>