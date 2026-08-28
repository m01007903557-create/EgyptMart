<?php 
include "../common.php";
$city_add = $_GET['city_add'];
$state_inp = $_GET['state_inp'];

$cun=$_GET['cun'];
$sql="insert into city set ct_cn_id='".$cun."', ct_name = '".$city_add."', ct_state = '".$state_inp."'";							
mysqli_query($con, $sql) or die(mysql_error());
?>

