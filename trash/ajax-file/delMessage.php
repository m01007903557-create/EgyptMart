<?php
ob_start();
session_start();
include "../common.php";

$msg_id=$_POST['id'];

$sql="select * from message where msg_id='".$msg_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

$flag=0;

if($row->msg_to==$_SESSION['uid_indm'])
{
	$fld='to';
}
else if($row->msg_from==$_SESSION['uid_indm'])
{
	$fld='from';
}

if($fld=='to')
{
	$sql_del="update message set msg_to_status='0' where msg_id='".$row->msg_id."'";
	mysqli_query($con, $sql_del);
	$flag=1;
}
else if($fld=='from')
{
    $sql_del="update message set msg_from_status='0' where msg_id='".$row->msg_id."'";
    mysqli_query($con, $sql_del);
	$flag=1;
}
echo $flag;
//echo $msg_id." - ".$row->msg_to." - ".$row->msg_from." - ".$_SESSION['uid_indm'];
?>
