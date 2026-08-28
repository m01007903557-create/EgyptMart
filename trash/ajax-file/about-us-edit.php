<?php
ob_start();
session_start(); 
$uid=$_SESSION['uid_indm'];
include "../common.php";

$id=trim(addslashes($_GET['id']));
$abtusheading=trim(addslashes($_GET['abtusheading']));
$abtusdesc=trim(addslashes($_GET['abtusdesc']));
$totaldesc=strlen($abtusdesc);

if($abtusheading=="")
{
$msg="Please check that Profile Heading cannot be blank";	
}
else if($abtusdesc=="")
{
$msg="Please check that Profile Heading cannot be blank";	
}
else if($totaldesc>4000)
{
$msg="Please check that Profile Description cannot have more than 4000 characters.";	
}
else
{
$sql ="update about_us set abtus_ph_id='".$abtusheading."',abtus_desc ='".$abtusdesc."' where abtus_id ='".$id."' ";
mysqli_query($con, $sql);
$msg1="Content Saved Successfully!";
}
echo $msg."||".$msg1;
?>