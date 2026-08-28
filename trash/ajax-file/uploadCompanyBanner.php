<?php
include "../common.php";

$cb_bnsprof_id=$_POST['id'];

$targetFolder = '../upload/company_banner/'; // Relative to the root

if(!empty($_FILES))
{
	
	$newFileName='cb-'.$cb_bnsprof_id.rand(0,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$imgSImage = new SimpleImage();			
		$imgSImage->load($_FILES['Filedata']['tmp_name']);			
		$imgSImage->resize(900,200);//width,height
				
		$imgSImage->save("../upload/company_banner/".$newFileName);
		
		//$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/company_banner/".$temp_image) or die('error');	
		
		$sql="insert into company_banner
			set
				cb_bnsprof_id='".$cb_bnsprof_id."',
				cb_image='".$newFileName."',
				cb_updated_date=now()";
		
		mysqli_query($con, $sql);
		
		//echo $sql;
	}
	else
	{
		echo 'Invalid file type.';
	}
}

?>