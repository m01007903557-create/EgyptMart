<?php include "includes/header.php"; 

$token=substr($_GET['token'],4);
$pdresk=mysql_query("select * from products where md5(pd_id)='".$token."' and pd_status='1' ");
$pdrowk=mysql_fetch_object($pdresk) or die(mysql_error());

?>
<div id="body">
<ul class="cb">

<li id="wideColumn">

<div id="breadcrumb">
<ul>
<li><a href="<?php echo '/company/index.php?c=' . $c;?>">Home</a><b>»</b></li>
<li><a href="<?php echo '/company/products.php?c=' . $c;?>">Product</a><b>»</b></li>
<li><?php echo $pdrowk->pd_title;?></li>
</ul>
</div> <br>

<div id="h1"><h1><?php echo $pdrowk->pd_title;?></h1></div><br>  
<div class="ac" style="position: relative;">
<?php if($pdrowk->pd_image!=''){?>
<img src="../upload/myproduct/<?php echo $pdrowk->pd_image;?>" style="max-height:363px; max-width:450px">
<?php if(!empty($pdrowk->pd_imagelogo)){ $limg=explode(',',$pdrowk->pd_imagelogo);?>
       <div class="zk" style=" border: 1px solid #267abf;height: 105px; width: 105px;position: absolute;bottom: 6px;left: 113px;">
       <?php  echo "<img style='width: 105px; height: 105px;' src='/upload/myproduct/".$limg[0]."'>"; ?></div>
                 <?php  } ?> 
					<?php } ?>
<?php if($pdrowk->pd_image==''){?>
<img src="../upload/myproduct/noimage.jpg" title="<?php echo $row_pd_h->pd_title; ?>" alt="<?php echo $row_pd_h->pd_title; ?>" class="bdr">
<?php if(!empty($pdrowk->pd_imagelogo)){ $limg=explode(',',$pdrowk->pd_imagelogo); ?>
       <div class="zk" style=" border: 1px solid #267abf;height: 105px; width: 105px;position: absolute;bottom: 6px;left: 113px;">
       <?php  echo "<img style='width: 105px; height: 105px;' src='/upload/myproduct/".$limg[0]."'>"; ?></div>
                 <?php  } ?> 
<?php } ?>
</div><br>
<section id="proDet" class="box1 cb">
<div class="p10px fo">

<p class="taj pt10px"><?php echo htmlentities($pdrowk->pd_desc); ?></p><br>
</div>
</section><br>

			<section id="career" class="box1">
			<div class="h2"><h2>Other details</h2></div>
			<nav class="proSpe">
<?php                
				$currencysql=mysql_query("select * from country where cn_id='".$pdrowk->pd_currency."'");
				$currencyrow=mysql_fetch_object($currencysql);
				$unitsql=mysql_query("select * from measurement_unit where mu_id='".$pdrowk->pd_unit."'");
				$unitrow=mysql_fetch_object($unitsql);
				?>
							  <div style="width:655px; overflow-x:scroll;">
				  <table style="width:100%" border="1" cellpadding="1" cellspacing="1">	
										<tbody><tr>
						<th scope="row" width="%"><center>Item Code</center></th><td width="%"><?php echo $pdrowk->pd_code;?>	</td>						</tr>
												<tr>
						<th scope="row" width="%"><center>FOB Price</center></th><td width="%"><?php echo $pdrowk->pd_fob_price;?> (<?php echo $currencyrow->cn_currency;?>)</td>						</tr>
												<tr>
						<th scope="row" width="%"><center>Stock</center></th><td width="%"><?php echo $pdrowk->pd_stocks;?> <?php echo $unitrow->mu_name;?>(s)</td>	</tr>
												<tr>
						<th scope="row" width="%"><center>Port of Dispatch</center></th><td width="%"><?php echo $pdrowk->pd_pod;?></td>	</tr>
																		<tr>
						<th scope="row" width="%"><center>Production Capacity</center></th><td width="%"><?php echo $pdrowk->pd_pn_capct;?></td>	</tr>												
						<tr>
						<th scope="row" width="%"><center>Delivery Time</center></th><td width="%"><?php echo $pdrowk->pd_dlv_time;?></td>	</tr>
						<tr>
						<th scope="row" width="%"><center>Packing Details</center></th><td width="%"><?php echo $pdrowk->pd_pck_dets;?></td>	</tr>
						<?php if($pdrowk->pd_pdf_attach!=''){?>
												<tr>
						<th scope="row" width="%"><center>File</center></th><td width="%">
						<a href="../upload/productdoc/<?php echo $pdrowk->pd_pdf_attach;?>" target="_blank">
						<?php echo $pdrowk->pd_pdf_attach;?></a></td>	</tr>
						<?php } ?>
						
												<tr>
						<th scope="row" width="%"><center>Product Status</center></th>
						
						<td width="%">
						<?php if($pdrowk->pd_hot==0){echo 'Default';}else{echo 'Hot';}?>
						</td>	</tr>
						
						
												<tr>
						<th scope="row" width="%"><center>Payment Terms</center></th>
						
						<td width="%">
						<?php echo $pdrowk->pd_payment ;?>
						</td>	</tr>
						
				</tbody></table>			
					</div>
								</nav>
			</section><br><br>
    <script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script type="text/javascript" src="js/jquery-1.9.1.min.js"></script>
<script src='http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.colorbox/1.6.3/jquery.colorbox.js"></script>
<script>
	$(document).ready(function(){
		$("#btn_ajax"+<?php echo $pdrowk->pd_id; ?>).colorbox({width:"62%", height:"89%"});
	});
</script>
<p class="jSea"><a href="quotationRequest.php?id=<?php echo rand(1000,9999).md5($row->bnsprof_id); ?>&pid=<?php echo $pdrowk->pd_id; ?>&vform=1" rel="product-send-inquiry" class="dib b darkbg2 gbibt white bdr darkbdr2 xlarge p7px15px br5px ml5px"  id="btn_ajax<?php echo $pdrowk->pd_id; ?>">SEND INQUIRY</a></p><br><br>

</li>
<li id="thinColumn">
	
	</li><?php include "includes/right.php"; ?>
</ul>
</div>
	<?php include "includes/footer.php"; ?>
</body></html>