<?php
/*ob_start();
session_start();*/
include "../common.php";

$usr=$_POST['usr'];

$targetFolder = '../upload/buy_requirement/'; // Relative to the root

if(!empty($_FILES))
{
	
	$newFileName='br-'.rand(1000,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/buy_requirement/".$newFileName) or die('error');	
		
		/** Thumb image creation **/
		$imgSImage = new SimpleImage();			
		$imgSImage->load("../upload/buy_requirement/".$newFileName);			
		$imgSImage->resize(100,80);//width,height
				
		$imgSImage->save("../upload/buy_requirement/thumb/".$newFileName);
		/** Thumb image creation **/
	
		$sql_tbi="select * from temp_buyrequirement_image where tbi_usr_id='".$usr."'";
		$res_tbi=mysqli_query($con, $sql_tbi);
		$row_tbi=mysqli_fetch_object($res_tbi);
	
		$pathLrg="../upload/buy_requirement/".$row_tbi->tbi_image;	//old path
		if(is_file($pathLrg))
		{
			unlink($pathLrg);
		}
		$pathThumb="../upload/buy_requirement/thumb/".$row_tbi->tbi_image;	//old path
		if(is_file($pathThumb))
		{
			unlink($pathThumb);
		}
		
		mysqli_query($con, "delete from temp_buyrequirement_image where tbi_usr_id='".$usr."'");
		
		$sql="insert into temp_buyrequirement_image
			set
				tbi_usr_id='".$usr."',
				tbi_image='".$newFileName."',
				tbi_upload_date=now()";
			mysqli_query($con, $sql);
	}
	else
	{
		echo 'Invalid file type.';
	}
}

?>