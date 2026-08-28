<?php
ob_start();
session_start();
include "../common.php";

//$id=$_POST['id'];
$sql="select * from temp_tender_alert_cat where ttac_usr_id='".$_SESSION['uid_indm']."'";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_exist="select * from tender_alert_category where tac_usr_id='".$_SESSION['uid_indm']."' AND tac_pc_id='".$row->ttac_pc_id."'";
		$res12=mysqli_query($con, $sql_exist);
		if($res12->num_rows==0){
			$sql_ins="insert into tender_alert_category
				set
					tac_usr_id='".$_SESSION['uid_indm']."',
					tac_pc_id='".$row->ttac_pc_id."',
					tac_updated_date=now()";

			mysqli_query($con, $sql_ins);
		}
}
mysqli_query($con, "delete from temp_tender_alert_cat where ttac_usr_id='".$_SESSION['uid_indm']."'");
?>