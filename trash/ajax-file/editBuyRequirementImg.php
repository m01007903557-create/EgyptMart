<?php
/*ob_start();
session_start();*/
include "../common.php";

$br_id=$_POST['id'];

$targetFolder = '../upload/buy_requirement/'; // Relative to the root

if(!empty($_FILES))
{
	
	$sql_br="select * from buy_requirement where br_id='".$br_id."'";
	$res_br=mysqli_query($con, $sql_br);
	$row_br=mysqli_fetch_object($res_br);
	
	
	$newFileName='br-'.rand(0,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/buy_requirement/".$newFileName) or die('error');
	
		if($ds)
		{
			/** Thumb image creation **/
			$imgSImage = new SimpleImage();			
			$imgSImage->load("../upload/buy_requirement/".$newFileName);			
			$imgSImage->resize(100,80);//width,height
				
			$imgSImage->save("../upload/buy_requirement/thumb/".$newFileName);
			/** Thumb image creation **/
			
			$pathLrg="../upload/buy_requirement/".$row_br->br_pic;	//old path
			if(is_file($pathLrg))
			{
				unlink($pathLrg);
			}
		
			$pathThumb="../upload/buy_requirement/thumb/".$row_br->br_pic;	//old path
			if(is_file($pathThumb))
			{
				unlink($pathThumb);
			}
			
			$sql="update buy_requirement
				set
					br_pic='".$newFileName."'
				where
					br_id='".$br_id."'";
		
			mysqli_query($con, $sql);
		}
		
		//echo $sql;
	} else {
		echo 'Invalid file type.';
	}
}

?>