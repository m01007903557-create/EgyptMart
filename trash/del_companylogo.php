<?php
include "common.php";
$sqlk="select * from business_profile where bnsprof_uid='".$_GET['imid']."'";
$resk=mysqli_query($con, $sqlk);
$rowk=mysqli_fetch_object($resk);


/*$path="upload/companylogo/".$rowk->bnsprof_complogo;
unlink($path);*/

$pathLrg="server/php/files/".$rowk->bnsprof_complogo;
if(is_file($pathLrg))
{
	unlink($pathLrg);
}


$sql="update business_profile set bnsprof_complogo='' where bnsprof_uid='".$_GET['imid']."'";
mysqli_query($con, $sql);
?>