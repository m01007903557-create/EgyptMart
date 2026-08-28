<?php
/*ob_start();
session_start();*/
include "../common.php";

$usr=$_POST['usr'];

$targetFolder = '../upload/myproduct/'; // Relative to the root

if(!empty($_FILES))
{
	
	$newFileName='prd-'.rand(1000,9999).trim(addslashes($_FILES['Filedata']['name']));
	
	$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array(strtolower($fileParts['extension']),$fileTypes))
	{
		$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/myproduct/".$newFileName) or die('error');	
	
		/** Thumb image creation **/
		$imgSImage = new SimpleImage();			
		$imgSImage->load("../upload/myproduct/".$newFileName);			
		$imgSImage->resize(100,80);//width,height
				
		$imgSImage->save("../upload/myproduct/thumb/".$newFileName);
		/** Thumb image creation **/	
	
		$sql_tpi="select * from temp_product_image where tpi_usr_id='".$usr."'";
		$res_tpi=mysql_query($sql_tpi);
		$row_tpi=mysql_fetch_object($res_tpi);
	
		$pathLrg="../upload/myproduct/".$row_tpi->tpi_logo;	//old path
		if(is_file($pathLrg))
		{
			unlink($pathLrg);
		}
		
		$pathThumb="../upload/myproduct/thumb/".$row_tpi->tpi_logo;	//old path
		if(is_file($pathThumb))
		{
			unlink($pathThumb);
		}
		
		$sql_get=mysqli_query($con,"select * from temp_product_image where tpi_usr_id='".$usr."'");
		$rowcount=mysqli_num_rows($sql_get);
		    if($rowcount>0){ 
		    $sql="update temp_product_image
			        set
				tpi_logo='".$newFileName."'
				where tpi_usr_id='".$usr."'";
			  mysqli_query($con, $sql);
		         }else{
		
		mysql_query("delete from temp_product_image where tpi_usr_id='".$usr."'");
		
		$sql="insert into temp_product_image
			set
				tpi_usr_id='".$usr."',
				tpi_logo='".$newFileName."',
				tpi_upload_date=now()";
			mysql_query($sql);
			}
	}
	else
	{
		echo 'Invalid file type.';
	}
}

?>