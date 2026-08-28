<?php 
//ob_start();
//session_start(); 
include "../common.php";
$randNo = rand(10000,55555);
	class addproduct{
	var $msg;
	var $ser_heading;
	var $ser_content;
	var $ser_image;
	
	function __construct($ser_heading,$ser_content,$ser_image)
	{
          $this->ser_heading=$ser_heading;
          $this->ser_content=$ser_content;
          $this->ser_image=$ser_image;
		
          $_SESSION['ser_heading']=$this->ser_heading;
          $_SESSION['ser_content']=$this->ser_content;
          $_SESSION['ser_image']=$this->ser_image;
	}
	
	function valid()
	{
		$valid=true;
		
		return $valid;
	}
	
	function add()
	{	
	global $con;
		$ext = end(explode('.',$this->ser_image)); 
        $validEXT = array('jpg','png','jpeg', 'gif', 'pdf');

        if (in_array($ext,$validEXT)) {
            
            $tempFile = $_FILES['ser_image']['tmp_name'];
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);

            $image = 'SERLOGO-' . rand(0,9999) . $this->ser_image;

            $imgSImage->resize(35,35);
            $imgSImage->save("../image/" . $image);
	
		$sql="insert into services set
						ser_heading = '".$this->ser_heading."',
						ser_content = '".$this->ser_content."',
                        ser_image='".$image."',   
						ser_updated_date=now()";					
	
		mysqli_query($con, $sql) or die(mysql_error());	
		
		
		
		$this->msg='<font color="#009900">Service added successfully</font>';	
		
                       
            unset($_SESSION['ser_heading']);
            unset($_SESSION['ser_content']);

						
		
		 }
        else
        {
            $this->msg = '<font color="#CC0000">Please Upload A Image With Valid Extention</font>';
        }
		
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg="";    }
if(isset($_SESSION['ser_heading'])){ $ser_heading=$_SESSION['ser_heading']; unset($_SESSION['ser_heading']); } else { $ser_heading=""; }
if(isset($_SESSION['ser_content'])){ $ser_content=$_SESSION['ser_content']; unset($_SESSION['ser_content']); } else { $ser_content=""; }

if(isset($_POST['btnAdd']))
{
	
	$adn=new addproduct(addslashes(trim($_POST['ser_heading'])), addslashes(trim($_POST['ser_content'])) , $_FILES['ser_image']['name']);

	
	
	if($adn->valid()){	
		$adn->add();		
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:service_add.php");
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
<h2>&rsaquo;&nbsp;&nbsp;Manage Service&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Service</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
<input type="button" class="delete-btn" onClick="window.location ='service_list.php'" value="Service List">
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>

<tr class="formSectionRow">
<td  style="width:678px">
	
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Heading: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                      <textarea name="ser_heading" id="ser_heading"class="reg_txtfld" style="height: 300px" ><?php echo $ser_heading;?></textarea> 
			</div>
		</div>
        
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Content: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                       <textarea name="ser_content" id="ser_content" class="reg_txtfld"  style="height: 300px" ><?php echo $ser_content;?></textarea> 
			</div>
		</div>
          
		 <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Image:</label>
			<div class="formInputBox" style="width:387px;height:auto;">
                     <input type="file" name="ser_image" id="ser_image"/>
			</div>
		</div>
        
        
        
        


</td>
</tr>
</tbody></table></div></div> </div>  		    																																					
	<div class="row buttons">
  	<input type="submit" name="btnAdd" id="btnAdd" value="Add" class="x2-button" style="margin-right:10px;margin-top:5px;"> 		</div>						    
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