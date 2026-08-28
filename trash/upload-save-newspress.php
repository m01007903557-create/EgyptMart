<?php
include 'common.php';
$uid=$_SESSION['uid_indm'];
?>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<link href="css/n.css" type="text/css" rel="stylesheet">
	<style>
	.newAddimage{display: block;background: -moz-linear-gradient(center top , #FFFFFF, #F0F0F0) repeat scroll 0 0 transparent; background:-webkit-linear-gradient(top, #ffffff, #f0f0f0);background:-ms-linear-gradient(top, #ffffff, #f0f0f0);background:-o-linear-gradient(top, #ffffff, #f0f0f0);background-color:#f0f0f0;background:-webkit-gradient(linear, 0% 0%, 0% 100%, from(#ffffff), to(#f0f0f0)); color: #444444;cursor:pointer;position: absolute; width:125px;padding-bottom:1px;margin-left: 0px;filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ffffff', endColorstr='#f0f0f0'); BACKGROUND-COLOR: #f0f0f0; COLOR: #444444; CURSOR: pointer;}
	.file_button{position:relative;width:125px;height:123px;overflow:hidden;}
	.file_button .hiddenMask{position:absolute;top:-5px;right:-5px;z-index:2;filter:alpha(opacity=0);opacity:0;font-size:106px!important;cursor:pointer}
	.file_button .fadeButton{position:absolute;top:2px;left:0;z-index:1;}
	</style>  
    
 <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
 <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
 <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
 <script type="text/javascript">

function list_photo()
{
	$.get("upldtmpsmall-news-list.php", {'uid' : <?php echo $uid; ?>}, 
	function(data){ 
	$('#list_photo').html(data); 
	});
}

function DelTempImage(imid,type)
{
$.get("del_pldtmp-news.php", {imid:imid,type:type},
 function(data){
list_photo();
 });
}

		$(function() {
			$('#file_upload').uploadifive({
				'auto'         : true,
				'formData'     : {'uid' : '<?php echo $uid; ?>'},
				'queueID'      : 'queue',
				'debug'    : true,
                'method'   : 'post',
				'buttonClass'     : 'input_textFiled2',
				'buttonText'      : '<div style="padding-right: 30px;"><strong style="font-size:12px;font-family:arial black;cursor:pointer;"> + </strong> Add</div>',
				'uploadScript' : 'upldtmpsmall-news.php',
				'onAddQueueItem' : function(file) {
                     //  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
                   },
					'onUploadComplete' : function(file,data) {
                  list_photo();
				  //location.reload();
						}
			});
		});
		
		function showImgButt()
		{
			$("#add_image1").show()
		}
		function hideImgButt()
		{
			$("#add_image1").hide()
		}
    </script>
	</head>

	<body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
    <form name="iform" style="margin:0px;" action="" method="post" enctype="multipart/form-data">
    
<div class="file_button" style="margin:0px;" id="addbut">
<script type="text/javascript">list_photo()</script>
        <div id="queue">
        <div align="left" id="list_photo" class="line clearfix">
        </div>
        </div>

<div id="buttons" onMouseOver="showImgButt();"  onMouseOut="hideImgButt();">
        <div id="file-wrapper" class="title" title="upload images from your computer" style=" "> 
        	<div style="border: 1px solid rgb(235, 235, 235); width: 125px; height: 125px; margin: 1px; text-align:center;">
			<img src="images/add-image.gif" width="125" height="125">
			</div>
        <div id="add_image1" style="border: 1px solid rgb(176, 176, 176); left: 22px; padding: 2px; position: absolute; font-size: 12px; text-align: center; top:80px; width: 72px; display: none;" class="newAddimage cpr">
        <input type="file" name="file_upload" multiple="multiple" id="file_upload" style=" " >
        </div>
        </div>
        <div class="clear"></div>
</div>
</div>
</form>
</body>
</html>