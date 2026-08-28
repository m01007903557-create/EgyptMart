<?php
include "common.php";

$sqlk="select * from temp_products_image where tmpimg_id='".$_GET['imid']."' ";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
$path="upload/myproduct/".$rowk->tmpimg_image;
unlink($path);
mysqli_query($con, "delete from temp_products_image where tmpimg_id='".$_GET['imid']."'");
?>