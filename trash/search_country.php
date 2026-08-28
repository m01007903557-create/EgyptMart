<?php
extract($_REQUEST);
include 'lib/connect.php';

$get_country = "SELECT * FROM `country` WHERE `cn_name` = '$cname'";
$rescn = $con->query($get_country);
$count =$rescn->num_rows;
if($count > 0){
	$row = $rescn->fetch_assoc();
	echo "<center><h4>Search results</h4><br/>
	<a href='#' onclick='setCountryLocation(".$row['cn_id'].");'>
	<img src='images/country_flag/".$row['cn_flag']."'/> &nbsp;".$row['cn_name']."</a><br/><br/></center>";
}
else{
	echo "<center><h4>Search results</h4><br/>Not found in the country list
	<br/><br/></center>";
}

?>