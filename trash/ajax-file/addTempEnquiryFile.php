<?php
include "../common.php";

$usr_id=$_POST['id'];


if(!empty($_FILES))
{
	
	$newFileName=rand(100,999).$usr_id.trim(addslashes($_FILES['Filedata']['name']));
	
	$ds = move_uploaded_file($_FILES["Filedata"]["tmp_name"], "../upload/message_attachment/".$newFileName) or die('error');	
		
	$sql="insert into temp_msg_attachment
		set
			tma_usr_id='".$usr_id."',
			tma_file='".$newFileName."',
			tma_upload_date=now()";
	mysqli_query($con, $sql);
}
?>