<?php
include "../common.php";

$sqlchk=mysqli_query($con, "select * from about_us where abtus_id='".$_GET['id']."'");
$rowchk=mysqli_fetch_object($sqlchk);
$path="../upload/myprofile/".$rowchk->abtus_image;
unlink($path);
mysqli_query($con, "delete from about_us where abtus_id='".$_GET['id']."'");
?>