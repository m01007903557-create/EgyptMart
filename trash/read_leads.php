<?php session_start();
include "lib/connect.php";
//echo '<pre>';print_r($_COOKIE['loc_id']);exit;//[is_global][loc_id]

if(!empty($_POST["keyword"])) {
$country_str = '';
if(isset($_COOKIE['loc_id'])){
	$country_str = "AND country = '".$_COOKIE['loc_id']."'";
}
//$query ="SELECT DISTINCT pd_title FROM `products` WHERE `pd_title` like '%" . $_POST["keyword"] . "%' ORDER BY pd_title LIMIT 0,3";
//$query ="SELECT
  //  `product_category`.`pc_id`
  //  , `product_category`.`pc_name`
 //   , `product_category`.`pc_sort_name`
 //   , `products`.`pd_title`
//FROM
//    `products`
//    INNER JOIN `product_category` 
 //       ON (`products`.`pd_subcat_id` = `product_category`.`pc_id`) WHERE `pd_title` like '%" . $_POST["keyword"] . "%' ORDER BY pd_title   ";

//$userQuery = "SELECT * FROM user WHERE user_type in (2,3) ".$country_str." AND (fname LIKE '%" . $_POST["keyword"] ."%' OR lname LIKE '%" . $_POST["keyword"]. "%')";

$userQuery = "SELECT business_profile.bnsprof_compname FROM business_profile INNER JOIN user on usr_id = business_profile.bnsprof_uid WHERE user.usr_mp_id in (3,4,5) ".$country_str." AND (business_profile.bnsprof_compname LIKE '%" . $_POST["keyword"] ."%')";
 //$query ="SELECT DISTINCT s.pc_id, `products`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat FROM product_category s, product_category c, product_category m , products WHERE s.pc_parent_id=c.pc_id AND c.pc_parent_id=m.pc_id AND m.pc_parent_id='0' AND m.pc_status='1' AND c.pc_status='1' AND s.pc_status='1' AND products.`pd_subcat_id` = s.`pc_id` AND products.`pd_title` like '%" . $_POST["keyword"] . "%' ORDER BY s.pc_name ";
//$result = $con->query($userQuery);




//$userQuery = "select buy_requirement.br_pd_name from buy_requirement where br_pd_name LIKE '%".$_POST["keyword"]."%' and br_display_status = '1' and br_status = '1' and br_approval_status = '1' group by (br_pd_name) order by br_pd_name asc"; // br_id desc
//webcast works
$userQuery = "select * from buy_requirement br JOIN measurement_unit mu ON br.br_estimate_qty_unit=mu.mu_id JOIN user u ON u.usr_id = br.br_u_id LEFT JOIN business_profile bf ON bf.bnsprof_uid = br.br_u_id LEFT JOIN country c ON c.cn_id = u.country LEFT JOIN smembership_icon_plan sip ON sip.mp_id = u.usr_mp_id WHERE br_pd_name LIKE '%".$_POST["keyword"]."%' and br_display_status = '1' and br_status='1' order by br_pd_name asc";
// echo $kk;die;
$result = $con->query($userQuery);

/*<li onClick="selectCountry('<?php echo $country["subcat"]; ?>');"><?php echo $country["maincat"]; ?> >> <?php echo $country["cat"]; ?>  >>   <span style="color: red" ><?php echo $country["pd_title"]; ?></span>  </li>*/
if(!empty($result)) {
?>
<ul id="country-list" class="countrytwo">
<?php
foreach($result as $users) {
?>
<li onClick="selectCountry('<?php echo $users["br_pd_name"]; ?>');"><a href="https://www.arabyos.com/search.php?rctyp=buy_lead&keywords=
<?php echo str_replace(" ","+",$users["br_pd_name"]); ?>" ><span style="color: red" ><?php echo $users["br_pd_name"]; ?></span> </a></li>
<?php } ?>
</ul>
<?php }
 } ?>