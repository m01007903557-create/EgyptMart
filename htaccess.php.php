<?php
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
	//echo 'product searching';
	trim($keywords);
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{ 
		if($i>0)
		{
			$keywords_string=$keywords_string." AND pd_title LIKE ";
		}
		$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}

function generateProdSearchString_pro_sup($keywords)
{trim($keywords);
	//echo 'product searching';
	
	$i=0;
	$keywords_string="";
	$key_array=explode(" ",$keywords);
	foreach($key_array as $v)
	{
		if($i>0)
		{
			$keywords_string=$keywords_string." AND bnsprof_compname LIKE ";
		}
		$keywords_string=$keywords_string."'%".$v."%'";
		$i++;
	}
	return $keywords_string;
}

function generateSupplierSearchString($keywords)
{
	//echo 'supllier searching';

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

function generateBuyleadSearchString($keywords)
{
	//echo 'lead searching';
	
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
	//echo 'tender searching';
	
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
<title><?php echo getSiteTitle(); ?></title>




<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/ctstyle.css?t=<?php echo rand(); ?>" rel="stylesheet" type="text/css">

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
<style>
.prdt-sup-ctrl span{     font-size: 11px!important;}
.text-right button.btn.btn-default.btn-xs {
        padding: 1px 4px!important;
}

#search_result .big-img-box .zoomthis img  ,.seach-page-inn .big-img-box .zoomthis img{
        height: auto !important;
        max-height:253px;


    }
    .box .zoomthis {
        position: relative;
        top: 50%;
        left: 50%;
        width: 100%;
        transform: translate(-50%,-50%);
    }
    .inner-search-right-img {
        position: absolute;
        bottom: 5px;
        right: 5px;
    }
    .wrapper-product-searchright {
        position: relative;
    }
    .seach-page-inn .box {height: 253px;}
   
#search_result figure.box{width:100%;}
.txt-dark-gray a {color: #302670 !important;}
.ar-box-1 .sub-box+img+b {color: gray !important;}

.box .zoomthis {
    position: relative !important;
    top: 50%;
    left: 50%;
    width: 100%;
    transform: translate(-50%,-50%);
}

@media screen and (max-width: 1024px) and (min-width: 990px)
.ar-box-1 .box-1 .box {
    float: none !important;
}
</style>

<?php
//echo "success"; die;
?>

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
	
	if($.trim($("#product_slider").html()) != '&nbsp;') {
		$("#product_slider").als({
			visible_items: 4,
			scrolling_items: 1,
			orientation: "horizontal",
			circular: "yes",
			autoscroll: "yes",
			interval: 4000
		});
	}
	if($.trim($("#saleoffer_slider").html()) != '&nbsp;') {
		$("#saleoffer_slider").als({
			visible_items: 4,
			scrolling_items: 1,
			orientation: "horizontal",
			circular: "yes",
			autoscroll: "yes",
			interval: 4500
		});
	}
});

</script>
<script type="text/javascript" src="js/jquery.als-1.6.js"></script>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="css/ctstyle.css" rel="stylesheet" type="text/css">
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css">


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
.bbc {
    width: 1260px;
    margin: 0 auto;
}    


@media(max-width:1024px){
    
    .right-section-search-buylead{position: relative!important;}
    .photo{position: static!important;}
    .search-show-box-buyleads.products-categories-listing #res{width: calc(100% - 0) !important;}
    #right-image {max-width: 215px !important;}
      
     
    
}

@media  (min-width:768px) and (max-width:1024px){
		  /* div.lft.ser-mid {width: calc(100% - 220px) !important;}*/
		   .hm1.bbc.search-wrap{    width: calc(100% - 233px) !important;}
	}
