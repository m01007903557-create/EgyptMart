<?php 
include "../common.php";


check_user_login();
class editBuyReq
{

	var $msg;
	var $br_id;
	var $pc_id;
	var $br_pc_id;
	var $br_pd_name;
	var $br_requirement;
	var $br_estimate_qty;
	var $br_estimate_qty_unit;
	var $br_preferred_supplier_location;

	var $br_apprx_order_value;
	var $br_apprx_order_currency;
	var $br_description;
	var $br_website;
	var $br_need_quote_for;
	var $br_purchase_time;

	var $br_need_for;
	var $br_requirement_frequency;
	var $br_pic;
	

	function __construct($br_id){
		$this->br_id=$br_id;
	}
	function detailsObj(){
		global $con;
		$sql="select * from buy_requirement,product_category_arabyos,measurement_unit where br_pc_id=pc_id and br_estimate_qty_unit=mu_id and md5(br_id)='".$this->br_id."'";
		$res=mysqli_query($con, $sql);
		return mysqli_fetch_object($res);
	}
	function valid()
	{
		global $con;
		$valid=true;
		
		$filename = $_FILES['so_pic']['name'];
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		
		if($this->pc_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
			$valid=false;
		}
		else if($this->br_pc_id=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
			$valid=false;
		}
		else if($this->br_pd_name=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Products / Services.</div>';
			$valid=false;
		}
		else if($this->br_requirement.value=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please describe Buying Requirements.</div>';
			$valid=false;
		}
		else if(strlen($this->br_requirement)<50)
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Buying Requirements must be atleast 50 characters length.</div>';
			$valid=false;
		}
		else if($this->br_estimate_qty.value=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Estimated Quantity.</div>';
			$valid=false;
		}
		else if($this->br_estimate_qty_unit.value=='')
		{
			$this->msg= '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Estimated Quantity Unit.</div>';
			$valid=false;
		}
		
		return $valid;
	}
	
	function update() 
	{
		global $con;
		if($_FILES["br_pic"]["name"] != '')
		{
			if ($_FILES["br_pic"]["error"] > 0)
			{
				$msg = "Return Code: " . $_FILES["br_pic"]["error"] . "<br />";
			}
			else
			{	
				$sqlImg="select * from buy_requirement where br_id='".$this->br_id."'";
				$resImg=mysqli_query($con, $sqlImg);
				$rowImg=mysqli_fetch_object($resImg);
				
				$pathLrg="../upload/buy_requirement/".$rowImg->br_pic;	
				$pathThumb="../upload/buy_requirement/thumb/".$rowImg->br_pic;
	
				if(is_file($pathLrg))
				{
					unlink($pathLrg);
				}
				
				if(is_file($pathThumb))
				{
					unlink($pathThumb);
				}	
				
				/*$imgSImage = new SimpleImage();			
				$imgSImage->load($_FILES['adv_img']['tmp_name']);			
				$imgSImage->resize($this->adv_imagewidth,$this->adv_imageheight);
				
				
				$imgSImage->save("../upload/advertisement/".$this->adv_img);*/	
				
				$this->br_pic="br-".rand(0,9999).trim(addslashes($_FILES['br_pic']['name']));	
				$ds = move_uploaded_file($_FILES["br_pic"]["tmp_name"], "../upload/buy_requirement/".$this->br_pic) or die('error');
				
				/** Thumb image creation **/
				$imgSImage = new SimpleImage();			
				$imgSImage->load("../upload/buy_requirement/".$this->br_pic);			
				$imgSImage->resize(100,80);//width,height
				
				$imgSImage->save("../upload/buy_requirement/thumb/".$this->br_pic);
				/** Thumb image creation **/
				
				/*if($ds)
				{*/	
										
					$sql="update buy_requirement
						set
						br_pc_id='".$this->br_pc_id."',
						br_pd_name='".$this->br_pd_name."',
						br_requirement='".$this->br_requirement."',
						br_estimate_qty='".$this->br_estimate_qty."',
						br_estimate_qty_unit='".$this->br_estimate_qty_unit."',
						br_preferred_supplier_location='".$this->br_preferred_supplier_location."',
						br_pic='".$this->br_pic."',
						br_apprx_order_value='".$this->br_apprx_order_value."',
						br_apprx_order_currency='".$this->br_apprx_order_currency."',
						br_description='".$this->br_description."',
						br_website='".$this->br_website."',
						br_need_quote_for='".$this->br_need_quote_for."',
						br_purchase_time='".$this->br_purchase_time."',

						br_need_for='".$this->br_need_for."',
						br_requirement_frequency='".$this->br_requirement_frequency."',
						br_updated_date=now()
					where
						br_id='".$this->br_id."'";						
			
					mysqli_query($con, $sql) or die(mysql_error());
														
					$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';	
				/*}*/
			}		
		}
		else
		{
			$sql="update buy_requirement
				set
					br_pc_id='".$this->br_pc_id."',
					br_pd_name='".$this->br_pd_name."',
					br_requirement='".$this->br_requirement."',
					br_estimate_qty='".$this->br_estimate_qty."',
					br_estimate_qty_unit='".$this->br_estimate_qty_unit."',
					br_preferred_supplier_location='".$this->br_preferred_supplier_location."',
					br_apprx_order_value='".$this->br_apprx_order_value."',
					br_apprx_order_currency='".$this->br_apprx_order_currency."',
					br_description='".$this->br_description."',
					br_website='".$this->br_website."',
					br_need_quote_for='".$this->br_need_quote_for."',
					br_purchase_time='".$this->br_purchase_time."',
					br_preferred_supplier_location='".$this->br_preferred_supplier_location."',
					br_need_for='".$this->br_need_for."',
					br_requirement_frequency='".$this->br_requirement_frequency."',
					br_updated_date=now()
				where
					br_id='".$this->br_id."'";
				
			mysqli_query($con, $sql);
		
			$this->msg='<div class="alert alert-success"><i class="icon-ok"></i> Buy Requirement updated successfully.</div>';
		}
	}	
}

