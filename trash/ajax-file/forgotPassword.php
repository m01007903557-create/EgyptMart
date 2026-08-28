<?php
include "../common.php";

$email=trim($_POST['email']);
$msg="";
$valid=1;
	
if($email=='')
{
		$msg="Please enter Email address";
		$valid=0;	
}
else
{
	$signin_sql="select * from user where email!='".$email."' and status=1"; 
	$signin_res=mysqli_query($con, $signin_sql);
	if(mysqli_num_rows($signin_res)<1)
	{
		$msg="No user exist with this Email Id";
		$valid=0;
	}
	else
	{
		$sqlchk="select * from user where email='".$email."' and status=1";
		$reschk =mysqli_query($con, $sqlchk);
		$rowchk =mysqli_fetch_object($reschk);
	
		
			$newpass=rand(100000,999999);
			$password_link="https://arabyos.com/login.php?email=$email&pass=$newpass&changepass=1&login=1&redirect=https://arabyos.com/change-password.php?current_pass=$newpass";
			$sql="update user set pass='".md5($newpass)."',password_email='1',password_link='".$password_link."' where email='".$email."' ";	
			mysqli_query($con, $sql) or die(mysql_error());
		$valid=1;
		$msg='<font color="#009900">Your login details sent to your email address.</font>';
		
		$fullname=$rowchk->name_prefix."&nbsp;".$rowchk->fname."&nbsp;".$rowchk->lname;
		
		$to = $email;  /*Put Your Email Adress Here*/
		$subject = "Your login details at ".get_page_settings(4); 
		$from_email = get_adminemail();
		$message = "Dear"."&nbsp;".$fullname."<br>";
		$message .= "In response to your request, please find your login details."."<br>";
	    $message.="<div style='max-width:302px;padding:7px;border:1px solid #e2e0e0;background:#f4f3f3;color:#444444'>
		<h1 style='font-family:Arial,Helvetica,sans-serif;font-size:16px;margin:0;color:#2f66a7'>Your"."&nbsp;".get_page_settings(4)."&nbsp;"."Login:</h1>
		<div style='font-size:13px;margin-top:7px;margin-bottom:10px;line-height:20px'><strong>Email Id: </strong>
		$email<br>
		</div>
		<a style='display:inline-block;border:1px solid #3079ed;padding:6px;text-align:center;color:#fff;font-size:16px;border-radius:2px;background-color:#4d90fe;background-image:linear-gradient(top,#4d90fe,#4787ed);text-decoration:none' alt='change your password' title='change your password' href='$password_link' target='_blank'>
		change your password</a>
		</div>";
		$headers  = "MIME-Version: 1.0\r\n";
		$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
		$headers .= "From: < ".$from_email." >";
		sendSMTPMail($to, $subject, $message, $headers);
	}
}
echo $msg."|".$valid;
?>