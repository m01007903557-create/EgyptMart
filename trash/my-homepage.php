<?php
include "common.php";

$_SESSION['last_page']="my-homepage.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");
}
$uid=$_SESSION['uid_indm'];

$sql="select * from website_content where wc_usr_id='".$uid."'";
$res=mysql_query($sql);
$row=mysql_fetch_object($res);


class CreateFreeWebsite
{
	var $msg;
	var $wc_usr_id;
	var $wc_homepage_key_desc;
	var $wc_homepage_detail_desc;
	
	function __construct($wc_usr_id, $wc_homepage_key_desc, $wc_homepage_detail_desc)
	{	
		$this->wc_usr_id=$wc_usr_id;
		$this->wc_homepage_key_desc=$wc_homepage_key_desc;
		$this->wc_homepage_detail_desc=$wc_homepage_detail_desc;
	}

	function valid()
	{
		//include "language.php";
		$valid=true;
		if($this->wc_homepage_key_desc == "" || $this->wc_homepage_key_desc == NULL)
		{
			$this->msg='<font color="#FF0000">Key Description of your Company cannot be empty.</font>';
			$valid=false;
		}
		else if(strlen($this->wc_homepage_detail_desc)<100)
		{
			$this->msg='<font color="#FF0000">Key Description of your Company should be minimum 100 characters.</font>';
			$valid=false;
		}
		else if($this->wc_homepage_detail_desc == "" || $this->wc_homepage_detail_desc == NULL)
		{
			$this->msg='<font color="#FF0000">Detailed Description of your Company cannot be empty.</font>';
			$valid=false;
		}
		else if(strlen($this->wc_homepage_detail_desc)<200)
		{
			$this->msg='<font color="#FF0000">Detailed Description of your Company should be minimum 200 characters.</font>';
			$valid=false;
		}

		return $valid;

	}
	
	function add()
	{	

		$sql="update website_content
			set
				wc_homepage_key_desc='".$this->wc_homepage_key_desc."',
				wc_homepage_detail_desc='".$this->wc_homepage_detail_desc."',
				wc_updated_date=now()
			where
				wc_usr_id='".$this->wc_usr_id."'";
                

		mysql_query($sql);
		
//		$this->msg='<font color="#009900">Sale Offer posted successfully.</font>';
	}
}
if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}
if(isset($_POST['btnSubmit']))
{ 	
/*echo getUserInfo($uid, 'usr_mp_id');exit;*/
 if(getUserInfo($uid, 'usr_mp_id') < 3){ 
 echo '<script>alert("You have to subscribe to premium membership to establish Vendor Page");
 window.location.href="membership_plans.php";
 </script>';
 //header("Location:membership_plans.php");
 exit;
 }

	$adn=new CreateFreeWebsite(addslashes(trim($_POST['wc_usr_id'])), addslashes(trim($_POST['wc_homepage_key_desc'])), addslashes(trim($_POST['wc_homepage_detail_desc'])));

	if($adn->valid())
	{	
		$adn->add();
		
	}
	$_SESSION['msg']=$adn->msg;
	header("Location:my-homepage.php");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet"/>
<link href="css/b-v-5.css" type="text/css" rel="stylesheet" />

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->

<!--<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>-->
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
<script type="text/javascript">
function validWebsite()
{
	var wc_homepage_key_desc=document.getElementById('wc_homepage_key_desc');
	var wc_homepage_detail_desc=document.getElementById('wc_homepage_detail_desc');
	
	var message="";
    var valid=true;
	if(wc_homepage_key_desc.value == "" || wc_homepage_key_desc.value == null)
	{
		message="Key Description of your Company cannot be empty.";
		wc_homepage_key_desc.focus();
		valid=false;
	}
	else if(wc_homepage_key_desc.value.length<100)
	{
		message="Key Description of your Company should be minimum 100 characters.";
		wc_homepage_key_desc.focus();
		valid=false;
	}
	else if(wc_homepage_detail_desc.value == "" || wc_homepage_detail_desc.value == null)
	{
		message="Detailed Description of your Company cannot be empty.";
		wc_homepage_detail_desc.focus();
		valid=false;
	}
	else if(wc_homepage_detail_desc.value.length<200)
	{
		message="Detailed Description of your Company should be minimum 200 characters.";
		wc_homepage_detail_desc.focus();
		valid=false;
	}
	
	if(!valid)
	{
		alert(message);
		/*document.getElementById('error_msg').style.display="block";
		document.getElementById('error_msg').style.color = "red";
		document.getElementById('error_msg').innerHTML = message;*/
	}
	return valid;
}
$(document).ready(function() {
    
    // Change this to the location of your server-side upload handler:
    var url ='http://arabyos.com/server/php/';
    jQuery('#file_upload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
				jQuery.post("companylogo-update.php", {'uid' :'<?php echo $uid; ?>', 'file' : file.name }, function(data) {
						list_photo();	
				});

            });
        }
       
    })
});

