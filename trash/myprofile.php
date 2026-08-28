<?php
include 'common.php';

$uid=$_SESSION['uid_indm'];

$sql_wc="select * from website_content where wc_usr_id='".$uid."'";
$res_wc=mysql_query($sql_wc);
$row_wc=mysql_fetch_object($res_wc);


class addsaleroom
{
	var $msg;
	var $abtus_ph_id;
	var $abtus_desc;
	var $abtus_wc_id;
	var $uid;
	
	function __construct($abtus_ph_id,$abtus_desc,$abtus_wc_id,$uid)
	{
		$this->abtus_ph_id=$abtus_ph_id;
		$this->abtus_desc=$abtus_desc;
		$this->abtus_wc_id=$abtus_wc_id;
		$this->uid = $uid;
	
		$_SESSION['abtus_ph_id']=$this->abtus_ph_id;
		$_SESSION['abtus_desc']=$this->abtus_desc;
	}
	
	function valid()
	{
		$valid=true;
		$hd=1;
		$totaldesc=strlen($this->abtus_desc);
		
		$sql_chk="select * from about_us where abtus_ph_id ='".$this->abtus_ph_id."' and abtus_wc_id ='".$this->abtus_wc_id."' ";
		$res_chk=mysql_query($sql_chk);
		if(mysql_num_rows($res_chk)>0)
		{	
			$hd=0;		
		}
		if($this->abtus_ph_id == "")
		{
			$this->msg= '<font color="#CC0000">Please check that Profile Heading cannot be blank.</font>';
			$valid=false;
		}
		else if($hd == 0)
		{
			$this->msg= '<font color="#CC0000">This title is already in use.</font>';
			$valid=false;
		}
		else if($this->abtus_desc == "")
		{
			$this->msg= '<font color="#CC0000">Please check that Profile Description cannot be blank.</font>';
			$valid=false;
		}
		else if($totaldesc > 4000)
		{
			$this->msg= '<font color="#CC0000">Please check that Profile Description cannot have more than 4000 characters.</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function add()
	{ 
	$tmpimgsql=mysql_query("select * from temp_about_us where tmabs_usrid='".$this->uid."'");
	$tmpimagerow =mysql_fetch_object($tmpimgsql);
	 
				$sql="insert into about_us 
					set
						abtus_wc_id='".$this->abtus_wc_id."',
						abtus_ph_id='".$this->abtus_ph_id."',
						abtus_image='".$tmpimagerow->tmabs_images."',
						abtus_desc='".$this->abtus_desc."',
						abtus_date=now()";					
	
				mysql_query($sql) or die(mysql_error());	
				mysql_query("delete from temp_about_us where tmabs_usrid='".$this->uid."' ");

				unset($_SESSION['abtus_ph_id']);
				unset($_SESSION['abtus_desc']);
	}
}
	
	if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg'];	unset($_SESSION['msg']); }
	if(isset($_SESSION['abtus_ph_id'])){ $abtus_ph_id=$_SESSION['abtus_ph_id']; unset($_SESSION['abtus_ph_id']); } else { $abtus_ph_id=""; }
	if(isset($_SESSION['abtus_desc'])){ $abtus_desc=$_SESSION['abtus_desc']; unset($_SESSION['abtus_desc']); } else { $abtus_desc=""; }
//echo 'usr_mp_id'.getUserInfo($uid, 'usr_mp_id');exit;
if(isset($_POST['btnAdd'])) {
if(getUserInfo($uid, 'usr_mp_id') < 3){ 
 echo '<script>alert("You have to subscribe to premium membership to establish Vendor Page");
 window.location.href="membership_plans.php";
 </script>';
 exit;
 }
{
	$adn=new addsaleroom(addslashes(trim($_POST['abtus_ph_id'])),addslashes(trim($_POST['abtus_desc'])),addslashes(trim($_POST['abtus_wc_id'])),$uid);
	
	$_SESSION['abtus_ph_id']= addslashes(trim($_POST['abtus_ph_id']));			
	$_SESSION['abtus_desc']=addslashes(trim($_POST['abtus_desc']));
	
	if($adn->valid()){	
		$adn->add();	
	}
	//echo $ecms->msg;
	$_SESSION['msg']=$adn->msg;
	header("location:myprofile.php");
}
}
 $abtsql=mysql_query("select * from about_us,profile_heading where abtus_ph_id=ph_id and abtus_wc_id='".$row_wc->wc_id."'"); 
 $totalabt=mysql_num_rows($abtsql);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/about-us.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script>
function descount()
{
var cnt=$("#abtus_desc").val().length;		
$("#cn").html(cnt);
}

function formopend()
{
$("#form_tst1").show();	
//$("#edit_abt").hide();
}

function showdeloption(id)
{
$("#dcon"+id).slideDown('slow');
}
function hidedeloption(id)
{
$("#dcon"+id).slideUp('slow');
}
function delmprofile(id)
{
$.get("ajax-file/delmprofile.php", {id:id},
	function(data){
	location.reload();
	});		
}

function formclose()
{
$("#form_tst1").hide();	
}
function showdesc(id)
{
$("#base_desc_hd"+id).show();	
$("#less_sd"+id).show();
$("#base_desc_sd"+id).hide();	
$("#less_hd"+id).hide();
}

function hidedesc(id)
{
$("#base_desc_hd"+id).hide();	
$("#less_sd"+id).hide();
$("#base_desc_sd"+id).show();	
$("#less_hd"+id).show();		
}

function showedit(id)
{	
$(".abouteditdv").hide();
$(".abtListdv").show();

$("#edit_abt"+id).show();
$("#list_abt"+id).hide();
$("#form_tst1").hide();
}

function hidedit(id)
{
$("#edit_abt"+id).hide();
$("#list_abt"+id).show();
}

function allcount(id)
{
var cnt=$("#abtusdesc"+id).val().length;		
$("#act"+id).html(cnt);
}

function mysave(id)
{
var abtusheading=$("#abtusheading"+id).val();	  
var abtusdesc=$("#abtusdesc"+id).val();
if(abtusheading=="")
{
$("#updatetmsg"+id).html('Please check that Profile Heading cannot be blank');
$("#updatetmsg"+id).css("color","red");
}
else if(abtusdesc=="")
{
$("#updatetmsg"+id).html('Please check that Profile Description cannot be blank.');
$("#updatetmsg"+id).css("color","red");
}
else
{
	$.get("ajax-file/about-us-edit.php", {id:id,
	abtusheading:abtusheading,abtusdesc:abtusdesc},
	function(data){
	var d=data.split('||');
	if(d[0]!=" ")
	{
	$("#updatetmsg"+id).html(d[0]);
	$("#updatetmsg"+id).css("color","red");
	}
	else
	{
	$("#updatetmsg"+id).html(d[1]);
	$("#updatetmsg"+id).css("color","green");
	location.reload();	
	}
	});		
}
}
</script>

</head>

<body>
<div class="hm1 bbc" id="res-mob1">
	<?php include "includes/header_new.php"; ?>
	<br><br>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

<div class="inner_wrapper">
    <?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->
        <div class="w56 f1 p2b p14 blr" style="width:80%;hight:100%;"><div></div>
		<div class="c3"></div>
		<div>
		<div id="chg_name" class="f1 chng_a"><h1 class="f1" id="cpf_name">Profile & News .. إملأ موضوعات وأخبار تجارية عن الشركة </h1></div><p id="pf_change" style="display:none;float:left;margin-top:0px"></p>
		<p class="f2 mt11 cnt_1" id="prof_cnt"></p>
		<div class="c3"></div>
		</div>
		
		<div class="clb px"></div> 
		<div class="" style="margin-top:4px;"><p class="aml"></p><div id="re_link" class="utab">
        <span style="font-size: 12px;*float:left;"title=" إملأ أخبار تجارية عن الشركة وموضوعات تهم المشتريين  " >Add - About Us & Company News to your Online Catalog:</span>
        <?php if($totalabt>0){ ?><a href="myprofileorder.php" class="f2 fw prf" style="display:block;" id="rearr_link"title=" أعد ترتيب الموضوعات أعلى وأسفل للنشر " >Rearrange</a><?php } ?>
        <a style="display: block;" class="f2 fw apr1" id="edit_add" onclick="formopend('add');" href="myprofile.php#form_tst1"title=" إحفظ الأخبار للنشر " >Add About Us</a>
        </div><div class="c3"></div>
				<div class="c3"></div>
<?php
 while($abtrow=mysql_fetch_object($abtsql))
 { 
?>
		<div id="list_abt<?php echo $abtrow->abtus_id; ?>" class="mt_7 ap4 p8 s mse abtListdv">
		<div class="c3"></div>
        <link href="css/colorbox.css" type="text/css" rel="stylesheet">
        <script src="js/jquery.colorbox.js"></script>

		<script>
			$(document).ready(function(){
				//Examples of how to assign the ColorBox event to elements
				
				$(".ajax").colorbox();
				$(".inline").colorbox({inline:true, width:"50%"});
				//Example of preserving a JavaScript event for inline calls.
				$("#click").click(function(){ 
					$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
					return false;
				});
			});
		</script>
		<table width="100%">
		<tbody>
        <!--<tr><td colspan="4"><div id="svd_1671511" class="save bnr db"><table width="100%"><tbody><tr>
        <td style="float:right;"><a style="color:#569AE1;" href="#add" onclick="formopen();">Add More</a></td></tr></tbody></table></div></td></tr>-->
		<tr><td style="vertical-align:top;width:125px;*width:126px;">
        <?php if($abtrow->abtus_image!="") { ?>
		<div class="f1" style="width:125px">
		<div class="f1 ap3" id="base_p_image_1671511" align="center">
        <img src="upload/myprofile/<?php echo $abtrow->abtus_image; ?>" id="img_small_form_1671511" width="125" height="93"></div>
         <a href="aboutzoomimage.php?token=<?php echo rand(1000,9999).md5($abtrow->abtus_id);?>" class="ajax" style="cursor:pointer;"><div class="z bnr f2 mrgzoom">
       &nbsp;
        </div></a>
		</div>
        <?php } else { ?>
        <div class="f1" style="width:125px">
		<div class="f1 ap3" id="base_p_image_1671511" align="center">
        <img src="images/noimage.jpg" id="img_small_form_1671511" width="125" height="125"></div>
		</div>
        <?php } ?>
		 </td><td style="vertical-align:top;">
			<div class="f1 ap5 wrd-brk awpf">
			<h2 class="mb5 itm_clr" id="base_title_1671511"><?php echo $abtrow->ph_title; ?></h2>
			<div  id="base_desc_hd<?php echo $abtrow->abtus_id; ?>" style="margin-right:20px;color: #222222; display:none;"><?php echo $abtrow->abtus_desc; ?></div> 
			<div id="base_desc_sd<?php echo $abtrow->abtus_id; ?>" style="margin-right:20px;color: #222222;">
			<?php echo substr($abtrow->abtus_desc,0,296); ?>
            </div> 
            <?php if(strlen($abtrow->abtus_desc)>296) { ?>
<a style="padding-right:20px;float:right;font-size:12.5px;text-align:center;text-decoration:underline; cursor:pointer;" id="less_hd<?php echo $abtrow->abtus_id; ?>" onClick="showdesc(<?php echo $abtrow->abtus_id; ?>)">
View Complete Details</a>
        	<?php } ?>
            <span id="less_sd<?php echo $abtrow->abtus_id; ?>" style="display:none;"> 
<a style="padding-right:20px;float:right;font-size:12.5px;text-align:center;text-decoration:underline;cursor:pointer;" onClick="hidedesc(<?php echo $abtrow->abtus_id; ?>)">
Less</a></span>
			</div>
			
			<div style="width: 100px; margin-left: 20px; margin-top: 100px;" class="f1">
			<span style="*margin-bottom:5px" class="link1 cpr">		
	<a onclick="showedit(<?php echo $abtrow->abtus_id; ?>);" class="edi bnr dl_pf" id="edit_1" style="*float:none;display:block;padding-bottom: 4px;"title=" أعد التحرير " >Edit</a>
            </span>
		<a id="delp_1671511" onclick="showdeloption(<?php echo $abtrow->abtus_id; ?>)" class="del bnr dl_pf" style="cursor:pointer;"title=" إحذف " >Delete</a>
            </div>
		</td></tr></tbody></table>
		<div class="c3"></div>
        <div class="info bnr dn" id="dcon<?php echo $abtrow->abtus_id; ?>" style="display:none;">
        <div style="width:125px;" class="f2">
        <a id="yesp_1671495" onclick="delmprofile(<?php echo $abtrow->abtus_id; ?>)" class="yn" style="cursor:pointer;">Yes</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <a id="nop_1671495" onclick="hidedeloption(<?php echo $abtrow->abtus_id; ?>)" class="yn" style="cursor:pointer;">No</a>
        </div>Do you really want to delete this record ?
		</div>
		</div> 
               
   <div id="edit_abt<?php echo $abtrow->abtus_id; ?>" style="display:none;" class="ap4 aef mt_7 abouteditdv">
   <div style="margin-right:2px;width:56px;float:right;" class="dis">
   <a style="cursor:pointer;" onclick="hidedit(<?php echo $abtrow->abtus_id; ?>);">Close [x]</a>
   </div> 
   <form action="" name="dataform" method="post"> 
   <table id="mysaveid" border="0" cellpadding="4" cellspacing="0" width="100%"> <tbody>
       	<tr>
		<td colspan="4" align="left" valign="top">
        <span class="label" style="font-weight:bold"></span>
		<div id="updatetmsg<?php echo $abtrow->abtus_id; ?>"><?php echo $msg;?></div>
		</td>
    	</tr>
   
   <tr> <td rowspan="3" valign="top" width="135"> <div> 
   <p style="display: none;" id="old_img_form" class="ap3" align="center"></p>
    
    		<div style="display: block;z-index: 0;" id="old_img_f" class="ap3">
            <div id="img_gdb"></div> 
<iframe src='update-aboutus-image.php?abtid=<?php echo $abtrow->abtus_id;?>' border="0" framespacing="0" allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125">
</iframe>
		    </div> 
     </div> 
     </td> <td style="padding:0" align="left"><span class="label" style="font-weight:bold"><span>*</span> Profile Heading:</span></td> </tr> <tr> <td style="padding:0" valign="top"> <table border="0" cellpadding="0" cellspacing="0"> <tbody><tr> <td collspan="2"> <table border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr><td> 
        <div id="mandropEdit">
        	<select name="abtusheading<?php echo $abtrow->abtus_id; ?>" id="abtusheading<?php echo $abtrow->abtus_id; ?>" class="a_f rf" style="width:276px">
            <option value="">Select Heading</option>
      		<?php
            $hdsql=mysql_query("select * from profile_heading where ph_status='1'");
			while($hdrow=mysql_fetch_object($hdsql))
			{
			?>
   <option value="<?php echo $hdrow->ph_id;?>" <?php if($abtrow->abtus_ph_id==$hdrow->ph_id){ ?>  selected="selected" <?php } ?>><?php echo $hdrow->ph_title;?></option>
            <?php } ?>
            </select>
        </div>
        </td>
        <td><div id="selectEdit2"></div><div></div> </td></tr> </tbody></table>  </td></tr> </tbody></table> </td> </tr> <tr> <td>&nbsp;</td>
  <td align="right" valign="bottom"> 
 <input style="margin-right:1px" id="save_button" onclick="mysave(<?php echo $abtrow->abtus_id; ?>);" value="Save" class="saps mt5" type="button"> 
 </td> 
 </tr> 
 <tr><td style="padding-top:0px;padding-bottom:0px"><div id="delete_smallimg_popup" style="display:none;margin-left:37px;padding-left:15px;" class="z2">
 <a href="javascript:delete_smallimg()" style="text-decoration:none;text-align: center;"><font size="1px"><b>remove</b></font></a></div>
 </td></tr>         
    <tr><td colspan="4" align="left" valign="top">
    <span class="label" style="font-weight:bold"><span>*</span> Profile Description:</span>
    <textarea aria-hidden="true" name="abtusdesc<?php echo $abtrow->abtus_id; ?>" rows="10" cols="80" id="abtusdesc<?php echo $abtrow->abtus_id; ?>" class="a_f rf" style="width: 100%; height: 200px; display: block;" onKeyUp="allcount(<?php echo $abtrow->abtus_id; ?>);">
 <?php echo $abtrow->abtus_desc;?></textarea>
   <span class="mceEditor defaultSkin" id="p_desc1_parent"></span>  
   <div class="max f11 tlx"><font id="Charcount1" color="#ff8000"><span id="act<?php echo $abtrow->abtus_id; ?>">0</span> character (maximum of 4000)</font>&nbsp;character(s)</div> 
   </td></tr>
    
   </tbody></table>        
        </form>
        </div>
		<!-- Additional Profile id:1671511 :: ends-->
<?php } ?>


	<!--add new extended profile:start-->
	<div id="form_tst1" style="display:<?php if(($totalabt<=0) || ($msg!='')){ ?>block<?php } else { ?> none <?php }?>;" >
	<div id="profile" class="aef ap4 mt_7" align="center">
	<div style="margin-right:2px;width:56px;float:right;" class="dis"><a href="javascript:formclose();">Close [x]</a></div>
	<form name="ad_profile" method="post" action="" onsubmit="return check_data();">
	<div>
	<table align="center" border="0" cellpadding="4" cellspacing="0" width="100%">

	<tbody>
    	<tr>
		<td colspan="4" align="left" valign="top">
        <span class="label" style="font-weight:bold"></span>
		<div id="message"><?php echo $msg;?></div>
		</td>
    	</tr>
    
    <tr>
		<td rowspan="3" valign="top" width="135">
		<div>
		<p id="old_img_form0" class="ap3" style="display:none" align="center"></p>
        
        
  <link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
 <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
 <script type="text/javascript">

function list_photo()
{
	$.get("list_photo.php", {'uid' : <?php echo $uid; ?>}, 
	function(data){ 
	$('#list_photo').html(data); 
	//setTimeout("list_photo()",1000);
	//$("#buttons").hide();
	});
}

function DelTempImage(imid)
{
$.get("del_temp_image.php", {imid:imid},
 function(data){
list_photo();
//location.reload();
//$("#buttons").show();
 });
}

/*function showupload_button()
{
$("#buttons").hide();
setTimeout("showupload_button()",1000);
}*/


		$(function() {
			$('#file_upload').uploadifive({
				'auto'         : true,
				'formData'     : {'uid' : '<?php echo $uid; ?>'},
				'queueID'      : 'queue',
				'debug'    : true,
                'method'   : 'post',
				'buttonClass'     : 'input_textFiled2',
				'buttonText'      : 'Upload',
				'uploadScript' : 'upload-image.php',
				'onAddQueueItem' : function(file) {
                     //  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
                   },
					'onUploadComplete' : function(file,data) {
                  list_photo();
				  //location.reload();
						}
			});
		});
    </script>
		<div style="display: block;z-index: 0;" id="old_img_f" class="ap3">
         <iframe src="upload-aboutus-image.php" border="0" framespacing="0" allowtransparency="true" scrolling="no" width="125" frameborder="0" height="125"></iframe>
		</div>
		</div>
		</td>

		<td style="padding:0" align="left"><span class="label" style="font-weight:bold"><span>*</span>&nbsp;Profile Heading</span></td>
	</tr>
	<tr>
		<td style="padding:0" valign="top">

		<table border="0" cellpadding="0" cellspacing="0">
		<tbody><tr>
			<td>
			<table border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr><td>
			<select name="abtus_ph_id" id="abtus_ph_id" class="a_f rf" onchange="check_dual_title('ad_profile');" style="width:276px">
            <option value="">Select Heading</option>
      		<?php
            $hdsql=mysql_query("select * from profile_heading where ph_status='1'");
			while($hdrow=mysql_fetch_object($hdsql))
			{
			?>
            <option value="<?php echo $hdrow->ph_id;?>" <?php if($abtus_ph_id==$hdrow->ph_id){ ?>  selected="selected" <?php } ?>><?php echo $hdrow->ph_title;?></option>
            <?php } ?>
            </select>
			</td><td> <div id="select2"></div><div><input name="p_sub_title" size="25" id="text1" style="display:none;width:175px;" class="a_f rf ml8" type="text"></div> </td></tr> </tbody></table>
			<input value="Select Heading" id="idtxt" name="p_title" type="hidden">
			</td>
		</tr>
		</tbody></table>

		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="right" valign="bottom">
		<input name="abtus_wc_id" id="abtus_wc_id" value="<?php echo $row_wc->wc_id; ?>" type="hidden">
	<span id="pf_save" style="display:none;float:right;margin-left:15px;margin-top:14px;"><img src="images/loading.gif" alt="" border="0" width="16" height="11"></span>
		<input name="btnAdd" value="Save" class="saps mt5" id="btnAdd" type="submit">
		</td>
	</tr>
	<tr>
		<td style="padding-top:0px;padding-bottom:0px"><div class="z2" style="display: none; margin-left: 37px; padding-left: 15px;" id="delete_smallimg"><a style="text-decoration:none;text-align: center;" href="javascript:delete_smallimg()"><font size="1px"><b>remove</b></font></a></div></td>
	</tr>
	<tr>
		<td colspan="4" align="left" valign="top">
        <span class="label" style="font-weight:bold"><span>*</span>&nbsp;Profile Description:</span>
 <textarea aria-hidden="true" name="abtus_desc" rows="10" cols="80" id="abtus_desc" class="a_f rf" style="width: 100%; height: 200px; display: block;" onKeyUp="descount();">
 <?php echo $abtus_desc;?></textarea>
        
		<div class="max f11 tlx"><font id="Charcount" color="#ff8000"><span id="cn">0</span> character (maximum of 4000)</font>&nbsp;character(s)</div></td>
    	</tr>
	</tbody></table>
	<div class="clb"></div>
	</div>


	</form>
	</div></div>

            </div></div>
		<div class="c3">&nbsp;</div></div>
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>