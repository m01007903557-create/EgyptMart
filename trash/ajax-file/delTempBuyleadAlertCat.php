<?php
ob_start();
session_start();
include "../common.php";

$id=$_POST['id'];
$sql="delete from temp_buylead_alert_cat where tbac_usr_id='".$_SESSION['uid_indm']."' and tbac_pc_id='".$id."'";
mysqli_query($con, $sql);
?>