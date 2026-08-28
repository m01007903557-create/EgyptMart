<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="insert into temp_auction_alert_cat
	set
		taac_usr_id='".$_SESSION['uid_indm']."',
		taac_pc_id='".$id."',
		taac_updated_date=now()";
		
mysqli_query($con, $sql);
?>