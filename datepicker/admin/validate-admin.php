<?php 
//ob_start();
//session_start();
	
      
	include "../common.php";
	$username = trim(addslashes($_POST['username']));	
	$pass = trim($_POST['password']);
	$encrypted_pass = md5(trim($_POST['password']));
	
	if(isset($_POST['login']))	
	{
     		if($username == "")
		{
			$msg = "Please enter username";
			$ERR = 1;
		}
		else if($pass == "")
		{
			$msg = "Please enter password";
			$ERR = 1;
		}
		else
		{
			$sql = "SELECT * FROM `admin_user` WHERE `username` = '".$username."' AND `password` = '".$encrypted_pass."' AND `status` = '1' LIMIT 1 ";
                  
			$qry = mysqli_query($con, $sql) or die(mysql_error());
			
			/*echo $ab=mysqli_num_rows($qry);*/
                        //echo mysqli_num_rows($qry);exit;
			if(mysqli_num_rows($qry) != 1)
			{
				$msg = "Username or Password Incorrect";
				$ERR = 1;
			}

			if($ERR == 1)
			{
				$_SESSION['err_msg'] = $msg;
				header("location:index.php");
				exit();
			}
			$arr = mysqli_fetch_array( $qry);
			$ip = getenv('REMOTE_ADDR');
			$sql  = "INSERT INTO `".$db_name."`.`admin_login_details` set 
                                 id = '".$arr['id']."',
				 last_login_time = NOW(),
				 user_ip = '".$ip."'";
																	
			$qry = mysqli_query($con, $sql);
			$_SESSION['ad_username_indm']=$username;
            $_SESSION['ad_email_indm']=$arr['email'];
			$_SESSION['ad_id_indm']=$arr['id'];
			header("location:welcome.php");	
		}
	}
	else
	{
		header("location:index.php");	
	}
	if($ERR == 1)
	{
		$_SESSION['err_msg'] = $msg;
		header("Location:index.php");
//		exit();
	}
	
?>
