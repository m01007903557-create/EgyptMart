<?php
ob_start();
session_start();
include "../common.php";

$br_id=$_POST['id'];

$sql="select * from buy_requirement,product_category,measurement_unit where br_pc_id=pc_id and br_estimate_qty_unit=mu_id and br_id='".$br_id."'";
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
<script type="text/javascript">
function show_photo(id)
{
	$.get("ajax-file/showBuyRequirementImage.php", {id:id},	function(data){
		$("#img_disp").html('');												 
		$("#img_disp").html('<img src="'+data+'" alt="" height="100" width="125"/>');
	});
}
function showCategory()
{
	var pc_id=document.getElementById('mcat_id').value;
	$.post("ajax-file/showSubcat.php",{id:pc_id},	function(data){	$('#pc_id').html(data);	showsubcat();	}); 
}
function showSubcat(id)
{
//	alert(id);
//	var pc_id=document.getElementById('pc_id').value;
	$.post("ajax-file/showSubcat.php",{id:id},	function(data){	$('#br_pc_id').html(data); }); 
}
function updateRequirement()
{
	
	var br_id=document.getElementById('br_id');
	var mcat_id=document.getElementById('mcat_id');
	var pc_id=document.getElementById('pc_id');
	var br_pc_id=document.getElementById('br_pc_id');
	var br_pd_name=document.getElementById('br_pd_name');
    var br_requirement=document.getElementById('br_requirement');
	var br_estimate_qty=document.getElementById('br_estimate_qty');
	var br_estimate_qty_unit=document.getElementById('br_estimate_qty_unit');
	var br_preferred_supplier_location = $('input:radio[name=br_preferred_supplier_location]:checked').val();	
		
	var br_apprx_order_value=document.getElementById('br_apprx_order_value');
	var br_apprx_order_currency=document.getElementById('br_apprx_order_currency');
	var br_description=document.getElementById('br_description');
	var br_website=document.getElementById('br_website');

	var br_need_quote_for = $('input:radio[name=br_need_quote_for]:checked').val();
	var br_purchase_time = $('input:radio[name=br_purchase_time]:checked').val();
	var br_need_for = $('input:radio[name=br_need_for]:checked').val();
	var br_requirement_frequency = $('input:radio[name=br_requirement_frequency]:checked').val();


	var message="";
    var valid=true;

   	if(br_pd_name.value=='')
	{
		alert("Kindly enter Products / Services you are looking for.");
		br_pd_name.focus();
		valid=false;
	}
	else if(!isNaN(br_pd_name.value))
	{
		alert("Kindly enter valid Products / Services you are looking for.");
		br_pd_name.focus();
		valid=false;
	}
 	else if(mcat_id.value=='')
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
	else if(br_pc_id.value=='' || br_pc_id.value=='0')
	{
		alert("Kindly select Sub-Category.");
		br_pc_id.focus();
		valid=false;
	}
	else if(br_requirement.value == "" || br_requirement.value == null)
	{
		alert("Kindly describe your Buying Requirements in detail.");
		br_requirement.focus();
		valid=false;
	}
	else if(br_requirement.value.length<50)
	{
		alert("Your Buy Requirement description should not be less than 50 characters.");
		br_requirement.focus();
		valid=false;
	}
	else if(br_estimate_qty.value=='')
	{
		alert("Kindly enter Estimated Quantity.");
		br_estimate_qty.focus();
		valid=false;
	}
	else if(isNaN(br_estimate_qty.value))
	{
		alert("Kindly enter valid Estimated Quantity.");
		br_estimate_qty.value='';
		br_estimate_qty.focus();
		valid=false;
	}
	else if(br_estimate_qty_unit.value=='')
	{
		alert("Kindly select Estimated Quantity Unit.");
		br_estimate_qty_unit.focus();
		valid=false;
	}
	else if(br_apprx_order_value.value!='' && isNaN(br_apprx_order_value.value))
	{
		alert("Kindly enter valid Approximate Order Value.");
		br_apprx_order_value.focus();
		valid=false;
	}
	else
	{
		$.post("ajax-file/updRequirement.php",{br_id:br_id.value,br_pc_id:br_pc_id.value,br_pd_name:br_pd_name.value,br_requirement:br_requirement.value,br_estimate_qty:br_estimate_qty.value,br_estimate_qty_unit:br_estimate_qty_unit.value,br_preferred_supplier_location:br_preferred_supplier_location,br_apprx_order_value:br_apprx_order_value.value,br_apprx_order_currency:br_apprx_order_currency.value,br_description:br_description.value,br_website:br_website.value,br_need_quote_for:br_need_quote_for,br_purchase_time:br_purchase_time,br_need_for:br_need_for,br_requirement_frequency:br_requirement_frequency},    function(data){
			console.log(data);
			data=data.trim();
			dt=data.split("|")
			if(dt[0]=='0')
			{
				alert(dt[1]);
			}
			else
			{
				//alert("Buy Requirement updated successfully.");
				alert(dt[1]);
				detailRequirement(br_id.value);
			}			
		});
	}
}

