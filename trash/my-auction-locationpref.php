<?php
ob_start();
include "common.php";

$_SESSION['last_page']="my-auction-locationpref.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];

/*$sql_usr_lpref="select * from user where usr_id='".$uid."'";
$res_usr_lpref=mysql_query($sql_usr_lpref);
$row_usr_lpref=mysql_fetch_object($res_usr_lpref);*/

class editPreference
{
	var $msg;
	var $usr_id;
	var $usr_auc_prefLocation;
	
	function __construct($usr_id)
	{
		$this->usr_id=$usr_id;
	}
	function detailsObj()
	{
		$sql="select * from user where usr_id=".$this->usr_id;
		$res=mysql_query($sql);
		return mysql_fetch_object($res);
	}
	
	function updatePref()
	{		
		$sql="update user
			set					
				usr_auc_prefLocation ='".$this->usr_auc_prefLocation."'
			where
				usr_id='".$this->usr_id."'";
				
		mysql_query($sql) or die(mysql_error());
															
	//	$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Plan updated successfully</div>';	
	}	
}

$obj_pref=new editPreference($uid);
$row_usr_lpref=$obj_pref->detailsObj();

if(isset($_POST['btnUpdate']))
{
	
	$obj_pref->usr_auc_prefLocation=addslashes(trim($_POST['usr_auc_prefLocation']));
	
	$obj_pref->updatePref();

//	$_SESSION['msg']=$ob->msg;
	
	header("location:my-auction-locationpref.php");
}

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
<link href="css/pbl-my02.css" type="text/css" rel="stylesheet">
<link href="css/mng-trde-alrt.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->

<!-- inline script/js start -->
	<!-- Validate logged in user code ends HERE-->
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
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
</head>
<body>

		<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
		<br><br>
<!-- Header start Here::-->
		
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>

			<?php include "includes/header_menu.php"; ?>
	
		<!--left navigation:start-->
		<div class="f1 w61n tb lh ml br" id="lnav">
		<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Auctions </h3></li> 
 			<li style="border-bottom:none"><h3>Auction Purchases</h3></li>
			<li class="np npnew"><a href="manage-purchased-auctions.php">»&nbsp;Purchased Auctions</a></li>
   			<li class="np npnew"><a href="manage-auction-alert.php">»&nbsp;Auction Alerts</a></li>
            <li class="np npnew"><a class="leftindi txtcol" href="my-auction-locationpref.php">»&nbsp;Location Preference</a></li>
			<li class="np npnew"><a href="transaction_history.php">»&nbsp;Transaction History</a></li>
			<li style="border-bottom: medium none;"><h3>Help / FAQs?</h3></li>
			<li class="np npnew"><a href="help.php">»&nbsp;Auction Help / FAQs?</a></li>
            <li class="ug-banner">
            
            <?php
	$sql_adv="select * from advertisement where adv_imagewidth='200' and adv_imageheight='154' and adv_status='1' order by rand() limit 1";
	$res_adv=mysql_query($sql_adv);
	if(mysql_num_rows($res_adv)>0)
	{
		$row_adv=mysql_fetch_object($res_adv);	
		?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="200" height="154"/></a><?php
	}
	else
	{
?>
		<img src="upload/advertisement/200-154-advertisement.png" alt="" border="0" height="154" width="200">
<?php	}	?>
            
            
            
            </li>
            </ul>
		</div>
		<!--left navigation:ends-->
        <div class="mctr mfl">
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
        <tr><td id="id_attribute_value" valign="TOP" width="100%">
    <div style="position:absolute; background-image:url('images/bg_popup.png'); left:0px; margin-top:0px; top:0px; right:0px; width:100%; z-index:2000; " class="win-close" id="div_info" align="CENTER"> <div id="divheight"></div> <table id="tableheight" border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr><td align="CENTER"><div id="dynamicheight"></div><div class="bg_border_new" style="height:675px" id="dvh1"><div style="background-color:#FFFFFF; height:670px" id="dvh2"><table border="0" cellpadding="0" cellspacing="0" width="100%">      <tbody><tr><td bgcolor="#E6E6E6"><div class="myta">Manage Your Auction Preference</div></td>     <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6"><img style="cursor:pointer" src="images/q_clbtn.png" onclick="win_open_buy();" height="16" width="16"></td> </tr> </tbody></table><img src="images/zero.gif" height="10" width="1"><br>  </div></div> </td> </tr> </tbody></table></div>
    


    <table style="table-layout:fixed;width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0"> <tbody> <tr> <td style="border-right:0px; padding-right:10px" valign="top" width="100%"> <table style="table-layout:fixed;width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr> <td align="LEFT" height="38" valign="TOP" width="325"><img src="images/zero.gif" height="4" width="1"> <br> <nobr><div class="mf18 mc5 mta2 mpr8 mpt10"> <!-- <DIV class="mf11 bc mbn">Alerts &amp; Newsletters &#187</DIV> --> Location Preferences </div></nobr></td> <td valign="bottom">
    </td>
    <td valign="bottom"><div class="manage_country mb"><a href="manage-auction-alert.php">Manage Your Auction Preferences</a></div></td>
    </tr> </tbody></table>
    
                <div style=" border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                
	<!-- test start -->
    
    <div class="mclb mpr5">

