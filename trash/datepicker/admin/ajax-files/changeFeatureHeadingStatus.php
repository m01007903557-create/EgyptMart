<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];
mysqli_query($con, "update featurepage_content set fpc_status = '".$stat."' where fpc_id = '".$id."'");
?>