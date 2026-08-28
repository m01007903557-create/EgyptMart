<?php
include "../common.php";

$emp_id=$_POST['emp_id'];

$sql="select * from employee_salary where es_emp_id='".$emp_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
echo $row->es_payFrequency;

?>