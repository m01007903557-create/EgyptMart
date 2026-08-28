<?php

include "../common.php";

$id=$_POST['id'];

$sql="select * from product_category_arabyos where pc_status='1' and pc_parent_id='".$id."' order by pc_name";
$res=mysqli_query($con, $sql);
$disp='';
while($row=mysqli_fetch_object($res))
{
	$disp.='<option value="'.$row->pc_id.'">'.ucfirst($row->pc_name).'</option>';
}

$sql_c="select * from product_category_arabyos where pc_status='1' and pc_id='".$id."'";
$res_c=mysqli_query($con, $sql_c);
$row_c=mysqli_fetch_object($res_c);
echo $disp.'|'.ucfirst($row_c->pc_name);
?>