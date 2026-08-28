<?php
include 'common.php';
if(!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm']=='')
{
	header("Location:sign-in.php");	
}
$uid=$_SESSION['uid_indm'];

class Editcat{
	var $bnsprof_regno;
	var $bnsprof_regauthority;
	var $bnsprof_cin_no;
	var $bnsprof_tan_no;
	var $bnsprof_pan_no;
	var $bnsprof_svtax_no;
	var $bnsprof_excisereg_no;
	var $bnsprof_vat_no;
	var $bnsprof_ie_code;
	var $bnsprof_cst_no;
	var $bnsprof_msme_no;
	var $bnsprof_epf_no;
	var $bnsprof_esi_no;
	var $bnsprof_sct_no;
	var $bnsprof_dnb_no;
	var $bnsprof_rbi_no;
	var $bnsprof_fssailic_no;
	var $bnsprof_nsic_no;
	var $bnsprof_sst_no;
	var $userd;
	var $msg;

	function __construct()
	{
	}

	function valid()
	{
		$valid=true;
		if($this->bnsprof_regauthority!= '' && $this->bnsprof_regno == "")
		{
			$this->msg='<font color=#CC0000">Registration Number is compulsory with Registration Authority.</font>';
			$valid=false;
		}
		else if($this->bnsprof_regno!= "" && $this->bnsprof_regauthority== '')
		{
			$this->msg= '<font color="#CC0000">Registration Authority is compulsory with Registration Number.</font>';
			$valid=false;
		}
		return $valid;
	}
	
	function update(){
			
		$sqlchk="select * from business_profile where bnsprof_uid='".$this->userd."'";
		$reschk=mysql_query($sqlchk);
		if(mysql_num_rows($reschk)>0)
		{
			  $sql="update business_profile set		
					bnsprof_regno='".$this->bnsprof_regno."',
					bnsprof_regauthority='".$this->bnsprof_regauthority."',
					bnsprof_cin_no='".$this->bnsprof_cin_no."',
					bnsprof_tan_no='".$this->bnsprof_tan_no."',
					bnsprof_pan_no='".$this->bnsprof_pan_no."',
					bnsprof_svtax_no='".$this->bnsprof_svtax_no."',
					bnsprof_excisereg_no='".$this->bnsprof_excisereg_no."',
					bnsprof_vat_no='".$this->bnsprof_vat_no."',
					bnsprof_ie_code='".$this->bnsprof_ie_code."',
					bnsprof_cst_no='".$this->bnsprof_cst_no."',
					bnsprof_msme_no='".$this->bnsprof_msme_no."',
					bnsprof_epf_no='".$this->bnsprof_epf_no."',
					bnsprof_esi_no='".$this->bnsprof_esi_no."',
					bnsprof_sct_no='".$this->bnsprof_sct_no."',
					bnsprof_dnb_no='".$this->bnsprof_dnb_no."',
					bnsprof_rbi_no='".$this->bnsprof_rbi_no."',
					bnsprof_fssailic_no='".$this->bnsprof_fssailic_no."',
					bnsprof_nsic_no='".$this->bnsprof_nsic_no."',
					bnsprof_sst_no='".$this->bnsprof_sst_no."'
					where bnsprof_uid=".$this->userd;					
				    mysql_query($sql) or die(mysql_error());
					$this->msg='<div class="save bnr mt12" id="savemsg"><strong> Statuory Details saved successfully ! </strong></div>';
		}
		else
		{
			  $sql="insert into business_profile set		
					bnsprof_uid='".$this->userd."',
					bnsprof_regno='".$this->bnsprof_regno."',
					bnsprof_regauthority='".$this->bnsprof_regauthority."',
					bnsprof_cin_no='".$this->bnsprof_cin_no."',
					bnsprof_tan_no='".$this->bnsprof_tan_no."',
					bnsprof_pan_no='".$this->bnsprof_pan_no."',
					bnsprof_svtax_no='".$this->bnsprof_svtax_no."',
					bnsprof_excisereg_no='".$this->bnsprof_excisereg_no."',
					bnsprof_vat_no='".$this->bnsprof_vat_no."',
					bnsprof_ie_code='".$this->bnsprof_ie_code."',
					bnsprof_cst_no='".$this->bnsprof_cst_no."',
					bnsprof_msme_no='".$this->bnsprof_msme_no."',
					bnsprof_epf_no='".$this->bnsprof_epf_no."',
					bnsprof_esi_no='".$this->bnsprof_esi_no."',
					bnsprof_sct_no='".$this->bnsprof_sct_no."',
					bnsprof_dnb_no='".$this->bnsprof_dnb_no."',
					bnsprof_rbi_no='".$this->bnsprof_rbi_no."',
					bnsprof_fssailic_no='".$this->bnsprof_fssailic_no."',
					bnsprof_nsic_no='".$this->bnsprof_nsic_no."',
					bnsprof_sst_no='".$this->bnsprof_sst_no."',
					bnsprof_creation_date=now()";					
				    mysql_query($sql) or die(mysql_error());
					$this->msg='<div class="save bnr mt12" id="savemsg"><strong> Statuory Details saved successfully ! </strong></div>';			
		}		
	}
}
				
$ecms=new Editcat();

if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg'];	unset($_SESSION['msg']); }   else{ $msg=""; }

if(isset($_POST['btnUpdate'])){
	
 	$ecms->bnsprof_regno=trim(addslashes($_POST['bnsprof_regno']));
	$ecms->bnsprof_regauthority=trim(addslashes($_POST['bnsprof_regauthority'])); 
	$ecms->bnsprof_cin_no= trim(addslashes($_POST['bnsprof_cin_no']));
	$ecms->bnsprof_tan_no=trim(addslashes($_POST['bnsprof_tan_no']));
	$ecms->bnsprof_pan_no=trim(addslashes($_POST['bnsprof_pan_no']));
	$ecms->bnsprof_svtax_no=trim(addslashes($_POST['bnsprof_svtax_no']));
	$ecms->bnsprof_excisereg_no=trim(addslashes($_POST['bnsprof_excisereg_no']));
	$ecms->bnsprof_vat_no=trim(addslashes($_POST['bnsprof_vat_no']));
	$ecms->bnsprof_ie_code=trim(addslashes($_POST['bnsprof_ie_code'])); 
	$ecms->bnsprof_cst_no= trim(addslashes($_POST['bnsprof_cst_no']));
	$ecms->bnsprof_msme_no=trim(addslashes($_POST['bnsprof_msme_no']));
	$ecms->bnsprof_epf_no=trim(addslashes($_POST['bnsprof_epf_no']));
	$ecms->bnsprof_esi_no=trim(addslashes($_POST['bnsprof_esi_no']));
	$ecms->bnsprof_sct_no=trim(addslashes($_POST['bnsprof_sct_no']));
	$ecms->bnsprof_dnb_no=trim(addslashes($_POST['bnsprof_dnb_no']));
	$ecms->bnsprof_rbi_no=trim(addslashes($_POST['bnsprof_rbi_no']));
	$ecms->bnsprof_fssailic_no=trim(addslashes($_POST['bnsprof_fssailic_no']));
	$ecms->bnsprof_nsic_no=trim(addslashes($_POST['bnsprof_nsic_no']));
	$ecms->bnsprof_sst_no=trim(addslashes($_POST['bnsprof_sst_no']));
	$ecms->userd=trim(addslashes($_POST['userd']));

	if($ecms->valid()){
		$ecms->update();
	}
	//echo $ecms->msg;
	 $_SESSION['msg']=$ecms->msg;
	header("location:myproduct-buy.php");
}

//echo "<pre>";print_r($row);echo "</pre>";exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<!-- meta start -->
<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/b-v-7.css" type="text/css" rel="stylesheet">
<link href="css/my-v1-v-14.css" type="text/css" rel="stylesheet">
<style>.label { color: #000 !important; } </style>
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.ui.widget.js"></script>
<script language="javascript" type="text/javascript" src="js/jquery.fileupload.js"></script>
<script type="text/javascript">
function chkalldetails()
{
	var bnsprof_regno=document.getElementById('bnsprof_regno');
	var bnsprof_regauthority=document.getElementById('bnsprof_regauthority');
    var message="";
    var valid=true;
	
	if(bnsprof_regauthority.value!= '' && bnsprof_regno.value== '')
	{
		message='Registration Number is compulsory with Registration Authority.';
		bnsprof_regno.focus();
		valid=false;
	}
	else if(bnsprof_regno.value!="" && bnsprof_regauthority.value == '')
	{
		message="Registration Authority is compulsory with Registration Number.";
		bnsprof_regauthority.focus();
		valid=false;	
	}
	if(!valid)
	{
		document.getElementById('updatemessage').style.color = "red";
		document.getElementById('updatemessage').innerHTML = message;	
	}
	return valid;
}
$(function () {
    
    // Change this to the location of your server-side upload handler:
    var url ='http://arabyos.com/server/php/';
    jQuery('#fileupload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
               jQuery('#business_documents').val(file.name);
			   jQuery('#business_doc').attr('src',file.thumbnailUrl);
                       list_photo();	

            });
        }
       
    })
});
$(document).ready(function() {
    
    // Change this to the location of your server-side upload handler:
    var url ='http://arabyos.com/server/php/';
    jQuery('#file_upload').fileupload({
        url: url,
        maxNumberOfFiles: 1,
        dataType: 'json',
        done: function (e, data) {
            jQuery.each(data.result.files, function (index, file)
			{
				jQuery.post("companylogo-update.php", {'uid' :'<?php echo $uid; ?>', 'file' : file.name }, function(data) {
						list_photo();	
				});

            });
        }
       
    })
});


