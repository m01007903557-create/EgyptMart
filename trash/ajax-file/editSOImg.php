<?php
/*ob_start();
session_start();*/
include "../common.php";

$so_id=$_POST['id'];

$targetFolder = '../upload/sale_offer/'; // Relative to the root

if(!empty($_FILES))
{
	
	$sql_so="select * from sale_offer where so_id='".$so_id."'";
	$res_so=mysqli_query($con, $sql_so);
	$row_so=mysqli_fetch_object($res_so);
	
	
	$newFileName='so-'.rand(0,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/sale_offer/".$newFileName) or die('error');
	
		if($ds)
		{
			/** Thumb image creation **/
			$imgSImage = new SimpleImage();			
			$imgSImage->load("../upload/sale_offer/".$newFileName);			
			$imgSImage->resize(100,80);//width,height
				
			$imgSImage->save("../upload/sale_offer/thumb/".$newFileName);
			/** Thumb image creation **/
			
			$pathLrg="../upload/sale_offer/".$row_so->so_pic;	//old path
			if(is_file($pathLrg))
			{
				unlink($pathLrg);
			}
		
			$pathThumb="../upload/sale_offer/thumb/".$row_so->so_pic;	//old path
			if(is_file($pathThumb))
			{
				unlink($pathThumb);
			}
			
			$sql="update sale_offer
				set
					so_pic='".$newFileName."'
				where
					so_id='".$so_id."'";
		
			mysqli_query($con, $sql);
		}
		
		//echo $sql;
	} else {
		echo 'Invalid file type.';
	}
}

?>