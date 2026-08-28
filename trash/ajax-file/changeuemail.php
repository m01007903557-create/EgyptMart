<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "../common.php";

	$emailad=addslashes(trim($_POST['emailad']));
	$em=1;

	$sql_chk="select * from user where email='".$emailad."' and usr_id!='".$uid."'";
	$res_chk=mysql_query($sql_chk);
	if(mysql_num_rows($res_chk)>0)
	{	
		$em=0;		
	}

	if($emailad=='' || $emailad==null)
	{
		$msg="Please enter email";	
	}
	else if(!validate::is_email($emailad))
	{
		$msg="Please enter valid email";		
	}
	else if($em==0)
	{
		$msg="Please enter anothe email.this is already exist";		
	}
	else
	{
			$sql="update user set email='".$emailad."'
				  where usr_id='".$uid."'";
			mysql_query($sql) or die(mysql_error());
			$_SESSION['eml_indm']=$emailad;
			$msg="Email changed successfully.";
	}
	echo $msg;

?>