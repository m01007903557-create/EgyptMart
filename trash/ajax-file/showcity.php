<?php
include "../common.php";

$uid=$_SESSION['uid_indm'];
$q=$_GET['q'];
$my_data=mysql_real_escape_string($q);

$sqlcn="select * from user where usr_id  = '".$uid."' and status = '1'";
$rescn=mysqli_query($con, $sqlcn);
$rowcn=mysqli_fetch_array( $rescn);

	$sql="SELECT * FROM city where ct_cn_id='".$rowcn['country']."' and ct_name LIKE '$my_data%' and ct_status='1'";
	$result = mysqli_query($con, $sql);
	if($result)
	{
		while($row=mysqli_fetch_object($result))
		{
			$sqlstate="select * from states where state_id = '".$row->ct_state."' and state_status = '1'";
			$restate=mysqli_query($con, $sqlstate);
			$rowstate=mysqli_fetch_object($restate);
			echo ucfirst($row->ct_name).">>".ucfirst($rowstate->state_name)."|".$row->ct_id."|".$rowstate->state_id."\n";
		}
	}
?>

