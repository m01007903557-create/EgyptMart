<?php 


function social_login_settings($x)
{
	global $con;
	$sql = "select * from social_media_login_info where smli_id='".$x."'";
	$qry = mysqli_query($con, $sql);
	$arr = mysqli_fetch_array( $qry);	
	return stripslashes($arr['smli_value']);
}
?>