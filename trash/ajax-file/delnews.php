<?php
include "../common.php";

$sqlchk=mysqli_query($con, "select * from news where nws_id='".$_GET['id']."'");
$rowchk=mysqli_fetch_object($sqlchk);
$path1="../upload/mynews/small/".$rowchk->nws_smallimg;
$path2="../upload/mynews/large/".$rowchk->nws_largeimg;
unlink($path1);
unlink($path2);
mysqli_query($con, "delete from news where nws_id='".$_GET['id']."'");
?>