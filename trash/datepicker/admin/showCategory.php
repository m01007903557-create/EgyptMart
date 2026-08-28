<?php
include "../common.php";
$q=$_GET["q"];

$ct="<option value='0'> - Select Category - </option>";
if (strlen($q) > 0)
{
	$sql="select * from product_category where pc_parent_id=".$q." order by pc_name";  
	$result=mysqli_query($con, $sql);
	
	while($row=mysqli_fetch_object($result))
	{
		$ct.="<option value='".$row->pc_id."'>".stripslashes($row->pc_name)."</option>";
  	}
}
echo $ct;
?>