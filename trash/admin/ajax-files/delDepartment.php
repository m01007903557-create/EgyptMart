<?php
include "../common.php";

$dept_id=$_POST['dept_id'];

$sql="delete from department where dept_id='".$dept_id."'";
$res=mysqli_query($con, $sql);

?>