<?php
include "../common.php";

$re_name=$_GET['re_name'];
echo $re_name;
$chk_sql = "select * from report_employee where re_name='".$re_name."'";
$chk_res =  mysqli_query($con, $chk_sql);
$chk_num = mysqli_num_rows($chk_res);
echo $chk_num;

?>