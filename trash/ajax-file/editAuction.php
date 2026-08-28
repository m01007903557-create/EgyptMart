<?php
ob_start();
session_start();
include "../common.php";

$auc_id=$_POST['id'];

//$sql="select * from auction,product_category,measurement_unit where auc_pc_id=pc_id and br_estimate_qty_unit=mu_id and br_id='".$br_id."'";
$sql="select * from auction,product_category,user,business_profile where auc_pc_id=pc_id and auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_id='".$auc_id."'";
//$sql="select * from buy_requirement,measurement_unit where br_estimate_qty_unit=mu_id and br_id='".$br_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

?>
<style>
.br_label
{
	text-align:right;
	font-weight:bold;
	vertical-align:top;
}
</style>
<script language="javascript" type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript" src="datepicker/date.js"></script>
<script type="text/javascript" src="datepicker/jquery.datePicker.js"></script>
<link rel="stylesheet" type="text/css" media="screen" href="datepicker/datePicker.css">
<link rel="stylesheet" type="text/css" media="screen" href="datepicker/demo.css">
<script type="text/javascript" charset="utf-8">
$(function()
{
	$('#auc_publish_date').datePicker().val(new Date().asString()).trigger('change');
	$('#auc_docSaleStart_date').datePicker().val(new Date().asString()).trigger('change');
	$('#auc_docSaleEnd_date').datePicker().val(new Date().asString()).trigger('change');
	$('#auc_docSubmitBefore_date').datePicker().val(new Date().asString()).trigger('change');
	$('#auc_due_date').datePicker().val(new Date().asString()).trigger('change');
});
</script>
<script type="text/javascript">
function showCategory()
{
	var pc_id=document.getElementById('mcat_id').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	}); 
}
function showSubcat(id)
{
//	alert(id);
//	var pc_id=document.getElementById('pc_id').value;
	$.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#auc_pc_id').html(data); }); 
}
function updateAuction()
{
	
	var auc_id=document.getElementById('auc_id');
	var mcat_id=document.getElementById('mcat_id');
	var pc_id=document.getElementById('pc_id');
	var auc_pc_id=document.getElementById('auc_pc_id');
	var auc_heading=document.getElementById('auc_heading');
	
	var auc_value=document.getElementById('auc_value');
	var auc_currency=document.getElementById('auc_currency');
	
	var auc_notice_type=document.getElementById('auc_notice_type');
	var auc_qty=document.getElementById('auc_qty');
	var auc_qty_mu_id=document.getElementById('auc_qty_mu_id');
	var auc_emd=document.getElementById('auc_emd');
	var auc_document_fees=document.getElementById('auc_document_fees');
	var auc_document_fees_currency=document.getElementById('auc_document_fees_currency');
	var auc_project_period=document.getElementById('auc_project_period');
	var auc_products=document.getElementById('auc_products');
	
	var auc_publish_date=document.getElementById('auc_publish_date');
	var auc_docSaleStart_date=document.getElementById('auc_docSaleStart_date');
	var auc_docSaleEnd_date=document.getElementById('auc_docSaleEnd_date');
	var auc_docSubmitBefore_date=document.getElementById('auc_docSubmitBefore_date');
	var auc_due_date=document.getElementById('auc_due_date');	
	
	var auc_prequalification_criteria=document.getElementById('auc_prequalification_criteria');
	var auc_details=document.getElementById('auc_details');
	
	var auc_preferred_location=$('input:radio[name=auc_preferred_location]:checked').val();
	
	var message="";
    var valid=true;
	
	if(mcat_id.value=='')
	{
		alert("Kindly select Main Category.");
		mcat_id.focus();
		valid=false;
	}
	else if(pc_id.value=='')
	{
		alert("Kindly select Category.");
		pc_id.focus();
		valid=false;
	}
	else if(auc_pc_id.value=='')
	{
		alert("Kindly select Sub-Category.");
		auc_pc_id.focus();
		valid=false;
	}
	else if(auc_heading.value=='')
	{
		alert("Kindly enter Auction Heading.");
		auc_heading.focus();
		valid=false;
	}
	else if(auc_value.value!='' && isNaN(auc_value.value))
	{
		alert("Kindly enter valid Auction value.");
		auc_value.focus();
		valid=false;
	}
	else if(auc_value.value!='' && auc_currency.value=='')
	{
		alert("Kindly select currency for Auction Value.");
		auc_currency.focus();
		valid=false;
	}
	else if(auc_notice_type.value=='')
	{
		alert("Kindly enter Notice Type.");
		auc_notice_type.focus();
		valid=false;
	}
	else if(auc_qty.value!='' && isNaN(auc_qty.value))
	{
		alert("Kindly enter valid Quantity.");
		auc_qty.focus();
		valid=false;
	}
	else if(auc_qty.value!='' && auc_qty_mu_id.value=='')
	{
		alert("Kindly select Quantity Unit.");
		auc_qty_mu_id.focus();
		valid=false;
	}
	else if(auc_document_fees.value=='')
	{
		alert("Kindly enter Document Fees.");
		auc_document_fees.focus();
		valid=false;
	}
	else if(auc_document_fees.value!='' && isNaN(auc_document_fees.value))
	{
		alert("Kindly enter valid Document Fees.");
		auc_document_fees.focus();
		valid=false;
	}
	else if(auc_document_fees_currency.value=='')
	{
		alert("Kindly select currency for Document Fees.");
		auc_document_fees_currency.focus();
		valid=false;
	}
	else if(auc_prequalification_criteria.value == '')
	{
		alert("Kindly describe Pre-qualification Criteria.");
		auc_prequalification_criteria.focus();
		valid=false;
	}
	else if(auc_details.value == '')
	{
		alert("Kindly describe Auction details.");
		auc_details.focus();
		valid=false;
	}
	else
	{
		
		$.post("ajax-file/updAuction.php",{auc_id:auc_id.value,auc_pc_id:auc_pc_id.value,auc_heading:auc_heading.value,auc_value:auc_value.value, auc_notice_type:auc_notice_type.value,auc_qty:auc_qty.value,auc_qty_mu_id:auc_qty_mu_id.value,auc_emd:auc_emd.value, auc_document_fees:auc_document_fees.value, auc_document_fees_currency:auc_document_fees_currency.value, auc_project_period:auc_project_period.value, auc_products:auc_products.value, auc_publish_date:auc_publish_date.value,auc_docSaleStart_date:auc_docSaleStart_date.value,auc_docSaleEnd_date:auc_docSaleEnd_date.value,auc_docSubmitBefore_date:auc_docSubmitBefore_date.value, auc_due_date:auc_due_date.value, auc_currency:auc_currency.value, auc_prequalification_criteria:auc_prequalification_criteria.value, auc_details:auc_details.value, auc_preferred_location:auc_preferred_location},    function(data){

			data=data.trim();
			dt=data.split("|")
			if(dt[0]=='0')
			{
				alert(dt[1]);
			}
			else
			{
				alert("Auction updated successfully.");
				detailAuction(auc_id.value);
			}			
		});
	}
}

