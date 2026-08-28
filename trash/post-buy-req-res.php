<?php
include "common.php";

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
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
	<?php include "includes/header_new.php"; ?>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>
        


<?php include "includes/header_menu.php"; ?>
		<!--left navigation:start-->
		<div class="f1 w61n tb lh ml br" id="lnav">
		<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Buyer Tools</h3></li>

		<li class="np npnew"><a href="post-buy-req.php">»&nbsp;Post a Buy Requirement</a></li>
		<li class="np npnew"><a href="manage-buy-requirement.php">»&nbsp;Manage Buy Requirements</a></li>
		<li class="np npnew"><a href="manage-selloffer-alert.php">»&nbsp;Manage Sell Offer Alerts</a></li>
			<li style="border-bottom: medium none; margin-top: 40px;"><h2>You may also like to</h2></li>
			<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
			<li class="np npnew"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
			<li class="np npnew"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
		</ul>
		</div>
		<!--left navigation:ends--><div class="mctr1 mfl">
				<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
				<tr><td valign="TOP" width="100%"><style type="text/css">.thanksmsg {
color: #333333;
font-family: ms sans serif,arial;
font-size: 13px;
padding: 10px 0 0 5px !important;
text-align: left;
} .thanksmsg ul li{ padding-bottom: 0 0 5px 0 !important; margin-left:16px !important; list-style-image:none !important} 
.lf{text-align:left}</style><div>

    <div><img src="post-buy-req-res_files/zero.gif" height="5" width="1"><br>
    </div>
    <table>
      <tbody><tr>
        <td valign="TOP"><img src="post-buy-req-res_files/zero.gif" height="1" width="1"></td>
        <td valign="TOP" width="100%">
        <div><img src="post-buy-req-res_files/zero.gif" height="15" width="1"><br>
        </div>
        <table class="lf mpl10" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tbody>
          <tr>
            <td style="border-right:0px;" valign="top">
            <div>
            <table style="border-collapse:collapse;border:1px solid #86CDFD;" align="CENTER" cellpadding="0" cellspacing="0" width="100%">
              <tbody><tr>
                <td bgcolor="#E1F0FF">
        <div class="thankscathead"><b>Dear <?php echo user_info($_SESSION['uid_indm'],'name_prefix')." ".user_info($_SESSION['uid_indm'],'fname')." ".user_info($_SESSION['uid_indm'],'lname'); ?></b></div></td>
      </tr>
    </tbody></table>
    <table class="lf" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tbody><tr>
                <td class="thanksmsg" width="100%">
        <ul style="margin:0px;padding:0px;"><img src="post-buy-req-res_files/zero.gif" onload="" height="2" width="1"><li class="thanksmsg">Thank you for sharing your buy requirement with us.</li>
          <li class="thanksmsg">Your buy requirement shall be visible online within few minutes &amp; you shall also receive a confirmation email.</li>
          <li class="thanksmsg">For faster response, verify your buy requirement by link provided in confirmation email.</li>
	<li class="thanksmsg">You will receive responses from relevant 
suppliers on your email as well as via phone. Keep on checking your 
emails on regular basis.</li></ul></td>
      </tr>
    </tbody></table>
	<br>
    <table align="CENTER" border="0" cellpadding="2" cellspacing="0">
              <tbody><tr>
                <td align="CENTER" height="35" valign="BOTTOM">
                    <div class="thanksadlink" style="background:#f4faff;border:1px solid #c2e6fe;border-radius:6px;padding:4px 8px" align="CENTER"><a href="post-buy-req.php"><b>Post More Buy Requirements</b></a></div></td>
	<td width="10px"></td>
         <td align="CENTER" valign="BOTTOM">
                    <div class="thanksadlink" style="background:#f4faff;border:1px solid #c2e6fe;border-radius:6px;padding:4px 8px" align="CENTER"><a href="manage-buy-requirement.php"><b>Manage Buy Requirements</b></a></div></td>
      </tr>
    </tbody></table>
</div></td></tr></tbody></table>
    <div>
    </div><div align="center">
     <br>
    </div></td></tr></tbody></table><br><br></div></td></tr></tbody></table><br></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>