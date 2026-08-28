<?php
include "../common.php";

$bac_id=$_POST['id'];
$cat_id=$_POST['cat_id'];
$category_get_sql="select * from product_category where pc_id='".$cat_id."'";
$query_key = mysqli_query($con,$category_get_sql);
$row_key=mysqli_fetch_object($query_key);
$key_cat_name = $row_key->pc_name;

$sql_pro_buy="delete from product_buy where pdby_title='".$key_cat_name."' and pdby_uid='".$_SESSION['uid_indm']."'";
mysqli_query($con, $sql_pro_buy);

$sql="delete from buylead_alert_category where bac_id='".$bac_id."' and bac_usr_id='".$_SESSION['uid_indm']."'";
mysqli_query($con, $sql);
?>