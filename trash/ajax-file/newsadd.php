<?php
include "../common.php";
$uid=$_SESSION['uid_indm'];

$id=trim(addslashes($_GET['id']));
$nws_postdate=trim(addslashes($_GET['nws_postdate']));
$nws_medianm=trim(addslashes($_GET['nws_medianm']));
$nws_mediatyp=trim(addslashes($_GET['nws_mediatyp']));
$nws_headline=trim(addslashes($_GET['nws_headline']));
$nws_covgurl=trim(addslashes($_GET['nws_covgurl']));
$nws_covgdet=trim(addslashes($_GET['nws_covgdet']));
$error=1;

if($nws_covgurl!= "" && !(validate::is_weblink($nws_covgurl)))
{
	$msg="Please Enter a Valid url link";
	$error=0;
}
else if($nws_covgdet== "")
{
	$msg="News / Press Release Detail cannot be blank.";
	$error=0;
}
else if($nws_covgdet== "")
{
	$msg="News / Press Release Detail cannot have more than 4000 characters.";
	$error=0;
}
else
{
	$tmpsmlimgsql=mysqli_query($con, "select * from temp_newsimage where tmpns_uid='".$id."' and tmpns_status='1' ");
	$tmpsmlimgrow =mysqli_fetch_object($tmpsmlimgsql);

	$tmplrgimgsql=mysqli_query($con, "select * from temp_newsimage where tmpns_uid='".$id."' and tmpns_status='2' ");
	$tmplrgimgrow =mysqli_fetch_object($tmplrgimgsql);
	
$sql ="insert into news set 
	   nws_uid='".$id."',
	   nws_medianm ='".$nws_medianm."',
 	   nws_mediatyp ='".$nws_mediatyp."', 
	   nws_headline ='".$nws_headline."', 
	   nws_covgurl ='".$nws_covgurl."',
	   nws_covgdet  = '".$nws_covgdet."',
	   nws_smallimg ='".$tmpsmlimgrow->tmpns_image."', 
	   nws_largeimg ='".$tmplrgimgrow->tmpns_image."',
	   nws_postdate ='".$nws_postdate."' ";
	   mysqli_query($con, $sql);
	   mysqli_query($con, "delete from temp_newsimage where tmpns_uid='".$id."' ");
$error=1;
}

echo $msg."||".$error;
?>