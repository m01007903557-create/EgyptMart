<?php
ob_start();
session_start();
include "../common.php";

$tnd_id=$_POST['id'];

//$sql="select * from tender,product_category,measurement_unit where tnd_pc_id=pc_id and br_estimate_qty_unit=mu_id and br_id='".$br_id."'";
$sql="select * from tender,product_category,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_id='".$tnd_id."'";
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
	$('#tnd_publish_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_docSaleStart_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_docSaleEnd_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_docSubmitBefore_date').datePicker().val(new Date().asString()).trigger('change');
	$('#tnd_due_date').datePicker().val(new Date().asString()).trigger('change');
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
	$.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#tnd_pc_id').html(data); }); 
}
function updateTender()
{
	
	var tnd_id=document.getElementById('tnd_id');
	var mcat_id=document.getElementById('mcat_id');
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
	else if(tnd_pc_id.value=='')
	{
		alert("Kindly select Sub-Category.");
		tnd_pc_id.focus();
		valid=false;
	}
	else if(tnd_heading.value=='')
	{
		alert("Kindly enter Tender Heading.");
		tnd_heading.focus();
		valid=false;
	}
	else if(tnd_value.value!='' && isNaN(tnd_value.value))
	{
		alert("Kindly enter valid Tender value.");
		tnd_value.focus();
		valid=false;
	}
	else if(tnd_value.value!='' && tnd_currency.value=='')
	{
		alert("Kindly select currency for Tender Value.");
		tnd_currency.focus();
		valid=false;
	}
	else if(tnd_notice_type.value=='')
	{
		alert("Kindly enter Notice Type.");
		tnd_notice_type.focus();
		valid=false;
	}
	else if(tnd_qty.value!='' && isNaN(tnd_qty.value))
	{
		alert("Kindly enter valid Quantity.");
		tnd_qty.focus();
		valid=false;
	}
	else if(tnd_qty.value!='' && tnd_qty_mu_id.value=='')
	{
		alert("Kindly select Quantity Unit.");
		tnd_qty_mu_id.focus();
		valid=false;
	}
	else if(tnd_document_fees.value=='')
	{
		alert("Kindly enter Document Fees.");
		tnd_document_fees.focus();
		valid=false;
	}
	else if(tnd_document_fees.value!='' && isNaN(tnd_document_fees.value))
	{
		alert("Kindly enter valid Document Fees.");
		tnd_document_fees.focus();
		valid=false;
	}
	else if(tnd_document_fees_currency.value=='')
	{
		alert("Kindly select currency for Document Fees.");
		tnd_document_fees_currency.focus();
		valid=false;
	}
	else if(tnd_prequalification_criteria.value == '')
	{
		alert("Kindly describe Pre-qualification Criteria.");
		tnd_prequalification_criteria.focus();
		valid=false;
	}
	else if(tnd_details.value == '')
	{
		alert("Kindly describe Tender details.");
		tnd_details.focus();
		valid=false;
	}
	else
	{
		
		$.post("ajax-file/updTender.php",{tnd_id:tnd_id.value,tnd_pc_id:tnd_pc_id.value,tnd_heading:tnd_heading.value,tnd_value:tnd_value.value, tnd_notice_type:tnd_notice_type.value,tnd_qty:tnd_qty.value,tnd_qty_mu_id:tnd_qty_mu_id.value,tnd_emd:tnd_emd.value, tnd_document_fees:tnd_document_fees.value, tnd_document_fees_currency:tnd_document_fees_currency.value, tnd_project_period:tnd_project_period.value, tnd_products:tnd_products.value, tnd_publish_date:tnd_publish_date.value,tnd_docSaleStart_date:tnd_docSaleStart_date.value,tnd_docSaleEnd_date:tnd_docSaleEnd_date.value,tnd_docSubmitBefore_date:tnd_docSubmitBefore_date.value, tnd_due_date:tnd_due_date.value, tnd_currency:tnd_currency.value, tnd_prequalification_criteria:tnd_prequalification_criteria.value, tnd_details:tnd_details.value, tnd_preferred_location:tnd_preferred_location},    function(data){

			data=data.trim();
			dt=data.split("|")
			if(dt[0]=='0')
			{
				alert(dt[1]);
			}
			else
			{
				alert("Tender updated successfully.");
				detailTender(tnd_id.value);
			}			
		});
	}
}

