<?php
include "common.php";
$sqlk="select * from products where pd_id='".$_GET['id']."'";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
$path="upload/productdoc/".$rowk->pd_pdf_attach;
unlink($path);
mysqli_query($con, "update products set pd_pdf_attach='' where pd_id='".$_GET['id']."'");
?>