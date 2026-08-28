<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update header_slider set hs_status = '".$stat."' where hs_id = '".$id."'");
?>