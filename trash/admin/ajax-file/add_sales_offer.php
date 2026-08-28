<?php 
include "../../common.php";
global $con;
$pid = explode('-' , $_GET['id']);
$ProID = $pid[1];
$SQL = "select * from products where pd_id = $ProID";
$unitsql = mysqli_query($con,$SQL);
while($unitrow=mysqli_fetch_object($unitsql)){
	
	$pd_subcat_id = $unitrow->pd_subcat_id;
	$pd_uid = $unitrow->pd_uid;
	$pd_title = $unitrow->pd_title;
	$pd_image = $unitrow->pd_image;
	$pd_dsection = $unitrow->pd_desc;
	$pd_global_display = $unitrow->pd_global_display;
	$so_preferred_buyer_location = 'any';
	$so_validity = 90;
	echo $pd_image ;
	copy('../../upload/myproduct/'.$pd_image,'../../upload/sale_offer/'.$pd_image);
	copy('../../upload/myproduct/'.$pd_image,'../../upload/sale_offer/thumb/'.$pd_image);
		$sql="INSERT INTO sale_offer
		SET			
		so_usr_id ='".$pd_uid."',
		so_pc_id ='".$pd_subcat_id."',
		so_service ='".$pd_title."',
		so_description ='".$pd_dsection."',
		so_preferred_buyer_location ='".$so_preferred_buyer_location."',
		so_pic ='".$pd_image."',
		so_validity ='".$so_validity."',
		so_posting_date=now()";
		mysqli_query($con, "update products set pd_so_slider='1' where pd_id='".$ProID."'");

		mysqli_query($con, $sql) or die(mysql_error());
				

}

  
  
?>