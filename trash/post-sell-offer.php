<?php
include "common.php";

$_SESSION['last_page']="post-sell-offer.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	//$_SESSION['request_url'] =  $_SERVER['REQUEST_URI'];  	
	header("Location:sign-in.php");
}
$uid=$_SESSION['uid_indm'];


if(isset($_SESSION['pc_id'])){	$pc_id=$_SESSION['pc_id'];	unset($_SESSION['pc_id']); }else{ $pc_id=""; }
if(isset($_SESSION['so_pc_id'])){	$so_pc_id=$_SESSION['so_pc_id'];	unset($_SESSION['so_pc_id']); }else{ $so_pc_id=""; }
if(isset($_SESSION['so_service'])){	$so_service=$_SESSION['so_service'];	unset($_SESSION['so_service']); }else{ $so_service=""; }
if(isset($_SESSION['so_description'])){	$so_description=$_SESSION['so_description'];	unset($_SESSION['so_description']); }else{ $so_description=""; }
if(isset($_SESSION['so_validity'])){	$so_validity=$_SESSION['so_validity'];	unset($_SESSION['so_validity']); }else{ $so_validity=""; }

class addSaleOffer
{
	var $msg;
	var $so_usr_id;
	var $main_cat;
	var $pc_id;
	var $so_pc_id;
	var $so_service;
	var $so_description;
	var $so_preferred_buyer_location;
	var $so_validity;
		
	function __construct($so_usr_id, $main_cat, $pc_id, $so_pc_id, $so_service, $so_description, $so_preferred_buyer_location, $so_validity)
	{	
		$this->so_usr_id=$so_usr_id;
		$this->main_cat=$main_cat;
		$this->pc_id=$pc_id;
		$this->so_pc_id=$so_pc_id;
		$this->so_service=$so_service;
		$this->so_description=$so_description;
		
		$this->so_preferred_buyer_location=$so_preferred_buyer_location;
		
		$this->so_validity=$so_validity;

		$_SESSION['main_cat']=$this->main_cat;
		$_SESSION['pc_id']=$this->pc_id;
		$_SESSION['so_pc_id']=$this->so_pc_id;
		$_SESSION['so_service']=$this->so_service;
		$_SESSION['so_description']=$this->so_description;
		$_SESSION['so_preferred_buyer_location']=$this->so_preferred_buyer_location;
		$_SESSION['so_validity']=$this->so_validity;		
	}
	function checkBadWord($param)
	{
		$valid=true;
		$sqlrpl = "select bd_word from bad_word";
		$resrpl = mysql_query($sqlrpl);
		while($rowrpl = mysql_fetch_object($resrpl))
		{		
			$letters[] = strtoupper($rowrpl->bd_word);
		}
		foreach($letters as $val)
		{
			$pos = strpos($param, $val);
			if ($pos !== false)
			{
				$valid=false;
			} 
		}
		
		return $valid;
	}
	function valid()
	{
		//include "language.php";
		$valid=true;
		if($this->main_cat=="")
		{
			$this->msg='<font color="#FF0000">Kindly select Main Category.</font>';
			$valid=false;
		}
		else if($this->pc_id=="")
		{
			$this->msg='<font color="#FF0000">Kindly select Category.</font>';
			$valid=false;
		}
		else if($this->so_pc_id=="")
		{
			$this->msg='<font color="#FF0000">Kindly select Sub-Category.</font>';
			$valid=false;
		}
		else if($this->so_service=="")
		{
			$this->msg='<font color="#FF0000">Kindly enter Products / Services you want to Sell.</font>';
			$valid=false;
		}
		else if($this->so_service!="" && $this->checkBadWord(strtoupper($this->so_service))==false)
		{
			$this->msg= "<font color='#FF0000'>You can't post this Product / Service Name. It contains some Bad words.</font>";
			$valid=false;
		}
		else if($this->so_description == "")
		{
			$this->msg= '<font color="#FF0000">Kindly describe your Products / Services in detail.</font>';
			$valid=false;
		}
		else if($this->so_description!="" && $this->checkBadWord(strtoupper($this->so_description))==false)
		{
			$this->msg= "<font color='#FF0000'>You can't post this Product / Services in detail. It contains some Bad words.</font>";
			$valid=false;
		}
		
		return $valid;
	}
	
