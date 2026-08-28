<?php
ob_start();
session_start();
include "../common.php";

$pauc_id=$_POST['id'];

$sql="select * from purchased_auction,auction,user where pauc_auc_id=auc_id and auc_usr_id=usr_id and pauc_id='".$pauc_id."'";
$res=mysqli_query($con, $sql);
$row=mysqli_fetch_object($res);

?>
<script src="js/jquery.colorbox.js"></script>

<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script>
	$(document).ready(function(){
		//Examples of how to assign the ColorBox event to elements
				
		$(".ajax").colorbox();
		$(".inline").colorbox({inline:true, width:"50%"});
		//Example of preserving a JavaScript event for inline calls.
		$("#click").click(function(){ 
			$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
			return false;
		});
	});
</script>

<div class="mctr_buyreq mfl">
<!--#######Responses Main Start-->
<div class="mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div><a class="mctr_manage">Purchased Auction</a>
<span style="float:right;color:#929292;font-size:16px;padding-right:87px">« <a href="javascript:goback()" style="font-size:12px;padding-top:4px;font-weight:bold">Back</a></span></div>
<!--Responses Main End-->
<!--Start Suppliers HTML--><div class="to_bd"> Auction Details</div><div class="to_ct"><div class="to_lp" style="min-height:53px;">
<b style="color: #1973B9;font-size:14px;"><?php echo $row->auc_heading; ?></b><br><?php echo stripslashes($row->auc_details); ?>

<?php if($row->auc_value!='0' && $row->auc_value != '0.00'){ ?>
<br><b>Auction Value: </b><?php echo $row->auc_value."&nbsp;".getCurrency($row->auc_currency); ?>
<?php } ?>
<?php if($row->auc_notice_type!=''){ ?>
<br><b>Notice Type: </b><?php echo $row->auc_notice_type; ?>
<?php	}	?>
<?php if($row->auc_qty!='0' && $row->auc_qty != ''){ ?>
<br><b>Quantity: </b><?php echo $row->auc_qty."&nbsp;".measurement_unit($row->auc_qty_mu_id); ?>
<?php	}	?>
<?php if($row->auc_emd!=''){ ?>
<br><b>EMD: </b><?php echo $row->auc_emd; ?>
<?php	}	?>
<?php if($row->auc_document_fees!='0' && $row->auc_document_fees != '0.00'){ ?>
<br><b>Document Fees: </b><?php echo $row->auc_document_fees."&nbsp;".getCurrency($row->auc_document_fees_currency); ?>
<?php	}	?>
<?php if($row->auc_project_period!=''){ ?>
<br><b>Project Period: </b><?php echo $row->auc_project_period; ?>
<?php	}	?>
<?php if($row->auc_products!=''){ ?>
<br><b>Products: </b><?php echo $row->auc_products; ?>
<?php	}	?>

              
              
<br><br>
<span class="bdd bo"><span class="artb sbg"></span><strong> Auction Authority Information : </strong></span>
<br>
<?php echo $row->name_prefix; ?> <?php echo $row->fname; ?> <?php echo $row->lname; ?>
<?php if($row->country!='0' && $row->country!=''){ echo "<br>".get_country_name($row->country); } ?>
<br>Email: <?php echo $row->email; ?>
<?php if($row->mobile1!='' && $row->mobile1!='0'){	?><br>
Mobile / Cell Phone: +(<?php echo $row->country_ph_code; ?>)-<?php echo $row->mobile1; ?><?php } ?>
<br><br>
<a class="ajax" rel="nofollow" href="sendAuctionEnquiry-form.php?id=<?php echo rand(1000,9999).md5($row->auc_usr_id); ?>">Send Enquiry</a>
</div>

<p class="to_rp1"><b>Purchased on:</b> <?php echo date("d M, Y",strtotime($row->pauc_purchase_date)); ?><br></p><div style="clear:both;"></div>
</div>

<!-- content part end here -->

</div>