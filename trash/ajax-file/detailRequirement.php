<?php
ob_start();
session_start();
include "../common.php";

$br_id=$_POST['id'];

$sql="select * from buy_requirement,user,business_profile,measurement_unit where br_u_id=usr_id and usr_id=bnsprof_uid and br_estimate_qty_unit=mu_id and br_id='".$br_id."'";
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
<div class="mctr_buyreq mfl">
<!--#######Responses Main Start-->
<div class="mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div>
	<a class="mctr_manage" style="text-decoration:none;">Manage Buy Requirements</a>
	<span style="float:right;color:#929292;font-size:16px;padding-right:87px">
		<a onclick="javascript:goback()" style="font-size:12px;padding-top:4px;font-weight:bold;cursor:pointer;">&laquo; Back</a>
		<a onclick="javascript:editRequirement(<?php echo $row->br_id; ?>)" style="font-size:12px;padding-top:4px;font-weight:bold;margin-left:20px;cursor:pointer;">Edit</a>
	</span>
</div>
<div id="ResBlack" class="resblack">
	<div class="to_viewres" id="responses_main" style="display:none">
		<table style="border-collapse:collapse" border="0" cellpadding="0" cellspacing="0" width="100%">
		<tbody><tr>

			<td class="to_reh nlbdr" bgcolor="#2362a5" height="30" width="370"><b>Supplier's Contact Details</b>
			</td>
			<td rowspan="2" valign="top">
				<div id="AllResp">
					<table cellpadding="0" cellspacing="0" width="100%">
						<tbody><tr>
							<td class="to_reh" bgcolor="#2362a5" height="30" width="115"><b>Response Date</b></td>
							<td class="to_reh" style="border-left:1px solid #6096cf" bgcolor="#2362a5"><b>Description</b></td>
						</tr>
					</tbody></table>
					<a href="javascript:closeResLayer()"><img src="../images/rescls.png" style="position:absolute;margin:-46px 0 0 469px" height="25" width="25"></a>
					<div style="overflow:auto;height:400px">
					<!--Responses-->
						<div id="ViewResp">
							<div style="font-family:arial;font-size:15px;font-weight:bold" align="center"><br><br>Loading...<br><br><img src="../images/loading2.gif"><br>
							</div>
						</div>
					<!--Responses-->
					</div>
				</div>
				<!--Send Responses-->
				<div id="SendResp" style="display:none">
					<form name="contactMailForm1" method="post">
						<input name="modid" value="MY" type="hidden">

						<table cellpadding="0" cellspacing="0" width="100%">
						<tbody><tr>
						<td class="to_reh" bgcolor="#2362a5" height="30"><b>Please send reply using form below:
						</b></td>
						</tr>
						<tr>
						<td align="center"><br>
						<b style="font-family:arial;font-size:14px;"><span style="color:#ae0000">Subject:</span> Trade.<?php echo get_page_settings(4);?>: <span id="OfrTitle">jhjhjh uhj</span></b><br>
						<textarea name="mesg" id="mesg" rows="12" style="width:420px;margin:10px 0 10px 0" cols="40" placeholder="Please enter feedback here."></textarea></td>
						</tr>
						<tr>
						<td align="CENTER">
						<input name="mail" value="Submit Response" onclick="return sendinfo1('contactMailForm1');" style="font-size:14px;font-weight:bold;font-family:arial;color:#247500;padding:5px 10px 5px 10px;" type="button">&nbsp;
						<input name="button" value="Cancel" onclick="SendMailResCan();" style="font-size:14px;font-weight:bold;font-family:arial;color:#9a0000;padding:5px 10px 5px 10px;" type="button">
						
						<input name="action" value="domail" type="hidden">
						<input name="exp" value="0" type="hidden"></td>
						</tr>
						</tbody></table><br><br>
					</form>
				</div>
				<div id="h_div1"></div>
			<!--Send Responses-->
			</td>
		</tr>
		<tr>
			<td class="nlbdr" valign="top" width="370">
			<div class="resdesc" id="SuppContactInfo"></div></td>
		</tr>
		</tbody></table>
	</div>
</div>
<!--Responses Main End-->




<!--Start Suppliers HTML-->
<div class="to_bd"> Buy Requirement Details</div>
<div class="to_ct">
	<div class="to_lp" style="min-height:53px;width:80%">

	<b style="color: #1973B9;font-size:18px;padding-left:10px;"><?php echo $row->br_pd_name; ?></b><br>
    <table>
    	<?php
	//	$sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name,s.pc_sort_name from product_category m,product_category c,product_category s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and md5(s.pc_id)='".$pc_id."'";
			$sql_pc="select m.pc_name,c.pc_name,s.pc_name from product_category m,product_category c,product_category s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and s.pc_id='".$row->br_pc_id."'";
			$res_pc=mysqli_query($con, $sql_pc);
			$row_pc=mysqli_fetch_array( $res_pc);
			
		?>
        <tr><td class="br_label">Image: </td><td><img src="upload/buy_requirement/<?php if($row->br_pic !=''){	echo $row->br_pic;	}else{ echo "no-image.png";	} ?>" id="6390059595_1" border="0" height="100" hspace="0" vspace="0" width="125"></td></tr>
   	    <tr><td class="br_label">Main Category: </td><td><?php echo $row_pc[0]; ?></td></tr>
	    <tr><td class="br_label">Category: </td><td><?php echo $row_pc[1]; ?></td></tr>
    	<tr><td class="br_label">Sub-Category: </td><td><?php echo $row_pc[2]; ?></td></tr>
    	<tr><td class="br_label">Details: </td><td><?php echo stripslashes($row->br_requirement); ?></td></tr>
	    <tr><td class="br_label">Estimated Quantity: </td><td><?php echo $row->br_estimate_qty." ".$row->mu_name; ?></td></tr>
        <?php if($row->br_apprx_order_value!='0.00' && $row->br_apprx_order_value!=''){ ?>
    	<tr><td class="br_label">Approximate Order Value: </td><td><?php echo $row->br_apprx_order_currency." ".$row->br_apprx_order_value;	?></td></tr>
        <?php	}	
		if($row->br_description!=''){
		?>
        <tr><td class="br_label">Product Application/ Usage: </td><td><?php echo $row->br_description; ?></td></tr>
        <?php
		}
		if($row->br_website!='http://' && $row->br_website!=''){	?>
        <tr><td class="br_label">Website: </td><td><?php echo $row->br_website;	?></td></tr>
        <?php 
		}
		if($row->br_need_quote_for!=''){	?>
        <tr><td class="br_label">Need Quotations: </td><td><?php echo $row->br_need_quote_for;	?></td></tr>
        <?php 
		}
		if($row->br_preferred_supplier_location!=''){	?>
        <tr><td class="br_label">Preferred Supplier Location: </td><td>
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
			}
			else if($row->br_preferred_supplier_location=='my_city' && $row->bnsprof_city!='0')
			{
				echo get_city_name($row->bnsprof_city);
			}
		?>
        </td></tr>
        <?php 
		}
		if($row->br_need_for!=''){	?>
        <tr><td class="br_label">Why need this: </td><td><?php echo $row->br_need_for; ?></td></tr>
        <?php }
        if($row->br_requirement_frequency!=''){ ?>
        <tr><td class="br_label">Requirement Frequency: </td><td><?php echo $row->br_requirement_frequency; ?></td></tr>
        <?php } ?>
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