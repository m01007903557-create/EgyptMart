<?php
ob_start();
session_start();
include "../common.php";

$cv_bnsprof_id=trim($_POST['cv_bnsprof_id']);
$vlink=trim($_POST['vlink']);

$sql="insert into company_video
	set
		cv_bnsprof_id='".$cv_bnsprof_id."',
		cv_video_link='".$vlink."',
		cv_updated_date=now()";
mysqli_query($con, $sql);
?>