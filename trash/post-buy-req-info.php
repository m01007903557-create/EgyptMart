<?php
include 'common.php';
$uid=$_SESSION['uid_indm'];

if(!isset($_SESSION['new_br_id']))
{
	header("Location:post-buy-req.php");
}

if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}
if(isset($_SESSION['br_pd_name'])){	$br_pd_name=$_SESSION['br_pd_name'];	unset($_SESSION['br_pd_name']); }else{ $br_pd_name=""; }
if(isset($_SESSION['br_requirement'])){	$br_requirement=$_SESSION['br_requirement'];	unset($_SESSION['br_requirement']); }else{ $br_requirement=""; }
if(isset($_SESSION['br_estimate_qty'])){	$br_estimate_qty=$_SESSION['br_estimate_qty'];	unset($_SESSION['br_estimate_qty']); }else{ $br_estimate_qty=""; }
if(isset($_SESSION['br_estimate_qty_unit'])){	$br_estimate_qty_unit=$_SESSION['br_estimate_qty_unit'];	unset($_SESSION['br_estimate_qty_unit']); }else{ $br_estimate_qty_unit=""; }

class addBuyReqInfo{
	
	var $msg;
	var $br_id;
	var $br_apprx_order_value;
	var $br_apprx_order_currency;
	var $br_description;
	var $br_website;
	var $br_need_quote_for;
	var $br_purchase_time;
	var $br_need_for;
	var $br_requirement_frequency;


	function __construct($br_id, $br_apprx_order_value, $br_apprx_order_currency, $br_description, $br_website, $br_need_quote_for, $br_purchase_time, $br_need_for, $br_requirement_frequency)
	{	

		$this->br_id=$br_id;
		$this->br_apprx_order_value=$br_apprx_order_value;
		$this->br_apprx_order_currency=$br_apprx_order_currency;
		$this->br_description=$br_description;
		$this->br_website=$br_website;
		$this->br_need_quote_for=$br_need_quote_for;
		$this->br_purchase_time=$br_purchase_time;
		$this->br_need_for=$br_need_for;
		$this->br_requirement_frequency=$br_requirement_frequency;
	
		
		$_SESSION['br_apprx_order_value']=$this->br_apprx_order_value;
		$_SESSION['br_apprx_order_currency']=$this->br_apprx_order_currency;
		$_SESSION['br_description']=$this->br_description;
		$_SESSION['br_website']=$this->br_website;
		$_SESSION['br_need_quote_for']=$this->br_need_quote_for;
		$_SESSION['br_purchase_time']=$this->br_purchase_time;
		$_SESSION['br_need_for']=$this->br_need_for;
		$_SESSION['br_requirement_frequency']=$this->br_requirement_frequency;
		
	}

	function valid()
	{

		$valid=true;	
		
		return $valid;
	}
	
	function add()
	{	
		$sql="update buy_requirement
			set	
				br_apprx_order_value='".$this->br_apprx_order_value."',
				br_apprx_order_currency ='".$this->br_apprx_order_currency."',
				br_description ='".$this->br_description."',
				br_website ='".$this->br_website."',
				br_need_quote_for ='".$this->br_need_quote_for."',
				br_purchase_time='".$this->br_purchase_time."',
				br_need_for ='".$this->br_need_for."',
				br_requirement_frequency ='".$this->br_requirement_frequency."' 
			where
				br_id='".$this->br_id."'";
		mysql_query($sql);
		
				
		$brf_br_id=mysql_insert_id();
				

		unset($_SESSION['br_apprx_order_value']);
		unset($_SESSION['br_apprx_order_currency']);
		unset($_SESSION['br_description']);
		unset($_SESSION['br_website']);
		unset($_SESSION['br_need_quote_for']);
		unset($_SESSION['br_need_for']);
		unset($_SESSION['br_requirement_frequency']);

		
//		$this->msg='<font color="#009900">Buy Request posted successfully.</font>';

	}	
}

