<?php
include "../common.php";
	$q=$_GET['q'];
$country=$_GET['country'];
	$my_data=mysql_real_escape_string($q);
	$sql="select * from states where state_status = '1' and state_name LIKE '$my_data%' and state_cn_id=".$country." order by state_id asc";
	$result = mysqli_query($con, $sql);
	if($result)
	{
		while($row=mysqli_fetch_object($result))
		{
			echo ucfirst($row->state_name)."|".$row->state_id."\n";
		}
	}
?>