<?php
/*include "lib/connect.php";
$keywords=$_REQUEST['searchkey'];
if(isset($keywords)){
	$query = "select * from `product_category` where `pc_parent_id`='131' AND `pc_name` LIKE '%".$keywords."%' ORDER BY `pc_name` ASC";
}
else{
	$query = "select * from `product_category` where `pc_parent_id`='131' ORDER BY `pc_name` ASC";
}
$result = $con->query($query);
if($result->num_rows>0) {
	$html= '<ul id="country-list1" style="width: 100% !important;position: static !important;">';
	foreach($result as $res){
		$val="'".$res['pc_name']."'";
		$id="'".md5($res['pc_id'])."'";
		$html.='<li onClick="selectService('.$id.','.trim($val).')" style="border-bottom: #F0F0F0 1px solid;padding: 10px;"><span style="color: red;font-weight:600;">'.$res['pc_name'].'</span></li>';
	}
	$html.='</ul>';
	echo $html;
}
*/
?>
<?php
include "lib/connect.php";
if(!empty($_REQUEST["searchkey"])) {
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

	//$query = "SELECT DISTINCT s.pc_id, `products`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat FROM product_category s, product_category c, product_category m , products, business_profile bp, user u WHERE s.pc_parent_id=c.pc_id AND c.pc_parent_id=m.pc_id AND products.pd_uid = u.usr_id AND bp.bnsprof_uid = u.usr_id AND m.pc_parent_id='0' AND m.pc_status='1' AND c.pc_status='1' AND s.pc_status='1' AND products.`pd_subcat_id` = s.`pc_id` AND bp.bnsprof_status = '1' ".$country_str." AND products.`pd_title` like '%".$_POST["keyword"]."%' ORDER BY s.pc_name ASC";
	$timeStamp = time();
	$query = "SELECT DISTINCT s.pc_id, `p`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat FROM product_category s INNER JOIN product_category c ON s.pc_parent_id=c.pc_id INNER JOIN product_category m ON c.pc_parent_id=m.pc_id INNER JOIN products p ON p.`pd_subcat_id` = s.`pc_id` INNER JOIN user u ON p.pd_uid = u.usr_id INNER JOIN business_profile bp ON bp.bnsprof_uid = u.usr_id LEFT JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id WHERE m.pc_parent_id='0' AND m.pc_status='1' AND c.pc_status='1' AND s.pc_status='1' AND p.`pd_subcat_id` = s.`pc_id` AND bp.bnsprof_status = '1' ".$country_str." AND p.pd_status = '1' AND p.`pd_title` LIKE '%".$_REQUEST["searchkey"]."%' AND pm.expiry_date > ".$timeStamp." AND `c`.`pc_parent_id`='131' ORDER BY s.pc_name ASC";
	
	//echo $query;die;
	
 //$query ="SELECT DISTINCT s.pc_id, `products`.`pd_title`, s.pc_name AS subcat, s.pc_sort_name, c.pc_name AS cat, m.pc_name AS maincat FROM product_category s, product_category c, product_category m , products WHERE s.pc_parent_id=c.pc_id AND c.pc_parent_id=m.pc_id AND m.pc_parent_id='0' AND m.pc_status='1' AND c.pc_status='1' AND s.pc_status='1' AND products.`pd_subcat_id` = s.`pc_id` AND products.`pd_title` like '%".$_POST["keyword"]."%' ORDER BY s.pc_name ASC";
$result = $con->query($query);
//print_r($result);die;
if(!empty($result)) {
?>
<ul id="country-list" class="countrytwo" style="top:0;left: initial;">
<?php
foreach($result as $country) {
?>
<li onClick="selectCountry('<?php echo $country["pd_title"]; ?>');"><a href="https://arabyos.com/search.php?rctyp=Products&keywords=<?php echo str_replace(" ","+",trim($country["pd_title"])); ?>"  class="search_pro_class" >
<?php //echo $country["maincat"]; ?> 
<?php echo $country["subcat"]; ?>  >>   <span style="color: red" ><?php echo $country["pd_title"]; ?></span></a></li>
<?php } ?>
</ul>
<?php }
 } ?>