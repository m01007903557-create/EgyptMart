<?php
include "common.php";

$sqlk="select * from about_us where abtus_id='".$_GET['imid']."' ";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
$path="upload/myprofile/".$rowk->abtus_image;
unlink($path);
mysqli_query($con, "update about_us set abtus_image='' where abtus_id='".$_GET['imid']."'");
?>