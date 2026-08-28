<?php
include "../common.php";
	$q=$_GET['q'];
	$my_data=mysql_real_escape_string($q);
	$sql="SELECT * FROM employee WHERE emp_status='1' and emp_firstName LIKE '$my_data%' and emp_firstName <> 'admin' ORDER BY emp_firstName";
	$result = mysqli_query($con, $sql);
	
	if($result)
	{
		while($row=mysqli_fetch_object($result))
		{
			echo ucfirst($row->emp_firstName)." ".ucfirst($row->emp_lastName)."|".$row->emp_id."\n";
		}
	}
?>