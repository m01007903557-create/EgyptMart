<?php
include "../common.php";

$email=trim($_POST['eml']);
$sql_chk="select * from user where email='".$email."' and status='1'";
//echo $sql_chk;
$res_chk=mysqli_query($con, $sql_chk);
if(mysqli_num_rows($res_chk)>0)
{	
	echo 1;
}
else
{
	echo 0;
}
?>