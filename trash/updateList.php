<?php 
include "common.php";

$array	= $_POST['arrayorder'];
if ($_POST['update'] == "update")
{
	$count = 1;
	foreach ($array as $idval) 
	{
		$query = "UPDATE about_us SET abtus_order = " . $count . " WHERE abtus_id = " . $idval;
		mysqli_query($con, $query) or die('Error, insert query failed');
		$count ++;	
	}
	echo '<font color=green>Titles has been changed successfully</font>';
}
?>