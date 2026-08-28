<?php 
ob_start();
session_start(); 
include "../common.php";

	class addreseller{
	var $msg;
	var $reseller_fullname;
	var $reseller_uname;
	var $reseller_email;
	var $reseller_domain;
	var $reseller_discount;
	var $reseller_website;
	var $reseller_terms;
    var $reseller_pass;
	var $reseller_confirmpass;
	var $reseller_logo;
	
	function __construct($reseller_fullname,$reseller_uname,$reseller_email,$reseller_domain,$reseller_discount,$reseller_website,$reseller_terms,$reseller_pass,$reseller_confirmpass,$reseller_logo)
	{
		$this->reseller_fullname=$reseller_fullname;
		$this->reseller_uname=$reseller_uname;
		$this->reseller_email=$reseller_email;
		$this->reseller_domain=$reseller_domain;
		$this->reseller_discount=$reseller_discount;
		$this->reseller_website=$reseller_website;
                $this->reseller_terms=$reseller_terms;
                $this->reseller_pass=$reseller_pass;
                $this->reseller_confirmpass=$reseller_confirmpass;
                $this->reseller_logo=$reseller_logo;
	
		$_SESSION['reseller_fullname']=$this->reseller_fullname;
		$_SESSION['reseller_uname']=$this->reseller_uname;
		$_SESSION['reseller_email']=$this->reseller_email;
		$_SESSION['reseller_domain']=$this->reseller_domain;
		$_SESSION['reseller_discount']=$this->reseller_discount;
                $_SESSION['reseller_website']=$this->reseller_website;
                $_SESSION['reseller_terms']=$this->reseller_terms;
                $_SESSION['reseller_pass']=$this->reseller_pass;
                $_SESSION['reseller_confirmpass']=$this->reseller_confirmpass;
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
                else if($this->reseller_pass == "")
		{
			$this->msg= '<font color="#CC0000">Please enter password</font>';
			$valid=false;
		}
                else if($this->reseller_confirmpass == "")
		{
			$this->msg= '<font color="#CC0000">Please enter confirm password</font>';
			$valid=false;
		}
                else if($this->reseller_pass!= $this->reseller_confirmpass)
		{
			$this->msg= '<font color="#CC0000">Password and confirm password does not match</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function add()
	{		
	global $con;
		if($_FILES["reseller_logo"]["name"] != "")
		{
				
				  $sql="insert into reseller set
					reseller_fullname='".$this->reseller_fullname."',
                                        reseller_uname='".$this->reseller_uname."',    
                                        reseller_pass='".md5($this->reseller_pass)."',
					reseller_email='".$this->reseller_email."',
					reseller_domain='".$this->reseller_domain."',
                                        reseller_website='".$this->reseller_website."',
                                        reseller_discount='".$this->reseller_discount."',
                                        reseller_terms='".$this->reseller_terms."',        
				        reseller_logo='".addslashes(file_get_contents( $_FILES['reseller_logo']['tmp_name']) )."',
					reseller_creation_date=now() ";					
	
				mysqli_query($con, $sql) or die(mysql_error());	
				
				$this->msg='<font color="#009900">Reseller added successfully</font>';	
		
                            unset($_SESSION['reseller_fullname']);
                            unset($_SESSION['reseller_uname']);
                            unset($_SESSION['reseller_pass']);
                            unset($_SESSION['reseller_email']);
                            unset($_SESSION['reseller_domain']);
                            unset($_SESSION['reseller_website']);
                            unset($_SESSION['reseller_discount']);
                            unset($_SESSION['reseller_terms']);
                            unset($_SESSION['reseller_confirmpass']);
		}
		else
		{
                    
$text = $this->reseller_website;
$font = "../font/hf.ttf";
$size = "45";

$bbox = imagettfbbox($size, 0, $font, $text);
$width = abs($bbox[2] - $bbox[0]);
$height = abs($bbox[7] - $bbox[1]);

$image = imagecreatetruecolor($width, $height);

$bgcolor = imagecolorallocate($image, 0, 0, 0);
$color = imagecolorallocate($image, 255, 255, 255);

$x = $bbox[0] + ($width / 2) - ($bbox[4] / 2);
$y = $bbox[1] + ($height / 2) - ($bbox[5] / 2);

imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgcolor);
imagettftext($image, $size, 0, $x, $y, $color, $font, $text);

$last_pixel= imagecolorat($image, 0, 0);

for ($j = 0; $j < $height; $j++)
{
    for ($i = 0; $i < $width; $i++)
    {
        if (isset($blank_left) && $i >= $blank_left)
        {
            break;
        }
        if (imagecolorat($image, $i, $j) !== $last_pixel)
        {
            if (!isset($blank_top))
            {
                $blank_top = $j;
            }
            $blank_left = $i;
            break;
        }
        $last_pixel = imagecolorat($image, $i, $j);
    }
}
$x -= $blank_left;
$y -= $blank_top;

imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgcolor);
imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
imagepng($image,'file.png');
                    
                    
                    
				  $sql="insert into reseller set
					reseller_fullname='".$this->reseller_fullname."',
                                        reseller_uname='".$this->reseller_uname."',    
                                        reseller_pass='".md5($this->reseller_pass)."',
					reseller_email='".$this->reseller_email."',
					reseller_domain='".$this->reseller_domain."',
                                        reseller_website='".$this->reseller_website."',
                                        reseller_discount='".$this->reseller_discount."',
                                        reseller_terms='".$this->reseller_terms."', 
                                        reseller_logo='".addslashes(file_get_contents('file.png'))."',    
					reseller_creation_date=now() ";						

			mysqli_query($con, $sql) or die(mysql_error());
			
			$this->msg='<font color="#009900">Reseller added successfully</font>';
                        
                            unset($_SESSION['reseller_fullname']);
                            unset($_SESSION['reseller_uname']);
                            unset($_SESSION['reseller_pass']);
                            unset($_SESSION['reseller_email']);
                            unset($_SESSION['reseller_domain']);
                            unset($_SESSION['reseller_website']);
                            unset($_SESSION['reseller_discount']);
                            unset($_SESSION['reseller_terms']);
                            unset($_SESSION['reseller_confirmpass']);
		}
	}
}
	
