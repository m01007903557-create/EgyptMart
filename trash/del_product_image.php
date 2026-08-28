<?php
include "common.php";
$sqlk="select * from products where pd_id='".$_GET['imid']."'";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
$path="upload/myproduct/".$rowk->pd_image;
unlink($path);
mysqli_query($con, "update products set pd_image='' where pd_id='".$_GET['imid']."'");
?>