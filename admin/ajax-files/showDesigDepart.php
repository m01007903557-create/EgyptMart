<?php
include "../common.php";

$emp_id=$_POST['eid'];

$sql="select * from employee_job,department,job_title where ej_dept_id=dept_id and ej_jt_id=jt_id and ej_emp_id='".$emp_id."'";

$res=mysqli_query($con, $sql);

$row=mysqli_fetch_object($res);

echo $row->jt_title." (".$row->dept_name.")&nbsp;&nbsp;";

?>