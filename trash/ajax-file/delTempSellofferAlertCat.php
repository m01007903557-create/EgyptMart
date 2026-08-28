<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="delete from temp_selloffer_alert_cat where tsac_usr_id='".$_SESSION['uid_indm']."' and tsac_pc_id='".$id."'";
mysqli_query($con, $sql);
?>