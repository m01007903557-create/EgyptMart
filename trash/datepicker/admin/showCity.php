<?php
include "../common.php";

$ct_state=$_GET['id'];

$ct="<option value=''> --- Select City --- </option>";
if (strlen($ct_state) > 0)
{
	$sql="select * from city where ct_state='".$ct_state."' and ct_status='1'";  
	$result=mysqli_query($con, $sql);
	
	while($row=mysqli_fetch_object($result))
	{
		$ct.="<option value='".$row->ct_id."'>".stripslashes($row->ct_name)."</option>";
  	}
}
echo $ct;
?>