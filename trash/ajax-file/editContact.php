<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "../common.php";

$division=trim(addslashes($_GET['division']));
$prefix=trim(addslashes($_GET['prefix']));
$fname=trim(addslashes($_GET['fname']));
$lname=trim(addslashes($_GET['lname']));
$address=trim(addslashes($_GET['address']));
$address1=trim(addslashes($_GET['address1']));
$phareacode=trim(addslashes($_GET['phareacode']));
$telephone=trim(addslashes($_GET['telephone']));
$mobile=trim(addslashes($_GET['mobile']));
$faxareacode=trim(addslashes($_GET['faxareacode']));
$fax=trim(addslashes($_GET['fax']));
$email=trim(addslashes($_GET['email']));

$sql ="update company_contact set comp_cnt_division='".$division."', comp_cnt_prefix ='".$prefix."', comp_cnt_fname ='".$fname."', comp_cnt_lname ='".$lname."', comp_cnt_address  = '".$address."', comp_cnt_address1 ='".$address1."', comp_cnt_phareacode ='".$phareacode."',comp_cnt_telephone ='".$telephone."', comp_cnt_mobile ='".$mobile."', comp_cnt_faxareacode ='".$faxareacode."', comp_cnt_fax ='".$fax."', comp_cnt_email ='".$email."' where comp_cnt_id='".$_GET['id']."'";
mysqli_query($con, $sql);
?>