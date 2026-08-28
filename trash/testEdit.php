<?php
include "common.php";

$id=$_GET['id'];

$sql="select * from sale_offer,user,business_profile where so_usr_id=usr_id and usr_id=bnsprof_uid and so_id='".$id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
?>
<script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
<script type="text/javascript">
function show_photo(id)
{
	$.get("ajax-files/showSaleofferImage.php", {id:id},	function(data){
		$("#img_disp").html('');												 
		$("#img_disp").html('<img src="'+data+'" alt="" height="125" width="125"/>');
	});
}
</script>
<div class="mctr mfl mpt8">
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tbody>
			<tr><td>
	<table class="mpr10" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">

      
      <tbody><tr>
        <td style="border-right:0px;" align="LEFT" valign="top" width="99%"><div class="mf18 mc10 mta2 mpt8 mpb10"><!-- <div class="mf11 bc mbn">Trade Offers &#187</div> --> <a class="mtd mc5">Manage your Offers</a> &gt;&gt; Offer</div><table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tbody><tr>
            <td valign="BOTTOM" width="140">
            <div class="o_detail">OFFER DETAILS</div><img src="ajax-file/images/zero_002.gif" height="6" width="150"></td></tr>
        </tbody></table>
        <table style="border-collapse:collapse" border="1" bordercolor="#CCEEFF" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td colspan="4" bgcolor="#DFF2FF" height="25">
	<div class="ofdt4"><b><font color="#800000">Offer Title:</font></b><font color="#800000">&nbsp; <?php echo $row->so_service; ?> </font></div></td>
	</tr>

    <tr>
        <td class="ofdt5" align="CENTER" height="25"><b>Offer Types</b></td>
        <td class="ofdt5" align="CENTER"><b>Original Posting Date</b></td>
		<td class="ofdt5" align="CENTER"><b>Updated/Refreshed Date</b></td>
        <td class="ofdt5" align="CENTER"><b>Expiry Date</b></td>
	</tr>
      <tr>
        <td class="o-testrd" align="CENTER" height="25">Sell</td>
        <td class="o-testrd" align="CENTER" height="25"><?php echo date("d M Y",strtotime($row->so_posting_date)); ?></td>
	<td class="o-testrd" align="CENTER" height="25"><?php echo date("d M Y",strtotime($row->so_updated_date)); ?></td>
        <td class="o-testrd" align="CENTER" height="25"><?php echo date('d M Y', strtotime($row->so_posting_date.' +'.$row->so_validity.' day')); ?></td>
      </tr>
    </tbody></table><br>
    <table class="td-padd" style="border-collapse: collapse;" align="left" border="0" bordercolor="#cceeff" cellpadding="0" cellspacing="0" width="100%">
       <tbody>
       <tr>
         <td class="adss" style="border-top: 0px none;"><img src="ajax-file/images/zero.gif" height="1" width="160"></td>
         <td width="100%"></td>
       </tr>
	<tr>
    	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Category</b></td>
	    <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
        <select id="pc_id" name="pc_id" onchange="showSubcat(this.value)">
            <option value="">--Select Category--</option>
            <?php
				$sql_pc="select * from product_category where pc_parent_id='0' and pc_status='1'";
				$res_pc=mysqli_query($con, $sql_pc);
				while($row_pc=mysqli_fetch_object($res_pc)){
			?>
			<option value="<?php echo $row_pc->pc_id; ?>" <?php if($row_pc->pc_id==$pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_pc->pc_name; ?></option>
            <?php	}	?>
		</select>
        <select id="so_pc_id" name="so_pc_id">
            <option value="">--Select Sub-Category--</option>
			<?php
				$sql_spc="select * from product_category where pc_parent_id='".$pc_id."' and pc_status='1' and pc_parent_id!='0'";
				$res_spc=mysqli_query($con, $sql_spc);
				while($row_spc=mysqli_fetch_object($res_spc)){
			?>
			<option value="<?php echo $row_spc->pc_id; ?>" <?php if($row_spc->pc_id==$so_pc_id){ ?>selected="selected"<?php } ?>><?php echo $row_spc->pc_name; ?></option>
			<?php	}	?>
		</select>
        </td>
	</tr>
	<tr>
       	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Product / Service Title</b></td>
        <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38"><input type="text" id="so_service" name="so_service" value="<?php echo stripslashes($row->so_service); ?>" style="width:400px;" /></td>
	</tr>
	<tr>
       	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Products / Service Description</b></td>
        <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
        <textarea id="so_description" name="so_description" style="width:400px;"><?php echo stripslashes($row->so_description); ?></textarea>
        </td>
	</tr>

        <tr>

        <td bgcolor="#F1F5FE">

            <div class="ofdt1" align="right"><b>Product Photo</b></div></td>
            <td bgcolor="#f6fdff">
				<table><tr><td>
    <script src="uploadifive/jquery.uploadifive.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="uploadifive/uploadifive.css">
						<script type="text/javascript">
							jQuery(function(){
								jQuery('#file_upload').uploadifive({
									'auto'     : true,
									'formData' : {'id' : '<?php echo $row->so_id; ?>'},
									'queueID'  : 'queue',
									'debug'    : true,
									'method'   : 'post',
									'uploadScript' : 'ajax-file/editSOImg.php',
									'onAddQueueItem' : function(file) {
											   //  this.data('uploadifive').settings.formData = {'albums': $('select#albums').val()};
									  // $("#img_disp").html('<img src="images/loader.gif" alt="Uploading...." height="125" width="125"/>');
									},
									'onUploadComplete' : function(file,data) {
								//		show_photo(<?php /*echo $row->so_id;*/ ?>);
									}
								});
							});
						</script>
						
			<div style="padding-left:18px;padding-top:5px;" id="img_disp">

			<img src="upload/sale_offer/<?php if($row->so_pic !=''){	echo $row->so_pic;	}else{ echo "no-image.png";	} ?>" id="6390059595_1" border="0" height="auto" hspace="0" vspace="0" width="125">
		
			</div>
            </td>
            <td>
            <div id="drop" style="padding-left:10px;">
            <input type="file" id="file_upload" name="file_upload"/>
            </div>
            <div id="queue"></div>
            </td>
            </tr>
            </table>
	
			</td>
      </tr>

	<tr>
       	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Validity</b></td>
        <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
        <?php
	    if($row->so_validity=='365')
		{
			echo "1 year";	
		}
		else if($row->so_validity=='90')
		{
			echo "3 months";	
		}
		else if($row->so_validity=='30')
		{
			echo "1 month";	
		}
		?>
        </td>
	</tr>
	<tr>
       	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Change Offer Validity</b></td>
        <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38"><input type="checkbox" id="" name="" value="yes"  /></td>
	</tr>
	<tr><td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Validity</b></td>
    <td class="ofdt" bgcolor="#F6FDFF" height="38">
		<input name="so_validity" value="30" <?php if($row->so_validity=='30'){ ?> checked="checked" <?php } ?> type="radio">1 Month 
		<input name="so_validity" value="90" <?php if($row->so_validity=='90'){ ?> checked="checked" <?php } ?> type="radio">3 Months
		<input name="so_validity" value="365" <?php if($row->so_validity=='365'){ ?> checked="checked" <?php } ?> type="radio">1 Year

    </td>
		</tr>
        
    <tr>
         <td align="left"><br><div class="o_detail">COMPANY DETAILS</div></td>
         <td></td>
    </tr>
          
	</tbody></table>
</td></tr>
</tbody></table>
<table style="BORDER-COLLAPSE: collapse" class="td-padd" align="center" border="0" bordercolor="#F2F2F2" cellpadding="0" cellspacing="0" width="95%"><tbody><tr>
         <td class="adss" style="border-top: 0px none;"><img src="ajax-file/images/zero.gif" height="1" width="160"></td>
         <td width="100%"></td>
       </tr>
       <tr><td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE">

            <b>Company Name</b>&nbsp;</td>
            <td class="ofdt" bgcolor="#F6FDFF" height="30" width="100%">&nbsp;<?php echo $row->bnsprof_compname; ?></td>
            </tr>
            <tr>
        	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Contact Person</b>&nbsp;</td>
			<td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php echo $row->name_prefix; ?> <?php echo $row->fname; ?> <?php echo $row->lname; ?></td></tr><tr>
    	    <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Address</b>&nbsp;</td>
        	<td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php if($row->bnsprof_address1!=''){ echo $row->bnsprof_address1.", "; } ?><?php if($row->bnsprof_address2!=''){ echo $row->bnsprof_address2; } ?></td>
            </tr>
            <tr>
            <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>City/Town</b>&nbsp;</td>
			<td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php if($row->bnsprof_city!='0'){ echo get_city_name($row->bnsprof_city); } ?></td>
			</tr>
			<tr>
            <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>State</b>&nbsp;</td>
			<td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php if($row->bnsprof_state!='0'){ echo get_state_name($row->bnsprof_state); } ?> </td>
			</tr>
            <tr>
            	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Country</b>&nbsp;</td>
				<td class="ofdt" bgcolor="#F6FDFF" height="30">&nbsp;<?php if($row->country!='0'){ echo get_country_name($row->country); } ?></td>
			</tr>
            <tr>
           	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Mobile / Cell Phone</b></td>
            <td class="ofdt" bgcolor="#F6FDFF" height="30" width="100%">&nbsp;<?php if($row_comp->mobile1!=''){ ?>0<?php echo $row_comp->mobile1; } ?></td>
            </tr>
            <tr><td colspan="2" height="20" width="100%">&nbsp;</td></tr>
            <tr><td colspan="2" bgcolor="#F6FDFF" width="100%" style="text-align:center"><a onClick="backToListing();" style="text-decoration:none;cursor:pointer;">Back</a></td></tr>
            <!--<tr>
            <td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Business Profile </b></td>
            <td class="ofdt" bgcolor="#F6FDFF" height="30">Lorem
 Ipsum is simply dummy text of the printing and typesetting industry. 
Lorem Ipsum has been the industry's standard dummy text ever since the 
1500s, when an unknown printer took a galley of type and scrambled it to
 make a type specimen book.</td>
 			</tr>-->
		 </tbody></table><div><br>
        </div></td></tr></tbody></table>
      
    
		</div>