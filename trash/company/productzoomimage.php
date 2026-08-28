<?php
session_start();
$uid=$_SESSION['uid_indm'];
include '../common.php';
$token=substr($_GET['token'],4);

$sqlchk=mysql_query("select * from products where md5(pd_id)='".$token."'");
$rowchk=mysql_fetch_object($sqlchk);
?>
<table width="100%">
<tr>
<td style="text-align:center">
<img src="../upload/myproduct/<?php echo $rowchk->pd_image;?>" style="max-width:100%; max-height:100%;">
</td>
</tr>
</table>
