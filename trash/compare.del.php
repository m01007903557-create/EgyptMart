<?php
session_start();
session_cache_limiter(false);
require_once 'common.php';

$uid = '';
if(isset($_SESSION['uid_indm'])){
	$uid=$_SESSION['uid_indm'];
}


$location=array();
$location=getLocationInfoByIp();
//echo $location['country'];

function generateProdSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or pd_title LIKE ";
		}
		$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}
function generateSupplierSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or bnsprof_compname LIKE ";
		}
		$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}
function generateSupplierNameSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string.=" OR (user.fname LIKE '%" . $v ."%' OR user.lname LIKE '%" . $v. "%')";
		}else{
			$keywords_string.=" (user.fname LIKE '%" . $v ."%' OR user.lname LIKE '%" . $v. "%')";
		}
		//$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}

function generateBuyleadSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or br_pd_name LIKE '%".$v."%' or br_requirement LIKE '%".$v."%'";
		}
		else
		{
			$keywords_string=$keywords_string."br_pd_name LIKE '%".$v."%' or br_requirement LIKE '%".$v."%'";
		}
		$i++;
	}
	return $keywords_string;
}
function generateTenderSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or tnd_heading LIKE '%".$v."%' or tnd_details LIKE '%".$v."%'";
		}
		else
		{
			$keywords_string=$keywords_string."tnd_heading LIKE '%".$v."%' or tnd_details LIKE '%".$v."%'";
		}
		$i++;
	}
	return $keywords_string;
}

function generateAuctionSearchString($keywords)
{
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." or auc_heading LIKE '%".$v."%' or auc_details LIKE '%".$v."%'";
		}
		else
		{
			$keywords_string=$keywords_string."auc_heading LIKE '%".$v."%' or auc_details LIKE '%".$v."%'";
		}
		$i++;
	}
	return $keywords_string;
}



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title> Index new  <?php echo getSiteTitle(); ?></title>




<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="zomImage/js/jquery-photo-enlarger/css/jquery-photo-enlarger.css" rel="stylesheet" type="text/css">
<link href="css/slidebars.css" rel="stylesheet" type="text/css">
<link href="css/ctstyle.css" rel="stylesheet" type="text/css">
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
<script language="javascript" type="text/javascript" src="js/jquery-1.11.1.min.js"></script>

<script type="text/javascript">
function loadProductByCategory(page,id)
{
	$.post("ajax-file/loadProductByCategory.php",{page:page,id:id},    function(data){    $('#res').html(data); });
	//alert ("Category");
}
function loadProductBySubCategory(page,id)
{
	$.post("ajax-file/loadProductBySubCategory.php",{page:page,id:id},    function(data){    $('#res').html(data); });
	//alert ("SubCategory");
}
function refineProductBySubCategory(page,id)
{
	$.post("ajax-file/refineProductBySubCategory.php",{page:page,id:id},    function(data){    $('#final_result').html(data); });
	//alert ("refine");
}
</script>


<script type="text/javascript">
$(document).ready(function()
{
//	showProdSlide(1);
//	showSOSlide(1);
//	showTestProdSlide();

	<?php	if(isset($_GET['c'])){	?>
		loadProductByCategory(1,'<?php  echo $_GET['c']; ?>');
	<?php	}	?>
	<?php	if(isset($_GET['sc'])){	?>
		loadProductBySubCategory(1,'<?php  echo $_GET['sc']; ?>');
	<?php	}	?>
		
	$("#product_slider").als({
		visible_items: 4,
		scrolling_items: 1,
		orientation: "horizontal",
		circular: "yes",
		autoscroll: "yes",
		interval: 4000
	});
	$("#saleoffer_slider").als({
		visible_items: 4,
		scrolling_items: 1,
		orientation: "horizontal",
		circular: "yes",
		autoscroll: "yes",
		interval: 4500
	});
	$("#select_all").change(function(){  //"select all" change 
    var status = this.checked; // "select all" checked status
    $('.checkbox').each(function(){ //iterate all listed checkbox items
        this.checked = status; //change ".checkbox" checked status
    });
});
});
function delprod(deltype){
	if(deltype=='com'){
	
	}
}
</script>
<script type="text/javascript" src="js/jquery.als-1.6.js"></script>
<style>
/*************************************
 * generic styling for ALS elements
 ************************************/

.als-container {position: relative;	width: 100%;	margin: 0px auto;	z-index: 0;	}
.als-viewport {	position: relative;	overflow: hidden;	margin: 0px auto;	}
.als-wrapper {	position: relative;	list-style: none;	}
.als-item {	position: relative;	display: block;	text-align: center;	cursor: pointer;	float: left;	}
.als-prev, .als-next {	position: absolute;	cursor: pointer;	clear: both;	}
/*************************************
 * specific styling for #demo3
 ************************************/

#product_slider {		margin: 2px auto;	}
#product_slider .als-item {	margin: 0px 5px;	padding: 4px 0px;	min-height: 140px;	min-width: 122px;	text-align: center;	}
#product_slider .als-item img {	display: block;	margin: 0 auto;	vertical-align: middle;	}
#product_slider .als-prev, #product_slider .als-next {	top: 60px;	}
#product_slider .als-prev {	left: 20px;}
#product_slider .als-next {	right: 20px;}

#saleoffer_slider {		margin: 2px auto;	}
#saleoffer_slider .als-item {	margin: 0px 5px;	padding: 4px 0px;	min-height: 140px;	min-width: 120px;	text-align: center;	}
#saleoffer_slider .als-item img {	display: block;	margin: 0 auto;	vertical-align: middle;	}
#saleoffer_slider .als-prev, #saleoffer_slider .als-next {	top: 60px;	}
#saleoffer_slider .als-prev {	left: 20px;}
#saleoffer_slider .als-next {	right: 20px;}

.goog-te-gadget-simple .goog-te-menu-value{
  font-size:10px;
}
.carousel-inner .item .col-md-3.compared-box {
    margin-right: 2%;
}

.carousel-inner .item {
    margin-left: 10%;
}

</style>
</head>


    <body> 
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
      

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
<?php	include ("./includes/header_new.php");	?>

<p class="bt cb"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></p>

<div class="hm1 bbc">
<!--Menu-->


<!------------ LEFT SIDEBAR ------------------------- -->
    <?php //include_once 'index_leftsidebar.php'; ?>


<!--Menu-->
   <!--<div id="res" class="lft fl" style="float:left;width:82%;margin-left:8px;margin-right:0px;padding-left:5px;"></div>-->

<div class="comparemainblock">


    
<!------------------HSR |AVI ----CENTRAL CONTENT --------------------------->

<?php 
include_once 'compare_middle_content.php';
?>
</div>







<!-------------------------- RIGHT PANEL ------------------->
<!----- --------HIMMAT SINGH | AVINASH ------------------------>
<?php   // include_once 'index_rightsidebar.php';  ?>



<!-------------------------- / RIGHT PANEL CLOSE ---------------------->

<br>
<!--
<div class="fb-like-box" data-href="<?php echo get_page_settings(26); ?>" data-width="400" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false">
</div>
    <div>
    <a class="twitter-timeline" width="400" height="225" href="https://twitter.com/himmsrathore" data-widget-id="479248951439159296"></a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    </div> -->




</div>

<p class="cb"><br></p>

<!--------------------------------------- Footer ----------------------------->

<?php include 'index_footer.php'; ?>

<?php
//include 'includes/footer.php'; 
?>
<?php include 'index_footer_js.php'; ?>
