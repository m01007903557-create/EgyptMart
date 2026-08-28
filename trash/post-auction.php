<?php
include "common.php";

$_SESSION['last_page']="post-tender.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");
}
$uid=$_SESSION['uid_indm'];

/*
if(isset($_SESSION['pc_id'])){	$pc_id=$_SESSION['pc_id'];	unset($_SESSION['pc_id']); }else{ $pc_id=""; }
if(isset($_SESSION['tnd_pc_id'])){	$tnd_pc_id=$_SESSION['tnd_pc_id'];	unset($_SESSION['tnd_pc_id']); }else{ $tnd_pc_id=""; }
if(isset($_SESSION['tnd_heading'])){	$tnd_heading=$_SESSION['tnd_heading'];	unset($_SESSION['tnd_heading']); }else{ $tnd_heading=""; }
if(isset($_SESSION['tnd_value'])){	$tnd_value=$_SESSION['tnd_value'];	unset($_SESSION['tnd_value']); }else{ $tnd_value=""; }
if(isset($_SESSION['tnd_currency'])){	$tnd_currency=$_SESSION['tnd_currency'];	unset($_SESSION['tnd_currency']); }else{ $tnd_currency=""; }
if(isset($_SESSION['tnd_prequalification_criteria'])){	$tnd_prequalification_criteria=$_SESSION['tnd_prequalification_criteria'];	unset($_SESSION['tnd_prequalification_criteria']); }else{ $tnd_prequalification_criteria=""; }
if(isset($_SESSION['tnd_details'])){	$tnd_details=$_SESSION['tnd_details'];	unset($_SESSION['tnd_details']); }else{ $tnd_details=""; }
if(isset($_SESSION['so_validity'])){	$so_validity=$_SESSION['so_validity'];	unset($_SESSION['so_validity']); }else{ $so_validity=""; }

class addTender
{
	var $msg;
	var $tnd_usr_id;
	var $main_cat;
	var $pc_id;
	var $tnd_pc_id;
	var $tnd_heading;
	var $tnd_value;
	var $tnd_currency;
	var $tnd_prequalification_criteria;
	var $tnd_details;
	var $tnd_preferred_location;
	var $so_validity;
		
	function __construct($tnd_usr_id, $main_cat, $pc_id, $tnd_pc_id, $tnd_heading, $tnd_value, $tnd_currency, $tnd_prequalification_criteria, $tnd_details, $tnd_preferred_location, $so_validity)
	{	
		$this->tnd_usr_id=$tnd_usr_id;
		$this->main_cat=$main_cat;
		$this->pc_id=$pc_id;
		$this->tnd_pc_id=$tnd_pc_id;
		$this->tnd_heading=$tnd_heading;
		$this->tnd_value=$tnd_value;
		$this->tnd_currency=$tnd_currency;
		$this->tnd_prequalification_criteria=$tnd_prequalification_criteria;
		$this->tnd_details=$tnd_details;
		$this->tnd_preferred_location=$tnd_preferred_location;
		
		$this->so_validity=$so_validity;

		$_SESSION['main_cat']=$this->main_cat;
		$_SESSION['pc_id']=$this->pc_id;
		$_SESSION['tnd_pc_id']=$this->tnd_pc_id;
		$_SESSION['tnd_heading']=$this->tnd_heading;
		$_SESSION['tnd_value']=$this->tnd_value;
		$_SESSION['tnd_currency']=$this->tnd_currency;
		$_SESSION['tnd_prequalification_criteria']=$this->tnd_prequalification_criteria;
		$_SESSION['tnd_details']=$this->tnd_details;
		$_SESSION['tnd_preferred_location']=$this->tnd_preferred_location;
		$_SESSION['so_validity']=$this->so_validity;		
	}
	function checkBadWord($param)
	{
		$valid=true;
		$sqlrpl = "select bd_word from bad_word";
		$resrpl = mysql_query($sqlrpl);
		while($rowrpl = mysql_fetch_object($resrpl))
		{		
			$letters[] = strtoupper($rowrpl->bd_word);
		}
		foreach($letters as $val)
		{
			$pos = strpos($param, $val);
			if ($pos !== false)
			{
				$valid=false;
			} 
		}
		
		return $valid;
	}
	function valid()
	{
		//include "language.php";
		$valid=true;
		if($this->main_cat=="")
		{
			$this->msg='<font color="#FF0000">Kindly select Main Category.</font>';
			$valid=false;
		}
		else if($this->pc_id=="")
		{
			$this->msg='<font color="#FF0000">Kindly select Category.</font>';
			$valid=false;
		}
		else if($this->tnd_pc_id=="")
		{
			$this->msg='<font color="#FF0000">Kindly select Sub-Category.</font>';
			$valid=false;
		}
		else if($this->tnd_heading=="")
		{
			$this->msg='<font color="#FF0000">Kindly enter Tender Heading.</font>';
			$valid=false;
		}
		else if($this->tnd_heading!="" && $this->checkBadWord(strtoupper($this->tnd_heading))==false)
		{
			$this->msg= "<font color='#FF0000'>You can't post this Tender Heading. It contains some Bad words.</font>";
			$valid=false;
		}
		else if($this->tnd_value=="")
		{
			$this->msg='<font color="#FF0000">Kindly enter Tender value.</font>';
			$valid=false;
		}
		else if($this->tnd_prequalification_criteria == "")
		{
			$this->msg= '<font color="#FF0000">Kindly describe Pre-qualification Criteria.</font>';
			$valid=false;
		}
		else if($this->tnd_prequalification_criteria!="" && $this->checkBadWord(strtoupper($this->tnd_prequalification_criteria))==false)
		{
			$this->msg= "<font color='#FF0000'>You can't post this Pre-qualification Criteria. It contains some Bad words.</font>";
			$valid=false;
		}
		else if($this->tnd_details == "")
		{
			$this->msg= '<font color="#FF0000">Kindly describe Tender details.</font>';
			$valid=false;
		}
		
		return $valid;
	}
	
	function add()
	{	
		if($_FILES["so_pic"]["name"] != "")		
		{
			if ($_FILES["so_pic"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["so_pic"]["error"] . "<br />";
			}
			else
			{
				$this->so_pic='so-'.rand(0,9999).trim(addslashes($_FILES['so_pic']['name']));	
			
				$ds = move_uploaded_file($_FILES["so_pic"]["tmp_name"], "upload/sale_offer/".$this->so_pic) or die('error');	
							
				$sql="insert into tender
				set
					tnd_usr_id='".$this->tnd_usr_id."',
					tnd_pc_id='".$this->tnd_pc_id."',
					tnd_heading ='".$this->tnd_heading."',
					tnd_value ='".$this->tnd_value."',
					tnd_currency ='".$this->tnd_currency."',
					tnd_prequalification_criteria ='".$this->tnd_prequalification_criteria."',
					tnd_details ='".$this->tnd_details."',
					tnd_preferred_location ='".$this->tnd_preferred_location."',
					so_validity ='".$this->so_validity."',
					so_pic ='".$this->so_pic."',
					so_approval_status='0',
					so_posting_date=now(),
					so_updated_date=now()";
			
				mysql_query($sql) or die(mysql_error());
			
				unset($_SESSION['main_cat']);
				unset($_SESSION['pc_id']);
				unset($_SESSION['tnd_pc_id']);
				unset($_SESSION['tnd_heading']);
				unset($_SESSION['tnd_value']);
				unset($_SESSION['tnd_currency']);
				unset($_SESSION['tnd_prequalification_criteria']);
				unset($_SESSION['tnd_preferred_location']);
				unset($_SESSION['so_validity']);
			
				$this->msg='<font color="#009900">Tender posted successfully.</font>';
			}
		}
		else
		{
						
			$sql="insert into tender
				set
					tnd_usr_id='".$this->tnd_usr_id."',
					tnd_pc_id='".$this->tnd_pc_id."',
					tnd_heading ='".$this->tnd_heading."',
					tnd_value ='".$this->tnd_value."',
					tnd_currency ='".$this->tnd_currency."',
					tnd_prequalification_criteria ='".$this->tnd_prequalification_criteria."',
					tnd_details ='".$this->tnd_details."',
					tnd_preferred_location ='".$this->tnd_preferred_location."',
					tnd_updated_date=now()";
					
			mysql_query($sql);
		
			unset($_SESSION['main_cat']);
			unset($_SESSION['pc_id']);
			unset($_SESSION['tnd_pc_id']);
			unset($_SESSION['tnd_heading']);
			unset($_SESSION['tnd_value']);
			unset($_SESSION['tnd_currency']);
			unset($_SESSION['tnd_prequalification_criteria']);
			unset($_SESSION['tnd_details']);
			unset($_SESSION['tnd_preferred_location']);
			unset($_SESSION['so_validity']);
			
			$this->msg='<font color="#009900">Tender posted successfully.</font>';
		}
	}	
}
if(isset($_SESSION['msg'])){	$msg=$_SESSION['msg'];	unset($_SESSION['msg']);	}else{	$msg="";	}
if(isset($_POST['submitTender']))
{
	$adn=new addTender(addslashes(trim($_POST['tnd_usr_id'])), addslashes(trim($_POST['main_cat'])), addslashes(trim($_POST['pc_id'])), addslashes(trim($_POST['tnd_pc_id'])),  addslashes(trim($_POST['tnd_heading'])),addslashes(trim($_POST['tnd_value'])),addslashes(trim($_POST['tnd_currency'])),addslashes(trim($_POST['tnd_prequalification_criteria'])),addslashes(trim($_POST['tnd_details'])), addslashes(trim($_POST['tnd_preferred_location'])),addslashes(trim($_POST['so_validity'])));	

	if($adn->valid())
	{	
		$adn->add();
		header("Location:post-tender-res.php");
	}
	else
	{
		$_SESSION['msg']=$adn->msg;
		header("Location:post-tender.php");
	}
}*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- meta start -->
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/eto-post-sell.css" type="text/css" rel="STYLESHEET">
<link href="css/my-v1.css" type="text/css" rel="stylesheet">
<link href="css/c.css" type="text/css" rel="STYLESHEET">
<link href="css/jquery.css" type="text/css" rel="stylesheet">
<link href="css/ui.css" rel="stylesheet">

<link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
<link href="css/pbl-my01.css" type="text/css" rel="stylesheet">
<link href="css/dir-new.css" type="text/css" rel="stylesheet">

<script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){

});
function showCategory()
{
	var pc_id=document.getElementById('main_cat').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showSubcat();	}); 
}
function showSubcat()
{
	var id=document.getElementById('pc_id').value;
	$.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#tnd_pc_id').html(data);	}); 
}
function validTender()
{
	var main_cat=document.getElementById('main_cat');
	var typeofselection=document.getElementById('typeofselection');
	var keywordsFilter1=document.getElementById('keywordsFilter1');
	var pc_id=document.getElementById('pc_id');
	var tnd_pc_id=document.getElementById('tnd_pc_id');
	var tnd_heading=document.getElementById('tnd_heading');
	var tnd_value=document.getElementById('tnd_value');
	var tnd_currency=document.getElementById('tnd_currency');
	
	var tnd_notice_type=document.getElementById('tnd_notice_type');
	var tnd_qty=document.getElementById('tnd_qty');
	var tnd_qty_mu_id=document.getElementById('tnd_qty_mu_id');
	var tnd_emd=document.getElementById('tnd_emd');
	var tnd_document_fees=document.getElementById('tnd_document_fees');
	var tnd_document_fees_currency=document.getElementById('tnd_document_fees_currency');
	var tnd_project_period=document.getElementById('tnd_project_period');
	var tnd_products=document.getElementById('tnd_products');
	
	var tnd_publish_date=document.getElementById('tnd_publish_date');
	var tnd_docSaleStart_date=document.getElementById('tnd_docSaleStart_date');
	var tnd_docSaleEnd_date=document.getElementById('tnd_docSaleEnd_date');
	var tnd_docSubmitBefore_date=document.getElementById('tnd_docSubmitBefore_date');
	var tnd_due_date=document.getElementById('tnd_due_date');	
	
	
	var tnd_prequalification_criteria=document.getElementById('tnd_prequalification_criteria');
	var tnd_details=document.getElementById('tnd_details');
	
	var tnd_preferred_location=$('input:radio[name=tnd_preferred_location]:checked').val();

	var fld_ids=$("#additional_field_ids").val();
	var fld_ids=fld_ids.split(",");
	
	var fld_types=$("#additional_field_types").val();
	var fld_types=fld_types.split(",");

	var af_id="";
	var afv_val="";
	var j=-1;
	
	for(var i=0;i<fld_ids.length;i++)
	{
		if(fld_types[i]=="checkbox" && 	$('input[name="chk-'+fld_ids[i]+'[]"]:checked').length > 0)
		{

			var c=0;
			var chkval="";
			$('input:checkbox[name="chk-'+fld_ids[i]+'[]"]').each(function() 
			{
				if($(this).is(':checked'))
				{
					if(c>0){	chkval=chkval+"-";	}
					chkval=chkval+$(this).val();
					c++;
				}
				
			});
			
			j++;
			
			if(j>0)
			{
				af_id=af_id+"|";
				afv_val=afv_val+"|";
			}
			af_id=af_id+fld_ids[i];
			afv_val=afv_val+chkval;
		}
		else if(fld_types[i]=="radio" && 	$('input[name="radio-'+fld_ids[i]+'"]:checked').length > 0)
		{

			var c=0;
			var chkval="";

			$('input:radio[name="radio-'+fld_ids[i]+'"]').each(function() 
			{
				if($(this).is(':checked'))
				{
					if(c>0){	chkval=chkval+"-";	}
					chkval=chkval+$(this).val();
					c++;
				}
				
			});
			
			j++;
			
			if(j>0)
			{
				af_id=af_id+"|";
				afv_val=afv_val+"|";
			}
			af_id=af_id+fld_ids[i];
			afv_val=afv_val+chkval;
		}
		else if(fld_types[i]=="select" && $("#"+fld_ids[i]).val()!='')
		{
			j++;
			
			if(j>0)
			{
				af_id=af_id+"|";
				afv_val=afv_val+"|";
			}
			af_id=af_id+fld_ids[i];
			afv_val=afv_val+$("#"+fld_ids[i]).val();
		}
		else if((fld_types[i]=="text" && $("#"+fld_ids[i]).val()!='') || (fld_types[i]=="textarea"  && $("#"+fld_ids[i]).val()!=''))
		{
			j++;
			
			if(j>0)
			{
				af_id=af_id+"|";
				afv_val=afv_val+"|";
			}
			af_id=af_id+fld_ids[i];
			afv_val=afv_val+$("#"+fld_ids[i]).val();
		}
		
	}


	var message="";
    var valid=true;
  	
    var typeofselectionvalue = typeofselection.value *1;
	//if(typeofselectionvalue=='0'){
	if(typeofselectionvalue=='0' && (main_cat.value==null || main_cat.value==''))
	{
		message="Kindly select Main Category.";
		main_cat.focus();
		valid=false;
	}
	else if(typeofselectionvalue=='0' && pc_id.value=='')
	{
		message="Kindly select Category.";
		pc_id.focus();
		valid=false;
	}
	else if(typeofselectionvalue=='0' && tnd_pc_id.value=='')
	{
		message="Kindly select Sub-Category.";
		tnd_pc_id.focus();
		valid=false;
	}
		
   // }
     else if(typeofselectionvalue==1 && keywordsFilter1.value=='')    {

		message="Kindly enter valid Search for category";
		keywordsFilter1.focus();
		valid=false;

    }

  else if(tnd_heading.value=='')
	{
		message="Kindly enter Tender Heading.";
		tnd_heading.focus();
		valid=false;
	}
	else if(tnd_value.value!='' && isNaN(tnd_value.value))
	{
		message="Kindly enter valid Tender value.";
		tnd_value.focus();
		valid=false;
	}
	else if(tnd_value.value!='' && tnd_currency.value=='')
	{
		message="Kindly select currency for Tender Value.";
		tnd_currency.focus();
		valid=false;
	}
	else if(tnd_notice_type.value=='')
	{
		message="Kindly enter Notice Type.";
		tnd_notice_type.focus();
		valid=false;
	}
	else if(tnd_qty.value!='' && isNaN(tnd_qty.value))
	{
		message="Kindly enter valid Quantity.";
		tnd_qty.focus();
		valid=false;
	}
	else if(tnd_qty.value!='' && tnd_qty_mu_id.value=='')
	{
		message="Kindly select Quantity Unit.";
		tnd_qty_mu_id.focus();
		valid=false;
	}
	else if(tnd_document_fees.value=='' || tnd_document_fees.value=='0')
	{
		message="Kindly enter Document Fees.";
		tnd_document_fees.focus();
		valid=false;
	}
	else if(tnd_document_fees.value!='' && isNaN(tnd_document_fees.value))
	{
		message="Kindly enter valid Document Fees.";
		tnd_document_fees.focus();
		valid=false;
	}
	else if(tnd_document_fees_currency.value=='')
	{
		message="Kindly select currency for Document Fees.";
		tnd_document_fees_currency.focus();
		valid=false;
	}	
	else if(tnd_prequalification_criteria.value == '')
	{
		message="Kindly describe Pre-qualification Criteria.";
		tnd_prequalification_criteria.focus();
		valid=false;
	}
	else if(tnd_details.value == '')
	{
		message="Kindly describe Tender details.";
		tnd_details.focus();
		valid=false;
	}
   	if(valid)
	{
		
		$.get("addNewTender.php", {keywordsFilter:keywordsFilter1.value,typeofselection:typeofselection.value,tnd_usr_id:tnd_usr_id.value,main_cat:main_cat.value,pc_id:pc_id.value,tnd_pc_id:tnd_pc_id.value, tnd_heading:tnd_heading.value, tnd_value:tnd_value.value, tnd_notice_type:tnd_notice_type.value,tnd_qty:tnd_qty.value,tnd_qty_mu_id:tnd_qty_mu_id.value,tnd_emd:tnd_emd.value, tnd_document_fees:tnd_document_fees.value, tnd_document_fees_currency:tnd_document_fees_currency.value, tnd_project_period:tnd_project_period.value, tnd_products:tnd_products.value, tnd_publish_date:tnd_publish_date.value, tnd_docSaleStart_date:tnd_docSaleStart_date.value,tnd_docSaleEnd_date:tnd_docSaleEnd_date.value,tnd_docSubmitBefore_date:tnd_docSubmitBefore_date.value, tnd_due_date:tnd_due_date.value, tnd_currency:tnd_currency.value, tnd_prequalification_criteria:tnd_prequalification_criteria.value, tnd_details:tnd_details.value, tnd_preferred_location:tnd_preferred_location,af_id:af_id,afv_val:afv_val},	function(data){
            // console.log(data);
			data=data.trim();
			dt=data.split("|");
			if(dt[0]=='0')
			{
				alert(dt[1]);
			}
			else
			{
				alert(dt[1]);
			   window.location="post-tender.php";
			}																  
		});	
	}
	if(!valid)
	{
		alert(message);
		/*document.getElementById('error_msg').style.display="block";
		document.getElementById('error_msg').style.color = "red";
		document.getElementById('error_msg').innerHTML = message;*/
	}
	return valid;
}

