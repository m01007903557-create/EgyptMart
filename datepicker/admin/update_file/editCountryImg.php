<?php
/*ob_start();
session_start();*/
include "../common.php";

$cn_id=$_POST['cn_id'];

$sql_cn="select * from country where cn_id='".$cn_id."'";
$res_cn=mysqli_query($con, $sql_cn);
$row_cn=mysqli_fetch_object($res_cn);

$targetFolder = '../images/country_flag/'; // Relative to the root

if(!empty($_FILES))
{
//	$cn_flag=$row_cn->cn_name.$row_cn->cn_currency.trim(addslashes($_FILES['cn_flag']['name']));	
	$newFileName=$row_cn->cn_name.$row_cn->cn_currency.trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../images/country_flag/".$newFileName) or die('error');	
		if($ds)
		{
			
			$imgSImage = new SimpleImage();			
			$imgSImage->load("../images/country_flag/".$newFileName);			
			$imgSImage->resize(30,20);//width,height
				
			$imgSImage->save("../images/country_flag/".$newFileName);
				
			$path="../images/country_flag/".$row_cn->cn_flag;	//old path
			if(is_file($path))
			{
				unlink($path);
			}
			
			$sql="update country
				set
					cn_flag='".$newFileName."'
				where
					cn_id='".$row_cn->cn_id."'";
			mysqli_query($con, $sql);
		}
	}
	else
	{
		echo '0';
	}
}

?>