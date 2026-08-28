<?php
include "lib/connect.php";
if(!empty($_POST["keyword"])) {
$city_str = '';
if(isset($_COOKIE['loc_id'])){
	$city_str = " AND ct_cn_id = '".$_COOKIE['loc_id']."'";
}
$query ="SELECT ct_id, ct_name FROM city WHERE  ct_name LIKE '" . $_POST["keyword"] . "%'".$city_str." ORDER BY ct_name ";
$result = $con->query($query);

if(!empty($result)) {
?>
<ul id="state-list" class="countrytwo">
<?php
foreach($result as $states) {
?>
<li onClick="selectCity('<?php echo $states["ct_name"]; ?>');"><span style="color: red" ><?php echo $states["ct_name"]; ?></span> </li>
<?php } ?>
</ul>
<?php }
 } ?>