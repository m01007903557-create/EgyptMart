<?php
include "common.php";

$_SESSION['last_page']="tenders.php";

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
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/eto-buyreq.css" type="text/css" rel="stylesheet">


<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->

<script type="text/javascript">
$(document).ready(function()
{
	showTenders(1);
});

function showTenders(page)
{
	$('#res').html('<div align="center" style="margin-top:50px;margin-bottom:50px;"><img src="images/loading-hor-red.gif" /></div>');
	$.post("ajax-file/showLatestTenders.php",{page:page},    function(data){ $('#res').html(data); });
}
function goback()
{
	$('#detail_req').css("display","none");
	$('#req_listing').css("display","block");
}

</script>

<style id="poshytip-css-tip-yellowsimple" type="text/css">div.tip-yellowsimple{visibility:hidden;position:absolute;top:0;left:0;}div.tip-yellowsimple table, div.tip-yellowsimple td{margin:0;font-family:inherit;font-size:inherit;font-weight:inherit;font-style:inherit;font-variant:inherit;}div.tip-yellowsimple td.tip-bg-image span{display:block;font:1px/1px sans-serif;height:10px;width:10px;overflow:hidden;}div.tip-yellowsimple td.tip-right{background-position:100% 0;}div.tip-yellowsimple td.tip-bottom{background-position:100% 100%;}div.tip-yellowsimple td.tip-left{background-position:0 100%;}div.tip-yellowsimple div.tip-inner{background-position:-10px -10px;}div.tip-yellowsimple div.tip-arrow{visibility:hidden;position:absolute;overflow:hidden;font:1px/1px sans-serif;}</style></head>
<body>
<div id="imgtrailer" style="position:absolute; z-index:4;visibility:hidden;"><img src="images/loading.gif" height="32" width="32"></div>


	<!-- Validate logged in user code ends HERE-->



		<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->
		<?php include "includes/header_new.php"; ?>
       
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<!-- Header End Here::-->

	
	<!-- autosuggest ends -->

	<!--iil header:ends-->

			<?php include "includes/header_menu.php"; ?>

		<!--left navigation:start-->
	<!--<div class="f1 w61n tb lh ml br" id="lnav" >
	<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Buyer Tools</h3></li>

		<li class="np npnew"><a href="post-buy-req.php">»&nbsp;Post a Buy Requirement</a></li>
		<li class="np npnew"><a class="leftindi txtcol" href="manage-buy-requirement.php">»&nbsp;Manage Buy Requirements</a></li>
		<li class="np npnew"><a href="manage-selloffer-alert.php">»&nbsp;Manage Sell Offer Alerts</a></li>
		<li style="border-bottom: medium none; margin-top: 40px;"><h2>You may also like to</h2></li>
		<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
		<li class="np npnew"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
		<li class="np npnew"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
	</ul>
	</div>-->
		<!--left navigation:ends-->
        
        <div class="mctr_buyreq mfl" id="req_listing" style="width:98%;"><!---->
		<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody>
		<tr>
		<td valign="TOP" width="100%">
		<style type="text/css">
.sub{display: none;}
.tbq{display: none;}
.tabTextColor{color:#fff;}
</style>

	<form style="margin:0px;" action="" name="form1">

        <table align="CENTER" border="0" cellpadding="0" cellspacing="0" style="min-width:100%">
          <tbody><tr>
            <td valign="TOP" width="100%">
		<div class="wd1 mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div>Latest Tenders</div>
		<div id="masterdiv"><div id="sub1" style="display:inline;">
		<div class="ap1">
        <?php
		$sql_opn="SELECT count(*) AS cnt from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id and br_approval_status!='2' and br_display_status='1' and br_status='1' and br_u_id='".$_SESSION['uid_indm']."'";
		$res_opn=mysqli_query($con, $sql_opn);
		$row_opn=mysqli_fetch_object($res_opn);
		
		$sql_cls="SELECT count(*) AS cnt from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id and br_approval_status!='2' and br_display_status='0' and br_status='1' and br_u_id='".$_SESSION['uid_indm']."'";
		$res_cls=mysqli_query($con, $sql_cls);
		$row_cls=mysqli_fetch_object($res_cls);
		?>
        	
			<div id="post-new" style="float: right;margin:5px 5px" class="postNewReq"><a href="post-tender.php" class="bo_m2 active">Post New Tender</a></div>
			<div class="c3"></div>
		</div>

		<div id="res"></div>


    
    </div></div>
	</td>
	<!--<td valign="TOP"><img src="images/zero.gif" height="1" width="10"></td>-->
	</tr>
	</tbody></table>

	</form><div style="clear:both"><br></div>

</td></tr></tbody></table></div>

		<div id="detail_req" style="display:none;"></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>