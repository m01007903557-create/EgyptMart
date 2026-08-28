<?php
include 'common.php';
$uid=$_SESSION['uid_indm'];
?>
	<style>
	.newAddimage{display: block;background: -moz-linear-gradient(center top , #FFFFFF, #F0F0F0) repeat scroll 0 0 transparent; background:-webkit-linear-gradient(top, #ffffff, #f0f0f0);background:-ms-linear-gradient(top, #ffffff, #f0f0f0);background:-o-linear-gradient(top, #ffffff, #f0f0f0);background-color:#f0f0f0;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f0f0f0)); color: #444444;cursor:pointer;position: absolute; width:125px;padding-bottom:1px;margin-left: 0px;filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f0f0f0'); BACKGROUND-COLOR: #f0f0f0; COLOR: #444444; CURSOR: pointer;}
	.file_button{position:relative;width:125px;height:123px;overflow:hidden;}
	.file_button .hiddenMask{position:absolute;top:-5px;right:-5px;z-index:2;filter:alpha(opacity=0);opacity:0;font-size:106px!important;cursor:pointer}
	.file_button .fadeButton{position:absolute;top:2px;left:0;z-index:1;}
	</style>
       
<link rel="stylesheet" type="text/css" href="css/jquery.fileupload.css">
<link rel="stylesheet" type="text/css" href="css/jf-1.css">
 <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
 <script type="text/javascript">

function list_photo()
{
	$.get("companylogo-list.php", {'uid' : <?php echo $uid; ?>}, 
	function(data){ 
	$('#list_photo').html(data); 
	});
}

function DelTempImage(imid)
{
   var cnf = confirm("Remove logo?");
   if(cnf==true)
   {
	$.get("del_companylogo.php", {imid:imid},
 	function(data){
	list_photo();
 	});	 
   }
}
		function showImgButt()
		{
			$("#add_image1").show()
		}
		function hideImgButt()
		{
			$("#add_image1").hide()
		}
    </script>
	
<div class="file_button" style="margin:0px;" id="addbut">
<script type="text/javascript">list_photo()</script>
        <div id="queue">
        <div align="left" id="list_photo" class="line clearfix">
        </div>
        </div>
 <div class="upload_div">
    <img  style="float:left; margin-right:10px;" src="<?php echo BASE_URL ?>/images/comp-logo-90.gif" id="file_upload"/>
     <input id="file_upload" type="file" name="files" style="cursor:pointer;" />
	<span class="file_input">Add image</span>
    </div>
</div>