</script>
<div class="mctr_buyreq mfl">
<!--#######Responses Main Start-->
<div class="mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div>
	<a class="mctr_manage" style="text-decoration:none;"><?php echo $row->tnd_heading; ?></a>
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
	$('#tnd_publish_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->tnd_publish_date)); ?>').trigger('change');
	$('#tnd_docSaleStart_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->tnd_docSaleStart_date)); ?>').trigger('change');
	$('#tnd_docSaleEnd_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->tnd_docSaleEnd_date)); ?>').trigger('change');
	$('#tnd_docSubmitBefore_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->tnd_docSubmitBefore_date)); ?>').trigger('change');
	$('#tnd_due_date').datePicker().val('<?php echo date("Y-m-d",strtotime($row->tnd_due_date)); ?>').trigger('change');
});
</script>

<!--Start Suppliers HTML-->
<div class="to_bd"> Tender Details</div>
<div class="to_ct">
	<div class="to_lp" style="min-height:53px;width:80%;">

	
    <table>
    	<input type="hidden" id="tnd_id" name="tnd_id" value="<?php echo $row->tnd_id; ?>" />
        <tr><td class="br_label" style="vertical-align:middle;">Tender Heading: </td><td><input type="text" id="tnd_heading" name="tnd_heading" value="<?php echo $row->tnd_heading; ?>" style="width:400px;padding:5px;"/></td></tr>
	    <tr>
        <?php
	$mcat_sql="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row->tnd_pc_id."' and pc_status='1') and pc_status='1'";
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
			$sql_spc="select * from product_category where pc_parent_id=(select pc_parent_id from product_category where pc_id='".$row->tnd_pc_id."') and pc_status='1'";
			$res_spc=mysqli_query($con, $sql_spc);
			?>
            <select id="tnd_pc_id" name="tnd_pc_id" class="a_f">
            	<option value=""> - Select Sub-Category - </option>
            <?php
			while($row_spc=mysqli_fetch_object($res_spc))
			{	?>
				<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$row->tnd_pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
		<?php	}	?>
	        </select>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Tender Value: </td>
        <td><input type="text" id="tnd_value" name="tnd_value" value="<?php echo $row->tnd_value; ?>" style="width:250px;padding:5px;"/>
        <select size="1" name="tnd_currency" id="tnd_currency" class="a_f s_u">
				<option value="">-Select Currency-</option>
                <?php                
				$currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($currencyrow=mysqli_fetch_object($currencysql)){
				?>
	            <option value="<?php echo $currencyrow->cn_id;?>" <?php if($row->tnd_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } else if($tnd_currency==$currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Notice Type: </td><td><input type="text" id="tnd_notice_type" name="tnd_notice_type" value="<?php echo $row->tnd_notice_type; ?>" style="width:200px;padding:5px;"/></td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Quantity: </td><td>
        <input type="text" id="tnd_qty" name="tnd_qty" value="<?php echo $row->tnd_qty; ?>" style="width:250px;padding:5px;"/>
        <select size="1" name="tnd_qty_mu_id" id="tnd_qty_mu_id" class="a_f s_u">
        <option value="">-Select Unit-</option>
                <?php                
				$res_mu=mysqli_query($con, "select * from measurement_unit where mu_status='1'");
				while($row_mu=mysqli_fetch_object($res_mu)){
				?>
                <option value="<?php echo $row_mu->mu_id; ?>" <?php if($row_mu->mu_id==$row->tnd_qty_mu_id){ ?> selected="selected"<?php } ?> ><?php echo $row_mu->mu_name;?></option>
				<?php } ?>
            </select>        
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">EMD: </td><td><input type="text" id="tnd_emd" name="tnd_emd" value="<?php echo $row->tnd_emd; ?>" style="width:200px;padding:5px;"/></td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Fees: </td><td>
        <input type="text" id="tnd_document_fees" name="tnd_document_fees" value="<?php echo $row->tnd_document_fees; ?>" style="width:250px;padding:5px;"/>
        <select size="1" name="tnd_document_fees_currency" id="tnd_document_fees_currency" class="a_f s_u">
				<option value="">-Select Currency-</option>
                <?php                
				$df_currencysql=mysqli_query($con, "select * from country where cn_status='1'");
				while($df_currencyrow=mysqli_fetch_object($df_currencysql)){
				?>
	            <option value="<?php echo $df_currencyrow->cn_id;?>" <?php if($row->tnd_document_fees_currency==$df_currencyrow->cn_id){ ?> selected="selected" <?php } else if(user_info($uid,'country')== $df_currencyrow->cn_id){ ?> selected="selected" <?php } ?>><?php echo $df_currencyrow->cn_currency;	?></option>
				<?php } ?>
            </select>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Project Period: </td><td><input type="text" id="tnd_project_period" name="tnd_project_period" value="<?php echo $row->tnd_project_period; ?>" style="width:255px;padding:5px;"/></td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Products: </td><td><input type="text" id="tnd_products" name="tnd_products" value="<?php echo $row->tnd_products; ?>" style="width:400px;padding:5px;"/></td></tr>
        
        <tr><td class="br_label" style="vertical-align:middle;">Tender Publish Date: </td><td>
		<input name="tnd_publish_date" id="tnd_publish_date" class="date-pick dp-applied " style="padding:5px;"readonly="readonly"/><a  title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Sale Starts: </td><td>
		<input name="tnd_docSaleStart_date" id="tnd_docSaleStart_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Sale Ends: </td><td>
		<input name="tnd_docSaleEnd_date" id="tnd_docSaleEnd_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Document Submit Before: </td><td>
		<input name="tnd_docSubmitBefore_date" id="tnd_docSubmitBefore_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        <tr><td class="br_label" style="vertical-align:middle;">Tender Due Date: </td><td>
		<input name="tnd_due_date" id="tnd_due_date" class="date-pick dp-applied" style="padding:5px;"/><a title="Choose date"></a>
        </td></tr>
        
    	<tr><td class="br_label">Pre-qualification Criteria: </td><td>
		<textarea id="tnd_prequalification_criteria" name="tnd_prequalification_criteria" style="width:400px;"><?php echo stripslashes($row->tnd_prequalification_criteria); ?></textarea>
        </td></tr>
        <tr><td class="br_label">Details: </td><td>
		<textarea id="tnd_details" name="tnd_details" style="width:400px;"><?php echo stripslashes($row->tnd_details); ?></textarea>
        </td></tr>
        <tr><td></td><td></td></tr>
	    
        <tr style="padding-top:2px;"><td class="br_label">Location Preferences: </td><td>
        <input type="radio" id="tnd_preferred_location_1" name="tnd_preferred_location" value="abroad" <?php if($row->tnd_preferred_location=='abroad'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Abroad Only</label>
        &nbsp;&nbsp;
        <input type="radio" id="tnd_preferred_location_2" name="tnd_preferred_location" value="any" <?php if($row->tnd_preferred_location=='any'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Abroad + Domestic</label>
        &nbsp;&nbsp;
        <input type="radio" id="tnd_preferred_location_3" name="tnd_preferred_location" value="domestic" <?php if($row->tnd_preferred_location=='domestic'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Domestic Only</label>
        &nbsp;&nbsp;
        <input type="radio" id="tnd_preferred_location_4" name="tnd_preferred_location" value="my_city" <?php if($row->tnd_preferred_location=='my_city'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">My City Only</label>
		
        </td></tr>
        
        
        <tr><td colspan="2" style="text-align:center"><input name="btnUpdate" id="btnUpdate" value="Update Tender" class="saps mt5" type="button" onclick="updateTender();"/></td></tr>
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