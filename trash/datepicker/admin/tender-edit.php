<?php 
include "../common.php";


check_user_login();
class editTender
{

	var $msg;
	var $tnd_id;
	var $tnd_heading;
	var $mcat_id;
	var $pc_id;
	var $tnd_pc_id;
	var $tnd_value;
	var $tnd_currency;
	
	var $tnd_notice_type;
	var $tnd_qty;
	var $tnd_qty_mu_id;
	var $tnd_emd;
	var $tnd_document_fees;
	var $tnd_document_fees_currency;
	var $tnd_project_period;
	var $tnd_products;
	
	var $tnd_publish_date;
	var $tnd_docSaleStart_date;
	var $tnd_docSaleEnd_date;
	var $tnd_docSubmitBefore_date;
	var $tnd_due_date;
	var $tnd_prequalification_criteria;
	var $tnd_details;
	var $tnd_preferred_location;
    var $tnd_country;	

	function __construct($tnd_id){
		$this->tnd_id=$tnd_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from tender,product_category where tnd_pc_id=pc_id and md5(tnd_id)='".$this->tnd_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		$valid=true;
		
		if($this->tnd_heading=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Tender heading.</div>';
			$valid=false;
		}
		if($this->mcat_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Main category.</div>';
			$valid=false;
		}
		if($this->pc_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
			$valid=false;
		}
		else if($this->tnd_pc_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
			$valid=false;
		}
		else if($this->tnd_value!='' && $this->tnd_currency=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Currency.</div>';
			$valid=false;
		}
		else if($this->tnd_notice_type=="")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Notice Type.</div>';
			$valid=false;
		}
		else if($this->tnd_document_fees=="")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Document Fees.</div>';
			$valid=false;
		}
		else if($this->tnd_document_fees_currency=="")
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select currency for Document Fees.</div>';
			$valid=false;
		}
		else if($this->tnd_publish_date=='' || $this->tnd_publish_date=='0000-00-00')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select tender publish date.</div>';
			$valid=false;
		}
		else if($this->tnd_docSaleStart_date=='' || $this->tnd_docSaleStart_date=='0000-00-00')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document sale start date.</div>';
			$valid=false;
		}
		else if($this->tnd_docSaleEnd_date=='' || $this->tnd_docSaleEnd_date=='0000-00-00')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document sale end date.</div>';
			$valid=false;
		}
		else if($this->tnd_docSubmitBefore_date=='' || $this->tnd_docSubmitBefore_date=='0000-00-00')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select document submit before date.</div>';
			$valid=false;
		}
		else if($this->tnd_due_date=='' || $this->tnd_due_date=='0000-00-00')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select tender due date.</div>';
			$valid=false;
		}
		else if($this->tnd_prequalification_criteria=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Pre-qualification criteria.</div>';
			$valid=false;
		}
		else if($this->tnd_details=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter tender details.</div>';
			$valid=false;
		}
		
		return $valid;
	}
	
	function update() 
	{ 
	  global $con;
	
		$sql="update tender
			set
				tnd_pc_id='".$this->tnd_pc_id."',
				tnd_heading ='".$this->tnd_heading."',
				tnd_value ='".$this->tnd_value."',
				tnd_currency ='".$this->tnd_currency."',
				tnd_notice_type = '".$this->tnd_notice_type."',
				tnd_qty = '".$this->tnd_qty."',
				tnd_qty_mu_id = '".$this->tnd_qty_mu_id."',
				tnd_emd = '".$this->tnd_emd."',
				tnd_document_fees = '".$this->tnd_document_fees."',
				tnd_document_fees_currency = '".$this->tnd_document_fees_currency."',
				tnd_project_period = '".$this->tnd_project_period."',
				tnd_products = '".$this->tnd_products."',
				tnd_prequalification_criteria ='".$this->tnd_prequalification_criteria."',
				tnd_details ='".$this->tnd_details."',
				tnd_preferred_location ='".$this->tnd_preferred_location."',
				tnd_publish_date = '".$this->tnd_publish_date."',
				tnd_docSaleStart_date = '".$this->tnd_docSaleStart_date."',
				tnd_docSaleEnd_date = '".$this->tnd_docSaleEnd_date."',
				tnd_docSubmitBefore_date = '".$this->tnd_docSubmitBefore_date."',
				tnd_due_date = '".$this->tnd_due_date."',
				tnd_updated_date=now()
			where
				tnd_id='".$this->tnd_id."'";
				
				//echo $sql;
				//exit;
				
			mysqli_query($con, $sql);
		
		$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Tender updated successfully.</div>';
	}	
}

