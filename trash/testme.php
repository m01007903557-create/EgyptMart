<?php
   error_reporting(0);
   ob_start();
   session_start();
   
   include 'common.php';
   
   $uid=$_SESSION['uid_indm'];
   $globalcntid = 243;
   if(isset($_COOKIE['loc_id']))
   {
     ## get Country id by
     $cn_id = $_COOKIE['loc_id'];
     $sqlcountry = "select cn_name from country where cn_id='$cn_id'";
     $rscountry = mysqli_query($con,$sqlcountry);
     if(mysqli_num_rows($rscountry) > 0)
     {
       $rowcountrty = mysqli_fetch_object($rscountry);
       $cn_name = $rowcountrty->cn_name;
     }
   }
   else
   {
     $cn_id = 0;
     $cn_name="Global";
   }
   ini_set('display_errors', 1);
   error_reporting(E_ALL & ~E_NOTICE);
   ## query for country
   if($cn_id!="" && $cn_id > 0)
    {
      //$strconutnry=" AND (adv_country LIKE '%$cn_id,%' OR adv_country LIKE '%,$cn_id%' OR adv_country LIKE '%,$cn_id,%' OR adv_country='$cn_id')";
      $strconutnry=" AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
    }
    else
    {
      //$strconutnry =" AND (adv_country LIKE '%$globalcntid,%' OR adv_country LIKE '%,$globalcntid%' OR adv_country='$globalcntid')";
      $strconutnry=" AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
    }
   ?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome :: ARABYOS</title>
<link href="css/new/css/bootstrap.css" rel='stylesheet' type='text/css' />
<link href=css/style.css rel=stylesheet>
<link href=css/responsive1.css rel=stylesheet>
<!--[if IE]><script src=js/html5.js></script><![endif]-->
<link href=css/verticle-menu.css rel=stylesheet>
<link href=css/theme.css rel=stylesheet>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="js/new_js/jquery.min.js" type="text/javascript"></script>
<script src="js/new_js/responsiveslides.min.js"></script>
<script type="text/javascript" src="js/new_js/jquery.accessible-news-slider.js"></script>
</head>
<body>
<div class="wrapper">
<?php include "includes/test_header.php"; ?>
</div>
<script type="text/javascript">
   $(function(){$("#slider").responsiveSlides({auto:!0,nav:!1,speed:500,namespace:"callbacks",pager:!0})});
</script>
<script>
   jQuery(document).ready(function(){jQuery("#newsslider").accessNews({}),jQuery("#newsslider2").accessNews({title:"BREAKING NEWS:",subtitle:"stories from the internet",speed:"slow",slideBy:5,slideShowInterval:1e5,slideShowDelay:1e5})});
