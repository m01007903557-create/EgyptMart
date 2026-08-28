<?php 
include "common.php";
//check_user_login();
$uid=$_POST['uid']; 
$file_name=$_POST['file']; 
$targetFolder = 'server/php/files'; // Relative to the root
if (!empty($file_name))
{
	
		$sqlk="SELECT * FROM business_profile WHERE bnsprof_uid ='".$uid."'";
    	$resk=mysql_query($sqlk);
		$rowk=mysql_fetch_object($resk);
		if(mysql_num_rows($resk)>0)
	    {
    	    $sql="update business_profile set bnsprof_complogo ='".$file_name."' where bnsprof_uid ='".$uid."'";	
			mysql_query($sql) or die(mysql_error());
		
			$pathLrg="server/php/files/".$rowk->bnsprof_complogo;
			if(is_file($pathLrg))
			{
				unlink($pathLrg);
			}
			$pathThumb="server/php/files/".$rowk->bnsprof_complogo;
			if(is_file($pathThumb))
			{
				unlink($pathThumb);
			}
			
		}
		else
		{
		    
			$sql="insert into business_profile set bnsprof_uid ='".$uid."',bnsprof_complogo ='".$file_name."',bnsprof_creation_date=now()";	
			mysql_query($sql) or die(mysql_error());		
		}
	}
?>