if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$ob=new editTender($_GET['token']);
$row=$ob->detailsObj();

if(isset($_POST['btnUpdate'])){
	
	$ob->tnd_id=addslashes(trim($_POST['tnd_id']));
	$ob->tnd_heading=addslashes(trim($_POST['tnd_heading']));
	$ob->mcat_id=addslashes(trim($_POST['mcat_id']));
	$ob->pc_id=addslashes(trim($_POST['pc_id']));
	$ob->tnd_pc_id=addslashes(trim($_POST['tnd_pc_id']));
	$ob->tnd_value=addslashes(trim($_POST['tnd_value']));
	$ob->tnd_currency=addslashes(trim($_POST['tnd_currency']));
	
	$ob->tnd_notice_type = addslashes(trim($_POST['tnd_notice_type']));
	$ob->tnd_qty = addslashes(trim($_POST['tnd_qty']));
	$ob->tnd_qty_mu_id = addslashes(trim($_POST['tnd_qty_mu_id']));
	$ob->tnd_emd = addslashes(trim($_POST['tnd_emd']));
	$ob->tnd_document_fees = addslashes(trim($_POST['tnd_document_fees']));
	$ob->tnd_document_fees_currency = addslashes(trim($_POST['tnd_document_fees_currency']));
	$ob->tnd_project_period = addslashes(trim($_POST['tnd_project_period']));
	$ob->tnd_products = addslashes(trim($_POST['tnd_products']));
	
	$ob->tnd_publish_date=addslashes(trim($_POST['tnd_publish_date']));
	$ob->tnd_docSaleStart_date=addslashes(trim($_POST['tnd_docSaleStart_date']));
	$ob->tnd_docSaleEnd_date=addslashes(trim($_POST['tnd_docSaleEnd_date']));
	$ob->tnd_docSubmitBefore_date=addslashes(trim($_POST['tnd_docSubmitBefore_date']));
	$ob->tnd_due_date=addslashes(trim($_POST['tnd_due_date']));
	
	$ob->tnd_prequalification_criteria=addslashes(trim($_POST['tnd_prequalification_criteria']));
	$ob->tnd_details=addslashes(trim($_POST['tnd_details']));
	$ob->tnd_preferred_location=addslashes(trim($_POST['tnd_preferred_location']));
	$ob->tnd_country="";
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header('Location: ../tender-email.php?admn_tnd_id='.$ob->tnd_id);
	//header("location:tender-edit.php?token=".md5($ob->tnd_id));
}
?>
<?php include "includes/admin-top.php" ?>
<div class="main-container" id="main-container">
	<script type="text/javascript">
		try{ace.settings.check('main-container' , 'fixed')}catch(e){}
	</script>

	<div class="main-container-inner">
		<a class="menu-toggler" id="menu-toggler" href="#">
			<span class="menu-text"></span>
		</a>
