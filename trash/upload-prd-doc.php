<?php
session_start();
$uid=$_SESSION['uid_indm'];
include 'common.php';
$pid=$_GET['pid'];
?>

<html><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<!--<script language="javascript" src="js/upload-prd-doc.js"></script>-->
	<style>
	.file_button{position:relative;width:155px;height:26px;overflow:hidden;}
	.file_button .hiddenMask{position:absolute;top:-5px;right:-5px;z-index:2;filter:alpha(opacity=0);opacity:0;font-size:100px!important;}
	.file_button .fadeButton{position:absolute;top:2px;left:0;z-index:1;}
	</style>
    
     <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
 <script src="js/jquery-1.7.min.js" type="text/javascript"></script>
 <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
 <script type="text/javascript">

function list_photo()
{
	$.get("product-doc-list.php", {'pid' : <?php echo $pid; ?>}, 
	function(data){ 
	$('#list_photo').html(data); 
	//setTimeout("list_photo()",1000);
	//$("#buttons").hide();
	});
}

function DelTempdoc(id)
{
$.get("del_product_doc.php", {id:id},
 function(data){
list_photo();
 });
}

		$(function() {
			$('#file_upload').uploadifive({
				'auto'         : true,
				'formData'     : {'pid' : <?php echo $pid; ?>},
				'queueID'      : 'queue',
				'debug'    : true,
                'method'   : 'post',
				'buttonClass'     : 'input_textFiled2',
				'buttonText'      : 'Upload',
				'uploadScript' : 'product-doc-add.php',
				'onAddQueueItem' : function(file) {
                     //  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
                   },
					'onUploadComplete' : function(file,data) {
						alert(data);
                  list_photo();
				  //location.reload();
						}
			});
		});
    </script>
    
    
	</head>

	<body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">
<form name="iform" style="margin:0px; margin-top:4px;" action="" method="post" enctype="multipart/form-data">
		<script type="text/javascript">list_photo()</script>
        <div id="queue">
        <div align="left" id="list_photo" class="line clearfix">
<div class="eID" style="width:400px; padding-top:5px;">

</div>
        </div>
          </div>
          
				  <div id="buttons">
                  <div id="file-wrapper" class="title" title="upload images from your computer" style="z-index: 0; ">
                  <span class="title" style="z-index: 0; ">
                  <input type="file" name="file_upload" multiple="multiple" id="file_upload">
                  </span>
                  </div>
                  <div class="clear"></div>
                  </div>
</form>
<span class="f2" id="indecator_gif0" style="left:37px;position:relative;top:-32px;"></span> (.pdf type attachment only) 
</body></html>