if(isset($_SESSION['msg'])){ $msg=$_SESSION['msg']; unset($_SESSION['msg']); }else{ $msg=""; }

$ob=new editBuyReq($_GET['token']);
$row=$ob->detailsObj();

$bsql="select bu.* from buy_requirement r JOIN business_profile bu ON r.br_u_id = bu.bnsprof_uid where md5(r.br_id) ='".$_GET['token']."'";
$bres=mysqli_query($con, $bsql);
$brow=mysqli_fetch_object($bres);

if(isset($_POST['btnUpdate'])){
	
	
	$ob->br_id=addslashes(trim($_POST['br_id']));	
	$ob->pc_id=addslashes(trim($_POST['pc_id']));
	$ob->br_pc_id=addslashes(trim($_POST['br_pc_id']));
	$ob->br_pd_name=addslashes(trim($_POST['br_pd_name']));
	$ob->br_requirement=addslashes(trim($_POST['br_requirement']));
	$ob->br_estimate_qty=addslashes(trim($_POST['br_estimate_qty']));
	$ob->br_estimate_qty_unit=addslashes(trim($_POST['br_estimate_qty_unit']));
	$ob->br_preferred_supplier_location=addslashes(trim($_POST['br_preferred_supplier_location']));
	
	
	$ob->br_apprx_order_value=addslashes(trim($_POST['br_apprx_order_value']));
	$ob->br_apprx_order_currency=addslashes(trim($_POST['br_apprx_order_currency']));
	$ob->br_description=addslashes(trim($_POST['br_description']));
	$ob->br_website=addslashes(trim($_POST['br_website']));
	$ob->br_need_quote_for=addslashes(trim($_POST['br_need_quote_for']));
	$ob->br_purchase_time=addslashes(trim($_POST['br_purchase_time']));
	$ob->br_need_for=addslashes(trim($_POST['br_need_for']));
	$ob->br_requirement_frequency=addslashes(trim($_POST['br_requirement_frequency']));
	$ob->br_pic=trim($_FILES['br_pic']['name']);
	
	
		
	if($ob->valid()){
		$ob->update();
	}
	$_SESSION['msg']=$ob->msg;
	header('Location: ../post-buy-req-email.php?admn_br_id='.$ob->br_id);
	//header("location:buyreq-edit.php?token=".md5($ob->br_id));
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
	$.get("showSubcat.php",{q:pc_id},	function(data){	$('#br_pc_id').html(data); }); 
}
function validForm()
{
	var mcat_id=document.getElementById('mcat_id');
	var pc_id=document.getElementById('pc_id');
	var br_pc_id=document.getElementById('br_pc_id');
	var br_pd_name=document.getElementById('br_pd_name');
	var br_requirement=document.getElementById('br_requirement');
	var br_estimate_qty=document.getElementById('br_estimate_qty');
	var br_estimate_qty_unit=document.getElementById('br_estimate_qty_unit');
	

	
	var message="";
	var valid=true;
	
	if(mcat_id.value=='')
	{
		message="Please select the Main Category.";
		mcat_id.focus();
		valid=false;
	}
	else if(pc_id.value=='' || pc_id.value == null)
	{
		message='Please select Category.';
		pc_id.focus();
		valid=false;
	}
	else if(br_pc_id.value=='' || br_pc_id.value == null)
	{
		message='Please select Sub-Category.';
		so_pc_id.focus();
		valid=false;
	}
	else if(br_pd_name.value=='' || br_pd_name.value == null)
	{
		message='Please enter Products / Services.';
		br_pd_name.focus();
		valid=false;
	}
	else if(!isNaN(br_pd_name.value))
	{
		message='Please enter valid Products / Services Name.';
		br_pd_name.focus();
		valid=false;
	}
	else if(br_requirement.value=='' || br_requirement.value == null)
	{
		message='Please describe Buying Requirements.';
		br_requirement.focus();
		valid=false;
	}
	else if(!isNaN(br_requirement.value))
	{
		message='Please describe valid Buying Requirements.';
		br_requirement.focus();
		valid=false;
	}
	else if(br_requirement.value.length<50)
	{
		message='Buying Requirements must be atleast 50 characters length.';
		br_requirement.focus();
		valid=false;
	}
	else if(br_estimate_qty.value=='' || br_estimate_qty.value == null)
	{
		message='Please enter Estimated Quantity.';
		br_estimate_qty.focus();
		valid=false;
	}
	else if(isNaN(br_estimate_qty.value))
	{

		message='Please enter valid Estimated Quantity.';
		br_estimate_qty.focus();
		valid=false;
	}
	else if(br_estimate_qty_unit.value=='' || br_estimate_qty_unit.value == null)
	{
			
		message='Please select Estimated Quantity measurement Unit.';
		br_estimate_qty_unit.focus();
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
				<a href="buyreq-view.php">Manage Buy Requirement</a>
			</li>
			<li class="active">Buy Requirement Edit</li>
		</ul><!-- .breadcrumb -->
		<!-- #nav-search -->
	</div>
				
<div class="page-content">
	<div class="page-header">
		<h1>
			Manage Buy Requirement
			<small>
				<i class="icon-double-angle-right"></i>
				Buy Requirement Edit
			</small>
		</h1>
	</div>
	<div class="row">
		<div class="col-xs-12">
<form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validForm();">
	<input type="hidden" id="br_id" name="br_id" value="<?php echo $row->br_id; ?>" />

	<div id="msg"><?php echo $msg;?></div>
	
    
    <div class="form-group">
    <?php
	$mcat_sql="select * from product_category_arabyos where pc_id=(select pc_parent_id from product_category_arabyos  where pc_id='".$row->pc_parent_id."' and pc_status='1') and pc_status='1'";
	$mcat_res=mysqli_query($con, $mcat_sql);
	$mcat_row=mysqli_fetch_object($mcat_res);
	?>
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Main Category:</label>
		<div class="col-sm-9">
        <select id="mcat_id" name="mcat_id" onChange="showCategory();">
        <?php
			$sql_mcat="select * from product_category_arabyos  where pc_parent_id='0' and pc_status='1'";
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
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category_arabyos  c,product_category_arabyos  s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_pc="select * from product_category_arabyos  where pc_parent_id!='0' and pc_parent_id='".$mcat_row->pc_id."'";
//			$sql_pc="select * from product_category_arabyos  where pc_parent_id='0'";
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
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category_arabyos  c,product_category_arabyos  s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_spc="select * from product_category_arabyos  where pc_parent_id=(select pc_parent_id from product_category_arabyos  where pc_id='".$row->br_pc_id."')";
			$res_spc=mysqli_query($con, $sql_spc);
			?>
            <select id="br_pc_id" name="br_pc_id">
            	<option value="0"> - Select Sub-Category - </option>
            <?php
			while($row_spc=mysqli_fetch_object($res_spc))
			{	?>
				<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$row->br_pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
		<?php	}	?>
	        </select>
		</div>
	</div>
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Products / Services:</label>
		<div class="col-sm-9">
        	<input name="br_pd_name" id="br_pd_name" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->br_pd_name; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Requirement in Detail:</label>
		<div class="col-sm-8">
			<textarea id="br_requirement" name="br_requirement" class="col-xs-8 col-sm-8"><?php echo $row->br_requirement; ?></textarea>
            <div class="col-xs-8 col-sm-8"><span style="float:left;color:#C69;font-size:12px">Minimum 50 Characters.</span>
            <!--<span style="float:right;color:#C69"><font id="Charcount" class="c4">4000</font> Characters Remaining.</span>--></div>
		</div>
	</div>

    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Estimated Quantity:</label>
		<div class="col-sm-8">
        	<input name="br_estimate_qty" id="br_estimate_qty" class="col-xs-10 col-sm-5" type="text" value="<?php if($row->br_estimate_qty!='0.00'){	echo $row->br_estimate_qty;	} ?>" />
            &nbsp;
            <select name="br_estimate_qty_unit" id="br_estimate_qty_unit">
			<?php
			
				$sql_mu="select * from measurement_unit where mu_status='1'";
				$res_mu=mysqli_query($con, $sql_mu);
			?>
		    	<option selected="selected" value="">--Select Unit--</option>
		    <?php	
			while($row_mu=mysqli_fetch_object($res_mu)){	?>
				<option value="<?php echo $row_mu->mu_id;?>" <?php if($row->br_estimate_qty_unit==$row_mu->mu_id){ ?> selected="selected"<?php } ?>><?php echo $row_mu->mu_name;	?></option>
			<?php	}	?>      
	</select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Location Prefrences:</label>
        <div class="radio col-sm-8">
			<label><input type="radio" id="br_preferred_supplier_location_1" name="br_preferred_supplier_location" class="ace" value="abroad" <?php if($row->br_preferred_supplier_location=='abroad'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="br_preferred_supplier_location_2" name="br_preferred_supplier_location" class="ace" value="any" <?php if($row->br_preferred_supplier_location=='any'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Abroad + Domestic</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="br_preferred_supplier_location_3" name="br_preferred_supplier_location" class="ace" value="domestic" <?php if($row->br_preferred_supplier_location=='domestic'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Domestic Only</span></label>
			&nbsp;&nbsp;
            <label><input type="radio" id="br_preferred_supplier_location_4" name="br_preferred_supplier_location" class="ace" value="my_city" <?php if($row->br_preferred_supplier_location=='my_city'){ ?> checked="checked"<?php } ?>/><span class="lbl"> My City Only</span></label>
            
        </div>
	</div>
        
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Approximate Order Value:</label>
		<div class="col-sm-8">
        	<input name="br_apprx_order_value" id="br_apprx_order_value" class="col-xs-10 col-sm-5" type="text" value="<?php if($row->br_apprx_order_value!='0.00'){	echo $row->br_apprx_order_value;	} ?>" />
            &nbsp;
            <select name="br_apprx_order_currency" id="br_apprx_order_currency">
                <?php
					$sql_curr="select distinct cn_currency from country where cn_currency!='' and cn_status='1'";
					$res_curr=mysqli_query($con, $sql_curr);
				?>
                	<option selected="selected" value="">--Select Currency--</option>
                <?php	while($row_curr=mysqli_fetch_object($res_curr)){	?>
                    <option value="<?php echo $row_curr->cn_currency; ?>" <?php if($row_curr->cn_currency==$row->br_apprx_order_currency){ ?> selected="selected"<?php } ?> ><?php echo $row_curr->cn_currency; ?></option> 
                <?php	}	?>
                </select>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Describe product application/ usage:</label>
		<div class="col-sm-8">
			<textarea id="br_description" name="br_description" class="col-xs-8 col-sm-8"><?php echo $row->br_description; ?></textarea>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Website:</label>
		<div class="col-sm-8">
        	<input name="br_website" id="br_website" class="col-xs-10 col-sm-5" type="text" value="<?php echo $row->br_website; ?>" />
		</div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Need Quotations:</label>
        <div class="radio col-sm-8">
			<label><input name="br_need_quote_for" id="br_need_quote_for0" class="ace" type="radio" value="To Make Purchase" <?php if($row->br_need_quote_for=='To Make Purchase'){ ?> checked="checked"<?php } ?>/><span class="lbl"> To Make Purchase </span></label>
			<label><input name="br_need_quote_for" id="br_need_quote_for1" class="ace" type="radio" value="To Know Price Only" <?php if($row->br_need_quote_for=='To Know Price Only'){ ?> checked="checked"<?php } ?>><span class="lbl"> To Know Price Only </span></label>
        </div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">How soon want to purchase:</label>
        <div class="radio col-sm-8">
			<label><input type="radio" id="q_timperiod0" name="br_purchase_time" class="ace" value="Immediate" <?php if($row->br_purchase_time=='Immediate'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Immediate</span></label>
			<label><input type="radio" id="q_timperiod1" name="br_purchase_time" class="ace" value="Within 15 Days" <?php if($row->br_purchase_time=='Within 15 Days'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Within 15 Days</span></label>
			<label><input type="radio" id="q_timperiod2" name="br_purchase_time" class="ace" value="Within 1 Month" <?php if($row->br_purchase_time=='Within 1 Month'){ ?> checked="checked"<?php } ?>/><span class="lbl"> Within 1 Month</span></label>
        </div>
	</div>
    
    
    
	<div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Why need this:</label>
        <div class="radio col-sm-8">
			<label><input type="radio" id="br_need_for0" name="br_need_for" class="ace" value="For Reselling" <?php if($row->br_need_for=='For Reselling'){ ?> checked="checked"<?php } ?>/><span class="lbl"> For Reselling</span></label>
			<label><input type="radio" id="br_need_for1" name="br_need_for" class="ace" value="For Your End Use" <?php if($row->br_need_for=='For Your End Use'){ ?> checked="checked"<?php } ?>/><span class="lbl"> For Your End Use</span></label>
			<label><input type="radio" id="br_need_for2" name="br_need_for" class="ace" value="As Raw Material" <?php if($row->br_need_for=='As Raw Material'){ ?> checked="checked"<?php } ?>/><span class="lbl"> As Raw Material</span></label>
            
        </div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Is this your:</label>
        <div class="radio col-sm-8">
			<label><input name="br_requirement_frequency" id="br_requirement_frequency1" class="ace" type="radio" value="One Time Requirement" <?php if($row->br_requirement_frequency=='One Time Requirement'){ ?> checked="checked"<?php } ?>/><span class="lbl"> One Time Requirement </span></label>
			<label><input name="br_requirement_frequency" id="br_requirement_frequency2" class="ace" type="radio" value="Regular Requirement" <?php if($row->br_requirement_frequency=='Regular Requirement'){ ?> checked="checked"<?php } ?>><span class="lbl"> Regular Requirement </span></label>
        </div>
	</div>
    
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Current Image</label>
		<div class="col-sm-9">
			<img src="../upload/buy_requirement/thumb/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image.png";	} ?>"/>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Upload Image</label>
		<div class="col-sm-9">
			<div class="ace-file-input" style="width:400px;"><input name="br_pic" id="id-input-file-2" type="file"></div>
		</div>
	</div>
    <div class="form-group">
		<label class="col-sm-3 control-label no-padding-right" for="form-field-2">Posted By:</label>
		<div class="col-sm-8">
   		   	<label style="padding-top:4px;"><?php echo $brow->bnsprof_compname; ?></label>
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