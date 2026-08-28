<?php
include "../common.php";

$cn_id=addslashes(trim($_POST['cn_id']));
$cn_name=addslashes(trim($_POST['cn_name']));
$cn_currency=addslashes(trim($_POST['cn_currency']));
$cn_ph=addslashes(trim($_POST['cn_ph']));

$sql="update country
	set
		cn_name='".$cn_name."',
		cn_currency='".$cn_currency."',
		cn_ph='".$cn_ph."'
	where
		cn_id='".$cn_id."'";
mysqli_query($con, $sql);
?>