if(isset($_POST['submitBuyReqDetails']))
{ 	

	$adn=new addBuyReqInfo($_POST['br_id'],addslashes(trim($_POST['br_apprx_order_value'])),addslashes(trim($_POST['br_apprx_order_currency'])),addslashes(trim($_POST['br_description'])), addslashes(trim($_POST['br_website'])),  addslashes(trim($_POST['br_need_quote_for'])), addslashes(trim($_POST['br_purchase_time'])), addslashes(trim($_POST['br_need_for'])),  addslashes(trim($_POST['br_requirement_frequency'])));	

	if($adn->valid())
	{
		$adn->add();
		header("Location:post-buy-req-info.php");
	}
	else
	{
		$_SESSION['msg']=$adn->msg;
		header("Location:post-buy-req.php");
	}
	header("Location:post-buy-req-res.php");
}
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
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/tradeleads05.css" type="text/css" rel="stylesheet">
<link href="css/eto-post-enrich.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
function validForm()
{
	var br_apprx_order_value=document.getElementById('br_apprx_order_value');
	var br_apprx_order_currency=document.getElementById('br_apprx_order_currency');
	
	var message="";
    var valid=true;
	
	if(br_apprx_order_value.value=='')
	{
		message="Kindly enter Approximate Order Value.";
		br_apprx_order_value.focus();
		valid=false;
	}
	else if(br_apprx_order_value.value!='' && isNaN(br_apprx_order_value.value))
	{
		message="Kindly enter valid Approximate Order Value.";
		br_apprx_order_value.focus();
		valid=false;
	}
	else if(br_apprx_order_value.value!='' && br_apprx_order_currency.value=='')
	{
		message="Kindly select Currency of Approximate Order Value.";
		br_apprx_order_currency.focus();
		valid=false;
	}
	if(!valid)
	{
		alert(message);
	}
	return valid;
	
}
</script>
    
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
<?php include "includes/header_new.php"; ?>
<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>
        

