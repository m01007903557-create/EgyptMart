<?php session_start();
include "lib/connect.php";
//echo '<pre>';print_r($_COOKIE['loc_id']);exit;//[is_global][loc_id]

if(!empty($_POST["keyword"])) {
$country_str = '';
if(isset($_COOKIE['loc_id'])){
	$country_str = "AND country = '".$_COOKIE['loc_id']."'";
}

$timeStamp = time();
$userQuery = "SELECT bp.bnsprof_compname FROM business_profile bp INNER JOIN user u on u.usr_id = bp.bnsprof_uid JOIN products p ON (p.pd_uid = u.usr_id AND p.pd_status = '1' ) LEFT JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id WHERE u.usr_mp_id in (3,4,5, 15) ".$country_str."  AND bp.bnsprof_status = '1' AND bp.bnsprof_compname LIKE '%" . $_POST["keyword"] ."%' AND pm.expiry_date > ".$timeStamp." GROUP BY u.usr_id HAVING COUNT(p.pd_id) > 0 ORDER BY bp.bnsprof_compname ASC";
//echo $userQuery;
$result = $con->query($userQuery);

if(!empty($result)) {
?>
<ul id="country-list" class="countrytwo">
<?php
foreach($result as $users) {
?>
<li onClick="selectCountry('<?php echo $users["bnsprof_compname"]; ?>');"><a href="http://arabyos.com/search.php?rctyp=Suppliers&keywords=<?php echo str_replace(" ","+",trim($users["bnsprof_compname"])); ?>"><span style="color: red" ><?php echo $users["bnsprof_compname"]; ?></span></a></li>
<?php } ?>
</ul>
<?php }
 } ?>
