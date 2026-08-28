<?php
include "../common.php";

$aac_id=$_POST['id'];

$sql="delete from auction_alert_category where aac_id='".$aac_id."'";
mysqli_query($con, $sql);
?>