<?php
include "common.php";
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

$token=substr($_GET['token'],4);

$sql_scat="select * from product_category where md5(pc_id)='".$token."' and pc_status ='1'";
$res_scat=mysqli_query($con, $sql_scat);
$row_scat=mysqli_fetch_object($res_scat);
$category_p_id = $row_scat->pc_id;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo getSiteTitle(); ?> :: Suppliers - <?php echo $row_scat->pc_name; ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
</meta>
<meta name="title" content="<?php echo getSiteTitle(); ?>">
<meta name="keywords" content="<?php echo get_page_settings(2); ?>">
<meta name="description" content="<?php echo get_page_settings(3); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link type="text/css" rel="stylesheet" href="css/main-v2.css">
<link href="css/dir-style-8.css" type="text/css" rel="stylesheet">
<link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
<link href="css/bl_form_temp5.css" rel="stylesheet" type="text/css">
<link href="css/iframe-script.css" rel="stylesheet" type="text/css">
<script language="javascript" type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
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
	width:903px !important;
	border: 1px solid #c2e6fe !important;
    padding: 14px !important;
    background: #fff !important;
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
</style>
</head>
<body data-twttr-rendered="true">
<div class="q_hm1">
  <?php include "includes/header_new.php"; ?>
  <!--<div class="bt"><img src="images/z.gif" alt="" height="1" width="1"></div>-->
  <div class="a1 wp cl" align="left"> <!-- google_ad_section_start -->
    <p class="m2"></p>
    <!--   Breadcrumb starts here  -->
    <div class="brdcrm">
      <ul>
        <!-- Groups start-->
        <li> <a href="dir.php" target="_top" itemprop="url"><img class="h_imgin bg nr" alt="" src="images/zero.gif" style="padding-right: 5px;" height="20px" width="24px"><span itemprop="title">Suppliers Directory</span></a><span><img src="images/zero.gif" alt="" class="brdcrm-arwin_pgen bg nr" height="16px" width="16px"></span> </li>
        <!-- Groups end-->
        <li style="border:none;font-size:13px;color:#444; display:inline;"><?php echo $row_scat->pc_name; ?></li>
      </ul>
      
    <p style="padding:0; margin:0; clear:both"></p>

    </div>
    <p class="m2"></p>
   <!-- <p class="bo b2 p4 f1 lht"></p>-->
   

    <br>


    <div class=" p4">
	<div class="middle-part">

		<button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize <?php echo $pc_id; ?>" style="border-top:2px solid #ff7519 !important;border:0px;font-weight:700;">Suppliers</button>

		<button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize "><a href="products.php?c=<?php echo md5($category_p_id); ?>" style="color:#000;font-weight: 700;" target="_new">Products</a></button>

		
	</div>
      <?php

//$sql_comp="select * from business_profile where bnsprof_uid in(SELECT distinct pd_uid FROM products WHERE pd_subcat_id='".$row_scat->pc_id."')";
$sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category pc ON p.pd_subcat_id =pc.pc_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where pc_parent_id='".$row_scat->pc_id."'  and pm.expiry_date > ". time() ." and pc_status='1' ".$sql_pd_ck." and pd_status='1' GROUP BY pd_uid";
//$sql_comp="select * from business_profile where bnsprof_uid in(SELECT distinct pd_uid FROM products WHERE pd_subcat_id in(select distinct pc_id from product_category where pc_parent_id='".$row_scat->pc_id."'  and pc_status='1') ".$sql_pd_ck.")";
$res_comp=mysqli_query($con, $sql_comp);
//echo $sql_comp;
while($row_comp=mysqli_fetch_object($res_comp)){

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
 <?php if($rowMem->mst_icon!='') { ?>
            <img  src="admin/images/<?php echo $rowMem->mst_icon; ?>"  title="<?php echo $rowMem->mst_name; ?>" width="30px" height="30px;">
            <?php } ?></div>
            <div class="col-md-6">
            <a style="" <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank">
			<span class="city-country" style="font-weight:700;"><?php echo $row_comp->bnsprof_compname; ?></a><br>
        <?php  echo $rowCountry->cn_name; ?>,  <?php echo get_city_name($row_comp->bnsprof_city); ?>    
           </div>
    
     

</div>
<div class="row" style="padding-top: 10px;">
<div class="three-pics1">
<div class="col-md-2">
<?php 
			if (file_exists($_SERVER['DOCUMENT_ROOT'].'/upload/myproduct/thumb/'.$row_comp->pd_image)){ 
			if($row_comp->pd_image !=""){
			?>
            <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank">
            <img src="/upload/myproduct/thumb/<?php echo $row_comp->pd_image; ?>" height="100" width="100"   title="<?php echo $row_comp->pd_title; ?>" style="border: 2px solid lightgray;"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo wordwrap(substr($row_comp->pd_title, 0,30), 17, "<br/>", true); ?></span>
            <?php } 
			else
			{
			?>
            <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title="<?php echo $row_comp->pd_title; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($row_comp->pd_title, 0,15); ?></span>
            <?php	
			} } else { ?>
            
			<!--<a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title=""></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($row_comp->pd_title, 0,15); ?></span>-->
			<?php } ?>
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


<a href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank">
<img src="/upload/company_banner/<?php echo $row_result_banner_details->cb_image; ?>" height="100" width="100" style="border: 1px solid lightgray;" title="<?php echo $row_comp->bnsprof_compname; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($row_comp->bnsprof_compname, 0,30); ?></span>

  <?php } 
			else
			{
			?>
          <a href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title="<?php echo $row_comp->bnsprof_compname; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($row_comp->bnsprof_compname, 0,15); ?></span>
			
            <?php	
			} } 
			else { ?>
            <!--<td valign="top"><a href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title="<?php echo $row_comp->bnsprof_compname; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($row_comp->bnsprof_compname, 0,15); ?></span>
			</td>-->
			<?php } ?>
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
            <a <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank">
            <img src="<?php echo $imageprofilepath; ?>" height="100" width="100" title="<?php echo $abtrow->abtus_desc; ?>" style="border: 1px solid lightgray;"> </a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($abtrow->abtus_desc, 0,35); ?></span>
            <?php } 
			else
			{
			?>
            
            <a <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title="<?php echo $abtrow->abtus_desc; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($abtrow->abtus_desc, 0,35); ?></span>
            <?php	
			} } 
			else { ?>
            <!--<a <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank"><img src="/images/noimage.jpg" height="100" width="100" title="<?php echo $abtrow->abtus_desc; ?>"></a>
			<br>
			<span style="font-size:11px;color:#37366d ;font-weight:bold;"><?php echo substr($abtrow->abtus_desc, 0,35); ?></span>-->
			<?php } ?>
