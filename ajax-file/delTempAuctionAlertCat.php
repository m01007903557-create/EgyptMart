<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="delete from temp_auction_alert_cat where taac_usr_id='".$_SESSION['uid_indm']."' and taac_pc_id='".$id."'";
mysqli_query($con, $sql);
?>