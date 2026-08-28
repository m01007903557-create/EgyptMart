<?php
include "common.php";

$_SESSION['last_page']="my-settings.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

<!-- meta start -->
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/ps-v-11.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->

<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>

</head>
<body>

	<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->
		<?php include "includes/header_new.php"; ?>
        <br><br>
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<!-- Header End Here::-->
<?php include "includes/header_menu.php"; ?>
<!--left navigation:start-->
<?php include 'includes/left_menu.php';?>		
<!--left navigation:ends-->
<div class="w56b f1 p2b p14 bl ps-ie7"><!--body:start-->
	<div>
		<!--<div class="bc f11">Settings &raquo;</div>-->
		<h1>Privacy Settings</h1>
	</div>

	<div>&nbsp;</div>
	<div class="mt5">
		<p class="f12">Customize your account settings for using <?php echo getWebSiteName(); ?></p>
		<div class="brm">
	<!-- Enquiries main div:starts -->
	<div style="float:right; width:300px;"></div>

	
<!-- Enquiries main div:ends -->
<!--free subscription:start-->
<div class="mp1">
<a name="Ale"></a>
	<div class="mp2">
	<div class="mp3w mp4">Alerts</div>
	</div>

	<div class="mp10" id="cd">
	<!--ISM subscription:start-->
	<div class="ps1 ism f12" style="background-image:url(images/email_settings/service-message.png); background-repeat:no-repeat; background-position:10px 8px;">
	<strong>Important Service Messages</strong><br>The notifications about 
your membership with <?php echo get_page_settings(4);?> help you manage your business in a 
better way. These exclusive messages are mandatory to inform you about 
your business promotion status on <?php echo get_page_settings(4);?> platform.
	<div class="ps2">
	<table align="right" border="0" cellpadding="2" cellspacing="0" width="350">
		<tbody><tr>
		<td align="right" width="63"><span>Email</span>&nbsp;</td>
		<td align="left" width="98">Mandatory</td>
		<td align="right" width="47"><span>SMS</span></td>
		<td width="20"><input id="important_service_messages_sms" value="5" onclick="myPrivacyUpdate(this,'load_5','enable_5')" checked="checked" type="checkbox"></td>
		<td align="left" width="102"><div class="fr lod" id="load_5" style="display:none"><img src="images/loading.gif" alt="" height="11" width="16"></div><label class="enb" id="enable_5">Enabled</label>
		</td></tr></tbody></table>
	</div>
	<div class="clb"></div>
	</div>
	<!--ISM subscription:ends-->

	<!--BUY leads alerts:start-->
	<a name="buy_lead"></a><a name="leads"></a>
	<div class="ps1 bla f12" style="background-image:url(images/email_settings/bulead_alerts.png); background-repeat:no-repeat; background-position:10px 8px;">
	<strong>Buy Leads Alerts</strong><br>Control your email alerts and SMS alerts for the preferred buy requirements from all across the globe.
	<div class="ps2">
	<table align="right" border="0" cellpadding="1" cellspacing="0">
	<tbody><tr>
		
		<td align="right" width="38"><span>Email</span></td>
		<td align="left" width="23"><input id="buy_leads_alerts_email" value="20" onclick="showbuyleademailalert(this,'load_20','enable_20')" checked="checked" type="checkbox"></td>
		<td align="left" width="76"><div class="fr lod" id="load_20" style="display:none"><img src="images/loading.gif" alt="" height="11" width="16"></div><label class="enb" id="enable_20">Enabled</label></td><td align="right" width="51"><span>SMS</span></td>
		<td width="23"><input id="buy_leads_alerts_sms" value="19" onclick="showbuyleadsmsalert(this,'load_19','enable_19')" checked="checked" type="checkbox"></td>
		<td align="left" width="102"><div class="fr lod" id="load_19" style="display:none"><img src="images/loading.gif" alt="" height="11" width="16"></div><label class="enb" id="enable_19">Enabled</label></td>
		</tr></tbody></table>
	</div>
	<div class="clb"></div>
	</div>
	<!--BUY leads alerts:ends-->

	<!--Sell Offer Alerts:start-->
	<a name="selloffer"></a>
	<div class="ps1 tl f12" style="background-image:url(images/email_settings/selloffer_alerts.png); background-repeat:no-repeat; background-position:10px 8px;">
	<strong>Sell Offer Alerts</strong><br>It includes the email alerts for 
