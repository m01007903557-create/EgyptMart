<?php
// عرض الأخطاء للتصحيح
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// منع المهلة الزمنية
set_time_limit(0);

// تخزين مؤقت للصفحة
ob_start();

// تعريف بسيط لتسريع تحميل الصور
function get_product_image_fast($image_name) {
    return 'https://egyptmart.shop/upload/myproduct/' . $image_name;
}

// بدء الجلسة
session_start();

// تضمين الملفات الأساسية
set_time_limit(600);
include 'common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
$globalcntid = 243;

if (isset($_COOKIE['loc_id'])) {
    $cn_id = $_COOKIE['loc_id'];
    $sqlcountry = "select cn_name from country where cn_id='$cn_id'";
    $rscountry = mysqli_query($con, $sqlcountry);
    if (mysqli_num_rows($rscountry) > 0) {
        $rowcountrty = mysqli_fetch_object($rscountry);
        $cn_name = $rowcountrty->cn_name;
    }
} else {
    $cn_id = 0;
    $cn_name = "Global";
}

// استعلام البلد للإعلانات
if ($cn_id != "" && $cn_id > 0) {
    $strconutnry = " AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
} else {
    $strconutnry = " AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
}
?>

<!DOCTYPE html>
<?php
$current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
if ($current_url == 'https://arab-mart.com' || $current_url == 'https://arab-mart.com/index') { ?>
  <meta content="text/html; charset=utf-8" http-equiv=Content-Type>
  <meta content=webkit name=renderer>
  <meta content="width=device-width,initial-scale=1" name=viewport>
  <meta content="<?php echo getSiteTitle(); ?>" name=title>
  <meta content="<?php echo get_page_settings(2); ?>" name=keywords>
  <meta content="<?php echo get_page_settings(3); ?>" name=description>
  <title><?php echo getSiteTitle(); ?></title>
  <link href=css/bootstrap.css rel=stylesheet>
  <script src="js/jquery.min.js"></script>
  <link href=css/style.css?t=<?php echo rand(); ?> rel=stylesheet>
  <link href=css/responsive1.css rel=stylesheet>
  <link href=fonts/font-awesome.css rel=stylesheet>
  <link href=css/im-style-v1.css rel=stylesheet>
  <link href=css/verticle-menu.css rel=stylesheet>
  <link href=css/theme.css rel=stylesheet>
  <script src=js/jquery.accessible-news-slider.js></script>
  <link href=css/type.css rel=stylesheet>
  <script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
  <script src=js/responsiveslides.min.js></script>
<?php
} else { ?>
  <meta content="text/html; charset=utf-8" http-equiv=Content-Type>
  <meta content=webkit name=renderer>
  <meta content="width=device-width,initial-scale=1" name=viewport>
  <meta content="<?php echo getSiteTitle(); ?>" name=title>
  <meta content="<?php echo get_page_settings(2); ?>" name=keywords>
  <meta content="<?php echo get_page_settings(3); ?>" name=description>
  <title><?php echo getSiteTitle(); ?>
  </title>
  <link href=css/bootstrap.css rel=stylesheet>
  <script src="js/jquery.min.js"></script>
  <link href="css/style.css?ver=3.3" rel="stylesheet">
  <link href=css/responsive1.css rel=stylesheet>
  <link href=fonts/font-awesome.css rel=stylesheet>
  <link href=css/im-style-v1.css rel=stylesheet>
  <link href=css/verticle-menu.css rel=stylesheet>
  <link href=css/theme.css rel=stylesheet>
  <script src="js/jquery.accessible-news-slider.js"></script>
  <link href=css/type.css rel=stylesheet>
  <script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
  <script src="js/responsiveslides.min.js"></script>
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
<?php
} ?>
<script type="text/javascript">
  function changeSlickArrowgDeferred() {
    setInterval(function changeHeader() {
      $('button.slick-next.slick-arrow').trigger('click');
      return false;
    }, 3000);
    return false;
  }
  changeSlickArrowgDeferred();
</script>

<style rel="stylesheet" type="text/css">
  li.page_bar_item>span {
    color: black !important;
    cursor: pointer;
  }
</style>
<script>
  $(function() {
    $("#slider").responsiveSlides({
      auto: !0,
      nav: !1,
      speed: 500,
      namespace: "callbacks",
      pager: !0
    })
  });
</script>
<script>
  jQuery(document).ready(function() {
    get_load_leftdata();

    setTimeout(() => {
	    $('#pull').trigger('click');
	    console.log('click pull');
	}, 1000);

    jQuery("#newsslider").accessNews({}), jQuery("#newsslider2").accessNews({
      title: "BREAKING NEWS:",
      subtitle: "stories from the internet",
      speed: "slow",
      slideBy: 5,
      slideShowInterval: 1e5,
      slideShowDelay: 1e5
    })
  });
</script>

