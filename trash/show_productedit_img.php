<?php
ob_start();
session_start();
include "common.php";
?>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script src="js/jquery.colorbox.js"></script>
<script type="text/javascript">
function searchcat()
{
	$("#sc").removeClass("tabclose").addClass("tabopen");
	$("#bc").removeClass("tabopen").addClass("tabclose");
	
	$("#browse_cat").css("display","none");
	$("#search_cat").css("display","block");
}
function beowswcat()
{
	$("#bc").removeClass("tabclose").addClass("tabopen");
	$("#sc").removeClass("tabopen").addClass("tabclose");
	
	$("#search_cat").css("display","none");
	$("#browse_cat").css("display","block");
}
function catajaxFunction(id)
{
	$("#display_mcat").css("display","block");
	
	setTimeout(function () {
		type="sell";
		$.post("ajax-file/subcategoryCheckBox.php",{id:id,type:type},    function(data){  
			$("#scat").html(data);
			$("#loading_scat").css("display","none");
			$("#scat").css("display","block");
		});
	}, 1000);
}
function scatAddDel(id)
{
	if($('#scat_'+id).attr('checked')) {
		$.post("ajax-file/addTempSellofferAlertCat.php",{id:id},    function(data){	showList()	});
	} else {
		$.post("ajax-file/delTempSellofferAlertCat.php",{id:id},    function(data){	showList()	});
	}
}
function showList()
{
	$.post("ajax-file/showTempSellofferAlertCat.php",{},    function(data){	$("#div1").html(data);	});
}
function remove(id)
{
	$.post("ajax-file/delTempSellofferAlertCat.php",{id:id},    function(data){	showList()	});
}
</script>
<div class="bg_border_new" style="height:625px;width:910px" id="dvh1">
<div style="background-color:#FFFFFF; height:620px" id="dvh2">
	<table border="0" cellpadding="0" cellspacing="0" width="100%" >
    	<tbody>
        	<tr>
            <td bgcolor="#E6E6E6"><div class="myta">Browse Image</div></td>
            <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6">&nbsp;</td>
            </tr>
		</tbody>
	</table>
	
    <img src="images/zero.gif" height="10" width="1"><br> <div style="height: 450px;">
<form style="margin:0px;" name="test" action="" onsubmit="return false"><div>
 <img src="images/zero.gif" height="14" width="1"><br>
 
  
 <div id="browse_cat" style="display: block;"> 
 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%" >
 <tbody><tr> <td width="19"><img src="images/zero.gif" height="1" width="19"></td> <td bgcolor="#f8fcff"> 
 
 <table align="left" border="0" cellpadding="0" cellspacing="0" > 
 <tbody><tr> 
 <td style="font-family:arial; font-size:12px; padding-left:3px;text-align:left" width="100%"> 
 <span id="grp1" style="text-align: center;position:static">
 <div id="textboxa">
<?php

$sql_proImg="select * from products where  pd_id = '".$_GET['pid']."'";
$pid=$_GET['pid'];
$res_row=mysqli_query($con, $sql_proImg);
while($row_proImg=mysqli_fetch_object($res_row)){
 $img=$row_proImg->pd_image;
 $makearr=explode(',', $img);
$imgarr = explode(',', $img);
$cnt=1;
   foreach($imgarr as $arrimg) {
 ?>
 

<div style="float:left;height:100px;width:107px;padding:1px;" id="imgrow<?php echo $cnt;?>">
 <span  >
<a onclick="delproimg('<?php echo $arrimg;?>','<?php echo $pid;?>','<?php echo $cnt;?>');">
 <img src="img/crossimg.png" class="delimg" style="width: 12px;position: relative;right: -26px;" />
 </a>
 </span>
<a  style="cursor:pointer;"><img  src="upload/myproduct/<?php echo $arrimg; ?>" height="100" width="105px;" /></a>
</div>
<?php $cnt++;} } ?>
</div>
 </span>
 </td>
 </tr> 
  <tr> <td width="100%"><img src="images/zero.gif" height="1" width="5"></td> </tr> 
  <tr> <td style="font-family:arial; font-size:12px; padding-left:3px;background:none;" width="100%"> <br> 
  <div style="height:170px"> 
  </div>	</td>  </tr> </tbody> 
</table>
<img src="images/zero.gif" height="8" width="1"> </td></tr></tbody></table></div>
 
   <a class="ajax" href="popup-imagegallery_edit.php" style="text-decoration:none;">Select from Image Gallery</a>
 <!--<div id="sel_category" style="display:block;">
 <span id="purDesc"><img src="images/zero.gif" height="14" width="1"><br></span>
 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"> <tbody><tr> <td valign="TOP" width="19"></td> <td> 
 
 <div align="CENTER"><img src="images/zero.gif" height="10" width="1"><br> 
 <input name="confirm1" value="Submit Categories" onclick="addAlertCategory();" type="button">
 <br><img src="images/zero.gif" height="10" width="1"><br> </div></td> </tr> </tbody></table>
 </div>-->
 <script>
 function delproimg(imgname,id,cnt)
   {
	//alert(imgname);
 
	/*if(jQuery.inArray(imgname,imgarray) != -1){
        imgarray= $.grep(imgarray, function(value) {
        return value != imgname;
          
          });
          }*/
          
          $.post("ajax-file/updateproductimg.php", {id:id,imgname:imgname},
          
          	function(data){
          
          	 $("#imgrow"+cnt).remove();
          	
		
	       });
   }
 </script>
  </div></form></div> </div></div>
 