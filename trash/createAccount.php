<?php
ob_start();
session_start();
include "common.php";


	$name_prefix= addslashes(trim($_POST['name_prefix']));
	$fname= addslashes(trim($_POST['fname']));	
	$lname= addslashes(trim($_POST['lname']));	
	$email= addslashes(trim($_POST['email']));
	$country= addslashes(trim($_POST['country']));
	$ph_country = $_POST['ph_country'];
	$mobile1= addslashes(trim($_POST['mobile1']));
	$website= addslashes(trim($_POST['website']));
	$city= addslashes(trim($_POST['city']));
	$state= addslashes(trim($_POST['state']));
	$city_others= addslashes(trim($_POST['city_others']));
	$state_others= addslashes(trim($_POST['state_others']));
	$postal_code= addslashes(trim($_POST['postal_code']));
	$businessname= addslashes(trim($_POST['businessname']));
	$authority= addslashes(trim($_POST['authority']));
	$authority1= addslashes(trim($_POST['authority1']));
	$perposition= addslashes(trim($_POST['perposition']));
	$profileimage= addslashes(trim($_POST['profileimage']));
	$comapnyimage= addslashes(trim($_POST['comapnyimage']));
	$pass= addslashes(trim($_POST['pass']));
	$npass= md5($pass);
	

$data=array();

	$em=1;
	$sql_chk="select * from user where email='".$email."' and status=1";
	$res_chk=mysqli_query($con, $sql_chk);
	if(mysqli_num_rows($res_chk)>0)
	{	
		$em=0;		
	}
	if($fname == '')
	{
		$data[0]="0";
		$data[1]="<font color=red>Please enter first name</font>";
		$valid=false;	
	}
	else if($email=="")
	{
		$data[0]="0";
		$data[1]="<font color='#CC0000'>Please enter email</font>";
		$valid=false;	
	}
	else if(!validate::is_email($email))
	{
		$data[0]="0";
		$data[1]="<font color='#CC0000'>Please enter valid email</font>";
		$valid=false;		
	}
	else if($em==0)
	{	
		$data[0]="0";
		$data[1]="<font color=red>Please enter another Email Id. User already exist with this ID.</font>";
		$valid=false;				
	}
	else if($country=="")
	{
		$data[0]="0";
		$data[1]="<font color=red>Please select country.</font>";
		$valid=false;	
	}
	else if($ph_country =="")
	{
		$data[0]="0";
		$data[1]="<font color=red>Country ISD Code Must Not Blank.</font>";
		$valid=false;	
	}
	else if($mobile1 =="")
	{
		$data[0]="0";
		$data[1]="<font color=red>Please Enter Mobile.</font>";
		$valid=false;	
	}
	else if($website != '' && !(validate::is_weblink($website)))
	{
		$data[0]="0";
		$data[1]="<font color=red>Please Enter a Valid Web Link</font>";
		$valid=false;
	}
	else if($pass=="")
	{
		$data[0]="0";
		$data[1]="<font color=red>Please enter password</font>";
		$valid=false;	
	}
	
	else
	{
		$valid=true;	
	}
	
	if($valid==true)
	{	
		$filePath = dirname(__FILE__).'/server/php/files/'.$profileimage;
		$thumbfilePath = dirname(__FILE__).'/server/php/files/thumbnail/'.$profileimage;
		//echo $filePath;
		$image_data ='';
		if (file_exists($filePath))
		{
		$image_data = addslashes (file_get_contents($filePath));
		unlink($filePath);
		unlink($thumbfilePath);
		}
		//echo $image_data; 
		//exit;
		$insert1="insert into user 
				set
				   email='".$email."',
				   name_prefix='".$name_prefix."',
				   fname='".$fname."', 
				   lname='".$lname."',
				   country_ph_code = '".$ph_country."',
				   country='".$country."',
				   mobile1='".$mobile1."',
				   pass='".$npass."',
				   website='".$website."',
				   image='".$profileimage."',
				   profileImage='".$image_data ."',
				   date=now()";
				  // echo $insert1;exit;
		mysqli_query($con, $insert1);
		$id=mysqli_insert_id($con);
		if(getEmailVerificationStatus()==0)
		{
			$sql_veify_upd="update user
				set
					usr_emailVerify='1'
				where
					usr_id='".$id."'";
			mysqli_query($con, $sql_veify_upd);
		}
		
		$sql_bpf="insert into business_profile
			set
				bnsprof_uid='".$id."',
                bnsprof_designation='".$perposition."',
				bnsprof_ceoprefix='".$name_prefix."',
				bnsprof_ceofname='".$fname."',
				bnsprof_ceolname='".$lname."',
				bnsprof_compname='".$businessname."',
				bnsprof_doc='".$comapnyimage."',
				bnsprof_city='".$city."',
				bnsprof_state='".$state."',
				bnsprof_zipcode='".$postal_code."',
				bnsprof_website_alt='".$website."',
				bnsprof_regauthority='".$authority."',
				bnsprof_svtax_no='".$authority1."',
				bnsprof_creation_date=now()";
		mysqli_query($con, $sql_bpf);
		
		$sql_webst="insert into website_content
			set
				wc_usr_id='".$id."',
				wc_updated_date=now()";
		mysqli_query($con, $sql_webst);
		
		
		$_SESSION['uid_indm']=$id;
		$_SESSION['eml_indm']= $email;
		$_SESSION['msg']=$msg;
		$fullname=$fname.' '.$lname;
		if(getEmailVerificationStatus()==1)
		{
		
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
			
			sendSMTPMail($to, $subject, $message1, $headers);
		
		}
		else
		{
			$to = stripslashes(getUserInfo($_SESSION['uid_indm'],'email'));  /*Put Your Email Adress Here*/
			$subject = "Welcome to ".get_page_settings(4);
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			
			include "email/emailVerification.php"; //email design with content included
			
			/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
			$message .= "We are happy you joined.";
			$message .= "<br /><br />".get_page_settings(4)." Team";*/
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";

			mail($to, $subject, $message2, $headers);	
			
		}
		
		
		/********** Email Notification to Admin on User Creation ***********/
			$sql_cn="select * from country where cn_id='".$country."'";
			$res_cn=mysqli_query($con, $sql_cn);
			$row_cn=mysqli_fetch_object($res_cn);
			
			$to = get_adminemail();
			$subject = "User Creation Notification";
			$from_name = get_page_settings(4);
			$from_email = get_adminemail();
			
			include "email/emailVerification.php"; //email design with content included
			
			/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
			$message .= "We are happy you joined.";
			$message .= "<br /><br />".get_page_settings(4)." Team";*/
			$headers  = "MIME-Version: 1.0\r\n";
	        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    	    $headers .= "From: $from_name < $from_email >";

			mail($to, $subject, $message_admin, $headers);
		/**********************/
		
		$data[0]="1";
		$data[1]="Thank you for creating account with ARABYOS, We have sent you a confirmation to your email.";	
	}

echo $data[0]."|".$data[1];
?>