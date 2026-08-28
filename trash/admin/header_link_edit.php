<?php 
ob_start();
//session_start(); 
include "../common.php";
	class addproduct{
	var $msg;
	var $hl_upper_text;
	var $hl_link;
	var $hl_content;
	var $hl_lower_text;
	var $hl_status;
	
	function __construct($hl_id)
	{
          $this->hl_id=$hl_id;
         
	}
	
	function detailsObj(){
		global $con;
		$sql="select * from header_link where md5(hl_id)='".$this->hl_id."'";
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
		if($this->hl_status == '1')
		{
			$fimage = '1';
		}
		else
		{
			$fimage = '0';
		}
		
		$sql="update header_link set
						hl_upper_text = '".$this->hl_upper_text."',
						hl_lower_text = '".$this->hl_lower_text."',
						hl_link = '".$this->hl_link."',   
						hl_content = '".$this->hl_content."',
						hl_updated_date=now(),
                        hl_status='".$fimage."'
				where 
						md5(hl_id) = '".$this->hl_id."'";					
	
		mysqli_query($con, $sql) or die(mysql_error());	
		
		
		
		
		
		$this->msg='<font color="#009900">Header Link Updated successfully</font>';	
	     
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg="";    }

$token = substr($_GET['token'], 4);
$ob=new addproduct($token);
$row=$ob->detailsObj();

if(isset($_POST['btnAdd']))
{	
	
	$ob->hl_upper_text=trim(addslashes($_POST['hl_upper_text']));			
	$ob->hl_lower_text=trim(addslashes($_POST['hl_lower_text']));	
	$ob->hl_link=trim(addslashes($_POST['hl_link']));
	$ob->hl_content=trim(addslashes($_POST['hl_content']));
	$ob->hl_status=trim(addslashes($_POST['hl_status']));
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header("location:header_link_edit.php?token=".$_GET['token']);
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
	if($('#hl_status').is(':checked'))
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
<h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Header Link Edit</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
<input type="button" class="delete-btn" onClick="window.location ='header_link_view.php'" value="Header Link">
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
                <input type="radio" name="hl_status" <?php if($row->hl_status=='1'){?>checked<?php }?> value="1">&nbsp;Active&nbsp;&nbsp;
                <input type="radio" value="0" name="hl_status" <?php if($row->hl_status=='0'){?>checked<?php }?>>&nbsp;Inactive
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Upper Text: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                 <input name="hl_upper_text" id="hl_upper_text"class="reg_txtfld" value="<?php echo $row->hl_upper_text;?>"/> 
			</div>
		</div>
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Lower Text: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                 <input name="hl_lower_text" id="hl_lower_text"class="reg_txtfld" value="<?php echo $row->hl_lower_text;?>"/> 
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Link: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                       <textarea name="hl_link" id="hl_link" class="reg_txtfld"  style="height: 300px" ><?php echo $row->hl_link;?></textarea> 
			</div>
		</div>
       
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Tooltip content: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                       <textarea name="hl_content" id="hl_content" class="reg_txtfld"  style="height: 300px" ><?php echo $row->hl_content;?></textarea> 
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