<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update services set ser_status = '".$stat."' where ser_id = '".$id."'");
?>