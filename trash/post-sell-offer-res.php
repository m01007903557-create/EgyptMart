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
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
<link href="css/pdash.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>
        


<?php include "includes/header_menu.php"; ?>
		<!--left navigation:start-->
		<div class="f1 w61n tb lh ml br" id="lnav">
 		<?php include "includes/seller-tools-panel.php"; ?>
		</div>
		<!--left navigation:ends-->  <div class="w57 b1_m2 f1 blr p2b b1_m2"><style type="text/css">.thanksmsg ul li{ padding-bottom:5px; margin-left:35px;list-style-image: url('http://my.imimg.com/gifs/ul.gif')}
.thanksmsg ul ul li { padding:0;margin-left:35px;list-style-image: url('http://my.imimg.com/gifs/ulul.gif')}.lf{text-align:left}</style><div>
    
    <table style="align:center;">
      <tbody><tr>
        <td valign="TOP"><img src="post-sell-offer-res_files/zero.gif" height="1" width="1"></td>
        <td valign="TOP" width="100%">
        <div><img src="images/zero.gif" height="15" width="1"><br>
        </div>
        <table class="lf" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
          <tbody>
          <tr>
            <td style="border-right:0px;" valign="top">
            <div>
            <table style="border-collapse:collapse;" align="CENTER" border="1" bordercolor="#86CDFD" cellpadding="0" cellspacing="0" width="100%">
              <tbody><tr>
                <td bgcolor="#E1F0FF">
        <div class="thankscathead"><b>Dear <?php echo user_info($_SESSION['uid_indm'],'name_prefix')." ".user_info($_SESSION['uid_indm'],'fname')." ".user_info($_SESSION['uid_indm'],'lname'); ?></b></div></td>
      </tr>
    </tbody></table>
    <table border="0" cellpadding="0" cellspacing="0" width="100%">
              <tbody><tr>
                <td class="thanksmsg" width="100%">
        <ul style="margin-bottom:0px;margin-top:0px;"><li class="thanksmsg">The offer posted by you has been received <b><font color="#BF0000">successfully </font></b>.</li>
          <li class="thanksmsg">It will be displayed within <b><font color="#BF0000">two business days </font></b> subject to its approval by the administrator.</li>
	<div align="center"><font color="#BF0000" face="arial" size="-1"><a href="post-sell-offer.php">Click here to continue...</a></font></div></ul></td>
      </tr>
    </tbody></table>
    <table align="CENTER" border="0" cellpadding="2" cellspacing="0" width="100%">
              <tbody><tr>
                <td align="CENTER" height="35" valign="BOTTOM" width="225">
                <table border="0" cellpadding="0" cellspacing="0" width="165">
                  <tbody><tr>
                    <td background="images/thkadbg2.gif" height="26">
                    <div class="thanksadlink" align="CENTER"><a href="post-sell-offer.php"><b>Post more trade offers</b></a></div></td>
          </tr>
        </tbody></table></td>

         <td align="CENTER" valign="BOTTOM" width="225">
                <table border="0" cellpadding="0" cellspacing="0" width="180">
                  <tbody><tr>
                    <td background="images/thkadbg1.gif" height="26">
                    <div class="thanksadlink" align="CENTER"><a href="manage-sell-offer.php"><b>Track your trade offers</b></a></div></td>
          </tr>
        </tbody></table></td>

        <td align="center" valign="BOTTOM" width="225">
                <table border="0" cellpadding="0" cellspacing="0" width="180">
                  <tbody><tr>
                    <td background="images/thkadbg.gif" height="26" align="center">
                    <div class="thanksadlink" align="CENTER"><a href="manage-selloffer-alert.php"><b>Subscribe to trade alerts </b></a></div></td>
          </tr>
	
        </tbody></table></td>
	</tr>
  
        </tbody></table></div></td>

      </tr>
    </tbody></table>
</td></tr></tbody></table>
    <div><br>
     <br>
    </div><!--latest buy leads:start-->
	<div class="dph f1 dem dem2 mt12 boxh2" style="width:730px;display:none" id="hhd">
		<h2>Latest Buy Leads</h2><div class="p75" id="buylead"><img src="images/sol.gif" alt="Loading..." border="0" height="16" width="16"></div>
		</div>
	<!--latest buy leads:ends--><div align="center">
     <br>
    </div><br><br><br></div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>