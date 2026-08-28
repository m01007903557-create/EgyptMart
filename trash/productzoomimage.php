<?php
session_start();
$uid=$_SESSION['uid_indm'];
include 'common.php';
$token=substr($_GET['token'],4);

$sqlchk=mysqli_query($con, "select * from products where md5(pd_id)='".$token."'");
$rowchk=mysqli_fetch_object($sqlchk);

$imgarr = explode(',',$rowchk->pd_image);
?>
<table width="100%">
<tr>
<td>
<img src="upload/myproduct/<?php echo $imgarr[0];?>" width="600" height="500">
</td>
</tr>
</table>
