<?php 
	$sql_cnname = "select * from site_settings where st_id='15'";
	$qry_cnname = mysqli_query($con, $sql_cnname);
	$arr_cnname = mysqli_fetch_array( $qry_cnname);
	$res = mysqli_query($con, "select * from user where usr_oauth_reg = '4' and email = '".$email."' and status = '1'");
	
	if(mysqli_num_rows($res) > 0)
	{
		$row = mysqli_fetch_object($res);
		
		$_SESSION['uid_indm'] = $row->usr_id;
		$_SESSION['login_indm'] = 'true';
		
		if(isset($_SESSION['last_page']) && $_SESSION['last_page']!='')
			{
				unset($_SESSION['errr_msg']);
				header("Location:../../".$_SESSION['last_page']);
			}
			else
			{   
				unset($_SESSION['errr_msg']);
				header("location:../../index.php");
			}	
	}
	else
	{
			mysqli_query($con, "insert into user
					set
						usr_oauth_reg = '4',
						fname = '".$fname."',
						lname = '".$lname."',
						email = '".$email."',
						pass = '".rand(1000000,9999999)."',
						country = '".$arr_cnname['st_value']."',
						usr_emailVerify =  '1',
						date = now()");
			
						$id = mysql_insert_id();
						$_SESSION['uid_indm']=$id;
						$_SESSION['login_indm'] = 'true';
				
				
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
								
								
			if(isset($_SESSION['last_page']) && $_SESSION['last_page']!='')
			{
				unset($_SESSION['errr_msg']);
				header("Location:../../".$_SESSION['last_page']);
			}
			else
			{   
				unset($_SESSION['errr_msg']);
				header("location:../../index.php");
			}					
								
								
	}
	
			
?>