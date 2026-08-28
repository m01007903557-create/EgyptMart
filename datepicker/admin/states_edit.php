<?php 
//ob_start();
//session_start(); 
include "../common.php";
//check_user_login();
$hid=$_GET['hid'];
$states_inp=$_GET['states_inp'];
$recObj_sql="update states set state_name='".$states_inp."' where state_id = '".$hid."'";
$recObj=mysqli_query($con, $recObj_sql);
echo "$states_inp";
?>

