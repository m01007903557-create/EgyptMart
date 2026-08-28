<?php
include "../../common.php";

$cn_id=$_POST['id'];

$sql_cn="select * from country where cn_status='1' and cn_id='".$cn_id."'";
$res_cn=mysqli_query($con, $sql_cn);
$row_cn=mysqli_fetch_object($res_cn);

echo $row_cn->cn_ph;
?>