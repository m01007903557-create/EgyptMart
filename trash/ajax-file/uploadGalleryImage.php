<?php
include "../common.php";

$ph_u_id=$_POST['id'];

$targetFolder = '../upload/image_gallery/'; // Relative to the root

if(!empty($_FILES))
{
	
	$temp_image='ph-'.rand(0,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		
	$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/image_gallery/".$temp_image) or die('error');	
	
		
		
		$sql="insert into photo
			set
				ph_u_id='".$ph_u_id."',
				ph_fileName='".$temp_image."',
				ph_updated_date=now()";			
		
		
		mysqli_query($con, $sql);
		
		//echo $sql;
	} else {
		echo 'Invalid file type.';
	}
}

?>