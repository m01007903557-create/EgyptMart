<?php 
include "../common.php";
$pi = $_GET['pi'];

$sql="select * from feature_images where fi_id =".$pi."";
$res=mysqli_query($con, $sql);
$row = mysqli_fetch_object($res);
$prop_id = $row->fi_f_id;
$prop_img = $row->fi_image;
mysqli_query($con, "delete from feature_images where fi_id = '".$pi."'");
unlink('../upload/feature/'.$prop_img);
$sql2="select * from feature_images where fi_f_id =".$prop_id."";
$res2 = mysqli_query($con, $sql2);
$timage_num=mysqli_num_rows($res2);
?>
