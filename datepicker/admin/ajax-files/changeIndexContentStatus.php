<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update index_content set ic_status = '".$stat."' where ic_id = '".$id."'");
?>