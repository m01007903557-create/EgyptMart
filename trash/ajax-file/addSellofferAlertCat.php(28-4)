<?php
ob_start();
session_start();
include "../common.php";

//$id=$_POST['id'];
$sql="select * from temp_selloffer_alert_cat where tsac_usr_id='".$_SESSION['uid_indm']."'";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res))
{
	$sql_ins="insert into selloffer_alert_category
		set
			sac_usr_id='".$_SESSION['uid_indm']."',
			sac_pc_id='".$row->tsac_pc_id."',
			sac_updated_date=now()";
echo $sql_ins;
	mysqli_query($con, $sql_ins);
}
mysqli_query($con, "delete from temp_selloffer_alert_cat where tsac_usr_id='".$_SESSION['uid_indm']."'");
?>