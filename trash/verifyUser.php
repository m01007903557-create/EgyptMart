<?php 
include 'common.php';

$token = substr($_GET['token'], 4);
if (!empty($token)) {
	$sql = "SELECT * FROM `user` WHERE md5(usr_id) = '".$token."'";
	$qry = mysqli_query($con, $sql) or die(mysql_error());
	$arr = mysqli_fetch_assoc($qry);
	if($arr['usr_emailVerify']==0){
		$_SESSION['uid_indm']=$arr['usr_id'];
		setcookie('cook_usr_id', $arr['usr_id'], time() + (86400 * 300), "/");
		setcookie("productids", '', time()-1000);
		setcookie("productids", '', time()-1000, '/');
	}
}
mysqli_query($con, "update user set usr_emailVerify = '1' where md5(usr_id) = '".$token."'");
header("location:my-dashboard.php?verifySucces=1");
exit();
?>