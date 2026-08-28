<?php 	
include "common.php";
error_reporting(1);
      if (isset($_GET['changepass'])) {
		$email = trim(($_GET['email']));	
		$password = md5(trim($_GET['pass']));
      }else{
      	$email = trim(($_POST['email']));	
		$password = md5(trim($_POST['pass']));
      }
	if(isset($_REQUEST['login']))	
	{	
	//echo $_SESSION['last_page'];exit;

	 $_SESSION['last_page']="my-dashboard.php";
		if($email == "")
		{
			$msg = "Please enter your Email id/Mobile";
			$ERR = 1;
			$_SESSION['errr_msg'] = $msg;
			header("location:sign-in.php");
		}
		else if($password == "")
		{
			$msg = "Please enter your password";
			$ERR = 1;
			$_SESSION['errr_msg'] = $msg;
			header("location:sign-in.php");
		}
		else
		{
             $sql = "SELECT * FROM `user` WHERE (`email` = '".$email."' OR `mobile1` = '".$email."') AND `pass` = '".$password."'";
			$qry = mysqli_query($con, $sql) or die(mysql_error());
			$arr = mysqli_fetch_assoc($qry);
			//echo $result= mysqli_num_rows($qry);
			if(mysqli_num_rows($qry) != 1)
			{
				$msg = "Email or Password is Incorrect";
				$ERR = 1;
			}
			if($ERR == 1)
			{
				$_SESSION['errr_msg'] = $msg;
				header("location: sign-in.php");
				exit();
			}
			//die();
			if (!empty($arr['usr_id'])) {
				$update_sql="update user set password_email='0',password_link='0' where usr_id='".$arr['usr_id']."' ";	
				mysqli_query($con, $update_sql) or die(mysql_error());
			}

			$_SESSION['uid_indm']=$arr['usr_id'];
			
			setcookie('cook_usr_id', $arr['usr_id'], time() + (86400 * 300), "/"); 
			
			/*if(isset($_SESSION['last_page']) && $_SESSION['last_page']!='')
			{
				unset($_SESSION['errr_msg']);
				if(strpos($_SESSION['last_page'], 'index.php?c=') !== false){
					header("location: company/".substr($_SESSION['last_page'],strpos($_SESSION['last_page'], 'index.php?c=')));
				}
				else {
					header("Location:".$_SESSION['last_page']);
				}
			}
			else
			{ */  

				unset($_SESSION['errr_msg']);
				if($_SESSION['last_page'] == 'compare.php'){
					header("location:compare.php");
					unset($_SESSION['last_page']);
					setcookie("productids", '', time()-1000);
        			setcookie("productids", '', time()-1000, '/');
				}
				else if(isset($_GET['redirect']) && $_GET['redirect'] != ''){				
					setcookie("productids", '', time()-1000);
        			setcookie("productids", '', time()-1000, '/');
					header("location:".$_GET['redirect']);
					die();
				}
				//elseif($_SESSION['last_page'] == 'post-buy-req.php'){
//					header("location:post-buy-req.php");
//					unset($_SESSION['last_page']);
//					setcookie("productids", '', time()-1000);
//        			setcookie("productids", '', time()-1000, '/');
//				}
				
					//else{
//					setcookie("productids", '', time()-1000);
//        			setcookie("productids", '', time()-1000, '/');
//					header("location:index.php");
//				}
//echo $_SESSION['last_page'];
				 if($_SESSION['last_page']){ 
                $url = $_SESSION['last_page'];
				}
				else {
					
                $url = "my-dashboard.php";
					
				}
			if($_SESSION['email_verify_for']){
				if($_SESSION['email_verify_for']==$_SESSION['uid_indm']){
					$sql="update user set `usr_emailVerify`='1' where `usr_id`='".$_SESSION['email_verify_for']."'";
					$res=mysqli_query($con, $sql);
				}
			}
			//die('bbbb');
				//echo $url;
				//exit;
				header("location:my-dashboard.php");exit();
				//header("Location: https://www.arabyos.com/$url");
				//die('gggg');
				
			//}
		}
	}
	else
	//die('die');
		{ header("location:sign-in.php");}	
	
?>