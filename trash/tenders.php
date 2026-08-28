<?php
include "common.php";
//echo "<pre>"; print_r($_COOKIE); echo "</pre>";
if(isset($_COOKIE['loc_id']))
{
	$sql_tnd_ck=" and ((tnd_preferred_location='domestic' and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(tnd_preferred_location='any' and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(tnd_preferred_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	/*
	(tnd_preferred_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and tnd_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(tnd_preferred_location='abroad' and tnd_usr_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/
}
else
{
	$sql_tnd_ck=" and (
	
	(tnd_preferred_location='any')
	or
	(tnd_preferred_location='abroad' and tnd_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	/*(tnd_preferred_location='domestic' and tnd_usr_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(tnd_preferred_location='my_city' and tnd_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<link href="css/eto-index-buy-1.css" rel="STYLESHEET" type="text/css">
<!--<link href="css/dir-style-8.css" rel="stylesheet" type="text/css">-->
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<style>
/* #res1 .xx { width: 315px;} */
.maincontainer { margin-bottom: 40px !important; }
.bp1 { height: 40px;}
</style>
</head>
<body class="search-show-box-buyleads tranders">
<div class="q_hm1">
<!-- Header start Here::-->

<?php include "includes/header_new.php"; ?>

<!---->


<div class="inner_wrapper" style="margin-top:40px;">
    <p class="q_c3"></p>


<!--New Header3 End --><p class="c3"><img alt="" src="images/zero.gif" height="1" width="1"></p> 
<div class="lft1 lfl fl col-md-3 col-sm-3 col-xs-12">
    <span class="mobile-menu"><i class="fa fa-bars" aria-hidden="true"></i></span>
<p class="bg bp1 fl d1 a6 bo col-md-3 col-sm-3 col-xs-12"><img alt="" src="css/img/my-market.png"></p>
<?php
if(get_page_settings('25')=='manual')
{
	$sql_order=" order by pc_order,pc_name";
}
else
{
	$sql_order=" order by pc_name";
}
?>

<link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
<div id='cssmenu' style="
    width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
">
    
  <div id="showsideleft"></div>
</div>

</div>
<div class="tender-right-side">
    <div class="q_bt1 w1 fl w3 col-md-9 col-sm-9 col-xs-12">
<a class="cb2" href="manage-sell-offer.php" rel="nofollow">My Trade Offers</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="cb2" href="manage-tenders.php" rel="nofollow">My Tenders</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="cb2" href="manage-tender-alert.php" rel="nofollow">Manage Tender Alerts</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="q_r" href="post-tender.php" rel="nofollow"><img src="images/zero.gif" alt="">Post Tender</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="q_r" href="post-auction.php" rel="nofollow"><img src="images/zero.gif" alt="">Post Auction</a></div>
    <div class="tender-banner-wrap">
        
    
    <div class=" col-md-9 col-sm-9 col-xs-12">
<a href="http://www.arabyos.com/post-tender.php" target="_blank">
    <div class="tender__banner-background" style="background-image:url('http://www.arabyos.com/images/tender_banner.jpg'); background-size: cover;"></div></a>
</div>
<div class=" col-md-3 col-sm-3 col-xs-12 hide" style="">

<div class="c3">
<?php
	$sql_adv="select * from advertisement where adv_imagewidth='239' and adv_imageheight='186' and adv_status='1' order by rand() limit 1";
	$res_adv=mysqli_query($con, $sql_adv);
	if(mysqli_num_rows($res_adv)>0)
	{
		$row_adv=mysqli_fetch_object($res_adv);	
		?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>"  class="banner_img"/></a><?php
	}
	else
	{
?>
		<img src="upload/advertisement/239-186-advertisement.png" width="239" height="186"/>
<?php	}	?>
</div>

</div>
</div>

<div class="mid fl col-md-12 col-sm-12 col-xs-12"><center> 
<div id="m4t1"></div>
</center>
		<!--<div class="bnbg bpt">
<div class="bnp1 "><img src="images/zero.gif" class="bnbg bpm fl" alt="Tenders" border="1" height="98" width="90">
<p class="bo esb fl wd585">Easy Buying<br>
<span class="f2 g5 lnh1">Send your purchase requirement &amp; receive quotations from verified suppliers. <a href="post-buy-req.php" class="g9 bo f1" onClick="recordInboundLink(this, 'Trac-Buy-home', 'Buy-form', 'startnow', 0);">Start Now</a></span><span class="j1"> ►</span></p>
</div><p class="c3"></p>
<div class="bnbg bpx fl tc bnm"><p class="f4 c2">Tell us what you need</p><br>
<span class="bnp g9 lnh1">Complete <strong>a simple form</strong> and<br>let us know about your<br>buy requirement.</span></div>
<div class="bnbg bpx fl tc bnm"><p class="f4 c2">Supplier Matchmaking</p><br>
<span class="bnp g9 lnh1">We'll find the <strong>best matched</strong> &amp;<br>verified <strong>suppliers</strong> for your <br> buy requirement.</span></div>
<div class="bnbg bpx fl tc"><p class="f4 c2">Receive Quotes</p><br>
<span class="bnp g9 lnh1">Get customized Quotations<br><strong>from Qualified Suppliers</strong><br>via email or phone.</span></div>

</div>-->
<p><br></p>
<script type="text/javascript">
/*$('document').ready( function(){
  $('ul#sidebarTabs').find('a').click( function(){
    $(this)
      .parent().addClass('ho')
      .siblings().removeClass('ho');
  });
});*/
function showLead(page,id)
{
	$('ul#sidebarTabs li').removeClass('ho');
	$('#tabbb'+id).addClass('ho');
	
	 $(".xx").removeClass("on").addClass("off");
	 $("#aaa"+id).addClass("on").removeClass("off");
	 
	 $('#res').html('<div style="width:100%;padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');
	 $.post("ajax-file/tenders.php",{page:page,id:id},    function(data,status){ console.log(status); 
	 	//showsidecate();   
	 	$('#res').html(data); });
	 //showAuction(page,id);
}
function showLeadMain(page,id)
{
	$('ul#sidebarTabs li').removeClass('ho');
	$('#tabbb'+id).addClass('ho');
	
	 $(".xx").removeClass("on").addClass("off");
	 $("#aaa"+id).addClass("on").removeClass("off");
	 
	 $('#res').html('<div style="width:100%;padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');
	 $.post("ajax-file/tenders.php",{page:page,id:id},    function(data,status){ 
	 	console.log(status); 
	 showsidecate();   
	 $('#res').html(data); });
	 //showAuction(page,id);
}
function showAuction(page,id)
{
	$('ul#sidebarTabs li').removeClass('ho');
	$('#tabbb'+id).addClass('ho');
	
	 $(".xx").removeClass("on").addClass("off");
	 $("#aaa"+id).addClass("on").removeClass("off");
	 
	 $('#res').html('<div style="width:100%;padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');
	 $.post("ajax-file/auctions.php",{page:page,id:id},    function(data){    $('#res1').html(data); });
}
</script>

<div class="bx fl" style="width:67%;">
<p class="c4 f4 g5 lbl"><span>Latest Tenders</span><a href="http://arabyos.com/auctions.php" class="Up_coming_Auction">Upcoming Auction</a></p>
<div class="latest-upcoming">
    

<div class="tbl fl">
	<ul id="sidebarTabs">
    <?php
		$pc=0;	
		$sql_cat="select pc.*, t.* from product_category_arabyos pc JOIN  product_category_arabyos pc1 ON pc1.pc_parent_id =  pc.pc_id JOIN  product_category_arabyos pc2 ON pc2.pc_parent_id = pc1.pc_id JOIN tender t ON t.tnd_pc_id = pc2.pc_id where tnd_approval_status='1' and pc.pc_status='1'  and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) ".$sql_tnd_ck."  and tnd_status='1' GROUP BY pc.pc_id";
		//echo $sql_cat;
		$res_cat=mysqli_query($con, $sql_cat);
		$arr=array();
		$i=1;
		while($row_cat=mysqli_fetch_object($res_cat)){	if($i==1){	$pc=$row_cat->pc_id;	}
	?>
		<li onClick="showLead('1','<?php echo $row_cat->pc_id; ?>');" <?php if($i==1){ ?> class="ho"<?php } ?> id="tabbb<?php echo $i; ?>">
	    	<a class="bgf cm1 cp" id="kk1" style="background: url(&quot;upload/category/<?php echo $row_cat->pc_image;?>&quot;) no-repeat scroll 0% 0% transparent;"></a><?php echo $row_cat->pc_name; ?>
		</li>
	<?php
	$i++;
	}	?>
		
	</ul>
</div>
<script type="text/javascript">
$('document').ready( function(){
  showLeadMain(1,<?php echo $pc; ?>);
});
</script>
<div id="res" style="float: left;width: 80%;"></div>
</div>
</div>
<!--<div class="bx fl"  style="width:28%;">
<p class="c4 f4 g5 lbl"><a href="auctions.php" class="q_r">Upcoming Auctions</a></p>
<div class="tbl fl">
</div>
<div id="res1"></div>
           
</div>-->


<div class="ryt fl" style="
    padding: 7px;   
    width: 28%;
	float:right;
">


<?php
$tdate=date("Y-m-d");
$sql_t="select * from tender,product_category_arabyos,user,business_profile where tnd_pc_id=pc_id and tnd_usr_id=usr_id and usr_id=bnsprof_uid and tnd_approval_status='1' and product_category_arabyos.pc_status='1' and TO_DAYS(tnd_due_date)>=TO_DAYS(now()) ".$sql_tnd_ck." and tnd_status='1' order by rand() LIMIT 5";
//echo $sql_t;
//$sql_testi="select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by testi_updated_date desc";
$res_t=mysqli_query($con, $sql_t);
if(mysqli_num_rows($res_t)>0){
?>
<div class="mb1 c3" style="
    border: 1px solid #ccc;
    border-radius: 5px;background: #fff;
">
<p class="bg bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
<div class=" bbx cln">
<p class=" bxh f2 color_coding" style="color: #c30000;">Related Tenders</p>



<div style="display: block;" id="d2">
<?php
$n=1;
while($row_t=mysqli_fetch_object($res_t)){
	$len=strlen($row_t->tnd_details);
?>
<?php if($n>1){ ?><br class="c3"><?php } ?>
<p class="lnh1">
<b class="cor lnh1"><a href="tender-details.php?id=<?php echo rand(1000,9999).md5($row_t->tnd_id); ?>" style="text-decoration:none;color:E84000"><?php echo $row_t->tnd_heading; ?></a><br>
<span class="cb2"><?php echo get_country_name($row_t->country); ?></span></b><br><?php echo substr($row_t->tnd_details,0,120); ?>
</p>
<?php if($len>120){	?><p class="c3 pa1 rm tr"><a href="tender-details.php?id=<?php echo rand(1000,9999).md5($row_t->tnd_id); ?>" target="_blank"> Read More...</a></p><?php } ?>
<?php 
$n++;
} ?>

</div>



<p class="c3"></p>
</div>
<p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
</div>
<?php } ?>



</div><p class="q_c3"><br></p>
<div class="c3 tenders-arab">
		<?php
			$sql_adv="select * from advertisement where adv_imagewidth='239' and adv_imageheight='186' and adv_status='1' order by rand() limit 1";
			$res_adv=mysqli_query($con, $sql_adv);
			if(mysqli_num_rows($res_adv)>0)
			{
				$row_adv=mysqli_fetch_object($res_adv);	
				?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" class="banner_img"/></a><?php
			}
			else
			{
		?>
				<img src="upload/advertisement/239-186-advertisement.png" width="239" height="186"/>
		<?php	}	?>

		</div >



</div>
</div>
<p class="q_c3"><br><br></p>
</div>
		<div id="bl_overlay_layer" class="layer" style="display:none"><div class="bl_overlay"></div></div><!--Footer starts here-->
        
</div>
        <?php 		include 'includes/footer.php';		?>
		<style>
		.tender-img
		{
			width:100%;
		}
		
			.tender-img
			{
				width:129%;
			}
			/*.c3
			{
				margin-left:178px;
			}*/
		.page2-header2-col1-row2
		{
			margin-left:28px;
		}
		.banner_img
		{
		width:257px; 
		height:251px;
		}
		.page-header-col2-intro
		{
			width:100% !important;
		}
		.page2-header2-col2
		{
			width:20.5% !important;
		}
		@media(max-width:640px)
		{
			.tender-img
			{
				width:106%;
			}
			.c3
			{
				margin-left:0px;
				margin-top:10px;
			}
			.banner_img
		{
		width:282px; 
		height:158px;
		}
		.page2-header2-col1-row2
		{
			margin-left:0px;
		}
		.page2-header2-col2
		{
			width:100% !important;
		}
		.page-header-col2-intro-texts
		{
			margin-left:6px;
		}
		}
		
		</style>
        
         <script>       
function showsidecate()
	{ 
	
	$('#showsideleft').html('<img src="http://arabyos.com/images/horizontal_loading.gif">');
	
	
   $.post("showidecate.php",{
	   
	   },    function(data){  	
	   				 $('#showsideleft').html(data);
 					 }); 
     }
	//showsidecate(); 
</script> 
