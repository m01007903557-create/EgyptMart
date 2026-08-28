<?php
include "../../common.php";
$g_id=$_POST['id'];
$status=$_POST['stat'];

$sql="update gig set g_status='".$status."' where g_id='".$g_id."'";
mysqli_query($con, $sql);

?>