<body>
  <?php
  $myvar = isset($_SESSION["popup"]) ? $_SESSION["popup"] : '';
  if ($myvar == 1 || $myvar == "") {
  ?>
    <div class="loader_img">
      <div class="popup-box">
        <div class="backg">
          <a href="#"><img id="popup_close" class="close" src="close.png" /></a>

          <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
            $uid = $_SESSION['uid_indm'];
          ?>

          <?php
          } else { ?>
            <p class="bp13 m12 j1" title="مرحبا بك في - منصة سوق مصر على الإنترنت">Welcome to <?php echo 'arab-mart'; ?><br><span><b class="bo1">New User? &nbsp; </b><a href="">View Benefits!</a></span></p>
          <?php
          } ?>
          <div class="tab-main">
            <div class="wrapper">
              <ul class="top_btn_list">
                <li>
                  <input id="radio1" class="radio_btn" name="tab" type="radio">
                  <label for="radio1" class="label_btn" title="لفرص الشراء – للمشتريين">New Buyer</label>
                  <div id="cont1" class="content_tab" title="مستورد – أو شركة شراء – أو تاجر - أو بيت شراء متخصص">
                    <img class="heading" src="head.png" />
                    <div class="text-box">
                      <div class="text-box-left">
                        <img src="1.png" />
                      </div>
                      <div class="text-box-right">
                        <h2><a href="" target="_blank" title="أرسل لنا - طلبات شراء - وتلقي تسعيرات وأفضل عروض البيع - من شركات بيع مؤهلة ولها وجود حقيقي"><span class="black-color" style="font-weight:700">Post Buy Requirements </span>Get quotes >></a></h2>
                        <p>get quotes from qualified suppliers.</p>
                      </div>
                    </div>
                    <div class="text-box">
                      <div class="text-box-left">
                        <img src="2.png" />
                      </div>
                      <div class="text-box-right">
                        <h2><a href="#" style="cursor:text" title="ابحث واكتشف – ألاف من فرص البيع - لمنتجات وخدمات تجارية لأعمالك وتجارتك"><span class="black-color" style="font-weight:700">Find Any Business Thing</span> Get Results >></a></h2>
                        <p>send inquires and get replies.</p>
                        <form method="GET" name="searchForm2" action="search.php" onsubmit="return validsearch_r();">
                          <input size="24" class="m1 bl6" id="keywords_r" name="keywords" type="text" title="- ابحث واكتشف – ألاف من فرص البيع">
                          <input value="Search" class="m1 fz1 ff1 m5" type="submit" title="- ابحث واكتشف – ألاف من فرص البيع">
                        </form>
                      </div>
                    </div>
                    <div class="text-box">
                      <div class="text-box-left">
                        <img src="3.png" />
                      </div>
                      <div class="text-box-right">
                        <h2><a href="" target="_blank" title="سجل - أهم الأصناف - التي تود شراؤها - لتتلقى إشعارات – عروض بيع خاصة لها – في بريدك وعلى الموبايل"><span class="black-color" style="font-weight:700">Manage Sell Offer Alerts </span> Get Offers >></a></h2>
                        <p>Get Updates and alerts in your inbox </p>
                      </div>
                    </div>
                    <div>
                    </div>
                  </div>
                </li>
                <li>
                  <input id="radio2" class="radio_btn" name="tab" type="radio" checked="checked">
                  <label for="radio2" class="label_btn" title="للموردين - شركات - ومصانع - وخدمات تجارية">New Supplier ?</label>
                  <div id="cont2" class="content_tab" title="للمصنعين – والمصدرين – وتجار الجملة – وتجار التجزئة – ومقدمو الخدمات التجارية">
                    <img class="heading" src="head2.png" />
                    <div class="text-box">
                      <div class="text-box-left">
                        <img src="1.png" />
                      </div>
                      <div class="text-box-right">
                        <h2><a href="" target="_blank" title="انضم لأهم 10,0000 - بائع ومشتري مصري وعربي وعالمي - في أكبر منصة أعمال - للبيع والشراء - بين الشركات - في مصر والمنطقة العربية"><span class="black-color" style="font-weight:700"> Products Export Catalog ..</span>FREE >></a> </h2>
                        <p> +10,000 Registered Businesses!</p>
                      </div>
                    </div>
                    <div class="text-box">
                      <div class="text-box-left">
                        <img src="2.png" />
                      </div>
                      <div class="text-box-right">
                        <h2><a href="" target="_blank" title="أنشئ صفحات - أعمالك التجارية – موقعك المصغر / متجرك – الذي يحتوي على أهم منتجاتك وخدماتك التجارية – في سوق مصر على الانترنت بين أهم تجار ومصانع مصر والمنطقة"><span class="black-color" style="font-weight:700">Lifelong SENIOR Plan, </span> Offer >></a></h2>
                        <p>Much Binifits for One Week Offer.</p>
                      </div>
                    </div>
                    <div class="text-box">
                      <div class="text-box-left">
                        <img src="3.png" />
                      </div>
                      <div class="text-box-right">
                        <h2><a href=" " target="_blank" title="سجل - أهم الأصناف - التي تقوم ببيعها - لتتلقى إشعارات – طلبات شراء جاهزة – في بريدك - وعلى الموبايل"><span class="black-color" style="font-weight:700">Get FREE Buyleads </span> Sign Up >></a></h2>
                        <p>Buyers are Waiting for your Sale Offers.</p>
                      </div>
                    </div>
                    <div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php
  } ?>
  <script type="text/javascript">
    $("#popup_close").click(function() {
      $(".loader_img").fadeOut("slow");
      <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
        $.post("/UpdateTheSession.php", {
          "id": "2"
        });
      <?php
      } ?>
    });
  </script>
  <?php
  if (get_page_settings('25') == 'manual') {
    $sql_order = " order by pc_order,pc_name";
  } else {
    $sql_order = " order by pc_name";
  }
  ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/style123.css?t=<?php echo rand(); ?>" type="text/css" rel="stylesheet" />
  <div class=wrapper>
    <?php include "includes/header.php"; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var header = document.querySelector('header');
    if (header) {
        header.style.marginBottom = '0';
        header.style.paddingBottom = '0';
    }
    
    var content = document.querySelector('.middlesection, .maincontainer, .wrapper > div:not(header)');
    if (content) {
        content.style.marginTop = '0';
        content.style.paddingTop = '0';
    }
});
</script>

    <div class="middlesection">
      <div class="maincontainer">
        <div class="demobox">
          <div id="leftsection">
            <div class="allcate">
              <h3>
                <a href=dir.php#main_cat>
                  <i class="fa fa-list-ul" style=color:#fff></i>
                  <span style="display:inline-table;color:#fff;">MY MARKETS</span>
                </a>
              </h3>
            </div>
            <div id="block_navigation" title="تصنيفات - كل مجالات - التجارة">
              <div id="pull" style="display:none;">
                <a href="#">
                  <img class="menu-img" alt="" src="css/img/my-market.png">
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
            <div class="video_slider mobile-slider hidden-xs" style="width:100%;padding-left:5px;margin:10px 0 0 0px;">
              <div class="slider">
                <ul class="rslides" id="slider">
                  <?php
                  ## get the top compnay video
                  $sqlvideo = "select * from video_slider where adv_status='1' $strconutnry LIMIT 3";
                  $resvideo = mysqli_query($con, $sqlvideo);
                  $totalvideo = mysqli_num_rows($resvideo);
                  if ($totalvideo > 0) {
                    while ($row_video = mysqli_fetch_object($resvideo)) {
                      $cv_video_link = $row_video->adv_link;
                      $adv_redirect = $row_video->adv_redirect;
                      $chklink = explode("://", $cv_video_link);
                      if ($chklink[0] == "http" || $chklink[0] == "https") {
                        preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $cv_video_link, $matches);
                        $id = $matches[1];
                        $width = '100%';
                        $height = '181';
                        $iframe2show = '<iframe class="hidden-xs" width="100%" height="181"
                                                                                     src="https://www.youtube.com/embed/' . $id . '" frameborder="0"
                                                                                     allowfullscreen></iframe>';
                      } else {
                        $iframe2show = $cv_video_link;
                      }
                      $bnsprof_compname = $row_video->adv_title;
                      $bnsprof_address1 = $row_video->adv_description;
                  ?>
                      <li>
                        <?php echo $iframe2show; ?>
                        <div class="iframebox">
                          <h2><i class="fa fa-play"></i><a href="<?php echo $adv_redirect; ?>" target="_blank"><?php echo substr(($bnsprof_compname), 0, 22) . ".." ?></a>
                          </h2>
                          <p><?php echo substr(($bnsprof_address1), 0, 30) . ".." ?>
                        </div>
                      </li>
                      <?php
                    } ?><?php
                      } ?>
                </ul>
              </div>
              <div class="verifiedbox_bottom" title="أنشر أهم - فيديوهات شركاتك - ضمن بروفايل شركتك">
                <h4><a href="company-video.php">Post <span></span>Company Video</a></h4>
              </div>
            </div>
            <div class="col-lg-12 col-xs-12 col-sm-12 list-top mobile-slider" title="لموردين مبيعابث الجملة - إنشئ حقيبة منتجاتك - للعرض على تجارة التجزئة">
              <h1>
                <a href=#>
                  <img alt="" src="images/wholesaler.jpg">
                </a>
              </h1>
              <h5>Set Your</h5>
              <a href="membership_plans.php" target=_>
                <div class="showcase">Products Showcase</div>
              </a>
              <p>Distribute in Your City
            </div>
            <div class="col-lg-12 col-xs-6 col-sm-12 map-tops">
              <div class="map"><a href="#"><a href="#"><img alt="" src="images/map.png"></a></a></div>
            </div>

            <div class="col-lg-12 col-xs-6 col-sm-12 list-top mobile-hide">
              <div class="seniorbox">
                <div class="siniorlistbox">
                  <div class="siconbox"><img alt="" src=images/left-icon.png></div>
                  <div class="scontentbox">
                    <h2>SENIOR <span>Supplier</span></h2>
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
                  <p><a href="membership_plans.php"target="_blank">Learn More <span>> ></span></a>
                </ul>
                <h3><a href="membership_plans.php"target="_blank">Upgrade Now</a></h3>
              </div>
            </div>
            <div class="col-lg-12 col-xs-6 mid-tops">
              <?php $banner = GetHomeBanner('left', $strconutnry); if ($banner != "") { echo '<div class="middle mid-center skyscrapper" style="padding:0;">'; echo $banner; echo '</div>';
              } ?>
              <div class="clear"></div>
            </div>
            <div class="clear"></div>
          </div>
          <div class="col-xs-12" id="midcenter">
            <style>
              #$.Loading Overlay {
                position: absolute;
                left: 44%;
                margin-top: 108px;
                z-index: 1;
                width: 150px;
                height: 150px;
                border: 16px solid #f3f3f3;
                border-radius: 50%;
                border-top: 16px solid #cd1a00;
                width: 120px;
                height: 120px;
                -webkit-animation: spin 2s linear infinite;
                animation: spin 2s linear infinite;
              }

              @-webkit-keyframes spin {
                0% {
                  -webkit-transform: rotate(0deg);
                }
                100% {
                  -webkit-transform: rotate(360deg);
                }
              }

              @keyframes spin {
                0% {
                  transform: rotate(0deg);
                }
                100% {
                  transform: rotate(360deg);
                }
              }

              .animate-bottom {
                position: relative;
                -webkit-animation-name: animatebottom;
                -webkit-animation-duration: 1s;
                animation-name: animatebottom;
                animation-duration: 1s
              }

              @-webkit-keyframes animatebottom {
                from {
                  bottom: -100px;
                  opacity: 0
                }
                to {
                  bottom: 0px;
                  opacity: 1
                }
              }

              @keyframes animatebottom {
                from {
                  bottom: -100px;
                  opacity: 0
                }
                to {
                  bottom: 0;
                  opacity: 1
                }
              }
            </style>
            
            <div id="$.Loading Overlay"></div>
            <div class="slider r_css">
              <div class="yahoo_slider" style="width:100%;opacity:0;" id="myDiv">
                <ul id="newsslider" style="width:100%;">
                  <?php
                  $sqllogo = "select * from yahoo_slider_arabyos where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
                  $rslogo = mysqli_query($con, $sqllogo);
                  if (mysqli_num_rows($rslogo) > 0) {
                    while ($rowlogo = mysqli_fetch_object($rslogo)) {
                      $adv_img = $rowlogo->adv_img;
                      $logopath = "upload/yahoo_slider/" . $adv_img;
                      $adv_link = $rowlogo->adv_link;
                      $adv_title = $rowlogo->adv_title;
                      $adv_description = Show_shortcontent($rowlogo->adv_description, 22);
                      $adv_imagewidth = $rowlogo->adv_imagewidth;
                      $adv_imageheight = $rowlogo->adv_imageheight;
                  ?>
                      <li>
                        <a href="<?php echo $adv_link; ?>" target="_blank" style="width:100%;"><img alt="<?php echo $adv_title; ?>" src="<?php echo $logopath; ?>" style="width:100% !important;"></a>
                        <h3><a href="<?php echo $adv_link; ?>" target="_blank"><?php echo $adv_title; ?></a></h3>
                        <p><a href="<?php echo $adv_link; ?>" style="color:#gray"></a>
                      </li>
                  <?php
                    }
                  } else {
                    echo '';
                  }
                  ?>
                </ul>
              </div>
            </div>
            <div class="col-lg-12 col-xs-6 col-sm-12 list-top desktop-hide">
              <div class="seniorbox">
                <div class="siniorlistbox">
                  <div class="siconbox"><img alt="" src=images/left-icon.png></div>
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
                  <p><a href="membership_plans.php"target="_blank">Learn More <span>> ></span></a>
                </ul>
                <h3><a href="membership_plans.php">Upgrade Now</a></h3>
              </div>
            </div>
            <div class="country-wrapper-with-verfier">
              <div class="countrybox">
                <div class="countrubox_top" title="بائعين ومشترين">
                  <div class="countrubox_heading global-page">
                    <div class="global-icon-search">
                      <div class="globalicon"><b>GLOBAL PAGE
                        </b> <a href="#" onclick="unsetCountryLocation();"><img alt=Global src="images/Untit.png"></a>
                      </div>
                      <h2><span> Suppliers & Buyers </span></h2>
                    </div>
                    <div class="search">
                      <input id="search" name="search" class="textbox" placeholder="Search Country">
                      <input id="submit" name="submit" type="submit" value="Subscribe">
                      <script>
                        $("#submit").click(function() {
                          var c = document.getElementById("search").value,
                            e = "cname=" + c;
                          $.ajax({
                            type: "POST",
                            url: "search_country.php",
                            data: e,
                            cache: !1,
                            success: function(c) {
                              $("#response").html(c)
                            }
                          })
                        })
                      </script>
                      <div id="response"></div>
                    </div>
                  </div>
                  <div class="clear"></div>
                </div>
                <div class="country-verfiy-vander">
                  <div class="country-div-wrapper">
                    <div class="cnt1">
                      <table>
                        <tr>
                          <td><span class="asia"><b>Asia :</b></span>
                          <td>
                            <ul class="country">
                              <li><a href="#" onclick="setCountryLocation(225);"><img alt="" src="images/uae.jpg">
                                  <span style="color:#4163a9;">UAE<span></a>
                              <li><a href="#" onclick="setCountryLocation(187)"><img alt="" src="images/Saudi-Arabia.jpg">
                                  <span style="color:#4163a9">Saudi Arb.<span></a>
                              <li><a href="#" onclick="setCountryLocation(112)"><img alt="" src="images/Kuwait.jpg">
                                  <span style="color:#4163a9">Kuwait<span></a>
                              <li><a href="#" onclick="setCountryLocation(173)"><img alt="" src="images/jordan.jpg">
                                  <span style="color:#4163a9">Jordan<span></a>
                              <li>
                                <a href="#" onclick="setCountryLocation(116)">
                                  <img alt="" </ul>
                      </tr>
                    </div>
                    <div class="cnt1">
                      <table>
                        <tr>
                          <td><span class="africa"><b>Africa:</b></span>
                          <td>
                            <ul class="country">
                              <li><a href="#" onclick="setCountryLocation(63)">
                                  <img alt="" src="images/flag01.png"> <span style="color:#4163a9">Egypt<span></a>
                              <li><a href="#" onclick="setCountryLocation(202)"><img alt="" </ul>
                      </table>
                    </div>
                  </div>
                </div>
              </div>
              <div class=verifiedbox_supplierbox>
                <h3>Verified Suppliers</h3>
                <p>Selected Suppliers from around the world ..
                  <span class="fright" </a></span>
                  <href="membership_plans.php"> </a></span>
                    <div class=clear></div>
                    <ul>
                      <li><a href="membership_plans.php" class="tooltip1"><img alt="" src="images/verified01.jpg" title="مورد - سبونسور - الأعلى - مزايا">
                          <span><i>SPONSOR Supplier</i></span></a>
                      <li><a href="membership_plans.php" class="tooltip1"><img alt="" src="images/verified02.jpg">
                          <span><i>SENIOR Supplier</i></span></a>
                      <li><a href="membership_plans.php" class="tooltip1"><img alt="" src="images/verified03.jpg">
                          <span><i>JUNIOR Supplier</i></span></a>
                    </ul>
              </div>
            </div>
            <div class="space21"></div>
            <div class="countrubox_top2" title="شاهد - المنتجات - والموردين">
              <div class="countrubox_heading">
                <h2>View <a href="dir.php#main_cat" target="_blank"><span>Products & Suppliers</span></a></h2>
              </div>
              <div class="list-rights" title="أعرض - منتجاتك وخدماتك - التجارية">
                <h2>
                <?php if(isset($usr_mp_id) && ($usr_mp_id == 3 || $usr_mp_id == 0)){ ?>
                  <a href="membership_plans.php"><span>+ Display</span> Your Products</a>
                <?php }else{ ?>
                  <a href="product-sel-cat.php"><span>Display</span> Your Products</a>
                <?php } ?>
                </h2>
              
              
 
              
   </div>
              <div class="clear"></div>
            </div>
            <div class="demobox">
              <div class="wrapper-container">
                <div class="white_bg hello">
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
                                                                           
                          (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country in (select cn_id from country where cn_code='" . (isset($location_geo_country) ? $location_geo_country : 243) . "')))
                                                            )";
                        $sql_so_ck = " and (
                                                            (so_preferred_buyer_location='any')
                                                            or
                                                            (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . (isset($location_geo_country) ? $location_geo_country : 243) . "')))
                                                            )";
                        $sql_br_ck = " and (
                                                            (br_preferred_supplier_location='any')
                                                            or
                                                            (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . (isset($location_geo_country) ? $location_geo_country : 243) . "')))
                                                            )";
                      }
                     if (isset($_COOKIE['loc_id']) && $_COOKIE['loc_id'] != "") {
                        $sql_prd = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24";
                      } else {
                        $sql_prd = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24";
                      }
                      //echo $sql_prd;

                      $res_prd = mysqli_query($con, $sql_prd);
                      echo mysqli_error($con);
                      $total_rows = mysqli_num_rows($res_prd);

                      $re_rows = $total_rows % 1;
                      if ($total_rows > 0) {
                        $indx1 = 1;
                      ?><?php
                          $useragent = $_SERVER['HTTP_USER_AGENT']; ?>
                      <ul id="products-suppliers">
                        <?php
                        while ($row_prd = mysqli_fetch_object($res_prd)) {
                          if ($indx1 > 30) {
                            break;
                          }
                          if ($indx1 % 3 == 1) {
                        ?>
                            <li class="main-slick-wrapper-item">
                            <?php
                          }
                          $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
                          $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_bprof->bnsprof_id;
                          //echo $row_bprof->bnsprof_id;
                          //echo $sql_icon;
                          $get_icon = mysqli_query($con, $sql_icon) or die(mysqli_error($con));
                            <div class="inner-main-wrapper">
                              <a class="slick-product-wrapper" href="/company/products.php?c=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $row_prd->pd_subcat_id; ?>#<?php echo $row_prd->pd_id; ?>" target="_blank" style="text-decoration:none;color:#000">
                                <div class="slick-product-image">

                                  <img alt="..." src="upload/myproduct/thumb/<?php echo $row_prd->pd_image; ?>">

                                  <?php
                                  if (count($row_prd->pd_imagelogo) && $row_prd->pd_imagelogo != '' && !empty($row_prd->pd_imagelogo)) {
                                    $logo = explode(',', $row_prd->pd_imagelogo); ?>

                                    <?php if (file_exists('upload/myproduct/' . $logo[0])) : ?>
                                      <img alt="<?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?>" src="upload/myproduct/<?php echo $logo[0]; ?>" style="position: absolute;bottom: 59px; left: 6px; width: 60px; height: 60px;" class="img-responsive" title="<?php echo ucwords($row_prd->pd_title); ?>">
                                    <?php endif; ?>
                                  <?php
                                  } ?>
                                </div>
                                <!--position: relative;bottom: 65px;left: -42px;-->
                                <div class="matterbox">
                                  <div class="icon-pic-with-heading">
                                    <div class="icon_pic"><?php if (mysqli_num_rows($get_icon) > 0) {
                                                            $title = 'Junior';
                                                            $icon = mysqli_fetch_array($get_icon);
                                                            if (strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
                                                              $title = 'Senior';
                                                            } else if (strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
                                                              $title = 'Sponsor';
                                                            }
                                                          ?><img alt="" src="admin/images/<?php echo $icon['mst_icon']; ?>" class="img-responsive" title="<?php echo strtoupper($title); ?>" style="width:18px;height:15px"><?php
                                                                                                                                                                                                    } else { ?>
                                        <img alt="" src="images/slider-icon01.jpg" class="img-responsive" title="JUNIOR">
                                        <?php
                                                                                                                                                                                                    } ?>
                                    </div>
                                    <div class="ihover-wrapper">
                                      <h3 class="ihoves">
                                        <?php if (strlen($row_prd->pd_title) > 20) {
                                          echo substr($row_prd->pd_title, 0, 20) . '...';
                                        } else {
                                          echo $row_prd->pd_title;
                                        } ?>
                                      </h3>
                                      <div class="auction_hover">
                                        <p><?php echo ucwords($row_prd->pd_title); ?></p>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="rightmatter">
                                    <p>
                                      <span class="nam"><?php echo get_country_name($row_prd->country); ?></span><br>
                                    <p>MOQ: <span class="nam"><?php echo $row_prd->pd_min_order_qty; ?><?php echo $row_prd->mu_name; ?></span><br>
                                    <p><?php echo $row_prd->cn_currency; ?><span style="font-size:11px!important" class="nam"><?php echo $row_prd->pd_fob_price ?>
                                        /</span><?php echo $row_prd->mu_name; ?>
                                    <div class="clear"></div>
                                  </div>
                                  <div class="clear"></div>
                                </div>
                              </a>
                            </div>
                          <?php if ($indx1 % 3 == 0) {
                            echo '</li>';
                          }
                          $indx1++;
                        }
                        if ($re_rows == 1) {
                          echo '</li>';
                        } ?>
                      </ul>
                      <?php
                      } else {
                      ?><?php
                        }
                          ?>

                    </div>
                  </div>
                </div>
                <div class="learnmores">
                  <p><a href="dir.php#main_cat" "target=_blank">View All Categories <span>>></span></a>
                </div>
              </div>
            </div>
            <div class="demobox oyee">
              <div class="countrubox_top">
                <div class="countrubox_heading" title="عروض بيع - خاصة - محدودة">
                  <h2>Hot <a href="sale-offers.php"target="_blank"><span>Sale Offers Ads</span></a></h2>
                </div>
                <div class="list-rights" title="أنشر - عروض بيع - خاصة - محدودة">
                  <h2><a href="post-sell-offer.php"><span>+  Post</span> Sale Offers Ads</a></h2>
                </div>
                <div class="clear"></div>
              </div>
              <div class="blank_bg">
                <div class="welcome_desc">
                  <div class="course_demo">
                    <?php
                    $sql_so = "select * from sale_offer,user,business_profile where so_usr_id=usr_id and usr_id=bnsprof_uid and so_approval_status='1' and so_status='1' and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)>=now() " . $sql_so_ck . " order by rand()";
                    $res_so = mysqli_query($con, $sql_so);
                    //echo $res_so->num_rows;
                    $indx1 = 1;
                    if ($res_so->num_rows > 0) {
                      $re_rows = $total_rows % 1;
                    ?>
                      <ul id="temporary-slides">
                        <?php
                        while ($row_so = mysqli_fetch_object($res_so)) {

                        ?>
                          <?php if ($indx1 % 2 == 1) { ?>
                            <li class="main-slick-wrapper-item">
                            <?php } ?>
                            <?php
                            $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
                            $sql_icon = "select sip.mst_icon ,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_so->bnsprof_id;
                            $get_icon = mysqli_query($con, $sql_icon) or die(mysqli_error($con));
                            ?>
                            <a class="slick-product-wrapper" href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5($row_so->so_id); ?>" target="_blank" style="text-decoration:none;color:#000">
                              <div class="slick-product-image">
                                <img class="black" alt="<?php echo ucwords(substr($row_so->so_service, 0, 25)); ?>" src="upload/sale_offer/thumb/<?php echo $row_so->so_pic; ?>" class="img-responsive black" title="<?php echo ucwords($row_so->so_service); ?>">
                              </div>
                              <div class="matterbox">
                                <div class="icon-pic-with-heading">
                                  <div class="icon_pic"><?php if (mysqli_num_rows($get_icon) > 0) {
                                                          $title = 'Junior';
                                                          $icon = mysqli_fetch_array($get_icon);
                                                          if (strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
                                                            $title = 'Senior';
                                                          } else if (strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
                                                            $title = 'Sponsor';
                                                          }
                                                          $title = 'Junior';
                                                          $icon = mysqli_fetch_array($get_icon);
                                                        ?><img alt="" src="admin/images/<?php echo ($icon['mst_icon'] != '') ? $icon['mst_icon'] : 'slider-icon01.jpg'; ?>" class="img-responsive" title="<?php echo strtoupper($title); ?>" style="width:18px;height:15px"><?php
                                                                                                                                                                                                                                                  } else { ?>
                                      <img alt="" src="images/slider-icon01.jpg" class="img-responsive" title="JUNIOR"><?php
                                                                                                                                                                                                                                                  } ?>
                                  </div>
                                  <div class="ihover-wrapper">
                                    <h3 class="ihoves"><?php echo ucwords(substr($row_so->so_service, 0, 20)); ?><?php if (strlen($row_so->so_service) > 21) { ?>...<?php
                                                                                                                  } ?></h3>
                                    <div class="auction_hover">
                                      <p><?php echo ucwords($row_so->so_service); ?></p>
                                    </div>
                                  </div>
                                </div>
                                <div class="rightmatter">
                                  <p class="color-red"><?php
                                                        //  $sql_cat="select * from product_category_arabyos where pc_id=(select pc_parent_id from product_category_arabyos where pc_id='".$row_so->so_pc_id."')";
                                                        $sql_cat = "select * from product_category_arabyos where pc_id=(select pc_parent_id from product_category_arabyos where pc_id=(select pc_parent_id from product_category_arabyos where pc_id='" . $row_so->so_pc_id . "'))";
                                                        $res_cat = mysqli_query($con, $sql_cat);
                                                        $row_cat = mysqli_fetch_object($res_cat);
                                                        echo "(" . $row_cat->pc_name . ")"; ?>
                                  </p>
                                  <div class="clear"></div>
                                </div>
                                <div class="clear"></div>
                              </div>
                            </a>
                            </li>
                          <?php
                        } ?>
                      </ul>
                      <?php
                    } else {
                      ?><?php
                      }
                        ?>

                  </div>
                  <div class="learnmores">
                    <p><a href="sale-offers.php" target="_blank">View all sale Offers
                        <span>>></span></a>
                  </div>
                </div>
              </div>
            </div>
            <div class="center-top">
              <?php
              $banner = GetHomeBanner('middle');
              if ($banner != "") {
                echo '<div class="middle" style="padding:0; width:100%;">';
                echo $banner;
              } else {
                echo '<div class="middle mid-content">';
                echo ' <h3>Banner Place</h3>';
              }
              ?>
              <div class="clear"></div>
              <?php
              PrintLeadingProduct();
              PrintHPProduct();
              ?>
              <?php
              $sqllogo = "select * from supplier_logo where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
              $rslogo = mysqli_query($con, $sqllogo);
              if (mysqli_num_rows($rslogo) > 0) {
              ?>
                <div class="demobox">
                  <div class="countrubox_top4">
                    <div class="countrubox_heading">
                      <div class="countryheadingboxleft" title="أهم الشركات الرائدة في مصر والمنطقة - التي تعمل بخطة - برومو أعمال">
                        <h3><a href="dir.php"><span class="black-color">PROMO Plan For</span> Leader Companies</a></h3>
                      </div>
                    </div>
                    <div class="list-rights" title="أضف شركتك إلى - خدمة برومو أعمال - وتعرف على الشروط">
                      <h2><a href="membership_plans.php"target="_blank"><span class="black-color">+ Add</span> Leader Company</a></h2>
                    </div>
                    <div class="clear"></div>
                  </div>
                  <div class="white_bg hello1">
                    <div class="welcome_desc">
                      <div class="course_demo">
                        <div id="sponsors">
                          <?php
                          while ($rowlogo = mysqli_fetch_object($rslogo)) {
                            $adv_img = $rowlogo->adv_img;
                            $logopath = "upload/supplier_logo/" . $adv_img;
                            $adv_link = $rowlogo->adv_link;
                          ?>
                            <div><a href="<?php echo $adv_link; ?>" target=_blank><img alt="" src="<?php echo $logopath; ?>" class="img-responsive"></a></div>
                          <?php
                          }
                          ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="mid-top">
                    <?php
                    $banner = GetHomeBanner('bottom', $strconutnry);
                    if ($banner != "") {
                      echo '<div class="middle" style="padding:0;">';
                      echo $banner;
                      echo '</div>';
                    } else {
                      //echo '<div class="middle mid-content">';
                      //echo ' <h3>Banner Place</h3>';
                      //echo '</div>';

                    }
                    ?>
                    <div class="clear"></div>
                  </div>
                </div>
              <?php
              } ?>
            </div>
          </div>
        </div>
        <div id="rightsection">
          <div class="bcg-sap_tabs">
            <?php
            $sql_br1 = "select * from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand()";
            $res_br1 = mysqli_query($con, $sql_br1);
            $cnt_br1 = $res_br1->num_rows;
            $sql_br = "select * from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand() limit 0, 5";
            $res_br = mysqli_query($con, $sql_br);
            $cnt_br = $res_br->num_rows;
            if ($cnt_br > 0) {
            ?>
              <div class="leftleads">
                <h2><a href="buyleads.php" target="_blank" title="طلبات شراء - من مشتريين - حقيقيين" style="font-weight:700"> Live Buy Leads <i class="fa fa-caret-right"></i></a></h2>
              </div>
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
                  $(function() {
                    var val = $('.number-count').html().split('');
                    // console.log(val);
                    str = "";
                    for (i = 0; i < val.length; i++) {
                      str += '<div>' + val[i] + '</div>';
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
                      <h3><a href="buyleads-details.php?id=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_br->br_id); ?>" target="_blank"><?php echo ucwords(stripslashes($row_br->br_pd_name)); ?></a></h3>
                      <div class="tendersbox">
                        <div class="verifiedbox">
                          <div class="cover"><img alt="" src="images/tick.png"> Verified & Updated</div>
                          <div class="date"><?php if ($row_br->br_estimate_qty != '0' && $row_br->br_estimate_qty != '') { ?><b>Quantity :</b><?php echo $row_br->br_estimate_qty; ?><?php echo measurement_unit($row_br->br_estimate_qty_unit); ?>(s).<?php
                                                                                                                                                  } ?></div>
                        </div>
                        <div class="flagbox">
                          <?php
                          $contyname = get_country_name($row_br->country);
                          $cntryflag = get_country_flag($row_br->country);
                          if ($cntryflag != "") {
                            $flag2show = '<img src="images/country_flag/' . $cntryflag . '" alt="">';
                          }
                          ?>
                          <ul>
                            <li style="height:auto"><a href="#"><?php echo $contyname . " " . $flag2show; ?></a>
                          </ul>
                          <div class="date"><span><?php
                                                  if ($row_br->br_preferred_supplier_location == '') {
                                                    echo get_country_name($row_br->country);
                                                  } else {
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
                  <?php
                  } ?>
                  <div class="learnmore">
                    <p><a href="buyleads.php" target="_blank">All Live Buy Requests<span>>></span></a>
                  </div>
                </div>
                <div class="clear"></div>           
              </div>
               <br>
            <?php
            } ?>
            <?php
            if ($cn_id != "") {
              $strtender = " AND user.country='$cn_id'";
            } else {
              $strtender = "";
            }
            $sqltender = "select tnd_id,tnd_heading,tnd_due_date,tnd_preferred_location,country from tender,user where tnd_status='1' and tnd_approval_status = 1 and tender.tnd_usr_id=user.usr_id $strtender AND tender.tnd_due_date >= curdate() order by tnd_publish_date DESC LIMIT 4";
            //echo $sqltender;
            $rstender = mysqli_query($con, $sqltender) or die("Error" . mysqli_error($con));
            if ($cn_id != "") {
              $strauction = " AND user.country='$cn_id'";
            } else {
              $strauction = "";
            }
            $sqltender = "select auc_id,auc_heading,auc_due_date,auc_preferred_location,country from auction,user where auc_status='1' and auction.auc_usr_id=user.usr_id $strauction  AND auction.auc_due_date >= curdate() and auc_approval_status =1 order by auc_publish_date DESC LIMIT 5";
            $rsauction = mysqli_query($con, $sqltender) or die("Error" . mysqli_error($con));
            ?>
            <?php if (mysqli_num_rows($rstender) || mysqli_num_rows($rsauction)) { ?>
              <div class="sap_tabs second-sap-tabs">
                <div id="horizontalTab" style="display:block;width:100%;margin:0;">
                  <ul class="resp-tabs-list">
                    <?php if (mysqli_num_rows($rstender)) { ?>
                      <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><a href="tenders.php"target="_blank"><span><b>Tenders</b></span></a><?php
                                                                                                                                              } ?>
                      <?php if (mysqli_num_rows($rsauction)) { ?>
                      <li class="resp-tab-item aria-controls=tab_item-1" role="tab"><a href="post-auction.php"><span><b>Auctions</b></span></a></li>
                    <?php
                      } ?>
                    <div class="clear"></div>
                  </ul>
                  <div class="resp-tabs-container">
                    <div class="resp-tab-content tab-1" aria-labelledby="tab_item-0">
                      <?php
                      if (mysqli_num_rows($rstender)) {
                        echo '<ul class="tab_img">';
                        while ($rowtender = mysqli_fetch_object($rstender)) {
                          $tnd_heading = $rowtender->tnd_heading;
                          $tnd_due_date = $rowtender->tnd_due_date;
                      ?>
                          <li>
                            <div class="popular-post-grids">
                              <div class="popular-post-grid">
                                <h3><a href="tender-details.php?id=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($rowtender->tnd_id); ?>" target="_blank"><?php echo $tnd_heading; ?></a></h3>
                                <div class="tendersbox">
                                  <div class="verifiedbox">
                                    <div class="cover"><img alt="" src="images/tick.png"> Verified & Updated</div>
                                    <div class="date"><b>Due Date: </b><?php echo $tnd_due_date; ?></div>
                                  </div>
                                  <div class="flagbox">
                                    <?php
                                    $contyname = get_country_name($rowtender->country);
                                    $cntryflag = get_country_flag($rowtender->country);
                                    if ($cntryflag != "") {
                                      $flag2show = '<img src="images/country_flag/' . $cntryflag . '" alt="">';
                                    }
                                    ?>
                                    <ul>
                                      <li><a href="#"><?php echo $contyname . " " . $flag2show; ?></a>
                                    </ul>
                                    <div class="date"><span><?php
                                                            if ($rowtender->tnd_preferred_location == '') {
                                                              echo get_country_name($rowtender->country);
                                                            } else {
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
                        <?php
                        } ?>
                      <?php
                      } ?>
                      <div class="clear"></div>
                      <?php if (mysqli_num_rows($rstender) || mysqli_num_rows($rsauction)) { ?>
                        <div class="learnmore">
                          <p><a href="#">View all <?php if (mysqli_num_rows($rstender)) { ?><a href="tenders.php"target="_blank">Tenders</a> / <?php
                                                            } ?><?php if (mysqli_num_rows($rsauction)) { ?><a href="auctions.php"target="_blank">Auctions</a></a><?php
                                                                } ?>
                        </div>
                        <div class="tabbotton"><a href="#"><span>+ Publish</span> <a href="post-tender.php"target="_blank">Tender</a>/</a> <a href="post-auction.php"target="_blank">Auction</a> <span>FREE</span></div>
                      <?php
                      } ?>
                    </div>
                    <div class="resp-tab-content tab-1" aria-labelledby="tab_item-1">
                      <ul class="tab_img">
                        <?php
                        if (mysqli_num_rows($rsauction)) {
                          echo '<ul class="tab_img">';
                          while ($rowaution = mysqli_fetch_object($rsauction)) {
                            $tnd_heading = $rowaution->auc_heading;
                            $auc_due_date = $rowaution->auc_due_date;
                        ?>
                            <li>
                              <div class="popular-post-grids">
                                <div class="popular-post-grid">
                                  <h3><a href="auction-details.php?id=<?php echo rand(1000, 9999) . md5($rowaution->auc_id); ?>" target="_blank"><?php echo $tnd_heading; ?></a></h3>
                                  <div class="tendersbox">
                                    <div class="verifiedbox">
                                      <div class="cover"><img alt="" src="images/tick.png"> Verified & Updated</div>
                                      <div class="date"><b>Due Date: </b><?php echo $auc_due_date; ?></div>
                                    </div>
                                    <div class="flagbox">
                                      <?php
                                      $contyname = get_country_name($rowaution->country);
                                      $cntryflag = get_country_flag($rowaution->country);
                                      if ($cntryflag != "") {
                                        $flag2show = '<img src="images/country_flag/' . $cntryflag . '" alt="">';
                                      }
                                      ?>
                                      <ul>
                                        <li><a href="#"><?php echo $contyname . " " . $flag2show; ?></a>
                                      </ul>
                                      <div class="date"><span><?php
                                                              if ($rowaution->auc_preferred_location == '') {
                                                                echo get_country_name($rowaution->country);
                                                              } else {
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
                          <?php
                          } ?>
                      </ul>
                    <?php
                        } ?>
                    <div class="clearfix"></div>
                    </div>
                  </div>
                </div>
              </div>
            <?php
            } ?>
          </div>
          <div class="sap_tabs second-sap_tabs">
            <div id="horizontalTab1" style="display:block;width:100%;margin:0">
              <ul class="resp-tabs-list">
                <li class="resp-tab-item" aria-controls="tab_item-0" role="tab" title="للشراء">
                  <span>
                    <h5>For Buying</h5>
                  </span>
                <li class="resp-tab-item" aria-controls="tab_item-1" role="tab" title="للبيع">
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
                          <div class="post-img"><a href="#"><img alt="" src="images/email-icon.jpg" class="img-responsive"></a>
                          </div>
                          <div class="post-text">
                            <a href="post-buy-req.php" class="pp-title">+ Post your Buy
                              Requirement</a>
                            <p>Receive responses and quotes from pre-verified
                              and qualified suppliers.
                          </div>
                          <div class="clear"></div>
                        </div>
                      </div>
                    <li>
                      <div class="popular-post-grids">
                        <div class="popular-post-grid">
                          <div class="post-img"><a href="#"><img alt="" src="images/search.jpg" class="img-responsive"></a>
                          </div>
                          <div class="post-text">
                            <a href="search_adv.php" class="pp-title">Find Anything For Your
                              Business Instantly.</a>
                            <p>Send inquiries directly to the suppliers of your choice.
                          </div>
                          <div class="clear"></div>
                        </div>
                      </div>
                    <li>
                      <div class="popular-post-grids">
                        <div class="popular-post-grid">
                          <div class="post-img"><a href="#"><img alt="" src="images/bell.jpg" class="img-responsive"></a>
                          </div>
                          <div class="post-text">
                            <a href="manage-selloffer-alert.php" class="pp-title">Subscribe
                              to sell offers Alerts</a>
                            <p>Get updates on relevant products and sell offers directly in
                              your mailbox.
                          </div>
                          <div class="clear"></div>
                        </div>
                      </div>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php include "includes/footer.php"; ?>
</body>
</html>                       
                 
                          
                          
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
                          <div class="post-img"><a href="#"><img alt="" src="images/boxrect.png" class="img-responsive"></a>
                          </div>
                          <div class="post-text">
                            <a href="product-sel-cat.php" class="pp-title">Display your
                              products/ services</a>
                            <p>Receive responses from domestic & global buyers.
                          </div>
                          <div class="clear"></div>
                        </div>
                      </div>
                    <li>
                      <div class="popular-post-grids">
                        <div class="popular-post-grid">
                          <div class="post-img"><a href="#"><img alt="" src="images/criclearrow.png" class="img-responsive"></a>
                          </div>
                          <div class="post-text">
                            <a href="product-sel-cat.php" class="pp-title">Create company
                              website</a>
                            <p>Promote your company in a huge online presence for home and
                              global sales.
                          </div>
                          <div class="clear"></div>
                        </div>
                      </div>
                    <li>
                      <div class="popular-post-grids">
                        <div class="popular-post-grid">
                          <div class="post-img"><a href="#"><img alt="" src="images/bell.jpg" class="img-responsive"></a>
                          </div>
                          <div class="post-text">
                            <a href="manage-buylead-alert.php" class="pp-title">Subscribe to
                              buy requests alerts</a>
                            <p>Get updates of relevant products and sell offers directly in
                              your mailbox.
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
          <div class="seniorbox-box hide-in-mobile">
            <div class='seniorbox'>
              <div class="sponsorbox">
                <div class="siconbox"><img alt="" src="images/right-icon.png"></div>
                <div class="scontentbox" title=" ">
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
                <p style="padding-right:15px"><a href="membership_plans.php"target="_blank">Learn More <span>> ></span></a>
              </ul>
              <h3><a href="membership_plans.php"target="_blank">Request All Privileges</a></h3>
            </div>
            <?php
            $sql_testi = "select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
            $res_testi = mysqli_query($con, $sql_testi);
            if (mysqli_num_rows($res_testi) > 0) {
            ?>
              <div class="testimonialbox desktop-hide">
                <div class="testimonialbg">
                  <h2>Buyer Speaks</h2>
                  <?php while ($row_testi = mysqli_fetch_object($res_testi)) { ?>
                    <div class="arrow_box">
                      <p><i><span>“</span><?php echo stripslashes($row_testi->testi_details); ?>
                          <span class="spacecomma">&#65533;?</span></i>
                    </div>
                    <div class="clear"></div>
                    <div class="testiwriter">
                      <div class="pic1"><img alt="" src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>">
                      </div>
                      <div class="pic-info">
                        <h5><?php echo $row_testi->testi_name; ?></h5>
                        <p>
                          <a href=#><?php echo get_country_name($row_testi->testi_cn_id); ?></a>
                      </div>
                    </div>
                  <?php
                  } ?>
                </div>
              </div>
              <?php
            } ?><?php
                $sql_testi = "select * from testimonials WHERE testi_type='supplier' and testi_status='1' order by testi_updated_date desc limit 1";
                $res_testi = mysqli_query($con, $sql_testi);
                if (mysqli_num_rows($res_testi) > 0) {
                  $row_testi = mysqli_fetch_object($res_testi);
                ?>
              <div class="juniorbox">
                <div class="trianglebox">
                  <div class="boxlefts"><img alt="" src="images/jonior-icon.png"></div>
                  <div class="boxrights">
                    <h2><span>Junior Supplier</span> <br><span>Trust Sign</span></h2>
                  </div>
                  <div class="clear"></div>
                </div>
                <div style="width:88%;margin-left:22px">
                  <p><i style="font-weight:700"><?php echo stripslashes($row_testi->testi_details); ?></i><br><br><a href="membership_plans.php" class="fright" style="padding-right:15px;">Learn
                      More <span>> ></span></a>
                </div>
                <div class="clear"></div>
                <h3><a href="membership_plans.php">Request Trust Sign</a></h3>
              </div>
          </div>
          <div class="testimonail-wrapper">
            <div class="testimonial-1">
              <?php
                } ?><?php
                  $sql_testi3 = "select * from testimonials WHERE testi_type='supplier' and testi_status='1' order by rand() desc limit 1";
                  $res_testi3 = mysqli_query($con, $sql_testi3);
                  if (mysqli_num_rows($res_testi3) > 0) {
                  ?>
              <div class="testimonialbox">
                <div class="testimonialbg">
                  <h2>Supplier Speaks</h2>
                  <?php
                    while ($row_testi3 = mysqli_fetch_object($res_testi3)) {
                  ?>
                    <div class="arrow_box">
                      <p>
                        <i><span>“</span><?php echo stripslashes($row_testi3->testi_details); ?>
                          <span class="spacecomma">&#65533;?</span></i>
                    </div>
                    <div class="clear"></div>
                    <div class="testiwriter">
                      <div class="pic1"><img alt="" src="upload/testimonial_img/<?php echo $row_testi3->testi_image; ?>">
                      </div>
                      <div class="pic-info">
                        <h5><?php echo $row_testi3->testi_name; ?></h5>
                        <p>
                          <a href=#><?php echo get_country_name($row_testi3->testi_cn_id); ?></a>
                      </div>
                    </div>
                  <?php
                    } ?>
                </div>
              </div>
            <?php
                  } ?>
            </div>
            <div class="testimonial-2">
              <?php
              $sql_testi = "select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
              $res_testi = mysqli_query($con, $sql_testi);
              if (mysqli_num_rows($res_testi) > 0) {
              ?>
                <div class="testimonialbox">
                  <div class="testimonialbg">
                    <h2>Buyer Speaks</h2>
                    <?php while ($row_testi = mysqli_fetch_object($res_testi)) { ?>
                      <div class="arrow_box">
                        <p>
                          <i><span>“</span><?php echo stripslashes($row_testi->testi_details); ?>
                            <span class="spacecomma">&#65533;?</span></i>
                      </div>
                      <div class="clear"></div>
                      <div class="testiwriter">
                        <div class="pic1"><img alt="" src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>">
                        </div>
                        <div class="pic-info">
                          <h5><?php echo $row_testi->testi_name; ?></h5>
                          <p>
                            <a href=#><?php echo get_country_name($row_testi->testi_cn_id); ?></a>
                        </div>
                      </div>
                    <?php
                    } ?>
                  </div>
                </div>
              <?php
              } ?>
            </div>
          </div>
        </div>
        <div class="clear"></div>
      </div>
    </div>
    <div class="clear"></div>
    
    <script>
// تقليل المسافة بعد تحميل الصفحة
window.addEventListener('load', function() {
    var content = document.querySelector('.middlesection');
    if (content) {
        content.style.marginTop = '-50px'; /* زد الرقم لتقليل المسافة أكثر */
    }
});
</script>
    
  </div>
  </div><?php include "includes/footer.php"; ?>
  <style>
    .page-header-col2-intro {
      border-left: 2px solid #237abf;
    }
  </style>
  <script>
    function get_load_leftdata(page = 0) {
      $.ajax({
        url: "ajax_get_leftmenu_again.php",
        datatype: "html",
        async: true,
        type: "POST",
        data: {
          page: page
        },
        beforeSend: function() {
          $("#left_ajax_geting").html("<img class=\"loading_m2\" src=\"images/horizontal_loading.gif\">&nbsp;Loading...&nbsp;");
        },
        success: function(resp) {
          $("#left_ajax_geting").html(resp);
        }
      });
    };
  </script>
  <script>
    $(document).ready(function() {
        
        $('#business-services,#egyptmart-leading,#sponsors,#temporary-slides,#products-suppliers').slick({
      //$('#business-services,#Arab-MART-leading,#sponsors,#temporary-slides,#products-suppliers').slick({
        centerMode: true,
        centerPadding: '30px',
        slidesToShow: 5,
        autoplay: false,
        autoplaySpeed: 3000,
        responsive: [{
            breakpoint: 1024,
            settings: {
              centerMode: true,
              centerPadding: '40px',
              slidesToShow: 3
            }
          },
          {
            breakpoint: 768,
            settings: {
              centerMode: true,
              centerPadding: '40px',
              slidesToShow: 2
            }
          },
          {
            breakpoint: 480,
            settings: {
              centerMode: true,
              centerPadding: '20px',
              slidesToShow: 2
            }
          }
        ]
      });
    });
  </script>
  <?php
  
  
  
//function PrintHPProduct()
{
    global $con, $strconutnry, $usr_mp_id;
    
    // استعلام واحد فقط لجلب خدمات الأعمال من prodservice_slider
    $sqlleading = "select * from prodservice_slider, country 
                   where slider_supplier_country=cn_id 
                   and adv_status='1' and adv_type='2' $strconutnry 
                   order by rand()";
    
    $rsleading = mysqli_query($con, $sqlleading);
    $totalbaneer = mysqli_num_rows($rsleading);
    $rembaner = $totalbaneer % 2;
    
    if ($totalbaneer > 0) { 
        ?>
        <div class="demobox">
            <div class="countrubox_top3">
                <div class="countrubox_heading">
                    <div class="mainflagbox">
                        <div class="membershipicon2"><a href="#"><img alt="" src="images/membership_icon03.png"></a></div>
                    </div>
                    <div class="countryheadingboxleft">
                        <h3><a href="sign-in.php" target="_blank"><span class="black-color">Loyal</span> Business Services</a></h3>
                    </div>
                </div>
                <div class="list-rights">
                    <h2>
                        <?php if(isset($usr_mp_id) && ($usr_mp_id == 3 || $usr_mp_id == 0)){ ?>
                            <a href="membership_plans.php"><span>+ Post </span> Business Services</a>
                        <?php }else{ ?>
                            <a href="product-sel-cat.php"><span>+ Post </span> Business Services</a>
                        <?php } ?>
                    </h2>
                </div>
                <div class="clear"></div>
            </div>
            <div class="col-md-12">
                <div class="bottom_bg">
                    <div class="welcome_desc">
                        <div class="course_demo">
                            <div id="business-services">
                                <?php
                                $indx = 1;
                                while ($rowleading = mysqli_fetch_object($rsleading)) {
                                    if ($indx % 2 == 1) {
                                        echo '<li>';
                                    }
                                ?>
                                    <div class="slick-wrapper">
                                        <a class="slick-product-wrapper" href="<?php echo $rowleading->adv_link; ?>" target=_blank>
                                            <div class="demobox">
                                                <div class="slick-product-image">
                                                    <img alt="" src="https://egyptmart.shop/upload/service_slider/<?php echo $rowleading->adv_img; ?>" class="black" style="max-width:115px" title="<?php echo $rowleading->adv_title; ?>">
                                                </div>
                                                <div class="matterbox">
                                                    <div class="icon-pic-with-heading">
                                                        <div class="ihover-wrapper">
                                                            <h3 class="ihoves">
                                                                <?php echo substr($rowleading->adv_title, 0, 20); ?><?php if(strlen($rowleading->adv_title) > 20) echo '...'; ?>
                                                            </h3>
                                                            <div class="auction_hover">
                                                                <p><?php echo $rowleading->adv_title; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="rightmatter">
                                                        <p><span class="nam"><?php echo $rowleading->cn_name; ?></span></p>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php
                                    if ($indx % 2 == 0) {
                                        echo '</li>';
                                    }
                                    $indx++;
                                }
                                if ($rembaner == 1) {
                                    echo '</li>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } // نهاية if ($totalbaneer > 0)
} // نهاية الدالة

                    </div>
                    <div class="countryheadingboxleft">
                        <h3><a href="sign-in.php" target="_blank"><span class="black-color">Loyal</span> Business Services</a></h3>
                    </div>
                </div>
                <div class="list-rights">
                    <h2>
                        <?php if(isset($usr_mp_id) && ($usr_mp_id == 3 || $usr_mp_id == 0)){ ?>
                            <a href="membership_plans.php"><span>+ Post </span> Business Services</a>
                        <?php }else{ ?>
                            <a href="product-sel-cat.php"><span>+ Post </span> Business Services</a>
                        <?php } ?>
                    </h2>
                </div>
                <div class="clear"></div>
            </div>
            <div class="col-md-12">
                <div class="bottom_bg">
                    <div class="welcome_desc">
                        <div class="course_demo">
                            <div id="business-services">
                                <?php
                                $indx = 1;
                                while ($rowleading = mysqli_fetch_object($rsleading)) {
                                    if ($indx % 2 == 1) {
                                        echo '<li>';
                                    }
                                ?>
                                    <div class="slick-wrapper">
                                        <a class="slick-product-wrapper" href="<?php echo $rowleading->adv_link; ?>" target=_blank>
                                            <div class="demobox">
                                                <div class="slick-product-image">
                                                    <img alt="" src="https://egyptmart.shop/upload/service_slider/<?php echo $rowleading->adv_img; ?>" class="black" style="max-width:115px" title="<?php echo $rowleading->adv_title; ?>">
                                                </div>
                                                <div class="matterbox">
                                                    <div class="icon-pic-with-heading">
                                                        <div class="ihover-wrapper">
                                                            <h3 class="ihoves">
                                                                <?php echo substr($rowleading->adv_title, 0, 20); ?><?php if(strlen($rowleading->adv_title) > 20) echo '...'; ?>
                                                            </h3>
                                                            <div class="auction_hover">
                                                                <p><?php echo $rowleading->adv_title; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="rightmatter">
                                                        <p><span class="nam"><?php echo $rowleading->cn_name; ?></span></p>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                <?php
                                    if ($indx % 2 == 0) {
                                        echo '</li>';
                                    }
                                    $indx++;
                                }
                                if ($rembaner == 1) {
                                    echo '</li>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
}



  //function PrintLeadingProduct()
{
    global $con, $strconutnry;
  
  
    ?>
    <div class="booking" title="ضع هنا - إعلانات المساحات الخاصة بشركتك - للعرض على أهم 10.000 شركة"><a href="advertise-with-us.php"target="_blank"> Advertise Here Now for <span>FREE for SENIOR Suppliers</span>
        , Kindly Contact Advertisements Team </a>
    </div>
    <div class="video_slider desktop-slider" style="width:100%;padding-left:5px;margin:10px 0 0 0px;">
      <div class="slider">
        <ul class="rslides" id="slider">
          <?php
          ## get the top compnay video
          $sqlvideo = "select * from video_slider where adv_status='1'";
          $resvideo = mysqli_query($con, $sqlvideo);
          $totalvideo = mysqli_num_rows($resvideo);
          var_dump($totalvideo);
          if ($totalvideo > 0) {
            while ($row_video = mysqli_fetch_object($resvideo)) {
              $cv_video_link = $row_video->adv_link;
              $adv_redirect = $row_video->adv_redirect;
              $chklink = explode("://", $cv_video_link);
              if ($chklink[0] == "http" || $chklink[0] == "https") {
                preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $cv_video_link, $matches);
                $id = $matches[1];
                $width = '100%';
                $height = '181';
                $iframe2show = '<iframe class="hidden-xs" width="100%" height="181"
                                                                       src="https://www.youtube.com/embed/' . $id . '" frameborder="0"
                                                                       allowfullscreen></iframe>';
              } else {
                $iframe2show = $cv_video_link;
              }
              $bnsprof_compname = $row_video->adv_title;
              $bnsprof_address1 = $row_video->adv_description;
          ?>
              <li>
                <?php echo $iframe2show; ?>
                <div class="iframebox">
                  <h2><i class="fa fa-play"></i>
                    <a href="<?php echo $adv_redirect; ?>" target="_blank"><?php echo substr(($bnsprof_compname), 0, 22) . ".." ?></a>
                  </h2>
                  <p><?php echo substr(($bnsprof_address1), 0, 30) . ".." ?>
                </div>
              </li>
              <?php
            } ?><?php
            } ?>
        </ul>
      </div>
      <div class="verifiedbox_bottom">
        <h4><a href="company-video.php">Post <span>FREE </span>Company Video</a></h4>
      </div>
    </div>
    <div class="col-lg-12 col-xs-12 col-sm-12 list-top mobile-hide-now">
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
                                    (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . (isset($location_geo_country) ? $location_geo_country : 243) . "')))
                                    )";
      $sql_so_ck = " and (
                                    (so_preferred_buyer_location='any')
                                    or
                                    (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . (isset($location_geo_country) ? $location_geo_country : 243) . "')))
                                    )";
      $sql_br_ck = " and (
                                    (br_preferred_supplier_location='any')
                                    or
                                    (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . (isset($location_geo_country) ? $location_geo_country : 243) . "')))
                                    )";
    }
    if ($_COOKIE['loc_id'] != "") {
        
        $sqlleading = "select * from prodservice_slider, country 
               where slider_supplier_country=cn_id 
               and adv_status='1' and adv_type='1' $strconutnry 
               order by rand()";
        
    } else {
        
        $sqlleading = "select * from prodservice_slider, country 
               where slider_supplier_country=cn_id 
               and adv_status='1' and adv_type='1' $strconutnry 
               order by rand()";
        
    }

    $rsleading = mysqli_query($con, $sqlleading);
    $totalbaneer = mysqli_num_rows($rsleading);
    $rembaner = $totalbaneer % 2;
    if ($totalbaneer > 0) { ?>
      <div class="demobox">
        <div class="booking mobile-hide"><a href="advertise-with-us.php"> Advertise Here Now for <span>FREE</span> Kindly Contact Advertisements Team </a></div>
        <div class="clear"></div>
        <div class="countrubox_top2">
          <div class="countrubox_heading">
            <div class="mainflagbox">
              <div class="membershipicon"><a href="membership_plans.php"><img alt="" src="images/membership-icon01.png"></a></div>
              <div class="membershipicon"><a href="membership_plans.php"><img alt="" src="images/membership_icon02.png"></a></div>
            </div>
            <div class="countryheadingboxleft" title="أهم منتجات - الشركات الرائدة - منصة سوق مصر على الإنترنت">
              <h3><a href="#"><span class="black-color">Arab-MART</span> Leading Suppliers</a></h3>
            </div>
          </div>
          
          <div class="list-rights" title="أنشر إعلانات - الموردين ورواد الصناعة والتجارة - مجانا">
            <h2><a href="advertise-with-us.php" target="_blank"><span>+ Post</span> FREE Ads</a></h2>
          </div>
          
          <div class="clear"></div>
        </div>
        <div class="wrapper-container">
          <div class="white_bg">
            <div class="welcome_desc">
              <div class="course_demo">
                <ul id="Arab-MART-leading">
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php
    }
  }
  ?>
  <script>
    function init() {
      var vidDefer = document.getElementsByTagName('iframe');
      for (var i = 0; i < vidDefer.length; i++) {
        if (vidDefer[i].getAttribute('data-src')) {
          vidDefer[i].setAttribute('src', vidDefer[i].getAttribute('data-src'));
        }
      }
    }
    window.onload = init;
  </script>