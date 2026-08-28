<?php
include "../common.php";

$tma_id=$_POST['id'];

$sql_tma="select * from temp_msg_attachment where tma_id='".$tma_id."'";
$res_tma=mysqli_query($con, $sql_tma);
$row_tma=mysqli_fetch_object($res_tma);

$path="../upload/message_attachment/".$row_tma->tma_file;
if(is_file($path))
{
	unlink($path);
}
mysqli_query($con, "delete from temp_msg_attachment where tma_id='".$row_tma->tma_id."'");

?>