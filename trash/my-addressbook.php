<?php
include "common.php";

$_SESSION['last_page']="my-addressbook.php";
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
<link href="css/my2.css" type="text/css" rel="stylesheet">
<link href="css/add.css" type="text/css" rel="stylesheet">

<!--[if IE 6]><link rel="stylesheet" type="text/css" href="css/ie6.css" /> <![endif]-->
<!--[if IE 9]><style>.nmz4 li{display:inline;list-style:none;padding:0px 2px 0px 2px;color:#fff}</style><![endif]-->
<!-- js start -->
<style>
.starRating .starActive{background-image:url(images/sth.gif);}
.starRating .starDactive{background:url(images/nst.gif);}
</style>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
	showAddressBook(1);
});
function showAddressBook(page)
{
	//$('#mail-details').html('');
//	$('#mail-details').css("display","none");
//	$('#mail').css("display","block");
//	
//	$(".inbox").addClass("txtcol");
//	$(".sent").removeClass("txtcol");
	$.post("ajax-file/showAddressBook.php",{page:page},    function(data){ $('#res_list').html(data); });
}
function detailContact(uid,mid,pg)
{
//	$.post("ajax-file/contactDetails.php",{id:id},    function(data){ $('#res_list').html(data); });
	
	$("#loading").css("display","block");
	
	setTimeout(function () {
		$.post("ajax-file/contactDetails.php",{uid:uid,mid:mid,pg:pg},    function(data){    
			$("#loading").css("display","none");
			$('#res_list').css("display","none");
			$("#res_detail").css("display","block");
			$('#res_detail').html(data);
		});
	}, 500);
}
function back_to_list(pg)
{
	$("#res_detail").css("display","none");
	$('#res_list').css("display","block");
	showAddressBook(pg);
}
function blockUser(blockBy,blocked)
{
	if(confirm("Are you sure to block this user?"))
	{
		$.post("ajax-file/addBlockUser.php",{blockBy:blockBy,blocked:blocked},    function(data){
			$("#res_detail").css("display","none");
			$('#res_list').css("display","block");
			showAddressBook(1);
		});
	}
}
</script>
</head>
<body>

		<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->
		<?php include "includes/header_new.php";?>
        
        <br><br>
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<!-- Header End Here::-->

<div class="inner_wrapper">
    	<?php include "includes/header_menu.php"; ?>
			
		<!--left navigation:start-->
		<div class="f1 w61n tb lh ml br" id="lnav"><ul id="enqulid" class="nln1" style="margin: 0px; padding: 0px;">
				<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Enquiries</h3></li>
                <li class="np"><a href="my-enquiries.php" class=" me inbox bnr">Inbox </a></li>
                <li class="np"><a href="my-enquiries.php" class=" me sent bnr">Sent Box </a></li>
                <li style="border-bottom: medium none;">
				<h3 style="height:18px;">
					<a href="javascript:showfolders();" id="folimg" class="mf_h me bnr f1">My Folders</a>
					<a href="javascript:newfol();" id="m2_w2nf" class=""></a>
				</h3>
				</li>
			<span id="m2_nf" style="display:none;">
			<li style="border-bottom:0;">
				<table border="0" cellpadding="0" cellspacing="3" width="100%">
					<tbody>
						<tr>
							<td><input class="mu11" style="width: 128px;font-size:10px;" id="m2_nfn" name="m2_nfn" type="text"></td>
							<td width="45"><input value="Add" onclick="addfolder();" class="fadb me bnr" type="button"></td>
							<td width="10"><input value="" onclick="newfol();" class="me ffc bnr" type="button"></td>
						</tr>
					</tbody>
				</table>
			</li>
			</span>
			<span id="allfol" style="display:block;"></span>
			</ul>
			<ul id="m2_sep">&nbsp;</ul>
		<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
			<li style="border-bottom: medium none;"><h3>Address Book</h3></li>
			<li class="np npnew"><a class="leftindi txtcol" href="my-addressbook.php">»&nbsp;Contacts List</a></li>
			<li class="np npnew"><a href="my-blocklist.php">»&nbsp;Blocked User List</a></li>
			<li class="np npnew"><a href="manage-purchased-buyleads.php">»&nbsp;Purchased Buy Leads</a></li>
		
		<li style="border-bottom: medium none; margin-top: 40px;"><h2>You may also like to</h2></li>
		<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
		<li class="np npnew"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
		<li class="np npnew"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
		<li class="np npnew"><a href="transaction_history.php">Transaction History</a></li>
		</ul></div>
		<!--left navigation:ends-->
        
        <div id="res_list"></div>
        <div id="res_detail"></div>
        
        
        
        
		<div class="c3">&nbsp;</div></div>
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>