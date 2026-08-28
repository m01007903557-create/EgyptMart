<?php 
//ob_start();
//session_start(); 
include "../common.php";
	class addproduct{
	var $msg;
	var $f_heading;
	var $f_content;
	var $f_main_feature;
	var $f_image;
	
	function __construct($f_id)
	{
          $this->f_id=$f_id;
         
	}
	function detailsObj(){
		global $con;
		$sql="select * from features where md5(f_id)='".$this->f_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	
	function valid()
	{
		$valid=true;
		
		return $valid;
	}
	
	function update()
	{	
	  global $con;
		if($this->f_image == '1')
		{
			$fimage = '1';
		}
		else
		{
			$fimage = '0';
		}
		
		$sql="update features set
						f_heading = '".$this->f_heading."',
						f_content = '".$this->f_content."',
						f_main_feature = '".$this->f_main_feature."',
                        f_image='".$fimage."',   
						f_updated_date=now()
				where 
						md5(f_id) = '".$this->f_id."'";					
	
		mysqli_query($con, $sql) or die(mysql_error());	
		
		
		
		
		
		$this->msg='<font color="#009900">Feature Updated successfully</font>';	
	     
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg="";    }

$token = substr($_GET['token'], 4);
$ob=new addproduct($token);
$row=$ob->detailsObj();

if(isset($_POST['btnAdd']))
{	
	
	$ob->f_main_feature=trim(addslashes($_POST['f_main_feature']));			
	$ob->f_heading=trim(addslashes($_POST['f_heading']));	
	$ob->f_content=trim(addslashes($_POST['f_content']));
	$ob->f_image=trim(addslashes($_POST['f_image']));
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header("location:software_feature_edit.php?token=".$_GET['token']);
}
?>

	<?php include "includes/admin-top.php" ?>
	
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>




<script type="text/javascript">
function myvalid()
{	
	var message="";
	var valid=true;

	
	return valid;
}
</script>

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({

		// General options
		mode : "textareas",
		theme : "advanced",
		
		plugins : "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		
		/*theme_advanced_disable: "image,advimage",*/

		// Example content CSS (should be your site CSS)
		content_css : "css/content.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",

		// Style formats
		style_formats : [
			{title : 'Bold text', inline : 'b'},
			{title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
			{title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
			{title : 'Example 1', inline : 'span', classes : 'example1'},
			{title : 'Example 2', inline : 'span', classes : 'example2'},
			{title : 'Table styles'},
			{title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
		],		 
		forced_root_block : false,
		force_p_newlines : false,
		remove_linebreaks : false,
		force_br_newlines : true,
		remove_trailing_nbsp : false,
		verify_html : false		 	
	});
</script>
<!-- /TinyMCE -->
<script>
function showUploader()
{
	if($('#f_image').is(':checked'))
	{
		$("#uploadImageDiv").css("display","block");
	}
	else
	{
		$("#uploadImageDiv").css("display","none");
	}
}
</script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
  <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
  <script type="text/javascript">
 /******************************* file Upload ******************************************/
 
 function mylist_file()
{
	$.get("list_temp_photo.php", {'pid' : <?php echo $row->f_id; ?>}, function(data){	$('#list_photo').html(data); });
}
 


		jQuery(function() {
			jQuery('#file_upload').uploadifive({
				'auto'     : true,
				'formData' : {'pid' : <?php echo $row->f_id; ?>},
				'queueID'  : 'queue',
				'debug'    : true,
                'method'   : 'post',
				'uploadScript' : 'upload-image.php',
				'buttonClass'     : 'butt',
				'buttonText'      : 'Upload',	
				'onAddQueueItem' : function(file) {
					
                     //  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
                   },
					'onUploadComplete' : function(file,data) {
						
                  mylist_file();
						}

			});
		});
function DelTempImage_rc(pi)
{
	$.get("del_temp_photo.php", {'pi' : pi}, function(data){  mylist_file(); });
}


</script>
  
  
        <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Manage Software Feature&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Software Feature</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
<input type="button" class="delete-btn" onClick="window.location ='software_feature_list.php'" value="Software Feature List">
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">This is our Main Feature :</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                <input type="radio" name="f_main_feature" <?php if($row->f_main_feature=='1'){?>checked<?php }?> value="1">&nbsp;Yes&nbsp;&nbsp;
                <input type="radio" value="0" name="f_main_feature" <?php if($row->f_main_feature=='0'){?>checked<?php }?>>&nbsp;No
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Heading: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <textarea name="f_heading" id="f_heading"class="reg_txtfld" style="height: 300px" ><?php echo $row->f_heading;?></textarea> 
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Content: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                       <textarea name="f_content" id="f_content" class="reg_txtfld"  style="height: 300px" ><?php echo $row->f_content;?></textarea> 
			</div>
		</div>
          
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">This Feature have Images:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     &nbsp;&nbsp; <input type="checkbox" name="f_image" id="f_image" onclick="showUploader();" value="1"  <?php if($row->f_image=='1'){?>checked<?php }?>/>
			</div>
		</div>
        
        
        
        <div id="uploadImageDiv" <?php if($row->f_image=='0'){?>style="display: none"<?php }?>>
        
         <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Upload Images:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <input type="file" name="file_upload" multiple="multiple" id="file_upload" style=" cursor:pointer"/>
			</div>
 		</div>
 <script>
 mylist_file();
</script> 
 		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                   <div id="queue">       
                      <div align="left" id="list_photo" class="line clearfix">&nbsp;</div> 
                   </div>
		</div>
        
 		</div>
        
        </div>


</td>
</tr>
</tbody></table></div></div> </div>  		    																																					
	<div class="row buttons">
  	<input type="submit" name="btnAdd" id="btnAdd" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;"> 		</div>						    
	</form>    
 			<br clear="all"/>
		</div>
			
	</div>
	</div>
  	<br clear="all" />   	
</div>
<?php include "includes/footer.php" ?>
</body>
</html>