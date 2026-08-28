<?php
ob_start();
session_start();
include "../common.php";


$cv_id=trim($_POST['cv_id']);

$sql="delete from company_video
	where
		cv_id='".$cv_id."'";
mysqli_query($con, $sql);
?>