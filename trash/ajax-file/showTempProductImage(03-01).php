<?php
include "../common.php";

$ph_id=$_POST['id'];
$tbl=$_POST['tbl'];
$so_id=$_POST['so_id'];
$br_id=$_POST['br_id'];
$usr=$_POST['usr'];
$pd_id=$_POST['pd_id'];

if($tbl=='sale_offer_edit')
{
	$sql_ph="select * from photo where ph_id='".$ph_id."'";
	$res_ph=mysqli_query($con, $sql_ph);
	$row_ph=mysqli_fetch_object($res_ph);
	
	$sql_so="select * from sale_offer where so_id='".$so_id."'";
	$res_so=mysqli_query($con, $sql_so);
	$row_so=mysqli_fetch_object($res_so);
			
	$newFileName="so-".rand(1000,9999).substr($row_ph->ph_fileName,7);
			
	$pathS="../upload/image_gallery/".$row_ph->ph_fileName;	//source path
	$pathD="../upload/sale_offer/".$newFileName;	//destination path
	
	/** Thumb image creation **/
	$imgSImage = new SimpleImage();			
	$imgSImage->load($pathS);			
	$imgSImage->resize(100,80);//width,height
				
	$imgSImage->save("../upload/sale_offer/thumb/".$newFileName);
	/** Thumb image creation **/
	
	if(copy($pathS, $pathD))
	{
		$pathPrev="../upload/sale_offer/".$row_so->so_pic;	//old path
		if(is_file($pathPrev))
		{
			unlink($pathPrev);
		}
		
		$sql="update sale_offer
			set
				so_pic='".$newFileName."'
			where
				so_id='".$so_id."'";
		mysqli_query($con, $sql);
	}
}
else if($tbl=='temp_selloffer_image')
{
	$sql_ph="select * from photo where ph_id='".$ph_id."'";
	$res_ph=mysqli_query($con, $sql_ph);
	$row_ph=mysqli_fetch_object($res_ph);
	
	$sql_tsi="select * from temp_selloffer_image where tsi_usr_id='".$usr."'";
	$res_tsi=mysqli_query($con, $sql_tsi);
	$row_tsi=mysqli_fetch_object($res_tsi);
			
	$newFileName="so-".rand(1000,9999).substr($row_ph->ph_fileName,7);
			
	$pathS="../upload/image_gallery/".$row_ph->ph_fileName;	//source path
	$pathD="../upload/sale_offer/".$newFileName;	//destination path
	
	/** Thumb image creation **/
	$imgSImage = new SimpleImage();			
	$imgSImage->load($pathS);			
	$imgSImage->resize(100,80);//width,height
				
	$imgSImage->save("../upload/sale_offer/thumb/".$newFileName);
	/** Thumb image creation **/
	
	if(copy($pathS, $pathD))
	{
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
}
else if($tbl=='temp_product_image')
{    
            $imgarr= $_POST['imgArr'];
            $items = array();
            foreach($imgarr as $arrimg){
	$sql_ph="select * from photo where ph_id='".$arrimg."'";
	$res_ph=mysqli_query($con, $sql_ph);
	$row_ph=mysqli_fetch_object($res_ph);
	
	$sql_tpi="select * from temp_product_image where tpi_usr_id='".$usr."'";
	$res_tpi=mysqli_query($con, $sql_tpi);
	$row_tpi=mysqli_fetch_object($res_tpi);
			
	$newFileName="prd-".rand(1000,9999).substr($row_ph->ph_fileName,7);
			
	$pathS="../upload/image_gallery/".$row_ph->ph_fileName;	//source path
	$pathD="../upload/myproduct/".$newFileName;	//destination path
	
	/** Thumb image creation **/
	$imgSImage = new SimpleImage();			
	$imgSImage->load($pathS);			
	$imgSImage->resize(100,80);//width,height
				
	$imgSImage->save("../upload/myproduct/thumb/".$newFileName);
	/** Thumb image creation **/	
	
	if(copy($pathS, $pathD))
	{
		$pathLrg="../upload/myproduct/".$row_tpi->tpi_image;	//old path
		if(is_file($pathLrg))
		{
			unlink($pathLrg);
		}
		
		$pathThumb="../upload/myproduct/thumb/".$row_tpi->tpi_image;	//old path
		if(is_file($pathThumb))
		{
			unlink($pathThumb);
		}
		$items[]=$newFileName;
		
		}//foreach
		
		if($_POST['typ']=='product'){
		$imgname = implode(',',$items);
		mysqli_query($con, "delete from temp_product_image where tpi_usr_id='".$usr."'");
		
$sql="insert into temp_product_image
			set
				tpi_usr_id='".$usr."',
				tpi_image='".$imgname."',
				tpi_upload_date=now()";
		mysqli_query($con, $sql);

		}else if($_POST['typ']=='logo'){
		$sql="update temp_product_image
			set
				
				tpi_logo='".$newFileName."'
				where tpi_usr_id='".$usr."'";

		mysqli_query($con, $sql);
		}
	}
}
else if($tbl=='products_edit')
{
	$sql_ph="select * from photo where ph_id='".$ph_id."'";
	$res_ph=mysqli_query($con, $sql_ph);
	$row_ph=mysqli_fetch_object($res_ph);
	
	$sql_pd="select * from products where pd_id='".$pd_id."'";
	$res_pd=mysqli_query($con, $sql_pd);
	$row_pd=mysqli_fetch_object($res_pd);
			
	$newFileName="prd-".rand(1000,9999).substr($row_ph->ph_fileName,7);
			
	$pathS="../upload/image_gallery/".$row_ph->ph_fileName;	//source path
	$pathD="../upload/myproduct/".$newFileName;	//destination path
	
	/** Thumb image creation **/
	$imgSImage = new SimpleImage();			
	$imgSImage->load($pathS);			
	$imgSImage->resize(100,80);//width,height
				
	$imgSImage->save("../upload/myproduct/thumb/".$newFileName);
	/** Thumb image creation **/
	
	if(copy($pathS, $pathD))
	{
		$pathLrg="../upload/myproduct/".$row_pd->pd_image;	//old path
		if(is_file($pathLrg))
		{
			unlink($pathLrg);
		}
		
		$pathThumb="../upload/myproduct/thumb/".$row_pd->pd_image;	//old path
		if(is_file($pathThumb))
		{
			unlink($pathThumb);
		}
		
		$sql="update products
			set
				pd_image='".$newFileName."'
			where
				pd_id='".$pd_id."'";
				echo $sql;
		mysqli_query($con, $sql);
	}
}
else if($tbl=='temp_buyrequirement_image')
{
	$sql_ph="select * from photo where ph_id='".$ph_id."'";
	$res_ph=mysqli_query($con, $sql_ph);
	$row_ph=mysqli_fetch_object($res_ph);
	
	$sql_tbi="select * from temp_buyrequirement_image where tbi_usr_id='".$usr."'";
	$res_tbi=mysqli_query($con, $sql_tbi);
	$row_tbi=mysqli_fetch_object($res_tbi);
			
	$newFileName="br-".rand(1000,9999).substr($row_ph->ph_fileName,7);
			
	$pathS="../upload/image_gallery/".$row_ph->ph_fileName;	//source path
	$pathD="../upload/buy_requirement/".$newFileName;	//destination path
	
	/** Thumb image creation **/
	$imgSImage = new SimpleImage();			
	$imgSImage->load($pathS);			
	$imgSImage->resize(100,80);//width,height
				
	$imgSImage->save("../upload/buy_requirement/thumb/".$newFileName);
	/** Thumb image creation **/
	
	if(copy($pathS, $pathD))
	{
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
}
else if($tbl=='buy_requirement_edit')
{
	$sql_ph="select * from photo where ph_id='".$ph_id."'";
	$res_ph=mysqli_query($con, $sql_ph);
	$row_ph=mysqli_fetch_object($res_ph);
	
	$sql_br="select * from buy_requirement where br_id='".$br_id."'";
	$res_br=mysqli_query($con, $sql_br);
	$row_br=mysqli_fetch_object($res_br);
			
	$newFileName="br-".rand(1000,9999).substr($row_ph->ph_fileName,7);
			
	$pathS="../upload/image_gallery/".$row_ph->ph_fileName;	//source path
	$pathD="../upload/buy_requirement/".$newFileName;	//destination path
	
	/** Thumb image creation **/
	$imgSImage = new SimpleImage();			
	$imgSImage->load($pathS);			
	$imgSImage->resize(100,80);//width,height
				
	$imgSImage->save("../upload/buy_requirement/thumb/".$newFileName);
	/** Thumb image creation **/
	
	if(copy($pathS, $pathD))
	{
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
}

?>