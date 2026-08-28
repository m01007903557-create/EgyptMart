<?php
ini_set('display_startup_errors',1);
ini_set('display_errors',1);
error_reporting(-1);
ob_start();
session_start();
//$uid=$_SESSION['uid_indm'];
include "../common.php";
//$pd_id=trim(addslashes($_GET['id']));
//$pd_id=substr($_GET['id'],4);
$pd_id=$_GET['id'];
if (substr($_GET['keywords'], 0, 1) == '"') {

    $keywords = substr(substr(trim($_GET['keywords']), 1), 0, strlen((substr(trim($_GET['keywords']), 1))) - 1);
} else {
    $keywords = trim($_GET['keywords']);
}
if ($pd_id) {
	//$sql = "SELECT * FROM `products` WHERE md5(pd_id) = ".$pd_id;
	//$sql="select bnsprof_id from business_profile,products where bnsprof_uid = pd_uid and pd_id ='".$pd_id."'";
	//$sql = "select smembership_plan.mst_icon as sponsericon , plan_member_id.* , smembership_icon_plan.mst_icon as producticon,smembership_icon_plan.mst_name as pplan from smembership_plan,plan_member_id , smembership_icon_plan where smembership_icon_plan.mp_id =plan_member_id.p_id and smembership_plan.mp_id =plan_member_id.p_id  and plan_member_id.b_id = " .$pd_id;
	//$sql="select * from business_profile where bnsprof_id = 5466";
	$sql = "select * from country where cn_status = '1' and cn_name LIKE '$pd_id%' order by cn_id asc";
}
else {
	//$sql = "select * from products,measurement_unit,country,business_profile,plan_member_id where mu_id=pd_unit and (bnsprof_compname LIKE '%".$keywords."%') and business_profile.bnsprof_uid = products.pd_uid and plan_member_id.b_id =business_profile.bnsprof_id  and pd_currency=cn_id ".$sql_pd_ck." group by products.pd_uid";
	$sql = "select business_profile.bnsprof_state from products INNER JOIN business_profile ON business_profile.bnsprof_uid = products.pd_uid  where (bnsprof_compname LIKE '%".$keywords."%') and ((pd_preferred_buyer_location =  'domestic' AND products.pd_currency =  '".$location_geo_country[0]."') OR (pd_preferred_buyer_location =  'any' AND products.pd_currency =  '".$location_geo_country[0]."') OR (pd_preferred_buyer_location =  'my_city' AND products.pd_currency =  '".$location_geo_country[0]."' )) group by products.pd_uid HAVING business_profile.bnsprof_state >0";
}
$result = mysql_query($sql);
$country_buy=array();
while($row = mysql_fetch_array($result))
{
   print_r($row);
}
var_dump($location_geo_country[0]);
?>