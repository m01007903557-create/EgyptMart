<?php
//error_reporting(E_ERROR | E_WARNING | E_PARSE);
include "../common.php";
$uid=$_SESSION['uid_indm'];
$pid=$_GET['id'];
$sqlk="select * from products where pd_id='".$pid."' and pd_pushed_top='0' and pd_status ='1' ";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);
if(mysqli_num_rows($resk)>0)
{
	mysqli_query($con, "update products set pd_pushed_top='1' where pd_id='".$pid."'");
}
?>

