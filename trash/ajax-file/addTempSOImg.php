<?php
/*ob_start();
session_start();*/
include "../common.php";

$usr=$_POST['usr'];

$targetFolder = '../upload/sale_offer/'; // Relative to the root

if(!empty($_FILES))
{
	
	$newFileName='so-'.rand(1000,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/sale_offer/".$newFileName) or die('error');	
		
		/** Thumb image creation **/
		$imgSImage = new SimpleImage();			
		$imgSImage->load("../upload/sale_offer/".$newFileName);			
		$imgSImage->resize(100,80);//width,height
				
		$imgSImage->save("../upload/sale_offer/thumb/".$newFileName);
		/** Thumb image creation **/
	
		$sql_tsi="select * from temp_selloffer_image where tsi_usr_id='".$usr."'";
		$res_tsi=mysqli_query($con, $sql_tsi);
		$row_tsi=mysqli_fetch_object($res_tsi);
	
		$pathLrg="../upload/sale_offer/".$row_tsi->tsi_image;	//old path
		if(is_file($pathLrg))
		{
			unlink($pathLrg);
		}
		$pathThumb="../upload/sale_offer/thumb/".$row_tsi->tsi_image;	//old path
		if(is_file($pathThumb))
		{
			unlink($pathThumb);
		}
		
		mysqli_query($con, "delete from temp_selloffer_image where tsi_usr_id='".$usr."'");
		
		$sql="insert into temp_selloffer_image
			set
				tsi_usr_id='".$usr."',
				tsi_image='".$newFileName."',
				tsi_upload_date=now()";
			mysqli_query($con, $sql);
	}
	else
	{
		echo 'Invalid file type.';
	}
}

?>