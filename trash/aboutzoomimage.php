<?php
session_start();
$uid=$_SESSION['uid_indm'];
include 'common.php';
$token=substr($_GET['token'],4);

$sqlchk=mysqli_query($con, "select * from about_us where md5(abtus_id)='".$token."'");
$rowchk=mysqli_fetch_object($sqlchk);
?>
<table width="100%">
<tr>
<td>
<img src="upload/myprofile/<?php echo $rowchk->abtus_image;?>" width="600" height="500">
</td>
</tr>
</table>

