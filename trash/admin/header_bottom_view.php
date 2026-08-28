<?php  
include "../common.php";
	class editproduct{
	var $msg;
	var $htbl_id;
	var $htbl_button_text;
	var $htbl_button_link;
	var $htbl_image;
	var $htbl_status;
	
	
	
	function detailsObj(){
		global $con;
		$sql="select * from header_top_bottom_link where htbl_id = '2'";
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
	if($this->htbl_image != '')
	{
	
		$ext = end(explode('.',$this->htbl_image)); 
        $validEXT = array('jpg','png','jpeg', 'gif', 'pdf');

        if (in_array($ext,$validEXT)) {
            
            $tempFile = $_FILES['htbl_image']['tmp_name'];
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);

            $image = 'HTRIMG-' . rand(0,9999) . $this->htbl_image;

			$lstImg = mysqli_fetch_object(mysqli_query($con, "select htbl_image from header_top_bottom_link where htbl_id = '2'"));
			unlink("../upload/slider/".$lstImg->htbl_image);
            $imgSImage->resize(254,163);
            $imgSImage->save("../upload/slider/" . $image);
	
		$sql="update header_top_bottom_link set
                        htbl_image = '".$image."',   
						htbl_button_text = '".$this->htbl_button_text."',
						htbl_button_link = '".$this->htbl_button_link."',
						htbl_updated_date=now(),						
						htbl_status = '".$this->htbl_status."'
						where htbl_id = '2'";				
	
		mysqli_query($con, $sql) or die(mysql_error());	
		
		
		 }
        else
        {
            $this->msg = '<font color="#CC0000">Please Upload A Image With Valid Extention</font>';
        }
		
		
		}
		else
		{		
			
			$sql="update header_top_bottom_link set
						htbl_button_text = '".$this->htbl_button_text."',
						htbl_button_link = '".$this->htbl_button_link."',
						htbl_updated_date=now(),						
						htbl_status = '".$this->htbl_status."'
						where htbl_id = '2'";				
							mysqli_query($con, $sql) or die(mysql_error());	
		}
		
		
		
		
		$this->msg='<font color="#009900">Header Top Updated successfully</font>';	
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }


$ob=new editproduct();
$row=$ob->detailsObj();


if(isset($_POST['btnAdd']))
{
	
	$ob->htbl_status=trim(addslashes($_POST['htbl_status']));
	$ob->htbl_button_link=trim(addslashes($_POST['htbl_button_link']));	
	$ob->htbl_button_text=trim(addslashes($_POST['htbl_button_text']));
	$ob->htbl_image=$_FILES['htbl_image']['name'];
		
	if($ob->valid()){
		$ob->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ob->msg;
	
	header("location:header_bottom_view.php");
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
 
  
  
  
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Manage <?php echo $row->htbl_field;?></h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
	<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Status :</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                <input type="radio" name="htbl_status" <?php if($row->htbl_status=='1'){?>checked<?php }?> value="1">&nbsp;Active&nbsp;&nbsp;
                <input type="radio" value="0" name="htbl_status" <?php if($row->htbl_status=='0'){?>checked<?php }?>>&nbsp;Inactive
			</div>
		</div>
        
        
        
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Button Text: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <input name="htbl_button_text" id="htbl_button_text"class="reg_txtfld" value="<?php echo $row->htbl_button_text;?>"/> 
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Button Link: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <input name="htbl_button_link" id="htbl_button_link"class="reg_txtfld" value="<?php echo $row->htbl_button_link;?>"/> 
			</div>
		</div>
          
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Current Image:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <img src="../upload/slider/<?php echo $row->htbl_image?>" />
			</div>
		</div> 
          
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Image:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <input type="file" name="htbl_image" id="htbl_image"/>
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