#mask{width: 100%!important;}
 .small-box table tr td img{ height:auto!important;width: auto !important;max-width: 100%;padding:5px;border:1px solid #8a8a8a;}
.box-under-twoimage .padding-0 {
    width: 50%;
}
.box-under-twoimage > div {
    display: flex;
    justify-items: center;
    align-items: baseline;
    border:1px solid #8a8a8a;
    
}
.wrapper-product-searchright{padding:5px;}
.small-box {
    width: 100%;
    display: inline-block;
}
.box-under-twoimage img.photo {
    max-width: 100%;
    max-height: 77px;
    /*width: 100%;*/
    width: auto;
    margin-right: auto;
    margin-left: auto;
    display: table;
}
@media(max-width:1023px){
    
 html #search_result .box-3{ width:auto!important;float: right;margin-top: 20px;} 
 .wrapper div.lft.ser-mid .row .ar-box-1{padding-bottom: 10px!important;}
    
}
@media(max-width:480px){
    div.lft.ser-mid .row .ar-box-1 + .small-box{width:100%!important;}
    div.lft.ser-mid .row .box-3 .ar-box-1{max-width: 100%!important;}
    .ar-mid-box .box-3 .small-box{display: block!important;margin-bottom: 15px;}
	.ar-mid-box .box-3 .hidden-xs.small-box{display: none !important;}

	button.btn.btn-sm.btn-warning.border-radius-0.btn-enquiry {
    margin-top: 45px !important;
	}
}

</style>
	<script type="text/javascript">
	$(window).load(function() {
    	$(".loader").hide();
	 	$(".yahoo_loader_image").hide();
	});
	</script>
</head>


<body id="search_result_page"> 
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

<!-- <p class="bt cb"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></p> -->
<div class="seach-page-inn">
<div class="hm1 bbc search-wrap">
 

   <div id="res" class="lft fl" >
   
   
<!--Menu-->

<link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
<!------------ LEFT SIDEBAR ------------------------- -->
    <?php include_once 'index_leftsidebar.php'; ?>


<?php
if(!isset($_GET['rctyp']) ) echo "</div>";
?>
  

<!--Menu--> 
   </div>
<div class="lft fl ser-mid" >

<!--<div class="yahoo_loader_image"></div>-->
    
<!------------------HSR |AVI ----CENTRAL CONTENT --------------------------->

<?php if($_GET['rctyp']=='Suppliers'){ ?>
	<link type="text/css" rel="stylesheet" href="css/main-v2.css">
<link href="css/dir-style-8.css" type="text/css" rel="stylesheet">
<link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
<link href="css/bl_form_temp5.css" rel="stylesheet" type="text/css">

<script src="js/jquery.colorbox.js"></script>
<link href="css/colorbox.css" type="text/css" rel="stylesheet">
<script>
			 $(document).on('click', '.ajax', function () {
					// $('#colorbox').remove();
					//$('#cboxOverlay').remove();
					$.colorbox(
							{
								href: $(this).attr('href'), open: true, iframe: true, width: '750px', height: '600px',

							}
					);
					return false;
				});
			$(document).ready(function(){
				//Examples of how to assign the ColorBox event to elements

				$(".inline").colorbox({inline:true, width:"50%"});
				//Example of preserving a JavaScript event for inline calls.
				$("#click").click(function(){ 
					$('#click').css({"background-color":"#f00", "color":"#fff", "cursor":"inherit"}).text("Open this window again and this message will still be here.");
					return false;
				});
			});
		</script>
<style>
.zoom-box .viewer-box {
    z-index: 1000000;
}
.g9 {
	font-size: 13px;
	background: white;
	padding: 17px;
}

#supplierid img{border: 1px solid #000;}
#supplierid td{
	width:100px;
	max-width:100px;
    word-wrap:break-word;}
	/* Smartphones (portrait and landscape) ----------- */
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
.my-container{
	width:344px !important;
	border: 1px solid #c2e6fe !important;
    padding: 14px !important;
    background: #fff !important;
	}
	.col-md-2{
	padding-left: 7px!important;
	padding-right: 7px!important;
	}
}

/* Desktops and laptops ----------- */
@media only screen
and (min-width : 1224px) {
.my-container{
	width:850px !important;
	border: 1px solid #c2e6fe !important;
    padding: 14px !important;
    background: #fff !important;
    margin-left: auto;
    margin-right: auto;
	}
.middle-part {
	margin-left: auto;margin-right: auto;
}
	}
}
 
/* Large screens ----------- */
@media only screen
and (min-width : 1824px) {
.my-container{
	width:1000px !important;
	border: 1px solid #c2e6fe !important;
    padding: 14px!important;
    background: #fff !important;
	
	}
}
.cat_image_div{
    height: 100px;
    width: 100%;
    border: 1px solid #c3bdbd !important;
}
.three-pics1 .col-md-2 {
    padding: 0px 5px;
}
.cat_image{ width:100%; height:100%; object-fit: contain;}

 

