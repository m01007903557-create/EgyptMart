<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update features set f_status = '".$stat."' where f_id = '".$id."'");
?>