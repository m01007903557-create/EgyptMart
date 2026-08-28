<?php
ob_start();
session_start();
include "common.php";

//$id=$_POST['id'];
$sql="delete from favourites_table where user_id='".$_SESSION['uid_indm']."' AND pro_id='".$_POST['pro_id']."'";
$res=mysqli_query($con, $sql) or die(mysqli_error());
echo true;
?>