function showTenderAdditionalFields()
{
	id=document.getElementById('tnd_pc_id').value;
	//id=$('input[name=scat_id]:checked').val()
	$.post("showTenderAdditionalFields.php", {id:id},	function(data){
		data=data.trim();
		dt=data.split("|");
		 $('#additional').html(dt[0]);
		 $("#additional_field_ids").val(dt[1]);
		 $("#additional_field_types").val(dt[2]);

	});
}
</script>
<style>
#login_frm1
{
	border:1px solid #6F0000;color:#fff;text-decoration:none;font-size:14px; font-weight:bold; padding:5px;text-align:center;-webkit-border-radius:5px;-moz-border-radius:5px;border-radius:5px;background-color:#DF0000;filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');background:-webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));background:-moz-linear-gradient(top,  #DF0000,  #DF0000);cursor:pointer;font-family:Arial, Helvetica, sans-serif
}
</style>
<script type="text/javascript" src="datepicker/date.js"></script>
<script type="text/javascript" src="datepicker/jquery.datePicker.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="datepicker/datePicker.css">
<link rel="stylesheet" type="text/css" media="screen" href="datepicker/demo.css">
<script type="text/javascript" charset="utf-8">
$(function()
{
	$('#tnd_publish_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_docSaleStart_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_docSaleEnd_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_docSubmitBefore_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_due_date').datePicker().val(new Date().asString()).trigger('change');
});
</script>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        <br><br>
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>


	<?php include "includes/header_menu.php"; ?>
	
	<!--left navigation:start-->
	<div class="f1 w61n tb lh ml m2" id="lnav" style="display: block;">
		<ul class="nln1" style="margin: 0px; padding: 0px;">
			<li>
			<h2>Trade Offers</h2>
			</li>
			<li style="border-bottom: medium none;">
			<h3>Buy Requirement</h3>
			<!--<ul>-->
			</li><li class="lp"><a href="post-buy-req.php">»&nbsp;Post a New Buy Requirement</a></li>
			<li class="lp"><a href="manage-buy-requirement.php">»&nbsp;Manage Buy Requirements</a></li>
			<!--</ul>-->
			
			<li style="border-bottom: medium none;">
			<h3>Sell Offers</h3>
			<!--<ul>-->
			</li><li class="lp"><a href="post-sell-offer.php">»&nbsp;Post a New Sell Offer</a></li>
			<li class="lp"><a href="manage-sell-offer.php">»&nbsp;Manage Sell Offers</a></li>
			<!--</ul>-->
			
			<li style="border-bottom: medium none;">
			<h3>Tenders</h3>
			<!--<ul>-->
			</li><li class="lp"><a href="post-tender.php">»&nbsp;Post a Tender</a></li>
			<li class="lp"><a href="manage-tenders.php">»&nbsp;Manage Tenders</a></li>
			<!--</ul>-->
			
			<li style="border-bottom: medium none; margin-top: 40px;">
			<h2>You may also like to</h2>
			</li>
			<li class="np"><a href="buyleads.php">View Latest Buy Leads</a></li>
		    <li class="np"><a href="sale-offers.php">View Latest Sell Offers</a></li>
            <li class="np"><a href="tenders.php">View Latest Tenders</a></li>
			<li class="np"><a href="manage-purchased-buyleads.php">View Purchased Buy Leads</a></li>
			<li class="np"><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
		</ul>
	</div>
	<!--left navigation:ends-->
	<div class="w57 b1_m2 f1 wd797" id="ldiv">
		<div style="display: none;" id="hdbord" class=""></div>
		<table id="topstrip" style="text-align: left; display:none" width="100%" border="0" cellpadding="0" cellspacing="0">
		<tbody><tr>
			<td class="sprite l_strip fl">
			</td>
			<td class="sprite cntr_strip fl">
				<table style="text-align: left;" width="100%" border="0" cellpadding="0" cellspacing="0">
				<tbody><tr>
					<td class="sprite icon1">
					</td>
					<td class="vm" style="padding: 6px 0pt; color:#5d5858; line-height:13px" width="234" align="left">
						<font style="color:#0056c0; font-size:15px; line-height:15px; font-weight:bold;">Tell Us About Your Product</font><br>
						 Complete this form and let the buyers<br> know about your product / services.
					</td>
					<td class="sprite arrow" align="left">
					</td>
					<td class="sprite icon2">
					</td>
					<td class="vm" style="padding: 6px 0pt; color:#5d5858; line-height:13px" width="229">
						<font style="color:#0056c0; font-size:15px; font-weight:bold; line-height:15px;">Receive Enquiries</font><br>
						 Receive business enquiries from global<br> buyers via email or phone.
					</td>
					<td class="sprite arrow" align="left">
					</td>
					<td class="sprite icon3">
					</td>
					<td class="vm" style="padding: 6px 0pt; color:#5d5858; line-height:13px" width="221">
						<font style="color:#0056c0; font-size:15px; font-weight:bold; line-height:15px;">Increase Revenue</font><br>
						Get More business easily and quickly<br> with increased revenue.
					</td>
				</tr>
				</tbody></table>
			</td>
			<td class="sprite r_strip" align="right">
			</td>
		</tr>
		</tbody></table>

        <script type="text/javascript">