</script>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
        <?php include "includes/header_new.php"; ?>
        <br><br>
        
		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>

<!-- Header End Here::-->
<div class="inner_wrapper">
    		<?php include 'includes/header_menu.php';?>
		<!--left navigation:start-->
		<?php include 'includes/left_menu.php';?>
		<!--left navigation:ends-->
        <div class="w56b f1 p2b p14 blr">
	<div>
	<!--<div class="bc f11">Company Profile &raquo;</div>-->
	<h1>Business Profile</h1>
	</div>
    <?php include 'includes/business-panel.php';?>
	<div id="re_link" class="utab"><span style="font-size: 14px;" class="f1">Provide your statutory details to build better trust among your prospective buyers.</span></div>
	<div class="tbox" id="div_succ" style="display: none;"><strong style="color:#000;">Saved Successfully!</strong></div>
    <div style="text-align:left;width:489px;" class="" id="updatemessage"><?php echo $msg; ?></div>
	<div class="mt5">
	<!--Company Registration form:start-->
	<form action="" name="form1" class="f12" method="post" onsubmit="return chkalldetails();">
	<div class="frm_a clb" style="background-color:#FAF4FF">
	<table align="left" border="0" cellpadding="4" cellspacing="0" width="100%">
	<?php
	$sql="select * from business_profile where bnsprof_uid='".$uid."'"; 
