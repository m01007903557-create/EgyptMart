<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update advertisementhome set adv_status = '".$stat."' where adv_id = '".$id."'");
?>