<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "../common.php";

$pd_hot=trim(addslashes($_GET['pd_hot']));
$pd_brand_name=trim(addslashes($_GET['pd_brand']));
$pd_payment=trim(addslashes($_GET['pd_payment']));
$pd_pod=trim(addslashes($_GET['pd_pod']));
$pd_pn_capct=trim(addslashes($_GET['pd_pn_capct']));
$pd_dlv_time=trim(addslashes($_GET['pd_dlv_time']));
$pd_pck_dets=trim(addslashes($_GET['pd_pck_dets']));

if(strlen($pd_pck_dets)>2000)
{
	 $msg="Packaging Details cannot have more than 2000 characters";
	 $e=0;
}
else
{
	$sql ="update products
		set
			pd_hot='".$pd_hot."',
			pd_payment ='".$pd_payment."',
			brand_name = '".$pd_brand_name."',
			pd_fob_price ='".$pd_fob_price."',
			pd_currency ='".$pd_currency."',
			pd_min_order_qty  = '".$pd_min_order_qty."',
			pd_unit ='".$pd_unit."',
			pd_pod ='".$pd_pod."',
			pd_pn_capct ='".$pd_pn_capct."',
			pd_dlv_time ='".$pd_dlv_time."',
			pd_pck_dets ='".$pd_pck_dets."'
		where
			pd_id ='".$_GET['pid']."'";
			
mysqli_query($con, $sql);
$e=1;
}
echo $msg."||".$e;
?>