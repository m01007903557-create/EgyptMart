<?php
include "../common.php";

$tac_id=$_POST['id'];

$sql="delete from tender_alert_category where tac_id='".$tac_id."'";
mysqli_query($con, $sql);
?>