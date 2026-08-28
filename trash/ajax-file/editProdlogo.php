<?php
/*ob_start();
session_start();*/
include "../common.php";

$pd_id=$_POST['id'];

$targetFolder = '../upload/myproduct/'; // Relative to the root

if(!empty($_FILES))
{
	$sql_pd="select * from products where pd_id='".$pd_id."'";
	$res_pd=mysqli_query($con, $sql_pd);
	$row_pd=mysqli_fetch_object($res_pd);
	
	$newFileName='prd-'.rand(1000,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/myproduct/".$newFileName) or die('error');	
		
		if($ds)
		{
			/** Thumb image creation **/
			$imgSImage = new SimpleImage();			
			$imgSImage->load("../upload/myproduct/".$newFileName);			
			$imgSImage->resize(100,80);//width,height
				
			$imgSImage->save("../upload/myproduct/thumb/".$newFileName);
			/** Thumb image creation **/
			
			$pathLrg="../upload/myproduct/".$row_pd->pd_imagelogo;	//old path
			if(is_file($pathLrg))
			{
				unlink($pathLrg);
			}
		
			$pathThumb="../upload/myproduct/thumb/".$row_pd->pd_imagelogo;	//old path
			if(is_file($pathThumb))
			{
				unlink($pathThumb);
			}
		        
		        $allimg = $row_pd->pd_imagelogo;
		        $newimg=$newFileName.",".$allimg;
			$sql="update products set pd_imagelogo='".$newimg."'
			where
				pd_id='".$pd_id."'";
		
		
			mysqli_query($con, $sql);
		}
		
	} else {
		echo 'Invalid file type.';
	}
}

?>