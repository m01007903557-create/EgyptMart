<?php
include "common.php";

$br_id=substr($_GET['id'],4);



$credit_available=0;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<link href="css/trade-7.css" rel="STYLESHEET" type="text/css">
<link href="css/trade-detail1.css" rel="STYLESHEET" type="text/css">
<link href="css/bl_form_temp1.css" rel="stylesheet" type="text/css">

<style type="text/css">
.form_area{background:#FFF;border:1px solid  #bebebe;width:683px;min-height: 216px;text-align: left;font-family: arial;border-radius: 5px;-webkit-border-radius: 3px;-moz-border-radius:5px;box-shadow:0px 1px 11px rgba(0,0,0,0.30);-webkit-box-shadow:0px 1px 11px rgba(0,0,0,0.30);-moz-box-shadow:0px 1px 11px rgba(0,0,0,0.30);padding-top:3px;}
.sbtn, .sbtn:focus {color: #000; font-family: arial; font-size: 18px; font-weight: bold; padding: 6px 15px 6px 15px; -webkit-border-radius: 3px; -moz-border-radius: 3px;margin-top: 5px; width: 288px; clear: both; border: solid #db8700 1px; background: #ffcb65; background: -webkit-gradient(linear, 0 0, 0 100%, from(#ffcb65), to(#ffb13a)); background: -moz-linear-gradient(top, #ffcb65, #ffb13a); -ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffcb65, endColorStr=#ffb13a); filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffcb65, endColorStr=#ffb13a); text-align:center;cursor:pointer;border-radius: 3px 3px 3px 3px;height:auto!important}
.sbtn:hover{background: #ffcb65;background: -webkit-gradient(linear, 0 0, 0 100%, from(#ffb13a), to(#ffcb65)); background: -moz-linear-gradient(top, #ffb13a, #ffcb65); -ms-filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffb13a, endColorStr=#ffcb65); filter: progid:DXImageTransform.Microsoft.gradient(startColorStr=#ffb13a, endColorStr=#ffcb65); text-align:center}
input.q_pro, input.stat_name, textarea.desq{border: 1px solid rgb(211, 211, 211)!important;border-top: 1px solid rgb(163, 163, 163)!important;}
#eto_ofr_ftr_frm{padding-bottom:18px!important}
.sh{font-size:12px;font-weight:bold;margin:0px 13px 0px 13px;padding:3px 0 8px 0; border-bottom:1px dotted #bebebe}
.tx_h{background-image:url(images/fform-main10.png);background-repeat:no-repeat}
</style>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
function purchaseLead(id)
{
	if(confirm('Are you sure to get this Lead?'))
	{
		$.post("ajax-file/purchaseBuyLead.php",{id:id},    function(data){ $("#buy_alert_msg").removeClass("doff"); });	
	}
}
function open_alert_close()
{
	window.location.reload();	
}
function showMessage()
{
	alert('Please purchase credits to Get this Lead.');	
}
</script>
<script type="text/javascript">
function choosePackage(id)
{
	window.location.href="payment-option.php?id="+id;
}
</script>
<!-- code for image zoom start here -->
<script type="text/javascript" src="js/mojozoom.js"></script>  
<link type="text/css" href="css/mojozoom.css" rel="stylesheet" />  
<!-- code for image zoom end here -->
<script type="text/javascript">
function sendEnquiry()
{
	var msg_from=document.getElementById('msg_from');
	var msg_to=document.getElementById('msg_to');
	var msg_subject=document.getElementById('msg_subject');
	var msg_message=document.getElementById('msg_message');
	var lead_headline = document.getElementById('lead_headline').value;
	var msg="";
	var valid=true;
	if(msg_message.value == '' || msg_message.value == null)
	{
		msg="Please fill in your enquiry.";

		valid=false;
	}
	else if(msg_message.value.length < 20)
	{
		msg="Enquiry must be atleast 20 characters length.";
		msg_message.focus();
		valid=false;
	}
	
	if(valid==false)
	{
//		$("#errmsg").html(msg);
//		$("#errmsg").css("display","block");
		alert(msg);
		msg_message.focus();
	}
	else
	{
		$("#enqloading").css("display","block");
		$("#enqloading1").css("display","none");
		
		
		$.post("ajax-file/sendMessage.php", {lead_headline:lead_headline,msg_from:msg_from.value,msg_to:msg_to.value,msg_subject:msg_subject.value,msg_message:msg_message.value}, function(data){

			if(data==1)
			{
				setTimeout(function () {
					alert('Your enquiry has been sent successfully');
					$("#enqloading").css("display","none");
					$("#enqloading1").css("display","block");
					msg_message.value="";
				}, 500);
			}
			else
			{
				setTimeout(function () {
					alert('Your enquiry not sent successfully. Please try after sometime');
					$("#enqloading").css("display","none");
					$("#enqloading1").css("display","block");
				}, 500);
			}
		});	
	}
}
</script>
</head>
<body>


<div class="q_hm1">
<!-- Header start Here::-->

<?php include "includes/header_new.php"; ?>

<div class="q_bt"><img src="images/zero.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>

<p class="q_c3"></p>
<?php
$sql="select * from buy_requirement,product_category_arabyos,user,business_profile where br_pc_id=pc_id and br_u_id=usr_id and usr_id=bnsprof_uid and md5(br_id)='".$br_id."'";
$res=mysqli_query($con, $sql);

$row=mysqli_fetch_object($res);
	$sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name,s.pc_sort_name from product_category_arabyos m,product_category_arabyos c,product_category_arabyos s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and s.pc_id='".$row->br_pc_id."'";
	$res_pcat=mysqli_query($con, $sql_pcat);
	$row_pcat=mysqli_fetch_array( $res_pcat);
?>

	<div align="CENTER" style="width:100%; max-width:1170px; margin: 0 auto!important;">
	<div style="width:100%;">
	 <div class="p3 pl lf mm"><a href="buyleads.php" class="c12 td">Buy Leads</a> &nbsp;&gt; &nbsp; 
     <a href="category.php?token=<?php echo rand(1000,9999).md5($row_pcat[0]);?>" class="c12" style="text-decoration:none"><?php echo ucwords($row_pcat[1]); ?></a>
     &nbsp;&gt;&nbsp;<a href="catcompany.php?token=<?php echo rand(1000,9999).md5($row_pcat[2]);?>" class="c12" style="text-decoration:none"><?php echo ucwords($row_pcat[3]); ?></a>
     &nbsp;&gt;&nbsp;<?php echo ucwords($row_pcat[4]); ?><br>
     </div>
     </div>
	<div style="float:left;width:70%;text-align:left">
	<div class="e5 lbx" style="margin-bottom:4px;" id="lftdsc">
    <h1 class="f6 cl2" style="display:inline" id="lead_headline"><?php echo $row->br_pd_name; ?> </h1>
    
    <?php 
	$cid=rand(1000,9999).md5($row->bnsprof_id);
	
	if($row->br_preferred_supplier_location!=''){
	 ?>
    - <span class="f5">
	<?php
    	if($row->br_preferred_supplier_location=='any')
		{
			echo "Anywhere";	
		}
    	else if($row->br_preferred_supplier_location=='abroad')
		{
			echo "Foreign";	
		}
		else if($row->br_preferred_supplier_location=='domestic')
		{
			echo get_country_name($row->country);	?>
			<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" height="16" width="24">&nbsp;&nbsp;
            <?php
		}
		else if($row->br_preferred_supplier_location=='my_city' && $row->bnsprof_city!='0')
		{
			echo get_city_name($row->bnsprof_city);	?>
			
            <?php
		}
	?>
    </span> 
    <?php } ?>
	<?php
	$uid=$_SESSION['uid_indm'];
	if($uid != ''){
	if($row->bnsprof_compname !=''){
		$sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join user u on sip.mp_id = u.usr_mp_id where u.usr_id = ".$uid;
			
			$get_icon = mysql_query($sql_icon) or die(mysql_error());
			?>
			<span>
			<?php if(mysql_num_rows($get_icon) > 0){ 
				$title = 'Junior';
				$icon = mysql_fetch_array($get_icon);
				
				if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
				$title = 'Senior';
				}
				else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
				$title = 'Sponsor';
				}								
				if($title == 'Junior') {?>
					<img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;" alt=""/>
				<?php } else { ?>
				<a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;" alt=""/></a>
				<?php } ?>
                                                                                              
																	<?php }?>
																																			   </span>
																	<?php } }?>
			</span>
    <span class="vlogoB1 tooltip2 valb mb1"><span class="g9 d1" style="font-weight:bold;padding:0px 2px 0px 21px; line-height:19px; display:inline-block;  background:#0095f9 url('images/verified-sign.jpg') left no-repeat;">Verified &amp; Updated</span></span><div style="padding-bottom:4px;margin-top:12px">
<p style="color: rgb(185, 184, 184);float:right; text-align: right;" class="j1 cb"><font style="color: rgb(152, 151, 151);">Requirement Last Updated :</font> <?php echo date("d M, Y",strtotime($row->br_updated_date)); ?><!-- IST--> | <font style="color: rgb(152, 151, 151);">Posting Date :</font> <?php echo date("d M, Y",strtotime($row->br_posting_date)); ?></p>
<div>
<img src="upload/buy_requirement/thumb/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image.png";	} ?>" id="zoomImage<?php echo $row->br_id; ?>" border="0" height="100" hspace="0" vspace="0" width="125" data-zoomsrc="upload/buy_requirement/<?php echo $row->br_pic; ?>"/>
    <div style="position: absolute; left: 0px; top: 0px; width: 380px; height: 400px; overflow: hidden; display: none;">
<div style="width: 256px; height: 205px;" class="mojozoom_marker">
<div class="mojozoom_fill"></div>
<div class="mojozoom_border"></div>
</div>
</div>
</div>
	<span class="c12 bo fs" style="font-size:14px">Buy Lead Details:</span>
	<div class="bdt" id="hdiv1" style="padding-top:5px">
            <div class="g2 fs k7" style="word-wrap:break-word;">
              <div><?php echo stripslashes($row->br_requirement); ?><br>
              <?php if($row->br_estimate_qty!='0' && $row->br_estimate_qty != ''){ ?>
              <div><div><span class="c13"><strong>Quantity </strong>: <?php echo $row->br_estimate_qty; ?>&nbsp;<?php echo measurement_unit($row->br_estimate_qty_unit); ?></span></div><br></div>
              <?php	}	?>
              
      <br>
      <?php
	  if($_SESSION['uid_indm'] != ''){
			$query_mp = mysql_query("SELECT mst_name FROM smembership_plan
		 mp JOIN user u ON u.usr_mp_id = mp.mp_id 
		 WHERE u.usr_id= ".$_SESSION['uid_indm']);
		 $row_mp = mysql_fetch_object($query_mp);
		 $membership_plan = $row_mp->mst_name;
		}
	  if(($row->br_apprx_order_value!='' && $row->br_apprx_order_currency!='') || $row->br_description!='' || ($row->br_website!="http://" && $row->br_website!='') || $row->br_need_quote_for!='' || $row->br_purchase_time!='' || $row->br_preferred_supplier_location!='' || $row->br_need_for!='' || $row->br_requirement_frequency){
		  ?>
      <span class="bdd bo"><span class="artb sbg"></span> Additional Information Provided by Buyer</span>
      <div class="c15 pt4 f1 pl">
      <?php
	  if($row->br_apprx_order_value!='' && $row->br_apprx_order_currency!=''){ ?>Approximate order value : <?php echo $row->br_apprx_order_currency; ?>&nbsp;<?php echo $row->br_apprx_order_value; ?><br> <?php } ?>
	  <?php if($row->br_description!=''){ ?>Application/Usage : <?php echo stripslashes($row->br_description); ?><br><?php } ?>
	  <?php if($row->br_website!=''){ ?>Website : <?php echo stripslashes($row->br_website); ?><br><?php } ?>
  	  <?php if($row->br_need_quote_for!=''){ ?>Need Quotation <?php echo stripslashes($row->br_need_quote_for); ?><br><?php } ?>
  	  <?php if($row->br_purchase_time!=''){ ?>Want to make this purchase <?php echo stripslashes($row->br_purchase_time); ?><br><?php } ?>	
  	  <?php if($row->br_preferred_supplier_location!=''){ ?>Preferred suppliers location : <?php 
	  
	  if($row->br_preferred_supplier_location=='any')
		{
			echo "Anywhere";	
		}
    	else if($row->br_preferred_supplier_location=='abroad')
		{
			echo "Foreign";	
		}
		else if($row->br_preferred_supplier_location=='domestic')
		{
			echo get_country_name($row->country);	?>
            &nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" height="16" width="24">
            <?php
		}
		else if($row->br_preferred_supplier_location=='my_city' && $row->bnsprof_city!='0')
		{
			echo get_city_name($row->bnsprof_city);	?>

            <?php
		}
	  
	  ?><br><?php } ?>
  	  <?php if($row->br_need_for!=''){ ?>Need this for <?php echo stripslashes($row->br_need_for); ?><br><?php } ?>
	  <?php if($row->br_requirement_frequency!=''){ ?>Requirement Frequency : <?php echo stripslashes($row->br_requirement_frequency); ?><br><?php } ?>
	  
      <br></div>
<?php } ?>
                </div></div></div></div><div style="clear: both;"></div>
	
	
    <div class="doff" id="buy_alert_msg" style="background:#fffdea;width:700px;position:fixed;top:50%;left:50%;font-family:arial;font-size:14px;padding:3px 10px 10px 10px;line-height:23px;border:4px solid #e4be75;z-index:99;margin-left:-300px;margin-top:-130px">
	<!--<span style="float:right;color:#0000ff;cursor:pointer;font-weight:bold" onClick="open_alert_close()">Close [X]</span>-->
	<div style="padding-top:10px">
	<b>Thank You for Purchasing the Buy Lead!</b><br>
	<div style="padding-left:5px;padding-top:3px">&#8226; This Buy Lead is saved in your "<a href="manage-purchased-buyleads.php">Purchased Buy Leads</a>" section
	
	<div style="height:10px;overflow:hidden"></div>
	&#8226; You can submit your response to this Buyer from "<a href="manage-purchased-buyleads.php">Purchased Buy Leads</a>"
	<?php if(strpos(strtolower($membership_plan), 'sponser') === false && strpos(strtolower($membership_plan), 'sponsor') === false  && strpos(strtolower($membership_plan), 'senior') === false ) { ?>
	<div style="height:10px;overflow:hidden"></div>
	&#8226; This purchase will reflect in your "<a href="transaction_history.php">Transaction History</a>" as well
	<?php } ?>
	<div style="height:10px;overflow:hidden"></div>
	&#8226; Write us at <a href="contact_us.php">Contact Us Page</a> within 7 days of purchase incase:
	<div style="font-size:12px;padding-left:10px">
	- You are unable to contact the buyer (Buyer's email id as well as phone number are wrong)<br>
	- Buyer's requirement fulfilled before lead purchase
	</div>
	</div></div>
	<div style="padding-top:5px" align="center"><input onClick="open_alert_close()" value="  OK  " style="font-size:16px;font-weight:bold" type="button"></div>
	</div>
    
    
    </div>
	
	</div>
	<!--end left panel-->
	
	<div class="wd9 lf gv" style="float:right;"> <!--TIME After Buy Lead Service Help: 0.086 -->
        
        <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=''){
			$uid=$_SESSION['uid_indm'];
		?>
        <div class="lgnDtl k7 mb3">
          <div class="f5 bdd">Welcome <?php echo user_info($uid,'name_prefix')."&nbsp;".user_info($uid,'fname');?></div>
		  <?php if(strpos(strtolower($membership_plan), 'sponser') !== false || strpos(strtolower($membership_plan), 'sponsor') !== false  || strpos(strtolower($membership_plan), 'senior') !== false ) { ?>
		  <span class="alrt"><span class="awa sbg"></span>
          <span class="c13 bo pl3" name="prcrdt" id="prcrdt"><a href="membership_plans.php" style="color: rgb(51, 51, 51);">Pay Annual Subscription </a></span>
          </span>
          <?php
		  } else {
          	$sql_usr="select * from user where usr_id='".$_SESSION['uid_indm']."'";
			$res_usr=mysqli_query($con, $sql_usr);
			$row_usr=mysqli_fetch_object($res_usr);
			if($row_usr->usr_credit<20){
		  ?>
          <span class="alrt"><span class="awa sbg"></span>You do not have any credit in your account!<br>
          <span class="c13 bo pl3" name="prcrdt" id="prcrdt"><a href="subscription.php" style="color: rgb(51, 51, 51);">Purchase Credits Now</a></span>
          </span>
          <?php	} }	?>
          </div>
        <?php } ?>
          
          <div id="rtmain" class="lbx1" style="background-color:#FAF4FF;">
			<p class="sbg d_bp1 bo f1"><?php echo date("d M, Y",strtotime($row->br_updated_date)); ?> <span class="x2 date_tp tooltip2 qmart sbg" id="Buy Requirement Last Updated on: <?php echo date("d M, Y",strtotime($row->br_updated_date)); ?> ### Original Posting Date: <?php echo date("d M, Y",strtotime($row->br_posting_date)); ?>">&nbsp;</span></p>
            
            <div class="ef3">
            
            <?php
			if($row->br_preferred_supplier_location==''){
			?>
            <img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" align="left" height="16" width="24">
            <span class="e4 f1 wb e5">&nbsp;<b><?php echo get_country_name($row->country); ?></b></span>
            <?php	}else{	?>
			<span class="e4 f1 wb e5">&nbsp;<b>
			<?php
			if($row->br_preferred_supplier_location=='any')
			{
				echo "Anywhere";	
			}
	    	else if($row->br_preferred_supplier_location=='abroad')
			{
				echo "Foreign";	
			}
			else if($row->br_preferred_supplier_location=='domestic')
			{	
				echo get_country_name($row->country); 
			?>
			&nbsp;&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" align="left" height="16" width="24">
		<?php	}
			else if($row->br_preferred_supplier_location=='my_city' && $row->bnsprof_city!='0')
			{
				echo get_city_name($row->bnsprof_city);

			}
			?></b></span>
            <?php	}	?>
            
            </div>
            
            <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=$row->br_u_id){	?>
			
                <?php
				
                	$sql_chk="select * from purchased_buy_requirement where pbr_usr_id='".$_SESSION['uid_indm']."' and pbr_br_id='".$row->br_id."'";
					$res_chk=mysqli_query($con, $sql_chk);
					
					
					if(mysqli_num_rows($res_chk)>0){
						$row_chk=mysqli_fetch_object($res_chk);
				?>
                <div id="sourcediv1">
            	<div class="mt12 l1 k7 mb">
                    <div class="btn1 point mt12 f4" id="buybtn" style="line-height: 20px;padding: 5px 27px;" onClick="purchaseLead(<?php echo $row->br_id; ?>);">
					    <span class="f1">Purchased on: <?php echo date("d M, Y",strtotime($row_chk->pbr_purchase_date)); ?></span>
    	                <div class="inAr sbg"></div>
                    </div>
                    </div>
				</div>
				<div style="position: relative;" id="rtmain1"> <div class="rit_ar" style="width:267px;margin-left:-22px;"><div id="topref" style="_position:static !important" itemscope=""><p class="m2"></p><p class="tr mr6 w1">  

<?php

if($row->bnsprof_yoe!='')
{
	$yr_diff=intval(date("Y"))-$row->bnsprof_yoe;
	if($yr_diff>0){
	?>
	<span title="<?php echo $yr_diff; ?> year of Membership" class="opacity b1 vam mems tc"><span title="<?php echo $yr_diff; ?> year of Membership" class="sp-mem1"><?php echo $yr_diff; ?><span class="sp-mem2">yr</span></span></span>
	<?php }
}

	?>


</p>
<?php if(strpos(strtolower($membership_plan), 'sponser') !== false || strpos(strtolower($membership_plan), 'sponsor') !== false ) { ?>
<p class="vcl txl ef3 lh21 pr2 rd f2 bo ml27"><?php echo stripslashes($row->bnsprof_compname); ?>  
<?php if($row->bnsprof_yoe!='' && $row->bnsprof_yoe!='0'){	?>
<span class="estd" style="margin-top:3px">(Estd.<span style="margin-left:5px"><?php echo $row->bnsprof_yoe; ?></span>)</span>
<?php } ?>
</p>
<p itemprop="address" itemscope="" class="txl txt1 vcl3 mt5 cn_cl ml27 lh21"><?php echo $row->name_prefix; ?> <?php echo $row->fname; ?> <?php echo $row->lname; ?>
		<br><!--<span itemprop="addressLocality">Accra</span>, <span itemprop="addressRegion">Greater Accra</span>, -->
<?php } ?> 
         <?php if($row->country!='0' && $row->country!=''){  ?>
        <span itemprop="addressCountry"><?php echo get_country_name($row->country); ?>&nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" class="w4" align="top" height="15" width="23"></span>
        <?php	}	?>
		<?php if(strpos(strtolower($membership_plan), 'sponser') !== false || strpos(strtolower($membership_plan), 'sponsor') !== false ) { ?>
        </p>
        
        
        <?php if(isset($_SESSION['uid_indm']) && $row->mobile1!='' && $row->mobile1!='0'){	?>           
        <p class="mt2 ml27 cn_cl"><span class="sbg ph a1"></span><span itemprop="telephone">+(<?php echo $row->country_ph_code; ?>)-<?php echo $row->mobile1; ?></span></p>
        <?php } } ?>       
        
        
        <?php	if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!=$row->usr_id){	?>
        <div name="logein" id="TP" class="form-container" style="margin-left:6px;">
			<a id="clx" style="display:none" class="clx1" href="#TP"><div id="cls" class="form-close"><img alt="" src="images/zero.gif" class="bg close-image" border="0" height="16" width="16"></div></a>
			<p class="form-caption">Send E-mail Enquiry</p>
			
		<div class="form-block"> 
		<p class="form-tagname">Message:</p>
        <input type="hidden" id="msg_from" name="msg_from" value="<?php echo $_SESSION['uid_indm']; ?>" />
	    <input type="hidden" id="msg_to" name="msg_to" value="<?php echo $row->usr_id; ?>" />
    	<input type="hidden" id="msg_subject" name="msg_subject" value="<?php echo "Enquiry for Buyleads"; ?>" />
		<textarea id="msg_message" name="msg_message" class="form-textarea" style="width:242px;"></textarea>
		</div>

		
		<div id="enqloading1" >
        	<input onClick="sendEnquiry();" class="point sndb" value="Contact this Supplier NOW !" name="" style="padding: 5px 3px; margin-left: 6px; margin-right: 0px; width: 240px; font-size: 16px;" type="button">
        </div>
		<div style="text-align:center;display:none;margin:14px 0;" class="bo" id="enqloading"><img src="images/indicator.gif" align="absmiddle">&nbsp;Processing...</div>
		
		<!--main form div ends here--></div>
        <?php	}	?>
        
        </div></div>
        <p class="c3"></p></div></div>
                
                    <?php }else{
						$sql_usr_chk="select * from user where usr_id='".$_SESSION['uid_indm']."'";
						$res_usr_chk=mysqli_query($con, $sql_usr_chk);
						$row_usr_chk=mysqli_fetch_object($res_usr_chk);
						if($row_usr_chk->usr_credit>20){	$credit_available=1;	}
						?>
                <div id="sourcediv1">
            	<div class="mt12 l1 k7 mb">
				<?php
				if(strpos(strtolower($membership_plan), 'sponser') === false && strpos(strtolower($membership_plan), 'sponsor') === false  && strpos(strtolower($membership_plan), 'senior') === false ) {?>
					<div class="btn1 point mt12 f4" id="buybtn" style="line-height: 20px;padding: 5px 27px;" <?php if(getUserCredit($_SESSION['uid_indm'])>=20){ ?> onClick="purchaseLead(<?php echo $row->br_id; ?>);" <?php }else{ ?> onClick="showMessage();" <?php } ?>>
                    	Get this Lead Now<br>
					    <span class="f1">And Contact Buyer</span>
    	                <div class="inAr sbg"></div>
        	            <div id="tps" class="doff sbg g1 k7">Purchasing of this Lead will make the full contact details visible to you for sending response to Buyer</div>
                    </div>
					</div>
					<div class="f3 mt11"> in <strong class="z6 f4">20 Credits - if you are not member yet !</strong><!-- for <span class="WebRupee">Rs.</span> 449/---></div>
				<?php } else {?>
					<div class="btn1 point mt12 f4" id="buybtn" style="line-height: 20px;padding: 5px 27px;" onClick="purchaseLead(<?php echo $row->br_id; ?>);">
                    	Get this Lead Now<br>
					    <span class="f1">And Contact Buyer</span>
    	                <div class="inAr sbg"></div>
        	            <div id="tps" class="doff sbg g1 k7">Purchasing of this Lead will make the full contact details visible to you for sending response to Buyer</div>
                    </div>
					</div>
				<?php } ?>
			</div>    
                    <?php
						
					} ?>

            <?php	}	?>
    
            </div>
		<table cellpadding="0" cellspacing="0" width="71%">
		<tbody><tr><td>
		<div class="c13" id="pkg" style="margin-top:20px;text-align:center;">
		<!--<p class="mtm3 bo fts22 c12 w3">OR</p>-->
        

