<?php 
ob_start();
session_start(); 
include "../common.php";

$cn_id=addslashes(trim($_POST['cn_id']));
$cn_name=addslashes(trim($_POST['cn_name']));
$cn_currency=addslashes(trim($_POST['cn_currency']));
$cn_ph=addslashes(trim($_POST['cn_ph']));

$sql_nm="select * from country where cn_name like '".$cn_name."' and cn_id!='".$cn_id."'";
$res_nm=mysqli_query($con, $sql_nm);

$sql_cr="select * from country where cn_currency like '".$cn_currency."' and cn_id!='".$cn_id."'";
$res_cr=mysqli_query($con, $sql_cr);

$sql_ph="select * from country where cn_ph like '".$cn_ph."' and cn_id!='".$cn_id."'";
$res_ph=mysqli_query($con, $sql_ph);

if(mysqli_num_rows($res_nm)>0 || mysqli_num_rows($res_cr)>0 || mysqli_num_rows($res_ph)>0)
{
	echo 1;	
}
else
{
	echo 0;
}
?>