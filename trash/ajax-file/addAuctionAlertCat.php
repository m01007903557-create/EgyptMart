<?php
ob_start();
session_start();
include "../common.php";

//$id=$_POST['id'];
$sql="select * from temp_auction_alert_cat where taac_usr_id='".$_SESSION['uid_indm']."'";

$res=mysqli_query($con, $sql);
while($row=mysqli_fetch_object($res)){
	$sql_exist="select * from auction_alert_category where aac_usr_id='".$_SESSION['uid_indm']."' AND aac_pc_id='".$row->taac_pc_id."'";
		$res12=mysqli_query($con, $sql_exist);
		if($res12->num_rows==0){
			$sql_ins="insert into auction_alert_category
				set
					aac_usr_id='".$_SESSION['uid_indm']."',
					aac_pc_id='".$row->taac_pc_id."',
					aac_updated_date=now()";

			mysqli_query($con, $sql_ins);
		}
}
mysqli_query($con, "delete from temp_auction_alert_cat where taac_usr_id='".$_SESSION['uid_indm']."'");
?>