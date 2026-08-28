<?php
include "../common.php";
$name=$_REQUEST['imgname'];
$sql_proImg="select * from products where  pd_id = ".$_REQUEST['id'];
$res_row=mysqli_query($con, $sql_proImg);
$row_proImg=mysqli_fetch_object($res_row);
//print_r($row_proImg);
$img=$row_proImg->pd_imagelogo;
$array = explode(',', $img);

if (($key = array_search($name, $array)) !== false) {
    unset($array[$key]);
    $pathLrg="upload/myproduct/".$name;
    unlink($pathLrg);
}

$img=implode(',',$array);
$sql="update products set pd_imagelogo='".$img."'
				where pd_id='".$_REQUEST['id']."'";
			  mysqli_query($con, $sql);

			  echo $sql;
 ?>