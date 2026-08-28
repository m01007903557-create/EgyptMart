<?php
echo "OK";
/*Get user ip address*/
$ip_address=$_SERVER['REMOTE_ADDR'];

/*Get user ip address details with geoplugin.net*/
$geopluginURL='http://www.geoplugin.net/php.gp?ip='.$ip_address;
$addrDetailsArr = unserialize(file_get_contents($geopluginURL)); 

/*Get City name by return array*/
$city = $addrDetailsArr['geoplugin_city']; 

/*Get Country name by return array*/
$country = $addrDetailsArr['geoplugin_countryName'];

/*Comment out these line to see all the posible details*/
/*echo '<pre>';
print_r($addrDetailsArr);
die();*/

if(!$city){
   $city='Not Define';
}if(!$country){
   $country='Not Define';
}
echo '<strong>IP Address</strong>:- '.$ip_address.'<br/>';
echo '<strong>City</strong>:- '.$city.'<br/>';
echo '<strong>Country</strong>:- '.$country.'<br/>';
?>
***********************************************************
<br />
<?php
function geoCheckIP($ip)
{
	echo $ip;
	echo "OK";
    //check, if the provided ip is valid
    if( !filter_var( $ip, FILTER_VALIDATE_IP ) )
    {
        throw new InvalidArgumentException("IP is not valid");
    }

    //contact ip-server
    $response=@file_get_contents( 'http://www.netip.de/search?query='.$ip );

    if( empty( $response ) )
    {
        throw new InvalidArgumentException( "Error contacting Geo-IP-Server" );
    }

    //Array containing all regex-patterns necessary to extract ip-geoinfo from page
    $patterns=array();
    $patterns["domain"] = '#Domain: (.*?) #i';
    $patterns["country"] = '#Country: (.*?) #i';
    $patterns["state"] = '#State/Region: (.*?)<br#i';
    $patterns["town"] = '#City: (.*?)<br#i';

    //Array where results will be stored
    $ipInfo=array();

    //check response from ipserver for above patterns
    foreach( $patterns as $key => $pattern )
    {
        //store the result in array

        $ipInfo[$key] = preg_match( $pattern, $response, $value ) && !empty( $value[1] ) ? $value[1] : 'not found';
    }
    
    /*I've included the substr function for Country to exclude the abbreviation (UK, US, etc..)
    To use the country abbreviation, simply modify the substr statement to:
    substr($ipInfo["country"], 0, 3)
    */
    $ipdata = $ipInfo["town"]. ", ".$ipInfo["state"].", ".substr($ipInfo["country"], 4);

    return $ipdata." OK";
}
echo geoCheckIP($ip_address);

?>