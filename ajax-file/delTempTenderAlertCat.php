<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="delete from temp_tender_alert_cat where ttac_usr_id='".$_SESSION['uid_indm']."' and ttac_pc_id='".$id."'";
mysqli_query($con, $sql);
?>