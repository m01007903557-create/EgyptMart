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
	<ul class="cb wide_thin_col_parent">
<?php 
$sql_wc="select * from website_content where wc_usr_id='".$row->usr_id."'";
$res_wc=mysqli_query($con,$sql_wc);
$row_wc=mysqli_fetch_object($res_wc);
?>	
	<li id="wideColumn">
<?php if($row_wc->wc_homepage_key_desc != '') : ?>	
		<section class="box1"title=" Company Description ">
		<div class="h2"><h2>الوصـف العــام للشركـة</h2></div>
		<nav class="comPro">
		
<p><?php echo $row_wc->wc_homepage_key_desc; ?></p>
		
		</nav>
		</section><br>
		<?php endif; ?>
		<?php if($row_wc->wc_homepage_detail_desc != '') : ?>
			<section class="box1"title="Company Information  " >
		<div class="h2"><h2>معلومات أكثر عن الشركة</h2></div>
		<nav class="comPro">
<p><?php echo $row_wc->wc_homepage_detail_desc; ?></p>
	
		</nav>
		</section><br>
		<?php endif; ?>
	
    <script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
				<section class="box1" id="featuredProducts"title="Products & Services  ">
				<div class="h2"><h2> منتجات وخدمات الشركة </h2>
			
			<!-- Separate social icons bar - one time only -->
					<div class="section-social-icons">
						<a href="https://twitter.com/GenidyEhab" target="_blnak"><i class="fa fa-twitter-square"></i></a>
						<a href="https://www.linkedin.com/in/ehab-genidy-a0730b105/" target="_blnak"><i class="fa fa-linkedin-square"></i></a>
						<a href="https://www.facebook.com/%D8%B3%D9%88%D9%82-%D9%85%D8%B5%D8%B1-%D8%B9%D9%84%D9%89-%D8%A7%D9%84%D8%A7%D9%86%D8%AA%D8%B1%D9%86%D8%AA-Egypt-MART-111509273583951" target="_blnak"><i class="fa fa-facebook-square"></i></a>
					</div>

					<style>
					.section-social-icons {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-left: 20px;
    vertical-align: middle;
}

.section-social-icons a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 6px;
    background: #f5f5f5;
    box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    transition: transform 0.2s ease;
    text-decoration: none;
}

.section-social-icons a:hover {
    transform: translateY(-2px);
}

.section-social-icons i {
    font-size: 20px;
}

.section-social-icons a:nth-child(1) i { color: #1da1f2; } /* Twitter */
.section-social-icons a:nth-child(2) i { color: #0077b5; } /* LinkedIn */
.section-social-icons a:nth-child(3) i { color: #1877f2; } /* Facebook */

/* Keep heading + icons aligned side by side */
.h2 {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

/* Mobile optimization */
@media (max-width: 768px) {
    .section-social-icons {
        margin-left: 0;
        margin-top: 8px;
        width: 100%;
        justify-content: flex-start;
        gap: 10px;
    }

    .section-social-icons a {
        width: 40px;   /* slightly bigger tap target on mobile */
        height: 40px;
    }

    .section-social-icons i {
        font-size: 22px;
    }
}
					
					
					</style>
			
			
			
			

			
			
			
			</div>			
				<nav>
				<ul class="index-product-listing">
<?php
$sql_pd_h="select * from products where pd_uid='".$row->usr_id."' and pd_status='1'";
$res_pd_h=mysqli_query($con,$sql_pd_h);
if(mysqli_num_rows($res_pd_h)>0)
{	
// $j=0;
while($row_pd_h=mysqli_fetch_object($res_pd_h)){
?>
	            <?php //if(($j%3 == 0)||($j == 0)){?><li class="cb"><?php //} ?>
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
					<p><a href="product-details.php?token=<?php echo rand(1000,9999).md5($row_pd_h->pd_id); ?>&c=<?php echo $c; ?>"><?php echo $row_pd_h->pd_title; ?></a></p>
					</figure>

					
					</div>
				<?php //if((($j+1)%3 == 0)&&($j != 0)){?></li><?php //} 
				// $j++;
				}}
if(mysqli_num_rows($res_pd_h)%3 != 0) {?></li><?php } 
				?>
										</ul>
				</nav>
				</section><br>
	</li><?php include "includes/right.php"; ?>
</ul>
	</div>
	<?php include "includes/footer.php"; ?>
</body></html>