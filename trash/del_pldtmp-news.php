<?php
include "common.php";
if($_GET['type']=='1')
{
	$sqlk="select * from temp_newsimage where tmpns_id ='".$_GET['imid']."' and tmpns_status='".$_GET['type']."'";
	$resk=mysqli_query($con, $sqlk);
	$rowk=mysqli_fetch_object($resk);
	$path="upload/mynews/small/".$rowk->tmpns_image;
	unlink($path);
	mysqli_query($con, "delete from temp_newsimage where tmpns_id='".$_GET['imid']."'");
	
}
if($_GET['type']=='2')
{
	$sqlk="select * from temp_newsimage where tmpns_id ='".$_GET['imid']."' and tmpns_status='".$_GET['type']."'";
	$resk=mysqli_query($con, $sqlk);
	$rowk=mysqli_fetch_object($resk);
	$path="upload/mynews/large/".$rowk->tmpns_image;
	unlink($path);
	mysqli_query($con, "delete from temp_newsimage where tmpns_id='".$_GET['imid']."'");
}
?>