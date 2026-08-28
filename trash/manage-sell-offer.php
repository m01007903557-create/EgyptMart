<?php
include "common.php";

$_SESSION['last_page']="manage-sell-offer.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];

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
<link href="css/dir-new.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->
<style>
	@media screen and (max-width: 1400px) and (min-width: 990px){

.n-hdrn li {
    padding: 10px !important;
    font-size: 13px!important;
	}}
	</style>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
function showActOffer()
{
	$("#active_offer_tab").removeClass("tab").addClass("active_tab");
	$("#approval_pending_tab").addClass("tab").removeClass("active_tab");
	$("#expired_offer_tab").addClass("tab").removeClass("active_tab");
	
	$("#active_offer").css("display","block");
	$("#pending_approval").css("display","none");
	$("#expired_offer").css("display","none");
	
	showActive(1);
}
function showApprPending()
{
	$("#approval_pending_tab").removeClass("tab").addClass("active_tab");
	$("#active_offer_tab").addClass("tab").removeClass("active_tab");
	$("#expired_offer_tab").addClass("tab").removeClass("active_tab");
	
	$("#active_offer").css("display","none");
	$("#pending_approval").css("display","block");
	$("#expired_offer").css("display","none");
	
	showPending(1);
}
function showExpiredOffer()
{
	$("#approval_pending_tab").addClass("tab").removeClass("active_tab");
	$("#active_offer_tab").addClass("tab").removeClass("active_tab");
	$("#expired_offer_tab").removeClass("tab").addClass("active_tab");
	
	
	$("#active_offer").css("display","none");
	$("#pending_approval").css("display","none");
	$("#expired_offer").css("display","block");
	
	showExpired(1);
}

function viewSODetails(id)
{
	$("#listing").css("display","none");
	$.post("ajax-file/sale-offer-details.php",{id:id},    function(data){ 

		$("#details").css("display","block");
		$('#details').html(data);		
	});
}
function backToListing()
{
	$("#details").css("display","none");
	$("#listing").css("display","block");
}
function editSaleOffer(id)
{
	$.post("ajax-file/sale-offer-edit.php",{id:id},    function(data){ 
		$('#details').html(data);		
	});
}
function delSaleOffer(id)
{
	if(confirm("Are you sure to delete this Offer?")){
		$.post("ajax-file/delSaleOffer.php",{id:id},    function(data){	window.location.reload();	});
	}
}
function showActive(page)
{
	$.post("ajax-file/active-selloffer.php",{page:page},    function(data){    $('#res').html(data); });
}
function showPending(page)
{
	$.post("ajax-file/pending-approval-selloffer.php",{page:page},    function(data){    $('#res').html(data); });
}
function showExpired(page)
{
	$.post("ajax-file/expired-selloffer.php",{page:page},    function(data){    $('#res').html(data); });
}

</script>
<style>
#updSO
{
	border:1px solid #6F0000;color:#fff;text-decoration:none;font-size:14px; font-weight:bold; padding:5px;text-align:center;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;background-color:#DF0000;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');background:-webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));background:-moz-linear-gradient(top,  #DF0000,  #DF0000);cursor:pointer;font-family:Arial, Helvetica, sans-serif
}
</style>
</head>
<body>
<div id="imgtrailer" style="position:absolute; z-index:4;visibility:hidden;"><img src="images/loading.gif" height="32" width="32"></div>

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
 		<!--<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Seller Tools</h3></li><li style="border-bottom:none"><h3>Products/Services</h3></li>
	
	<li class="np npnew"><a href="product-add.php">»&nbsp;Add New Products</a></li>	
	<li class="np npnew"><a href="product-list.php" class=" ">»&nbsp;Manage Products</a></li>

		<li style="border-bottom: medium none;"><h3>Buy Leads</h3></li>
		<li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;Purchased Buy Leads</a></li>
		<li style="border-bottom: medium none;"><h3>Sell Offers</h3></li>
		<li class="np npnew"><a href="post-sell-offer.php">»&nbsp;Post a Sell Offer</a></li>
		<li class="np npnew"><a class="txtcol leftindi" href="manage-sell-offer.php">»&nbsp;Manage Sell Offer</a></li>
		
		<li style="border-bottom: medium none;"><h3>Subscriptions</h3></li>
		<li style="border-bottom: medium none; margin-top: 40px;"><h2>You may also like to</h2></li>
		<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
		<li class="np npnew"><a href="post-buy-req.php">Post a New Buy Requirement</a></li>
		<li class="np npnew"><a href="my-enquiries.php">Reply Enquiries from Your Website</a></li>
		
		<li class="np npnew"><a href="my-contactdetails.php">Update Contact Details</a></li>
		<li class="np npnew"><a href="business-details.php">Update Business Information</a></li>
		</ul>-->
         <?php include "includes/seller-tools-panel.php"; ?>
		</div>
		<!--left navigation:ends-->  
        <div id="details" style="display:none;"></div>
        <div class="mctr mfl" id="listing">
		<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody>
		<tr>
		<td valign="TOP" width="100%"><style type="text/css">