<?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
	<div class="f1 w61n tb lh ml br" id="lnav">
		<ul id="ulid" class="nln1" style="margin: 0px; padding: 0px;">
		<li><h3 style="font-size: 16px;font-weight: bold; color:#000; margin:0;padding: 18px 5px 18px 5px;background-color: #FFFFFF;">Buyer Tools</h3></li>

		<li class="np npnew"><a href="post-buy-req.php">»&nbsp;Post a Buy Requirement</a></li>
		<li class="np npnew"><a href="manage-buy-requirement.php">»&nbsp;Manage Buy Requirements</a></li>
		<li class="np npnew"><a href="manage-selloffer-alert.php">»&nbsp;Manage Sell Offer Alerts</a></li>
		<li style="border-bottom: medium none; margin-top: 40px;"><h2>You may also like to</h2></li>
		<li class="np npnew"><a href="buyleads.php">View Latest Buy Leads</a></li>
		<li class="np npnew"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
		<li class="np npnew"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
		</ul>
		</div>
		<!--left navigation:ends--><div class="mctr mfl">
	<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody>
		<tr>
        	<td valign="TOP" width="100%"><style type="text/css">.thanksmsg ul li{ padding-bottom: 0 0 5px 0; margin-left:16px;} .lf{text-align:left}</style><div>
				<table>
					<tbody>
                    	<tr>
							<td valign="TOP"><img src="eto-post-ins-buy.mp_files/zero.gif" height="1" width="1"></td>
							<td valign="TOP" width="100%">        
								<table class="lf" border="0" cellpadding="0" cellspacing="0" width="100%">
									<tbody>
										<tr>
					            <td style="border-right:0px;" valign="top">
            <div>        
    <table class="lf" border="0" cellpadding="0" cellspacing="0" width="100%">
              <tbody><tr>
                <td width="100%">
        <ul style="margin-bottom:0px;margin-top:0px;padding:0px;">
		<img src="eto-post-ins-buy.mp_files/zero.gif" onload="" height="1" width="1"><br>
		<div id="eto_ofr_enrichmt_inside" class="etf_box-area" style="margin-top: 1px;">
                <div id="enrichment_form_start" style="display: block;">
                <form style="margin:0" method="post" name="enrichmentForm" action="" onsubmit="return validForm();">
                <input type="hidden" id="br_id" name="br_id" value="<?php echo $_SESSION['new_br_id']; ?>" />
                 <div class="etf_hm1">Please provide more details to get Quick Response from Suppliers.</div>
                <table style="margin-top:8px" border="0" cellpadding="0" cellspacing="0" width="700px"><tbody>
                
                <tr>
					<td style="text-align:right" class="etf_tp etf_fs">Approximate order value:</td>
                    <td class="etf_tp1 etf_fs1" align="left"><span id="q_qt_help4"></span>
				<input maxlength="50" name="br_apprx_order_value" id="br_apprx_order_value" style="width: 100px;" class="etf_q_txtb" type="text" value="<?php echo $br_apprx_order_value; ?>"/>
                <select name="br_apprx_order_currency" id="br_apprx_order_currency" style="margin-left: 3px;width:170px; border: 1px solid #d7e2e9;">
                <?php
					$sql_curr="select distinct cn_currency from country where cn_status='1'";
					$res_curr=mysqli_query($con, $sql_curr);
				?>
                	<option selected="selected" value="">--Select Currency--</option>
                <?php	while($row_curr=mysqli_fetch_object($res_curr)){	?>
                    <option value="<?php echo $row_curr->cn_currency; ?>" ><?php echo $row_curr->cn_currency; ?></option> 
                <?php	}	?>
                </select>
                <!--<input maxlength="50" name="p_certification_other" class="etf_q_txtb" id="p_certification_other" style="margin-left:4px; width: 95px;visibility:hidden" type="text">-->		</td>
                </tr>
                
                <tr>
                    <td style="text-align:right" class="etf_tp etf_fs" valign="top">Describe product application/ usage:</td>
                    <td class="etf_tp1 etf_fs1" align="left"><span id="q_qt_help5"></span><textarea name="br_description" id="br_description" class="etf_q_txtb1" style="width:400px; height:50px; vertical-align:baseline;"><?php echo $br_description; ?></textarea>
                    </td>  
                </tr>
                <tr id="enrich_websit_label">
                    <td style="text-align:right" class="etf_tp etf_fs">Website:</td>
                    <td class="etf_tp1 etf_fs1" align="left"><span id="q_qt_help2"></span><input maxlength="100" name="br_website" id="br_website" style="width:272px;" class="etf_q_txtb" type="text" value="http://<?php echo $br_website; ?>"/></td>
                </tr>
                <tr>
                	<td class="etf_tp etf_fs etf_tbb" align="right" valign="top">Need quotations: </td>
                    <td class="etf_tp2 etf_fs1 etf_tbb" align="left" width="420"><span id="q_qt_help13"></span>
                <input value="To Make Purchase" id="br_need_quote_for0" class="etf_rbmp" name="br_need_quote_for" type="radio"> To Make Purchase
                <input value="To Know Price Only" id="br_need_quote_for1" class="etf_rbm" name="br_need_quote_for" type="radio">To Know Price Only
                	</td>
				</tr></tbody>
                </table>
                <table class="etf_tba" border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
                <tr>
                        <td class="etf_tp etf_fs etf_tbb" align="right" valign="top">How soon do you want to purchase: </td>
                        <td class="etf_tp2 etf_fs1 etf_tbb" align="left">
                        <span id="q_qt_help7"></span>
                                <input value="Immediate" id="q_timperiod0" class="etf_rbmp" name="br_purchase_time" type="radio"> Immediate
				<input value="Within 15 Days" id="q_timperiod1" name="br_purchase_time" class="etf_rbm" type="radio">Within 15 Days
				<input value="Within 1 Month" id="q_timperiod2" name="br_purchase_time" class="etf_rbm" type="radio">Within 1 Month</td>
                </tr>
 
		 
                <tr>
                        <td class="etf_tp etf_fs etf_tbb" align="right" valign="top">Why do you need this:</td>
                        <td class="etf_tp2 etf_fs1 etf_tbb" align="left">
                        <span id="q_qt_help8"></span>
                                <input value="For Reselling" id="br_need_for0" class="etf_rbmp" name="br_need_for" type="radio"> 
				For Reselling
				<input value="For Your End Use" id="br_need_for1" class="etf_rbm" name="br_need_for" type="radio">
				For Your End Use
				<input value="As Raw Material" id="br_need_for1" class="etf_rbm" name="br_need_for" type="radio">
				As Raw Material</td>
                </tr>
                <tr>
                        <td class="etf_tp etf_fs" align="right" valign="top">Is this your:</td>
                        <td class="etf_fs1" align="left" height="35">&nbsp;
                        <span id="q_qt_help9"></span>
                        <input value="One Time Requirement" id="br_requirement_frequency1" name="br_requirement_frequency" class="etf_rbmp" type="radio">One Time Requirement
			<input style="margin: 0 4px 0 7px;" value="Regular Requirement" id="br_requirement_frequency2" class="etf_rbm" name="br_requirement_frequency" type="radio">Regular Requirement
			</td>
                </tr>
                </tbody>
                </table>
                <span style="clear:both"></span>
                <div align="center"><input value=" Confirm your Requirement " name="submitBuyReqDetails" class="enc_sbt_new" type="submit"></div>                
                
		

                </form>
                
                </div>
                </div>
	</ul></td>
      	</tr>
    	</tbody></table>    
	</div></td></tr></tbody></table>
    	<div><br>
     	<br>
    	</div><div align="center">
     	<br>
    	</div>
	<!-- Google Code for Buy Lead Conversion Page -->
	
	<img alt="" src="imagess/a.gif" border="0" height="1" width="1">
	</td></tr></tbody></table><br><br></div></td></tr></tbody></table><br></div>
		<div class="c3">&nbsp;</div></div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>
		