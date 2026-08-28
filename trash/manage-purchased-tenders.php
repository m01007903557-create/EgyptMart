<?php
include "common.php";

$_SESSION['last_page']="manage-purchased-tenders.php";
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
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/eto-buyreq.css" type="text/css" rel="stylesheet">


<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->
<script src="js/jquery.colorbox.js"></script>

<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script>
	$(document).ready(function(){
		//Examples of how to assign the ColorBox event to elements
$('.ajax').live('click', function() {
  $.colorbox({href:$(this).attr('href'), open:true, width:"800px", height: "500px"});
  return false;
});
		//$(".ajax").colorbox({ width:"70%"});
		
	});
</script>
<script type="text/javascript">
$(document).ready(function()
{
	showPurTenders(1);
});
function showPurTenders(page)
{
	$("#opnReq_tab").removeClass("npo").addClass("ap2 tabTextColor");
	$("#clsReq_tab").removeClass("ap2 tabTextColor").addClass("npo");
	purchasedTenders(page);
}
function showClosedReq(page)
{
	$("#clsReq_tab").removeClass("npo").addClass("ap2 tabTextColor");
	$("#opnReq_tab").removeClass("ap2 tabTextColor").addClass("npo");
	closedRequirement(page)
}
function purchasedTenders(page)
{
	$.post("ajax-file/purchased-tenders.php",{page:page},    function(data){ $('#res').html(data); });
}
function closedRequirement(page)
{
	$.post("ajax-file/closedRequirement.php",{page:page},    function(data){ $('#res').html(data); });
}
function detailPurTenders(id)
{
	$.post("ajax-file/detailPurTenders.php",{id:id},    function(data){ 
		$('#detail_req').html(data);
		$('#req_listing').css("display","none");
		$('#detail_req').css("display","block");
	});
}

function goback()
{
	$('#detail_req').css("display","none");
	$('#req_listing').css("display","block");
}
function delTender(id,v)
{
	if(confirm("Are you sure to delete this?"))
	{
		$.post("ajax-file/delTender.php",{id:id},    function(data){
			if(v=='op')
			{
				purchasedTenders(1);
			}
			else
			{
				closedRequirement(1);
			}
	   });
	}
}

</script>



<style id="poshytip-css-tip-yellowsimple" type="text/css">div.tip-yellowsimple{visibility:hidden;position:absolute;top:0;left:0;}div.tip-yellowsimple table, div.tip-yellowsimple td{margin:0;font-family:inherit;font-size:inherit;font-weight:inherit;font-style:inherit;font-variant:inherit;}div.tip-yellowsimple td.tip-bg-image span{display:block;font:1px/1px sans-serif;height:10px;width:10px;overflow:hidden;}div.tip-yellowsimple td.tip-right{background-position:100% 0;}div.tip-yellowsimple td.tip-bottom{background-position:100% 100%;}div.tip-yellowsimple td.tip-left{background-position:0 100%;}div.tip-yellowsimple div.tip-inner{background-position:-10px -10px;}div.tip-yellowsimple div.tip-arrow{visibility:hidden;position:absolute;overflow:hidden;font:1px/1px sans-serif;}</style>

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
		<!--myzone drop elements:ends--> 
		<!--left navigation:start-->
		<div class="f1 w61n tb lh ml br" id="lnav">
		<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Buyer Tools</h3></li>

		<li class="np npnew"><a href="post-buy-req.php">»&nbsp;Post a Buy Requirement</a></li>
		<li class="np npnew"><a href="manage-buy-requirement.php">»&nbsp;Manage Buy Requirements</a></li>
		<li class="np npnew"><a href="manage-selloffer-alert.php">»&nbsp;Manage Sell Offer Alerts</a></li>
		<li style="border-bottom:none"><h3>Buy Lead Purchases</h3></li>
		<li class="np npnew"><a href="subscription.php">Purchase Buy Leads</a></li>
		<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
		<li class="np npnew"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
		<li class="np npnew"><a class="leftindi txtcol" href="manage-purchased-tenders.php">View Purchased Tenders</a></li>
		<li class="np npnew"><a href="transaction_history.php">Transaction History</a></li>
		<li class="np npnew"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
		</ul>
		</div>
		<!--left navigation:ends-->
        
        <div class="mctr_buyreq mfl" id="req_listing">
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

        <table align="CENTER" border="0" cellpadding="0" cellspacing="0">
          <tbody><tr>
            <td valign="TOP" width="100%">
            <!---->
            
		 <?php
		  $sql="select * from user where usr_id='".$uid."'";
		  $res=mysqli_query($con, $sql);
		  $row=mysqli_fetch_object($res);
		  ?>
		
			
			<?php if($row->usr_mp_id > 3) {?>
			<div class="mfr mf14 md1 mc3"> <span class="mf12">[ 
			<a href="membership_plans.php">Pay Annual Subscription</a>
			<?php } else { ?>
			<div class="mfr mf14 md1 mc3">Available Credits: <font class="mc6"> <span id="current_balance">0</span>
		Credits</font><span class="mf12">[ 
			<a href="subscription.php">Purchase More Credits</a>
			<?php } ?>
			]</span><div class="mta mpb8 mpt5 mf11"><a href="transaction_history.php">View Your Transaction History</a></div></div><div class="wd mf18 mc5 mta2 mpb10">Purchased Tenders</div>
		
            <!---->
		<!--<div class="wd1 mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div>Manage Buy Requirements</div>-->
		<div id="masterdiv"><div id="sub1" style="display:inline;">
		<div class="ap1">
        <?php
		$sql_pur="SELECT count(*) AS cnt from tender,purchased_tender where ptnd_tnd_id=tnd_id and tnd_status='1' and ptnd_usr_id='".$_SESSION['uid_indm']."'";
		$res_pur=mysqli_query($con, $sql_pur);
		$row_pur=mysqli_fetch_object($res_pur);
		
		?>
        	<a class="f1 ap2 tabTextColor" onclick="showPurTenders(1);" style="cursor:pointer;" id="opnReq_tab">Purchased Tenders  (<?php echo $row_pur->cnt; ?>)</a>
            <!--<a class="f1 npo m5" onclick="showClosedReq(1);" style="cursor:pointer;" id="clsReq_tab">Closed Requirements ()</a>
			<div id="post-new" style="float: right;margin:5px 0" class="postNewReq"><a href="post-buy-req.php" class="bo_m2 active">Post a New Buy Requirement</a></div>-->
			<div class="c3"></div>
		</div>

		<div id="res"></div>
    
    </div></div>
	</td>
	<td valign="TOP"><img src="images/zero.gif" height="1" width="10"></td>
	</tr>
	</tbody></table>

	</form><div style="clear:both"><br></div>

</td></tr></tbody></table></div>

		<div id="detail_req" style="display:none;"></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>