.sub{display: none;}
.tbq{display: none;}
</style>

<style type="text/css">
.active_tab{background-color:#FF8080;}
.tab{background-color:#ACACAC;}
</style>


	<form style="margin:0px;" action="" name="form1">
        <table align="CENTER" border="0" cellpadding="0" cellspacing="0">
          <tbody><tr>
            <td valign="TOP" width="100%">
		<div class="wd1 mf18 mc5 mta2 mpb10"><!-- <Div class="mf11 bc mbl mbn">Trade Offers &#187</Div> --> Manage Sell Offers</div>
		
                <div id="masterdiv"><div id="sub2" style="display:inline;">
				<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tbody>
                
               
                
               
               <td align="LEFT" class="tab" valign="TOP" id="active_offer_tab">
                <?php
					$sql_active_cnt="select count(*) as cnt from sale_offer where so_usr_id='".$_SESSION['uid_indm']."' and so_approval_status='1' and so_status='1' and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)>=now() order by so_updated_date desc";
					$res_active_cnt=mysqli_query($con, $sql_active_cnt);
					$row_active_cnt=mysqli_fetch_object($res_active_cnt);
				?>
				<div class="mpl5 mpr5 mf13 mc1 mpt10" style="vertical-align:middle"><nobr><a class="hover mtd mb" onClick="showActOffer();" style="cursor:pointer;">Live /Active Offer</a> (<?php echo $row_active_cnt->cnt; ?>)</nobr></div></td>
               
				<td align="LEFT" class="active_tab" valign="TOP" id="approval_pending_tab">
                <?php
					$sql_pending_cnt="select count(*) as cnt from sale_offer where so_usr_id='".$_SESSION['uid_indm']."' and so_approval_status='0' and so_status='1' order by so_updated_date desc";
					$res_pending_cnt=mysqli_query($con, $sql_pending_cnt);
					$row_pending_cnt=mysqli_fetch_object($res_pending_cnt);
				?>
				<div class="mpl5 mpr5 mf13 mc1 mpt10" style="vertical-align:middle">
                <nobr><a class="hover mtd mb" onClick="showApprPending();" style="cursor:pointer;">Approval Pending</a> (<?php echo $row_pending_cnt->cnt; ?>)</nobr>
                </div></td>
				
                <td align="LEFT"  class="tab" valign="TOP" id="expired_offer_tab">
                <?php
					$sql_expired_cnt="select count(*) as cnt from sale_offer where so_usr_id='".$_SESSION['uid_indm']."' and so_approval_status='1' and so_status='1' and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)<now() order by so_updated_date desc";
					$res_expired_cnt=mysqli_query($con, $sql_expired_cnt);
					$row_expired_cnt=mysqli_fetch_object($res_expired_cnt);
				?>
				<div class="mpl5 mpr5 mf13 mc1 mpt10" style="vertical-align:middle"><nobr><a class="hover mtd mb" onclick="showExpiredOffer();" style="cursor:pointer;">Expired Offer</a> (<?php echo $row_expired_cnt->cnt; ?>)</nobr></div></td>
                
				<td align="LEFT" valign="TOP"><img src="images/zero.gif" height="1" width="5"></td><td align="RIGHT" background="images/topline-bg.gif" width="100%">
				<div id="post-new"><!--<input name="Submit1" id="pno" value="Post a New Offer" style="width: 120px; margin-bottom: 5px;" onmouseover="hsb()" type="button">-->
				<p id="sellb" class="sellb mf13 fw mb3" style="display:block" onmouseover="hsb()" onmouseout="hsb1()">
                <a href="post-buy-req.php">Post Buy Offer</a>&nbsp;|&nbsp;<a href="post-sell-offer.php">Post Sell Offer</a>
                </p>
				</div></td>
				</tr>
                
				</tbody>
                </table>
<script type="text/javascript">
$( document ).ready(function() {
							 
							 
	<?php if($row_active_cnt->cnt > $row_pending_cnt->cnt && $row_active_cnt->cnt > $row_expired_cnt->cnt){	?>
	
	showActOffer();
	
	<?php }else if($row_expired_cnt->cnt > $row_pending_cnt->cnt && $row_expired_cnt->cnt > $row_active_cnt->cnt){ ?>
	showExpiredOffer();
	
	<?php }else{	?>
	showActOffer();
	
	<?php } ?>
});
</script>
            <div id="res"></div>
            
            
            
            
            
            </div>
            
            </div> 
	</td>
	<td valign="TOP"><img src="images/zero.gif" height="1" width="10"></td>
	</tr>
	</tbody></table>
	</form><table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td height="30"></td>
	<td>
	<div class="liv" style="margin-right:20px;" align="RIGHT"><b></b></div></td>
	</tr>
	</tbody></table><div style="clear:both"><br></div><div align="CENTER"><br></div><div align="CENTER"><br><br></div></td></tr></tbody></table></div>
    
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>