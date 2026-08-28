<?php
include '../common.php';

$msg = $_POST['msg'];
$msg_id = explode(',',$msg);

foreach($msg_id as $mid)
{
	mysqli_query($con, "update message set msg_to_status = '0' where msg_id = '".$mid."'");	
}
?>