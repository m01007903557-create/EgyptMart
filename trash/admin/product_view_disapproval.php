<?php 
include "../../common.php";
global $con;
$pid = $_GET['id'];
mysqli_query($con, "update products set pd_status='2' where pd_id='".$pid."'");
echo true;
?>