</div>
</div>
<div class="col-md-6">
				<b>
              <?php
				$sql_wc="select * from website_content where wc_usr_id='".$row_comp->bnsprof_uid."'";
				$res_wc=mysqli_query($con, $sql_wc);
				$row_wc=mysqli_fetch_object($res_wc);
				echo stripslashes($row_wc->wc_homepage_key_desc);
				?>
			 </b>
             <br>
             <a href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" target="_blank" class="fc td j1 g4 bg im3 lht pb2">About Us</a> &nbsp;&nbsp;<a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank" class="fc td j1 g4 bg im4 pb2 lht">View Products</a>
             <br>
             <b>Main Buisness : </b>
           <?php
		    $string_buisnessmain = "";
            $user_products ="select * from products where pd_uid = '".$row_comp->bnsprof_uid."' GROUP by pd_subcat_id";
			$result_user_products = mysqli_query($con, $user_products);
			while($row_user_products = mysqli_fetch_object($result_user_products)){
				$pd_subcat_id = $row_user_products->pd_subcat_id;
				$user_products_category ="select * from product_category where pc_id = '".$pd_subcat_id."'";
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
			    $buisnsql="select * from business_type where bsntyp_id = '".$databuisness."' GROUP BY bsntyp_title";
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
        <a class="bg ima z1 e4 hl a2 td bo f2 wdt c6 td ajax" rel="nofollow" href="sendenquiry-form.php?id=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>">Send Enquiry</a>
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
              <b>Phone:</b>&nbsp;
			  <?php if($rowBuisness_user_details->mobile1!=''){ ?>
              <span id="pns1">0</span><?php echo $rowBuisness_user_details->mobile1; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <!--<?php if($row_comp->bnsprof_ph1!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_ph1; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <?php if($row_comp->bnsprof_ph2!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_ph2; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <?php if($row_comp->bnsprof_ph3!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_ph3; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
			  <?php if($row_comp->bnsprof_ph4!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_ph4; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>-->
              <?php if($row_comp->bnsprof_mobile2!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_mobile2; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
              <?php if($row_comp->bnsprof_mobile3!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_mobile3; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
              <?php } ?>
              <?php if($row_comp->bnsprof_mobile4!=''){ ?>
              <span id="pns1">0</span><?php echo $row_comp->bnsprof_mobile4; ?><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20">
              <?php } ?>
              <?php	}	?>
              <span id="sms1" class="tool-tip-lg soff"><span class="bg nr toolarw"></span><span class="txt-call">&nbsp;</span></span><br>
              <?php if($row_comp->bnsprof_website_alt != ''){	?>
              <b>Website:</b> <a target="_blank" href="https://<?php echo $row_comp->bnsprof_website_alt; ?>"><?php echo $row_comp->bnsprof_website_alt; ?></a><br>
              <?php } ?>
            </p>
</div>
</div>
</div>



      
      <br>
      <?php } ?>
    </div>
  </div>
  <div class="wd6 z1 cr" id="hdiv"> <br>
    <img src="images/z.gif" alt="" height="5">
    <div class="c9 brd fy1 gv w12">
      <?php
	$sql_adv="select * from advertisement where adv_imagewidth='300' and adv_imageheight='300' and adv_status='1' order by rand() limit 1";
	$res_adv=mysqli_query($con, $sql_adv);
	if(mysqli_num_rows($res_adv)>0)
	{
		$row_adv=mysqli_fetch_object($res_adv);	
		?>
      <a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="300" height="300"/></a>
      <?php
	}
	else
	{
?>
      <img src="upload/advertisement/300-300-advertisement.png" width="300" height="300"/>
      <?php	}	?>
    </div>
    <br>
    <div class="bg im25" align="LEFT">
      <div class="bg im25 n9 a1"> 
        
        <!-- Dir Gen Pages Right Link Unit --> 
        
      </div>
    </div>
    <div class="m2"></div>
    <div style="margin:27px 0px 0px 2px; float:left;width:245;">
      <div style="float:left"> </div>
    </div>
  </div>
  
  <!--Buy Lead Form Code Ends--><!--bottom banner ends-->
  <p class="m2"></p>
</div>
<!--Footer code Starts--> 
<!-- Footer Start Here::-->

<?php include 'includes/footer.php'; ?>