if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg'];	unset($_SESSION['msg']); }
if(isset($_SESSION['reseller_fullname'])){ $reseller_fullname=$_SESSION['reseller_fullname']; unset($_SESSION['reseller_fullname']); } else { $reseller_fullname=""; }

if(isset($_SESSION['reseller_uname'])){ $reseller_uname=$_SESSION['reseller_uname']; unset($_SESSION['reseller_uname']); } else { $reseller_uname=""; }
if(isset($_SESSION['reseller_email'])){ $reseller_email=$_SESSION['reseller_email']; unset($_SESSION['reseller_email']); } else { $reseller_email=""; }
if(isset($_SESSION['reseller_domain'])){ $reseller_domain=$_SESSION['reseller_domain']; 
unset($_SESSION['reseller_domain']); } else { $reseller_domain=""; }
if(isset($_SESSION['reseller_discount'])){ $reseller_discount=$_SESSION['reseller_discount']; unset($_SESSION['reseller_discount']); } else { $reseller_discount=""; }
if(isset($_SESSION['reseller_website'])){ $reseller_website=$_SESSION['reseller_website']; unset($_SESSION['reseller_website']); } else { $reseller_website=""; }
if(isset($_SESSION['reseller_terms'])){ $reseller_terms=$_SESSION['reseller_terms']; 
unset($_SESSION['reseller_terms']); } else { $reseller_terms=""; }
if(isset($_SESSION['reseller_pass'])){ $reseller_pass=$_SESSION['reseller_pass']; unset($_SESSION['reseller_pass']); } else { 
    $reseller_pass=""; }
if(isset($_SESSION['reseller_confirmpass'])){ $reseller_confirmpass=$_SESSION['reseller_confirmpass']; unset($_SESSION['reseller_confirmpass']); } else { $reseller_confirmpass=""; }

