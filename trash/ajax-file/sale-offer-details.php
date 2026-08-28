<?php
include "../common.php";

$id=$_POST['id'];

$sql="select * from sale_offer,user,business_profile where so_usr_id=usr_id and usr_id=bnsprof_uid and so_id='".$id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);
?>

<div class="mctr mfl mpt8">
			<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
			<tbody>
			<tr><td>
	<table class="mpr10" align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">

      
      <tbody><tr>
        <td style="border-right:0px;" align="LEFT" valign="top" width="99%"><div class="mf18 mc10 mta2 mpt8 mpb10"><!-- <div class="mf11 bc mbn">Trade Offers &#187</div> --> <a class="mtd mc5">Manage your Offers</a> &gt;&gt; Offer</div><table border="0" cellpadding="0" cellspacing="0" width="100%">
          <tbody><tr>
            <td valign="BOTTOM" width="140">
            <div class="o_detail">OFFER DETAILS</div><img src="images/zero_002.gif" height="6" width="150"></td></tr>
        </tbody></table>
        <table style="border-collapse:collapse" border="1" bordercolor="#CCEEFF" cellpadding="0" cellspacing="0" width="100%">
	<tbody><tr>
	<td colspan="4" bgcolor="#DFF2FF" height="25">
	<div class="ofdt4"><b><font color="#800000">Offer Title:</font></b><font color="#800000">&nbsp; <?php echo $row->so_service; ?> </font></div></td>
	</tr>

    <tr>
        <td class="ofdt5" align="CENTER" height="25"><b>Offer Type</b></td>
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
    </tbody></table><br><table class="td-padd" style="border-collapse: collapse;" align="left" border="0" bordercolor="#cceeff" cellpadding="0" cellspacing="0" width="100%">
       <tbody>
       <tr>
         <td class="adss" style="border-top: 0px none;"><img src="images/zero.gif" height="1" width="160"></td>
         <td width="100%"></td>
       </tr>
       <tr>
       	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Description</b></td>
        <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38"><?php echo stripslashes($row->so_description); ?></td>
	</tr>
    <tr>
       	<td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Location Preference</b></td>
        <td class="ofdt tfrm" bgcolor="#F6FDFF" height="38">
		<?php
		if($row->so_preferred_buyer_location=='any')
		{
			echo "Anywhere";	
		}
    	else if($row->so_preferred_buyer_location=='abroad')
		{
			echo "Foreign";	
		}
		else if($row->so_preferred_buyer_location=='domestic')
		{
			echo get_country_name($row->country);	
		}
		else if($row->so_preferred_buyer_location=='my_city' && $row->bnsprof_city!='0')
		{
			echo get_city_name($row->bnsprof_city);
		}
		?>
        </td>
	</tr>

	<tr><td class="ofdt1" align="RIGHT" bgcolor="#F1F5FE"><b>Offer Validity</b></td>
    <td class="ofdt" bgcolor="#F6FDFF" height="38">
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
        <?php if($row->so_pic !=''){	?>
        <tr>

        <td bgcolor="#F1F5FE">

            <div class="ofdt1" align="right"><b>Product Photo</b></div></td>
            <td bgcolor="#f6fdff">
        <form style="margin:0px;">

        <table style="border-collapse:collapse;" border="0" bordercolor="#F0F9FF" cellpadding="4" cellspacing="0">
	<tbody><tr><th valign="MIDDLE" width="33%">
    
			<div style="padding-left:18px;padding-top:5px;">

			<div style="border:1px solid #71A3C5;background:#FFFFFF;cursor:pointer;">
            
			<img src="upload/sale_offer/<?php echo $row->so_pic; ?>" id="6390059595_1" border="0" height="auto" hspace="0" vspace="0" width="125"></div>
		
			<div id="6390059595_1_H" vspace="0" hspace="0" style="display:none;position:absolute;top:0;left:0;width:0;height:0;background:#FFFFFF;" height="90">
			</div>
		
			</div>
	
			</th>
            <th valign="MIDDLE" width="33%"></th><th valign="MIDDLE" width="33%"></th></tr></tbody></table></form></td>
      </tr>
	  <?php } ?>
    <tr>
         <td align="left"><br><div class="o_detail">COMPANY DETAILS</div></td>
         <td></td>
    </tr>
          
	</tbody></table>
</td></tr>
</tbody></table>
<table style="BORDER-COLLAPSE: collapse" class="td-padd" align="center" border="0" bordercolor="#F2F2F2" cellpadding="0" cellspacing="0" width="95%"><tbody><tr>
         <td class="adss" style="border-top: 0px none;"><img src="images/zero.gif" height="1" width="160"></td>
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
            <tr><td colspan="2" bgcolor="#F6FDFF" width="100%" style="text-align:center">
            	<a onClick="backToListing();" style="text-decoration:none;cursor:pointer;">Back</a>
                &nbsp;&nbsp;
				<a onClick="editSaleOffer(<?php echo $row->so_id; ?>);" style="text-decoration:none;cursor:pointer;">Edit</a>   
             </td></tr>
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