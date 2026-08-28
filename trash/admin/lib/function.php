<?php
	function check_user_login()
	{
		global $con;
		$sql="select id from admin_user where username='".$_SESSION['ad_username_indm']."'";
		$res=mysqli_num_rows(mysqli_query($con, $sql));
		if($res==0)
		{
			header("location:index.php");
		}	
	}	
function getAdminUserId() {
global $con;
		$sql="select id from admin_user where username='".$_SESSION['ad_username_indm']."'";
		$res=mysqli_fetch_object(mysqli_query($con, $sql));
		return $res->id;
}
function init_site_settings() { //webxtor 2021Jan25: making only one request to settings //
	//global $site_settings;
	global $con;
	$result = @mysqli_query($con, "select st_value, st_field, st_id from site_settings_arabyos where st_status=1");
	if (!$result) {
	  printf("Query failed: %s\n", mysqli_error($con));
	  exit;
	}      
	while($row = $result->fetch_array()) {
		$key = ($row['st_field'] == 'website-title') ? $row['st_field'] : $row['st_id'];
		$site_settings[$key]  =$row['st_value'];
	}
	return $site_settings;
}
function getWebSiteName()	//pranab
{
	global $con;
	$sql="select st_value from site_settings_arabyos where st_id=4 and st_status=1";
	$query=mysqli_query($con, $sql);
	if($tit=mysqli_fetch_object($query))
	{
		return ucfirst($tit->st_value);
	}
	
	return "No Title";
}
function getWebSiteTitle()	//pranab
{
	global $con;
	$sql="select st_value from site_settings_arabyos where st_id=1 and st_status=1";
	$query=mysqli_query($con, $sql);
	if($tit=mysqli_fetch_object($query))
	{
		return ucfirst($tit->st_value);
	}
	
	return "No Title";
}
function get_time_difference($time1, $time2) //pranab
{
	$time1 = strtotime("1/1/1980 $time1");
	$time2 = strtotime("1/1/1980 $time2");										
	if ($time2 < $time1)
	{
		$time2 = $time2 + 86400;
	}										
	return ($time2 - $time1) / 60;	//return minute
}



function get_days_in_month($month, $year)
{
   return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year %400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

function getSiteLogo()	//pranab
{
	global $con;
	$sql="select st_value from site_settings_arabyos where st_id='5' and st_status=1";
	$query=mysqli_query($con, $sql);
	if($tit=mysqli_fetch_object($query))
	{
		return $tit->st_value;
	}
	
	return "No Title";
}
function get_page_settings($x)
{
	global $con;
	$sql = "select * from site_settings_arabyos where st_id='".$x."'";
	$qry = mysqli_query($con, $sql);
	$arr = mysqli_fetch_array( $qry);	
	return stripslashes($arr['st_value']);
}
function getEmailVerificationStatus()
{
	global $con;
	$sql = "select * from site_settings_arabyos where st_field='email-verification'";
	$qry = mysqli_query($con, $sql);
	$arr = mysqli_fetch_array( $qry);	
	return $arr['st_value'];
}
function getUserInfo($id,$fld)	//pranab
{
	global $con;
	$sql="select ".$fld." from user where usr_id='".$id."'";
	$res=mysqli_query($con, $sql);
	$row=mysqli_fetch_array( $res);
	return $row[0];
}
function getCompanyName($uid)	//pranab
{
	global $con;
	$sql="select bnsprof_compname from business_profile where bnsprof_uid='".$uid."'";
	$res=mysqli_query($con, $sql);
	$row=mysqli_fetch_array( $res);
	return $row[0];
}
function getRealIpAddr()//pranab
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
function getCountryCode()//pranab
{
	$xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=".getRealIpAddr());
	return $xml->geoplugin_countryCode;

	/*echo "<pre>" ;
	foreach ($xml as $key => $value)
	{
    	echo $key , "= " , $value ,  " \n" ;
	}*/
}
function getCurrency($curr)
{
	global $con;
	$sql="select cn_currency from country where cn_id='".$curr."'";
	$res=mysqli_query($con, $sql);
	$row=mysqli_fetch_object($res);

	return $row->cn_currency;
}
function measurement_unit($id)	//pranab
{
	global $con;
	$sql="select * from measurement_unit where mu_id='".$id."'";
	$res=mysqli_query($con, $sql);
	$row=mysqli_fetch_object($res);
	return $row->mu_name;
}
?>