<script type="text/javascript">
function showCategory()
{
	var pc_id=document.getElementById('mcat_id').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showSubcat();	 }); 
}
function showSubcat()
{
	var pc_id=document.getElementById('pc_id').value;
	$.get("showSubcat.php",{q:pc_id},	function(data){	$('#tnd_pc_id').html(data); }); 
}
function validForm()
{
	var main_cat=document.getElementById('main_cat');
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
	

	
	var message="";
	var valid=true;
	
	if(mcat_id.value=='')
	{
		message="Kindly select Main Category.";
		mcat_id.focus();
		valid=false;
	}
	else if(pc_id.value=='')
	{
		message="Kindly select Category.";
		pc_id.focus();
		valid=false;
	}
	else if(tnd_pc_id.value=='')
	{
		message="Kindly select Sub-Category.";
		tnd_pc_id.focus();
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
	
	if(!valid)
	{
		document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> "+message;
		document.getElementById('msg').className="alert alert-danger";
	}
	return valid;
	
}
</script>
<?php include "includes/admin-left-con.php" ?>
<div class="main-content">
	<div class="breadcrumbs" id="breadcrumbs">
		<script type="text/javascript">
			try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
		</script>

		<ul class="breadcrumb">
			<li>
				<i class="icon-home home-icon"></i>
				<a href="welcome.php">Home</a>
			</li>
			<li>
				<a href="tender-view.php">Manage Tender</a>
			</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Tender Edit
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validForm();">
	<input type="hidden" id="tnd_id" name="tnd_id" value="<?php echo $row->tnd_id; ?>" />

	<div id="msg"><?php echo $msg;?></div>
	
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Tender Heading:</label>
		<div class="col-sm-9">
        	<input name="tnd_heading" id="tnd_heading" class="col-xs-12 col-sm-6" type="text" value="<?php echo $row->tnd_heading; ?>" />
		</div>
	</div>
	
    <div class="form-group">
    <?php
	$mcat_sql="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row->pc_parent_id."' and pc_status='1') and pc_status='1'";
	$mcat_res=mysqli_query($con, $mcat_sql);
	$mcat_row=mysqli_fetch_object($mcat_res);
	?>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category:</label>
		<div class="col-sm-9">
        <select id="mcat_id" name="mcat_id" onChange="showCategory();">
        <?php
			$sql_mcat="select * from product_category where pc_parent_id='0' and pc_status='1'";
			$res_mcat=mysqli_query($con, $sql_mcat);
			while($row_mcat=mysqli_fetch_object($res_mcat))
			{	?>
				<option value="<?php echo $row_mcat->pc_id; ?>" <?php if($row_mcat->pc_id==$mcat_row->pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_mcat->pc_name; ?></option>
		<?php	}	?>
        </select>
		</div>
	</div>
     <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Category:</label>
		<div class="col-sm-8">
        <?php
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category c,product_category s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_pc="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$mcat_row->pc_id."'";
//			$sql_pc="select * from product_category where pc_parent_id='0'";
			$res_pc=mysqli_query($con, $sql_pc);
			?>
            <select id="pc_id" name="pc_id" onChange="showSubcat();">
            <?php
			while($row_pc=mysqli_fetch_object($res_pc))
			{	?>
				<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$row->pc_parent_id){ ?> selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
		<?php	}	?>
	        </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Sub-Category:</label>
		<div class="col-sm-8">
        <?php
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category c,product_category s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_spc="select * from product_category where pc_parent_id=(select pc_parent_id from product_category where pc_id='".$row->tnd_pc_id."')";
			$res_spc=mysqli_query($con, $sql_spc);
			?>
            <select id="tnd_pc_id" name="tnd_pc_id">
            	<option value="0"> - Select Sub-Category - </option>
            <?php
			while($row_spc=mysqli_fetch_object($res_spc))
			{	?>
				<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$row->tnd_pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
		<?php	}	?>
	        </select>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Tender Value:</label>
		<div class="col-sm-9">
        	<input name="tnd_value" id="tnd_value" class="col-xs-12 col-sm-4" type="text" value="<?php echo $row->tnd_value; ?>" />
            &nbsp;
            <select name="tnd_currency" id="tnd_currency">
				<option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($currencyrow=mysqli_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if($row->tnd_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Notice Type:</label>
		<div class="col-sm-9">
        	<input name="tnd_notice_type" id="tnd_notice_type" class="col-xs-12 col-sm-6" type="text" value="<?php echo $row->tnd_notice_type; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Quantity:</label>
		<div class="col-sm-9">
        	<input name="tnd_qty" id="tnd_qty" class="col-xs-12 col-sm-4" type="text" value="<?php echo $row->tnd_qty; ?>" />
            &nbsp;
            <select name="tnd_qty_mu_id" id="tnd_qty_mu_id">
               <option value="">-Select Unit-</option>
                <?php                
				$res_mu=mysqli_query($con, "select * from measurement_unit where mu_status='1'");
				while($row_mu=mysqli_fetch_object($res_mu)){
				?>
                <option value="<?php echo $row_mu->mu_id; ?>" <?php if($row_mu->mu_id==$row->tnd_qty_mu_id){ ?> selected="selected"<?php } ?> ><?php echo $row_mu->mu_name;?></option>
				<?php } ?>
            </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">EMD:</label>
		<div class="col-sm-9">
        	<input name="tnd_emd" id="tnd_emd" class="col-xs-12 col-sm-6" type="text" value="<?php echo $row->tnd_emd; ?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Fees:</label>
		<div class="col-sm-9">
        	<input name="tnd_document_fees" id="tnd_document_fees" class="col-xs-12 col-sm-4" type="text" value="<?php echo $row->tnd_document_fees; ?>" />
            &nbsp;
            <select name="tnd_document_fees_currency" id="tnd_document_fees_currency">
				<option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($currencyrow=mysqli_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if($row->tnd_document_fees_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Project Period:</label>
		<div class="col-sm-9">
        	<input name="tnd_project_period" id="tnd_project_period" class="col-xs-12 col-sm-6" type="text" value="<?php echo $row->tnd_project_period; ?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Products:</label>
		<div class="col-sm-9">
        	<input name="tnd_products" id="tnd_products" class="col-xs-12 col-sm-7" type="text" value="<?php echo $row->tnd_products; ?>" />
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Tender Publish Date:</label>
		<div class="col-sm-2">
			<div class="input-group">
				<input class="form-control date-picker" id="tnd_publish_date" name="tnd_publish_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo date("Y-m-d",strtotime($row->tnd_publish_date)); ?>" />
				<span class="input-group-addon">
					<i class="icon-calendar bigger-110"></i>
				</span>
			</div>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Sale Starts:</label>
		<div class="col-sm-2">
			<div class="input-group">
				<input class="form-control date-picker" id="tnd_docSaleStart_date" name="tnd_docSaleStart_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo date("Y-m-d",strtotime($row->tnd_docSaleStart_date)); ?>" />
				<span class="input-group-addon">
					<i class="icon-calendar bigger-110"></i>
				</span>
			</div>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Sale Ends:</label>
		<div class="col-sm-2">
			<div class="input-group">
				<input class="form-control date-picker" id="tnd_docSaleEnd_date" name="tnd_docSaleEnd_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo date("Y-m-d",strtotime($row->tnd_docSaleEnd_date)); ?>" />
				<span class="input-group-addon">
					<i class="icon-calendar bigger-110"></i>
				</span>
			</div>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Document Submit Before:</label>
		<div class="col-sm-2">
			<div class="input-group">
				<input class="form-control date-picker" id="tnd_docSubmitBefore_date" name="tnd_docSubmitBefore_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo date("Y-m-d",strtotime($row->tnd_docSubmitBefore_date)); ?>" />
				<span class="input-group-addon">
					<i class="icon-calendar bigger-110"></i>
				</span>
			</div>
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Tender Due Date:</label>
		<div class="col-sm-2">
			<div class="input-group">
				<input class="form-control date-picker" id="tnd_due_date" name="tnd_due_date" data-date-format="yyyy-mm-dd" type="text" value="<?php echo date("Y-m-d",strtotime($row->tnd_due_date)); ?>"/>
				<span class="input-group-addon">
					<i class="icon-calendar bigger-110"></i>
				</span>
			</div>
		</div>
	</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Pre-qualification Criteria:</label>
		<div class="col-sm-8">
        	<textarea id="tnd_prequalification_criteria" name="tnd_prequalification_criteria" class="col-sm-8"><?php echo $row->tnd_prequalification_criteria; ?></textarea>
            
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Details:</label>
		<div class="col-sm-8">
        	<textarea id="tnd_details" name="tnd_details" class="col-sm-8"><?php echo $row->tnd_details; ?></textarea>
            
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Prefrences:</label>
        <div class="radio col-sm-8">
			<label><input type="radio" id="tnd_preferred_location_1" name="tnd_preferred_location" class="ace" value="abroad" <?php if($row->tnd_preferred_location=='abroad'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="tnd_preferred_location_2" name="tnd_preferred_location" class="ace" value="any" <?php if($row->tnd_preferred_location=='any'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad + Domestic</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="tnd_preferred_location_3" name="tnd_preferred_location" class="ace" value="domestic" <?php if($row->tnd_preferred_location=='domestic'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Domestic Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="tnd_preferred_location_4" name="tnd_preferred_location" class="ace" value="my_city" <?php if($row->tnd_preferred_location=='my_city'){ ?> checked="checked"<?php } ?>/><span class="lbl"> My City Only</span></label>
            
        </div>
	</div>
        
    
       
	<div class="clearfix form-actions">
		<div class="col-md-offset-3 col-md-9">
            <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate"><i class="icon-ok bigger-110"></i>Update</button>
		</div>
	</div>
    
</form>    
 			</div>		<br clear="all"/>
		</div>
			
	</div>
	<br clear="all" />	
<?php include "includes/footer.php" ?>
</body>
		<script type="text/javascript">
			window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
		</script>

		<!-- <![endif]-->

		<!--[if IE]>
<script type="text/javascript">
 window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

		<script type="text/javascript">
			if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
		</script>
		<script src="assets/js/bootstrap.min.js"></script>
		<script src="assets/js/typeahead-bs2.min.js"></script>

		<!-- page specific plugin scripts -->

		<!--[if lte IE 8]>
		  <script src="assets/js/excanvas.min.js"></script>
		<![endif]-->

		<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
		<script src="assets/js/chosen.jquery.min.js"></script>
		<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
		<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
		<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
		<script src="assets/js/date-time/moment.min.js"></script>
		<script src="assets/js/date-time/daterangepicker.min.js"></script>
		<script src="assets/js/bootstrap-colorpicker.min.js"></script>
		<script src="assets/js/jquery.knob.min.js"></script>
		<script src="assets/js/jquery.autosize.min.js"></script>
		<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
		<script src="assets/js/jquery.maskedinput.min.js"></script>
		<script src="assets/js/bootstrap-tag.min.js"></script>

		<!-- ace scripts -->

		<script src="assets/js/ace-elements.min.js"></script>
		<script src="assets/js/ace.min.js"></script>

		<!-- inline scripts related to this page -->

		<script type="text/javascript">
			jQuery(function($) {
							
				/*$(document).on('keyup', '#br_requirement', function(e){
					var msgSpan = $(this).parents('li').find('#Charcount');
					var length = $(this).val().length;
					var msg =4000 - length;
					msgSpan.empty().html(msg);
				});*/
							
				$('#id-disable-check').on('click', function() {
					var inp = $('#form-input-readonly').get(0);
					if(inp.hasAttribute('disabled')) {
						inp.setAttribute('readonly' , 'true');
						inp.removeAttribute('disabled');
						inp.value="This text field is readonly!";
					}
					else {
						inp.setAttribute('disabled' , 'disabled');
						inp.removeAttribute('readonly');
						inp.value="This text field is disabled!";
					}
				});
			
			
				$(".chosen-select").chosen(); 
				$('#chosen-multiple-style').on('click', function(e){
					var target = $(e.target).find('input[type=radio]');
					var which = parseInt(target.val());
					if(which == 2) $('#form-field-select-4').addClass('tag-input-style');
					 else $('#form-field-select-4').removeClass('tag-input-style');
				});
			
			
				$('[data-rel=tooltip]').tooltip({container:'body'});
				$('[data-rel=popover]').popover({container:'body'});
				
				$('textarea[class*=autosize]').autosize({append: "\n"});
				$('textarea.limited').inputlimiter({
					remText: '%n character%s remaining...',
					limitText: 'max allowed : %n.'
				});
			
				$.mask.definitions['~']='[+-]';
				$('.input-mask-date').mask('99/99/9999');
				$('.input-mask-phone').mask('(999) 999-9999');
				$('.input-mask-eyescript').mask('~9.99 ~9.99 999');
				$(".input-mask-product").mask("a*-999-a999",{placeholder:" ",completed:function(){alert("You typed the following: "+this.val());}});
			
			
			
				$( "#input-size-slider" ).css('width','200px').slider({
					value:1,
					range: "min",
					min: 1,
					max: 8,
					step: 1,
					slide: function( event, ui ) {
						var sizing = ['', 'input-sm', 'input-lg', 'input-mini', 'input-small', 'input-medium', 'input-large', 'input-xlarge', 'input-xxlarge'];
						var val = parseInt(ui.value);
						$('#form-field-4').attr('class', sizing[val]).val('.'+sizing[val]);
					}
				});
			
				$( "#input-span-slider" ).slider({
					value:1,
					range: "min",
					min: 1,
					max: 12,
					step: 1,
					slide: function( event, ui ) {
						var val = parseInt(ui.value);
						$('#form-field-5').attr('class', 'col-xs-'+val).val('.col-xs-'+val);
					}
				});
				
				
				$( "#slider-range" ).css('height','200px').slider({
					orientation: "vertical",
					range: true,
					min: 0,
					max: 100,
					values: [ 17, 67 ],
					slide: function( event, ui ) {
						var val = ui.values[$(ui.handle).index()-1]+"";
			
						if(! ui.handle.firstChild ) {
							$(ui.handle).append("<div class='tooltip right in' style='display:none;left:16px;top:-6px;'><div class='tooltip-arrow'></div><div class='tooltip-inner'></div></div>");
						}
						$(ui.handle.firstChild).show().children().eq(1).text(val);
					}
				}).find('a').on('blur', function(){
					$(this.firstChild).hide();
				});
				
				$( "#slider-range-max" ).slider({
					range: "max",
					min: 1,
					max: 10,
					value: 2
				});
				
				$( "#eq > span" ).css({width:'90%', 'float':'left', margin:'15px'}).each(function() {
					// read initial values from markup and remove that
					var value = parseInt( $( this ).text(), 10 );
					$( this ).empty().slider({
						value: value,
						range: "min",
						animate: true
						
					});
				});
			
				
				$('#id-input-file-1 , #id-input-file-2').ace_file_input({
					no_file:'No File ...',
					btn_choose:'Choose',
					btn_change:'Change',
					droppable:false,
					onchange:null,
					thumbnail:false //| true | large
					//whitelist:'gif|png|jpg|jpeg'
					//blacklist:'exe|php'
					//onchange:''
					//
				});
				
				$('#id-input-file-3').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'small'//large | fit
					//,icon_remove:null//set null, to hide remove/reset button
					/**,before_change:function(files, dropped) {
						//Check an example below
						//or examples/file-upload.html
						return true;
					}*/
					/**,before_remove : function() {
						return true;
					}*/
					,
					preview_error : function(filename, error_code) {
						//name of the file that failed
						//error_code values
						//1 = 'FILE_LOAD_FAILED',
						//2 = 'IMAGE_LOAD_FAILED',
						//3 = 'THUMBNAIL_FAILED'
						//alert(error_code);
					}
			
				}).on('change', function(){
					//console.log($(this).data('ace_input_files'));
					//console.log($(this).data('ace_input_method'));
				});
				
			
				//dynamically change allowed formats by changing before_change callback function
				$('#id-file-format').removeAttr('checked').on('change', function() {
					var before_change
					var btn_choose
					var no_icon
					if(this.checked) {
						btn_choose = "Drop images here or click to choose";
						no_icon = "icon-picture";
						before_change = function(files, dropped) {
							var allowed_files = [];
							for(var i = 0 ; i < files.length; i++) {
								var file = files[i];
								if(typeof file === "string") {
									//IE8 and browsers that don't support File Object
									if(! (/\.(jpe?g|png|gif|bmp)$/i).test(file) ) return false;
								}
								else {
									var type = $.trim(file.type);
									if( ( type.length > 0 && ! (/^image\/(jpe?g|png|gif|bmp)$/i).test(type) )
											|| ( type.length == 0 && ! (/\.(jpe?g|png|gif|bmp)$/i).test(file.name) )//for android's default browser which gives an empty string for file.type
										) continue;//not an image so don't keep this file
								}
								
								allowed_files.push(file);
							}
							if(allowed_files.length == 0) return false;
			
							return allowed_files;
						}
					}
					else {
						btn_choose = "Drop files here or click to choose";
						no_icon = "icon-cloud-upload";
						before_change = function(files, dropped) {
							return files;
						}
					}
					var file_input = $('#id-input-file-3');
					file_input.ace_file_input('update_settings', {'before_change':before_change, 'btn_choose': btn_choose, 'no_icon':no_icon})
					file_input.ace_file_input('reset_input');
				});
			
			
			
			
				$('#spinner1').ace_spinner({value:0,min:0,max:200,step:10, btn_up_class:'btn-info' , btn_down_class:'btn-info'})
				.on('change', function(){
					//alert(this.value)
				});
				$('#spinner2').ace_spinner({value:0,min:0,max:10000,step:100, touch_spinner: true, icon_up:'icon-caret-up', icon_down:'icon-caret-down'});
				$('#spinner3').ace_spinner({value:0,min:-100,max:100,step:10, on_sides: true, icon_up:'icon-plus smaller-75', icon_down:'icon-minus smaller-75', btn_up_class:'btn-success' , btn_down_class:'btn-danger'});
			
			
				
				$('.date-picker').datepicker({autoclose:true}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				$('input[name=date-range-picker]').daterangepicker().prev().on(ace.click_event, function(){
					$(this).next().focus();
				});
				
				$('#timepicker1').timepicker({
					minuteStep: 1,
					showSeconds: true,
					showMeridian: false
				}).next().on(ace.click_event, function(){
					$(this).prev().focus();
				});
				
				$('#colorpicker1').colorpicker();
				$('#simple-colorpicker-1').ace_colorpicker();
			
				
				$(".knob").knob();
				
				
				//we could just set the data-provide="tag" of the element inside HTML, but IE8 fails!
				var tag_input = $('#form-field-tags');
				if(! ( /msie\s*(8|7|6)/.test(navigator.userAgent.toLowerCase())) ) 
				{
					tag_input.tag(
					  {
						placeholder:tag_input.attr('placeholder'),
						//enable typeahead by specifying the source array
						source: ace.variable_US_STATES,//defined in ace.js >> ace.enable_search_ahead
					  }
					);
				}
				else {
					//display a textarea for old IE, because it doesn't support this plugin or another one I tried!
					tag_input.after('<textarea id="'+tag_input.attr('id')+'" name="'+tag_input.attr('name')+'" rows="3">'+tag_input.val()+'</textarea>').remove();
					//$('#form-field-tags').autosize({append: "\n"});
				}
				
				
				
			
				/////////
				$('#modal-form input[type=file]').ace_file_input({
					style:'well',
					btn_choose:'Drop files here or click to choose',
					btn_change:null,
					no_icon:'icon-cloud-upload',
					droppable:true,
					thumbnail:'large'
				})
				
				//chosen plugin inside a modal will have a zero width because the select element is originally hidden
				//and its width cannot be determined.
				//so we set the width after modal is show
				$('#modal-form').on('shown.bs.modal', function () {
					$(this).find('.chosen-container').each(function(){
						$(this).find('a:first-child').css('width' , '210px');
						$(this).find('.chosen-drop').css('width' , '210px');
						$(this).find('.chosen-search input').css('width' , '200px');
					});
				})
				/**
				//or you can activate the chosen plugin after modal is shown
				//this way select element becomes visible with dimensions and chosen works as expected
				$('#modal-form').on('shown', function () {
					$(this).find('.modal-chosen').chosen();
				})
				*/
			
			});
		</script>
	</body>
</html>