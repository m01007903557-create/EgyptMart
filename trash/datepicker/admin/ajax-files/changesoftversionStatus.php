<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update index_software_version set isv_status = '".$stat."' where isv_id = '".$id."'");
?>