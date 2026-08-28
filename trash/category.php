<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "common.php";

$token=substr($_GET['token'],4);
//echo $token; exit;

if(isset($_COOKIE['loc_id']))
{
  $sql_pd_ck=" and (
  (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."')) 
  or 
  (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
  or
  (pd_preferred_buyer_location='my_city'  and pd_uid in(select distinct bnsprof_uid from business_profile bf JOIN city c ON bf.bnsprof_city = c.ct_id where c.ct_cn_id='".$_COOKIE['loc_id']."')))";
  /*
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

$sql_mcat="select * from product_category_arabyos where md5(pc_id)='".$token."' and pc_status ='1'";
$res_mcat=mysqli_query($con, $sql_mcat);
$row_mcat=mysqli_fetch_object($res_mcat);
//echo "<pre>"; print_r($row_mcat); echo "</pre>";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
        <title><?php echo getSiteTitle(); ?></title>
        <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
        <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
    <meta name="title" content="<?php echo getSiteTitle(); ?>">
    <meta name="keywords" content="<?php echo get_page_settings(2); ?>">
    <meta name="description" content="<?php echo get_page_settings(3); ?>">
        <meta http-equiv="content-type" content="text/html; charset=UTF-8">

    
        <link type="text/css" rel="stylesheet" href="css/main-v2.css">        
    <link href="css/dir-style-8.css" type="text/css" rel="stylesheet">
    <link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
    <link href="css/bl_form_temp5.css" rel="stylesheet" type="text/css">
    <link href=css/verticle-menu.css rel=stylesheet>
    <!-- <link href=css/verticle-menu.css rel=stylesheet> -->

    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    <style>
    .countrubox_top{
      margin-bottom: 5px;
      }
    #midcenter{
      width:81%!important;
    }
    h2,h3{
      margin: 0!important;
    }
    
    @media screen and (max-width: 796px) {
      #midcenter{
        width:100%!important;
      }
    }
    .col-half-offset{
      margin-left:4.166666667%
    }
    .product_main{
      padding: 5px;
      border-radius: 5px;
      margin-bottom: 10px;
      background: white;
    }
    .product_main .utext{
      text-align: center;
      min-height: 35px;
    }
    .product_main img{
      margin: 0 auto;
      vertical-align: middle;
      max-height: 95%;
      max-width: 95%;
      width: auto;
    }
    .countrubox_top {
      margin-bottom: 30px!important;
    }
    .blank_bg
    {
      margin:0px 4px 5px;
    }
    
ul.dropdown li:hover .mega {
  display: block;
}

.mega {
  width: 600px;
  display: none;
  position: absolute;
  left: 215px;
 margin-top:-25px;
  background: #F7F7F7;
z-index: 9;
}
.mega ul
{
  padding-left:25px;
  width:100%;
  
}
.mega ul li
{
  list-style:none;
  padding:7px;
  width:33%;
  float:left;
}
.mega ul li:hover
{
  background-color:#fff;
}
.mega aside {
  float: left;
}
.mega .featured {
  float: right;
  width: 440px;
}
.mega .featured img {
  max-width: 400px;
}

    </style>
</head>
<body data-twttr-rendered="true">
        
        <div class="q_hm1">
        
        <?php include "includes/header_new.php"; 

        ?>
                
    <!-- <div class="bt"><img src="images/z.gif" alt="" height="1" width="1"></div> -->
    <div>
    <div class="row">
      
    </div>

    <p class="m2"></p>     
      <div class="brdcrm">
        <ul>
        <!-- Groups start-->
        <li itemscope="" itemtype=""> <a href="dir.php" target="_top" itemprop="url"><img class="h_imgin bg nr" alt="" src="images/zero.gif" style="padding-right: 5px;" height="20px" width="24px"><span itemprop="title">Suppliers Directory</span></a><span><img src="images/zero.gif" alt="" class="brdcrm-arwin_pgen bg nr" height="16px" width="16px"></span>
        </li>
        <!-- Groups end-->
        <li style="border:none;font-size:13px;color:#444; display:inline;"><?php echo $row_mcat->pc_name; ?></li>

        </ul>
        <p style="padding:0; margin:0; clear:both"></p>
      </div>

    </div>
    <div id="leftsection">
        <!-- <div class="a1 wp cl" align="left"> -->
      <p>
        <!-- <div class="col-12 prc-left-side" style="margin-left: 10px !important">

          <h3 class="togle_style" style="display: block;font-size:19px;cursor: pointer;font-weight:700;width: 190px;" onclick="toggle_menu();"><span class="fa fa-list"></span>&nbsp; MY MARKETS&nbsp;<i id="uparrow" class="fa fa-angle-up" style="font-size:18px;display:none;"></i><i id="downarrow" class="fa fa-angle-down" style="font-size:18px;"></i></h3>
          <div style="padding-left:0px;padding-right:10px;" class="left-side-bar-sale-offer">

              <ul class="ptag navigation" id="left_ajax_geting">

              </ul>
          </div>
        </div> -->



        <div class="allcate">
                      <h3>
                        <a href=dir.php#main_cat>
                        <i class="fa fa-list-ul" style="color:#fff" ></i>
                        <span style="display:inline-table;color:#fff;">MY MARKETS</span>
                        </a>
                      </h3>
                    </div>
                    <div id="block_navigation"title=" &#1578;&#1589;&#1606;&#1610;&#1601;&#1575;&#1578; - &#1603;&#1604; &#1605;&#1580;&#1575;&#1604;&#1575;&#1578; - &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1577; ">
                      <div id="pull" style="display:none; margin-bottom: 10px;">
                        <a href="#">
                        <h3 class="togle_style" style="display: block;font-size:19px;cursor: pointer;font-weight:700;width: 190px;"><span class="fa fa-list"></span>&nbsp; MY MARKETS&nbsp;<i id="uparrow" class="fa fa-angle-up" style="font-size:18px;display:none;"></i><i id="downarrow" class="fa fa-angle-down" style="font-size:18px;"></i></h3>
                        </a>
                      </div>
                      <style>
                        #block_navigation ul li:hover {
                        width: 100%;
                        }
                      </style>
                      <ul class="ptag navigation" id="left_ajax_geting">
                      </ul>
                    </div>
      </p>
      <p class="m2"></p>

      <p>
        

      </p>
      <p class="bo b2 p4 f1 lht">
      <!-- <span class="bd1 z1 g9 w4 w2">Companies</span> -->
      <?php echo $row_mcat->pc_name; ?></p>


      <div class="g9 fc3 tc l1">
      <ul class="dropdown">
<?php

$sql_cat="select pc_id,pc_name from product_category_arabyos pc where pc_parent_id='".$row_mcat->pc_id."' and pc_status='1'";
//echo $sql_cat;
$res_cat=mysqli_query($con, $sql_cat);
while($row_cat=mysqli_fetch_object($res_cat)){
  
    
$sql_cmt_cnt="SELECT count(*) FROM products,business_profile,plan_member_id WHERE business_profile.bnsprof_uid = products.pd_uid and  plan_member_id.b_id =business_profile.bnsprof_id and pd_subcat_id in(select pc_id from product_category_arabyos where pc_parent_id='".$row_cat->pc_id."' and pc_status='1') ".$sql_pd_ck." AND plan_member_id.expiry_date > " . time() . "  and pd_status='1' group by pd_uid ";



//  $sql_cmt_cnt="SELECT count(*) FROM products WHERE pd_subcat_id in(select pc_id from product_category_arabyos where pc_parent_id='".$row_cat->pc_id."' and pc_status='1') ".$sql_pd_ck." and pd_status='1' group by pd_uid";
  //echo $sql_cmt_cnt;
  
  $res_cmt_cnt=mysqli_query($con, $sql_cmt_cnt);
  $num_comp=mysqli_num_rows($res_cmt_cnt);
  if($num_comp>0){
    
    $sql_cat1 = "select pc_id,pc_name from product_category_arabyos pc where pc_parent_id='".$row_cat->pc_id."' and pc_status='1'";
    
    $res_cat1= mysqli_query($con, $sql_cat1);
    $subCount = mysqli_num_rows($res_cat1);
    
?>

<!-- <span class="bd1 z1"><?php echo $num_comp; ?></span> -->
<li onMouseOver="javascript:this.style.background='#F7F7F7'" onMouseOut="javascript:this.style.background='#ffffff'" style="text-align: left; padding:7px"><a href="catcompany.php?token=<?php echo rand(1000,9999).md5($row_cat->pc_id);?>" style="cursor:pointer;text-decoration:none;" class="x"><?php echo ucwords($row_cat->pc_name); ?></a>
<?php
  if($subCount > 0){
  
?>

<!-- <div class="mega">
  <aside>
    <ul>
    <?php //while($row_cat1=mysqli_fetch_object($res_cat1)){ ?>
    <li><a target="_blank" href="products.php?c=<?php //echo md5($row_cat1->pc_id);?>" style="cursor:pointer;text-decoration:none;" class="x"><?php //echo ucwords($row_cat1->pc_name); ?></a></li>
    <?php //} ?>
    </ul>
  </aside>
</div> -->
  <?php } //end subcategory condition ?>
</li>

<?php }} ?>
</ul>

</div>

</div>

<!-- <div class="wd6 z1 cr" id="hdiv">
<?php
  $sql_adv="select * from advertisement where adv_imagewidth='305' and adv_imageheight='294' and adv_status='1' order by rand() limit 1";
  $res_adv=mysqli_query($con, $sql_adv);
  if(mysqli_num_rows($res_adv)>0)
  {
    $row_adv=mysqli_fetch_object($res_adv); 
    ?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="305" height="294"/></a><?php
  }
  else
  {
?>
    <img src="upload/advertisement/334-294-advertisement.png" width="305" height="294"/>
<?php } ?>


</div> -->
<?php include "css/custom.php"; 


?>
<?php



?>
<div class="col-xs-12" id="midcenter">

  <div class="row">
    <div style="height:240px;overflow:hidden;margin-bottom:12px; width:99%;padding: 0px 40px 0px 48px;">
    <?php 
    if($row_mcat->pc_banner != '' && is_file("upload/bannerimage/".$row_mcat->pc_banner)): ?>
      <img src="upload/bannerimage/<?php echo $row_mcat->pc_banner?>" style="object-fit: cover; cursor: pointer; width:100%"/>
    <?php else: ?>
      <img src="upload/bannerimage/SLDIMG-1762hongkong1081704.jpg" height="230px" style="object-fit: cover; cursor: pointer; width:100%"/>
    <?php endif; ?>
    </div>
  </div>
  <div class="demobox">
    <div class="countrubox_top ">
      <div class="" title="<?php echo $row_mcat->pc_name; ?>">
        <h2><?php echo $row_mcat->pc_name; ?></h2>
      </div>
      <div class="clear"></div>
    </div>
    <div class="blank_bg">
      <div class="welcome_desc">
        <div class="course_demo">
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

          $sql_so =  "select * from products p JOIN product_category_arabyos pc ON p.pd_subcat_id=pc.pc_id JOIN measurement_unit mu ON p.pd_unit=mu.mu_id JOIN country ON p.pd_currency=country.cn_id JOIN business_profile bp ON p.pd_uid=bp.bnsprof_uid WHERE (pd_pck_dets='1' OR pd_lp_slider='1' OR pd_so_slider='1') AND pc.pc_parent_id in (select distinct pc_id from product_category_arabyos where pc_parent_id = '".$row_mcat->pc_id."') and pc.pc_status='1' ".$sql_pd_ck."";
          
          // echo $sql_so;exit;
          $res_so = mysqli_query($con, $sql_so);
          if ($res_so->num_rows > 0) {
            ?>
            <div class="row">
              <?php
              while ($row_so = mysqli_fetch_object($res_so)) {
                $cn_name = "";
                if ($row_so->bnsprof_city) {
                                    if (isset($_COOKIE['loc_id'])) {

                                        $row_cityname = mysql_fetch_object(mysql_query("select ct_name from city where ct_id='" . $row_so->bnsprof_city . "' limit 1"));

                                        $cn_city = $row_cityname->ct_name;


                                        $row_statename = mysql_fetch_object(mysql_query("select state_name from states where state_id='" . $row_so->bnsprof_state . "' limit 1"));

                                        $cn_state = $row_statename->state_name;


                                        $cn_name = $cn_city ." - " . $cn_state;


                                    } else {


                                        $row_cityname = mysql_fetch_object(mysql_query("select ct_cn_id from city where ct_id='" . $row_so->bnsprof_city . "' limit 1"));


                                        $row_countryname = mysql_fetch_object(mysql_query("select cn_name from country where cn_id='" . $row_cityname->ct_cn_id . "' limit 1"));


                                        $cn_name = $row_countryname->cn_name;

                                    } } else {
                    $cn_name = $row_so->cn_name;
                  }

                
                ?>
                  <div class="col-md-2 col-sm-3 col-xs-6 " style="padding-left: 5px;padding-right: 5px;">
                    <div class="product_main">
                      <a href="https://<?php echo $_SERVER['HTTP_HOST']; ?>/company/product-details.php?token=<?php echo rand(1000, 9999) . md5($row_so->pd_id) ?>&c=<?php echo rand(1000, 9999) . md5($row_so->bnsprof_id); ?>" style="text-decoration:none;color:#000" target="_blank">
                        
                        <div style="height:150px;text-align: center;" id="img-div">
                          <span class="img-helper" style="display: inline-block; height: 100%; vertical-align: middle;"></span>
                          <img src="upload/myproduct/<?php echo $row_so->pd_image;?>" alt="<?php echo $row_so->pd_title; ?>" title="<?php echo $row_so->pd_title; ?>"><!--thumb-->
                        </div>
                        
                        <!-- <img  class="img-fluid img-responsive" src="upload/myproduct/<?php echo $row_so->pd_image;?>" alt="Agrochemicals-Herbicide" title="Agrochemicals-Herbicide"> -->
                        <div class="utext" ><b><?php 
                        $result = substr($row_so->pd_title, 0, 25);
                        if(strlen($row_so->pd_title) > 25){
                          echo $result.'...';
                        }else{
                          echo $result;
                        }
                        ?></b></div>


                        
                      <div style="text-align:center;color:red;">
                        <span class="span_red"><?php echo $cn_name/*.','.$row_prd->cn_id*/; ?></span>
                      </div>
                          

                        <div style="line-height: 0.8;margin-top:1%; font-size:10px;text-align: center;"><?php echo $row_so->cn_currency; ?>
                          &nbsp;<span
                              style="color:red; font-weight: 600; font-size:13px !important;"><?php echo $row_so->pd_fob_price ?></span> / <span style="color: #9ca1ac;"><?php echo $row_so->mu_name; ?></span>
                        </div>

                        <div style="height:10%;margin-top:1%; font-size:10px;text-align: center;">MOQ : <span
                              style="color:red; font-weight: 600; font-size:13px !important;"><?php echo $row_so->pd_min_order_qty; ?>
                            &nbsp;</span><span style="color: #9ca1ac;"><?php echo $row_so->mu_name; ?></span></div>

                        

                      </a>
                    </div>
                  </div>
              <?php } ?>
            </div>
            <?php
          }
          ?>
          <script>$(window).load(function () {
              $("#flexiselDemo2").flexisel({
                visibleItems: 4,
                animationSpeed: 1e3,
                autoPlay: !0,
                autoPlaySpeed: 3e3,
                pauseOnHover: !0,
                enableResponsiveBreakpoints: !0,
                responsiveBreakpoints: {
                  portrait: {changePoint: 480, visibleItems: 1},
                  landscape: {changePoint: 640, visibleItems: 2},
                  tablet: {changePoint: 768, visibleItems: 2}
                }
              })
            })
            var count_clicks = 0;
            $("#pull").on('click', function(event) {
              if (count_clicks == 0) {
                get_load_leftdata();
              }
              toggle_menu();
              console.log('clicked');
              count_clicks++;
            });
            function toggle_menu() {
              if($('#pull').hasClass('clicked'))
              {
                $("#downarrow").css('display','inline');
                $("#uparrow").css('display','none');
                } else {
                $("#uparrow").css('display','inline');
                $("#downarrow").css('display','none');
                }
                  $("#pull").toggleClass("clicked");
              }

              function get_load_leftdata(page=0) {
                 $.ajax({
                     url:"ajax_get_leftmenu_again.php",
                     datatype:"html",
                     async: true,
                     type:"POST",
                     data:{page:page},
                     beforeSend:function () {
                        $("#left_ajax_geting").html("<img class=\"loading_m2\" src=\"images/horizontal_loading.gif\">&nbsp;Loading...&nbsp;");
                    },
                    success:function (resp) {
                        $("#left_ajax_geting").html(resp);
                    }
                 });
              };
            </script>
        </div>
        
      </div>
    </div>
  </div>
</div>
    <!--Buy Lead Form Code Ends--><!--bottom banner ends--> <p class="m2"></p></div> <!--Footer code Starts-->
        <!-- Footer Start Here::-->
<?php include 'includes/footer.php'; ?>