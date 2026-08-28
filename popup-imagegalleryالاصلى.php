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
 
 /*function usePhoto()
{
	var tbl='buy_requirement_edit';
	var br_id = 0;
	if($('#br_id').length > 0) {
	br_id=document.getElementById('br_id').value;
	}
	
	var id= $('img.highlighted').attr('id');
	$.post("ajax-file/addNewImgFrmGallery.php", {id:id,br_id:br_id,tbl:tbl}, function(data){
//		parent.jQuery.colorbox.close();
		$('#cboxClose').click();
		$.colorbox.close();
		parent.$.colorbox.close();
		$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="100" width="100"/>');
		
		setTimeout(function (){

		show_photo(br_id);

         }, 500);
	});
}*/
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
	<style>
.highlighted {
    border:3px solid #F44336;
    background-image: url(images/tick.png);
}
#textbox span > a > span {
    bottom: 21px;
    color: #0f0;
    left: 41px;
    position: relative;
    display:none;
    border:none;
}
#textbox span > a > span.highlighted {
    display:inline;
    
}
</style>
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
 <div id="textbox">
<?php

$sql_ph="select * from photo where ph_status = '1' and ph_u_id = '".$_SESSION['uid_indm']."' order by ph_id desc";
$res_ph=mysqli_query($con, $sql_ph);
while($row_ph=mysqli_fetch_object($res_ph)){
 ?>
<span style="float:left;height:100px;width:107px;padding:1px;">
<a onclick="usePhotoToUpload('<?php echo $row_ph->ph_id; ?>');" style="cursor:pointer;"><img  id="<?php echo $row_ph->ph_id; ?>" src="upload/image_gallery/<?php echo $row_ph->ph_fileName; ?>" height="100" width="105px;" /><span class="tickmark">&#10004;</span></a>
</span>
<?php } ?>
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
 
 
 <!--<div id="sel_category" style="display:block;">
 <span id="purDesc"><img src="images/zero.gif" height="14" width="1"><br></span>
 <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"> <tbody><tr> <td valign="TOP" width="19"></td> <td> 
 
 <div align="CENTER"><img src="images/zero.gif" height="10" width="1"><br> 
 <input name="confirm1" value="Submit Categories" onclick="addAlertCategory();" type="button">
 <br><img src="images/zero.gif" height="10" width="1"><br> </div></td> </tr> </tbody></table>
 </div>-->
<button class=" saps  mtt" onclick="usePhoto();">OK</button>
  </div></form></div> </div></div>
  <script>
 
var ImageSelector = function() {
  var imgs = null;
  var selImg = null;
  return {
    addImages: function(container) {
      imgs = container.getElementsByTagName("img");
      for (var i = 0, len = imgs.length; i < len; i++) {
        var img = imgs[i];
        //img.className = "normal";
       // img.nextSibling.className = "normal";
        img.onclick = function() {
        selImg=this.className;
          if (selImg=="highlighted") {
            this.className = "normal";
           this.nextSibling.className  = "normal";
          }
          else{
          this.className = "highlighted";
          this.nextSibling.className = "highlighted";
          }
          selImg = null;
        };
      }
    }
  };
}();
var div = document.getElementById("textbox");
ImageSelector.addImages(div);
  </script>