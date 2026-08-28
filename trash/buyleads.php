<?php ini_set('max_execution_time', 500000); //120 seconds ?>
<style>
	.tbl li {
    height: 90px;
    width: 90px;
    background-position: 0 -392px;
    position: relative;
    text-align: center;
    line-height: 14px;
    overflow: hidden;
}
.bpt {
    width: 780px !important;
    height: 265px;
    padding-left: 11px;
}
/* .mid.fl {
    width: 84% !Important;
    float: left;
} */
.ryt.fl {
/*    float: right;*/
}
.adi_bro{
	margin-right:10px;
}
@media(max-width:640px)
{
	.post-product-btn
	{
		margin-left:-53px !important;
	}
	.wd585
	{
		font-size:14px !important;
	}
	.lft1.lfl.fl.col-md-3.col-sm-3.col-xs-12
	{
		width:100% !important;
	}
	.bg.bp1.fl.d1.a6.bo.cate_allign.col-md-3.col-sm-3.col-xs-12
	{
		width:100% !important;
	}
	.q_bt1.w1.fl.w3.sub_menu_bar.col-md-9.col-sm-9.col-xs-12
	{
		width:100% !important;
	}
	
}
</style>
<?php
include "common.php";

if(isset($_COOKIE['loc_id']))
{
	$sql_br_ck=" and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city in (select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'  )))
	)";
	/*
	
	
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/
}
else
{
	$sql_br_ck=" and (
	
	(br_preferred_supplier_location='any')
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	
)";
	/*(br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html><head>

<title><?php echo getSiteTitle(); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">


<link href="css/eto-index-buy-1.css" rel="STYLESHEET" type="text/css">
<!--<link href="css/dir-style-8.css" rel="stylesheet" type="text/css">-->
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>

</head>
<body class="search-show-box-buyleads main-warpp">
<div class="q_hm1">
<!-- Header start Here::-->

<?php include "includes/header_new.php"; ?>

<!---->


<p class="q_c3"></p>


<!--New Header3 End --><p class="c3"><img alt="" src="images/zero.gif" height="1" width="1"></p>
<div class="lft1 lfl fl col-md-3 col-sm-3 col-xs-12">
<p class="bg bp1 fl d1 a6 bo cate_allign col-md-3 col-sm-3 col-xs-12"><img class="my-market" alt="" src="css/img/my-market.png"></p>
<a class="hmbrgr-menu" href="#">
    <span></span>
    <span></span>
    <span></span>
</a>
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
<div id='cssmenu' style="width: 100% !important;margin: 0 !important;padding: 0 !important;">
 <div id="showsideleft"></div>
</div>

</div>
<div class="main-content mid fl col-md-7 col-sm-7 col-xs-12">
    <div class="q_bt1 w1 fl w3 sub_menu_bar col-md-9 col-sm-9 col-xs-12">
<a class="cb2" href="manage-sell-offer.php" rel="nofollow">My Trade Offers</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="cb2" href="manage-buy-requirement.php" rel="nofollow">My Buy Requests</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="cb2" href="manage-buylead-alert.php" rel="nofollow">Manage Buy Requests Alerts</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="q_r" href="post-buy-req.php" rel="nofollow"><img src="images/zero.gif" alt="">Post Buy Requirements</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a class="q_r" href="post-auction.php" rel="nofollow"><img src="images/zero.gif" alt="">Post Auction</a>
</div>
    <center> 
        <div id="m4t1"></div>
    </center>
    <div class="bnbg bpt">
        <div class="bnp1 "><img src="images/zero.gif" class="bnbg bpm fl" alt="Buy Leads" border="1" height="98" width="90">
            <p class="bo esb  wd585">Easy Buying<br>
                <span class="f2 g5 lnh1">Send your purchase requirement &amp; receive quotations from verified suppliers. <a href="post-buy-req.php" class="g9 bo f1" onClick="recordInboundLink(this, 'Trac-Buy-home', 'Buy-form', 'startnow', 0);">Start Now</a></span><span class="j1"> ►</span></p>
        </div><p class="c3"></p>
            <div class="bnbg bpx fl tc bnm"><p class="f4 c2">Tell us what you need</p><br>
                <span class="bnp g9 lnh1">Complete <strong>a simple form</strong> and<br>let us know about your<br>buy requirement.</span>
            </div>
            <div class="bnbg bpx fl tc bnm"><p class="f4 c2">Supplier Matchmaking</p><br>
                <span class="bnp g9 lnh1">We'll find the <strong>best matched</strong> &amp;<br>verified <strong>suppliers</strong> for your <br> buy requirement.</span>
            </div>
            <div class="bnbg bpx fl tc"><p class="f4 c2">Receive Quotes</p><br>
                <span class="bnp g9 lnh1">Get customized Quotations<br><strong>from Qualified Suppliers</strong><br>via email or phone.</span>
            </div>

    </div>
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

	 $.post("ajax-file/buyLeads.php",{page:page,id:id},    function(data,status){ 
	 	console.log(status);
	 	//showsidecate();
	    //alert(data);
	    $('#res').html(data); });
}
function showLeadMain(page,id)
{
	$('ul#sidebarTabs li').removeClass('ho');
	$('#tabbb'+id).addClass('ho');
	
	 $(".xx").removeClass("on").addClass("off");
	 $("#aaa"+id).addClass("on").removeClass("off");
	 
	 $('#res').html('<div style="width:100%;padding-top:8%;" align="center"><img src="images/horizontal_loading.gif" alt="Loading"/></div>');

	 $.post("ajax-file/buyLeads.php",{page:page,id:id},    function(data,status){ 
	 	console.log(status);
	 	//showsidecate();
	 	setTimeout(function(){ showsidecate(); }, 500);
	    //alert(data);
	    $('#res').html(data); });
}
if($(window).width() < 992 ){
    $('#cssmenu').hide();
    $('#cssmenu li.has-sub > a').append('<span class="toggle-icon"><span class="plus">+</span><span class="minus">-</span></span>');
}
$('.hmbrgr-menu').click(function(ev){
    ev.preventDefault();
    $('#cssmenu').slideToggle();
});

$('#cssmenu li a .toggle-icon').click(function(ev){
    ev.preventDefault();
    $(this).parent().toggleClass('show-ch');
});
</script>
<div class="bx mid-btm-wrapper fl col-md-12">
<p class="c4 f4 g5 lbl web-nores">Latest Buy Requests</p>

<div class="tbl fl adi_bro col-md-3 col-lg-2 web-nores">

	<ul id="sidebarTabs">
    <?php
	$pc=0;
	//	$sql_cat="select * from product_category_arabyos where pc_id in(select distinct pc_parent_id from product_category_arabyos where pc_id in(select br_pc_id from buy_requirement where br_approval_status='1' and br_display_status='1' and br_status='1'))";
	
	//$sql_cat="select * from product_category_arabyos where pc_id in(select distinct pc_parent_id from product_category_arabyos where pc_id in(select pc_parent_id from buy_requirement,product_category_arabyos where br_pc_id=pc_id and br_approval_status='1' and br_display_status='1' ".$sql_br_ck." and  br_status='1')) limit 0,2 ";
	
	
	 $sql_check1="select pc_parent_id from buy_requirement,product_category_arabyos,user where br_u_id=usr_id and br_pc_id=pc_id and br_approval_status='1' and br_display_status='1' and  product_category_arabyos.pc_status='1' ".$sql_br_ck." and  br_status='1'";
     	$res_check1=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
     	while($data=mysql_fetch_array($res_check1)){
		
                   $pc_parent_id_arr[]=$data['pc_parent_id'];		
		
	}
	
	$ids = join("','",$pc_parent_id_arr); 
	
	//echo "<pre>";
//	    print_r($pc_parent_id_arr);
//	echo "</pre>";
//echo "<hr>";
//	echo $sql_check2="select distinct pc_parent_id from product_category_arabyos where pc_id in('$ids')";
//	$res_check2=mysql_query($sql_check1)or die('MySql Error' . mysql_error());
//     	while($data1=mysql_fetch_array($res_check2)){
//		
//                   $pc_parent_id_arr1[]=$data1['pc_parent_id'];		
//		
//	}
//	$ids1 = join("','",$pc_parent_id_arr1); 
		//	echo "<pre>";
//			    print_r($pc_parent_id_arr1);
//			echo "</pre>";
		//exit(); 
		
		 $sql_cat="select * from product_category_arabyos where pc_id in(select distinct pc_parent_id from product_category_arabyos where pc_id in('$ids')) and product_category_arabyos.pc_status='1'";
		$res_cat=mysql_query($sql_cat) or die('MySql Error' . mysql_error());
		
		$arr=array();
		$i=1;
		while($row_cat=mysql_fetch_object($res_cat)){ 
		 if($i==1) {	
		      $pc=$row_cat->pc_id;	
		  	       }
	    ?>
		<li onClick="showLead('1','<?php echo $row_cat->pc_id; ?>');" <?php if($i==1){ ?> class="ho"<?php } ?> id="tabbb<?php echo $i; ?>">
	    	<a class="bgf cm1 cp" id="kk1" style="background: url(&quot;upload/category/<?php echo $row_cat->pc_image;?>&quot;) no-repeat scroll 0% 0% transparent;"></a><?php echo $row_cat->pc_name; ?></a>
		</li>
	<?php
	$i++;
	}	?>
		
	</ul>
</div>
<?php
//webcast works start
if(isset($_COOKIE['loc_id']))
{
	
	$sql_br_ck=" and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='".$_COOKIE['loc_id']."'))))";
	/*
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/
}
else
{
	$sql_br_ck=" and (
	
	(br_preferred_supplier_location='any')
	or
	(br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	/*(br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}
/* -----Total count--- */
/*$query_pag_num = "SELECT count(*) AS count from buy_requirement,product_category_arabyos,user,business_profile where br_pc_id=pc_id and br_u_id=usr_id and usr_id=bnsprof_uid and br_approval_status='1' ".$sql_br_ck." and br_status='1' and br_display_status='1' and pc_parent_id in(select distinct pc_id from product_category_arabyos where pc_parent_id='".$pc."')"; // Total records*/
$query_pag_num = "select count(*) AS count from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand()";

