<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update header_link set hl_status = '".$stat."' where hl_id = '".$id."'");
?>