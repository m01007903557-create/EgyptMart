<?php 
include '../../common.php';

$stat = $_POST['stat'];
$id = $_POST['id'];

mysqli_query($con, "update servicepage_content set spc_status = '".$stat."' where spc_id = '".$id."'");
?>