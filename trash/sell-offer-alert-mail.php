<?php
include "common.php";

$sql_so="select * from sale_offer,user where so_usr_id=usr_id and so_id='".$_GET['so']."'";
$res_so=mysql_query($sql_so);
$row_so=mysql_fetch_object($res_so);

$sql_atert_usr="select * from user where usr_id='".$_GET['u']."'";
$res_alert_usr=mysql_query($sql_atert_usr);
$row_alert_usr=mysql_fetch_object($res_alert_usr);

?>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="680">
<tbody><tr>
  <td style="padding-top:10px;border-bottom:1px solid #bdd0f2">
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tbody>
  <tr>
  <td style="padding-bottom:5px" valign="middle" width="32%">
  <a rel="nofollow" href="index.php" target="_blank"><img alt="<?php echo getWebSiteName(); ?>" src="<?php echo $_SESSION['HTTP_HOST']."/sitelogo/".getSiteLogo(); ?>" style="margin:0 20px 0px 0" border="0"></a></td>

  <td style="font-family:'Trebuchet MS';font-size:13px;text-align:center" valign="middle" width="36%">
  
  <b>Today's Latest <br>
  <span style="font-size:18px">Sale Offers</span> </b></td>
  
  <td style="padding:7px 5px 10px 0;font-size:13px" align="right" valign="middle" width="32%"><b><?php echo date("l, F d, Y"); ?></b>
  
  </td>
  </tr>
  </tbody>
  </table>
  </td>
  </tr>
					   
  <tr>
  <td style="color:#7e7e7f;padding:15px 5px 15px 0;line-height:16px"><b>Dear <?php echo $row_alert_usr->name_prefix; ?> <?php echo $row_alert_usr->fname; ?> <?php echo $row_alert_usr->lname; ?>,</b><br><br>
  Latest sell offers relevant to your subscribed categories are listed below:</td>
  </tr> 
<tr><td>
<table align="center" border="0" cellpadding="0" cellspacing="0" width="680">
<tbody><tr>



<td style="vertical-align:top" width="680">		 
		<div style="width:95%;overflow:hidden;background-color:rgb(243,243,243);border-top:1px solid rgb(225,36,0);padding:2px 2px 12px;min-height:175px;line-height:normal">
		<div style="margin:0 0 5px 0;padding:0;min-height:26px">
				<table border="0" cellpadding="0" cellspacing="0">
				<tbody><tr><td style="width:210px;text-align:left" align="left">
				<a href="<?php echo $_SESSION['HTTP_HOST']."/saleoffer-details.php?id=".rand(1000,9999).md5($row_so->so_id); ?>" style="color:#0000ff;font-family:Arial;font-size:13px;line-height:15px;word-wrap:break-word" target="_blank"><b><?php echo $row_so->so_service; ?></b></a>
				</td>

				<td style="text-align:right;width:100px" align="right">

				<div style="margin-left:3px">
									
									
									
									
								</div>
				</td></tr>
				</tbody></table>
		</div>
<table>
<tbody><tr>
<td style="list-style:none outside none;line-height:normal;vertical-align:top;width:47%">
<div style="line-height:normal;border:4px solid rgb(170,170,170);vertical-align:middle;min-height:125px!important;width:auto;background-color:rgb(255,255,255)">
<a href="<?php echo $_SESSION['HTTP_HOST']."/saleoffer-details.php?id=".rand(1000,9999).md5($row_so->so_id); ?>" style="text-decoration:none;line-height:normal" target="_blank"><table style="line-height:normal">
<tbody><tr><td style="vertical-align:middle;width:125px;word-wrap:break-word;height:125px;background-color:#ffffff;line-height:normal" align="center"><img alt="<?php echo $row_so->so_service; ?>" style="line-height:normal;margin:0px;padding:0px" src="<?php echo $_SESSION['HTTP_HOST']."/upload/sale_offer/".$row_so->so_pic; ?>" border="0"></td>
</tr></tbody></table></a></div>

			</td>
			<td style="list-style:none outside none;width:53%;line-height:normal;vertical-align:top"><div style="line-height:14px;font-size:13px;font-family:Arial;word-wrap:break-word;font-weight:700;padding:5px 0px 0px 2px;margin:0px"><?php echo user_info($row_so->so_usr_id,'bnsprof_compname'); ?></div>

			<div style="line-height:14px;font-size:12px;color:#3b3b3b;font-weight:700;margin:0;padding:5px 0 0 2px;font-family:Arial">Location:&nbsp;<span style="font-weight:normal;word-wrap:break-word"><?php echo get_city_name($row_so->so_usr_id,'bnsprof_city'); ?><br>[<?php echo get_country_name($row_so->country); ?>]</span></div>
			

			<br>	
			<div style="line-height:normal;margin:0;padding:0;background:#f75b16;border:1px solid #bf5305;background:-moz-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:-webkit-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:-o-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:-ms-linear-gradient(top,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);background:linear-gradient(to bottom,#f77219 1%,#fec6a7 3%,#f77219 7%,#f75b16 100%);width:122px;min-height:32px;text-align:center">

			<a href="<?php echo $_SESSION['HTTP_HOST']."/saleoffer-details.php?id=".rand(1000,9999).md5($row_so->so_id); ?>" style="color:#fff;padding:8px 0px;display:block;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:bold;line-height:normal;text-decoration:none;text-align:center" target="_blank">Send Enquiry</a>
		</div>
			</td>
	</tr></tbody></table>
		</div>
		
		</td></tr>
</tbody></table>
</td></tr>



  <tr> <td>
<table align="left" width="668">
<tbody><tr>
  <td style="padding:10px 5px;font-size:10px;color:#888888;background-color:#ebebeb">You have received this email by virtue of your opt-in subscription for sell offers alert on <span style="color:#4163a2;text-decoration:underline"><a href="<?php echo $_SESSION['HTTP_HOST']; ?>" target="_blank"><?php echo getWebSiteName(); ?></a></span> 
  <br>
  <a href="<?php echo $_SESSION['HTTP_HOST']."/manage-selloffer-alert.php"; ?>" target="_blank">Click here</a> if you wish to modify your sell offers alert categories.<br>

  <br>
   </td>
  </tr>
</tbody></table>
</td>
  </tr>
</tbody></table>