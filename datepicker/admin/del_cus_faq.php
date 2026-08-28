<?php 
include "../common.php";
$hid=$_GET['hid'];
$recObj_sql="select * from custom_faq where cf_id=".$hid;
$recObj=mysqli_query($con, $recObj_sql);
$row=mysqli_fetch_array( $recObj);
$caid=$row['cf_fc_id'];

$delObj_sql="delete from custom_faq where cf_id=".$hid;
$delObj=mysqli_query($con, $delObj_sql);
echo "$caid";
?>