<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update yahoo_slider_arabyos set adv_status = '".$stat."' where adv_id = '".$id."'");
?>