if(isset($_POST['btnAdd']))
{	
	$adn=new addreseller(addslashes(trim($_POST['reseller_fullname'])),addslashes(trim($_POST['reseller_uname'])),addslashes(trim($_POST['reseller_email'])),addslashes(trim($_POST['reseller_domain'])),addslashes(trim($_POST['reseller_discount'])), addslashes(trim($_POST['reseller_website'])), addslashes(trim($_POST['reseller_terms'])), addslashes(trim($_POST['reseller_pass'])), addslashes(trim($_POST['reseller_confirmpass'])), addslashes(trim($_FILES["reseller_logo"]["name"])));
	
	$_SESSION['reseller_fullname']=addslashes(trim($_POST['reseller_fullname']));
	$_SESSION['reseller_uname']=addslashes(trim($_POST['reseller_uname']));
	$_SESSION['reseller_email']=addslashes(trim($_POST['reseller_email']));
	$_SESSION['reseller_domain']=addslashes(trim($_POST['reseller_domain']));
	$_SESSION['reseller_discount']=addslashes(trim($_POST['reseller_discount']));
        $_SESSION['reseller_website']=addslashes(trim($_POST['reseller_website']));
        $_SESSION['reseller_terms']=addslashes(trim($_POST['reseller_terms']));
	$_SESSION['reseller_pass']=addslashes(trim($_POST['reseller_pass']));
        $_SESSION['reseller_confirmpass']=addslashes(trim($_POST['reseller_confirmpass']));
	
	if($adn->valid()){	
		$adn->add();		
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:reseller-registration.php");
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

<script type="text/javascript">
function myvalid()
{	
	var pd_title=document.getElementById('pd_title');
	var pd_version=document.getElementById('pd_version');
	var pd_price=document.getElementById('pd_price');
	var pd_short_desc=document.getElementById('pd_short_desc');
	var pd_desc=document.getElementById('pd_desc');
	var pd_home_desc=document.getElementById('pd_home_desc');
	var message="";
	var valid=true;

	if(pd_title.value=='')
	{
		message='Please enter product title';
		pd_title.focus();
		valid=false;
	}
	else if(pd_version.value=='')
	{
		message='Please enter product version';
		pd_version.focus();
		valid=false;
	}
	else if(pd_price.value=='')
	{
		message='Please enter product price';
		pd_price.focus();
		valid=false;
	}
	else if(pd_short_desc.value=='')
	{
		message='Please enter product short descriptions';
		pd_short_desc.focus();
		valid=false;
	}
	else if(pd_desc.value=='')
	{
		message='Please enter product long descriptions';
		pd_desc.focus();
		valid=false;
	}
	else if(pd_home_desc.value=='')
	{
		message='Please enter product home descriptions';
		pd_home_desc.focus();
		valid=false;
	}
	if(!valid)
	{
		document.getElementById('message').style.color = "red";
		document.getElementById('message').innerHTML = message;	
	}
	return valid;
}
</script>
<!-- /TinyMCE -->	
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>
    <div class="control_Panel">
	<?php include "includes/admin-left-con.php" ?>
		<div id="content-container">
		<div id="content">
<h2>&rsaquo;&nbsp;&nbsp;Admin Management&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Reseller</h2>
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
<input name="reseller_fullname" id="reseller_fullname" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_fullname;?>" />
			</div>
		</div>
        
		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">User Name: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_uname" id="reseller_uname" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_uname;?>" />
			</div>
        </div>

		<div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Email: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
	<input name="reseller_email" id="reseller_email" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_email;?>" />
			</div>
        </div>
        
    <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Domain: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_domain" id="reseller_domain" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_domain;?>" />
			</div>
   </div>
 
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Discount: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_discount" id="reseller_discount" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_discount;?>" />
			</div>
   </div>
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Website: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_website" id="reseller_website" type="text" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_website;?>" />
			</div>
   </div>
    
    
            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Password: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_pass" id="reseller_pass" type="password" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_pass;?>" />
			</div>
   </div>
    
    
                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Confirm Password: <span>*</span></label>
			<div class="formInputBox" style="width:387px;height:auto;">
<input name="reseller_confirmpass" id="reseller_confirmpass" type="password" class="reg_txtfld" maxlength="255" value="<?php echo $reseller_confirmpass;?>" />
			</div>
   </div>
    
    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
			<label style="width:120px;">Terms:</label>
			<div class="formInputBox" style="width:440px;height:auto;">
<textarea name="reseller_terms" id="reseller_terms" class="reg_txtarea" rows="10" cols="30"><?php echo $reseller_terms;?></textarea>
			</div>
        </div>

    
        <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
	<label style="width:120px;">Logo:</label>
	<div class="formInputBox" style="width:187px;height:auto;">
	<input name="reseller_logo" id="reseller_logo" type="file"/>
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