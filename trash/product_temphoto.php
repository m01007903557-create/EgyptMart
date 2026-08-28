<?php
include "common.php";
?>
<style>
	.file_button, .brw{width:125px;height:125px;position:relative;overflow:hidden; cursor:pointer}
	.file_button .hiddenMask{position:absolute;top:-5px;right:-5px;z-index:2;filter:alpha(opacity=0);opacity:0;font-size:100px!important;}
	.file_button .fadeButton{position:absolute;top:2px;left:0;z-index:1;}
	body{font-family:verdana,Arial,Helvetica; font-size:12px; font-weight:bold}
	.brw{border:none;cursor:pointer}
	.l_ai{padding:3px; border:1px solid #dbdbdb; -webkit-border-radius:2px;-moz-border-radius:2px;border-radius:2px;color:#222; font-size:11px; text-decoration:none; font-weight:bold;background-color:#f1f1f1;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#f1f1f1), to(#f6f6f6));background:-webkit-linear-gradient(top, #f1f1f1, #f6f6f6);background:-moz-linear-gradient(top, #f1f1f1, #f6f6f6);background:-ms-linear-gradient(top, #f1f1f1, #f6f6f6);background:-o-linear-gradient(top, #f1f1f1, #f6f6f6)}
	.l_ai:hover{border:1px solid #c6c6c6; color:#222}
	
	.edit{box-shadow:0 0 1px 1px #e4e4e4;
	cursor:pointer;position:absolute;width:72px;margin:64px 0 0 6px;*margin:62px 0 0 -36px;padding:2px;border:1px solid #b0b0b0;background:-webkit-linear-gradient(top, #ffffff, #f0f0f0);background:-moz-linear-gradient(top, #ffffff, #f0f0f0);}
	
	.mover{border:1px solid #DCDCDC;cursor:pointer}
	.mover:hover{-ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=4, Direction=135, Color='#EAEAEA')";box-shadow:0 0 3px 3px #EAEAEA;border:1px solid #DCDCDC}
</style>
     <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
	 <script>
		function showrmvButt()
		{
			$("#cm-ed1").show()
		}
		function hidermvButt()
		{
			$("#cm-ed1").hide()
		}
     </script>
<?php
$sql="SELECT * FROM temp_products_image WHERE tmpimg_uid ='".$_GET['uid']."' "; 
$recObj=mysqli_query($con, $sql) or die(mysql_error());
$timage_num=mysqli_num_rows($recObj);
$rowk=mysqli_fetch_object($recObj);

if($timage_num>0){ ?>
 <center>
<div class="mover file_button" style="box-shadow:none;border: 1px solid rgb(220, 221, 222); margin-top:5px; margin-bottom:5px; height: 125px; width: 125px;" align="center" onMouseOver="showrmvButt();" onMouseOut="hidermvButt();">
         
	   <div id="cm-ed1" class="edit" style="display: none; margin-top: 88px; margin-left:25px;" align="center" >
       <div id="tanuj" style="width:100px;padding-left:20px;float:left;clear:both" align="left">
       <a onclick="DelTempImage(<?php echo $rowk->tmpimg_id; ?>)">
	   <img src="images/remove.gif" border="0" width="44" height="10">
	   </a>
       </div>
       </div>
		<div id="companyimages_1" style=" width:125px; height:125px;">
        <img src="upload/myproduct/<?php echo $rowk->tmpimg_image;?>" style="width:100%; height:auto;">
        </div>		
		</div>
</center>
<?php } ?>