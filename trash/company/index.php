<?php
$siteName='';
if($_GET['c'] )
{
$sitePostName=$_GET['c'];
 $siteNameCheck = preg_match('~^[A-Za-z0-9_]{3,20}$~i', $sitePostName);
   if($siteNameCheck)
   {
	   //echo "test";die;
     //Do something. Eg: Connect database and validate the siteName.
   }
   else
  {
 //   header("Location: http://yourwebsite.com/404.php");
   }
}
?>
<?php include "includes/header.php" ?>
<div id="body">
	<ul class="cb">
<?php 
$sql_wc="select * from website_content where wc_usr_id='".$row->usr_id."'";
$res_wc=mysql_query($sql_wc);
$row_wc=mysql_fetch_object($res_wc);
?>	
	<li id="wideColumn">
<?php if($row_wc->wc_homepage_key_desc != '') : ?>	
		<section class="box1"title=" وصف الشركة ">
		<div class="h2"><h2>Company Description</h2></div>
		<nav class="comPro">
		
<p><?php echo $row_wc->wc_homepage_key_desc; ?></p>
		
		</nav>
		</section><br>
		<?php endif; ?>
		<?php if($row_wc->wc_homepage_detail_desc != '') : ?>
			<section class="box1"title=" معلومات عن الشركة " >
		<div class="h2"><h2>Company Information</h2></div>
		<nav class="comPro">
<p><?php echo $row_wc->wc_homepage_detail_desc; ?></p>
	
		</nav>
		</section><br>
		<?php endif; ?>
	
    <script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
				<section class="box1" id="featuredProducts"title=" منتجات وخدمات الشركة  ">
				<div class="h2"><h2> Products & Services</h2></div>			
				<nav>
				<ul>
<?php
$sql_pd_h="select * from products where pd_uid='".$row->usr_id."' and pd_status='1'";
$res_pd_h=mysql_query($sql_pd_h);
if(mysql_num_rows($res_pd_h)>0)
{	
$j=0;
while($row_pd_h=mysql_fetch_object($res_pd_h)){
?>
	            <?php if(($j%3 == 0)||($j == 0)){?><li class="cb"><?php } ?>
					<div>
					<figure class="pr">
					<p>
<script>
	$(document).ready(function(){
		$("#pic_ajax"+<?php echo $row_pd_h->pd_id; ?>).colorbox({width:"62%", height:"89%"});
	});
</script>
<?php if($row_pd_h->pd_image!=''){?>
					<a href="../upload/myproduct/<?php echo $row_pd_h->pd_image;?>" id="pic_ajax<?php echo $row_pd_h->pd_id; ?>">
					<img src="../upload/myproduct/<?php echo $row_pd_h->pd_image;?>" title="<?php echo $row_pd_h->pd_title; ?>" alt="<?php echo $row_pd_h->pd_title; ?>" class="bdr" height="122" width="150"></a>
					</p><div class="zoom pa lh11em"><a href="../upload/myproduct/<?php echo $row_pd_h->pd_image;?>" id="pic_ajax<?php echo $row_pd_h->pd_id; ?>"><img src="images/icon_zoom.png" class="vab"></a></div>
					<?php } ?>
<?php if($row_pd_h->pd_image==''){?>
					<img src="../upload/myproduct/noimage.jpg" title="<?php echo $row_pd_h->pd_title; ?>" alt="<?php echo $row_pd_h->pd_title; ?>" class="bdr" height="122" width="150">
					</p>
					<?php } ?>
					<p></p>
					</figure>
					<p><a href="product-details.php?token=<?php echo rand(1000,9999).md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>"><?php echo $row_pd_h->pd_title; ?></a></p>
					</div>
				<?php if((($j+1)%3 == 0)&&($j != 0)){?></li><?php } 
				$j++;
				}}
if(mysql_num_rows($res_pd_h)%3 != 0) {?></li><?php } 
				?>
										</ul>
				</nav>
				</section><br>
	</li><?php include "includes/right.php"; ?>
</ul>
	</div>
	<?php include "includes/footer.php"; ?>
</body></html>