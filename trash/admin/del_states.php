<?php 
//ob_start();
//session_start(); 
include "../common.php";
//check_user_login();
$hid=$_GET['hid'];

$recObj_sql="select state_cn_id from states where state_id=".$hid;
$recObj=mysqli_query($con, $recObj_sql);
$row=mysqli_fetch_array( $recObj);
$state_cn_id=$row[state_cn_id];
$delObj_sql="delete from states where state_id=".$hid;
$delObj=mysqli_query($con, $delObj_sql);
echo "$state_cn_id";
?>