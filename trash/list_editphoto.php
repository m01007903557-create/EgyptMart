<?php
ob_start();
session_start();
include "common.php";
?>

<?php
$sql="SELECT * FROM about_us WHERE abtus_id ='".$_GET['abtid']."' "; 
$recObj=mysqli_query($con, $sql) or die(mysql_error());
$timage_num=mysqli_num_rows($recObj);
$rowk=mysqli_fetch_object($recObj);

if($timage_num>0){ ?>
<div class="" id="3P1W6" title="" style="position:relative; ">
  <img src="upload/myprofile/<?php echo $rowk->abtus_image;?>" width="125" height="93" >
</div>
 <br />
 <div id="delete_smallimg_popup" style="display: block; margin-left: 37px; padding-left: 15px;" class="z2">
 <a href="javascript:DelTempImage(<?php echo $rowk->abtus_id; ?>)" style="text-decoration:none;text-align: center;"><font size="1px"><b>remove</b></font></a>
 </div>
 
<!-- 	<tr>
		<td style="padding-top:0px;padding-bottom:0px"><div class="z2" style="display: block; margin-left: 37px; padding-left: 15px;" id="delete_smallimg"><a style="text-decoration:none;text-align: center;" href="javascript:delete_smallimg()"><font size="1px"><b>remove</b></font></a></div></td>
	</tr>-->
<?php } else { ?>
<div class="" id="3P1W6" title="" style="position:relative; ">
  <img src="images/add-image.gif" width="125" height="93"/>
</div>
<?php } ?>