<?php
ob_start();
session_start();
include "../common.php";

//$id=$_POST['id'];
$sql="select * from temp_buylead_alert_cat where tbac_usr_id='".$_SESSION['uid_indm']."'";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_ins="insert into buylead_alert_category
		set
			bac_usr_id='".$_SESSION['uid_indm']."',
			bac_pc_id='".$row->tbac_pc_id."',
			bac_updated_date=now()";

	mysqli_query($con, $sql_ins);
}
mysqli_query($con, "delete from temp_buylead_alert_cat where tbac_usr_id='".$_SESSION['uid_indm']."'");
?>