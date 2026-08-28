<?php
include "common.php";

$_SESSION['last_page']="manage-purchased-buyleads.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];
if(isset($_GET['pageno'])) 
{
$pageno = $_GET['pageno'];
}
else 
{
$pageno = 1;
}
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





<style id="poshytip-css-tip-yellowsimple" type="text/css">div.tip-yellowsimple{visibility:hidden;position:absolute;top:0;left:0;}div.tip-yellowsimple table, div.tip-yellowsimple td{margin:0;font-family:inherit;font-size:inherit;font-weight:inherit;font-style:inherit;font-variant:inherit;}div.tip-yellowsimple td.tip-bg-image span{display:block;font:1px/1px sans-serif;height:10px;width:10px;overflow:hidden;}div.tip-yellowsimple td.tip-right{background-position:100% 0;}div.tip-yellowsimple td.tip-bottom{background-position:100% 100%;}div.tip-yellowsimple td.tip-left{background-position:0 100%;}div.tip-yellowsimple div.tip-inner{background-position:-10px -10px;}div.tip-yellowsimple div.tip-arrow{visibility:hidden;position:absolute;overflow:hidden;font:1px/1px sans-serif;}
table td{text-align: center;}
</style>

</head>
<body>

		<!--main div:start-->
	<div class="hm1 bbc" id="res-mob1">