$res=mysql_query($sql);
$row=mysql_fetch_object($res);
?>
	<tbody><tr>
		<td class="label" width="160">Registration No.</td>
		<td><div id="a17" class="tbp cona" style="display:none"><div class="t1a" align="left">Company Registration Number</div></div>
        <input name="bnsprof_regno" id="bnsprof_regno" class="a_f rf" maxlength="30" type="text" value="<?php echo $row->bnsprof_regno;?>">
        <span id="reg_nu" class="em" style="display:none"></span></td>
	</tr>

	<tr>
		<td class="label">Registration Authority</td>
		<td><div id="a18" class="tbp cona" style="display:none"><div class="t1a" align="left">Registration Authority</div></div>
        <input name="bnsprof_regauthority" id="bnsprof_regauthority" value="<?php echo $row->bnsprof_regauthority;?>" class="a_f rf" maxlength="100" type="text">
        <span id="reg_w" class="em" style="display:none"></span></td>
	</tr>

	<tr>
		<td class="label">CIN No.</td>
		<td><div id="a19" class="tbp cona" style="display:none"><div class="t1a" align="left">Company Identification Number</div></div>
        <input name="bnsprof_cin_no" class="a_f rf"  id="bnsprof_cin_no" maxlength="25" type="text" value="<?php echo $row->bnsprof_cin_no;?>">
        </td>
	</tr>

	<tr>
		<td class="label">TAN No.</td>
		<td><div id="a20" class="tbp cona" style="display:none"><div class="t1a" align="left">Tax Deduction Account Number</div></div>
        <input id="bnsprof_tan_no" name="bnsprof_tan_no" class="a_f rf" maxlength="10" type="text" value="<?php echo $row->bnsprof_tan_no;?>">
        </td>
	</tr> 
	
	<tr>
		<td class="label">PAN No.</td>
		<td><div id="a21" class="tbp cona" style="display:none"><div class="t1a" align="left">Permanent Account Number</div></div>
        <input id="bnsprof_pan_no" name="bnsprof_pan_no" class="a_f rf" maxlength="10" type="text" value="<?php echo $row->bnsprof_pan_no;?>">
        </td>
	</tr>

	<tr>
		<td class="label">Service Tax No.</td>
		<td><div id="a22" class="tbp cona" style="display:none"><div class="t1a" align="left">Service Tax Number</div></div>
        <input id="bnsprof_svtax_no" name="bnsprof_svtax_no" class="a_f rf" maxlength="15" type="text" value="<?php echo $row->bnsprof_svtax_no;?>">
        </td>
	</tr> 

	<tr>
		<td class="label">Excise Reg. No.</td>
		<td><div id="a23" class="tbp cona" style="display:none"><div class="t1a" align="left">Excise Registration Number</div></div>
        <input id="bnsprof_excisereg_no" name="bnsprof_excisereg_no" class="a_f rf" maxlength="15" type="text" value="<?php echo $row->bnsprof_excisereg_no;?>"></td>
	</tr>

	<tr>
		<td class="label">TIN No. / VAT No.</td>
		<td><div id="a24" class="tbp cona" style="display:none"><div class="t1a" align="left">Value Added Tax Number</div></div>
        <input id="bnsprof_vat_no" name="bnsprof_vat_no" class="a_f rf" maxlength="12" type="text" value="<?php echo $row->bnsprof_vat_no;?>"></td>
	</tr>

	<tr>
		<td class="label">DGFT/IE Code</td>
		<td><div id="a25" class="tbp cona" style="display:none"><div class="t1a" align="left">Directorate General of<br>Foreign Trade/Import Export Code</div></div>
        <input id="bnsprof_ie_code" name="bnsprof_ie_code" class="a_f rf" maxlength="10" type="text" value="<?php echo $row->bnsprof_ie_code;?>"></td>
	</tr>

	<tr>
		<td class="label">CST No.</td>
		<td><div id="a26" class="tbp cona" style="display:none"><div class="t1a" align="left">Central Sales Tax Number</div></div>
        <input id="bnsprof_cst_no" name="bnsprof_cst_no" class="a_f rf" maxlength="12" type="text" value="<?php echo $row->bnsprof_cst_no;?>"></td>
	</tr>

	<tr>
		<td class="label">SSI No. / MSME No.</td>
		<td><div id="a27" class="tbp cona" style="display:none"><div class="t1a" align="left">Small Scale Industries<br>Registration Number</div></div>
        <input id="bnsprof_msme_no" name="bnsprof_msme_no" class="a_f rf" maxlength="25" type="text" value="<?php echo $row->bnsprof_msme_no;?>"></td>
	</tr>

	<tr>
		<td class="label">EPF No.</td>
		<td><div id="a28" class="tbp cona" style="display:none"><div class="t1a" align="left">Employee Provident Fund Number</div></div>
        <input name="bnsprof_epf_no" class="a_f rf" id="bnsprof_epf_no" maxlength="40" type="text" value="<?php echo $row->bnsprof_epf_no;?>"></td>
	</tr>

	<tr>
		<td class="label">ESI No.</td>
		<td><div id="a29" class="tbp cona" style="display:none"><div class="t1a" align="left">Employee's State Insurance Number</div></div>
        <input name="bnsprof_esi_no" class="a_f rf" id="bnsprof_esi_no" maxlength="30" type="text" value="<?php echo $row->bnsprof_esi_no;?>"></td>
	</tr>

	<tr>
		<td class="label">SCT No.</td>
		<td><div id="a30" class="tbp cona" style="display:none"><div class="t1a" align="left">SCT Number</div></div>
        <input name="bnsprof_sct_no" class="a_f rf" id="bnsprof_sct_no" maxlength="20" type="text" value="<?php echo $row->bnsprof_esi_no;?>"></td>
	</tr>

	<tr>
		<td class="label">DNB No.</td>
		<td><div id="a31" class="tbp cona" style="display:none"><div class="t1a" align="left">DNB Number</div></div>
        <input name="bnsprof_dnb_no" class="a_f rf" id="bnsprof_dnb_no" maxlength="20" type="text" value="<?php echo $row->bnsprof_dnb_no;?>"></td>
	</tr>

	<tr>
		<td class="label">RBI No.</td>
		<td><div id="a32" class="tbp cona" style="display:none"><div class="t1a" align="left">RBI Number</div></div>
        <input name="bnsprof_rbi_no" class="a_f rf" id="bnsprof_rbi_no" maxlength="20" type="text" value="<?php echo $row->bnsprof_rbi_no;?>"></td>
	</tr>
	<tr>
		<td class="label">FSSAI-LICENSE NO.</td>
		<td><div id="a33" class="tbp cona" style="display:none"><div class="t1a" align="left">FSSAI-LICENSE Number</div></div>
        <input name="bnsprof_fssailic_no" class="a_f rf"  id="bnsprof_fssailic_no" maxlength="30" type="text" value="<?php echo $row->bnsprof_fssailic_no;?>"></td>
	</tr>
	<tr>
		<td class="label">N.S.I.C No.</td>
		<td><div id="a34" class="tbp cona" style="display:none"><div class="t1a" align="left">N.S.I.C Number</div></div>
        <input name="bnsprof_nsic_no" class="a_f rf" id="bnsprof_nsic_no" maxlength="30" type="text" value="<?php echo $row->bnsprof_nsic_no;?>"></td>
	
	</tr>
	<tr>
		<td class="label">S.S.T No.</td>
		<td><div id="a35" class="tbp cona" style="display:none"><div class="t1a" align="left">S.S.T Number</div></div>
        <input name="bnsprof_sst_no" class="a_f rf" id="bnsprof_sst_no" maxlength="30" type="text" value="<?php echo $row->bnsprof_sst_no;?>"></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left">
			<table><tbody><tr><td width="118px;">
            	<input name="userd" id="userd" value="<?php echo $uid;?>" tabindex="31" type="hidden">
				<input name="btnUpdate" id="btnUpdate" value="Update Details" class="saps mt5" type="submit">
				</td><td></td></tr></tbody></table>
		</td>
	</tr> 

	</tbody></table>
	<div class="clb">&nbsp;</div>
	</div>
	</form>
	<!--Company Registration form:ends-->
	</div>
	</div>
		<div class="c3">&nbsp;</div></div>
</div>
		<!--footer:start-->
		<?php include 'includes/footer.php'; ?>