@media only screen and (max-width: 768px) {
.cat_image{ width:100% !important;  height:100% !important; object-fit: contain;}
.cat_image_div{border: 1px solid #cac6c6;
height: 80px !important;
width:80px !important;
}
.three-pics1, .col-md-2{ width:100px !important; padding:0px !important}
}
.page-header-col2-intro-texts .post-product-btn {
	font-size: 14px !important;
}
.page-header-col2-intro-texts .post-product-btn small {
	font-size: 10px !important;
}
.ps-15{
    padding:15px!important;
}

 @media(max-width:1024px){

        #changeLocation { top: -5px !important;}
        .postRequirement .girl-img{left: -132px !important;z-index: 99999;}
        .page-header-col1-row1-col1_row{right: -24px;}
        .scontentbox h2 {white-space: nowrap;}
    }

</style>


<?php
if(isset($_COOKIE['loc_id']))
{
	$sql_pd_ck=" and (
	(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
	or 
	(pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	or
(pd_preferred_buyer_location='my_city'  and pd_uid in(select distinct bnsprof_uid from business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id where c.ct_cn_id='".$_COOKIE['loc_id']."')))";
	/*
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
	*/
}
else
{
	$sql_pd_ck=" and (
	
	(pd_preferred_buyer_location='any')
	or
	(pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	)";
	
	/*(pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country=(select cn_id from country where cn_code='".$location_geo_country."')))
	or
	or
	(pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')))
	*/
}

	if (isset($_GET['page'])) {
		$page = $_GET['page'];
	} else {
		$page = 1;
	}
	$newkw  = $_GET['keywords'];
	$supptotalpage = 50;
	$suppstartpage = 0;
	if ($page > 1) {
		$supplimit = ($page - 1) * $supptotalpage;
		$suppsetLimit = " LIMIT " . $supplimit . "," . $supptotalpage;
	} else {
		$supplimit = $suppstartpage;
		$suppsetLimit = " LIMIT " . $supplimit . "," . $supptotalpage;
	}
	// $sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where bnsprof_compname LIKE '%" . $newkw . "%' and pm.expiry_date > ". time() ." and pc_status='1' and pd_status='1' GROUP BY pd_uid " .$suppsetLimit;
	if (isset($_POST['country_id'])) {
		$p = 1;
		foreach ($_POST['country_id'] as $key => $value) {
			if ($p == 1) {
				$cntryval1 .= " and (country.cn_name = '" . $value . "'";
			} else {
				$cntryval1 .= " or country.cn_name = '" . $value . "'";
			}
			$p++;
		}
		$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category _arabyos pc ON p.pd_subcat_id =pc.pc_id INNER JOIN user on user.usr_id = p.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON bf.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where ((bnsprof_compname LIKE '%" . $newkw . "%') or (pd_title LIKE '%" . $newkw . "%')) " . $cntryval1 . ") and pm.expiry_date > ". time() ." and pc_status='1' and pd_status='1' GROUP BY pd_uid ORDER BY pm.icon_id  ASC  " .$suppsetLimit;
		// echo $sql_comp;exit;
		
	}else if (isset($_POST['state_id'])) {
		if (count($_POST['state_id']) > 1) {
			foreach ($_POST['state_id'] as $key => $value) {
				$stateid .= $value . ',';
			}
			$stateid = rtrim($stateid, ',');
		} else {
			$stateid = isset($_POST['state_id'][0]) ? $_POST['state_id'][0] : '';
		}
		$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id INNER JOIN user on user.usr_id = p.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON bf.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where ((bnsprof_compname LIKE '%" . $newkw . "%') or (pd_title LIKE '%" . $newkw . "%'))  and bnsprof_state IN (" . $stateid . ")  and pm.expiry_date > ". time() ." and pc_status='1' and pd_status='1' GROUP BY pd_uid ORDER BY pm.icon_id  ASC  " .$suppsetLimit;
		// echo $sql_comp;exit;
	}else{
		$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where ((bnsprof_compname LIKE '%" . $newkw . "%') or (pd_title LIKE '%" . $newkw . "%')) and pm.expiry_date > ". time() ." and pc_status='1' ".$sql_pd_ck." and pd_status='1' GROUP BY pd_uid ORDER BY pm.icon_id  ASC " .$suppsetLimit;
	}
	// echo $sql_comp;exit;