	function add()
	{	
		if($_FILES["so_pic"]["name"] != "")		
		{
			if ($_FILES["so_pic"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["so_pic"]["error"] . "<br />";
			}
			else
			{
				$this->so_pic='so-'.rand(0,9999).trim(addslashes($_FILES['so_pic']['name']));	
			
				$ds = move_uploaded_file($_FILES["so_pic"]["tmp_name"], "upload/sale_offer/".$this->so_pic) or die('error');	
							
				$sql="insert into sale_offer
				set
					so_usr_id='".$this->so_usr_id."',
					so_pc_id='".$this->so_pc_id."',
					so_service ='".$this->so_service."',
					so_description ='".$this->so_description."',
					so_preferred_buyer_location ='".$this->so_preferred_buyer_location."',
					so_validity ='".$this->so_validity."',
					so_pic ='".$this->so_pic."',
					so_approval_status='0',
					so_posting_date=now(),
					so_updated_date=now()";
			
				mysql_query($sql) or die(mysql_error());
			
				unset($_SESSION['main_cat']);
				unset($_SESSION['pc_id']);
				unset($_SESSION['so_pc_id']);
				unset($_SESSION['so_service']);
				unset($_SESSION['so_description']);
				unset($_SESSION['so_preferred_buyer_location']);
				unset($_SESSION['so_validity']);
			
				$this->msg='<font color="#009900">Sale Offer posted successfully.</font>';
			}
		}
		else
		{
			$imgFile="";
			$sql_tsi="select * from temp_selloffer_image where tsi_usr_id='".$this->so_usr_id."'";
			$res_tsi=mysql_query($sql_tsi);
			if(mysql_num_rows($res_tsi))
			{
				$row_tsi=mysql_fetch_object($res_tsi);
				$imgFile=$row_tsi->tsi_image;
				mysql_query("delete from temp_selloffer_image where tsi_usr_id='".$this->so_usr_id."'");
			}

			
			$sql="insert into sale_offer
				set
					so_usr_id='".$this->so_usr_id."',
					so_pc_id='".$this->so_pc_id."',
					so_service ='".$this->so_service."',
					so_description ='".$this->so_description."',
					so_preferred_buyer_location ='".$this->so_preferred_buyer_location."',
					so_validity ='".$this->so_validity."',
					so_pic ='".$imgFile."',
					so_approval_status='0',
					so_posting_date=now(),
					so_updated_date=now()";
					
			mysql_query($sql) or die(mysql_error());
		
			unset($_SESSION['main_cat']);
			unset($_SESSION['pc_id']);
			unset($_SESSION['so_pc_id']);
			unset($_SESSION['so_service']);
			unset($_SESSION['so_description']);
			unset($_SESSION['so_preferred_buyer_location']);
			unset($_SESSION['so_validity']);
			
			$this->msg='<font color="#009900">Sale Offer posted successfully.</font>';
		}
	}	
}
if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}
if(isset($_POST['submitSaleOffrButt']))
{
$typeofselection = $_POST['typeofselection'];
 $keywordsFilter = $_POST['keywordsFilter1'];
     $valid = false ;
  if($typeofselection){
     $valid = true ;
 if($keywordsFilter=="")
{
	$data[0]="0";
	$data[1]='Kindly enter Keyword.';
	$valid=false;
}


 $searchedproducts = $_SESSION['searchedproducts'];

if(!$searchedproducts && !array_key_exists($keywordsFilter,$searchedproducts))  {
	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
}
     $keywordsFilter =  explode(">>",$keywordsFilter)   ;

$keywordsFilter1 = end($keywordsFilter);
$tnd_pc_id = $searchedproducts[$keywordsFilter1];
$_POST['so_pc_id'] = $tnd_pc_id;
$_POST['pc_id'] = $searchedproducts[$keywordsFilter[1]];
$_POST['main_cat'] = $searchedproducts[$keywordsFilter[0]];
if(!$tnd_pc_id){
 	$data[0]="0";
	$data[1]='No category found with given keywords';
	$valid=false;
}

}
	$adn=new addSaleOffer(addslashes(trim($_POST['so_usr_id'])), addslashes(trim($_POST['main_cat'])), addslashes(trim($_POST['pc_id'])), addslashes(trim($_POST['so_pc_id'])),  addslashes(trim($_POST['so_service'])),addslashes(trim($_POST['so_description'])),addslashes(trim($_POST['so_preferred_buyer_location'])),addslashes(trim($_POST['so_validity'])));
	 

	$key_cat_id = $_POST['so_pc_id'];
	$uid = $_SESSION['uid_indm'];

	$query = "SELECT * FROM buylead_alert_category WHERE bac_pc_id='$key_cat_id' AND bac_usr_id='$uid'";	
	$r=mysql_query($query);	
	if(mysql_num_rows($r) == 0){		
		$SQL_BUY_ALERT="INSERT  INTO buylead_alert_category SET 
										  bac_usr_id=".$uid.",
										  bac_pc_id=".$key_cat_id.",
										  bac_updated_date=now()";
		$r=mysql_query($SQL_BUY_ALERT) or die('Error in query while saving');
	}

	if($adn->valid() || $valid)
	{
		$adn->add();
		$sql_exist="select * from buylead_alert_category where bac_usr_id='".$_SESSION['uid_indm']."' AND bac_pc_id='". $_POST['so_pc_id']."'";
		$res12=mysql_query($sql_exist);
		if(mysql_num_rows($res12)==0){
			$sql_ins="insert into buylead_alert_category
				set
					bac_usr_id='".$_SESSION['uid_indm']."',
					bac_pc_id='". $_POST['so_pc_id']."',
					bac_updated_date=now()";

			mysql_query($sql_ins);
		}
  	header("Location:post-sell-offer-res.php");
        exit;


	}
	else
	{
		$_SESSION['msg']=$adn->msg;
      header("Location:post-sell-offer.php");    
        exit;


	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- meta start -->
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/eto-post-sell.css" type="text/css" rel="STYLESHEET">
<link href="css/my-v1.css" type="text/css" rel="stylesheet">
<link href="css/c.css" type="text/css" rel="STYLESHEET">
<link href="css/jquery.css" type="text/css" rel="stylesheet">
<link href="css/ui.css" rel="stylesheet">

<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/dir-new.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
	showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
});

