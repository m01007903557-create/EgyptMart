<?php 
include "../common.php";
$hid=$_GET['hid'];
$city_inp=$_GET['city_inp'];
$state_inp=$_GET['state_inp'];
$metro_inp=$_GET['metro_inp'];

$recObj_sql="update city set ct_name='".$city_inp."', ct_metro = '".$metro_inp."', ct_state = '".$state_inp."' where ct_id= '".$hid."'";
$recObj=mysqli_query($con, $recObj_sql);
echo $city_inp;
?>