$result_pag_num = mysql_query($query_pag_num);
$row = mysql_fetch_array($result_pag_num);
$count = $row['count'];
if ($count == 0) {
?>
<style type="text/css">
	.web-nores {display: none;}
</style>
<?php	
}
?>
<script type="text/javascript">
$('document').ready( function(){
  showLeadMain(1,<?php echo $pc; ?>);
});
</script>
<div id="res" class="col-md-9 col-lg-10"></div>
           
</div>







<p class="q_c3"><br></p>



</div>
<div class="mid right-content fl col-md-2 col-sm-2 col-xs-12 hide">
<div class="c3">
<?php
	$sql_adv="select * from advertisement where adv_imagewidth='239' and adv_imageheight='186' and adv_status='1' order by rand() limit 1";
	$res_adv=mysql_query($sql_adv);
	if(mysql_num_rows($res_adv)>0)
	{
		$row_adv=mysql_fetch_object($res_adv);	
		?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="239" height="186"/></a><?php
	}
	else
	{
?>
		<img src="upload/advertisement/239-186-advertisement.png" width="239" height="186"/>
<?php	}	?>
</div>
    <div class="ryt fl col-md-12">


<?php
$sql_testi="select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by testi_updated_date desc";
$res_testi=mysql_query($sql_testi);
if(mysql_num_rows($res_testi)>0){
?>
<div class="mb1 c3">
<p class="bxt"><img alt="" src="images/zero.gif" height="1" width="1"></p>
<div class="bbx cln">
<p class="bxh f2 bo">Buyers Speak</p>



<div style="display: block;" id="d2">
<?php
$n=1;
while($row_testi=mysql_fetch_object($res_testi)){
	$len=strlen($row_testi->testi_details);
?>
<?php if($n>1){ ?><br class="c3"><?php } ?>
<p class="lnh1">
<img alt="" class="fl pt1 pr1" src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>" height="76" width="76">
<b class="cor lnh1"><?php echo $row_testi->testi_name; ?>,<br>
<span class="cb2"><?php echo get_country_name($row_testi->testi_cn_id); ?></span></b><br><?php echo substr($row_testi->testi_details,0,120); ?>
</p>
<?php if($len>120){	?><p class="c3 pa1 rm tr"><a href="testimonial.php" target="_blank"> Read More...</a></p><?php } ?>
<?php 
$n++;
} ?>

</div>



<p class="c3"></p>
</div>
<p class="bg bxb"><img alt="" src="images/zero.gif" height="1" width="1"></p>
</div>
<?php } ?>



</div>
</div>
<p class="q_c3"><br><br></p>
</div>
		<div id="bl_overlay_layer" class="layer" style="display:none"><div class="bl_overlay"></div></div><!--Footer starts here-->
        
        <?php 		include 'includes/footer.php';		?>
		
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
        