function searchcat()
{
	$("#scs").removeClass("tabclose").addClass("tabopen");
	$("#bcs").removeClass("tabopen").addClass("tabclose");
        $('#typeofselection').val(1);
	$(".bcc").css("display","none");
	$(".scc").removeAttr('style');
}
function beowswcat()
{
	$("#bcs").removeClass("tabclose").addClass("tabopen");
	$("#scs").removeClass("tabopen").addClass("tabclose");
    $('#typeofselection').val(0);
	$(".scc").css("display","none");
	$(".bcc").removeAttr('style');;
}

</script>
         <input type="hidden" value="0"  id="typeofselection" />
		<div id="div2" style="display:block;">
			<div>
				<img src="images/zero.gif" width="1" height="19">
			</div>
			<table width="100%" align="center">
			<tbody><tr>
				<td>
					<div align="left">
						<div class="tw2l fl" id="formmain" style="margin-left:8px;background-color:#FAF4FF">
							<div class="" id="lgn1">
								<p class="c-1 g2 fs bo1">Post Tender FREE and Get Verified Bidders<span class="p6 q4 tm1 cbc fsz1"><i class="co">*</i>
									Required Information</span>
								</p>
								<p class="ts1 ptp">
								</p>
							</div>
							<div>
								<form method="post" name="postForm1" action="" onsubmit="return validTender();" enctype="multipart/form-data">

									
									<div id="error_msg" style="">
                                    <?php echo $msg; ?>
									</div>

                                    <input type="hidden" id="tnd_usr_id" name="tnd_usr_id" value="<?php echo $_SESSION['uid_indm']; ?>"/>
                                       <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="99%"><tbody>
 <tr><!--<td valign="TOP" width="19"><img src="images/zero.gif" height="6" width="1"><br><img src="images/11.gif" height="15" width="19"></td>--><td><table border="0" cellpadding="0" cellspacing="0" width="100%"><tbody>
 <tr>
 <td class="tabclose" onclick="searchcat()" id="scs" width="152">Search Categories</td>
 <td class="tabborder" width="10"><img src="images/zero.gif" height="1" width="10"></td>
 <td class="tabopen" onclick="beowswcat()" id="bcs" width="155">Browse Categories</td>
 <td class="tabborder"><img src="images/zero.gif" height="1" width="1"></td>
 </tr>
 </tbody></table></td></tr></tbody></table>
									<table class="frm mt5" width="100%">
									<tbody>
                                      <tr class="scc" id="r0" style="display: none;">
                                      	<td valign="middle" width="30%">
											<p class="pd15">
												 <b style="font-size:13px;"><font color="#E95801">Enter product keywords to find a category</font></b>
											</p>
										</td>
										<td valign="TOP">



  <input role="textbox" class="txt ui-placeholder-input ui-autocomplete-input" name="keywordsFilter1" id="keywordsFilter1" style="width: 450px;float: left; height:30px; border: 1px solid #ff8a8a;" type="text" maxlength="60" size="33" >
  </td>
  </tr>

       <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css" />
