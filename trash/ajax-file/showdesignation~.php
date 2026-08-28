<?php
include "../common.php";
$q=$_GET['q'];
$my_data=mysql_real_escape_string($q);
$sql="SELECT * FROM designation where desig_title LIKE '$my_data%' and desig_status='1' order by desig_id";
	$result = mysqli_query($con, $sql);
	if($result)
	{
		while($row=mysqli_fetch_object($result))
		{
			echo ucfirst($row->desig_title)."|".$row->desig_id."\n";
		}
	}
?>

