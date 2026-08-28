<?php
include "../common.php";

$dept_id=$_POST['dept_id'];

$sql="select * from employee_job where ej_dept_id='".$dept_id."'";
$res=mysqli_query($con, $sql);
$num=mysqli_num_rows($res);

$sql2="select * from department where dept_under='".$dept_id."'";
$res2=mysqli_query($con, $sql2);
$num2=mysqli_num_rows($res2);

if($num>=1 || $num2>=1)
{
	echo "1";	
}
else
{
	echo "0";	
}

?>