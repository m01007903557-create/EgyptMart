<?php 
//ob_start();
//session_start(); 
include "../common.php";
//check_user_login();
$country_add=$_GET['country_add'];
$currency_add=$_GET['currency_add'];
$sql="insert into country set cn_name ='".$country_add."', cn_currency ='".$currency_add."'";							
mysqli_query($con, $sql) or die(mysql_error());
?>

