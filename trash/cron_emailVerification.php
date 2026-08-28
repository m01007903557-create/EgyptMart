<?php
include "common.php";
//echo '--'.getEmailVerificationStatus().'--';
//exit;
$total_sent = 0;
if(getEmailVerificationStatus()==1)
{
	$date_today = date('Y-m-d');
	$sql="select * from user where usr_emailVerify='0' and (usr_emailVerify_lastDate < '$date_today 00:00:00' or usr_emailVerify_lastDate IS NULL)";
	$res=mysqli_query($con, $sql) or die(mysqli_error($con)); echo mysqli_num_rows($res);
	while($row=mysqli_fetch_object($res))
	{

    /**** code for email sending start here ****/
           
		$_SESSION['uid_indm']=$row->usr_id;
		$_SESSION['eml_indm']= $row->email;
		$_SESSION['msg']=$msg;//all of this except id may be not needed // webxtor 1 June 2018
		$fullname=$row->fname.' '.$row->lname;

		
		$link = "<a href=http://".$_SERVER['SERVER_NAME']."/verifyUser.php?token=".rand(1000,9999).md5($_SESSION['uid_indm']).">Verify</a>";
		$to = stripslashes(getUserInfo($_SESSION['uid_indm'],'email'));  /*Put Your Email Adress Here*/
		$subject = "Email Verification from ".get_page_settings(4);
		$from_name = get_page_settings(4);
		$from_email = get_adminemail();
		
		include "email/emailVerification.php"; //email design with content included
			
		/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
		$message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;
		$message .= "<br /><br />".get_page_settings(4)." Team";*/
		$headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
   	    $headers .= "From: $from_name < $from_email >";
			//print_r($_SESSION);
			//echo "$to, $subject, $message1, $headers";exit;
		//
		if(mail($to, $subject, $message1, $headers)) {
			$total_sent++;
			mysqli_query($con, "update user set usr_emailVerify_lastDate = '".date('Y-m-d H:i:s')."' where usr_id='".$row->usr_id."'");
			sleep(1);
		}
	}
		/**** code for email sending end here ****/
}
echo "Total emails sent: $total_sent";

