<?php
ob_start();
session_start();
include "common.php";

$sqlk="select * from temp_about_us where tmabs_id='".$_GET['imid']."' ";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
$path="upload/myprofile/".$rowk->tmabs_images;
unlink($path);
mysqli_query($con, "delete from temp_about_us where tmabs_id='".$_GET['imid']."'");
?>