</script>
<div class="mctr_buyreq mfl">
<!--#######Responses Main Start-->
<div class="mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div>
	<a class="mctr_manage" style="text-decoration:none;"><?php echo $row->auc_heading; ?></a>
	<span style="float:right;color:#929292;font-size:16px;padding-right:87px">
		<a href="javascript:goback()" style="font-size:12px;padding-top:4px;font-weight:bold">&laquo; Back</a>
	</span>
</div>
<script type="text/javascript">
//alert(new Date('2015-01-22').toString());
</script>
<!--Responses Main End-->



<script type="text/javascript">
$(function()
{
	$('#auc_publish_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->auc_publish_date)); ?>').trigger('change');
	$('#auc_docSaleStart_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->auc_docSaleStart_date)); ?>').trigger('change');
	$('#auc_docSaleEnd_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->auc_docSaleEnd_date)); ?>').trigger('change');
	$('#auc_docSubmitBefore_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->auc_docSubmitBefore_date)); ?>').trigger('change');
	$('#auc_due_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->auc_due_date)); ?>').trigger('change');
});
</script>

<!--Start Suppliers HTML-->
<div class="to_bd"> Auction Details</div>
<div class="to_ct">
	<div class="to_lp" style="min-height:53px;width:80%;">

	
    <table>
    	<input type="hidden" id="auc_id" name="auc_id" value="<?php echo $row->auc_id; ?>" />
        <tr><td class="br_label" style="vertical-align:middle;">Auction Heading: </td><td><input type="text" id="auc_heading" name="auc_heading" value="<?php echo $row->auc_heading; ?>" style="width:400px;padding:5px;"/></td></tr>
	    <tr>
        <?php
	$mcat_sql="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row->auc_pc_id."' and pc_status='1') and pc_status='1'";
	$mcat_res=mysqli_query($con, $mcat_sql);
	$mcat_row=mysqli_fetch_object($mcat_res);
	?>
    
        <?php
	        $sql_mcat="select * from product_category where pc_parent_id='0' and pc_status='1'";
			$res_mcat=mysqli_query($con, $sql_mcat);
		?>
        <td class="br_label" style="vertical-align:middle;">Main Category: </td><td>
		<select id="mcat_id" name="mcat_id" onChange="showCategory();" class="a_f">
            <?php
			while($row_mcat=mysqli_fetch_object($res_mcat))
			{	?>
			<option value="<?php echo $row_mcat->pc_id; ?>" <?php if($row_mcat->pc_id==$mcat_row->pc_parent_id){ ?> selected="selected"<?php } ?>><?php echo $row_mcat->pc_name; ?></option>
		<?php	}	?>
	    </select>
        </td></tr>
        <tr>
        <?php
	        $sql_pc="select * from product_category where pc_parent_id!='0' and pc_parent_id='".$mcat_row->pc_parent_id."' and pc_status='1'";
			$res_pc=mysqli_query($con, $sql_pc);
		?>
        <td class="br_label" style="vertical-align:middle;">Category: </td><td>
		<select id="pc_id" name="pc_id" onChange="showSubcat(this.value);" class="a_f">
            <?php
			while($row_pc=mysqli_fetch_object($res_pc))
			{	?>
			<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$mcat_row->pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
		<?php	}	?>
	    </select>
        </td></tr>
    	<tr><td class="br_label" style="vertical-align:middle;">Sub-Category: </td><td>
		<?php
			$sql_spc="select * from product_category where pc_parent_id=(select pc_parent_id from product_category where pc_id='".$row->auc_pc_id."') and pc_status='1'";
			$res_spc=mysqli_query($con, $sql_spc);
			?>
            <select id="auc_pc_id" name="auc_pc_id" class="a_f">
            	<option value=""> - Select Sub-Category - </option>
            <?php
			while($row_spc=mysqli_fetch_object($res_spc))
			{	?>
				<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$row->auc_pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
		<?php	}	?>
	        </select>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Auction Value: </td>
        <td><input type="text" id="auc_value" name="auc_value" value="<?php echo $row->auc_value; ?>" style="width:250px;padding:5px;"/>
        <select size="1" name="auc_currency" id="auc_currency" class="a_f s_u">
				<option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($currencyrow=mysqli_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if($row->auc_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } else if($auc_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Notice Type: </td><td><input type="text" id="auc_notice_type" name="auc_notice_type" value="<?php echo $row->auc_notice_type; ?>" style="width:200px;padding:5px;"/></td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Quantity: </td><td>
        <input type="text" id="auc_qty" name="auc_qty" value="<?php echo $row->auc_qty; ?>" style="width:250px;padding:5px;"/>
        <select size="1" name="auc_qty_mu_id" id="auc_qty_mu_id" class="a_f s_u">
        <option value="">-Select Unit-</option>
                <?php                
				$res_mu=mysqli_query($con, "select * from measurement_unit where mu_status='1'");
				while($row_mu=mysqli_fetch_object($res_mu)){
				?>
                <option value="<?php echo $row_mu->mu_id; ?>" <?php if($row_mu->mu_id==$row->auc_qty_mu_id){ ?> selected="selected"<?php } ?> ><?php echo $row_mu->mu_name;?></option>
				<?php } ?>
            </select>        
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">EMD: </td><td><input type="text" id="auc_emd" name="auc_emd" value="<?php echo $row->auc_emd; ?>" style="width:200px;padding:5px;"/></td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Fees: </td><td>
        <input type="text" id="auc_document_fees" name="auc_document_fees" value="<?php echo $row->auc_document_fees; ?>" style="width:250px;padding:5px;"/>
        <select size="1" name="auc_document_fees_currency" id="auc_document_fees_currency" class="a_f s_u">
				<option value="">-Select Currency-</option>
                <?php                
				$df_currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($df_currencyrow=mysqli_fetch_object($df_currencysql)){
				?>
	            <option value="<?php echo $df_currencyrow->cn_id;?>" <?php if($row->auc_document_fees_currency==$df_currencyrow->cn_id){ ?> selected="selected" <?php } else if(user_info($uid,'country')== $df_currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $df_currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Project Period: </td><td><input type="text" id="auc_project_period" name="auc_project_period" value="<?php echo $row->auc_project_period; ?>" style="width:255px;padding:5px;"/></td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Products: </td><td><input type="text" id="auc_products" name="auc_products" value="<?php echo $row->auc_products; ?>" style="width:400px;padding:5px;"/></td></tr>
        
        <tr><td class="br_label" style="vertical-align:middle;">Auction Publish Date: </td><td>
		<input name="auc_publish_date" id="auc_publish_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Sale Starts: </td><td>
		<input name="auc_docSaleStart_date" id="auc_docSaleStart_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Sale Ends: </td><td>
		<input name="auc_docSaleEnd_date" id="auc_docSaleEnd_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Submit Before: </td><td>
		<input name="auc_docSubmitBefore_date" id="auc_docSubmitBefore_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Auction Due Date: </td><td>
		<input name="auc_due_date" id="auc_due_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        
    	<tr><td class="br_label">Pre-qualification Criteria: </td><td>
		<textarea id="auc_prequalification_criteria" name="auc_prequalification_criteria" style="width:400px;"><?php echo stripslashes($row->auc_prequalification_criteria); ?></textarea>
        </td></tr>
        <tr><td class="br_label">Details: </td><td>
		<textarea id="auc_details" name="auc_details" style="width:400px;"><?php echo stripslashes($row->auc_details); ?></textarea>
        </td></tr>
        <tr><td></td><td></td></tr>
	    
        <tr style="padding-top:2px;"><td class="br_label">Location Preferences: </td><td>
        <input type="radio" id="auc_preferred_location_1" name="auc_preferred_location" value="abroad" <?php if($row->auc_preferred_location=='abroad'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Abroad Only</label>
        &nbsp;&nbsp;
        <input type="radio" id="auc_preferred_location_2" name="auc_preferred_location" value="any" <?php if($row->auc_preferred_location=='any'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Abroad + Domestic</label>
        &nbsp;&nbsp;
        <input type="radio" id="auc_preferred_location_3" name="auc_preferred_location" value="domestic" <?php if($row->auc_preferred_location=='domestic'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Domestic Only</label>
        &nbsp;&nbsp;
        <input type="radio" id="auc_preferred_location_4" name="auc_preferred_location" value="my_city" <?php if($row->auc_preferred_location=='my_city'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">My City Only</label>
		
        </td></tr>
        
        
        <tr><td colspan="2" style="text-align:center"><input name="btnUpdate" id="btnUpdate" value="Update Auction" class="saps mt5" type="button" onclick="updateAuction();"/></td></tr>
    </table>
	
	


</div>

<p class="to_rp1"><b>Posted on:</b> <?php echo date("d M, Y",strtotime($row->br_posting_date)); ?><br></p><div style="clear:both;"></div>
</div>
<?php if($row->br_approval_status=='0'){ ?>
<br>
<div id="NotiFicationDivSuccmsg"></div>
<div class="NotiFicationDiv" id="NotiFicationDiv"><h1>Your Buy Requirement is under review by our system</h1>After approval, your Buy Requirement will be Live and we will send you a confirmation mail.</div>
<?php	}	?>
<!-- content part end here -->

</div>