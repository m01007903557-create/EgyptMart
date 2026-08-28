<?php
include "../common.php";

$sqlchk=mysqli_query($con, "select * from products where pd_id='".$_GET['id']."'");
$rowchk=mysqli_fetch_object($sqlchk);
$path="../upload/myproduct/".$rowchk->pd_image;
unlink($path);
mysqli_query($con, "delete from products where pd_id='".$_GET['id']."'");
?>