<form name="savelocpref" id="savelocpref" method="post">

<div id="location">
<table border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody><tr>

<td id="prf_main" style="margin-right: 0px; *padding-top:0px" valign="top">
	
    <div style="font-size:24px; font-weight:bold;font-family:arial; width:98%;color:#fff; background:#024ca7; padding:10px 17px 5px 5px;margin-bottom:15px;text-transform:uppercase;border:solid 1px #024ca7; margin-right:0px;" class="boxbg bgi1">I want Auctions from </div>
    
    
	<div onmouseover="change_bg(1)" onmouseout="remove_bg(1)" style="margin-right:0px;" id="box_1" class="boxbg boxbg1 <?php if($row_usr_lpref->usr_auc_prefLocation=='any'){ ?>bgi1<?php } ?>" onclick="location_prf(1)">
		<label for="locationid_1"  id="label_1"><!--class="r_on"-->
        	<input name="usr_auc_prefLocation" value="any" id="usr_auc_prefLocation_1" <?php if($row_usr_lpref->usr_auc_prefLocation=='any'){ ?>checked="checked"<?php } ?> type="radio">
			<span class="fs18 lc4 fwb">All Over the World (My City + My Country<?php /*echo get_country_name($row_usr_lpref->country);*/ ?> + Export)</span> 
			<div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:2px;">Selecting this option would mean you will receive auctions from all over the world including Your Country<?php /*echo get_country_name($row_usr_lpref->country);*/ ?> and Foreign. 
				<br>
				<div class="mpt3 mf12 mc2 mlh16">This means that you essentially do business worldwide and do both Domestic and International business.</div>
			</div>
		</label>
	</div>

	
    <div onmouseover="change_bg(2)" onmouseout="remove_bg(2)" onclick="location_prf(2)" id="box_2 " class="boxbg1 <?php if($row_usr_lpref->usr_auc_prefLocation=='abroad'){ ?>bgi1<?php } ?>" style="float:left; width:32%; margin-right:12px;height:120px">
		<label for="locationid_2" id="label_2"><!--class="label_radio"-->
        	<input name="usr_auc_prefLocation" value="abroad" id="usr_auc_prefLocation_2" <?php if($row_usr_lpref->usr_auc_prefLocation=='abroad'){ ?>checked="checked"<?php } ?> type="radio">
            <span class="fs18 lc4 fwb">Foreign Only <br><span style="padding-left:30px">(Export Only)</span></span>
            <div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:4px;">
            	Selecting this option would mean:
                <br>
				<div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
			   	&bull; No Auction from Your Country<?php /*echo get_country_name($row_usr_lpref->country);*/ ?> <br><img src="images/zero.gif" height="6" width="1"><br>
				&bull; No Auction from Your City
                </div>
			</div>
		</label>
	</div>

	<div onmouseover="change_bg(3)" onmouseout="remove_bg(3)" id="box_3" style="float:left; width:32%; margin-right:12px;height:120px" class="boxbg1 <?php if($row_usr_lpref->usr_auc_prefLocation=='domestic'){ ?>bgi1<?php } ?>" onclick="location_prf(3)">
		<label for="locationid_3"  id="label_3"><!--class="label_radio"-->
        	<input name="usr_auc_prefLocation" id="usr_auc_prefLocation_3" value="domestic" <?php if($row_usr_lpref->usr_auc_prefLocation=='domestic'){ ?>checked="checked"<?php } ?> type="radio">
            <span class="fs18 lc4 fwb"><?php /*echo get_country_name($row_usr_lpref->country);*/ ?>My Country Only <br><span style="padding-left:30px">(Domestic Only)</span></span>
			<div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:4px;">
            	Selecting this option would mean:
            	<br>
				<div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                &bull; No Auction from Outside Your Country<?php /*echo get_country_name($row_usr_lpref->country);*/ ?><br><img src="images/zero.gif" height="6" width="1"><br>
                &bull; No Export Enquiry
				</div>
			</div>
		</label>
	</div>
	
    
    <div onmouseover="change_bg(4)" onmouseout="remove_bg(4)" id="box_4" style="float:left; width:32%; margin-right:0px;height:120px" class="boxbg1 <?php if($row_usr_lpref->usr_auc_prefLocation=='my_city'){ ?>bgi1<?php } ?>" onclick="location_prf(4)">
		<label for="locationid_4" id="label_4"><!--class="label_radio"-->
        	<input name="usr_auc_prefLocation" id="usr_auc_prefLocation_4" value="my_city" <?php if($row_usr_lpref->usr_auc_prefLocation=='my_city'){ ?>checked="checked"<?php } ?> type="radio">
            <span class="fs18 lc4 fwb">Local Area Only<br><span style="padding-left:30px">(My City &amp; its 250 KM)</span></span>
			<div class="pdl20 mrgn lc5" style="line-height:17px;padding-top:4px;">
            	Selecting this option would mean:<br>
				<div class="mpt3 mf12 mc2 mlh16" style="line-height:14px">
                &bull; No Auction Outside 250 KM of Your City<br>
                <img src="images/zero.gif" height="6" width="1"><br>
                &bull; No Auction from Outside Your Country<?php /*echo get_country_name($row_usr_lpref->country);*/ ?>
                </div>
			</div>
		</label>
	</div>

	<div style="clear:both"></div>

  <table style="border-collapse: collapse; margin-top: 10px; clear:both" border="1" bordercolor="#007af4" cellpadding="5" cellspacing="0" align="center">
    <tbody>
      <tr>
        <td style="padding: 8px;" bgcolor="#9fcfff">  <input name="btnUpdate" value="Save Changes" style="padding: 3px 8px; font-size: 18px;" type="SUBMIT">  </td>
        </tr>
      </tbody>
    </table>
  
</td>
			</tr>
			</tbody></table>
		</div>


</form>
      </div>
    
    <!-- test end -->
                
               
                 <div style=" border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
        
        
         <span id="subs_cats"></span> <span id="procssing"></span> </td> <td><img src="images/zero.gif" height="2" width="2"></td> </tr><input name="catid" id="catid" value="" type="hidden"> </tbody></table> <div><br> <br> <br> <br> </div></td> <td style="border-right:0px;" valign="top"><img src="images/gray-line.gif" height="1" width="1"></td> </tr></tbody> </table> <div style="clear:both"><br> </div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>