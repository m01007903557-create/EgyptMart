<?php
ob_start();
session_start();
include "common.php";

//$id=$_POST['id'];
$sql="select * from favourites_table where user_id='".$_SESSION['uid_indm']."' AND pro_id='".$_POST['pro_id']."'";
$res=mysqli_query($con, $sql) or die(mysqli_error());
if(mysqli_num_rows($res)==0){
	$sql_1="insert  into favourites_table SET 
												  user_id=".$_SESSION['uid_indm'].",
												  pro_id=".$_POST['pro_id'].",
												  created_datetime=now()";
	mysqli_query($con,$sql_1);
	echo true;
}
?>