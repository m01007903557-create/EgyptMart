<?php 
//ob_start();
//session_start(); 
include "../common.php";
//check_user_login();
$hid=$_GET['hid'];
$country_inp=$_GET['country_inp'];
$currency_inp=$_GET['currency_inp'];
$recObj_sql="update country set cn_name='".$country_inp."', cn_currency ='".$currency_inp."' where cn_id=".$hid;
$recObj=mysqli_query($con, $recObj_sql);
echo "$country_inp - $currency_inp";
?>

