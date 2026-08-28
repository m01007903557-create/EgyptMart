<?php 

$_SESSION['uid_indm'] = $userdata['usr_id'];
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




?>