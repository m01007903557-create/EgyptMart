<?php
include "lib/connect.php";
if(!empty($_POST["keyword"])) {
$country_str = '';
if(isset($_COOKIE['loc_id'])){
	$country_str = "AND country = '".$_COOKIE['loc_id']."'";
}
//$query ="SELECT DISTINCT pd_title FROM `products` WHERE `pd_title` like '%" . $_POST["keyword"] . "%' ORDER BY pd_title LIMIT 0,3";
//$query ="SELECT
  //  `product_category_arabyos`.`pc_id`
  //  , `product_category_arabyos`.`pc_name`
 //   , `product_category_arabyos`.`pc_sort_name`
 //   , `products`.`pd_title`
//FROM
//    `products`
//    INNER JOIN `product_category_arabyos` 
 //       ON (`products`.`pd_subcat_id` = `product_category_arabyos`.`pc_id`) WHERE `pd_title` like '%" . $_POST["keyword"] . "%' ORDER BY pd_title   ";

	//$query = "SELECT DISTINCT s.pc_id, `products`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat FROM product_category_arabyos s, product_category_arabyos c, product_category_arabyos m , products, business_profile bp, user u WHERE s.pc_parent_id=c.pc_id AND c.pc_parent_id=m.pc_id AND products.pd_uid = u.usr_id AND bp.bnsprof_uid = u.usr_id AND m.pc_parent_id='0' AND m.pc_status='1' AND c.pc_status='1' AND s.pc_status='1' AND products.`pd_subcat_id` = s.`pc_id` AND bp.bnsprof_status = '1' ".$country_str." AND products.`pd_title` like '%".$_POST["keyword"]."%' ORDER BY s.pc_name ASC";
	$timeStamp = time();
	$query = "SELECT DISTINCT s.pc_id, `p`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat 
	FROM product_category_arabyos s 
				INNER JOIN product_category_arabyos c ON s.pc_parent_id=c.pc_id 
				INNER JOIN product_category_arabyos m ON c.pc_parent_id=m.pc_id 
				INNER JOIN products p ON p.`pd_subcat_id` = s.`pc_id` 
				INNER JOIN user u ON p.pd_uid = u.usr_id 
				INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id 
				LEFT JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id 
				WHERE m.pc_parent_id='0' 
					AND m.pc_status='1' 
					AND c.pc_status='1' 
					AND s.pc_status='1' 
					AND p.`pd_subcat_id` = s.`pc_id` 
					AND bp.bnsprof_status = '1' ".$country_str." AND p.pd_status = '1' 
					AND (p.`pd_title` LIKE '%".$_POST["keyword"]."%' OR bp.`bnsprof_compname` LIKE '%".$_POST["keyword"]."%')
					AND pm.expiry_date > ".$timeStamp." ORDER BY s.pc_name ASC";
	
	// echo $query;die;
	
 //$query ="SELECT DISTINCT s.pc_id, `products`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat FROM product_category_arabyos s, product_category_arabyos c, product_category_arabyos m , products WHERE s.pc_parent_id=c.pc_id AND c.pc_parent_id=m.pc_id AND m.pc_parent_id='0' AND m.pc_status='1' AND c.pc_status='1' AND s.pc_status='1' AND products.`pd_subcat_id` = s.`pc_id` AND products.`pd_title` like '%".$_POST["keyword"]."%' ORDER BY s.pc_name ASC";
$result = $con->query($query);
//print_r($result);die;
if(!empty($result)) {
?>
<ul id="country-list" class="countrytwo">
<?php
foreach($result as $country) {
?>
<li onClick="selectCountry('<?php echo $country["pd_title"]; ?>');"><a href="https://www.arabyos.com/search.php?rctyp=Products&keywords=<?php echo str_replace(" ","+",trim($country["pd_title"])); ?>"  class="search_pro_class"  >
<?php //echo $country["maincat"]; ?> 
<?php echo $country["subcat"]; ?>  >>   <span style="color: red" ><?php echo $country["pd_title"]; ?></span></a></li>
<?php } ?>
</ul>
<?php }
 } ?>