<script type="text/javascript" src="js/jquery.autocomplete.js"></script>


<script type="text/javascript">
$(document).ready(function($113){
	lostFocus();
	$113('#keywordsFilter1').unbind().live('keyup',function() {
		var type11='Products';
		$113("#keywordsFilter1").autocomplete("autocomplete.php", {
			selectFirst: true,
			extraParams: {type:type11},
			width: 407
		})
		.result(function(event, data, formatted) {
 			$("input#keywordsFilter1").val(data);
		});
	});
});
</script>

                                    <tr id="r0" style="height: 48px;" class="bcc">
										<td valign="middle" width="30%">
											<p class="pd15">
												<i>*</i><b>Main Category:</b>
											</p>
										</td>
										<td valign="TOP">

											<select class="bd4 hw6 mr3 htb" id="main_cat" name="main_cat" style="height:30px;" onchange="showCategory()">
                                            <option value="">--Select Category--</option>
                                            <?php
												$sql_pc="select * from product_category_arabyos where pc_parent_id='0' and pc_status='1'";
												$res_pc=mysql_query($sql_pc);
												while($row_pc=mysql_fetch_object($res_pc)){
											?>
					                       	<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
                    					    <?php	}	?>
                                            </select>
										</td>
									</tr>
                                    <tr id="r1" style="height: 48px;" class="bcc">
										<td valign="middle" width="30%">
											<p class="pd15">
												<i>*</i><b>Category:</b>
											</p>
										</td>
										<td valign="TOP">
											
											<select class="bd4 hw6 mr3 htb" id="pc_id" name="pc_id" style="height:30px;" onchange="showSubcat()">
                                            <option value="">--Select Category--</option>
                                            <?php
												$sql_pc="select * from product_category_arabyos where pc_parent_id!='0' and pc_parent_id='".$main_cat."' and pc_status='1'";
												$res_pc=mysql_query($sql_pc);
												while($row_pc=mysql_fetch_object($res_pc)){
											?>
					                       	<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
                    					    <?php	}	?>
                                            </select>
                                            <select class="bd4 hw6 mr3 htb" id="tnd_pc_id" name="tnd_pc_id" style="height:30px;" onchange="showTenderAdditionalFields();">
                                            <option value="">--Select Sub-Category--</option>
                                            <?php
												$sql_spc="select * from product_category_arabyos where pc_parent_id='".$pc_id."' and pc_status='1' and pc_parent_id!='0'";
												$res_spc=mysql_query($sql_spc);
												while($row_spc=mysql_fetch_object($res_spc)){
											?>
											<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$tnd_pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
                                            <?php	}	?>
                                            </select>
										</td>
									</tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Tender Heading:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
											<input name="tnd_heading" id="tnd_heading" style="width:450px;" class="bd4 hw6 mr3 htb" maxlength="90" value="<?php echo $tnd_heading; ?>"/>
											<div class="displayoff" id="hlp" style="line-height:14px;height:14px;"></div>
										</td>
									</tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<b>Tender Value:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
											<input name="tnd_value" id="tnd_value" style="width:280px;" class="bd4 hw6 mr3 htb" maxlength="90" value="<?php echo $tnd_value; ?>"/>
                                            <select size="1" name="tnd_currency" id="tnd_currency" class="a_f s_u">
				<option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysql_query("select * from country where cn_status='1'");
				while($currencyrow=mysql_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if(user_info($uid,'country')== $currencyrow->cn_id){ ?> selected="selected" <?php } else if($tnd_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
											<div class="displayoff" id="hlp" style="line-height:14px;height:14px;"></div>
										</td>
									</tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Notice Type:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_notice_type" id="tnd_notice_type" class="bd4 hw6 mr3 htb" style="width:200px;height:18px;" />
                                        
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<b>Quantity:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_qty" id="tnd_qty" class="bd4 hw6 mr3 htb" style="width:75px;height:18px;" />
                                        <select size="1" name="tnd_qty_mu_id" id="tnd_qty_mu_id" class="a_f s_u">
				<option value="">-Select Unit-</option>
                <?php                
				$res_mu=mysql_query("select * from measurement_unit where mu_status='1'");
				while($row_mu=mysql_fetch_object($res_mu)){
				?>
                <option value="<?php echo $row_mu->mu_id; ?>" ><?php echo $row_mu->mu_name;?></option>
	            
				<?php } ?>
            </select>
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<b>EMD:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_emd" id="tnd_emd" class="bd4 hw6 mr3 htb" style="width:200px;height:18px;" />
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Document Fees:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_document_fees" id="tnd_document_fees" class="bd4 hw6 mr3 htb" style="width:200px;height:18px;" />
                                        <select size="1" name="tnd_document_fees_currency" id="tnd_document_fees_currency" class="a_f s_u">
											<option value="">-Select Currency-</option>
							                <?php                
												$df_currencysql=mysql_query("select * from country where cn_status='1'");
												while($df_currencyrow=mysql_fetch_object($df_currencysql)){
											?>
								            <option value="<?php echo $df_currencyrow->cn_id;?>" <?php if(user_info($uid,'country')== $df_currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $df_currencyrow->cn_currency;	?></option>
											<?php } ?>
							            </select>
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<b>Project Period:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_project_period" id="tnd_project_period" class="bd4 hw6 mr3 htb" style="width:200px;height:18px;" />
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<b>Products / Services:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_products" id="tnd_products" class="bd4 hw6 mr3 htb" style="width:375px;height:18px;" />
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Tender Publish Date:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_publish_date" id="tnd_publish_date" class="date-pick dp-applied bd4 hw6 mr3 htb" style="width:75px;height:18px;" readonly="readonly"/><a  title="Choose date"></a><!--class="dp-choose-date"-->
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Document Sale Starts:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_docSaleStart_date" id="tnd_docSaleStart_date" class="date-pick dp-applied bd4 hw6 mr3 htb" style="width:75px;height:18px;" readonly="readonly"/><a  title="Choose date"></a><!--class="dp-choose-date"-->
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Document Sale Ends:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_docSaleEnd_date" id="tnd_docSaleEnd_date" class="date-pick dp-applied bd4 hw6 mr3 htb" style="width:75px;height:18px;" readonly="readonly"/><a  title="Choose date"></a><!--class="dp-choose-date"-->
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Document Submit Before:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_docSubmitBefore_date" id="tnd_docSubmitBefore_date" class="date-pick dp-applied bd4 hw6 mr3 htb" style="width:75px;height:18px;" readonly="readonly"/><a  title="Choose date"></a><!--class="dp-choose-date"-->
                                        </td>
                                    </tr>
                                    <tr id="r2" style="height: 48px;">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Tender Due Date:</b>
											</p>
											<img src="images/zero.gif" width="190" height="1">
										</td>
										<td valign="TOP">
                                        <input name="tnd_due_date" id="tnd_due_date" class="date-pick dp-applied bd4 hw6 mr3 htb" style="width:75px;height:18px;" readonly="readonly"/><a title="Choose date"></a><!--class="dp-choose-date"-->
                                        </td>
                                    </tr>
									<tr id="r3">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Pre-qualification Criteria: </b>
                                                <br />
												<b class="q4"></b><font class="co1" id="Charcount" color="#ff8000">2000</font><b class="fwn cbc">Characters Remaining </b>
											</p>
										</td>
										<td onmouseover="document.getElementById('tt2').style.display='block';" onmouseout="document.getElementById('tt2').style.display='none';" valign="TOP">
											<div id="lgn6" style="width: 360px; height: 105px;">
												<textarea aria-hidden="true" name="tnd_prequalification_criteria" id="tnd_prequalification_criteria" style="max-width: 4500px;width:450px; height:95px; max-height:95px; display: block;" rows="5" cols="30"></textarea>
											</div>

											
										</td>
									</tr>
                                    <tr id="r3">
										<td valign="TOP" width="30%">
											<p class="pd15">
												<i>*</i><b>Details: </b>
											</p>
										</td>
										<td onmouseover="document.getElementById('tt2').style.display='block';" onmouseout="document.getElementById('tt2').style.display='none';" valign="TOP">
											<div id="lgn6" style="width: 360px; height: 105px;">
												<textarea aria-hidden="true" name="tnd_details" id="tnd_details" style="max-width: 4500px;width:450px; height:95px; max-height:95px; display: block;" rows="5" cols="30"></textarea>
											</div>

											
										</td>
									</tr>
							<tr id="r4">
								<td valign="TOP" width="30%">
									<p class="pd15"><b>Location Preferences: </b></p>
								</td>
								<td valign="TOP">
									<div style="vertical-align:bottom">
                       					<input type="radio" id="tnd_preferred_location_1" name="tnd_preferred_location" value="abroad" /><label style="top:0px;">Abroad Only</label>		
				                        &nbsp;&nbsp;
				                        <input type="radio" id="tnd_preferred_location_2" name="tnd_preferred_location" value="any" checked="checked"/><label style="top:0px;">Abroad + Domestic</label>
				                        &nbsp;&nbsp;
	                			        <input type="radio" id="tnd_preferred_location_3" name="tnd_preferred_location" value="domestic"/><label style="top:0px;">Domestic Only</label>
    				                    &nbsp;&nbsp;
                    				    <input type="radio" id="tnd_preferred_location_4" name="tnd_preferred_location" value="my_city"/><label style="top:0px;">My City Only</label>
                    					    </div>
	
										</td>
									</tr>
                                    <tr><td>&nbsp;</td><td>&nbsp;</td></tr>
                                    
									</tbody></table>
                                    <table id="additional" class="frm mt5" width="100%">
                                    </table>
                                    <input type="hidden" id="additional_field_ids" >
									<input type="hidden" id="additional_field_types" >
                                    
									
																		
							<div class="a2 pt pb" id="loginsubmit" style="display: block;"><input name="frmsubmitbutton" value="login" type="hidden"><input name="submitTender" id="login_frm1" class="cr bo1 fsz1" style="height: 32px; width: 170px;" value="Submit your Offer" type="button" onclick="validTender();"></div>
						</form>
					</div>
				</div>
			</div>
			
		</td>
	</tr>
	</tbody></table>
</div>
<div class="p_rl" id="slempform" style="display:none;font-family: arial; font-weight:bold; padding: 30px 0px 0px 0px; text-align: center;color: #FF6000;font-size: 16px;height:200px"><nobr>You do not have privilege to access this section</nobr></div>

<div>
	<br>
	<br>
	<br>
</div>







<!-- MY TD ENDS -->
</div>
<!--footer:start-->
<div style="clear:both;">
<br>
<br>
&nbsp;&nbsp;
</div>
</div>
<!--footer:start--> 
<?php include 'includes/footer.php';?>