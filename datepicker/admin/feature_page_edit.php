<?php 
//ob_start();
//session_start(); 
include "../common.php";
//include '../lib/simpleimage.php';	

//check_user_login();
class editProduct{
	
	var $msg;
	var $fpc_id;
	var $fpc_heading;
		
	function __construct($fpc_id){
		$this->fpc_id=$fpc_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from featurepage_content where fpc_id='".$this->fpc_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid(){
	
		$valid=true;
		
			
		return $valid;
	}

	function update()
	{		
		global $con;
			$sql="update featurepage_content
				set	
					fpc_heading ='".$this->fpc_heading."',
					fpc_updated_date=now()
				where fpc_id='".$this->fpc_id."'";							
		
		mysqli_query($con, $sql) or die(mysql_error());
														
		$this->msg='<font color="#009900">Record updated successfully</font>';	
	}	

}

if(isset($_SESSION['msg'])){    $msg=$_SESSION['msg'];  unset($_SESSION['msg']);    }else{  $msg="";    }
//$st_field=$_POST['st_field'];
$ob=new editProduct($_GET['sid']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){
      
   
		$ob->fpc_heading=addslashes(trim($_POST['fpc_heading']));
	
		
	if($ob->valid()){
		$ob->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ob->msg;
	
	header("location:feature_page_edit.php?sid=".$ob->fpc_id);
}
?>

	<?php include "includes/admin-top.php" ?>
	
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

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
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&nbsp;Manage Webpage&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Feature Page Edit</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
<div  class="row buttons"><input type="button" name="btnUpdate" id="btnUpdate"  onclick=location.href='feature_page_view.php' value="Back" class="x2-button" style="margin-right:10px;margin-top:5px;"> </div>		
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
                      <textarea name="fpc_heading" id="fpc_heading"class="reg_txtfld" style="height: 300px" ><?php echo $row->fpc_heading;?></textarea> 
			</div>
		</div>
        
       
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;"></label>
			<div class="formInputBox" style="width:387px;height:auto;">
                                       
              		<a href="software_feature_list.php">View & Upload Content List</a>
                                     
			</div>
		</div>
      
        
        
        
</td>
</tr>
</tbody></table></div></div> </div>
  
	<div class="row buttons">
  	<input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;"> 
    
    		</div>			    
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
