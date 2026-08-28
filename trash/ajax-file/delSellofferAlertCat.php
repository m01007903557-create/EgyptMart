<?php
include "../common.php";

$sac_id=$_POST['id'];

$sql="delete from selloffer_alert_category where sac_id='".$sac_id."'";
mysqli_query($con, $sql);
?>