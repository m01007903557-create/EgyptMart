<?php
// #MickeyVUNDO = product_category_arabyos->product_category
include "common.php";

$_SESSION['last_page']="manage-buylead-alert.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");
}
$uid=$_SESSION['uid_indm'];

if(isset($_POST['sub_cat_id']) && $_POST['sub_cat_id']!='' ){
$query = "SELECT * FROM buylead_alert_category WHERE bac_pc_id=".$_POST['sub_cat_id']." and bac_usr_id=".$uid;	
		$r=mysql_query($query);	
		if(mysql_num_rows($r) == 0){
 			$SQL_BUY_ALERT="INSERT  INTO buylead_alert_category SET 
								          bac_usr_id=".$uid.",
										  bac_pc_id=".$_POST['sub_cat_id'].",
										  bac_updated_date=now()";
										  $r=mysql_query($SQL_BUY_ALERT) or die(mysql_error());
		}
}else if(isset($_GET['sub_cat_id']) && $_GET['sub_cat_id']!=''){
		$query = "SELECT * FROM buylead_alert_category WHERE bac_pc_id=".$_GET['sub_cat_id']." and bac_usr_id=".$uid;	
		$r=mysql_query($query);	
		if(mysql_num_rows($r) == 0){
 		$SQL_BUY_ALERT="INSERT  INTO buylead_alert_category SET 
                                          bac_usr_id=".$uid.",
										  bac_pc_id=".$_GET['sub_cat_id'].",
										  bac_updated_date=now()";
										  $r=mysql_query($SQL_BUY_ALERT) or die(mysql_error());
		}
}else {
	if(isset($_GET['keywords']) && $_GET['rctyp'] == 'buy_lead'){
		$sql_key="SELECT * FROM buy_requirement join product_category_arabyos on product_category_arabyos.pc_id=buy_requirement.br_pc_id WHERE br_pd_name = '".$_GET['keywords']."' and pc_status='1'";
		//echo $sql_key;die;
		$query_key = mysql_query($sql_key);
		$row_key=mysql_fetch_object($query_key);
		$key_cat_id = $row_key->pc_id;
		
		$query = "SELECT * FROM buylead_alert_category WHERE bac_pc_id=".$key_cat_id." and bac_usr_id=".$uid;	
		$r=mysql_query($query);	
		if(mysql_num_rows($r) == 0){		
			$SQL_BUY_ALERT="insert  into buylead_alert_category SET 
											  bac_usr_id=".$uid.",
											  bac_pc_id=".$key_cat_id.",
											  bac_updated_date=now()";
			$r=mysql_query($SQL_BUY_ALERT);
		}
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<!-- css start -->
<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
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

	$('.ajax').live('click', function() {
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
<script type="text/javascript">
function addAlertCategory()
{
	$.post("ajax-file/addBuyleadAlertCat.php",{},    function(data){	window.location.reload();   });
}
function delAlertCat(id)
{
	if(confirm("Are you sure to delete this Category?")){
		$.post("ajax-file/delBuyleadAlertCat.php",{id:id},    function(data){	window.location.reload();   });
	}
}
</script>
<style>
    .select_sp tr td
    {
        padding: 10px;
    }
</style>
</head>
<body class="search-show-box">

		<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
    <?php include "includes/header_new.php"; ?>
<!-- Header start Here::-->
		 <br><br>
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>

<div class="inner_wrapper">
    			<?php include "includes/header_menu.php"; ?>

		<!--left navigation:start-->
		<div class="f1 w61n tb lh ml br" id="lnav">
		<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Buy Leads </h3></li>
 			<li style="border-bottom:none"><h3>Buy Lead Purchases</h3></li>
			<li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;Purchased Buy Leads</a></li>
			<li class="np npnew"><a class="leftindi txtcol" href="manage-purchased-buyleads.php">»&nbsp;Buy Lead Alerts</a></li>
            <li class="np npnew"><a href="my-buylead-locationpref.php">»&nbsp;Location Preference</a></li>
			<li class="np npnew"><a href="transaction_history.php">»&nbsp;Transaction History</a></li>
			<li style="border-bottom: medium none;"><h3>Help / FAQs?</h3></li>
			<li class="np npnew"><a href="help.php">»&nbsp;Buy Leads Help / FAQs?</a></li>
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
    <div style="position:absolute; background-image:url('images/bg_popup.png'); left:0px; margin-top:0px; top:0px; right:0px; width:100%; z-index:2000; " class="win-close" id="div_info" align="CENTER"> <div id="divheight"></div> <table id="tableheight" border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr><td align="CENTER"><div id="dynamicheight"></div><div class="bg_border_new" style="height:675px" id="dvh1"><div style="background-color:#FFFFFF; height:670px" id="dvh2"><table border="0" cellpadding="0" cellspacing="0" width="100%">      <tbody><tr><td bgcolor="#E6E6E6"><div class="myta">Manage Your Buy Lead Product Preference</div></td>     <td style="padding-right:7px;" align="RIGHT" bgcolor="#E6E6E6"><img style="cursor:pointer" src="images/q_clbtn.png" onclick="win_open_buy();" height="16" width="16"></td> </tr> </tbody></table><img src="images/zero.gif" height="10" width="1"><br>  </div></div> </td> </tr> </tbody></table></div>


    <form style="margin:0px;padding:10px;" id="postForm" name="postForm" method="post" action="/cgi/eto-alert-subs-new.mp">

    <table style="table-layout:fixed;width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0"> 
    <tbody> 
    <tr> 
    <td style="border-right:0px; padding-right:10px" valign="top" width="100%"> 
    <table style="table-layout:fixed;width:100%" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%"> 
    <tbody>
    <tr> 
    <td align="LEFT" height="38" valign="TOP" width="325">
    <img src="images/zero.gif" height="4" width="1"> <br> 
    <nobr><div class="mf18 mc5 mta2 mpr8 mpt10"> <!-- <DIV class="mf11 bc mbn">Alerts &amp; Newsletters &#187</DIV> --> Manage Buy-leads Categories To Get Alerts Via Your Mailbox </div></nobr>
    </td> 
    <td valign="bottom">
    </td>

    <td valign="bottom">
    <div class="manage_country mb" style="text-align:left;">
    <a href="browse-cat-for-buylead-alert.php" class="ajax">Add More Categories</a>
    </div>
    </td>
    <td valign="bottom">
    <div class="manage_country mb"><a href="my-buylead-locationpref.php">Manage Your Location Preferences</a>
    </div>
    </td>
    </tr> 
               </tbody>
               </table>
               
                <div style=" border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>
                <?php
				$sql="SELECT * FROM product_category_arabyos,buylead_alert_category WHERE pc_id=bac_pc_id and bac_usr_id='".$_SESSION['uid_indm']."'";
				$res=mysql_query($sql);
				$count=mysql_num_rows($res);
				?>
                <div style="font-family:arial;font-size:12px;margin-top: 9px;color:#000000;padding-left: 5px;">
                    <div style="width: 218px;float: left;">No. of Product Categories Subscribed</div> : <?php echo $count; ?> <br>

                </div> <div style=" border-top:solid 1px #dce9f6; margin:8px 0px; clear:both"></div>

        <table style="table-layout:fixed;width:100%" class="mgoffer" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
        	<tbody>
            <tr>
            <td colspan="2" align="LEFT"><b style="font-size:14px;">Your Existing Buy-lead Products Subscription:</b></td>
            </tr>
        </tbody>
        </table>
        <table class="select_sp" style="border-top:1px solid #C8DDEC" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%"> <tbody><tr> <td class="tdoffer" style="padding-left:5px;" align="LEFT" bgcolor="#F1F9FE" width="80%">Product Categories</td> <td class="tdoffer" align="CENTER" bgcolor="#F1F9FE"><img src="images/zero.gif" height="1" width="85"><br>Remove</td> </tr>
        <?php

			if($count>0){
				while($row=mysql_fetch_object($res)){
		?>
        <tr id="map1">
        	<td class="mgoffer" align="LEFT"><?php echo $row->pc_name; ?></td>
	        <td style="cursor:pointer;" align="CENTER"><a onclick="delAlertCat(<?php echo $row->bac_id; ?>)" style="cursor:pointer;"><img src="images/del_img.gif" hspace="6"></a> </td>
		</tr>
        <?php 	}
			}else{	?>
        <tr><td colspan="5" height="60"><div style="font-family:arial; font-size:16px; color:#FF0000;" align="center"><b>You do not have any Buy Lead Product Alerts</b></div>
            <div style="font-family:arial; font-size:16px; color:#FF0000;" align="center"><a href="browse-cat-for-buylead-alert.php" class="ajax">Click here to Add Buy Lead Product Alerts </a></div></td></tr>
        <?php } ?>

            </tbody></table> <span id="subs_cats"></span> <span id="procssing"></span> </td> <td><img src="images/zero.gif" height="2" width="2"></td> </tr><input name="catid" id="catid" value="" type="hidden"> </tbody></table></form> <div><br> <br> <br> <br> </div></td> <td style="border-right:0px;" valign="top"><img src="images/gray-line.gif" height="1" width="1"></td> </tr></tbody> </table> <div style="clear:both"><br> </div></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->



        <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>



<script type="text/javascript">
$(document).ready(function($113){
	lostFocus();
	$113('#keywordsFilter').unbind().live('keyup',function() {
		var type11='Products';
		$113("#keywordsFilter").autocomplete("autocomplete.php", {
			selectFirst: true,
			extraParams: {type:type11},
			width: 407
		})
		.result(function(event, data, formatted) {
 			$("input#keywordsFilter").val(data);
		});
	});
});
</script>
</div>

		<?php include 'includes/footer.php';?>