</script>
<div class="mctr_buyreq mfl">
<!--#######Responses Main Start-->
<div class="mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div>
	<a class="mctr_manage" style="text-decoration:none;">Manage Buy Requirements</a>
	<span style="float:right;color:#929292;font-size:16px;padding-right:87px">
		<a href="javascript:goback()" style="font-size:12px;padding-top:4px;font-weight:bold">&laquo; Back</a>
	</span>
</div>

<!--Responses Main End-->




<!--Start Suppliers HTML-->
<div class="to_bd"> Buy Requirement Details</div>
<div class="to_ct">
	<div class="to_lp" style="min-height:53px;width:80%;">

	
    <table>
    	<input type="hidden" id="br_id" name="br_id" value="<?php echo $row->br_id; ?>" />
        <tr><td class="br_label">Product/Service: </td><td><input type="text" id="br_pd_name" name="br_pd_name" value="<?php echo $row->br_pd_name; ?>" /></td></tr>
	    <tr>
        <?php
	$mcat_sql="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row->br_pc_id."' and pc_status='1') and pc_status='1'";
	$mcat_res=mysqli_query($con, $mcat_sql);
	$mcat_row=mysqli_fetch_object($mcat_res);
	?>
    
        <?php
	        $sql_mcat="select * from product_category where pc_parent_id='0' and pc_status='1'";
			$res_mcat=mysqli_query($con, $sql_mcat);
		?>
        <td class="br_label">Main Category: </td><td>
		<select id="mcat_id" name="mcat_id" onChange="showCategory();">
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
        <td class="br_label">Category: </td><td>
		<select id="pc_id" name="pc_id" onChange="showSubcat(this.value);">
            <?php
			while($row_pc=mysqli_fetch_object($res_pc))
			{	?>
			<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$mcat_row->pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
		<?php	}	?>
	    </select>
        </td></tr>
    	<tr><td class="br_label">Sub-Category: </td><td>
		<?php
