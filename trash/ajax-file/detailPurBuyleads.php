<?php
ob_start();
session_start();
include "../common.php";

$pbr_id=$_POST['id'];

$sql="select * from purchased_buy_requirement,buy_requirement,user where pbr_br_id=br_id and br_u_id=usr_id and pbr_id='".$pbr_id."'";
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
<div class="mf18 mc5 mta2 mpb10"><div class="mf11 bc mbl mbn"></div><a class="mctr_manage">Purchased Buy Leads</a>
<span style="float:right;color:#929292;font-size:16px;padding-right:87px">« <a href="javascript:goback()" style="font-size:12px;padding-top:4px;font-weight:bold">back</a></span></div>
<!--Responses Main End-->
<!--Start Suppliers HTML--><div class="to_bd"> Buylead Details</div><div class="to_ct"><div class="to_lp" style="min-height:53px;">
<b style="color: #1973B9;font-size:14px;"><?php echo $row->br_pd_name; ?></b><br><?php echo stripslashes($row->br_requirement); ?>

<?php if($row->br_estimate_qty!='0' && $row->br_estimate_qty != ''){ ?>
<br><b>Quantity: </b><?php echo $row->br_estimate_qty; ?>&nbsp;<?php echo measurement_unit($row->br_estimate_qty_unit); ?>
<?php } ?>
<br><br>
<span class="bdd bo"><span class="artb sbg"></span><strong> Buyer Information : </strong></span>
<br>
<?php echo $row->name_prefix; ?> <?php echo $row->fname; ?> <?php echo $row->lname; ?>
<?php if($row->country!='0' && $row->country!=''){ echo "<br>".get_country_name($row->country); } ?>
<br>Email: <?php echo $row->email; ?>
<?php if($row->mobile1!='' && $row->mobile1!='0'){	?><br>
Mobile / Cell Phone: +(<?php echo $row->country_ph_code; ?>)-<?php echo $row->mobile1; ?><?php } ?>
<br><br>
<a class="ajax" rel="nofollow" href="sendLeadEnquiry-form.php?id=<?php echo rand(1000,9999).md5($row->br_u_id); ?>&headline=<?php echo urlencode($row->br_pd_name); ?>">Send Enquiry</a>
</div>

<p class="to_rp1"><b>Purchased on:</b> <?php echo date("d M, Y",strtotime($row->pbr_purchase_date)); ?><br></p><div style="clear:both;"></div>
</div>

<!-- content part end here -->

</div>