<?php
include "../common.php";
$uid=$_SESSION['uid_indm'];
$pid=$_GET['id'];
$pdsql=mysqli_query($con, "select * from products where pd_id='".$pid."' and pd_hot='0' and pd_status ='1' ");
$pdrow=mysqli_fetch_object($pdsql);
if(mysqli_num_rows($pdsql)>0)
{
	mysqli_query($con, "update products set pd_hot='1' where pd_id='".$pid."'");
}
else
{
	mysqli_query($con, "update products set pd_hot='0' where pd_id='".$pid."'");
}
?>

