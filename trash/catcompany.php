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
  
  $sql_scat="select * from product_category_arabyos where md5(pc_id)='".$token."' and pc_status ='1'";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="<?php echo get_page_settings(2); ?>">
    <meta name="description" content="<?php echo get_page_settings(3); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link type="text/css" rel="stylesheet" href="css/main-v2.css">
    <link href="css/dir-style-8.css" type="text/css" rel="stylesheet">
    <link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
    <link href="css/bl_form_temp5.css" rel="stylesheet" type="text/css">
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
    <link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.0/normalize.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/jquery.jqZoom.css?v=1.3" type="text/css"/>
    <script src="js/jquery.jqZoom.js?v=1.1"></script>
    <script>
      $(function(){
          $(".zoom-box img").jqZoom({
              selectorWidth: 30,
              selectorHeight: 30,
              viewerWidth: 400,
              viewerHeight: 300
          });
      
      })
    </script>
    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-36251023-1']);
      _gaq.push(['_setDomainName', 'jqueryscript.net']);
      _gaq.push(['_trackPageview']);
      
      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
      
    </script>
    <script>
      try {
        fetch(new Request("https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js", { method: 'HEAD', mode: 'no-cors' })).then(function(response) {
          return true;
        }).catch(function(e) {
          var carbonScript = document.createElement("script");
          carbonScript.src = "//cdn.carbonads.com/carbon.js?serve=CK7DKKQU&placement=wwwjqueryscriptnet";
          carbonScript.id = "_carbonads_js";
          document.getElementById("carbon-block").appendChild(carbonScript);
        });
      } catch (error) {
        console.log(error);
      }
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
            margin-left: -25px !important;
            margin-right: auto !important;
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
    </style>
  </head>
  <body data-twttr-rendered="true">
    <div class="q_hm1">
      <?php include "includes/header_new.php"; ?>
      <!--<div class="bt"><img src="images/z.gif" alt="" height="1" width="1"></div>-->
      <!-- <div class="a1 wp cl" align="left"> -->
      <div class="a1 wd cl" align="left">
        <!-- google_ad_section_start -->
        <p class="m2"></p>
        <!--   Breadcrumb starts here  -->
        <p class="m2"></p>
        <!-- <p class="bo b2 p4 f1 lht"></p>-->
        <div class="maincontainertop ps-15">
          <div class="brdcrm">
            <ul>
              <!-- Groups start-->
              <li> <a href="dir.php" target="_top" itemprop="url"><img class="h_imgin bg nr" alt="" src="images/zero.gif" style="padding-right: 5px;" height="20px" width="24px"><span itemprop="title">Suppliers Directory</span></a><span><img src="images/zero.gif" alt="" class="brdcrm-arwin_pgen bg nr" height="16px" width="16px"></span> </li>
              <!-- Groups end-->
              <li style="border:none;font-size:13px;color:#444; display:inline;"><?php echo $row_scat->pc_name; ?></li>
            </ul>
            <p style="padding:0; margin:0; clear:both"></p>
          </div>
          <div class="row">
            <div class="col-md-2 col-sm-2 col-xs-12 left_div">
              <!-- &nbsp; -->
              <!--Menu-->
              <link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
              <?php 
                $_GET['rctyp']='Suppliers';
                include_once 'index_leftsidebar_catcompany.php'; ?>
              <!--Menu--> 
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12 middle-part">
              <button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize <?php echo $pc_id; ?>" style="border-top:2px solid #ff7519 !important;border:0px;font-weight:700;">Suppliers</button>
              <button type="button" class="btn btn-default border-radius-0  txt-bold bold-xs btn-white text-capitalize " style="background-color: #F5F7FA"><a href="products.php?c=<?php echo md5($category_p_id); ?>" style="color:#000;font-weight: 700;" target="_new">Products</a></button>
              <?php
                //$sql_comp="select * from business_profile where bnsprof_uid in(SELECT distinct pd_uid FROM products WHERE pd_subcat_id='".$row_scat->pc_id."')";
                $sql_comp="SELECT * FROM business_profile bf JOIN products p ON bf.bnsprof_uid = p.pd_uid JOIN product_category_arabyos pc ON p.pd_subcat_id =pc.pc_id JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id where pc_parent_id='".$row_scat->pc_id."'  and pm.expiry_date > ". time() ." and pc_status='1' ".$sql_pd_ck." and pd_status='1' GROUP BY pd_uid";
                //$sql_comp="select * from business_profile where bnsprof_uid in(SELECT distinct pd_uid FROM products WHERE pd_subcat_id in(select distinct pc_id from product_category_arabyos where pc_parent_id='".$row_scat->pc_id."'  and pc_status='1') ".$sql_pd_ck.")";
                $res_comp=mysqli_query($con, $sql_comp);
                //echo $sql_comp;
                $related_id = array();
                while($row_comp=mysqli_fetch_object($res_comp)){
                    $related_id[] = $row_comp->bnsprof_uid;
                
                $iCountry = user_info($row_comp->bnsprof_uid,'country'); 
                 
                
                
                $iCounsql="select cn_name from country where cn_id = '".$iCountry."' ";
                $resultCount = mysqli_query($con, $iCounsql);
                $rowCountry=mysqli_fetch_object($resultCount);
                
                 
                
                ///Lastest DOne BY SHail
                $dataNewAbc = mysql_query("select * from plan_member_id where  b_id='".$row_comp->bnsprof_id."' ");
                
                $rowNewAb=mysql_fetch_object($dataNewAbc);
                ///Lastest DOne BY SHail
                
                  
                $dataMem = mysql_query("select * from smembership_icon_plan where mp_id = '".$rowNewAb->icon_id."'");
                $rowMem=mysql_fetch_object($dataMem);
                  
                ?>
              <div class="my-container" style="width:auto!important;">
                <div class="row">
                  <div class="col-md-1" style="width:4%">
                    <?php if($rowMem->mst_icon!='') { ?>
                    <img  src="admin/images/<?php echo $rowMem->mst_icon; ?>"  title="<?php echo $rowMem->mst_name; ?>" width="30px" height="30px;">
                    <?php } ?>
                  </div>
                  <div class="col-md-6">
                    <a style="" <?php if(user_info($row_comp->bnsprof_uid,'bnsprof_compname')!=''){ ?>href="company/profile.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id); ?>" <?php } ?> target="_blank">
                    <span class="city-country" style="font-weight:900;"><?php echo $row_comp->bnsprof_compname; ?></a><br>
                    <?php  echo $rowCountry->cn_name; ?>,  <?php echo get_city_name($row_comp->bnsprof_city); ?>    
                  </div>
                </div>
                <div class="row" style="padding-top: 10px;">
                  <div class="three-pics1">
                    <?php 
                      $get_product = mysqli_query($con,"SELECT * FROM `products` where pd_uid = '".$row_comp->bnsprof_uid."' GROUP BY `pd_subcat_id` limit 3");
                      
                      while ($run = mysqli_fetch_object($get_product)) { ?>
                    <div class="col-md-2">
                      <?php   
                        if($run->pd_image !=""){
                          //echo $row_comp->pd_image;die();
                        ?>
                      <div style="width:100%;">
                        <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank">
                          <div class="cat_image_div zoom-box">
                            <img src="/upload/myproduct/<?php echo $run->pd_image; ?>"    title="<?php echo $run->pd_title; ?>" class="cat_image">
                          </div>
                        </a>
                        <br>
                        <span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php echo wordwrap(substr($run->pd_title, 0,30), 17, "<br/>", true); ?></span>
                      </div>
                      <?php } else { ?>
                      <div style="width:100%;">
                        <a href="company/products.php?c=<?php echo rand(1000,9999).md5($row_comp->bnsprof_id);?>" target="_blank">
                          <div class="cat_image_div zoom-box"><img src="/images/noimage.jpg"  class="cat_image" title="<?php echo $row_comp->pd_title; ?>"></div>
                        </a>
                        <br>
                        <span style="font-size:11px;color:#37366d ;font-weight:0px;"><?php echo substr($run->pd_title, 0,15); ?></span>
                      </div>
                      <?php }  ?>
                    </div>
                    <?php } ?>
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
                    <p class="g1">
                      <b>Address:</b>&nbsp;
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
                      <a href="tel:+<?php echo $rowBuisness_user_details->mobile1; ?>"><span id="pns1">+</span><?php echo $rowBuisness_user_details->mobile1; ?></a><img alt="" style="position:absolute;margin-top:0px; margin-left:5px;" class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
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
                      <?php } ?>
                      <span id="sms1" class="tool-tip-lg soff"><span class="bg nr toolarw"></span><span class="txt-call">&nbsp;</span></span><br>
                      <?php if($row_comp->bnsprof_website_alt != ''){ ?>
                      <b>Website:</b> <a target="_blank" href="https://<?php echo $row_comp->bnsprof_website_alt; ?>"><?php echo $row_comp->bnsprof_website_alt; ?></a><br>
                      <?php } ?>
                    </p>
                  </div>
                </div>
              </div>
              <br>
              <?php } ?>
            </div>
            <style>
              .right_part{
              padding-left: 0px;padding-right: 0px;
              }
              @media only screen and (max-width: 767px) {
              .right_part{
              padding-left: 15px;padding-right: 15px;
              }
              }
            </style>
            <div class="col-md-2 col-sm-2 col-xs-12 " style="">
              <!-- slider -->
              <?php  ?>
              <link rel="stylesheet" type="text/css" href="css/slick.css">
              <link rel="stylesheet" type="text/css" href="css/slick-theme.css">
              <script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
              <?php include "css/custom.php"; ?>
              <style>
                .slick-product-image > img {
                min-height: auto!important;
                max-height: 190px!important;
                border: 1px solid #E9E9E9!important;
                }
                .slick-product-wrapper {
                max-width: none!important;
                width: 80%;
                display: inline-block;
                padding-top: 10px;
                padding-bottom: 10px;
                }
                .matterbox p {
                text-align: center;
                }
                .ihoves{
                text-align: center!important;
                }
                .top-arrow::before, .bottom-arrow::before {
                font-family: slick;
                font-size: 20px;
                line-height: 1;
                opacity: .75;
                color: #fff;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                }
                .bottom-arrow::before {
                content: '←';
                }
                .top-arrow::before {
                content: '→';
                }
                .arrow_sli {
                height: 100%;
                width: 100%;
                right: 0;
                background: rgb(34,122,191);
                z-index: 9;
                }
              </style>
              <?php
                if (isset($_COOKIE['loc_id'])) {
                                $sql_pd_ck = " and (
                        (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                        or
                        (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                        or
                        (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                
                                $sql_so_ck = " and (
                        (so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                        or
                        (so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                        or
                        (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                
                                $sql_br_ck = " and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                        or
                        (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                        or
                        (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                               
                            } else {
                                $sql_pd_ck = " and (
                        (pd_preferred_buyer_location='any')
                        or
                        (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
                        )";
                                $sql_so_ck = " and (
                        (so_preferred_buyer_location='any')
                        or
                        (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
                        )";
                                $sql_br_ck = " and (
                        (br_preferred_supplier_location='any')
                        or
                        (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
                        )";
                            }
                            
                            if(isset($related_id)&&!empty($related_id))
                            { 
                                $rel_sup_id = array();
                                
                                foreach($related_id as $related_id){
                                    $rel_sup_id[] = $related_id;
                                }
                                $rel_id= implode(',',$rel_sup_id);
                                
                                $sqls="SELECT `pd_subcat_id` FROM `products` WHERE pd_uid IN ($rel_id) and pd_status='1'";
                                $ress=mysql_query($sqls);
                                $RelatedCatCount = mysql_num_rows($ress);
                                $rel_pc_id = array();
                                while( $Results = mysql_fetch_object($ress) ){
                                    $rel_pc_id[] = $Results->pd_subcat_id;
                                }
                                // echo '<pre>';print_r($rel_pc_id);exit;
                                $rel_id= implode(',',$rel_pc_id); 
                            
                                                            if ($_COOKIE['loc_id'] != "") {
                                                                $sqlleading = "select * from products,measurement_unit_arabyos,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_subcat_id IN ($rel_id) and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 0 , 100";
                                                            } else {
                                                                $sqlleading = "select * from products,measurement_unit_arabyos,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_subcat_id IN ($rel_id) and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 0 , 100";
                                                            }
                                                            // echo  $sqlleading;exit;
                                                             
                                                             $rsleading = mysqli_query($con,$sqlleading);
                                                             $totalbaneer = mysqli_num_rows($rsleading);
                                                            // echo $totalbaneer ;exit;
                                                             $rembaner = $totalbaneer%2;
                                                             if($totalbaneer > 2){
                                                             if($totalbaneer> 0)
                                                             {
                                                              ?>
              <p style="font-size: 16px;margin-top: 35px;margin-bottom: 10px;"><b>Related Leader Suppliers</b></p>
              <div class="demobox" >
                <div class="wrapper-container">
                  <div class="white_bg">
                    <div class="welcome_desc">
                      <div class="course_demo">
                        <ul id="ARABYOS-relatedCat">
                          <?php
                            while($rowleading = mysqli_fetch_object($rsleading))
                              {
                                $pd_id = $rowleading->pd_id;
                                $pd_image = $rowleading->pd_image;
                                $pd_title = $rowleading->pd_title;
                                $adv_icon = '';
                                
                                  echo '<div class=" main-slick-wrapper-item">';
                                $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $rowleading->pd_uid . "' limit 1"));
                                             $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_bprof->bnsprof_id;
                             ?>
                          <a class="slick-product-wrapper" href="company/products.php?c=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $rowleading->pd_subcat_id; ?>#<?php echo $rowleading->pd_id; ?>" target=_blank>
                            <div class="demobox">
                              <div class="slick-product-image ">
                                <img alt="" src="upload/myproduct/<?php echo $rowleading->pd_image; ?>" class="black" style="margin: auto;border: 1px solid #E9E9E9!important;" title="<?php echo ucwords($rowleading->pd_title); ?>">
                              </div>
                              <div class="matterbox">
                                <div class="icon-pic-with-heading">
                                  <div class="icon_pic"><img alt=""src="images/<?php echo $adv_icon;?>"class="img-responsive" width="18"></div>
                                  <div class="ihover-wrapper">
                                    <h3 class="ihoves">
                                      <?php echo ucwords(substr($pd_title, 0,15)); ?><?php if (strlen($pd_title) > 15) { ?>...<?php } ?>
                                    </h3>
                                    <div class="auction_hover">
                                      <p><?php echo ucwords($pd_title); ?></p>
                                    </div>
                                  </div>
                                </div>
                                <div class="rightmatter">
                                  <p>
                                    <span class="nam"><?php echo get_country_name($rowleading->country); ?></span><br>
                                  <p>MOQ: <span
                                    class="nam"><?php echo $rowleading->pd_min_order_qty; ?><?php echo $rowleading->mu_name; ?></span><br>
                                  <p><?php echo $rowleading->cn_currency; ?><span
                                    style="font-size:11px!important"
                                    class="nam"><?php echo $rowleading->pd_fob_price ?>
                                    /</span><?php echo $rowleading->mu_name; ?>
                                  <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                              </div>
                            </div>
                          </a>
                          <?php
                            echo '</div>';
                            }
                            if($rembaner==1){ echo '</div>';}
                            ?>
                        </ul>
                        <script>$(window).load(function(){$("#flexiselDemo4").flexisel({visibleItems:4,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                      </div>
                    </div>
                    <div class="clear" style="height:1px"></div>
                  </div>
                </div>
              </div>
              <?php }} ?>
              <?php } ?>
              <script>
                $('#ARABYOS-relatedCat').slick({
                    nextArrow: '<div class="arrow_sli"><img src="/assets/img/botom.png" class="top-arrow" aria-label="Previous" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></div>',
                    prevArrow: '<div class="arrow_sli"><img src="/assets/img/top.png" class="bottom-arrow" aria-label="Next" style="width:30px;display: block;margin: auto;border: none;background: rgb(34,122,191);padding: 5px;"></button></div>',
                    centerMode: true,
                    centerPadding: '10px',
                    slidesToShow: 7,
                    autoplay: true,
                    vertical: true,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                centerMode: true,
                                centerPadding: '10px',
                                slidesToShow: 7
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                centerMode: true,
                                centerPadding: '10px',
                                slidesToShow: 7
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                centerMode: true,
                                centerPadding: '10px',
                                slidesToShow: 7
                            }
                        }
                    ]
                });
              </script>
            </div>
            <!-- slider -->
          </div>
        </div>
      </div>
      <div class="wd6 z1 cr hide" id="hdiv">
        <br>
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
          <?php } ?>
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