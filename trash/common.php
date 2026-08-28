<?php
date_default_timezone_set("Asia/Kolkata"); 
//error_reporting(0);
error_reporting(E_ERROR | E_PARSE);
ini_set("display_errors", 1);
ob_start();
session_start();

if (preg_match('#^/admin#', $_SERVER['REQUEST_URI'])) { //webxtor: 2021Feb06
	define('BASEDIR', $_SERVER['DOCUMENT_ROOT'] . '/admin/');
} else {
	define('BASEDIR', $_SERVER['DOCUMENT_ROOT'] . '/');
}
include str_replace('/admin', '', BASEDIR) . 'lib/connect.php';//webxtor: 2021Feb06 adding BASEDIR everywhere - admin don't work
include BASEDIR . 'lib/function.php';
include str_replace('/admin', '', BASEDIR) . 'lib/website_function.php';
include str_replace('/admin', '', BASEDIR) . 'lib/pagination.php';
include BASEDIR . 'lib/validation.php';
include BASEDIR . 'lib/simpleimage.php';

/**/error_reporting(1);
error_reporting(E_ERROR | E_PARSE);
ini_set("display_errors", 1);/**/ // webxtor: 2021Feb06 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**/require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';/**/

//$memcached = new Memcached();//webxtor 2021Jan25
//$memcached->addServer('127.0.0.1', 20159);

$memcached_key = 'site_settings_arabyos';
$site_settings = '';//$memcached->get($memcached_key);
if (!$site_settings) {
	$site_settings = init_site_settings();
	//$memcached->set($memcached_key, $site_settings, 60*60*10 );
}

$path=$_SERVER['SCRIPT_NAME'];
$pos=strrpos($path,'/');
$file=substr($path,($pos+1));

//$file = strstr($file, '.', true);
$dotpos=strrpos($file,'.');
$file=substr($file,0,($dotpos));

//$location_geo=array();
/*$location_geo_country=getCountryCode();// Disabled by webxtor on Jan 25 2021 as no longer used and used code below
$location_geo_country = json_decode(json_encode($location_geo_country),true);
$location=array();
//error_reporting(1);
//$location=getLocationInfoByIp();

//$codeOfCountry = $location['country'];
$codeOfCountry = $location_geo_country[0];
  //print_r($location_geo_country);
// exit;

/*if(!isset($_COOKIE['loc_id']) && !isset($_COOKIE['is_global']))
{
  $sql_cnLoc="select * from country where cn_code ='".$codeOfCountry."'";

$res_cnLoc=mysql_query($sql_cnLoc);
if(mysql_num_rows($res_cnLoc))
{
	$row=mysql_fetch_object($res_cnLoc);
	if ($row->cn_id == 243) { // webxtor
		setcookie("is_global",1, time()+3600, '/');
	} else {
   		setcookie("loc_id",$row->cn_id, time()+3600);
	}
}

}*/

$base_url="https://".$_SERVER['SERVER_NAME'].dirname($_SERVER["REQUEST_URI"].'?').'/';
$baseurl = str_replace("/company/", "", $base_url);
define("BASE_URL", $baseurl);

/*function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE) {
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
    $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "AF" => "Africa",
        "AN" => "Antarctica",
        "AS" => "Asia",
        "EU" => "Europe",
        "OC" => "Australia (Oceania)",
        "NA" => "North America",
        "SA" => "South America"
    );
    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
        $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
                case "location":
                    $output = array(
                        "city"           => @$ipdat->geoplugin_city,
                        "state"          => @$ipdat->geoplugin_regionName,
                        "country"        => @$ipdat->geoplugin_countryName,
                        "country_code"   => @$ipdat->geoplugin_countryCode,
                        "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                        "continent_code" => @$ipdat->geoplugin_continentCode
                    );
                    break;
                case "address":
                    $address = array($ipdat->geoplugin_countryName);
                    if (@strlen($ipdat->geoplugin_regionName) >= 1)
                        $address[] = $ipdat->geoplugin_regionName;
                    if (@strlen($ipdat->geoplugin_city) >= 1)
                        $address[] = $ipdat->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ipdat->geoplugin_city;
                    break;
                case "state":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ipdat->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ipdat->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ipdat->geoplugin_countryCode;
                    break;
            }
        }
    }
    return $output;
}*/

//added by webcast
function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);    
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //webxtor 26 Jan
    curl_setopt($ch, CURLOPT_TIMEOUT_MS, 300); //webxtor 26 Jan 

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
}
//end by webcast
function ip_info () {
    $ip = $_SERVER["REMOTE_ADDR"];
    $ipdat = @json_decode(file_get_contents_curl("http://www.geoplugin.net/json.gp?ip=" . $ip));//echo '<!--'; print_r($ipdat); echo '-->';//webxtor 2021Jan25 replaced: https://office.blogdesire.com/track.php?ip=
        if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            $cou_name = $ipdat->geoplugin_countryCode;
        }
    return $cou_name;
}
if (!isset($_SESSION['user_country'])) { //webxtor Jan262021
	$_SESSION['user_country'] = ip_info ();  //webxtor
}
$user_country = $_SESSION['user_country'];//ip_info ();  //webxtor
//echo '<!--'.$user_country;
if(!isset($_COOKIE['loc_id']) && !isset($_COOKIE['is_global']) && $user_country)
{
$sql="select * from country where cn_status = '1' and cn_name LIKE '$user_country%' order by cn_id asc";
    $result = mysql_query($sql);
    if(mysql_num_rows($result))
    {//echo '-f-'.$row->cn_id.'-';
        $row=mysql_fetch_object($result);
        if ($row->cn_id == 243) { // webxtor
            setcookie("is_global",1, time()+3600, '/');
        } else {
            setcookie("loc_id",$row->cn_id, time()+3600);
        }
    }
}
//echo '-->';

function sendSMTPMail($email, $subject, $message,$headers='')
    {

        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isMAIL();
        $mail->SMTPDebug = 0;
        $mail->Mailer = "smtp";
        $mail->Host = 'smtp.hostinger.com';//'mail.egyptmart.online';//"secureus187.sgcpanel.com";
        $mail->Port = 587;//"465"; // 8025, 587 and 25 can also be used. Use Port 465 for SSL.
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Username = "info@arabyos.com";
        $mail->Password = "info@arabyos.com";  

        $mail->addAddress($email);
        $mail->setFrom('info@arabyos.com', 'ARABYOS');
        $mail->addReplyTo($email);
        //$mail->Subject = $subject;
        $mail->Subject  =  '=?UTF-8?B?'.base64_encode($subject).'?=';
        $mail->msgHTML($message);
        if(!$mail->Send()) {
            return false;
        } else {
        return true;
        }
    }

?>
