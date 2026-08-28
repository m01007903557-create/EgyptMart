<?php 
session_start();
include 'common.php';
if(isset($_SESSION['uid_indm'])){
	if(isset($_POST['cat_id'])){
		$key_cat_id = $_POST['cat_id'];
		$uid = $_SESSION['uid_indm'];

		$query = "SELECT * FROM buylead_alert_category WHERE bac_pc_id='$key_cat_id' AND bac_usr_id='$uid'";	
		$r=mysql_query($query);	
		if(mysql_num_rows($r) == 0){		
			$SQL_BUY_ALERT="INSERT  INTO buylead_alert_category SET 
											  bac_usr_id=".$uid.",
											  bac_pc_id=".$key_cat_id.",
											  bac_updated_date=now()";
			$r=mysql_query($SQL_BUY_ALERT) or die('Error in query while saving');
		}
	}else{
		echo "Invalid Request";
	}
}else{
	echo "You are not authorized to do this";
}
