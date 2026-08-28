<?php
include 'common.php';


$link = "<a href=http://".$_SERVER['SERVER_NAME']."/verifyUser.php?token=".rand(1000,9999).md5($_SESSION['uid_indm']).">Verify</a>";
$to = stripslashes(user_info($_SESSION['uid_indm'],'email')); 
$subject = "Email Verification from ".get_page_settings(4);
$from_name = get_page_settings(4);
$from_email = get_adminemail();

include "email/emailVerification.php"; //email design with content included
$_SESSION['email_verify_for']=$_SESSION['uid_indm'];
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From: $from_name < $from_email >";

if(sendSMTPMail($to, $subject, $message1, $headers)){
	header("location:my-dashboard.php?verifylinksend=1");
}
			
		
		/*$link = "<a href=http://".$_SERVER['SERVER_NAME']."/verifyUser.php?token=".rand(1000,9999).md5($_SESSION['uid_indm']).">Verify</a>";
		$to = stripslashes(user_info($_SESSION['uid_indm'],'email')); 
		$subject = "Email Verification from ".get_page_settings(4);
		$from_name = get_page_settings(4);
		$from_email = get_adminemail();
		$message = "Click on folowing link to verify your email with us : ".$link;
		$headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= "From: $from_name < $from_email >";

		if(mail($to, $subject, $message, $headers)){header("location:my-dashboard.php?verifylinksend=1");}*/
       
?>

