<?php 
ob_start();
session_start(); 
include "../common.php";
$resellerid=substr($_GET['r'],4);
class editreseller{
	var $reseller_id;	
	var $msg;		
	var $reseller_fullname;
	var $reseller_uname;
	var $reseller_email;
	var $reseller_domain;
	var $reseller_discount;
	var $reseller_website;
	var $reseller_terms;
	var $reseller_logo;
        var $reslid;

	function __construct($reseller_id){
		$this->reseller_id=$reseller_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from reseller where md5(reseller_id)='".$this->reseller_id."' ";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		$valid=true;
		if($this->reseller_fullname == "")
		{
			$this->msg= '<font color="#CC0000">Please enter Full name</font>';
			$valid=false;
		}
		else if($this->reseller_uname == "")
		{
			$this->msg= '<font color="#CC0000">Please enter User name</font>';
			$valid=false;
		}
		else if($this->reseller_email == "")
		{
			$this->msg= '<font color="#CC0000">Please enter Email</font>';
			$valid=false;
		}
//                else if(!validate::is_email($this->reseller_email))
//		{
//			$this->msgContact='<font color="#CC0000">Please enter valid email.</font>';
//			$valid=false;
//		}
		else if($this->reseller_domain == "")
		{
			$this->msg= '<font color="#CC0000">Please enter Domain</font>';
			$valid=false;
		}
		else if($this->reseller_discount == "")
		{
			$this->msg= '<font color="#CC0000">Please enter Discount</font>';
			$valid=false;
		}
                else if($this->reseller_website == "")
		{
			$this->msg= '<font color="#CC0000">Please enter website</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function update() 
	{
		global $con;
	  if($_FILES["reseller_logo"]["name"] != "")
	  {
             
       		 	  $sql="update reseller set		
					reseller_fullname='".$this->reseller_fullname."',
                                        reseller_uname='".$this->reseller_uname."',    
					reseller_email='".$this->reseller_email."',
					reseller_domain='".$this->reseller_domain."',
                                        reseller_website='".$this->reseller_website."',
                                        reseller_discount='".$this->reseller_discount."',
                                        reseller_terms='".$this->reseller_terms."',        
				        reseller_logo='".addslashes(file_get_contents( $_FILES['reseller_logo']['tmp_name']) )."'
					where reseller_id=".$this->reslid;					
		mysqli_query($con, $sql) or die(mysql_error());
		$this->msg='<font color="#009900">Reseller updated successfully</font>';          
	  }
	else{
       		 	  $sql="update reseller set		
					reseller_fullname='".$this->reseller_fullname."',
                                        reseller_uname='".$this->reseller_uname."',    
					reseller_email='".$this->reseller_email."',
					reseller_domain='".$this->reseller_domain."',
                                        reseller_website='".$this->reseller_website."',
                                        reseller_discount='".$this->reseller_discount."',
                                        reseller_terms='".$this->reseller_terms."'
					where reseller_id=".$this->reslid;					
		mysqli_query($con, $sql) or die(mysql_error());
		$this->msg='<font color="#009900">Reseller updated successfully</font>'; 
	   }	
	}	
}

if(isset($_SESSION['msg'])){
	$msg=$_SESSION['msg'];
	unset($_SESSION['msg']);
}

$ob=new editreseller($resellerid);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){
	
	$ob->reseller_fullname=trim(addslashes($_POST['reseller_fullname']));			
	$ob->reseller_uname=trim(addslashes($_POST['reseller_uname']));	
	$ob->reseller_email=trim(addslashes($_POST['reseller_email']));
	$ob->reseller_domain=trim(addslashes($_POST['reseller_domain']));
	$ob->reseller_website=trim(addslashes($_POST['reseller_website']));
	$ob->reseller_discount=trim(addslashes($_POST['reseller_discount']));
        $ob->reseller_terms=trim(addslashes($_POST['reseller_terms']));
	$ob->reseller_logo=addslashes(trim($_FILES["reseller_logo"]["name"]));
	$ob->reslid=trim(addslashes($_POST['reslid']));				
		
	if($ob->valid()){
		$ob->update();
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$ob->msg;
	
	header("location:reseller-edit.php?r=".$_GET['r']);
}
?>

	<?php include "includes/admin-top.php" ?>
	
<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
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
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Admin Management&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Edit Reseller</h2>
<form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="">
<em style="display:block;margin:5px;">Fields with <span >*</span> are required.</em>
 <div id="message"><?php echo $msg;?></div><br />

<div class="x2-layout" style="width:850px;">
 <div class="formSection showSection">
<div class="tableWrapper">
<table><tbody>
<tr class="formSectionRow">
<td  style="width:678px">
		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Full Name: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_fullname" id="reseller_fullname" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $row->reseller_fullname;?>" />
			</div>
		</div>
        
		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">User Name: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_uname" id="reseller_uname" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $row->reseller_uname;?>" />
			</div>
        </div>

		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Email: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
	<input name="reseller_email" id="reseller_email" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $row->reseller_email;?>" />
			</div>
        </div>
        
    <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Domain: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_domain" id="reseller_domain" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $row->reseller_domain;?>" />
			</div>
   </div>
 
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Discount: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_discount" id="reseller_discount" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $row->reseller_discount;?>" />
			</div>
   </div>
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Website: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_website" id="reseller_website" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $row->reseller_website;?>" />
			</div>
   </div>   
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Terms:</label>
			<div class="formInputBox" style="width:440px;height:auto;">
<textarea name="reseller_terms" id="reseller_terms" class="reg_txtarea" rows="10" cols="30"><?php echo $row->reseller_terms;?></textarea>
			</div>
        </div>

    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
	<label style="width:120px;">Logo:</label>
	<div class="formInputBox" style="width:187px;height:auto;">
        <?php if($row->reseller_logo!=""){ ?>
        <img src="data:image/jpeg;base64,<?php echo base64_encode($row->reseller_logo); ?>" width="80" height="80"/>
        <?php } else { ?>
	<img src="../products_images/il_75x75.jpg" width="80">
	<?php }?>    
	<input name="reseller_logo" id="reseller_logo" type="file"/>
        <input type="hidden" name="reslid" id="reslid" value="<?php echo $row->reseller_id;?>">
	</div>
        </div>            
</td>
</tr>
</tbody></table></div></div> </div>  		    																																					
	<div class="row buttons">
  	<input type="submit" name="btnUpdate" id="btnUpdate" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;"> 		</div>						    
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