<?php
ob_start();
session_start();
include "../../common.php";
global $con; 

$pid = explode('-', $_GET['id']);

$PrID = $pid[1] ;

/**
pd_so_slider
pd_pck_dets
pd_lp_slider
**/

if($pid[0]=='saleoffer'){
	
	mysqli_query($con, "update products set pd_so_slider= 1 where pd_id='".$PrID."'");
	

	
}elseif($pid[0]=='leader'){
	
	mysqli_query($con, "update products set pd_pck_dets= 1 where pd_id='".$PrID."'");
	
}elseif($pid[0]=='loyal'){
	 
	mysqli_query($con, "update products set pd_lp_slider= 1 where pd_id='".$PrID."'"); 
	
}


?>