the latest trade leads in your preferred categories. You may enable/ 
disable your alerts or manage the subscribed categories.
	<div class="ps2">
	<table align="right" border="0" cellpadding="1" cellspacing="0" width="460">
	<tbody><tr>
		<td align="right" width="38"><span>Email</span>&nbsp;</td>
		<td align="left" width="23"><input id="trade_lead_alerts_email" value="14" onclick="myPrivacyUpdate(this,'load_14','enable_14')" checked="checked" type="checkbox"></td>
		<td align="left" width="76"><div class="fr lod" id="load_14" style="display:none"><img src="images/loading.gif" alt="" height="11" width="16"></div><label class="enb" id="enable_14">Enabled</label></td>
		<td align="right" width="51">&nbsp;</td>
		<td width="22">&nbsp;</td>
		<td align="left" width="102">&nbsp;</td>
		</tr>
		</tbody></table>
	</div>
	<div class="clb"></div>
	</div>
	<!--Sell Offer Alerts:ends-->

	<!--newsletters Alerts:start-->
	<a name="newsletters"></a>
	<div class="ps1 ind f12" style="border-bottom:0px;">
	<strong>Industry Newsletter</strong><br>You may select from an array of
 specialized industry newsletters covering all new trends, opportunities
 and news. These newsletters are sent to you on fortnightly basis.
	<div class="ps2">
		<table align="right" border="0" cellpadding="1" cellspacing="0" width="460">
		<tbody><tr>
		<td align="right" width="38"><span>Email</span>&nbsp;</td>
		<td align="left" width="23"><input id="industry_newsletter_email" value="18" onclick="myPrivacyUpdate(this,'load_18','enable_18')" checked="checked" type="checkbox"></td>
		<td align="left" width="76"><div class="fr lod" id="load_18" style="display:none"><img src="images/loading.gif" alt="" height="11" width="16"></div><label class="enb" id="enable_18">Enabled</label>
		</td>
		<td align="right" width="51">&nbsp;</td>
		<td width="22">&nbsp;</td>
		<td align="left" width="102">&nbsp;</td>
		</tr>
		</tbody></table>
	</div>
	<div class="clb"></div>
	</div>
	<!--newsletters Alerts:ends-->

	</div>
	<div class="clb"></div>
</div>
<!--free subscription:ends-->
<!--application settings:start-->
<div class="mp1">
	<div class="mp2"><a name="Cmn"></a>
	<div class="mp3w mp4">Promotional Communication</div>
	</div>

	<div class="mp10" id="cd">
		<div class="ps1 usa f12">
		<strong><?php echo getWebsiteName(); ?> News &amp; Service Announcements</strong><br>It 
includes alerts on newly introduced free or paid service offerings from 
<?php echo get_page_settings(4);?> that are highly recommended for your business growth.
		<div class="ps2">
		<table align="right" border="0" cellpadding="2" cellspacing="0" width="350">
			<tbody><tr>
			<td align="right" width="63"><span>Email</span>&nbsp;</td>
			<td align="left" width="98">Mandatory</td>
			<td align="right" width="47"><span>SMS</span></td>
			<td width="20"><input id="iupdates_service_announcements_sms" value="7" onclick="myPrivacyUpdate(this,'load_7','enable_7')" checked="checked" type="checkbox"></td>
			<td align="left" width="102"><div class="fr lod" id="load_7" style="display:none"><img src="images/loading.gif" alt="" height="11" width="16"></div><label class="enb" id="enable_7">Enabled</label>
			</td>
			</tr>
		</tbody></table>
		</div>
		<div class="clb"></div>
		</div>
		
	</div>
	<div class="clb"></div>
</div>
<!--application settings:ends-->
</div></div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>