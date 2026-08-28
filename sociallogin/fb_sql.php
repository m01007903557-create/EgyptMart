<?php 
	$sql_cnname = "select * from site_settings where st_id='15'";
	$qry_cnname = mysqli_query($con, $sql_cnname);
	$arr_cnname = mysqli_fetch_array( $qry_cnname);
	
	
 $f_l_name=explode(" ",$username);
        $query = mysqli_query($con, "SELECT * FROM `user` WHERE usr_oauth_reg = '1' and email = '".$email."' and status = '1'") or die(mysql_error());
        $result = mysqli_fetch_array( $query);
		
        if (!empty($result))
		{
        	$query_f = mysqli_query($con, "update `user` set usr_oauth_reg = '1' WHERE email = '".$email."'") or die(mysql_error());  
        } 
		else 
		{
            #user not present. Insert a new Record
				$usr_password=createRandomPassword('1');
 				$usr_password=rand(10,99).md5($usr_password);
				$parts = explode("@", $email);
				$usr_name = $parts[0];
            					
				$query = mysqli_query($con, "insert into user
			set
				usr_oauth_reg = '1',
				fname = '".$f_l_name[0]."',
				lname = '".$f_l_name[1]."',
				email = '".$email."',
				pass = '".$usr_password."',
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
				
           		$query = mysqli_query($con, "SELECT * FROM `user` WHERE usr_oauth_reg = '1' and email = '".$email."' and status = '1'");
            	$result = mysqli_fetch_array( $query);
            	return $result;
        }

?>