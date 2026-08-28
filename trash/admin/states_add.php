<?php 
//ob_start();
//session_start(); 
include "../common.php";
//check_user_login();
$states_add=$_GET['states_add'];
$cun=$_GET['cun'];
$sql="insert into states set state_name ='".$states_add."', state_cn_id='".$cun."'";							
mysqli_query($con, $sql) or die(mysql_error());
?>