//		 	$sql_pc="select c.pc_name,s.pc_name from product_category c,product_category s where c.pc_id=s.pc_parent_id and s.pc_id='".$row->so_pc_id."'";
			$sql_spc="select * from product_category where pc_parent_id=(select pc_parent_id from product_category where pc_id='".$row->br_pc_id."') and pc_status='1'";
			$res_spc=mysqli_query($con, $sql_spc);
			?>
            <select id="br_pc_id" name="br_pc_id">
            	<option value=""> - Select Sub-Category - </option>
            <?php
			while($row_spc=mysqli_fetch_object($res_spc))
			{	?>
				<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$row->br_pc_id){ ?> selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
		<?php	}	?>
	        </select>
        </td></tr>
    	<tr><td class="br_label">Details: </td><td>
		<textarea id="br_requirement" name="br_requirement" style="width:400px;"><?php echo $row->br_requirement; ?></textarea>
        </td></tr>
	    <tr><td class="br_label">Estimated Quantity: </td><td>
        <input name="br_estimate_qty" id="br_estimate_qty" type="text" value="<?php if($row->br_estimate_qty!='0.00'){	echo $row->br_estimate_qty;	} ?>" />
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
        </td></tr>
        <tr><td class="br_label">Location Preferences: </td><td>
        <input type="radio" id="br_preferred_supplier_location_1" name="br_preferred_supplier_location" value="abroad" <?php if($row->br_preferred_supplier_location=='abroad'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Abroad Only</label>
        &nbsp;&nbsp;
        <input type="radio" id="br_preferred_supplier_location_2" name="br_preferred_supplier_location" value="any" <?php if($row->br_preferred_supplier_location=='any'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Abroad + Domestic</label>
        &nbsp;&nbsp;
        <input type="radio" id="br_preferred_supplier_location_3" name="br_preferred_supplier_location" value="domestic" <?php if($row->br_preferred_supplier_location=='domestic'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">Domestic Only</label>
        &nbsp;&nbsp;
        <input type="radio" id="br_preferred_supplier_location_4" name="br_preferred_supplier_location" value="my_city" <?php if($row->br_preferred_supplier_location=='my_city'){ ?> checked="checked"<?php } ?>/><label style="top:0px;">My City Only</label>
		
        </td></tr>
        
        
        <tr>
        	<td class="br_label">Image: </td>
        	<td>
            <table>
            <tr>
            <td>
            <div style="padding-left:5px;padding-top:0px;" id="img_disp">
				<img src="upload/buy_requirement/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image.png";	} ?>" id="6390059595_1" border="1" height="100" hspace="0" vspace="0" width="125">
			</div>
            </td>
            <td>
            <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
			<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
			<script type="text/javascript">
				
					jQuery('#file_upload').uploadifive({
						'auto'     : true,
						'formData' : {'id' : '<?php echo $row->br_id; ?>'},
						'queueID'  : 'queue',
						'debug'    : true,
						'method'   : 'post',
						'uploadScript' : 'ajax-file/editBuyRequirementImg.php',
						'onAddQueueItem' : function(file) {
							//  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
							$("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
						},
						'onUploadComplete' : function(file,data) {
							show_photo(<?php echo $row->br_id; ?>);
						}
					});
				</script>
                <div id="drop" style="padding-left:10px;">
            <input type="file" id="file_upload" name="file_upload"/>
            </div>
            <div id="queue"></div>
            </td>
            <td>
            
            <script>
				$(document).ready(function(){
					/*$('body').on('click', '.ajax', function() {
					  $.colorbox({width:"72%"});
					  return false;
					});*/
					//Examples of how to assign the ColorBox event to elements
					//$(".ajax").colorbox({width:"72%"});
					$(".inline").colorbox({inline:true, width:"50%"});
					//Example of preserving a JavaScript event for inline calls.
					$("#click").click(function(){ 
						$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
						return false;
					});
				});
			</script>
            <a class="ajax" href="popup-imagegallery.php" style="text-decoration:none;">Select from Image Gallery</a>
            </td>
            </td>
            </tr>
            </table>
            </td>        
        </tr>
    	<tr><td class="br_label">Approximate Order Value: </td><td>
		<input name="br_apprx_order_value" id="br_apprx_order_value" type="text" value="<?php if($row->br_apprx_order_value!='0.00'){	echo $row->br_apprx_order_value;	} ?>" />
        <select name="br_apprx_order_currency" id="br_apprx_order_currency">
        <?php
			$sql_curr="select distinct cn_currency from country where cn_status='1'";
			$res_curr=mysqli_query($con, $sql_curr);
		?>
        	<option selected="selected" value="">--Select Currency--</option>
            <?php	while($row_curr=mysqli_fetch_object($res_curr)){	?>
            <option value="<?php echo $row_curr->cn_currency; ?>" <?php if($row_curr->cn_currency==$row->br_apprx_order_currency){ ?> selected="selected"<?php } ?> ><?php echo $row_curr->cn_currency; ?></option> 
            <?php	}	?>
		</select>
		</td></tr>
        <tr><td class="br_label">Product Application/ Usage: </td><td>
		<textarea id="br_description" name="br_description" style="width:400px;"><?php echo $row->br_description; ?></textarea>
        </td></tr>
        <tr><td class="br_label">Website: </td><td>
		<input name="br_website" id="br_website" type="text" value="<?php if($row->br_website!='http://'){	echo $row->br_website;	} ?>" />
        </td></tr>

        <tr><td class="br_label">Need Quotations: </td><td>
        <input name="br_need_quote_for" id="br_need_quote_for0" type="radio" value="To Make Purchase" <?php if($row->br_need_quote_for=='To Make Purchase'){ ?> checked="checked"<?php } ?>/> To Make Purchase 
		<input name="br_need_quote_for" id="br_need_quote_for1" type="radio" value="To Know Price Only" <?php if($row->br_need_quote_for=='To Know Price Only'){ ?> checked="checked"<?php } ?>> To Know Price Only 
        </td></tr>
        <tr><td class="br_label">How soon want to purchase: </td><td>
		<input type="radio" id="q_timperiod0" name="br_purchase_time" value="Immediate" <?php if($row->br_purchase_time=='Immediate'){ ?> checked="checked"<?php } ?>/> Immediate
		<input type="radio" id="q_timperiod1" name="br_purchase_time" value="Within 15 Days" <?php if($row->br_purchase_time=='Within 15 Days'){ ?> checked="checked"<?php } ?>/> Within 15 Days
		<input type="radio" id="q_timperiod2" name="br_purchase_time" class="ace" value="Within 1 Month" <?php if($row->br_purchase_time=='Within 1 Month'){ ?> checked="checked"<?php } ?>/> Within 1 Month
        </td></tr>
        <tr><td class="br_label">Why need this: </td><td>
		<input type="radio" id="br_need_for0" name="br_need_for" value="For Reselling" <?php if($row->br_need_for=='For Reselling'){ ?> checked="checked"<?php } ?>/> For Reselling
		<input type="radio" id="br_need_for1" name="br_need_for" value="For Your End Use" <?php if($row->br_need_for=='For Your End Use'){ ?> checked="checked"<?php } ?>/> For Your End Use
		<input type="radio" id="br_need_for2" name="br_need_for" value="As Raw Material" <?php if($row->br_need_for=='As Raw Material'){ ?> checked="checked"<?php } ?>/> As Raw Material
        </td></tr>
        <tr><td class="br_label">Requirement Frequency: </td><td>
		<input name="br_requirement_frequency" id="br_requirement_frequency1" type="radio" value="One Time Requirement" <?php if($row->br_requirement_frequency=='One Time Requirement'){ ?> checked="checked"<?php } ?>/> One Time Requirement
		<input name="br_requirement_frequency" id="br_requirement_frequency2" type="radio" value="Regular Requirement" <?php if($row->br_requirement_frequency=='Regular Requirement'){ ?> checked="checked"<?php } ?>> Regular Requirement
        </td></tr>
        <tr><td colspan="2" style="text-align:center"><input name="btnUpdate" id="btnUpdate" value="Update Buy Requirement" class="saps mt5" type="button" onclick="updateRequirement();"/></td></tr>
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