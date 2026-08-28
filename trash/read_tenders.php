<?php 
//echo $_POST["keyword"];die;

//echo $_COOKIE['loc_id'];die;
session_start();
include "lib/connect.php";
//echo '<pre>';print_r($_COOKIE['loc_id']);exit;//[is_global][loc_id]

if(!empty($_POST["keyword"])) {
$country_str = '';
if(isset($_COOKIE['loc_id'])){
	$country_str = "AND country = '".$_COOKIE['loc_id']."'";
}

$timeStamp = date('Y-m-d',time());
$userQuery = "SELECT td.tnd_id,td.tnd_heading FROM tender td INNER JOIN user u ON td.tnd_usr_id = u.usr_id INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id WHERE bp.bnsprof_status = '1' AND td.tnd_heading LIKE '%".$_POST["keyword"]."%' AND td.tnd_status = '1' ".$country_str." AND tnd_due_date > '".$timeStamp."' GROUP BY td.tnd_heading ORDER BY td.tnd_heading ASC"; // br_id desc
 
$result = $con->query($userQuery);

$userQuery1 = "SELECT a.auc_id,a.auc_heading FROM auction a INNER JOIN user u ON a.auc_usr_id = u.usr_id INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id WHERE bp.bnsprof_status = '1' AND a.auc_heading LIKE '%".$_POST["keyword"]."%' AND a.auc_status = '1' ".$country_str." AND auc_due_date > '".$timeStamp."' GROUP BY a.auc_heading ORDER BY a.auc_heading ASC"; // br_id desc
 
$result1 = $con->query($userQuery1);

if(!empty($result) || !empty($result1)) {

?>
<ul id="country-list" class="countrytwo">
<?php
if(!empty($result)) {
foreach($result as $users) {
?>
<li onClick="selectCountry('<?php echo $users["tnd_heading"]; ?>');"><a href="https://arabyos.com/search.php?rctyp=tender&keywords=<?php echo str_replace(" ","+",trim($users["tnd_heading"])); ?>" ><span style="color: red" ><?php echo $users["tnd_heading"]; ?></span></a></li>
<?php } 
}
?>
<?php
if(!empty($result1)) {
foreach($result1 as $users) {
?>
<li onClick="selectCountry('<?php echo $users["auc_heading"]; ?>');"><a href="https://arabyos.com/search.php?rctyp=tender&keywords=<?php echo str_replace(" ","+",trim($users["auc_heading"])); ?>"  ><span style="color: red" ><?php echo $users["auc_heading"]; ?></span></a></li>
<?php } }
?>
</ul>
<?php }else{
	
	echo "<ul id='country-list' class='countrytwo'><li><span style='color: red'>No Relevent Due Date Tender</span></li></ul>";
	
	}
 } ?>