function showCategory()
{
	var pc_id=document.getElementById('main_cat').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showSubcat();	}); 
}
function showSubcat()
{
	var id=document.getElementById('pc_id').value;
	$.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#so_pc_id').html(data);	}); 
}
function validSaleOffer()
{
  	var typeofselection=document.getElementById('typeofselection');
	var keywordsFilter1=document.getElementById('keywordsFilter1');
	var main_cat=document.getElementById('main_cat');
	var pc_id=document.getElementById('pc_id');
	var so_pc_id=document.getElementById('so_pc_id');
	var so_service=document.getElementById('so_service');
	var so_description=document.getElementById('so_description');

	var message="";
    var valid=true;
	  var valid=true;
   //  alert(typeofselection.value);
    typeofselectionvalue = typeofselection.value *1;
	if(typeofselectionvalue==0){
	if(main_cat.value=='')
	{
		message="Kindly select Main Category.";
		main_cat.focus();
		valid=false;
	}
	else if(pc_id.value=='')
	{
		message="Kindly select Category.";
		pc_id.focus();
		valid=false;
	}
	else if(so_pc_id.value=='')
	{
		message="Kindly select Sub-Category.";
		so_pc_id.focus();
		valid=false;
	}
    }
     else if(typeofselectionvalue && keywordsFilter1.value=='')    {

	{
		message="Kindly enter valid Search for category";
		keywordsFilter1.focus();
		valid=false;
	}

    }
	else if(so_service.value=='')
	{
		message="Kindly enter Products / Services you want to Sell.";
		so_service.focus();
		valid=false;
	}
	else if(so_description.value == '')
	{
		message="Kindly describe your Products / Services in detail.";
		so_description.focus();
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
<style>
#login_frm1
{
	border:1px solid #6F0000;color:#fff;text-decoration:none;font-size:14px; font-weight:bold; padding:5px;text-align:center;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;background-color:#DF0000;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');background:-webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));background:-moz-linear-gradient(top,  #DF0000,  #DF0000);cursor:pointer;font-family:Arial, Helvetica, sans-serif
}
</style>

</head>

<body class="search-show-box">
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        <br><br>
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>


<div class="inner_wrapper">
    	<?php include "includes/header_menu.php"; ?>
	
	<!--left navigation:start-->
	<div class="f1 w61n tb lh ml m2" id="lnav" style="display: block;">
		<ul class="nln1" style="margin: 0px; padding: 0px;">
			<li>
			<h2>Trade Offers</h2>
			</li>
			<li style="border-bottom: medium none;">
			<h3>Buy Requirement</h3>
			<!--<ul>-->
			</li><li class="lp"><a href="post-buy-req.php">»&nbsp;Post a New Buy Requirement</a></li>
			<li class="lp"><a href="manage-buy-requirement.php">»&nbsp;Manage Buy Requirements</a></li>
			<!--</ul>-->
			
			<li style="border-bottom: medium none;">
			<h3>Sell Offers</h3>
			<!--<ul>-->
			</li><li class="lp"><a href="post-sell-offer.php">»&nbsp;Post a New Sell Offer</a></li>
			<li class="lp"><a href="manage-sell-offer.php">»&nbsp;Manage Sell Offers</a></li>
			<!--</ul>-->
			
			<li style="border-bottom: medium none;">
			<h3>Buy Lead Alerts</h3>
			<!--<ul>-->
			</li><li class="lp"><a href="manage-buylead-alert.php">»&nbsp;Manage Buy Lead Alerts</a></li>
			<!--</ul>-->
			
			<li style="border-bottom: medium none; margin-top: 40px;">
			<h2>You may also like to</h2>
			</li>
			<li class="np"><a href="buyleads.php">View Latest Buy Leads</a></li>
		    <li class="np"><a href="sale-offers.php">View Latest Sell Offers</a></li>
			<li class="np"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
			<li class="np"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
		</ul>
	</div>
	<!--left navigation:ends-->
	<div class="w57 b1_m2 f1 wd797" id="ldiv">
		<div style="display: none;" id="hdbord" class=""></div>
		<table id="topstrip" style="text-align: left; display:none" width="100%" border="0" cellpadding="0" cellspacing="0">
		<tbody><tr>
			<td class="sprite l_strip fl">
			</td>
			<td class="sprite cntr_strip fl">
				<table style="text-align: left;" width="100%" border="0" cellpadding="0" cellspacing="0">
				<tbody><tr>
					<td class="sprite icon1">
					</td>
					<td class="vm" style="padding: 6px 0pt; color:#5d5858; line-height:13px" width="234" align="left">
						<font style="color:#0056c0; font-size:15px; line-height:15px; font-weight:bold;">Tell Us About Your Product</font><br>
						 Complete this form and let the buyers<br> know about your product / services.
					</td>
					<td class="sprite arrow" align="left">
					</td>
					<td class="sprite icon2">
					</td>
					<td class="vm" style="padding: 6px 0pt; color:#5d5858; line-height:13px" width="229">
						<font style="color:#0056c0; font-size:15px; font-weight:bold; line-height:15px;">Receive Enquiries</font><br>
						 Receive business enquiries from global<br> buyers via email or phone.
					</td>
					<td class="sprite arrow" align="left">
					</td>
					<td class="sprite icon3">
					</td>
					<td class="vm" style="padding: 6px 0pt; color:#5d5858; line-height:13px" width="221">
						<font style="color:#0056c0; font-size:15px; font-weight:bold; line-height:15px;">Increase Revenue</font><br>
						Get More business easily and quickly<br> with increased revenue.
					</td>
				</tr>
				</tbody></table>
			</td>
			<td class="sprite r_strip" align="right">
			</td>
		</tr>
		</tbody></table>
		<div id="div2" style="display:block;">
			<div>
				<img src="post-sell_offer_files/zero.gif" width="1" height="19">
			</div>
			<table width="100%" align="center">
			<tbody><tr>
				<td>
					<div align="left">
						<div class="tw2l fl" id="formmain" style="margin-left:8px;background-color:#FAF4FF">
							<div class="" id="lgn1">
								<p class="c-1 g2 fs bo1">Post Business Ads FREE<span class="p6 q4 tm1 cbc fsz1"><i class="co">*</i>
									Required Information</span>
								</p>
								<p class="ts1 ptp">
								</p>
							</div>
							<div>
								<form method="post" name="postForm1" action="" onsubmit="return validSaleOffer();" enctype="multipart/form-data">
									
									
									<div id="error_msg" style="">
                                    <?php echo $msg; ?>
									</div>

                                    <input type="hidden" id="so_usr_id" name="so_usr_id" value="<?php echo $_SESSION['uid_indm']; ?>"/>

        <script type="text/javascript">
function searchcat()
{
	$("#scs").removeClass("tabclose").addClass("tabopen");
	$("#bcs").removeClass("tabopen").addClass("tabclose");
        $('#typeofselection').val(1);
	$(".bcc").css("display","none");
	$(".scc").removeAttr('style');
}
function beowswcat()
{
	$("#bcs").removeClass("tabclose").addClass("tabopen");
	$("#scs").removeClass("tabopen").addClass("tabclose");
    $('#typeofselection').val(0);
	$(".scc").css("display","none");
	$(".bcc").removeAttr('style');;
}

</script>
         <input type="hidden" value="0"  id="typeofselection" name="typeofselection" />

              <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"><tbody>
 <tr><!--<td valign="TOP" width="19"><img src="images/zero.gif" height="6" width="1"><br><img src="images/11.gif" height="15" width="19"></td>--><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
 <tr>
 <td class="tabclose" onclick="searchcat()" id="scs" width="152">Search Categories</td>
 <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
 <td class="tabopen" onclick="beowswcat()" id="bcs" width="155">Browse Categories</td>
 <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
 </tr>
 </tbody></table></td></tr></tbody></table>
									<table class="frm mt5" width="100%">
									<tbody>
                                     <tr class="scc" id="r0" style="display: none;">
                                      	<td valign="middle" width="30%">
											<p class="pd15">
												 <b style="font-size:13px;"><font color="#E95801">Enter product keywords to find a category</font></b>
											</p>
										</td>
										<td valign="TOP">



  <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" name="keywordsFilter1" id="keywordsFilter1" style="width: 450px;float: left;" type="text" maxlength="60" size="33" >
  </td>
  </tr>
   <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>


<script type="text/javascript">
$(document).ready(function($113){
	lostFocus();
	$113('#keywordsFilter1').unbind().live('keyup',function() {
		var type11='Products';
		$113("#keywordsFilter1").autocomplete("autocomplete.php", {
			selectFirst: true,
			extraParams: {type:type11},
			width: 407
		})
		.result(function(event, data, formatted) {
 			$("input#keywordsFilter1").val(data);
		});
	});
});
</script>


                                    <tr id="r0" style="height: 48px;" class="bcc">
										<td valign="middle" width="40%">
											<p class="pd15">
												<i>*</i><b>Main Category:</b>
											</p>
										</td>
										<td valign="TOP">
											
											<select class="bd4 hw6 mr3 htb" id="main_cat" name="main_cat" style="height:30px;" onchange="showCategory()">
                                            <option value="">--Select Category--</option>
                                            <?php
												$sql_pc="select * from product_category_arabyos where pc_parent_id='0' and pc_status='1'";
												$res_pc=mysql_query($sql_pc);
												while($row_pc=mysql_fetch_object($res_pc)){
											?>
					                       	<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
                    					    <?php	}	?>
                                            </select>
										</td>
									</tr>
                                    <tr id="r1" style="height: 48px;" class="bcc">
										<td valign="middle" width="40%">
											<p class="pd15">
												<i>*</i><b>Category:</b>
											</p>
										</td>
										<td valign="TOP">
											
											<select class="bd4 hw6 mr3 htb" id="pc_id" name="pc_id" style="height:30px;" onchange="showSubcat()">
                                            <option value="">--Select Category--</option>
                                            <?php
												$sql_pc="select * from product_category_arabyos where pc_parent_id!='0' and pc_parent_id='".$main_cat."' and pc_status='1'";
												$res_pc=mysql_query($sql_pc);
												while($row_pc=mysql_fetch_object($res_pc)){
											?>
					                       	<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
                    					    <?php	}	?>
                                            </select>
                                            <select class="bd4 hw6 mr3 htb" id="so_pc_id" name="so_pc_id" style="height:30px;">
                                            <option value="">--Select Sub-Category--</option>
                                            <?php
												$sql_spc="select * from product_category_arabyos where pc_parent_id='".$pc_id."' and pc_status='1' and pc_parent_id!='0'";
												$res_spc=mysql_query($sql_spc);
												while($row_spc=mysql_fetch_object($res_spc)){
											?>
											<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$so_pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>                        
                                            <?php	}	?>
                                            </select>
										</td>
									</tr>
                                    <tr id="r2" style="height: 48px;" >
										<td valign="TOP" width="40%">
											<p class="pd15">
												<i>*</i><b>Products / Services you want to Sell:</b>
											</p>
											<img src="post-sell_offer_files/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
											<input name="so_service" id="so_service" style="width:450px;" class="bd4 hw6 mr3 htb" maxlength="90" value="<?php echo $so_service; ?>"/>
											<div class="displayoff" id="hlp" style="line-height:14px;height:14px;"></div>
										</td>
									</tr>
									<tr id="r3">
										<td valign="TOP" width="40%">
											<p class="pd15">
												<i>*</i><b>Describe Your Products /	Services in Detail: </b>
                                                <br />
												<b class="q4"></b><font class="co1" id="Charcount" color="#ff8000">2000</font><b class="fwn cbc">Characters Remaining </b>
											</p>
										</td>
										<td onmouseover="document.getElementById('tt2').style.display='block';" onmouseout="document.getElementById('tt2').style.display='none';" valign="TOP">
											<div id="lgn6" style="width: 360px; height: 105px;">
												<textarea aria-hidden="true" name="so_description" id="so_description" style="max-width: 4500px;width:450px; height:95px; max-height:95px; display: block;" rows="5" cols="30"></textarea>
											</div>

											
										</td>
									</tr>
							<tr id="r4">
								<td valign="TOP" width="40%">
									<p class="pd15"><b>Location Preferences: </b></p>
								</td>
								<td valign="TOP">
									<div style="vertical-align:bottom">
                       					<input type="radio" id="so_preferred_buyer_location_1" name="so_preferred_buyer_location" value="abroad" /><label style="top:0px;">Abroad Only</label>		
				                        &nbsp;&nbsp;
				                        <input type="radio" id="so_preferred_buyer_location_2" name="so_preferred_buyer_location" value="any" checked="checked"/><label style="top:0px;">Abroad + Domestic</label>
				                        &nbsp;&nbsp;
	                			        <input type="radio" id="so_preferred_buyer_location_3" name="so_preferred_buyer_location" value="domestic"/><label style="top:0px;">Domestic Only</label>
    				                    &nbsp;&nbsp;
                    				    <input type="radio" id="so_preferred_buyer_location_4" name="so_preferred_buyer_location" value="my_city"/><label style="top:0px;">My City Only</label>
                    					    </div>
	
										</td>
									</tr>
                                    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                    <tr>
                                    <td>&nbsp;</td>
                                    <td class="j1"><i class="co fl" style="height:26px;">** </i>Please do not post offers with copied or duplicate content otherwise they will not be approved</td>
                                    </tr>
									<tr>
										<td colspan="2">&nbsp;</td>
									</tr>
									<tr id="r4">
										<td class="pb1 pt2" valign="top">
											<b class="q4"></b><b>Product Picture:</b><br/>
											
                                            (Upload Images in .jpg, .jpeg, .png or .gif file format)
										</td>
										<td class="s pb" align="left">
                                        <table width="100%">
                                        <tr>
                                        	<td>
											<div id="main" class="po-com1">
												
													<script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
				<script type="text/javascript">
					jQuery(function(){
						jQuery('#file_upload').uploadifive({
							'auto'     : true,
							'formData' : {'usr' : '<?php echo $_SESSION['uid_indm']; ?>'},
							'queueID'  : 'queue',
							'debug'    : true,
							'method'   : 'post',
							'uploadScript' : 'ajax-file/addTempSOImg.php',
							'onAddQueueItem' : function(file) {
								//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
								$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
							},
							'onUploadComplete' : function(file,data) {
								showTempPhoto(<?php echo $_SESSION['uid_indm']; ?>);
							}
						});
					});
				</script>
						
			<div style="padding-left:18px;padding-top:5px;" id="img_disp">

			<img src="upload/sale_offer/<?php if($row->so_pic !=''){	echo $row->so_pic;	}else{ echo "no-image.png";	} ?>" id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125">
		
			</div>
							</div>
											<!--<div class="po-com1 m2">(Upload Images in .jpg, .jpeg, .png or .gif file format)</div>-->
											
                                            </td>
                                            <td>
                                       			<div id="drop" style="padding-left:10px;float:right">
										            <input type="file" id="file_upload" name="file_upload"/>
									            </div>
									            <div id="queue"></div>
                                            </td>
                                            <td>
                                       <link rel="stylesheet" href="css/colorbox.css" />
										<script src="js/jquery.colorbox.js"></script>
                                       <script>
											$(document).ready(function(){
											//Examples of how to assign the ColorBox event to elements
											$('.ajax').on('click', function() {
											  $.colorbox({href:$(this).attr('href'), open:true});
											  return false;
											});
											$(".inline").colorbox({inline:true, width:"50%"});
											//Example of preserving a JavaScript event for inline calls.
											$("#click").click(function(){ 
												$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
												return false;
											});
											});
									</script>
            <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">Select from Image Gallery</a></td>
                                            
											</tr>
                                            </table>
										</td>
									</tr>
									</tbody></table>
									<p id="add_dtl" style="padding:5px 13px;color:#0000ff;cursor:pointer;float:right" onclick="javascript:document.getElementById('dtl').style.display='block';this.style.display='none';">
										<b style="color:#0000ff;">+</b> Additional Information
									</p>
									<div style="display:none" id="dtl">
										<table class="frm" width="100%">
										<tbody><tr id="r20">
											<td>
												<b class="q4"></b><b>Validity of your product:</b>
											</td>
											<td class="v">
												<input name="so_validity" value="30"  type="radio">1 Month 
                                                <input name="so_validity" value="90"  type="radio">3 Months
                                                <input name="so_validity" value="365" checked="checked" type="radio">
												1 Year
                                                <span class="cc j1" style="display:block; margin-left:6px">(How long your offer is available.)</span>
											</td>
										</tr>
										</tbody></table>
									</div>
									<br>
									<div style="display: none;" id="mem_reg">
										<div>
											<table class="m2 db1" width="100%">
											<tbody><tr>
												<td class="wh" style="padding-top: 7px;" width="12%">
													<div class="lh2 lf fl q_p9 db1" style="float:left; width: 181px; margin:0 0 0 10px" id="n">
														<p class="ptp q4 fl">
															<label for="ne1" onclick="newusr('new');" class="cr j1 cc"><b class="fsz1 fc3 bo1"><input name="Radio2" value="Value1" id="ne1" class="ml1" onclick="javascript: this.form.frmsubmitbutton.value='newreg';d_ne();" type="RADIO"><input name="action" value="newreg1_submit" type="HIDDEN">New User?</b><br>
															<span class="q5">Enter your Contact Information</span></label>
														</p>
													</div>
												</td>
												<td class="q_p14" style="padding-top: 7px;">
													<div class="lh2 lf fl q_p9 db2" id="e" style="width: 188px; display: block;">
														<p class="ptp q4 fl">
															<label for="ex" class="cr j1 cc"><b class="fsz1 fc3 bo1" style="padding-right:3px;"><input checked="checked" name="Radio1" value="Value1" id="ex" class="ml1" onclick="javascript: this.form.frmsubmitbutton.value='login';d_ex();" type="RADIO">Existing Member?</b><br>
															<span class="q5">Enter your Email and Password</span></label>
														</p>
													</div>
												</td>
											</tr>
											</tbody></table>
										</div>
										<p class="m2">
										</p>
										<div class="off j1 mr4" id="new" style="display:none;">
										<div class="fl z ml10">
											<table class="form" width="348" align="left" cellpadding="0" cellspacing="0">
											<tbody><tr id="r11">
												<td class="v" width="30%">
													<p class="wd1">
													</p>
													<i>*</i><b>Your Name:</b>
												</td>
												<td>
													<select name="salute" style="width:52px;margin-top:0;" onfocus="color('r11');" onblur="clr(document.postForm1);" class="bd4 y vt htb">
														<option value="Mr." selected="selected">Mr. </option>
														<option value="Ms.">Ms. </option>
														<option value="Mrs.">Mrs. </option>
														<option value="Dr.">Dr. </option>
													</select>
													<input value="Rakesh" name="first_name" class="bd4 hw6 vt htb" maxlength="20" style="width: 80px;" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r11');document.getElementById('ttn').style.display='block';" onblur="clr(document.postForm1);document.getElementById('ttn').style.display='none';">
													<div id="ttn" style="display: none; position: absolute; margin:-26px 0 0 231px;*margin:2px 0 0 83px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:0">
															<div class="fieldTipsMsg">
																Please enter your first name
															</div>
														</div>
													</div>
													<input value="Bose" name="last_name" class="bd4 hw6 vt htb" maxlength="20" style="width: 76px;" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r11');document.getElementById('ttln').style.display='block';" onblur="clr(document.postForm1);document.getElementById('ttln').style.display='none';">
													<div id="ttln" style="display: none; position: absolute; margin:-26px 0 0 231px;*margin:2px 0 0 -3px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:0">
															<div class="fieldTipsMsg">
																Please enter your last name
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr id="r12">
												<td class="v" width="32%">
													<b class="q4"></b><b>Company Name:</b>
												</td>
												<td>
													<input value="Marina Infotech Private Limited" name="comp_name" style="width: 224px;" class="bd4 hw6 htb" maxlength="60" onfocus="color('r12');document.getElementById('ttecomp').style.display='block';" onblur="clr(document.postForm1);document.getElementById('ttecomp').style.display='none';">
													<div id="ttecomp" style="display: none; position: absolute; margin:-30px 0 0 231px;*margin:-5px 0 0 -3px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:7px">
															<div class="fieldTipsMsg">
																Enter your company name or the company you are working with
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr id="r13">
												<td class="v" width="30%">
													<i>*</i><b>Your Email Id:</b>
												</td>
												<td>
													<input value="boserakesh61@gmail.com" name="email" style="width: 224px;" class="bd4 hw6 htb" maxlength="60" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r13');document.getElementById('ttemail').style.display='block';" onblur="clr(document.postForm1);document.getElementById('ttemail').style.display='none';">
													<div id="ttemail" style="display: none; position: absolute; margin:-30px 0 0 231px;*margin:-5px 0 0 -3px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:7px">
															<div class="fieldTipsMsg">
																Enter your email id where you want to receive responses from buyers
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr id="r15">
												<td class="v" width="30%">
													<i>*</i><b>Country:</b>
												</td>
												<td>
													
<input name="country_name" class="bd4 hw6 htb" id="S_countryname" style="width: 224px;" onfocus="color('r15');document.getElementById('ttcountyr').style.display='block';ccs();" autocomplete="off" onblur="document.getElementById('ttcountyr').style.display='none';ccs();" value="India" onkeypress="if (event.which == 13) return false;">
 
													<input name="country_iso" value="" id="txtCountry_frm1" type="Hidden">
													<input name="country" value="IN" id="country_frm1" type="Hidden">
													<div id="ttcountyr" style="display: none; position: absolute; margin:-26px 0 0 231px;*margin:2px 0 0 -9px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:0">
															<div class="fieldTipsMsg">
																Enter the country name. eg: India, China etc.
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr>
												<td colspan="2">
													<table>
													<tbody><tr style="display: block;" id="city_state">
														<td class="v" width="100px">
															<b class="q4" style="font-size: 12px;text-align: left;"><b>City/ State:</b>
														</b></td>
														<td>
															<ul style="padding:0 0 0 1px">
																<li style="display:inline" class="fl">
<input name="city_others" class="bd4 hw6 y htb" id="city_others" value="Kolkata" autocomplete="off" maxlength="20" style="width: 95px; float: left; color: rgb(0, 0, 0);" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('city_state');if (this.value=='City'){this.value='';this.style.color='#000';} document.getElementById('ttcity').style.display='block';" onkeyup="ccs();" onblur="clr(document.postForm1); if (this.value=='') {this.value='City';this.style.color='#949494';} this.className='bd4 hw6 y htb'; document.getElementById('ttcity').style.display='none';ccs(); " onclick="this.style.color='#000';">
																<div id="ttcity" style="display: none; position: absolute; margin:2px 0 0 231px;*margin:2px 0 0 125px">
																	<div style="">
																		<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:0">
																		<div class="fieldTipsMsg">
																			Enter the City name eg: Noida etc.
																		</div>
																	</div>
																</div>
																<div id="ttstate" style="display: none; position: absolute; margin:2px 0 0 231px;*margin:2px 0 0 125px">
																	<div style="">
																		<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:0">
																		<div class="fieldTipsMsg">
																			Enter the State name eg: Uttar Pradesh etc.
																		</div>
																	</div>
																</div>
																</li>
																<li style="display:inline" class="fl">
<input class="bd4 hw6 htb" name="state_others" autocomplete="off" id="state_others" value="West Bengal" maxlength="20" style="width: 119px; color: rgb(0, 0, 0);" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('city_state');if (this.value=='State'){this.value='';this.style.color='#000';} document.getElementById('ttstate').style.display='block';" onkeyup="ccs();" onblur="clr(document.postForm1);if (this.value=='') {this.value='State';this.style.color='#949494';} document.getElementById('ttstate').style.display='none'; ccs();" onclick="this.style.color='#000'; this.className='bd4 hw6 htb';"></li>
															</ul>
														</td>
														<input value="" name="city" id="city" type="hidden">
														<input value="" name="state" id="state" type="hidden">
													</tr>
													</tbody></table>
												</td>
											</tr>
											</tbody></table>
										</div>
										<div class="fl z">
											<table class="form" width="335" align="left" cellpadding="0" cellspacing="0">
											<tbody><tr style="" id="r16">
												<td class="v">
													<i>*</i><b>Telephone:</b>
												</td>
												<td>
													<input value="+91" name="ph_country" id="S_ccode_frm1" readonly="readonly" class="bd4 hw6 bgc y htb" style="width: 45px;" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r16');" onblur="clr(document.postForm1);"><input name="ph_area" class="bd4 hw6 y j1 vt htb" id="S_acode_frm1" value="Area Code" maxlength="6" style="width: 60px; color: rgb(148, 148, 148);" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r16');if (this.value=='Area Code'){this.value='';this.style.color='#000';} document.getElementById('m_op').style.display='block';" onblur="clr(document.postForm1);if (this.value=='') {this.value='Area Code';this.style.color='#949494';}this.className='bd4 hw6 y vt j1 htb';document.getElementById('m_op').style.display='none';" onclick="this.style.color='#000';"><input name="ph_no" value="Phone Number" class="bd4 hw6 j1 vt htb" maxlength="35" style="width: 95px; color: rgb(148, 148, 148);" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r16');mph();if (this.value=='Phone Number'){this.value='';this.style.color='#000';}" onblur="clr(document.postForm1);mph1();if (this.value=='') {this.value='Phone Number';this.style.color='#949494';}this.className='bd4 hw6 vt j1 htb';">
													<div id="m_op" style="display: none; position: absolute; margin:-10px 0 0 220px; *margin:18px 0 0 -10px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:8px">
															<div class="fieldTipsMsg">
																In Phone or Mobile one<br>
																field is compulsory
															</div>
														</div>
													</div>
												</td>
											</tr>
											<tr style="" id="r17">
												<td class="v">
													<b class="q4"></b><b class="wd1">Mobile:</b>
												</td>
												<td>
													<input value="+91" name="S_cmobile" id="S_cmobile_frm1" readonly="readonly" class="bd4 hw6 bgc y htb" style="width: 45px;" onfocus="color('r17');" onblur="clr(document.postForm1);"><input name="mobile" class="bd4 hw6 j1 vt htb" maxlength="40" value="9804525336" style="width: 165px; color: rgb(0, 0, 0);" onfocus="javascript: this.form.frmsubmitbutton.value='newreg'; color('r17');mph();if (this.value=='Mobile Number'){this.value='';this.style.color='#000';}" onblur="clr(document.postForm1);mph1();if (this.value=='') {this.value='Mobile Number';this.style.color='#949494';}this.className='bd4 hw6 j1 vt htb';">
												</td>
											</tr>
											<tr id="r14">
												<td class="v" width="32%">
													<b class="q4"></b><b>Website:</b>
												</td>
												<td>
													<input name="website" class="bd4 hw6 htb" style="width: 220px;" maxlength="80" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r14');document.getElementById('ttwebsite').style.display='block';" onblur="clr(document.postForm1);document.getElementById('ttwebsite').style.display='none';">
													<div id="ttwebsite" style="display: none; position: absolute; margin:-30px 0 0 227px;*margin:-5px 0 0 -3px">
														<div style="">
															<img src="post-sell_offer_files/tips_corner.gif" style="z-index:99;position:absolute;top:7px">
															<div class="fieldTipsMsg">
																Enter your company website, to let the buyers connect to you. eg: <strong>www.xyz.com
															</strong></div><strong>
														</strong></div><strong>
													</strong></div><strong>
												</strong></td>
											</tr>
											<tr style="" id="r18">
												<td class="v vt pt15">
													<i>*</i><b>Enter the code: </b>
												</td>
												<td>
													<table>
													<tbody><tr>
														<td>
															<input name="captcha_text" class="bd4 hw6 htb" style="width: 90px;" onfocus="javascript: this.form.frmsubmitbutton.value='newreg';color('r18');" onblur="clr(document.postForm1);">
															<input maxlength="6" size="15" name="captcha_ref" value="3714.1391002797.3617" type="hidden">
														</td>
														<td>
															<script type="text/javascript">document.write('<IMG SRC="'+captcha_url+'" WIDTH="100" HEIGHT="30" onload="setCaptchaRef(document.postForm1);">');</script><img src="post-sell_offer_files/get_captcha.png" onload="setCaptchaRef(document.postForm1);" width="100" height="30">
														</td>
													</tr>
													<tr>
														<td colspan="2" style="color:#3c64b6">
															(To complete registration, enter the code shown on the image.)
														</td>
													</tr>
													</tbody></table>
												</td>
											</tr>
											</tbody></table>
										</div>
									</div>
									<div id="em">
									</div>
									<div id="exis" style="display:block;" class="mr4 z">
									<table class="form" width="99%" align="CENTER">
									<tbody><tr id="r9">
										<td class="v" width="135">

											<i>*</i><b>E-mail Id / Username:</b>
										</td>
										<td>
											<input value="boserakesh61@gmail.com" name="email1" class="bd4 hw6 htb" size="28" style="width: 150px;" onfocus="javascript: this.form.frmsubmitbutton.value='login';color('r9');" onblur="clr(document.postForm1);">
										</td>
										<td class="v">
											<i>*</i><b>Your Password:</b>
										</td>
										<td>
											<input name="usr_pass1" class="bd4 tw4 hw6 htb" style="width: 150px;" size="28" onfocus="javascript: this.form.frmsubmitbutton.value='login';color('r9');" onblur="clr(document.postForm1);" type="password"><a href="forgot-password.php" class="q5">Forgot Password?</a>
										</td>
									</tr>
									</tbody></table>
								</div>
								<div class="a2 pt pb m2">
									<p style="padding:4px;background:#78BBFF;margin:0 auto;width:170px;">
										<input name="newreg" id="newreg_frm1" class="cr bo1 fsz1" style="height: 32px; width: 170px;" value="Submit your Offer" type="SUBMIT">
									</p>
								</div>
							</div>
							<div class="a2 pt pb" id="loginsubmit" style="display: block;"><input name="frmsubmitbutton" value="login" type="hidden"><input name="submitSaleOffrButt" id="login_frm1" class="cr bo1 fsz1" style="height: 32px; width: 170px;" value="Submit your Offer" type="SUBMIT"></div>
						</form>
					</div>
				</div>
			</div>
			
		</td>
	</tr>
	</tbody></table>
</div>
<div class="p_rl" id="slempform" style="display:none;font-family: arial; font-weight:bold; padding: 30px 0px 0px 0px; text-align: center;color: #FF6000;font-size: 16px;height:200px"><nobr>You do not have privilege to access this section</nobr></div>

<div>
	<br>
	<br>
	<br>
</div>







<!-- MY TD ENDS -->
</div>
<!--footer:start-->
<div style="clear:both;">
<br>
<br>
&nbsp;&nbsp;
</div>
</div>
</div>
	<script type="text/javascript">
			var imageBasket = [];
function usePhotoToUpload(id){

 //imageBasket.push(id);
 if(jQuery.inArray(id,imageBasket) != -1){
   
  imageBasket= $.grep(imageBasket, function(value) {
  return value != id;
    });
  }else{
    imageBasket.push(id);
    }
   //alert(imageBasket);
 }
function usePhoto(id)
{
	var tbl='temp_selloffer_image';
	var usr=document.getElementById('so_usr_id').value;
	if(imageBasket.length > 0){  
		id = imageBasket.pop();
	}
	$.post("ajax-file/addNewImgFrmGallery.php", {id:id,usr:usr,tbl:tbl}, function(data){
		$('#cboxClose').click();

		$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="100" width="100"/>');
		
		setTimeout(function (){

		showTempPhoto(usr);

         }, 500);
	});
}
		
function showTempPhoto(usr)
{
	$.get("ajax-file/showTempSaleofferImage.php", {usr:usr},	function(data){
		$("#img_disp").html('');												 
		$("#img_disp").html('<img src="'+data+'" alt="" height="100" width="125"/>');
	});
}
	</script>
<!--footer:start--> 
<?php include 'includes/footer.php';?>