<!-- Header start Here::-->
		<?php include "includes/header_new.php"; ?>
       <br><br>
		<div class="bt"><img src="images/z.gif" alt="<?php echo get_page_settings(4);?>" height="1" width="1"></div>
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
		<li class="np npnew"><a href="subscription.php">Purchase Credits</a></li>
		<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
		<li class="np npnew"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
		<li class="np npnew"><a class="leftindi txtcol" href="transaction_history.php">Transaction History</a></li>
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

        <table style="border-collapse: collapse;" align="center" border="1" bordercolor="#f0f0f0" cellpadding="3" cellspacing="0" width="100%">
		<tbody><tr>
			<td colspan="2" class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="19%"><b>Summary</b></td>
			<td colspan="2" class="lead" height="24" align="left" bgcolor="#FFDFDF" valign="middle" width="47%"><b>Total Lead Purchased: 
            <?php 
			$tot_lead = mysqli_num_rows(mysqli_query($con, "select bh_id from billing_history where bh_status = '1' and bh_type = '1' and bh_usr_id = '".$_SESSION['uid_indm']."'"));
			echo $tot_lead;
			?>
            </b></td>
			<td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>Amount Paid</b></td>
			<td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>Credit Purchased</b></td>
			<td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>Credit Used</b></td>
			<td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>Balance</b></td>
			<td class="lead" height="24" align="center" bgcolor="#FFDFDF" valign="middle" width="11%"><b>Expiry Date </b></td>
		</tr>
        
        
		<tr>
			<td class="tab-head" height="24" align="center" bgcolor="#f3f3f3" valign="middle"><b>Sl.No.</b></td>
			<td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>Date</b></td>
			<td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>Description</b></td>
            <td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b>From</b></td>
            <td class="tab-head" height="24" align="left" bgcolor="#f3f3f3" valign="middle"><b></b></td>
			<td class="tab-head" height="24" align="right" bgcolor="#f3f3f3" valign="middle"><b></b></td>
			<td class="tab-head" height="24" align="right" bgcolor="#f3f3f3" valign="middle"><b>0</b></td>
			<td class="tab-head" height="24" align="right" bgcolor="#f3f3f3" valign="middle"><b>0</b></td>
		</tr>
        <?php 
		$bh_sql = mysqli_query($con, "select * from billing_history where bh_status = '1' and bh_usr_id = '".$_SESSION['uid_indm']."'");
		
		 $totitems=mysqli_num_rows($bh_sql);
		 
		 $limits = 10;
		 $total_pages = ceil($totitems/$limits); 
		 $start_limit=$limits *($pageno-1);
		
		$bh_res = mysqli_query($con, "select * from billing_history where bh_status = '1' and bh_usr_id = '".$_SESSION['uid_indm']."' limit ".$start_limit.",".$limits);
		
		if(mysqli_num_rows($bh_res) > 0){
			$i = 1;
			while($bh_row = mysqli_fetch_object($bh_res)){
				
				$br_res = mysqli_query($con, "select * from buy_requirement,user where usr_id=br_u_id and br_id = '".$bh_row->bh_from."' and br_approval_status = '1' and br_display_status = '1'");
				$br_row = mysqli_fetch_object($br_res);
				
				$tnd_res = mysqli_query($con, "select * from tender,user where usr_id=tnd_usr_id and tnd_id = '".$bh_row->bh_from."'");
				$tnd_row = mysqli_fetch_object($tnd_res);
				
				$auc_res = mysqli_query($con, "select * from auction,user where usr_id=auc_usr_id and auc_id = '".$bh_row->bh_from."'");
				$auc_row = mysqli_fetch_object($auc_res);
				
				$sub_res = mysqli_query($con, "select pm.expiry_date from plan_member_id pm JOIN business_profile bf ON pm.b_id = bf.bnsprof_id JOIN user u ON u.usr_id=bf.bnsprof_uid WHERE u.usr_id ='".$_SESSION['uid_indm']."'");
				$sub_row = mysqli_fetch_object($sub_res);
		?>
        <tr>
			<td class="tab-head" height="24" align="center" valign="middle"><b><?php echo $i;?></b></td>
			<td class="tab-head" height="24" align="left" valign="middle"><b><?php echo date('d M, y',strtotime($bh_row->bh_updated_date));?></b></td>
			<td class="tab-head" height="24" align="left" valign="middle"><b><?php if($bh_row->bh_type == '1'){echo 'Credit Purchased';}else if($bh_row->bh_type == '2'){echo 'Credit Used For Buy Leads';}else if($bh_row->bh_type == '3'){echo 'Credit Used For Tender';}else if($bh_row->bh_type == '4'){echo 'Credit Used For Auction';}else if($bh_row->bh_type == '5'){echo 'Subscription Payment';}?></b></td>
			<td class="tab-head" height="24" align="left" valign="middle"><b>
			<?php 
				if($bh_row->bh_type == '1')
				{
					echo $bh_row->bh_from." <span style='font-weight:300'>Transaction Id </span><br/>(".$bh_row->bh_txn_id.")";
				}
				else if($bh_row->bh_type == '2')
				{
					if(mysqli_num_rows($br_res) > '0')
					{
						echo "<a target='_blank' href='buyleads-details.php?id=".rand(1000,9999).md5($br_row->br_id)."'>".ucfirst($br_row->fname." ".$br_row->lname)."</a>";
					}
					else
					{
						echo "<font color='red'>Inacative Buy Lead</font>";
					}
				}
				else if($bh_row->bh_type == '3')
				{
					if(mysqli_num_rows($tnd_res) > '0')
					{
						echo "<a target='_blank' href='tender-details.php?id=".rand(1000,9999).md5($tnd_row->tnd_id)."'>".ucfirst($tnd_row->fname." ".$tnd_row->lname)."</a>";
					}
					else
					{
						echo "<font color='red'>Inacative Tender</font>";
					}
				}
				else if($bh_row->bh_type == '4')
				{
					if(mysqli_num_rows($tnd_res) > '0')
					{
						echo "<a target='_blank' href='auction-details.php?id=".rand(1000,9999).md5($tnd_row->auc_id)."'>".ucfirst($auc_row->fname." ".$auc_row->lname)."</a>";
					}
					else
					{
						echo "<font color='red'>Inacative Auction</font>";
					}
				}				

			?></b></td>
			<td class="tab-head" height="24" align="left" valign="middle"><b><?php echo $bh_row->bh_currency_code.$bh_row->bh_amount;?></b></td>
			<td class="tab-head" height="24" align="right" valign="middle"><b><?php echo $bh_row->bh_credit_purchased;?></b></td>
			<td class="tab-head" height="24" align="right" valign="middle"><b><?php echo $bh_row->bh_credit_used;?></b></td>
			<td class="tab-head" height="24" align="right" valign="middle"><b><?php echo $bh_row->bh_user_balance;?></b></td>
			<td class="tab-head" height="24" align="right" valign="middle"><b><?php if($bh_row->bh_type == '5')
				echo date("d M, y", strtotime($sub_row->expiry_date)); 
				else
				echo '';
			?></b></td>			
		</tr>
        
        <?php 
			$i++;}
		}?>
        
        </tbody></table>
        <br />
<style>
.pagination
{
	float:right;
	padding: 0px 10px;
	
}
.pagination a {
	color: #333;
	font-weight:bold; 
	text-decoration:none;
	border: 1px solid #333;
	padding: 3px 9px;
	background-color:#f3f3f3;
	border-radius: 3px
	}

</style>
        <div class="pagination">
   <?php
   if ($pageno>1){
   ?>	
  <a href="transaction_history.php?pageno=<?php echo $page=($pageno-1);?>" style="width:65px;">« Prev</a>
   <?php 
   }
   for ($i = 1; $i <= $total_pages; $i++){
   if ($pageno == $i)
   {
   ?>
   <span id="pageno"><?php echo $i;?></span>
   <?php } else { ?>
   <a href="transaction_history.php?pageno=<?php echo $i;?>"><?php echo $i;?></a>       
   <?php
   }
   }
   if ($pageno<$total_pages)
   {
   ?>     
   <a style="width:65px;" href="transaction_history.php?pageno=<?php echo $page=($pageno+1);?>" >Next »</a>
   <?php }?>
   </div>

	</form><div style="clear:both"><br></div>

</td></tr></tbody></table></div>

		<div id="detail_req" style="display:none;"></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php';?>