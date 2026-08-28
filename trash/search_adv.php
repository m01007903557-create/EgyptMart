<?php
include 'common.php';

$_SESSION['last_page']="conatct_us.php";
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">

<link href="css/main-mp-v1.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<style>
.fl{float:left}
.tr,.bx1{text-align:right}
.wx1-d{width:77%;padding:10px;margin-right:10px}
INPUT,LABEL{padding:0;margin:0}
h1{margin:0 0 15px;padding:0 0 10px;font-size:20px;border-bottom:1px solid #f1f1f1}
.bx1,.bx2{margin-bottom:15px}
.bx1{width:180px;font:400 13px arial}
.bx2{width:70%}
.txtb_ad{border:1px solid #C488FF;padding:7px;color:#000}
.txtb_ad:focus{outline:none;box-shadow: 0 0 6px #D9B3FF;-webkit-box-shadow: 0 0 6px #D9B3FF;-moz-box-shadow: 0 0 6px #D9B3FF; }
.txtb_ad{width:270px}
.vm{vertical-align:middle}
.wx2-d {width:22%;background:#FBF4FF;border:1px solid #ededed;margin-top:45px;padding: 10px 15px 0px !important;}
.wx2-d ul{padding:0;margin:0 0 0 15px}
.wx2-d li{font:400 13px arial;list-style-type:circle;color:#000;padding-bottom:15px}
.btn1 {background:#DF0000;border:1px solid #DF0000;font:700 16px arial;padding:5px 24px;text-decoration:none; background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #DF0000), color-stop(1, #DF0000) ); background:-moz-linear-gradient( center top, #DF0000 5%, #DF0000 100% ); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#DF0000');color:#000;}
 .btn1:focus,.btn1:hover{-moz-box-shadow: 0 0 5px #C2C2C2;-webkit-box-shadow: 0 0 5px #C2C2C2;box-shadow: 0 0 5px #C2C2C2;}
 .ps11{padding:0 10px}
.cl1{color:#767676}
.stp{background:url('../images/search-tips.jpg') no-repeat;padding:3px 0 8px 23px;color:#c30000}
 .fz4 {font-size: 14px}
.lft1 p, .li1 {line-height: 23px}
.p9 {padding-right: 8px}
.p3 {padding-top: 5px}
 .c3 {clear: both}
#eto_ofr_ftr_frm h1{display:none}
#eto_ofr_ftr_frm .sh{display:none}
.form_area{border:0 solid #eee!important}
#eto_ofr_ftr_frm input,#eto_ofr_ftr_frm textarea,#eto_ofr_ftr_frm select{border:1px solid #aed2f2!important}
#eto_ofr_ftr_frm textarea{height:80px}
.lsyp{font:700 12px arial; margin-left:2px; padding:6px 0px 6px 0; background:#fff}
.lsyp b{color:#000099; font-size:16px}
.lsyp strong{color:#990000;font-size:22px;}
#q_send_req_button .ssbt{width:280px}
#q_contact_dtl1 table td{vertical-align:top!important}
.form_area{float:right}
.inx{background:#f8f7ff;padding:10px}
#q_send_req_button .sbtn{border:0!important}
.p33 {padding-top:2px}

</style>
<style type="text/css">
.form_area{background:#fff;border:1px solid  #eaeaea;width:683px;min-height:216px;text-align:left;font-family:arial}
.sbtn{background:url(../images/fform-main10.png) no-repeat -5px -259px;width:310px;height:29px;color:#ffffff;text-align:left;line-height:29px;font-size:19px;font-weight:bold;border:0px;padding-bottom:3px; /padding-bottom:0px;padding-left:10px;text-align:left; cursor:pointer;font-family:arial;}
.sh{font-size:12px;font-weight:bold;margin:0px 13px 0px 13px;padding:3px 0 8px 0; border-bottom:1px dotted #eaeaea}
.tx_h{background-image:url(../images/fform-main10.png);background-repeat:no-repeat}
</style>

</head>



<body class="search-show-box">

<div class="hm1 bbc">


<?php include 'includes/header_new.php';?>


<script>
function pagevalidsearch()
{
	var keywords=document.getElementById('pagekeywords');
	var rctyp=document.getElementById('rctyp');
	var adv_quantity=document.getElementById('adv_quantity');
	var adv_qty_list=document.getElementById('adv_qty_list');
	
	if(keywords.value=='' || keywords.value == null)
	{
		alert("Please enter a valid text to search.");
		return false;
	}
	else if(rctyp.value!='Suppliers' && adv_quantity.value!='' && (isNaN(adv_quantity.value)))
	{
		alert("Please enter a valid Quantity.");
		adv_quantity.value='';
		adv_quantity.focus();
		return false;
	}
	else if(adv_quantity.value!='' && adv_qty_list.value=='')
	{
		alert("Please select Measurement Unit.");
		adv_qty_list.focus();
		return false;
	}
}
function quot_on()
{
	if (document.getElementById('pagekeywords').value.length > 0)
	{
		document.getElementById('em1').className='tr1 cl1 off';
		document.getElementById('em2').className='tr1 on';
	}
	else
	{
		document.getElementById('em1').className='tr1 cl1 on';
		document.getElementById('em2').className='tr1 off';
		document.getElementById('exmch').checked = false
	}
}
function intext()
{
	document.getElementById('pagekeywords').value = document.getElementById('pagekeywords').value.replace(/"/g, '');
	var x1 = '"';
	var x2 = '"';
	val = document.getElementById('pagekeywords').value;
	if(document.getElementById('exmch').checked == true)
	{
		document.getElementById('pagekeywords').value =x1 + val + x2;
	}
	if(document.getElementById('exmch').checked == false)
	{
		document.getElementById('pagekeywords').value = document.getElementById('pagekeywords').value.replace(/"/g, '');
	}
}
function searchType(rctyp)
{
	if(rctyp=='Suppliers' || rctyp=='Products' || rctyp=='tender' || rctyp=='auction')
	{
		$("#est_qty").hide();
		$("#est_qty_unit").hide();
	}
	else
	{
		$("#est_qty").show();
		$("#est_qty_unit").show();
	}
}
</script>

<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" height="1" width="1"></div>
<div class="inner_wrapper">
<div class="wx1-d fl">


<h1>Advanced Search</h1>
<form name="searchForm1" action="search.php" onSubmit="return pagevalidsearch();" method="get" style="display:block" id="aa1">
<p class="bx1 fl p9 p3">Enter Keywords:</p>
<p class="bx2 fl li1">
<input aria-haspopup="true" aria-autocomplete="list" role="textbox" autocomplete="off" id="pagekeywords" name="keywords" onKeyUp="quot_on();" class="txtb_ad ui-autocomplete-input" style="width: 100%; max-width: 600px;"/>
<br>
<label class="tr1 off" id="em2"><input name="exmatch" class="vm" onClick="intext();" id="exmch" type="CHECKBOX"> Exact Match</label>
<label class="tr1 cl1 on" id="em1"><input disabled="disabled" name="" class="vm" onClick="" id="exmch" type="CHECKBOX"> Exact Match</label>
<script language="javascript" type="text/javascript">quot_on();</script>
</p>
<p class="bx1 fl p9 p3">Looking For:</p>
<p class="bx2 fl li1">
<select style="width: 100%; max-width: 300px;" name="rctyp" id="rctyp" class="txtb_ad" onChange="searchType(this.value);">
	<option value="Products">Products</option>
	<option value="Suppliers">Suppliers</option>
	<option value="buy_lead">Buy Leads</option>
    <option value="tender">Tender</option>
    <option value="auction">Auction</option>
</select>
</p>
<p class="bx1 fl p9 p3" id="est_qty" style="display:none">Estimated Quantity:</p>
<p class="bx2 fl li1" id="est_qty_unit" style="display:none"><input name="adv_quantity" id="adv_quantity" class="txtb_ad" style="width:180px;margin-right:7px">
	<select style="width:130px" name="adv_qty_list" id="adv_qty_list" class="txtb_ad">
    	<?php
		$sql_mu="select * from measurement_unit where mu_status='1'";
		$res_mu=mysqli_query($con, $sql_mu);
	?>
    	<option value="">--Select Unit--</option>
    <?php	
		while($row_mu=mysqli_fetch_object($res_mu)){	?>
		<option style="color: rgb(0, 0, 0);" value="<?php echo $row_mu->mu_id; ?>"><?php echo $row_mu->mu_name;	?></option>
    <?php	}	?>
        </select>
        </p>

<p><br /></p>
<p style="padding-left:190px">
<br/><br/><input value=""  type="hidden"><input value="Search" class="btn1" id="btnSearch1" name="search" type="SUBMIT">
</p>
</form>


</div>
<div class="wx2-d fl">
<p class="fz5 bo stp">Search Tips</p>
<ul>
<li>To search exact phrase in search result, use double quotes around the text, e.g. <u>"organic milk"</u> by selecting the Exact Match option.</li>
<li>For better results, find only one product / service at a time</li>
<li>Avoid using very long search text.</li>
</ul>
</div>
</div><!-- inner_wrapper end -->

<p class="c3"><br></p>
<div style="text-align:center;margin-bottom:10px;">
<?php
	$sql_adv="select * from advertisement where adv_imagewidth='728' and adv_imageheight='90' and adv_status='1' order by rand() limit 1";
	$res_adv=mysqli_query($con, $sql_adv);
	if(mysqli_num_rows($res_adv)>0)
	{
		$row_adv=mysqli_fetch_object($res_adv);	
		?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="728" height="90" id="advertisement_banner_img"/></a><?php
	}
	else
	{
?>
		<img src="upload/advertisement/239-186-advertisement.png" width="728" height="90" id="advertisement_banner_img"/>
<?php	}	?>
</div>
</div>

<?php include 'includes/footer.php';?>