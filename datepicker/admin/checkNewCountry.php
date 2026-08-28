<?php 
ob_start();
session_start(); 
include "../common.php";

$cn_name=addslashes(trim($_POST['cn_name']));
$cn_code=addslashes(trim($_POST['cn_code']));
$cn_currency=addslashes(trim($_POST['cn_currency']));
$cn_ph=addslashes(trim($_POST['cn_ph']));

$sql_nm="select * from country where cn_name like '".$cn_name."'";
$res_nm=mysqli_query($con, $sql_nm);

$sql_cd="select * from country where cn_code like '".$cn_code."'";
$res_cd=mysqli_query($con, $sql_cd);

$sql_cr="select * from country where cn_currency like '".$cn_currency."'";
$res_cr=mysqli_query($con, $sql_cr);

$sql_ph="select * from country where cn_ph like '".$cn_ph."'";
$res_ph=mysqli_query($con, $sql_ph);

if(mysqli_num_rows($res_nm)>0 || mysqli_num_rows($res_cd)>0 || mysqli_num_rows($res_cr)>0 || mysqli_num_rows($res_ph)>0)
{
	echo 1;	
}
else
{
	echo 0;
}
?>