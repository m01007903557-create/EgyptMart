<?php
ob_start();
session_start();
include "common.php";

$_SESSION['last_page']="create-free-website.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");
}
$uid=$_SESSION['uid_indm'];

$sql="select * from user,business_profile where bnsprof_uid=usr_id and usr_id='".$uid."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

/***********************************/
class CreateSupplierWebsite
{
	var $msg;
	var $usr_id;
	var $bnsprof_id;
	var $name_prefix;
	var $fname;
	var $lname;
	var $bnsprof_compname;
	var $city;	
	var $bnsprof_city;
	var $bnsprof_address1;
	
	function __construct($usr_id, $bnsprof_id, $name_prefix, $fname, $lname, $bnsprof_compname, $city, $bnsprof_city, $bnsprof_address1)
	{	
		$this->usr_id=$usr_id;
		$this->bnsprof_id=$bnsprof_id;
		$this->name_prefix=$name_prefix;
		$this->fname=$fname;
		$this->lname=$lname;
		$this->bnsprof_compname=$bnsprof_compname;
		$this->city=$city;
		$this->bnsprof_city=$bnsprof_city;
		$this->bnsprof_address1=$bnsprof_address1;
		
		/*$_SESSION['pc_id']=$this->pc_id;
		$_SESSION['so_pc_id']=$this->so_pc_id;
		$_SESSION['so_service']=$this->so_service;
		$_SESSION['so_description']=$this->so_description;
		$_SESSION['so_validity']=$this->so_validity;*/
	}

	function valid()
	{

		//include "language.php";
		$valid=true;
		if($this->fname == "" || $this->fname == NULL)
		{
			$this->msg='<font color="#FF0000">Kindly enter your first name.</font>';
			$valid=false;
		}
		else if($this->lname == "" || $this->lname == NULL)
		{
			$this->msg='<font color="#FF0000">Kindly enter your lirst name.</font>';
			$valid=false;
		}
		else if($this->bnsprof_compname=='' || $this->bnsprof_compname==NULL)
		{
			$this->msg='<font color="#FF0000">Kindly enter your Company Name.</font>';
			$valid=false;
		}
		else if($this->bnsprof_city=='' || $this->bnsprof_city==NULL || $this->city=='' || $this->city==NULL || $this->city=="Select City")
		{
			$this->msg='<font color="#FF0000">Kindly enter City name.</font>';
			$valid=false;
		}
		else if($this->bnsprof_address1=='' || $this->bnsprof_address1==NULL)
		{
			$this->msg='<font color="#FF0000">Kindly enter Address.</font>';
			$valid=false;
		}

		return $valid;

	}
	
