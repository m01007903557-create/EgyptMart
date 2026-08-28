<?php
ob_start();
session_start();
include "../common.php";

$auc_id=$_POST['id'];

$sql="select * from auction,user,business_profile where auc_usr_id=usr_id and usr_id=bnsprof_uid and auc_id='".$auc_id."'";
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
	<a class="mctr_manage" style="text-decoration:none;"><?php echo stripslashes($row->auc_heading); ?></a>
	<span style="float:right;color:#929292;font-size:16px;padding-right:87px">
		<a onclick="javascript:goback()" style="font-size:12px;padding-top:4px;font-weight:bold;cursor:pointer;">&laquo; Back</a>
		<a onclick="javascript:editAuction(<?php echo $row->auc_id; ?>)" style="font-size:12px;padding-top:4px;font-weight:bold;margin-left:20px;cursor:pointer;">Edit</a>
	</span>
</div>

<!--Start Suppliers HTML-->
<div class="to_bd"> Auction Details</div>
<div class="to_ct">
	<div class="to_lp" style="min-height:53px;width:80%">

	<!--<b style="color: #1973B9;font-size:18px;padding-left:10px;"><?php /*echo $row->br_pd_name;*/ ?></b>-->
    <table>
    	<?php
	//	$sql_pcat="select m.pc_id,m.pc_name,c.pc_id,c.pc_sort_name,s.pc_sort_name from product_category m,product_category c,product_category s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and md5(s.pc_id)='".$pc_id."'";
			$sql_pc="select m.pc_name,c.pc_name,s.pc_name from product_category m,product_category c,product_category s where m.pc_id=c.pc_parent_id and c.pc_id=s.pc_parent_id and s.pc_id='".$row->auc_pc_id."'";
			$res_pc=mysqli_query($con, $sql_pc);
			$row_pc=mysqli_fetch_array( $res_pc);
			
		?>
   	    <tr style="line-height:20px;"><td class="br_label">Auction Value: </td><td style="padding-left:5px;"><?php if($row->auc_value!='' && $row->auc_value!='0.00'){ echo $row->auc_value."&nbsp;".getCurrency($row->auc_currency); } ?></td></tr>
		<tr style="line-height:20px;"><td class="br_label">Main Category: </td><td style="padding-left:5px;"><?php echo $row_pc[0]; ?></td></tr>
	    <tr style="line-height:20px;"><td class="br_label">Category: </td><td style="padding-left:5px;"><?php echo $row_pc[1]; ?></td></tr>
    	<tr style="line-height:20px;"><td class="br_label">Sub-Category: </td><td style="padding-left:5px;"><?php echo $row_pc[2]; ?></td></tr>
        
        <tr style="line-height:20px;"><td class="br_label">Notice Type: </td><td style="padding-left:5px;"><?php echo $row->auc_notice_type; ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Quantity: </td><td style="padding-left:5px;"><?php echo $row->auc_qty." ".measurement_unit($row->auc_qty_mu_id); ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">EMD: </td><td style="padding-left:5px;"><?php echo $row->auc_emd; ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Document Fees: </td><td style="padding-left:5px;"><?php echo $row->auc_document_fees."&nbsp;".getCurrency($row->auc_document_fees_currency); ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Project Period: </td><td style="padding-left:5px;"><?php echo $row->auc_project_period; ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Products: </td><td style="padding-left:5px;"><?php echo $row->auc_products; ?></td></tr>
		<tr style="line-height:20px;"><td class="br_label">Publish Date: </td><td style="padding-left:5px;"><?php echo date("d-M,Y",strtotime($row->auc_publish_date)); ?></td></tr>
        
        <tr style="line-height:20px;"><td class="br_label">Document Sale Starts: </td><td style="padding-left:5px;"><?php echo date("d-M,Y",strtotime($row->auc_docSaleStart_date)); ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Document Sale Ends: </td><td style="padding-left:5px;"><?php echo date("d-M,Y",strtotime($row->auc_docSaleEnd_date)); ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Document Submit Before: </td><td style="padding-left:5px;"><?php echo date("d-M,Y",strtotime($row->auc_docSubmitBefore_date)); ?></td></tr>
        
       	<tr style="line-height:20px;"><td class="br_label">Due Date: </td><td style="padding-left:5px;"><?php echo date("d-M,Y",strtotime($row->auc_due_date)); ?></td></tr>
	    <tr style="line-height:20px;"><td class="br_label">Pre-qualification Criteria: </td><td style="padding-left:5px;"><?php echo stripslashes($row->auc_prequalification_criteria); ?></td></tr>
        <tr style="line-height:20px;"><td class="br_label">Preferred Location: </td><td style="padding-left:5px;">
		<?php 
		if($row->auc_preferred_location=='any')
		{
			echo "Anywhere";	
		}
    	else if($row->auc_preferred_location=='abroad')
		{
			echo "Foreign";	
		}
		else if($row->auc_preferred_location=='domestic')
		{
			echo get_country_name($row->country);	?>
            &nbsp;<img src="images/country_flag/<?php echo get_country_flag($row->country); ?>" alt="" height="16" width="24">
            <?php
		}
		else if($row->auc_preferred_location=='my_city' && $row->bnsprof_city!='0')
		{
			echo get_city_name($row->bnsprof_city);	?>

            <?php
		}
	   ?>
       </td></tr>
        <tr><td class="br_label">Detail Description: </td><td style="padding-left:5px;"><?php echo stripslashes($row->auc_details); ?></td></tr>
        <?php
		$sql_af="select * from auction_additional_value,additional_field where aav_af_id=af_id and aav_auc_id='".$row->auc_id."' group by af_id";
		$res_af=mysqli_query($con, $sql_af);
		if(mysqli_num_rows($res_af))
		{
			while($row_af=mysqli_fetch_object($res_af))
			{
		?>
        <tr><td class="br_label"><?php echo stripslashes($row_af->af_label); ?>: </td><td style="padding-left:5px;">
		<?php
				$sql_aav="select * from auction_additional_value where aav_af_id='".$row_af->aav_af_id."' and aav_auc_id='".$row->auc_id."'";
				$res_aav=mysqli_query($con, $sql_aav);
				$i=0;
				while($row_aav=mysqli_fetch_object($res_aav))
				{
					if($i>0){	?><br/><?php	}
			?>
				<?php echo stripslashes($row_aav->aav_value); ?>
             <?php	
				$i++;
				}	?>
        </td></tr>
        <?php 
			}
		} ?>
    </table>
	
	


</div>

<p class="to_rp1"><b>Last Updated:</b> <?php echo date("d M, Y",strtotime($row->auc_updated_date)); ?><br></p><div style="clear:both;"></div>
</div>
<?php if($row->auc_approval_status=='0'){ ?>
<br>
<div id="NotiFicationDivSuccmsg"></div>
<div class="NotiFicationDiv" id="NotiFicationDiv"><h1>Your Auction is under review by our system</h1>After approval, your Auction will be Live.</div>
<?php	}	?> <!--and we will send you a confirmation mail-->
<!-- content part end here -->

</div>