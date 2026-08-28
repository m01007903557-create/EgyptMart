<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update supplier_logo set adv_status = '".$stat."' where adv_id = '".$id."'");
?>