$res_comp=mysqli_query($con, $sql_comp);
//echo $sql_comp;

$getSearchCount = $res_comp->num_rows;
if ($getSearchCount > 0) {

$res_comp_cat=mysqli_query($con, $sql_comp);
$row_s_cat=mysqli_fetch_object($res_comp_cat);

$category_p_id = $row_s_cat->pd_subcat_id;


	$iMainParentId="SELECT * FROM `product_category_arabyos` WHERE pc_id = '$category_p_id' and pc_status='1'";
    $iMainParentIdqueryResult = mysql_query($iMainParentId);
	$Results = mysql_fetch_object($iMainParentIdqueryResult);
	$category_p_id = $Results->pc_parent_id;
    
    
	

$related_id = array();
?>
<div class="middle-part" style="margin-left:23px">
<button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize <?php echo $pc_id; ?>" style="border-top:2px solid #ff7519 !important;border:0px;font-weight:700; z-index: 9999;">موردون</button>
<button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize " style="background-color: #F5F7FA"><a href="products.php?c=<?php echo md5($category_p_id); ?>" style="color:#000;font-weight: 700; z-index: 99999;" target="_new">منتجات</a></button>
</div>
<?php 
while($row_comp = mysqli_fetch_object($res_comp)){
	
$iCountry = user_info($row_comp->bnsprof_uid,'country'); 
 


$iCounsql="select cn_name from country where cn_id = '".$iCountry."' ";
$resultCount = mysqli_query($con, $iCounsql);
$rowCountry=mysqli_fetch_object($resultCount);

 

///Lastest DOne BY SHail
$dataNewAbc = mysql_query("select * from plan_member_id where  b_id='".$row_comp->bnsprof_id."' ");

$rowNewAb=mysql_fetch_object($dataNewAbc);
///Lastest DOne BY SHail

	
$dataMem = mysql_query("select * from smembership_icon_plan where mp_id = '".$rowNewAb->icon_id."'");
$rowMem=mysql_fetch_object($dataMem)	;
	
	
?>
<div class="my-container">
<div class="row">
<div class="col-md-1" style="width:4%">
 <?php 
$strT = stripslashes($row_comp->bnsprof_compname);
$strreT = '<span style="color:orange">'.$newkw.'</span>';
$strT = str_ireplace($newkw,$strreT,$strT);
 
 if($rowMem->mst_icon!='') { 
 
	
 ?>
            <img  src="admin/images/<?php echo $rowMem->mst_icon; ?>"  title="<?php echo $rowMem->mst_name; ?>" width="30px" height="30px;">
            <?php } ?></div>
            <div class="col-md-6">
            <a style="" <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank">
			<span class="city-country" style="font-weight:900;"><?php echo $strT; ?></a><br>
        <?php  echo $rowCountry->cn_name; ?>,  <?php echo get_city_name($row_comp->bnsprof_city); ?>    
           </div>
</div>
    
    
<div class="row" style="padding-top: 10px;">
<div class="three-pics1">
<div class="col-md-2">
<?php 
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/myproduct/thumb/'.$row_comp->pd_image)){ 
				$strI = stripslashes($row_comp->pd_title);
				$strreT = '<span style="color:orange">'.$newkw.'</span>';
				$strI = str_ireplace($newkw,$strreT,$strI);
			if($row_comp->pd_image !=""){
				
				
			?>
            <div style="width:100%;">
            <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank">
            <div class="cat_image_div zoom-box">
            <img src="/upload/myproduct/<?php echo $row_comp->pd_image; ?>" text="<?php echo $row_comp->pd_title; ?>" title="<?php echo $row_comp->pd_title; ?>" class="cat_image"></div></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php echo wordwrap(substr($row_comp->pd_title, 0,30), 17, "<br/>", true); ?></span>
            </div>
            <?php } 
			else
			{
			?>
             <!-- <div style="width:100%;">
            <a href="company/products.php?c=<?php //echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank">
			<div class="cat_image_div zoom-box"><img src="/images/noimage.jpg"  class="cat_image" title="<?php //echo $row_comp->pd_title; ?>"></div></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php //echo substr($row_comp->pd_title, 0,15); ?></span>
            </div> -->
            <?php	
			} }
			?>
</div>
<div class="col-md-2" >
<?php
			$banner_details ="select * from company_banner where cb_bnsprof_id = '".$row_comp->bnsprof_id."' AND cb_status='1' LIMIT 1";
			$result_banner_details = mysqli_query($con, $banner_details);
			$row_result_banner_details=mysqli_fetch_object($result_banner_details);
			//echo $_SERVER['DOCUMENT_ROOT'].'/upload/company_banner/'.$row_result_banner_details->cb_image;
			?>
            <?php if (file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/company_banner/'.$row_result_banner_details->cb_image)){ 
			if($row_result_banner_details->cb_image !=""){
				
				
			?>

<div style="width:100%;">
<a href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank">
<div class="cat_image_div zoom-box">
<img src="/upload/company_banner/<?php echo $row_result_banner_details->cb_image; ?>"  class="cat_image" title="<?php echo $row_comp->bnsprof_compname; ?>" text="<?php echo $row_comp->bnsprof_compname; ?>">
</div></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php echo substr($row_comp->bnsprof_compname, 0,30); ?></span>
</div>
  <?php } 
			else
			{
			?>
             <!-- <div style="width:100%;">
          <a href="company/profile.php?c=<?php //echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank">
		  <div class="cat_image_div zoom-box"><img src="/images/noimage.jpg"  title="<?php //echo $row_comp->bnsprof_compname; ?>" class="cat_image"></div></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php //echo substr($row_comp->bnsprof_compname, 0,15); ?></span>
			</div> -->
            <?php	
			} }
			?>
            </div>
<div class="col-md-2" >
<?php
			$user_details ="select * from website_content where wc_usr_id = '".$row_comp->bnsprof_uid."' ";
			$result_user_details = mysqli_query($con, $user_details);
			$row_result_user_details=mysqli_fetch_object($result_user_details);
			$abtsql=mysqli_query($con,"select * from about_us,profile_heading where abtus_ph_id=ph_id and abtus_wc_id='".$row_result_user_details->wc_id."' AND ph_id='1'"); 
			$abtrow=mysqli_fetch_object($abtsql);
			$imageprofilepath = '/upload/myprofile/'.$abtrow->abtus_image;
			if (file_exists($_SERVER['DOCUMENT_ROOT'].$imageprofilepath))
			{
			if($abtrow->abtus_image !=""){
			?>
             <div style="width:100%;">
            <a <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank">
            <div class="cat_image_div zoom-box"><img src="<?php echo $imageprofilepath; ?>" class="cat_image" title="<?php echo $abtrow->abtus_desc; ?>" text="<?php echo $abtrow->abtus_desc; ?>"></div> </a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php echo substr($abtrow->abtus_desc, 0,35); ?></span>
            </div>
            <?php } 
			else
			{
			?>
           <!-- <div style="width:100%;">
            <a <?php 
            //if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ 
            ?>href="company/profile.php?c=<?php //echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php 
            //} ?> target="_blank"><div class="cat_image_div zoom-box">
            <img src="/images/noimage.jpg"   title="<?php //echo $abtrow->abtus_desc; ?>" class="cat_image"></div>
            </a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php //echo substr($abtrow->abtus_desc, 0,35); ?></span>
            </div> -->
            <?php	
			} } 
			else { ?>
            <!--<a <?php //if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php //echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php //} ?> target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title="<?php //echo $abtrow->abtus_desc; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php //echo substr($abtrow->abtus_desc, 0,35); ?></span>-->
			<?php } $related_id[] = $row_comp->bnsprof_uid;?>
</div>
</div>

<div class="col-md-6">
				<b>
              <?php
				$sql_wc="select * from website_content where wc_usr_id='".$row_comp->bnsprof_uid."'";
				$res_wc=mysqli_query($con, $sql_wc);
				$row_wc=mysqli_fetch_object($res_wc);
				$str = stripslashes($row_wc->wc_homepage_key_desc);
				$strre = '<span style="color:orange">'.$newkw.'</span>';
				$str = str_ireplace($newkw,$strre,$str);
				
				echo $str;
				?>
			 </b>
             <br>
             <a href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank" class="fc td j1 g4 bg im3 lht pb2">About Us</a> &nbsp;&nbsp;<a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank" class="fc td j1 g4 bg im4 pb2 lht">شاهد المنتجات</a>
             <br>
             <b>Main Buisness : </b>
           <?php
		    $string_buisnessmain = "";
            $user_products ="select * from products where pd_uid = '".$row_comp->bnsprof_uid."' GROUP by pd_subcat_id";
			$result_user_products = mysqli_query($con, $user_products);
			while($row_user_products = mysqli_fetch_object($result_user_products)){
				$pd_subcat_id = $row_user_products->pd_subcat_id;
				$user_products_category ="select * from product_category_arabyos where pc_id = '".$pd_subcat_id."'";
				$result_products_category = mysqli_query($con, $user_products_category);
				$rowBuisness_main = mysqli_fetch_object($result_products_category);
				$string_buisnessmain .= $rowBuisness_main->pc_name.",";
			}
			$string_buisnessmain = rtrim($string_buisnessmain,",");
		   ?>
           <?php echo substr($string_buisnessmain, 0, 70); ?>...
           <?php
		   $buisness_typeexpode = explode(",",$row_comp->bnsprof_businesstype);
		   $string_buisness = "";
		   foreach($buisness_typeexpode as $databuisness)
		   {
			    $buisnsql="select * from business_type_arabyos where bsntyp_id = '".$databuisness."' GROUP BY bsntyp_title";
				$resultCountbuis = mysqli_query($con, $buisnsql);
				$rowBuisness=mysqli_fetch_object($resultCountbuis);
				$string_buisness .= $rowBuisness->bsntyp_title.",";
		   }
		   $string_buisness = rtrim($string_buisness,",");
		   ?>
           <br>
           <b>Buisness Type : </b><?php echo substr($string_buisness, 0, 70); ?>...
           <br>
            <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm']!='' &&  $_SESSION['uid_indm']!=$row_comp->bnsprof_uid){ ?>
        <a class="bg ima z1 e4 hl a2 td bo f2 wdt c6 td ajax" rel="nofollow" href="sendenquiry-form.php?id=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>">إرسل إستفسارك</a>
        	<?php } ?>
           	<p class="g1"> <b>Address:</b>&nbsp;
			  <?php if($row_comp->bnsprof_address1!=''){ echo $row_comp->bnsprof_address1.", "; } ?>
              <?php if($row_comp->bnsprof_address2!=''){ echo $row_comp->bnsprof_address2.", "; } ?>
              <?php if($row_comp->bnsprof_city!='0'){ echo get_city_name($row_comp->bnsprof_city).", "; } ?>
              <?php if($row_comp->bnsprof_state!='0'){ echo get_state_name($row_comp->bnsprof_state)." "; } ?>
              <br>
			  <?php
              $user_details ="select * from user where usr_id = '".$row_comp->bnsprof_uid."'";
			  $result_user_details = mysqli_query($con, $user_details);
			  $rowBuisness_user_details = mysqli_fetch_object($result_user_details);
			  ?>
              <?php //if($row_comp->bnsprof_mobile2!='' || $row_comp->bnsprof_mobile3!='' || $row_comp->bnsprof_mobile4!=''  || $row_comp->bnsprof_ph1!='' || $row_comp->bnsprof_ph2!='' || $row_comp->bnsprof_ph3!='' || $row_comp->bnsprof_ph4!=''){
			  if($row_comp->bnsprof_mobile2!='' || $row_comp->bnsprof_mobile3!='' || $row_comp->bnsprof_mobile4!='' || $rowBuisness_user_details->mobile1){
			  ?>
              <b><strong style="color:#2923ae; font-weight: bold;"> Phone:</strong></b>&nbsp;
			  <?php if($rowBuisness_user_details->mobile1!=''){ ?>
			  
			  <?php if (($_SESSION['uid_indm'] ) != '') { ?>
              <a href="tel:+<?php echo $rowBuisness_user_details->mobile1; ?>"><span id="pns1">+</span><?php echo $rowBuisness_user_details->mobile1; ?></a>
              <?php } else {?> 
                                               <a class="a_tel" href="/sign-in.php#loginform">
                                                   Show number
                                               </a>
                                            <?php } ?>
                                            
              <img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <!--<?php if($row_comp->bnsprof_ph1!=''){ ?>
              <a href="tel:+<?php //echo $row_comp->bnsprof_ph1; ?>"><span id="pns1">+</span><?php //echo $row_comp->bnsprof_ph1; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <?php if($row_comp->bnsprof_ph2!=''){ ?>
              <a href="tel:+<?php //echo $row_comp->bnsprof_ph2; ?>"><span id="pns1">+</span><?php //echo $row_comp->bnsprof_ph2; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <?php if($row_comp->bnsprof_ph3!=''){ ?>
              <a href="tel:+<?php //echo $row_comp->bnsprof_ph3; ?>"><span id="pns1">+</span><?php //echo $row_comp->bnsprof_ph3; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <?php if($row_comp->bnsprof_ph4!=''){ ?>
              <a href="tel:+<?php //echo $row_comp->bnsprof_ph4; ?>"><span id="pns1">+</span><?php //echo $row_comp->bnsprof_ph4; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>-->
              <?php if($row_comp->bnsprof_mobile2!=''){ ?>
              <a href="tel:+<?php echo $row_comp->bnsprof_mobile2; ?>"><span id="pns1">+</span><?php echo $row_comp->bnsprof_mobile2; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
              <?php if($row_comp->bnsprof_mobile3!=''){ ?>
              <a href="tel:+<?php echo $row_comp->bnsprof_mobile3; ?>"><span id="pns1">+</span><?php echo $row_comp->bnsprof_mobile3; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
              <?php if($row_comp->bnsprof_mobile4!=''){ ?>
              <a href="tel:+<?php echo $row_comp->bnsprof_mobile4; ?>"><span id="pns1">+</span><?php echo $row_comp->bnsprof_mobile4; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20">
              <?php } ?>
              <?php	}	?>
              <span id="sms1" class="tool-tip-lg soff"><span class="bg nr toolarw"></span><span class="txt-call">&nbsp;</span></span><br>
              <?php if($row_comp->bnsprof_website_alt != ''){	?>
              <b>Website:</b> <a target="_blank" href="<?php echo $row_comp->bnsprof_website_alt; ?>"><?php echo $row_comp->bnsprof_website_alt; ?></a><br>
              <?php } ?>
            </p>
</div>
</div>
</div>



      
      <br>
	  <?php }
	   } else {
		?>
		<table cellspacing="0" cellpadding="0" border="0" align="CENTER" width="100%">
			<tr style="width:100%; text-align:left;">
				<td valign="TOP" style="width:100%"><div class="sor"> <b class="cb1"><?php echo $_GET['keywords']; ?></b> للأسف تعذر إيجاد نتائج لكلمة </div><div class="sug"><b> : المقترحات </b><ul><li>أكتب الكلمة بحروف صحيحة باللغة العربية الفصحى  </li><li>جرب إستخدام كتابة الكلمة بتعبير آخر</li><li>لاتستخدم جملة طويلة للبحث بحد أقصى مقطعين لنتائج دقيقة </li><li>لاتستخدم رموز خاصة مثل الشرطة والنقطة وخلافة  </li><li> (e.g., 20x25 mm tone &nbsp;&nbsp;tiles)  لاتستخدم كلمات بحث خاصة مثل الكلمات المحددة تلك</li></ul> </div><div style="clear: both;"><br><br></div></td>
			</tr>
		</table>
	<?php }
if(isset($_GET['keywords'])){
	$newkw  = $_GET['keywords'];
	
	if (isset($_POST['country_id'])) {
		$p = 1;
		foreach ($countryid as $key => $value) {
			if ($p == 1) {
				$cntryval1 .= " and (country.cn_name = '" . $value . "'";
			} else {
				$cntryval1 .= " or country.cn_name = '" . $value . "'";
			}
			$p++;
		}
		$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id INNER JOIN user on user.usr_id = p.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON bf.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where bnsprof_compname LIKE '%" . $newkw . "%' " . $cntryval1 . ") and pm.expiry_date > ". time() ." and pc_status='1' and pd_status='1' GROUP BY pd_uid ";
		
	}else if (isset($_POST['state_id'])) {
		if (count($_POST['state_id']) > 1) {
			foreach ($_POST['state_id'] as $key => $value) {
				$stateid .= $value . ',';
			}
			$stateid = rtrim($stateid, ',');
		} else {
			$stateid = isset($_POST['state_id'][0]) ? $_POST['state_id'][0] : '';
		}
		$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id INNER JOIN user on user.usr_id = p.pd_uid INNER JOIN country ON user.country = country.cn_id INNER JOIN city ON bf.bnsprof_city = city.ct_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where bnsprof_compname LIKE '%" . $newkw . "%'  and bnsprof_state IN (" . $stateid . ")  and pm.expiry_date > ". time() ." and pc_status='1' and pd_status='1' GROUP BY pd_uid ";
		// echo $sql_comp;exit;
	}else{
		$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where bnsprof_compname LIKE '%" . $newkw . "%' and pm.expiry_date > ". time() ." and pc_status='1' ".$sql_pd_ck." and pd_status='1' GROUP BY pd_uid ";
	}

	$run_query = mysql_query($sql_comp) or die(mysql_error());
	$getSearchCount = mysql_num_rows($run_query);
		  
	$countRec = $page*50;
	$pages = $page+1;
	if ($countRec < $getSearchCount) {
		echo '<div class="col-lg-12 text-center" style="padding:30px;"><a href="https://arabyos.com/suppliers_search.php?rctyp=' . $_GET['rctyp'] . '&keywords=' . $_GET['keywords'] . '&page=' . $pages . '"><button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px; font-weight:bolder;">Display More Services </button></a></div>';
		}
}
	  
							?>
<?php }else{ 
	include_once 'index_middle_content.php';
 } ?>


   	<div class="als-container" id="product_slider" style="height:250px;">&nbsp;</div>

	<div class="als-container" id="saleoffer_slider" style="height:250px;">&nbsp;</div>