	function add()
	{	
	
		$sql_u="update user
			set
				name_prefix='".$this->name_prefix."',
				fname='".$this->fname."',
				lname='".$this->lname."',
				date=now()
			where
				usr_id='".$this->usr_id."'";

		mysqli_query($con, $sql_u);
		
		
		
		$sql_bp="update business_profile
			set
				bnsprof_compname='".$this->bnsprof_compname."',
				bnsprof_city='".$this->bnsprof_city."',
				bnsprof_address1='".$this->bnsprof_address1."'
			where
				bnsprof_id='".$this->bnsprof_id."'";
				
		mysqli_query($con, $sql_bp);
			
		$this->msg='<font color="#009900">Company created successfully.</font>';
	}
}
if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}
if(isset($_POST['btnSubmit']))
{ 	
	
	$adn=new CreateSupplierWebsite(addslashes(trim($_POST['usr_id'])), addslashes(trim($_POST['bnsprof_id'])), addslashes(trim($_POST['name_prefix'])), addslashes(trim($_POST['fname'])), addslashes(trim($_POST['lname'])), addslashes(trim($_POST['bnsprof_compname'])), addslashes(trim($_POST['city'])), addslashes(trim($_POST['bnsprof_city'])), addslashes(trim($_POST['bnsprof_address1'])));

	if($adn->valid())
	{	
		$adn->add();
		header("Location:my-dashboard.php");
	}
	else
	{
		$_SESSION['msg']=$adn->msg;
		header("Location:create-free-website.php");
	}
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<title><?php echo getSiteTitle(); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
	<meta name="title" content="<?php echo getSiteTitle(); ?>">
	<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
	<meta name="description" content="<?php echo get_page_settings(3); ?>">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

	<link href="css/free-web.css" rel="stylesheet" type="text/css">
	<link href="css/main-v1.css" rel="stylesheet" type="text/css">
    
    <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.autocomplete.css" />
	<script type="text/javascript" src="js/jquery.autocomplete2.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	$("#city").autocomplete("ajax-file/showcity.php", {
		selectFirst: true
	})
	.result(function(event, data, formatted) {
		var dm =	data[0].split(">>");
		$("#city").val(dm[0]);
		$("#state").val(dm[1]);
		$("#bnsprof_city").val(data[1]);
		$("#bnsprof_state").val(data[2]);
//		$("#reset").show();
		//$("input#country_name").attr('disabled','disabled');
	});
});
</script>
<script type="text/javascript">
function validWebsite()
{

	var fname=document.getElementById('fname');
	var lname=document.getElementById('lname');
	var bnsprof_compname=document.getElementById('bnsprof_compname');
	var country=document.getElementById('country');
	var bnsprof_city=document.getElementById('bnsprof_city');
	var city=document.getElementById('city');
//	var bnsprof_state=document.getElementById('bnsprof_state');
//	var country_ph_code=document.getElementById('country_ph_code');
//	var mobile1=document.getElementById('mobile1');
	var bnsprof_address1=document.getElementById('bnsprof_address1');
	
	var message="";
    var valid=true;
	if(fname.value == "" || fname.value == null)
	{
		message="Kindly enter your first name.";
		fname.focus();
		valid=false;
	}
	else if(lname.value == "" || lname.value == null)
	{
		message="Kindly enter your lirst name.";
		lname.focus();
		valid=false;
	}
	else if(bnsprof_compname.value=='' || bnsprof_compname.value==null)
	{
		message="Kindly enter your Company Name.";
		bnsprof_compname.focus();
		valid=false;
	}
	else if(bnsprof_city.value=='' || bnsprof_city.value==null || city.value=='' || city.value==null || city.value=="Select City")
	{
		message="Kindly enter City name.";
		city.focus();
		valid=false;
	}
	else if(bnsprof_address1.value=='' || bnsprof_address1.value==null)
	{
		message="Kindly enter Address.";
		bnsprof_address1.focus();
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
</script>
</head>
<body bgcolor="#ffffff" marginheight="0" marginwidth="0" class="search-show-box">
	
	<div class="hm1 bbc">
	<!-- Header start Here::-->
    <?php include "includes/header_new.php"; ?>
	
    
    
<!--	<div class="bt"><img src="images/zero.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>-->
	<div class="help_script_error" style="display:none" id="error" align="center"><b><?php echo $msg; ?></b><br>
	<br>
	</div>	
	<!--form html start--> 
	<form style="margin:0px;" action="" method="POST" name="ModReg" onsubmit="return validWebsite();">
	<input type="hidden" id="usr_id" name="usr_id" value="<?php echo $uid; ?>" />
	<input type="hidden" id="bnsprof_id" name="bnsprof_id" value="<?php echo $row->bnsprof_id; ?>" />
	<div class="cfw_wrap" style="width:966px;  padding:0px 20px;  margin:0 auto; overflow:hidden; ">
	<div style="position:relative; width:20px; height:24px; top:62px; left: 381px; "><img src="images/arrow.png"></div>
	<div id="con_right" style="float:left;  background-color:#F9F0FF; margin-top:25px; background-repeat:no-repeat; padding:8px 8px 8px 15px;  width:400px; overflow:hidden;">
	<h1 style="font-size:25px; color:#000099; margin-top:0;line-height:30px; border-bottom:1px solid #e7e5f2; padding-bottom:9px;">Create your <span style="font-size:32px;color:#890101;">Free Website</span> in
	single easy step!</h1>
	<h1 style="font-weight:bold; font-size:14px; color:#222222; padding-top:12px; padding-bottom:5px; margin-top:0;">Benefits:</h1>
	<div id="bnfts">
	<ul>
	<li><span style="color:#666666;">Showcase your products</span></li>
	<li><span style="color:#666666;">Get listing in relevant product categories</span> </li>
	<li><span style="color:#666666;">Expand your business</span></li>
	</ul>
	</div>
	</div>
	<div id="con_left" class="f1" style="border:1px solid #F9F0FF; border-radius:8px; padding:10px 15px; width:515px;">
	<div style="width:100%; overflow:hidden;" class="pt10">
	<div class="label f1"><font color="#FF0000">* </font>Your Name:</div>
	<div class="f2" style="width:75%">
	<select tabindex="1" class="f1 p6" style="width:62px;border:1px solid #d7d8dd; border-radius:3px; padding:5px;" id="name_prefix" name="name_prefix">
    <option <?php if($row->name_prefix=="Mr."){	?>selected="selected" <?php } ?> value="Mr.">Mr. </option>
	<option <?php if($row->name_prefix=="Ms."){	?>selected="selected" <?php } ?> value="Ms.">Ms. </option>
	<option <?php if($row->name_prefix=="Mrs."){ ?>selected="selected" <?php } ?> value="Mrs.">Mrs. </option>
	<option <?php if($row->name_prefix=="Dr."){	?>selected="selected" <?php } ?> value="Dr.">Dr. </option>
	</select>
	<input class="txtlname f1 p6" id="fname" name="fname" value="<?php echo $row->fname; ?>" placeholder="" style="border:1px solid #d7d8dd; border-radius:3px;width: 133px; margin-left:5px; " type="text"/>
	<input class="txtlname f2 p6" placeholder="" name="lname" value="<?php echo $row->lname; ?>" id="lname" style="border:1px solid #d7d8dd; border-radius:3px;width: 133px; " type="text"/>
	</div>
	</div>
	<div style="width:100%; overflow:hidden;" class="pt10">
	<div class="label f1"><font color="#FF0000">* </font>Your Email ID:</div>
	<div class="f2" id="my" style="width:75%">
		<input name="email" id="email" value="<?php echo $row->email; ?>" class="txtlname f2 p6" style="border:1px solid #d7d8dd; border-radius:3px;width: 361px;background-color: #EDEDED; " readonly="true" type="text">
	</div>
	</div>
	
	<span id="id_exist" style="display:none;margin-left:121px; padding-left:15px;background-repeat:no-repeat;" font-size="11px" font-family="Arial, Helvetica, sans-serif">
	</span>
	<span id="e2" style="display:none;color: #FF0000;margin-left:121px;" font-size="12px" font-family="Arial, Helvetica, sans-serif"></span><span id="e3" style="display:none;color: #FF0000;font-size:12px;margin-left:121px;font-family:Arial, Helvetica, sans-serif;"></span>
	<!--comp and web-->
	<div style="width:100%; overflow:hidden;" class="pt10">
	<div class="label f1"><font color="#FF0000">* </font>Company Name:</div>
	<div class="f2" style="width:75%">
	<input class="txtlname f2 p6" id="bnsprof_compname" value="<?php echo stripslashes($row->bnsprof_compname); ?>" name="bnsprof_compname" placeholder="" style="border:1px solid #d7d8dd; border-radius:3px;width: 361px; " type="text">
	</div>
	</div>
	<!--
	<div style="width:100%; overflow:hidden;" class="pt10">
	<div class="label f1" >Website:</div>
	<div class="f2" style="width:75%" >
	<div id="web">
	<input type="text" class="txtlname f2 p6" placeholder="Alternate Website URL" name="website" 
	value=''  onblur="webhttpag();" onfocus="webhttp();" id="website" value=""
	onkeypress="http_val(this.value);" onkeyup="http_val(this.value);" style="border:1px solid #d7d8dd; border-radius:3px;width: 361px; "  />
	</div>
	</div>
	</div>-->
	<!--comp and web end-->
	<div style="width:100%; overflow:hidden;" class="pt10">
	<div class="label f1"><font color="#FF0000">* </font>Country:</div>
	<div class="f2" id="xyz" style="width:75%">
	
		<input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" name="country" id="country" value="<?php echo get_country_name($row->country); ?>" class="txtlname f2 p6 ui-autocomplete-input" style="border:1px solid #d7d8dd; border-radius:3px;width: 361px;background-color: #EDEDED; " readonly="true" type="text">
		
	</div>
	<span id="e4" style="display:none;color: #FF0000;font-size:12px;margin-left:121px;font-family:Arial, Helvetica, sans-serif;"></span>
	</div>
	<div style="width:100%; overflow:hidden;" id="ban" class="pt10">
	<div class="label f1"><font color="#FF0000">* </font>City/State:</div>
	<div class="f2" style="width:75%">
	<div id="xyq">
    <input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" name="city" value="<?php if($row->bnsprof_city!=0){ echo get_city_name($row->bnsprof_city); } ?>" placeholder="Select City" id="city" style="border:1px solid #d7d8dd;  border-radius:3px;margin-right:4px;  " class="prd1 p6 f1 city_o ui-autocomplete-input" type="text">
    <input class="ui-autocomplete-input" name="bnsprof_city" id="bnsprof_city" autocomplete="off" value="<?php echo $row->bnsprof_city; ?>" type="hidden">
    </div>
	<div id="xyl">
    <input placeholder="State" name="state" id="state" value="<?php if($row->bnsprof_state!=0){ echo get_state_name($row->bnsprof_state); } ?>" style="border:1px solid #d7d8dd; border-radius:3px;" class="prd2 p6 f2" readonly="true" type="text">
	<input class="ui-autocomplete-input" name="bnsprof_state" id="bnsprof_state" autocomplete="off" value="<?php echo $row->bnsprof_state; ?>" type="hidden">
    </div>
	
	</div>
	</div>
	
	<div class="clr pt10" style="overflow:hidden;">
	<div class="label f1"><font color="#FF0000">* </font>Mobile No.:</div>
	<div id="nano"><input class="f1 p6" name="country_ph_code" id="country_ph_code" value="<?php echo $row->country_ph_code; ?>" style="border:1px solid #d7d8dd; border-radius:3px; margin-left:1px; float:left;  width:65px; background-color: #EDEDED;" readonly="true" type="text"></div>
	<div id="nano1"><input name="mobile1" value="<?php echo $row->mobile1; ?>" id="mobile1" style="border:1px solid #d7d8dd;  border-radius:3px;width: 285px;background-color: #EDEDED; " placeholder="" class="txtlname f2 p6" readonly="true" type="text">
	</div>
	</div>
	
	<div style="width:100%; overflow:hidden;" class="pt10">
	<div class="label f1"><font color="#FF0000">* </font>Address:</div>
	<div class="f2" style="width:75%">
	<input name="bnsprof_address1" value="<?php echo $row->bnsprof_address1; ?>" id="bnsprof_address1" class="txtlname f2 p6" placeholder="" style="border:1px solid #d7d8dd; min-height:32px;  border-radius:3px;width: 361px; " type="text">
	</div>
	</div>
	<!--<div class="pt10 " style="width:100%; overflow:hidden;">
	<div class="label f1" style="line-height:16px;"><font color="#FF0000">* </font>Products or Services:
	<span onmouseover="showhint('Enter main products or services you <b>sell</b> into the fields provided. <br><IMG SRC=/gifs/zero.gif WIDTH=1 HEIGHT=7><br>Add only one product / service name in each box.', this, event, '200px')">[?]</span>
	</div>
	<div class="f2" style="width:75%">
	<input placeholder="Product 1" name="item_name1" id="item_name1" style="border:1px solid #d7d8dd; border-radius:3px;margin-right:3px; " class="prd1 p6 f1" type="text">
	<input placeholder="Product 2" name="item_name2" id="item_name2" style="border:1px solid #d7d8dd; border-radius:3px; margin-left:3px; " class="prd2 p6 f2" type="text">
	</div>
	<div class="f2" style="width:75%">
	<input name="item_name3" id="item_name3" placeholder="Product 3" style="border:1px solid #d7d8dd; border-radius:3px;margin-right:3px;margin-top:8px; " class="prd1 p6 f1" type="text">
	<input placeholder="Product 4" name="item_name4" id="item_name4" style="border:1px solid #d7d8dd; border-radius:3px; margin-left:3px;margin-top:8px; " class="prd2 p6 f2" type="text">
	</div>
	</div>-->

	<div class="pt10" style="width:100%; overflow:hidden;">
	<div class="label f1">&nbsp;</div>
	<div class="f2" style="width:75%">
	<div id="but" align="center"><input class="clr btnbg" style="font-size:16px;width:180px;" value="Create Website Now" name="btnSubmit" id="save_button" type="submit"></div></div>
	</div>
	</div>
	</div>
	</form>
    <br>
	<!--form end-->
	<div><br><br>
	</div>
	</div>
	<!-- Footer Start Here::-->
	<?php include 'includes/footer.php';?>