</script>
</head>
<body>

		<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->
		<?php include "includes/header_new.php"; ?>
		<br><br>
		<div class="bt"><img src="images/z.gif" height="1" width="1" alt="<?php echo getWebSiteName(); ?>"></div>
<!-- Header End Here::-->
		
<div class="inner_wrapper">
            <?php include "includes/header_menu.php"; ?>	

		<?php include "includes/left_menu.php"; ?>
		<!--left navigation:ends--><div class="w56 f1 p2b p14 blr" style="width:80%;height:100%;">
<style type="text/css">
.max{color:#fa5901; font-size:11px; margin-top:5px}
.s_u{width:144px}
.frm_a{width:98%; border:1px solid #e0f0fd; padding:10px}
</style>
<div> 
	<!--<div class="bc f11">Company Profile &raquo;</div>-->
	<h1>My Home Page</h1>
	</div>
    <script>
	$(document).ready(function(){mecount();})
	
	function mecount()
	{
		var cnt=$("#wc_homepage_key_desc").val().length;
		var cnt2=$("#wc_homepage_detail_desc").val().length;	
		var ncnt = 250-Number(cnt);
		var ncnt2 = 4000-Number(cnt2);
		$("#cn").html(ncnt);
		$("#cn2").html(ncnt2);
	}
	</script>
	<p style="border-top: 3px solid #589CE3;margin-top: 8px;"></p>
    <div id="re_link" style="background-color: #F0E1FF;border-bottom: 1px solid #D2D2D2;color: #444444;font-size: 14px;height: 19px;padding: 10px;">
    <span style="font-size: 14px;">Add Home Page details to your Online Catalog:</span></div>

	<div class="clb px"></div>
	<div class="clb"></div>
	<div class="mt5">

		<form style="margin:0px;" action="" method="POST" name="ModReg" ONSUBMIT="return validWebsite();">
		<input type="hidden" name="wc_usr_id" id="wc_usr_id" value="<?php echo $uid; ?>"/>
		<div class="frm_a clb" style="background-color:#FAF4FF;">
		<table border="0" cellspacing="0" cellpadding="4" align="left" width="100%">

		<tr>
		
			<td class="f1" valign="top" style="color: rgb(34, 34, 34);"><b>Key Description of your Company&nbsp;</b><font  class="f11" style="color: #707070;">(Visible on your website and <?php echo get_page_settings(4);?> search)</font></td></tr>
		<tr>
			<td>
            <textarea id="wc_homepage_key_desc" style="width:100%" onkeyup="mecount();" name="wc_homepage_key_desc" rows="6" cols="100" class="a_f" tabindex="4" maxlength="250" showremain="limitfive" oncontextmenu = "return false"><?php echo $row->wc_homepage_key_desc; ?></textarea>
            <div><font id="cn" color="#ff8000"> </font>&nbsp;character(s).</div>
            </td>
		</tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td class="f1" valign="top" style="font-weight: bold; color: rgb(34, 34, 34);">Detailed Description of your Website Home Page</td></tr>
		<tr>
			<td valign="top"> 
			<textarea class="a_f" style="width:100%" id="wc_homepage_detail_desc" onkeyup="mecount();" name="wc_homepage_detail_desc" rows="10" cols="100" maxlength="4000"  showremain="limitOne" oncontextmenu  = "return  false"><?php echo $row->wc_homepage_detail_desc; ?></textarea>
            <!--<span id="cd" class="em" style="display:none"></span>-->
            <div><font id="cn2" color="#ff8000">  </font>&nbsp;character(s).</div>
                                           
		</td>
		</tr>

		<tr>
			<td align="left"><table><tr><td width= "118px;"><input type="submit" name="btnSubmit" class="saps mt5" value="Update Details" tabindex="31"/></td><td> <span id="pf_save" style="display:none;margin-left:15px;margin-top:6px;"><img width="16" height="11" border="0" src="images/loading.gif" alt=""></span> </td></tr></table></td>
		</tr>
		</table>
		<div class="clb">&nbsp;</div>
		</div>
		</form>
	</div>

	<div><br></div>
	<div><br></div></div>
		<div class="c3">&nbsp;</div></div>
</div>
		<!--footer:start-->
	<?php include 'includes/footer.php';?>