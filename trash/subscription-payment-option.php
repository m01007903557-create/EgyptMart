<?php
include "common.php";

$_SESSION['last_page']="subscription-payment-option.php?id=".$_GET['id'];

if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];


$id=substr($_GET['id'],5);

$sql="select * from membership_plan where md5(mp_id)='".$id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/credit_subs01.css" type="text/css" rel="stylesheet">
<link href="css/AutoSuggestBox.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->

<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>

</head>
<body>
<div id="imgtrailer" style="position:absolute; z-index:4;visibility:hidden;">
	<img src="images/loading.gif" height="32" width="32">
</div>

		<!--main div:start-->
<div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->

	<?php include "includes/header_new.php"; ?>
      
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<!-- Header End Here::-->

	
	
<style type="text/css">
.ui-widget-content {background: none repeat-x scroll 50% 50% #FFFFFF;border: 1px solid #AAAAAA;color: #222222;padding:0 0 2px}
.ui-menu {display: block;float: left;list-style: none outside none;margin: 0;}
.ui-menu .ui-menu-item {background-color: #FFFFFF;cursor: pointer;list-style-type: none;}
.ui-menu .ui-menu-item a {width:auto !important;color: #000000;cursor: pointer;display: block;font-family: arial;font-size: 14px;font-weight: normal;list-style-type: none; padding: 1px 4px;text-decoration: none;cursor:pointer;}
.ui-menu .ui-menu-item a.ui-state-hover,
.ui-menu .ui-menu-item a.ui-state-active {background: none repeat scroll 0 0 #0095F9;color: #FFFFFF;}
.ui-menu .ui-placeholder-input{margin-left:0px;margin-top:0px;color: #cccccc;}
.labelContdet{width:100px;color:#313131;padding-top:10px;font-weight:bold;font-size:12px}
</style>
			

<div class="m_pkgdtl"> 	
	 <h2>Order Summary</h2>
		<div class="m_pkgdtl-inner">
        
        
	
			<p></p><table class="m_tmt" border="0">
			<tbody>
            <tr>
            <td class="m_pkgdetails"><?php echo $row->mp_name; ?> - <?php echo $row->mp_credits; ?> Credits</td>
            <td class="m_pkgdetails" width="10">:</td>
            <td class="m_pkgdetails m_tdpl"><span class="WebRupee"><?php echo getCurrencySymbol(); ?></span> <?php echo $row->mp_amount; ?></td>
            </tr>
            <tr>
            <td class="m_stax">Service Tax (12.36%)</td>
            <td class="m_stax" width="10">:</td>
            <td class="m_stax m_tdpl"><span class="WebRupee"><?php echo getCurrencySymbol(); ?></span> <?php echo number_format(($row->mp_amount*12.36)/100,2); ?></td></tr>
			<tr>
            <td class="m_pricefnl">Total Amount Payable</td>
            <td class="m_pricefnl" width="10">:</td>
            <td class="m_pricefnl m_tdpl"><span class="WebRupee"><?php echo getCurrencySymbol(); ?>&nbsp;</span> <?php $tot=$row->mp_amount+(($row->mp_amount*12.36)/100); echo number_format($tot,2); ?></td>
            </tr>
			</tbody></table>

</div>
</div>


	<input type="hidden" id="usr" name="usr" value="<?php echo $uid; ?>" />
	<input type="hidden" id="mp" name="mp" value="<?php echo $row->mp_id; ?>" />

<div class="m_ca2ndstep cpa1">
 	<div class="m_crtop">
		<div class="m_fst1s cpa1 m_crtophd">
		  <p class="m_spimg m_1nons cpa1"></p>Select Subscription Plan
		</div>
		 <p class="m_spimg m_arwmiddle cpa1"></p>
		<div class="m_fst2s cpa1 m_crtophds">
		  <p class="m_spimg m_2s cpa1"></p>Choose Payment Option
		</div>
	</div>


<div class="m_paymentgateway" style="text-align:center;padding-top:10px;">

<?php include "paymentgateway/api.php";?>
<!--  	<input value="Credit Card" name="credit_card" id="credit_card" class="m_paycard" type="submit">
		 
	<input value="Debit Card" name="debit_card" id="debit_card" class="m_paycard" type="submit">
   
	<input value="Net Banking" name="net_banking" id="net_banking" class="m_paycard" type="submit"> -->
</div></div>

<div class="m_contactdetail" id="kelly" style="display:block;">

	<h3>Contact Information:</h3>
    <?php
	
		$sql_usr="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$uid."' and bnsprof_status='1'";
		$res_usr=mysqli_query($con, $sql_usr);
		$row_usr=mysqli_fetch_object($res_usr);
	
	?>    
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
 <tbody><tr>
   <td><table border="0" cellpadding="0" cellspacing="0">
			<tbody><tr>
   				<td class="m_formtxt" valign="top" width="75">
					Your Name:
				</td>
				<td>
                <select name="salute" size="1" style="width:43px; padding:3px 0px;height:25px; margin:0px;" class="m_forminput" tabindex="1" readonly="readonly">
                <option value="Mr." <?php if(user_info($uid,'name_prefix')=="Mr."){ ?>selected="selected"<?php } ?>>Mr.</option>
				<option value="Ms." <?php if(user_info($uid,'name_prefix')=="Ms."){ ?>selected="selected"<?php } ?>>Ms.</option>
				<option value="Mrs." <?php if(user_info($uid,'name_prefix')=="Mrs."){ ?>selected="selected"<?php } ?>>Mrs.</option>
				<option value="Dr." <?php if(user_info($uid,'name_prefix')=="Dr."){ ?>selected="selected"<?php } ?>>Dr.</option>
				</select>
                <input name="first_name" size="10" style="width:177px; " class="m_forminput" onfocus="fn_subs()" onclick="fnset_subs()" onblur="fnset_subs()" value="<?php echo ucfirst(user_info($uid,'fname'))." ".ucfirst(user_info($uid,'lname')); ?>" tabindex="2" type="text" readonly="readonly"/>
    				</td>
  			</tr>
			<tr>
				<td class="m_formtxt" valign="top">
					Country:
				</td>
				<td>
					<input aria-haspopup="true" aria-autocomplete="list" role="textbox" placeholder="" name="country_name" id="country_name" autocomplete="off" value="<?php echo get_country_name($row_usr->country); ?>" size="40" style="width:223px;" onblur="coset_subs()" onclick="coset_subs()" class="m_forminput ui-autocomplete-input" tabindex="4" type="text" readonly="readonly"/>

				</td>
			</tr>
			<tr>
				<td class="m_formtxt" valign="top">
				Address:</td><td><input name="add1" value="<?php if($row_usr->bnsprof_address1!=''){ echo $row_usr->bnsprof_address1.", "; } if($row_usr->bnsprof_address2!=''){ echo $row_usr->bnsprof_address2; } ?>" size="40" style="width:223px;" onblur="adset_subs()" onclick="adset_subs()" class="m_forminput" tabindex="8" type="text" readonly="readonly"/>
				</td>
     			</tr>
</tbody></table>
   </td>
   <td>
<table border="0" cellpadding="0" cellspacing="0">
			<tbody><tr>
				<td class="m_formtxt" valign="top">
				E-mail:	</td><td><input name="email" value="<?php echo user_info($uid,'email'); ?>" size="40" style="width:243px;" onclick="emset_subs()" onblur="emset_subs()" class="m_forminput" tabindex="3" type="text" readonly="readonly"/>
				</td>
     			</tr>
			<tr>
			   <td class="m_formtxt1" valign="top">
					Location:</td><td><table border="0" cellpadding="0" cellspacing="0" width="100%">
 			    <tbody><tr>
				<td class="cpcart-nn">City</td>
				<td class="cpcart-nn">State</td>
				<td class="cpcart-nn">Postal Code</td>
  			   </tr>
 			    <tr>
    				<td>
					<input aria-haspopup="true" aria-autocomplete="list" role="textbox" placeholder="" name="txtCity" size="11" id="txtCity" autocomplete="off" onblur="ciset_subs()" onclick="ciset_subs()" value="<?php echo get_city_name($row_usr->bnsprof_city); ?>" tabindex="5" class="m_forminput ui-autocomplete-input" type="text" readonly="readonly"/>
				</td>
   				<td>
					<input name="txtState" size="11" id="txtState" autocomplete="off" value="<?php echo get_state_name($row_usr->bnsprof_state); ?>" onclick="stset_subs()" onblur="stset_subs()" style="width:90px;margin-right: 3px;" tabindex="6" class="m_forminput" type="text" readonly="readonly"/>
				</td>
    				<td>
					<input name="zip" size="12" style="width:51px;" onclick="pcset_subs()" onblur="pcset_subs()" class="m_forminput" tabindex="7" type="text" value="<?php echo $row_usr->bnsprof_zipcode; ?>" readonly="readonly"/>
				</td>
 			</tr>
		</tbody></table>
		</td></tr>

			 <tr>
				<td class="m_formtxt" valign="top">
				Mobile: </td><td>
                <input name="S_cmobile" id="S_cmobile" value="<?php echo get_country_phn_code($row_usr->country); ?>" size="4" style="width:36px;  background-color:#f1f1f1;" class="m_forminput" readonly="READONLY" tabindex="9" type="text"/>
                <input style="width:198px;" id="mobile" name="mobile" value="<?php echo $row_usr->mobile1; ?>" size="25" maxlength="16" class="m_forminput" onfocus="mo_subs()" onblur="moset_subs()" onclick="moset_subs()" tabindex="10" type="text" readonly="readonly"/>
				</td>
     			</tr>
</tbody></table>
</td>
 </tr>
</tbody></table>
</div>

 <div class="cpm2"><!-- clear:both --></div>
<img src="images/z_002.gif" height="10" width="1">

 <div class="cpm2"><!-- clear:both --></div>
<!-- payment page End here -->
  
</div> <style>.redirect{color:#0000ff; text-decoration:none;}
		.redirect:hover{color:#ff0000; text-decoration:underline; cursor:pointer;}</style>
		<div class="c3">&nbsp;</div>
		<!--footer:start-->
	<?php include 'includes/footer.php';?>