</script>
<body>
   <div class="loader_img" ></div>
   <script type="text/javascript">
      $(window).load(function() {
      $(".loader_img").fadeOut("slow");
      });
   </script>
   <script type="text/javascript">
        setTimeout(function(){
          var objDate = new Date(),
          locale = "en-us",
          month = objDate.toLocaleString(locale, { month: "long" });
          day = objDate.getDate();
          year = objDate.getFullYear();
          $('.jqans-headline').html('<p><strong>TODAY NEWS:&nbsp;</strong>'+month+' '+day+' '+year+'</p>');
        }, 3000);
  </script>
   <div id="fb-root"></div>
   <script>
      !function(e,n,t){var o,c=e.getElementsByTagName(n)[0];e.getElementById(t)||(o=e.createElement(n),o.id=t,o.src="//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0",c.parentNode.insertBefore(o,c))}(document,"script","facebook-jssdk")
   </script>
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
   
      <div class="middlesection">
         <div class="maincontainer">
            <div class="demobox">
               <div id="leftsection">
                  <div class="allcate">
                     <h3>
                        <a href=dir.php#main_cat>
                        <i class="fa fa-list-ul"style=color:#fff></i> 
                        <span style="display:inline-table;color:#fff;">&nbsp;MY MARKETS</span>
                        </a>
                     </h3>
                  </div>
                  <div id="block_navigation">
                     <div id="pull" style="display:none;">
                        <a href="#">
                        <i class="icon-reorder"></i>Menu
                        </a>
                     </div>
                     <ul class="ptag navigation">
                        <?php
                           $sql_dd_mnu = "select pc_id,pc_name,pc_image from product_category where pc_parent_id = '0' and pc_status = '1' " . $sql_order;
                           $res_dd_mnu = mysqli_query($con, $sql_dd_mnu);
                           
                           while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)) {
                           ?>
                        <li class="ptag">
                           <?php
                              if( $row_dd_mnu->pc_name == "Business Services" )
                              { ?><a href="dir.php#main_cat_<?php echo $row_dd_mnu->pc_id; ?>"style="color:red;font-family:Arial #000"><?php echo $row_dd_mnu->pc_name; ?><span class="main_links_span"></span></a>
                           <?php
                              } else {?>
                           <a href="dir.php#main_cat_<?php echo $row_dd_mnu->pc_id; ?>"><?php echo $row_dd_mnu->pc_name; ?><span class=main_links_span></span> </a>
                           <?php  }?>
                           <div class="typography_3_colm">
                              <div class="colm_3_container">
                                 <?php
                                    $sql_dd_cmnu = "select pc_id, pc_sort_name from product_category where pc_parent_id = '" . $row_dd_mnu->pc_id . "' and pc_status = '1' " . $sql_order;
                                    $res_dd_cmnu = mysqli_query($con, $sql_dd_cmnu);
                                    
                                    $item_cnt = mysqli_num_rows($res_dd_cmnu);
                                    //echo var_dump($item_cnt);
                                    
                                    $res_dd_cmnu_arr = array();
                                    while ($row_dd_cmnu = mysqli_fetch_object($res_dd_cmnu)) {
                                        $row_scnt = mysqli_fetch_object(mysqli_query($con, "select count(*) as cnt from product_category where pc_parent_id = '" . $row_dd_cmnu->pc_id . "' and pc_status = '1'"));
                                        $res_dd_cmnu_arr[] = $row_dd_cmnu;
                                    }
                                    
                                    if($item_cnt  >= 1) $res_col_arr = array_chunk($res_dd_cmnu_arr, ceil($item_cnt / 3));
                                        foreach($res_col_arr as $r){ ?>
                                 <div class="colmn_3_fullwidth">
                                    <ol class="ptaga some_links">
                                       <?php if(is_array($r)){
                                          for($i = 0 ; $i < count($r) ; $i++){ ?>
                                       <li><a href="products.php?c=<?php echo md5($r[$i]->pc_id); ?>"class="ptaga"><?php echo ucwords($r[$i]->pc_sort_name); ?></a></li>
                                       <?php } }else{ ?>
                                       <li>
                                          <a href="products.php?c=<?php echo md5($r['pc_id']); ?>"class="ptaga"><?php echo ucwords($r['pc_sort_name']); ?></a>
                                       </li>
                                       <?php } ?>
                                    </ol>
                                 </div>
                                 <?php } ?>
                              </div>
                           </div>
                        </li>
                        <?php } ?>
                        <p>
                           <a href="dir.php#main_cat" target="_blank">View All Categories <span>>></span></a>
                     </ul>
                  </div>
                  <div class="col-lg-12 col-xs-12 col-sm-12 list-top">
                     <h1>
                        <a href=#>
                        <img alt="" src="images/wholesaler.jpg">
                        </a>
                     </h1>
                     <h5>Set Your</h5>
                     <a href="create-free-website.php" target=_>
                        <div class="showcase">Products Showcase</div>
                     </a>
                     <p>Distribute in Your City
                  </div>
                  <div class="col-lg-12 col-xs-6 col-sm-12 map-tops">
                     <div class="map"><a href="#"><a href="#"><img alt=""src="images/map.png"></a></a></div>
                  </div>
                  <div class="col-lg-12 col-xs-6 col-sm-12 list-top">
                     <div class="seniorbox">
                        <div class="siniorlistbox">
                           <div class="siconbox"><img alt=""src=images/left-icon.png></div>
                           <div class="scontentbox">
                              <h2>Senior <span>Supplier</span></h2>
                           </div>
                           <div class="clear"></div>
                        </div>
                        <ul>
                           <li>> <a href="#">Premium Company Websites</a>
                           <li>> <a href="#">Product ShowCase</a>
                           <li>> <a href="#">Product Top Rank</a>
                           <li>> <a href="#">Full Access to Buy Leads</a>
                           <li>> <a href="#">Free Banner Advisements</a>
                           <li>> <a href="#">Company video</a></li>
                           <p><a href="membership_plans.php">Learn More <span>> ></span></a>
                        </ul>
                        <h3><a href="membership_plans.php">Upgrade Now</a></h3>
                     </div>
                  </div>
                  <div class="clear"></div>
               </div>
               <div class="col-xs-12" id="midcenter">
                  <div class="slider">
                     <div class="yahoo_slider">
                        <ul id="newsslider">
                           <?php
                              $sqllogo = "select * from yahoo_slider_arabyos where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
                              $rslogo = mysqli_query($con,$sqllogo);
                              if(mysqli_num_rows($rslogo) > 0)
                              {
                               while($rowlogo= mysqli_fetch_object($rslogo))
                               {
                                 $adv_img = $rowlogo->adv_img;
                                 $logopath = "upload/yahoo_slider/".$adv_img;
                                 $adv_link = $rowlogo->adv_link;
                                 $adv_title = $rowlogo->adv_title;
                                 $adv_description = Show_shortcontent($rowlogo->adv_description,22);
                                 $adv_imagewidth = $rowlogo->adv_imagewidth;
                                 $adv_imageheight = $rowlogo->adv_imageheight;
                              ?>
                           <li>
                              <a href="<?php echo $adv_link;?>"target="_blank"><img alt="<?php echo $adv_title;?>" src="<?php echo $logopath;?>"></a>
                              <h3><a href="<?php echo $adv_link;?>"target="_blank"><?php echo $adv_title;?></a></h3>
                              <p><a href="<?php echo $adv_link;?>"style="color:#gray">» read more</a>
                           </li>
                           <?php } }
                              else
                              {
                                echo '';
                              }
                              ?>
                        </ul>
                     </div>
                     <div class="video_slider">
                        <div class="slider">
                           <ul class="rslides" id="slider">
                              <?php
                                 ## get the top compnay video
                                 $sqlvideo="select * from video_slider where adv_status='1' $strconutnry";
                                                 $resvideo=mysqli_query($con,$sqlvideo);
                                 $totalvideo = mysqli_num_rows($resvideo);
                                 if($totalvideo > 0)
                                 {
                                   while($row_video=mysqli_fetch_object($resvideo))
                                                     {
                                     $cv_video_link = $row_video->adv_link;
                                     $adv_redirect = $row_video->adv_redirect;
                                     $chklink = explode("://",$cv_video_link);
                                     if($chklink[0]=="http" || $chklink[0]=="https")
                                     {
                                       preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$cv_video_link,$matches);
                                       $id = $matches[1];
                                       $width = '100%';
                                       $height = '181';
                                       $iframe2show ='<iframe width="100%" height="181"
                                                                 src="https://www.youtube.com/embed/'.$id.'" frameborder="0"
                                                                 allowfullscreen></iframe>';
                                     }
                                     else
                                     {
                                       $iframe2show = $cv_video_link;
                                     }
                                     $bnsprof_compname = $row_video->adv_title;
                                     $bnsprof_address1 = $row_video->adv_description;
                                   ?>
                              <li>
                                 <?php echo $iframe2show;?>
                                 <div class="iframebox">
                                    <h2><i class="fa fa-play"></i><a href="<?php echo $adv_redirect;?>"target="_blank"><?php echo substr(($bnsprof_compname),0,22).".."?></a></h2>
                                    <p><?php echo substr(($bnsprof_address1),0,30).".."?>
                                 </div>
                              </li>
                              <?php }?><?php } ?>
                           </ul>
                        </div>
                        <div class="verifiedbox_bottom">
                           <h4><a href="company-video.php">Post <span>FREE </span>Company Video</a></h4>
                           <div class=verifiedbox_supplierbox>
                              <h3>Verified Suppliers</h3>
                              <p>Selected Supplier from around the world <span class="fright"><a href="membership_plans.php">Learn More > ></a></span>
                              <div class=clear></div>
                              <ul>
                                 <li><a href="membership_plans.php" class="tooltip1"><img alt=""src="images/verified01.jpg"> <span><i>SPONSOR Supplier</i></span></a>
                                 <li><a href="membership_plans.php" class="tooltip1"><img alt=""src="images/verified02.jpg"> <span><i>SENIOR Supplier</i></span></a>
                                 <li><a href="membership_plans.php" class="tooltip1"><img alt=""src="images/verified03.jpg"> <span><i>JUNIOR Supplier</i></span></a>
                              </ul>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="countrybox">
                     <div class="countrubox_top">
                        <div class="countrubox_heading">
                           <div class="globalicon"><b>GLOBAL PAGE
                              </b> <a href="#" onclick="unsetCountryLocation();"><img alt=Global src="images/Untit.png"></a>
                           </div>
                           <h2>ALL <span> ARABIAN SUPPLIERS </span></h2>
                        </div>
                        <div class="search">
                           <input id="search" name="search" class="textbox" placeholder="Search Country"> <input id="submit" name="submit" type="submit" value="Subscribe"><script>$("#submit").click(function(){var c=document.getElementById("search").value,e="cname="+c;$.ajax({type:"POST",url:"search_country.php",data:e,cache:!1,success:function(c){$("#response").html(c)}})})</script>
                           <div id="response"></div>
                        </div>
                        <div class="clear"></div>
                     </div>
                     <div class="cnt1">
                        <table>
                           <tr>
                              <td><span><b>Asia   :</b></span>
                              <td>
                                 <ul class="country">
                                    <li><a href="#" onclick="setCountryLocation(225);"><img alt=""src="images/uae.jpg"> <span style="color:#4163a9;">UAE<span></a>
                                    <li><a href="#" onclick="setCountryLocation(187)"><img alt=""src="images/Saudi-Arabia.jpg"> <span style="color:#4163a9">Saudi Arb.<span></a>
                                    <li><a href="#" onclick="setCountryLocation(112)"><img alt=""src="images/Kuwait.jpg"> <span style="color:#4163a9">Kuwait<span></a>
                                    <li><a href="#" onclick="setCountryLocation(173)"><img alt=""src="images/Qatar.jpg"> <span style="color:#4163a9">Qatar<span></a>
                                    <li><a href="#" onclick="setCountryLocation(108)"><img alt=""src="images/jordan.jpg"> <span style="color:#4163a9">Jordan<span></a>
                                    <li><a href="#" onclick="setCountryLocation(116)"><img alt=""src="images/Lebanon.jpg"> <span style="color:#4163a9">Lebanon<span></a>
                                    <li><a href="#" onclick="setCountryLocation(237)"><img alt=""src="images/yemen.jpg"> <span style="color:#4163a9">Yemen<span></a>
                                    <li><a href="#" onclick="setCountryLocation(101)"><img alt=""src="images/iraq.jpg"> <span style="color:#4163a9">Iraq<span></a>
                                    <li><a href="#" onclick="setCountryLocation(208)"><img alt=""src="images/Syria.jpg"> <span style="color:#4163a9">Syria<span></a>
                                    <li><a href="#" onclick="setCountryLocation(163)"><img alt=""src="images/Palestine.jpg"> <span style="color:#4163a9">Palestine<span></a>
                                 </ul>
                        </table>
                     </div>
                     <div class="cnt1">
                        <table>
                           <tr>
                              <td><span><b>Africa:</b></span>
                              <td>
                                 <ul class="country">
                                    <li><a href="#" onclick="setCountryLocation(63)">
                                      <img alt=""src="images/flag01.png"> <span style="color:#4163a9">Egypt<span></a>
                                    <li><a href="#" onclick="setCountryLocation(202)"><img alt=""src="images/sudan.jpg"> <span style="color:#4163a9">Sudan<span></a>
                                    <li><a href="#" onclick="setCountryLocation(119)"><img alt=""src="images/Libya.jpg"> <span style="color:#4163a9">Libya<span></a>
                                    <li><a href="#" onclick="setCountryLocation(142)"><img alt=""src="images/morroco.jpg"> <span style="color:#4163a9">Morocco<span></a>
                                    <li><a href="#" onclick="setCountryLocation(3)"><img alt=""src="images/flag02.png"> <span style="color:#4163a9">Algeria<span></a>
                                    <li><a href="#" onclick="setCountryLocation(217)"><img alt=""src="images/Tunisia.jpg"> <span style="color:#4163a9">Tunisia<span></a>
                                    <li><a href="#" onclick="setCountryLocation(133)"><img alt=""src="images/Mauritania.jpg"> <span style="color:#4163a9">Mauritania<span></a>
                                    <li><a href="#" onclick="setCountryLocation(58)"><img alt=""src="images/Djibouti.jpg"> <span style="color:#4163a9">Djibouti<span></a>
                                    <li><a href="#" onclick="setCountryLocation(196)"><img alt=""src="images/Somalia.jpg"> <span style="color:#4163a9">Somalia<span></a>
                                    <li><a href="#" onclick="setCountryLocation(49)"><img alt=""src="images/Comoros.jpg"> <span style="color:#4163a9">Comoros<span></a>
                                 </ul>
                        </table>
                     </div>
                  </div>
                  <div class="space21"></div>
                  <div class="countrubox_top2">
                     <div class="countrubox_heading">
                        <h2>View <a href="dir.php#main_cat"><span>Products & Suppliers</span></a></h2>
                     </div>
                     <div class="list-rights">
                        <h2><a href="product-sel-cat.php"><span>Display</span> Your Products</a></h2>
                     </div>
                     <div class="clear"></div>
                  </div>
                  <div class="demobox">
                     <div class="col-md-12">
                        <div class="white_bg">
                           <div class="clear" style="height:5px"></div>
                           <div class="welcome_desc">
                              <div class="course_demo">
                                 <?php
                                    if (isset($_COOKIE['loc_id'])) {
                                    
                                        $sql_pd_ck = " and (
                                    (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                        /*
                                          (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                          or
                                          (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                         */
                                    
                                        $sql_so_ck = " and (
                                    (so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                        /*
                                          (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and so_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                          or
                                          (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                    
                                         */
                                    
                                        $sql_br_ck = " and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                        /*
                                          (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                          or
                                          (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                         */
                                    
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
                                    
                                    
                                    
                                    if($_COOKIE['loc_id']!="")
                                    {
                                    $sql_prd = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24"; }
                                    else
                                    {
                                    $sql_prd = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24"; 
                                    }  
                                    //echo $sql_prd;
                                    $res_prd = mysqli_query( $con,$sql_prd);
                                    $total_rows = mysqli_num_rows($res_prd);
                                    $re_rows = $total_rows%2;
                                    if ($total_rows > 0) {
                                    $indx1=1;
                                        ?><?php
                                    $useragent = $_SERVER['HTTP_USER_AGENT']; ?>
                                 <ul id="flexiselDemo1">
                                    <?php
                                       while ($row_prd = mysqli_fetch_object($res_prd)) {
                                       if($indx1%2==1){
                                           ?>
                                    <li>
                                       <?php }
                                          $row_bprof = mysqli_fetch_object(mysqli_query( $con,"select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
                                          $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = ".$row_bprof->bnsprof_id;
                                          //echo $row_bprof->bnsprof_id;
                                          //echo $sql_icon;
                                          $get_icon = mysql_query($sql_icon) or die(mysql_error());?>
                                       <a href="company/products.php?c=<?php echo rand(10, 9999) . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $row_prd->pd_subcat_id; ?>#<?php echo $row_prd->pd_id; ?>"target="_blank" style="text-decoration:none;color:#000">
                                          <img alt="<?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?>"src="upload/myproduct/thumb/<?php echo $row_prd->pd_image; ?>"class="img-responsive black" title="<?php echo ucwords($row_prd->pd_title); ?>">
                                          <?php 
                                             if(count($row_prd->pd_imagelogo) && $row_prd->pd_imagelogo!='' && !empty($row_prd->pd_imagelogo)){ 
                                             
                                                $logo=explode(',',  $row_prd->pd_imagelogo); ?>
                                          <img alt="<?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?>"src="upload/myproduct/<?php echo $logo[0]; ?>" style="position: absolute;bottom: 59px; left: 6px; width: 60px; height: 60px;" class="img-responsive" title="<?php echo ucwords($row_prd->pd_title); ?>">
                                          <?php }  ?>
                                          <!--position: relative;bottom: 65px;left: -42px;-->
                                          <div class="matterbox">
                                             <div class="icon_pic"><?php if(mysql_num_rows($get_icon) > 0){
                                                $title = 'Junior';
                                                $icon = mysql_fetch_array($get_icon);
                                                
                                                if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
                                                $title = 'Senior';
                                                }
                                                else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
                                                $title = 'Sponsor';
                                                }
                                                
                                                                          ?><img alt=""src="admin/images/<?php echo $icon['mst_icon']; ?>"class="img-responsive" title="<?php echo strtoupper($title); ?>"style="width:18px;height:15px"><?php }
                                                else { ?><img alt=""src="images/slider-icon01.jpg" class="img-responsive" title="JUNIOR"><?php } ?></div>
                                             <div class="rightmatter">
                                                <h3 class="ihoves">
                                                   <?php  if( strlen($row_prd->pd_title) > 20 ){ echo substr( $row_prd->pd_title, 0, 20 ).'...'; }else{ echo $row_prd->pd_title; }   ?>
                                                   <div class="auction_hover">
                                                      <p><?php echo ucwords($row_prd->pd_title); ?></p>
                                                   </div>
                                                </h3>
                                                <p><span class="nam"><?php echo get_country_name($row_prd->country);?></span><br>
                                                <p>MOQ: <span class="nam"><?php echo $row_prd->pd_min_order_qty; ?><?php echo $row_prd->mu_name; ?></span><br>
                                                <p><?php echo $row_prd->cn_currency; ?><span style="font-size:15px!important" class="nam"><?php echo $row_prd->pd_fob_price ?>/</span><?php echo $row_prd->mu_name; ?>
                                                <div class="clear"></div>
                                             </div>
                                             <div class="clear"></div>
                                          </div>
                                       </a>
                                       <?php if($indx1%2==0){ echo '</li>';}
                                          $indx1++;
                                          }
                                          if($re_rows==1){ echo '</li>';} ?>
                                 </ul>
                                 <?php
                                    } else {
                                        ?><?php }
                                    ?><script>$(window).load(function(){$("#flexiselDemo1").flexisel({visibleItems:4,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                                    <script src="js/jquery.flexisel.js"></script>
                              </div>
                              <div class="learnmores">
                                 <p><a href=dir.php#main_cat target=_blank>View All Categories <span>>></span></a>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="demobox">
                     <div class="countrubox_top">
                        <div class="countrubox_heading">
                           <h2>Temporary <a href="sale-offers.php"><span>Sale Offers Ads</span></a></h2>
                        </div>
                        <div class="list-rights">
                           <h2><a href="post-sell-offer.php"><span>Post</span> Sale Offers Ads</a></h2>
                        </div>
                        <div class="clear"></div>
                     </div>
                     <div class="blank_bg">
                        <div class="welcome_desc">
                           <div class="course_demo">
                              <?php
                                 $sql_so = "select * from sale_offer,user,business_profile where so_usr_id=usr_id and usr_id=bnsprof_uid and so_approval_status='1' and so_status='1' and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)>=now() " . $sql_so_ck . " order by rand()";
                                 
                                 $res_so = mysqli_query($con, $sql_so);
                                 if ($res_so->num_rows > 0) {
                                             ?>
                              <ul id="flexiselDemo2">
                                 <?php
                                    while ($row_so = mysqli_fetch_object($res_so)) {
                                        ?>
                                 <li>
                                    <?php
                                       $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
                                       $sql_icon = "select sip.mst_icon ,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = ".$row_so->bnsprof_id;
                                       $get_icon = mysql_query($sql_icon) or die(mysql_error());
                                       ?>
                                    <a href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5($row_so->so_id); ?>"target="_blank" style="text-decoration:none;color:#000">
                                       <img alt="<?php echo ucwords(substr($row_so->so_service, 0, 25)); ?>"src="upload/sale_offer/thumb/<?php echo $row_so->so_pic; ?>"class="img-responsive" title="<?php echo ucwords($row_so->so_service); ?>">
                                       <div class="matterbox">
                                          <div class="icon_pic"><?php if(mysql_num_rows($get_icon) > 0){
                                             $title = 'Junior';
                                             $icon = mysql_fetch_array($get_icon);
                                             
                                             if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
                                             $title = 'Senior';
                                             }
                                             else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
                                             $title = 'Sponsor';
                                             }
                                             $title = 'Junior';
                                             $icon = mysql_fetch_array($get_icon);
                                             
                                             ?><img alt=""src="admin/images/<?php echo ($icon['mst_icon'] != '')?$icon['mst_icon']:'slider-icon01.jpg'; ?>"class="img-responsive" title="<?php echo strtoupper($title); ?>"style="width:18px;height:15px"><?php }
                                             else { ?><img alt=""src="images/slider-icon01.jpg" class="img-responsive" title="JUNIOR"><?php } ?></div>
                                          <div class="rightmatter">
                                             <h3 class="ihoves">
                                                <?php echo ucwords(substr($row_so->so_service, 0, 20)); ?><?php if (strlen($row_so->so_service) > 21) { ?>...<?php } ?>
                                                <div class="auction_hover">
                                                   <p><?php echo ucwords($row_so->so_service); ?></p>
                                                </div>
                                             </h3>
                                             <p><?php
                                                //  $sql_cat="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row_so->so_pc_id."')";
                                                $sql_cat = "select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id=(select pc_parent_id from product_category where pc_id='" . $row_so->so_pc_id . "'))";
                                                $res_cat = mysqli_query($con, $sql_cat);
                                                $row_cat = mysqli_fetch_object($res_cat);
                                                echo "(" . $row_cat->pc_name . ")"; ?><br>
                                             <p><?php echo substr($row_so->so_description, 0, 25); ?><?php if (strlen($row_so->so_description) > 26) { ?>...<?php } ?>
                                             <div class="clear"></div>
                                          </div>
                                          <div class="clear"></div>
                                       </div>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                              <?php
                                 } else {
                                     ?><?php }
                                 ?><script>$(window).load(function(){$("#flexiselDemo2").flexisel({visibleItems:4,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                           </div>
                           <div class="learnmores">
                              <p><a href="sale-offers.php" target="_blank">View all sale Offers <span>>></span></a>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="center-top">
                     <?php
                        $banner=GetHomeBanner('middle');
                        if($banner!="")
                        {
                             echo '<div class="middle" style="padding:0; width:100%;">';
                          echo $banner;
                        }
                        else
                        {
                          echo '<div class="middle mid-content">';
                          echo ' <h3>Banner Place</h3>';
                        }
                        ?>
                     <div class="clear"></div>
                  </div>
               </div>
               <div class="demobox">
                  <div class="booking"><a href="advertise-with-us.php"> Advertise Here Now for <span>FREE</span> Kindly Contact Advertisements Team </a></div>
                  <div class="clear"></div>
                  <div class="countrubox_top2">
                     <div class="countrubox_heading">
                        <div class="mainflagbox">
                           <div class="membershipicon"><a href="membership_plans.php"><img alt=""src="images/membership-icon01.png"></a></div>
                           <div class="membershipicon"><a href="membership_plans.php"><img alt=""src="images/membership_icon02.png"></a></div>
                        </div>
                        <div class="countryheadingboxleft">
                           <h3><a href="#">ARABYOS Leading Products</a></h3>
                        </div>
                     </div>
                     <div class="list-rights">
                        <h2><a href="advertise-with-us.php" target="_blank"><span>Post</span> Premium Ads</a></h2>
                     </div>
                     <div class="clear"></div>
                  </div>
                  <div class="col-md-12">
                     <div class="white_bg">
                        <div class="clear" style="height:5px"></div>
                        <div class="welcome_desc">
                           <div class="course_demo">
                              <?php
                                 $sqlleading = "select * from prodservice_slider where adv_status='1' and adv_type='1' $strconutnry";
                                 $rsleading = mysqli_query($con,$sqlleading);
                                 $totalbaneer = mysqli_num_rows($rsleading);
                                 $rembaner = $totalbaneer%2;
                                 if($totalbaneer> 0)
                                 {?>
                              <ul id="flexiselDemo4">
                                 <?php
                                    $indx=1;
                                    while($rowleading = mysqli_fetch_object($rsleading))
                                     {
                                       $adv_id = $rowleading->adv_id;
                                       $pd_image = $rowleading->adv_img;
                                       $pd_title = $rowleading->adv_title;
                                       $pd_fob_price = $rowleading->adv_price;
                                       $adv_imagewidth = $rowleading->adv_imagewidth;
                                       $adv_imageheight = $rowleading->adv_imageheight;
                                       $adv_icon = $rowleading->adv_icon;
                                       $adv_link = $rowleading->adv_link;
                                    
                                       $adv_piece = $rowleading->adv_piece;
                                       $adv_currency = $currency_symbols[$rowleading->adv_currency];
                                       if($indx%2==1){ echo '<li>';}
                                    ?>
                                 <a href="<?php echo $adv_link;?>"target="_blank">
                                    <div class="demobox">
                                       <img alt=""src="upload/product_slider/<?php if($pd_image!=''){ echo $pd_image; }else{ echo 'noimage.jpg';  } ?>"class="black">
                                       <div class="matterbox">
                                          <div class="icon_pic"><img alt=""src="images/<?php echo $adv_icon;?>"class="img-responsive" width="18"></div>
                                          <div class="rightmatter">
                                             <h3 class="ihoves">
                                                <?php echo ucwords(substr($pd_title, 0,15)); ?><?php if (strlen($pd_title) > 15) { ?>...<?php } ?>
                                                <div class="auction_hover">
                                                   <p><?php echo ucwords($pd_title); ?></p>
                                                </div>
                                             </h3>
                                             <p><span class="nam"><?php echo $cn_name;?></span><br>
                                             <p>MOQ: <span class="nam"><?php echo $adv_piece;?>Pyeces</span><br>
                                             <p><?php //echo $cn_name;?><span><ins><?php echo $adv_currency;?></ins><?php echo $pd_fob_price;?></span>/ PIECES
                                             <div class="clear"></div>
                                          </div>
                                          <div class="clear"></div>
                                       </div>
                                    </div>
                                 </a>
                                 <?php
                                    if($indx%2==0){ echo '</li>';}
                                    $indx++;
                                    }
                                    if($rembaner==1){ echo '</li>';}
                                    ?>
                              </ul>
                              <?php }?><script>$(window).load(function(){$("#flexiselDemo4").flexisel({visibleItems:4,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                           </div>
                        </div>
                        <div class="clear" style="height:1px"></div>
                     </div>
                  </div>
               </div>
               <div class="demobox">
                  <div class="countrubox_top3">
                     <div class="countrubox_heading">
                        <div class="mainflagbox">
                           <div class="membershipicon2"><a href="#"><img alt=""src="images/membership_icon03.png"></a></div>
                        </div>
                        <div class="countryheadingboxleft">
                           <h3><a href="advertise-with-us.php"><span>Loyal</span> Business Services</a></h3>
                        </div>
                     </div>
                     <div class="list-rights">
                        <h2><a href="product-sel-cat.php" target="_blank"><span>Post</span> Business Services</a></h2>
                     </div>
                     <div class="clear"></div>
                  </div>
                  <div class="col-md-12">
                     <div class="bottom_bg">
                        <div class="welcome_desc">
                           <div class="course_demo">
                              <?php
                                 $sqlleading = "select * from prodservice_slider where adv_status='1' and adv_type='2' $strconutnry";
                                 //echo $sqlleading;
                                 $rsleading = mysqli_query($con,$sqlleading);
                                 $totalbaneer = mysqli_num_rows($rsleading);
                                 $rembaner = $totalbaneer%2;
                                 if($totalbaneer> 0)
                                 {?>
                              <ul id="flexiselDemo5">
                                 <?php
                                    $indx=1;
                                    $ccc = 1;
                                    while($rowleading = mysqli_fetch_object($rsleading))
                                     {
                                       $adv_id = $rowleading->adv_id;
                                       $pd_image = $rowleading->adv_img;
                                       $pd_title = $rowleading->adv_title;
                                       $pd_fob_price = $rowleading->adv_price;
                                       $adv_imagewidth = $rowleading->adv_imagewidth;
                                       $adv_imageheight = $rowleading->adv_imageheight;
                                       $adv_icon = $rowleading->adv_icon;
                                       $adv_link = $rowleading->adv_link;
                                       $adv_piece = $rowleading->adv_piece;
                                       $adv_currency = $currency_symbols[$rowleading->adv_currency];
                                       if($indx%2==1){ echo '<li>';}
                                    ?>
                                 <a href="<?php echo $adv_link;?>"target=_blank>
                                    <div class="demobox">
                                       <img alt=""src="upload/service_slider/<?php if($pd_image!=''){ echo $pd_image; }else{ echo 'noimage.jpg';  } ?>"class="black" style="max-width:115px">
                                       <div class="matterbox">
                                          <div class="icon_pic"><img alt=""src="images/<?php echo $adv_icon;?>"class="img-responsive" width="18"></div>
                                          <div class="rightmatter">
                                             <h3 class="ihoves">
                                                <?php  if( strlen($pd_title) > 20 ){ echo substr( $pd_title, 0, 20 ).'...'; }else{ echo $pd_title; }   ?>
                                                <div class="auction_hover">
                                                   <p><?php echo $pd_title;?></p>
                                                </div>
                                             </h3>
                                             <p><span class="nam"><?php echo $cn_name;?></span><br>
                                             <p>MOQ: <span class="nam"><?php echo $adv_piece;?>Pyeces</span><br>
                                             <p><?php //echo $cn_name;?><span><ins><?php echo $adv_currency;?></ins><?php echo $pd_fob_price;?></span>/ PIECES
                                             <div class="clear"></div>
                                          </div>
                                          <div class="clear"></div>
                                       </div>
                                    </div>
                                 </a>
                                 <?php
                                    if($indx%2==0){ echo '</li>';}
                                    $indx++;
                                    }
                                    if($rembaner==1){ echo '</li>';}
                                    ?>
                              </ul>
                              <?php }?><script>$(window).load(function(){$("#flexiselDemo5").flexisel({visibleItems:5,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="demobox">
                  <div class="countrubox_top4">
                     <div class="countrubox_heading">
                        <div class="countryheadingboxleft">
                           <h3><a href="#">Sponsors Supplier</a></h3>
                        </div>
                     </div>
                     <div class="list-rights">
                        <h2><a href="contact_us.php target=_blank"><span>Add</span> Your Logo</a></h2>
                     </div>
                     <div class="clear"></div>
                  </div>
                  <div class="white_bg">
                     <div class="welcome_desc">
                        <div class="course_demo">
                           <ul id="flexiselDemo8">
                              <?php
                                 $sqllogo = "select * from supplier_logo where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
                                 $rslogo = mysqli_query($con,$sqllogo);
                                 if(mysqli_num_rows($rslogo) > 0)
                                 {
                                   while($rowlogo= mysqli_fetch_object($rslogo))
                                   {
                                     $adv_img = $rowlogo->adv_img;
                                     $logopath = "upload/supplier_logo/".$adv_img;
                                     $adv_link = $rowlogo->adv_link;
                                 ?>
                              <li><a href="<?php echo $adv_link;?>"target=_blank><img alt=""src="<?php echo $logopath;?>"class="img-responsive"></a></li>
                              <?php } }
                                 else
                                 {
                                   echo '';
                                 }
                                 ?>
                           </ul>
                        </div>
                     </div>
                  </div>
                  <script>$(window).load(function(){$("#flexiselDemo8").flexisel({visibleItems:5,animationSpeed:1e3,autoPlay:!0,autoPlaySpeed:3e3,pauseOnHover:!0,enableResponsiveBreakpoints:!0,responsiveBreakpoints:{portrait:{changePoint:480,visibleItems:1},landscape:{changePoint:640,visibleItems:2},tablet:{changePoint:768,visibleItems:2}}})})</script>
                  <?php /* <div class=whitefooter><ul><?php
                     $sqllogo = "select * from supplier_logo where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
                     $rslogo = mysqli_query($con,$sqllogo);
                     if(mysqli_num_rows($rslogo) > 0)
                     {
                       while($rowlogo= mysqli_fetch_object($rslogo))
                       {
                         $adv_img = $rowlogo->adv_img;
                         $logopath = "upload/supplier_logo/".$adv_img;
                         $adv_link = $rowlogo->adv_link;
                     ?>
                  <li><a href="<?php echo $adv_link;?>"target=_blank><img alt=""src="<?php echo $logopath;?>"class=img-responsive></a></li>
                  <?php } }
                     else
                     {
                       echo '';
                     }
                     ?></ul>
                  <div class=clear></div>
               </div>
               */ ?> 
               <div class="mid-top">
                  <?php
                     $banner=GetHomeBanner('bottom',$strconutnry);
                     if($banner!="")
                     {
                         echo '<div class="middle" style="padding:0;">';
                       echo $banner;
                        echo '</div>';
                     }
                     else
                     {
                       //echo '<div class="middle mid-content">';
                       //echo ' <h3>Banner Place</h3>';
                        //echo '</div>';
                     }
                     ?>
                  <div class="clear"></div>
               </div>
            </div>
         </div>
         <div id="rightsection">
            <div class="buyleads">
               <div class="leftleads">
                  <h2><a href="buyleads.php">Live Buy Requests <i class="fa fa-caret-right"></i></a></h2>
               </div>
               <?php
                  $sql_br1 = "select * from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand()";
                  $res_br1 = mysqli_query($con, $sql_br1);
                  $cnt_br1 = $res_br1->num_rows;
                  
                  
                  $sql_br = "select * from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand() limit 0, 5";
                  $res_br = mysqli_query($con, $sql_br);
                  $cnt_br = $res_br->num_rows;
                  if ($cnt_br > 0) {
                  ?>
               <div class="bgc1 brd bx2">
                  <?php
                     $x = (int)$cnt_br1;
                     $c = (int)0;
                     while ($x != 0) {
                         $x = (int)$x / 10;
                         $c = (int)$c + 1;
                     }
                     $str = "";
                     for ($i = $c; $i <= 4; $i++) {
                         $str = $str . '0';
                     }
                     //echo $str;
                     ?>
                  <div class="rightnumber"><span class="number-count"><?php echo $str . $cnt_br1; ?></span><span class="off tic1"><?php echo $cnt_br1; ?></span></div>
                  <script>
                     $(function(){
                     var val=$('.number-count').html().split('');
                     console.log(val);
                     str="";
                     for(i=0;i<val.length;i++)
                     {
                     str+='<div>'+val[i]+'</div>';
                     
                     }
                     $('.number-count').html(str);
                     });
                  </script>
                  <div class="clear"></div>
                  <div class="buybox">
                     <?php
                        while ($row_br = mysqli_fetch_object($res_br)) {
                        ?>
                     <div class="popular-post-grid">
                        <h3><a href="buyleads-details.php?id=<?php echo rand(10,9999).md5($row_br->br_id); ?>"target="_blank"><?php echo ucwords(stripslashes($row_br->br_pd_name)); ?></a></h3>
                        <div class="tendersbox">
                           <div class="verifiedbox">
                              <div class="cover"><img alt=""src="images/tick.png"> Verified & Updated</div>
                              <div class="date"><?php if ($row_br->br_estimate_qty != '0' && $row_br->br_estimate_qty != '') { ?><b>Quantity :</b><?php echo $row_br->br_estimate_qty; ?><?php echo measurement_unit($row_br->br_estimate_qty_unit); ?>(s).<?php } ?></div>
                           </div>
                           <div class="flagbox">
                              <?php
                                 $contyname=get_country_name($row_br->country);
                                 $cntryflag = get_country_flag($row_br->country);
                                 if($cntryflag!="")
                                 {
                                   $flag2show = '<img src="images/country_flag/'.$cntryflag.'" alt="">';
                                 }
                                 ?>
                              <ul>
                                 <li style="height:auto"><a href="#"><?php echo $contyname." ".$flag2show;?></a>
                              </ul>
                              <div class="date"><span><?php
                                 if($row_br->br_preferred_supplier_location == '')
                                   {
                                     echo get_country_name($row_br->country);
                                   }else
                                   {
                                     if ($row_br->br_preferred_supplier_location == 'any') {
                                       echo "Anywhere";
                                     } else if ($row_br->br_preferred_supplier_location == 'abroad') {
                                       echo "Foreign";
                                     } else if ($row_br->br_preferred_supplier_location == 'domestic') {
                                       echo get_country_name($row_br->country);
                                     } else if ($row_br->br_preferred_supplier_location == 'my_city' && $row_br->bnsprof_city != '0') {
                                       echo get_city_name($row_br->bnsprof_city);
                                     }
                                   }
                                  ?></span></div>
                           </div>
                        </div>
                        <div class="clear"></div>
                     </div>
                     <?php } ?>
                     <div class="learnmore">
                        <p><a href="buyleads.php" target="_blank">All Live Buy Requests<span>>></span></a>
                     </div>
                  </div>
                  <div class="clear"></div>
               </div>
               <?php } ?><br><b>
               <div class="sap_tabs">
                  <div id="horizontalTab" style="display:block;width:100%;margin:0;">
                     <ul class="resp-tabs-list">
                        <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><a href="tenders.php"><span><b>Tenders</b></span></a>
                        <li class=resp-tab-item aria-controls=tab_item-1 role=tab><a href=auctions.php><span><b>Auctions</b></span></a></li>
                        <div class="clear"></div>
                     </ul>
                     <div class="resp-tabs-container">
                        <div class="resp-tab-content tab-1"aria-labelledby="tab_item-0">
                           <?php
                              if($cn_id!="")
                               {
                                 $strtender=" AND user.country='$cn_id'";
                               }
                               else
                               {
                                 $strtender ="";
                               }
                              $sqltender = "select tnd_id,tnd_heading,tnd_due_date,tnd_preferred_location,country from tender,user where tnd_status='1' and tnd_approval_status = 1 and tender.tnd_usr_id=user.usr_id $strtender AND tender.tnd_due_date >= curdate() order by tnd_publish_date DESC LIMIT 4";
                              //echo $sqltender;
                              $rstender = mysqli_query($con,$sqltender) or die("Error".mysqli_erorr());
                              if(mysqli_num_rows($rstender))
                              {
                                echo '<ul class="tab_img">';
                                while($rowtender = mysqli_fetch_object($rstender))
                                {
                                  $tnd_heading = $rowtender->tnd_heading;
                                  $tnd_due_date= $rowtender->tnd_due_date;
                              
                              ?>
                           <li>
                              <div class="popular-post-grids">
                                 <div class="popular-post-grid">
                                    <h3><a href="tender-details.php?id=<?php echo rand(10,9999).md5($rowtender->tnd_id); ?>"target="_blank"><?php echo $tnd_heading;?></a></h3>
                                    <div class="tendersbox">
                                       <div class="verifiedbox">
                                          <div class="cover"><img alt=""src="images/tick.png"> Verified & Updated</div>
                                          <div class="date"><b>Due Date: </b><?php echo $tnd_due_date;?></div>
                                       </div>
                                       <div class="flagbox">
                                          <?php
                                             $contyname=get_country_name($rowtender->country);
                                             $cntryflag = get_country_flag($rowtender->country);
                                             if($cntryflag!="")
                                             {
                                               $flag2show = '<img src="images/country_flag/'.$cntryflag.'" alt="">';
                                             }
                                             ?>
                                          <ul>
                                             <li><a href="#"><?php echo $contyname." ".$flag2show;?></a>
                                          </ul>
                                          <div class="date"><span><?php
                                             if($rowtender->tnd_preferred_location == '')
                                               {
                                                 echo get_country_name($rowtender->country);
                                               }else
                                               {
                                                 if ($rowtender->tnd_preferred_location == 'any') {
                                                   echo "Anywhere";
                                                 } else if ($rowtender->tnd_preferred_location == 'abroad') {
                                                   echo "Foreign";
                                                 } else if ($rowtender->tnd_preferred_location == 'domestic') {
                                                   echo get_country_name($rowtender->country);
                                                 } else if ($rowtender->tnd_preferred_location == 'my_city' && $rowtender->country != '0') {
                                                   echo get_city_name($rowtender->country);
                                                 }
                                               }
                                              ?></span></div>
                                       </div>
                                    </div>
                                    <div class="clear"></div>
                                 </div>
                              </div>
                           </li>
                           <?php } ?>
                           <div class="clear"></div>
                           <div class="learnmore">
                              <p><a href="#">View all <a href="tenders.php">Tenders</a> / <a href="auctions.php">Auctions</a></a>
                           </div>
                           <div class="tabbotton"><a href="#"><span>Publish</span> <a href="post-tender.php">Tender</a>/</a> <a href="post-auction.php">Auction</a> <span>FREE</span></div>
                           <?php }?>
                        </div>
                        <div class="resp-tab-content tab-1"aria-labelledby="tab_item-1">
                           <ul class="tab_img">
                              <?php
                                 if($cn_id!="")
                                  {
                                    $strauction=" AND user.country='$cn_id'";
                                  }
                                  else
                                  {
                                    $strauction ="";
                                  }
                                 $sqltender = "select auc_id,auc_heading,auc_due_date,auc_preferred_location,country from auction,user where auc_status='1' and auction.auc_usr_id=user.usr_id $strauction  AND auction.auc_due_date >= curdate() and auc_approval_status =1 order by auc_publish_date DESC LIMIT 5";
                                 $rstender = mysqli_query($con,$sqltender) or die("Error".mysqli_erorr());
                                 if(mysqli_num_rows($rstender))
                                 {
                                   echo '<ul class="tab_img">';
                                   while($rowaution = mysqli_fetch_object($rstender))
                                   {
                                     $tnd_heading = $rowaution->auc_heading;
                                     $auc_due_date= $rowaution->auc_due_date;
                                 
                                 ?>
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <h3><a href="auction-details.php?id=<?php echo rand(1000,9999).md5($rowaution->auc_id); ?>"target="_blank"><?php echo $tnd_heading;?></a></h3>
                                       <div class="tendersbox">
                                          <div class="verifiedbox">
                                             <div class="cover"><img alt=""src="images/tick.png"> Verified & Updated</div>
                                             <div class="date"><b>Due Date: </b><?php echo $auc_due_date;?></div>
                                          </div>
                                          <div class="flagbox">
                                             <?php
                                                $contyname=get_country_name($rowaution->country);
                                                $cntryflag = get_country_flag($rowaution->country);
                                                if($cntryflag!="")
                                                {
                                                  $flag2show = '<img src="images/country_flag/'.$cntryflag.'" alt="">';
                                                }
                                                ?>
                                             <ul>
                                                <li><a href="#"><?php echo $contyname." ".$flag2show;?></a>
                                             </ul>
                                             <div class="date"><span><?php
                                                if($rowaution->auc_preferred_location == '')
                                                  {
                                                    echo get_country_name($rowaution->country);
                                                  }else
                                                  {
                                                    if ($rowaution->auc_preferred_location == 'any') {
                                                      echo "Anywhere";
                                                    } else if ($rowaution->auc_preferred_location == 'abroad') {
                                                      echo "Foreign";
                                                    } else if ($rowaution->auc_preferred_location == 'domestic') {
                                                      echo get_country_name($rowaution->country);
                                                    } else if ($rowaution->auc_preferred_location == 'my_city' && $rowaution->country != '0') {
                                                      echo get_city_name($rowaution->country);
                                                    }
                                                  }
                                                 ?></span></div>
                                          </div>
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              </li>
                              <?php } ?>
                           </ul>
                           <?php }?>
                           <div class="clearfix"></div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="sap_tabs">
                  <div id="horizontalTab1" style="display:block;width:100%;margin:0">
                     <ul class="resp-tabs-list">
                        <li class="resp-tab-item" aria-controls="tab_item-0" role="tab">
                           <span>
                              <h5>For Buying</h5>
                           </span>
                        <li class="resp-tab-item" aria-controls="tab_item-1" role="tab">
                           <span>
                              <h5>For Supplying</h5>
                           </span>
                        </li>
                        <div class="clear"></div>
                     </ul>
                     <div class="resp-tabs-container" style="border:#e4e4e4 1px solid">
                        <div class="resp-tab-content tab-1" aria-labelledby="tab_item-0">
                           <ul class="tab_img">
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <div class="post-img"><a href="#"><img alt=""src="images/email-icon.jpg" class="img-responsive"></a></div>
                                       <div class="post-text">
                                          <a href="post-buy-req.php" class="pp-title">Send us your Buy Requirement</a>
                                          <p>Receive Responses from pre-verified and qualified suppliers.
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <div class="post-img"><a href="#"><img alt=""src="images/search.jpg" class="img-responsive"></a></div>
                                       <div class="post-text">
                                          <a href="search_adv.php" class="pp-title">Search for a product</a>
                                          <p>Send enquiries directly to the Suppliers of your Choice.
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <div class="post-img"><a href="#"><img alt=""src="images/bell.jpg" class="img-responsive"></a></div>
                                       <div class="post-text">
                                          <a href="manage-selloffer-alert.php" class="pp-title">Subscribe to sell offers Alerts</a>
                                          <p>Get updates on relevant products and sell offers directly in your email.
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              </li>
                              <div class="clearfix"></div>
                           </ul>
                        </div>
                        <div class="resp-tab-content tab-1" aria-labelledby="tab_item-1">
                           <ul class="tab_img">
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <div class="post-img"><a href="#"><img alt=""src="images/boxrect.png" class="img-responsive"></a></div>
                                       <div class="post-text">
                                          <a href="product-sel-cat.php" class="pp-title">Display your products/ services</a>
                                          <p>Receive responses from domestic & global buyers.
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <div class="post-img"><a href="#"><img alt=""src="images/criclearrow.png" class="img-responsive"></a></div>
                                       <div class="post-text">
                                          <a href="product-sel-cat.php" class="pp-title">Create company website</a>
                                          <p>Promote your company in a huge online presence for home and global sales.
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              <li>
                                 <div class="popular-post-grids">
                                    <div class="popular-post-grid">
                                       <div class="post-img"><a href="#"><img alt=""src="images/bell.jpg" class="img-responsive"></a></div>
                                       <div class="post-text">
                                          <a href="manage-buylead-alert.php" class="pp-title">Subscribe to buy requests alerts</a>
                                          <p>Get updates on relevant products and sell offers directly in your email.
                                       </div>
                                       <div class="clear"></div>
                                    </div>
                                 </div>
                              </li>
                              <div class="clearfix"></div>
                           </ul>
                        </div>
                     </div>
                  </div>
               </div>
               <div class='seniorbox'>
                  <div class="sponsorbox">
                     <div class="siconbox"><img alt=""src="images/right-icon.png"></div>
                     <div class="scontentbox">
                        <h2>Sponsor <span>Supplier</span></h2>
                     </div>
                     <div class="clear"></div>
                  </div>
                  <ul>
                     <li>> <a href="#">Exclusive Access to Buying Requests</a>
                     <li>> <a href="#">Rank of Buyers to Find Your Products</a>
                     <li>> <a href="#">Customized Website</a>
                     <li>> <a href="#">Link to the company website</a>
                     <li>> <a href="#">Premium Sponsor Supplier Sign</a>
                     <li>> <a href="#">Product Posting Service</a>
                     <li>> <a href="#">Email Marketing</a></li>
                     <p style="padding-right:15px"><a href="membership_plans.php">Learn More <span>> ></span></a>
                  </ul>
                  <h3><a href="membership_plans.php">Request All Privileges</a></h3>
               </div>
               <?php
                  $sql_testi = "select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
                  $res_testi = mysqli_query($con, $sql_testi);
                  if (mysqli_num_rows($res_testi) > 0) {
                  
                  ?>
               <div class="testimonialbox">
                  <div class="testimonialbg">
                     <h2>Buyer Speaks</h2>
                     <?php while($row_testi = mysqli_fetch_object($res_testi)){?>
                     <div class="arrow_box">
                        <p><i><span>“</span><?php echo stripslashes($row_testi->testi_details); ?><span class="spacecomma">”</span></i>
                     </div>
                     <div class="clear"></div>
                     <div class="testiwriter">
                        <div class="pic1"><img alt=""src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>"></div>
                        <div class="pic-info">
                           <h5><?php echo $row_testi->testi_name; ?></h5>
                           <p><a href=#><?php echo get_country_name($row_testi->testi_cn_id); ?></a>
                        </div>
                     </div>
                     <?php }?>
                  </div>
               </div>
               <?php } ?><?php
                  $sql_testi = "select * from testimonials WHERE testi_type='supplier' and testi_status='1' order by testi_updated_date desc limit 1";
                  $res_testi = mysqli_query($con, $sql_testi);
                  if (mysqli_num_rows($res_testi) > 0) {
                  $row_testi = mysqli_fetch_object($res_testi);
                  ?>
               <div class="juniorbox">
                  <div class="trianglebox">
                     <div class="boxlefts"><img alt=""src="images/jonior-icon.png"></div>
                     <div class="boxrights">
                        <h2>Junior <span>Supplier</span><br><span>Trust Sign</span></h2>
                     </div>
                     <div class="clear"></div>
                  </div>
                  <div style="width:88%;margin-left:22px">
                     <p><i style="font-weight:700"><?php echo stripslashes($row_testi->testi_details); ?></i><br><br><a href="membership_plans.php" class="fright" style="padding-right:15px;">Learn More <span>> ></span></a>
                  </div>
                  <div class="clear"></div>
                  <h3><a href="membership_plans.php">Request Trust Sign</a></h3>
               </div>
               <?php } ?><?php
                  $sql_testi3 = "select * from testimonials WHERE testi_type='supplier' and testi_status='1' order by rand() desc limit 1";
                  $res_testi3 = mysqli_query($con, $sql_testi3);
                  if (mysqli_num_rows($res_testi3) > 0) {
                  
                  ?>
               <div class="testimonialbox">
                  <div class="testimonialbg">
                     <h2>Supplier Speaks</h2>
                     <?php
                        while($row_testi3 = mysqli_fetch_object($res_testi3))
                        {
                        ?>
                     <div class="arrow_box">
                        <p><i><span>“</span><?php echo stripslashes($row_testi3->testi_details); ?><span class="spacecomma">”</span></i>
                     </div>
                     <div class="clear"></div>
                     <div class="testiwriter">
                        <div class="pic1"><img alt=""src="upload/testimonial_img/<?php echo $row_testi3->testi_image; ?>"></div>
                        <div class="pic-info">
                           <h5><?php echo $row_testi3->testi_name; ?></h5>
                           <p><a href=#><?php echo get_country_name($row_testi3->testi_cn_id); ?></a>
                        </div>
                     </div>
                     <?php }?>
                  </div>
               </div>
               <?php } ?>
            </div>
            <div class="clear"></div>
            <div class="col-lg-12 col-xs-6 mid-tops">
               <?php
                  $banner=GetHomeBanner('left',$strconutnry);
                  if($banner!="")
                  {
                      echo '<div class="middle mid-center skyscrapper" style="padding:0;">';
                  echo $banner;
                    echo '</div>';
                  }
                  else
                  {
                    // echo '<div class="middle mid-center">';
                    //echo ' <h2>Advisement Place</h2>';
                     //echo '</div>';
                  }
                  ?>
               <div class="clear"></div>
            </div>
         </div>
      </div>
      <div class="clear"></div>
   </div>
   </div><?php include "includes/responsive_footer.php";  ?>
   <style>
      .page-header-col2-intro
      {
      border-left:2px solid #237abf;
      }
   </style>