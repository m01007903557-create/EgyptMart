<?php
include "../common.php";
$scat=$_GET["scat"];

//lookup all hints from array if length of q>0
$ct="<option value=''>-Select-</option>";
if (strlen($scat) > 0)
{
	$sql="select * from additional_field where af_pc_id=".$scat." and af_type in('select','radio','checkbox') order by af_label";  

	$result=mysqli_query($con, $sql);
	
	while($row=mysqli_fetch_object($result))
	{
  		$ct.="<option value=".$row->af_id.">".$row->af_label."</option>";
  	}
}

// Set output to "no suggestion" if no hint were found
// or to the correct values


//output the response
echo $ct;
?>