<?php if(strpos(strtolower($membership_plan), 'sponser') === false && strpos(strtolower($membership_plan), 'sponsor') === false  && strpos(strtolower($membership_plan), 'senior') === false ) {
		if(getUserCredit($uid)==0){	?>
        
        <h2 class="f4 ts1 w3">Select Credit Plan<span class="x2 date_tp tooltip4 qmart1 sbg" id="Credit Plans consists of Credits which you will need to contact the buyer. These Credits will be added into your account, once you purchase any package.">&nbsp;</span></h2>
            <div class="pkg">
            <?php

$sql_mp="select * from membership_plan where mp_status='1'";
$res_mp=mysqli_query($con, $sql_mp);
while($row_mp=mysqli_fetch_object($res_mp)){
?>
            <p class="c13 bdd" style="line-height:26px;font-size:16px;text-align:center !important;">
               <?php echo $row_mp->mp_name; ?><!-- - <span class="c14" style="font-weight:bold">Save 47%</span>--><br>
                <span class="c12 bo f3"><?php echo $row_mp->mp_credits; ?> Credits for <span class="WebRupee f4"><?php echo getCurrencySymbol(); ?></span> <?php echo $row_mp->mp_amount; ?></span><br>              
                <a onClick="choosePackage('<?php echo rand(10000,99999).md5($row_mp->mp_id); ?>');" class="point" style="font-size:14px;padding:2px 8px; background:#0e4ec7; color:#fff; text-decoration:none; margin:5px auto 10px; display:inline-block; width:66px">Buy Now</a>
                </p>
  <?php } ?>              
              
	</div>
<?php	}}?>
    
            <div class="bsSd sbg"></div>
</div>
</td></tr></tbody></table>
	 
	
	<div class="n1 n2 z1"><span id="gright"><div class="bxr w1 w3"></div></span></div>
	
	

</div>
	</div>
	<div class="m2"></div>
   </div>
   <div style="clear: both;"><br></div>
    
    
    <?php include 'includes/footer.php';?>