</div>




<?php if($_GET['rctyp']=='Suppliers'){echo '</div>';}?>


<!-------------------------- RIGHT PANEL ------------------->
<!----- --------HIMMAT SINGH | AVINASH ------------------------>
<?php    include_once 'index_rightsidebar.php';  ?>



<!-------------------------- / RIGHT PANEL CLOSE ---------------------->

<br>
<!--
<div class="fb-like-box" data-href="<?php //echo get_page_settings(26); ?>" data-width="400" data-colorscheme="light" data-show-faces="true" data-header="false" data-stream="false" data-show-border="false">
</div>
    <div>
    <a class="twitter-timeline" width="400" height="225" href="https://twitter.com/himmsrathore" data-widget-id="479248951439159296"></a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
    </div> -->




</div>
</div>
<p class="cb"><br></p>

<!--------------------------------------- Footer ----------------------------->
		<?php if($_GET['rctyp']=='Products'){ ?>
		<script type="text/javascript">
			// jQuery(document).ready(function($) {
			// $(window).on("load", function() {
			// 	 window.open('https://www.arabyos.com/manage-selloffer-alert.php', 
   //                       'newwindow', 
   //                       'width=700,height=400,menubar=true'); 
			// });
			// });
		</script>
		<?php }else if($_GET['rctyp']=='buy_lead'){ ?>
		<script type="text/javascript">
			// $(window).on("load", function() {
			// 	 window.open('https://www.arabyos.com/manage-buylead-alert.php', 
   //                       'newwindow', 
   //                       'width=700,height=400,menubar=true'); 
			// });
		</script>
		<?php } else if($_GET['rctyp']=='tender'){ ?>
		<script type="text/javascript">
			// $(window).on("load", function() {
			// 	 window.open('https://www.arabyos.com/manage-tender-alert.php', 
   //                       'newwindow', 
   //                       'width=700,height=400,menubar=true'); 
			// 	 window.open('https://www.arabyos.com/manage-auction-alert.php', 
   //                       'newwindow', 
   //                       'width=700,height=400,menubar=true'); 
			// });
		</script>
		<?php } else{?>
			<script type="text/javascript">
			$(window).on("load", function() {
				if ($(window).width()>768) {
				 // window.open('https://arabyos.com/sign-in.php', 
     //                     'newwindow', 
     //                     'width=700,height=400,menubar=true'); 
				 /*window.open('https://arabyos.com/sign-in.php', 
                         'newwindow', 
                         'width=700,height=400,menubar=true');*/ 
                 }
			});
		</script>
		<?php } ?>

<?php //include 'index_footer.php'; ?>

<?php include "includes/footer.php";  ?>

<link rel="stylesheet" href="css/jquery.jqZoom.css?v=4.4" type="text/css"/>
<script src="js/jquery.jqZoom.js?v=4.1"></script>
<script>
    jQuery(document).ready(function($) {
        $(".zoom-box img").jqZoom({
            selectorWidth: 30,
            selectorHeight: 30,
            viewerWidth: 400,
            viewerHeight: 300
        });

    });
</script>

