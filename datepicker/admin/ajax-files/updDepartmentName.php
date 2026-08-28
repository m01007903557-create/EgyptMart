<?php
include "../common.php";

$dept_id=$_GET['dept'];
$dept_name=$_GET['dept_name'];

$upd_sql="update department set dept_name='".$dept_name."' where dept_id='".$dept_id."'";
mysqli_query($con, $upd_sql);
$sql="select * from department where dept_id ='".$dept_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
echo $row->dept_name;
?>