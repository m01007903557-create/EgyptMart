<?php
ob_start();
session_start();
include '../common.php';
$abtus_id=substr($_GET['token'],4);

$sql="select * from about_us,profile_heading where abtus_ph_id=ph_id and md5(abtus_id)='".$abtus_id."' limit 1";
//echo $sql;
//$sqlchk=mysql_query("select * from products where md5(pd_id)='".$token."'");
$res=mysql_query($sql);
$row=mysql_fetch_object($res);
?>
<table width="100%">
<tr>
<td>
<img src="../upload/myprofile/<?php echo $row->abtus_image;?>" width="100%" height="50%">
</td>
</tr>
</table>
