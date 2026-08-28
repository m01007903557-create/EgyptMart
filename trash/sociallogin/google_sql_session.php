<?php 
	$sql_cnname = "select * from site_settings_arabyos where st_id='15'";
	$qry_cnname = mysqli_query($con, $sql_cnname);
	$arr_cnname = mysqli_fetch_array( $qry_cnname);

	
	 //compare user id in our database
    $result = mysqli_query($con, "SELECT * FROM user WHERE usr_oauth_reg = '2' and email='".$google_email."'");
	if($result === false) { 
		die(mysql_error()); //result is false show db error and exit.
	}
	
	//echo mysqli_num_rows($result);
    if(mysqli_num_rows($result) > 0) //user id exist in database
    {
		$UserCount = mysqli_fetch_array( $result);
		$_SESSION['uid_indm']=$UserCount['usr_id'];
		$_SESSION['login_indm'] = 'true';	
		
		if(isset($_SESSION['last_page']) && $_SESSION['last_page']!='')
			{
				unset($_SESSION['errr_msg']);
				header("Location:".$_SESSION['last_page']);
			}
			else
			{   
				unset($_SESSION['errr_msg']);
				header("location:index.php");
			}
	
    }
	else
	{ //user is new
		
			$splitName = explode(' ',$user_name);
			mysqli_query($con, "insert into user
			set
				usr_oauth_reg = '2',
				fname = '".$splitName[0]."',
				lname = '".$splitName[1]."',
				email = '".$google_email."',
				pass = '".rand(1000000,9999999)."',
				country = '".$arr_cnname['st_value']."',
				usr_emailVerify =  '1',
				date = now()");
		
		
				
				$id=mysql_insert_id();
				$sql_bpf="insert into business_profile
			set
				bnsprof_uid='".$id."',
				bnsprof_creation_date=now()";
		mysqli_query($con, $sql_bpf);
		
		$sql_webst="insert into website_content
			set
				wc_usr_id='".$id."',
				wc_updated_date=now()";
		mysqli_query($con, $sql_webst);
			
			
		$_SESSION['uid_indm']=$id;
		$_SESSION['login_indm'] = 'true';
		
			
			
		if(isset($_SESSION['last_page']) && $_SESSION['last_page']!='')
			{
				unset($_SESSION['errr_msg']);
				header("Location:".$_SESSION['last_page']);
			}
			else
			{   
				unset($_SESSION['errr_msg']);
				header("location:index.php");
			}
		
		
	}

	


?>