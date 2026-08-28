<?php
include "../common.php";

$curr_pass=trim($_POST['curr_pass']);
$new_pass=trim($_POST['new_pass']);
$conf_pass=trim($_POST['conf_pass']);
$uid=trim($_POST['usid']);
$msg="";
function validPass($curr_pass,$uid)
{
	$sqlchk="select * from user where pass = '".md5($curr_pass)."' and usr_id='".$uid."'";
	$reschk =mysql_query($sqlchk);
	if(mysql_num_rows($reschk)>0)
	{
		return 1;	
	}
	else
	{
		return 0;	
	}
		
}
$valid=1;
if($curr_pass== "")
{
	$msg= 'Please enter your Current password.';
	$valid=0;
}
else if(validPass($curr_pass,$uid)==0)
{
	$msg= 'Please enter your correct password.';
	$valid=0;
}
else if($new_pass== "")
{
	$msg= 'Please enter your New password.';
	$valid=0;
}
else if($conf_pass=="")
{
	$msg= 'Please enter your Confirm password.';
	$valid=0;
}
else
{
	$sql="update user set pass='".md5($new_pass)."' where usr_id='".$uid."' ";
	mysql_query($sql) or die(mysql_error());
	
	$valid=1;
		
	$to = user_info($uid,'email');  /*Put Your Email Adress Here*/
	$subject = "Password change Alert on ".getWebSiteName();
	$from_email = get_adminemail();
	$message = "Dear"."&nbsp;".user_info($uid,'name_prefix')." ".user_info($uid,'fname')." ".user_info($uid,'lname')."<br><br>";
	$message .= "Your new password has been updated successfully. Kindly use your new password for signing in now onwards.<br>";
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: $from_name < ".$from_email." >";
	mail($to, $subject, $message, $headers);
			
	$msg= 'Your new password has been updated successfully.';
}
echo $msg."|".$valid;
?>