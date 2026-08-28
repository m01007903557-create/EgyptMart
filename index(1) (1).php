<?php
// تصحيح المتغيرات غير المعرفة
error_reporting(E_ALL);
ini_set('display_errors', 1);

$location_geo_country = $location_geo_country ?? '';

// التحقق من وجود تحديد تلقائي للموقع وعدم وجود خيار محدد مسبقًا من المستخدم
if (empty($_COOKIE['loc_id']) && !empty($location_geo_country)) {
    // تعيين الـ cookie بقيمة البلد المستنتج من IP (تأكد من أن `$location_geo_country` هو كود البلد الصحيح، مثلاً 'EG'، 'SA'، ...)
    setcookie('loc_id', $location_geo_country, time() + (86400 * 30), "/"); // 86400 = 1 يوم (مدة صلاحية cookie لمدة 30 يومًا)
    $_COOKIE['loc_id'] = $location_geo_country; // تحديث المتغير للجلسة الحالية

    // **الخطوة الحاسمة لتحديث الصفحة**: إعادة تحميل الصفحة لتعكس المحتوى الخاص بالبلد الجديد.
    echo '<script>window.location.reload();</script>';
    exit; // إيقاف تنفيذ باقي السكربت لحين إعادة التحميل
}

$uid = $_SESSION['uid_indm'] ?? 0;
$myvar = $myvar ?? '';


  ob_start();
  session_start();
  set_time_limit(600);
  include 'common.php';
  
  $globalcntid = 243;
  if (isset($_COOKIE['loc_id']))
  {
      ## get Country id by
      $cn_id = $_COOKIE['loc_id'];
      $sqlcountry = "select cn_name from country where cn_id='$cn_id'";
      $rscountry = mysqli_query($con, $sqlcountry);
      if (mysqli_num_rows($rscountry) > 0)
      {
          $rowcountrty = mysqli_fetch_object($rscountry);
          $cn_name = $rowcountrty->cn_name;
      }
  }
  else
  {
      $cn_id = 0;
      $cn_name = "Global";
  }
  ini_set('display_errors', 1);
  error_reporting(E_ALL & ~E_NOTICE);
  ## query for country
  if ($cn_id != "" && $cn_id > 0)
  {
      //$strconutnry=" AND (adv_country LIKE '%$cn_id,%' OR adv_country LIKE '%,$cn_id%' OR adv_country LIKE '%,$cn_id,%' OR adv_country='$cn_id')";
      $strconutnry = " AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
  }
  else
  {
      //$strconutnry =" AND (adv_country LIKE '%$globalcntid,%' OR adv_country LIKE '%,$globalcntid%' OR adv_country='$globalcntid')";
      $strconutnry = " AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
  }
  ?><!DOCTYPE html>
<?php
  $current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
  if ($current_url == 'http://egyptmart.shop/' || $current_url == 'http://egyptmart.shop/index')
  { ?>
<meta content="text/html; charset=utf-8" http-equiv=Content-Type>
<meta content=webkit name=renderer>
<meta content="width=device-width,initial-scale=1" name=viewport>
<meta content="<?php echo getSiteTitle(); ?>" name=title>
<meta content="<?php echo get_page_settings(2); ?>" name=keywords>
<meta content="<?php echo get_page_settings(3); ?>" name=description>

<title><?php echo getSiteTitle(); ?></title>
<link href=css/bootstrap.css rel=stylesheet>
<script src="js/jquery.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script> -->
<link href=css/style.css?t=<?php echo rand(); ?> rel=stylesheet>
<link href=css/responsive1.css rel=stylesheet>
<link href=fonts/font-awesome.css rel=stylesheet>
<link href="fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>
<link href=css/im-style-v1.css rel=stylesheet>
<!--[if IE]>
<script src=js/html5.js></script><![endif]-->
<link href=css/verticle-menu.css rel=stylesheet>
<link href=css/theme.css rel=stylesheet>
<script src=js/jquery.accessible-news-slider.js></script>
<link href=css/type.css rel=stylesheet>
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" type="text/javascript" charset="utf-8"></script> -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
<script src=js/responsiveslides.min.js></script>
<?php
  }
  else
  { ?>
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
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script> -->
<link href="css/style.css?ver=3.3" rel="stylesheet">
<link href=css/responsive1.css rel=stylesheet>
<link href=fonts/font-awesome.css rel=stylesheet>
<link href=css/im-style-v1.css rel=stylesheet>
<!--[if IE]>
<script src=js/html5.js></script><![endif]-->
<link href=css/verticle-menu.css rel=stylesheet>
<link href=css/theme.css rel=stylesheet>
<script src="js/jquery.accessible-news-slider.js"></script>
<link href=css/type.css rel=stylesheet>
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<script src="js/responsiveslides.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.js" type="text/javascript" charset="utf-8"></script> -->
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
<?php
  } ?>
<script type="text/javascript">
//   setInterval(function(){ 
//       $('button.slick-next.slick-arrow').trigger('click');
//        }, 3000);

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
  color:black!important;
  cursor: pointer;
  }
</style>
<script>
  $(function () {
      $("#slider").responsiveSlides({auto: !0, nav: !1, speed: 500, namespace: "callbacks", pager: !0})
  });
</script>
<script>
  jQuery(document).ready(function () {
      get_load_leftdata();
      setTimeout(() => {
	$('#pull').trigger('click');
	console.log('click pull');
	}, 500);

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
    $myvar;
    if (isset($_SESSION["popup"]))
    {
        $myvar = $_SESSION["popup"];
    }
    if ($myvar == 1 || $myvar == "")
    {
    ?>
  <div class="loader_img">
    <div class="popup-box">
      <div class="backg">
        <a href="#"><img id="popup_close" class="close" src="close.png"/></a>
        <!--<h1>Welcome to EgyptMART</h1>
          <h2>  مستخدم جديد ؟ إنضم مجانا الآن <a href="http://egyptmart.shop//create_account.php"title=" &#1571;&#1606;&#1588;&#1609;&#1569; &#1581;&#1587;&#1575;&#1576; &#1605;&#1580;&#1575;&#1606;&#1575; "> </a></h2>-->
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '')
          {
              $uid = $_SESSION['uid_indm'];
          ?>
        <p class="bp13 m12 j1">  <span class="cr6"><?php echo user_info($uid, 'name_prefix') . "&nbsp;" . user_info($uid, 'fname'); ?>مرحبا بك </span><br><span><b
          class="bo1"title=" &#1604;&#1608;&#1581;&#1577; &#1605;&#1601;&#1575;&#1578;&#1610;&#1581; - &#1571;&#1593;&#1605;&#1575;&#1604; &#1575;&#1604;&#1605;&#1606;&#1589;&#1577;  "></b><a href="my-dashboard.php" style="text-decoration:none;">  لوحة التحكم</a></span>
        </p>
        <?php
          }
          else
          { ?>
        <p class="bp13 m12 j1"style=" font-family:GE SS Two ; font-size: 16px ;"title=" &#1605;&#1585;&#1581;&#1576;&#1575; &#1576;&#1603; &#1601;&#1609; - &#1605;&#1606;&#1589;&#1577; &#1587;&#1608;&#1602; &#1605;&#1589;&#1585; &#1593;&#1604;&#1609; &#1575;&#1604;&#1573;&#1606;&#1578;&#1585;&#1606;&#1578; ">  مرحبا بك فى أسواق التصدير والجملة <br><span><b class="bo1"><a href="dir.php"> &nbsp; </b>  شاهد أسواق تجارة مصر والعالم &nbsp;</a></span></p>
        <?php
          } ?>
        <div class="tab-main">
          <div class="wrapper">
            <ul class="top_btn_list">
              <li>
                <input id="radio1" class="radio_btn" name="tab" type="radio">
                <label for="radio1" class="label_btn"style=" font-family:GE SS Two ; font-size:25px; font-wieght:bold; "title=" &#1604;&#1601;&#1585;&#1589; &#1575;&#1604;&#1588;&#1585;&#1575;&#1569; – &#1604;&#1604;&#1605;&#1588;&#1578;&#1585;&#1610;&#1610;&#1606;  ">للـشــراء  </label>
                <div id="cont1" class="content_tab"title=" &#1605;&#1587;&#1578;&#1608;&#1585;&#1583; –  &#1571;&#1608; &#1588;&#1585;&#1603;&#1577; &#1588;&#1585;&#1575;&#1569; –  &#1571;&#1608;  &#1578;&#1575;&#1580;&#1585;  -  &#1571;&#1608; &#1576;&#1610;&#1578; &#1588;&#1585;&#1575;&#1569; &#1605;&#1578;&#1582;&#1589;&#1589; ">
                  <img class="heading" src="head.png"/>
                  <div class="text-box">
                    <div class="text-box-left">
                      <img src="1.png"/>
                    </div>
                    <div class="text-box-right">
                      <h2><a href=""title=" &#1573;&#1585;&#1587;&#1604; &#1604;&#1606;&#1575; - &#1591;&#1604;&#1576;&#1575;&#1578; &#1588;&#1585;&#1575;&#1569; -  &#1608;&#1578;&#1604;&#1602;&#1609; &#1578;&#1587;&#1593;&#1610;&#1585;&#1575;&#1578; &#1608;&#1571;&#1601;&#1590;&#1604; &#1593;&#1585;&#1608;&#1590; &#1575;&#1604;&#1576;&#1610;&#1593; -  &#1605;&#1606; &#1588;&#1585;&#1603;&#1575;&#1578; &#1576;&#1610;&#1593; &#1605;&#1572;&#1607;&#1604;&#1577; &#1608;&#1604;&#1607;&#1575; &#1608;&#1580;&#1608;&#1583; &#1581;&#1601;&#1610;&#1601;&#1609;  "> طلبات تسعير للحصول على أقل سعر  </a></h2>
                      <p>
                      </p>
                    </div>
                  </div>
                  <div class="text-box">
                    <div class="text-box-left">
                      <img src="2.png"/>
                    </div>
                    <div class="text-box-right">
                      <h2><a href="#" style="cursor:text"title=" &#1573;&#1576;&#1581;&#1579; &#1608;&#1573;&#1603;&#1578;&#1588;&#1601; –  &#1571;&#1604;&#1575;&#1601; &#1605;&#1606; &#1601;&#1585;&#1589; &#1575;&#1604;&#1576;&#1610;&#1593; -  &#1604;&#1605;&#1606;&#1578;&#1580;&#1575;&#1578; &#1608;&#1582;&#1583;&#1605;&#1575;&#1578; &#1578;&#1580;&#1575;&#1585;&#1610;&#1577; &#1604;&#1571;&#1593;&#1605;&#1575;&#1604;&#1603;  &#1608;&#1578;&#1580;&#1575;&#1585;&#1578;&#1603; "> إبحث عن أى شىء لتجارتك   </a>
                      </h2>
                      <p>
                      </p>
                      <form method="GET" name="searchForm2" action="search.php"
                        onsubmit="return validsearch_r();">
                        <input size="24" class="m1 bl6" id="keywords_r" name="keywords"
                          type="text"title="- &#1573;&#1576;&#1581;&#1579; &#1608;&#1573;&#1603;&#1578;&#1588;&#1601; –  &#1571;&#1604;&#1575;&#1601; &#1605;&#1606; &#1601;&#1585;&#1589; &#1575;&#1604;&#1576;&#1610;&#1593; ">
                        <input value="إبحث" class="m1 fz1 ff1 m5" type="submit"title="- &#1573;&#1576;&#1581;&#1579; &#1608;&#1573;&#1603;&#1578;&#1588;&#1601; –  &#1571;&#1604;&#1575;&#1601; &#1605;&#1606; &#1601;&#1585;&#1589; &#1575;&#1604;&#1576;&#1610;&#1593; ">
                      </form>
                    </div>
                  </div>
                  <div class="text-box">
                    <div class="text-box-left">
                      <img src="3.png"/>
                    </div>
                    <div class="text-box-right">
                      <h2><a href=""title=" &#1587;&#1580;&#1604; - &#1571;&#1607;&#1605; &#1575;&#1604;&#1571;&#1589;&#1606;&#1575;&#1601; - &#1575;&#1604;&#1578;&#1609; &#1578;&#1608;&#1583; &#1588;&#1585;&#1575;&#1572;&#1607;&#1575;  -  &#1604;&#1578;&#1578;&#1604;&#1602;&#1609; &#1573;&#1588;&#1593;&#1575;&#1585;&#1575;&#1578; –  &#1593;&#1585;&#1608;&#1590; &#1576;&#1610;&#1593; &#1582;&#1575;&#1589;&#1607;  &#1604;&#1607;&#1575;   –  &#1601;&#1609; &#1576;&#1585;&#1610;&#1583;&#1603; &#1608;&#1593;&#1604;&#1609; &#1575;&#1604;&#1605;&#1608;&#1576;&#1575;&#1610;&#1604; ">تلقى مشترياتك على بريدك  </a></h2>
                      <p></p>
                    </div>
                  </div>
                  <div>
                  </div>
                </div>
              </li>
              <li>
                <input id="radio2" class="radio_btn" name="tab" type="radio" checked="checked">
                <label for="radio2" class="label_btn"style=" font-family:GE SS Two; font-size:25px"title=" &#1604;&#1604;&#1605;&#1608;&#1585;&#1583;&#1610;&#1606; - &#1588;&#1585;&#1603;&#1575;&#1578; -  &#1608;&#1605;&#1589;&#1575;&#1606;&#1593; - &#1608;&#1582;&#1583;&#1605;&#1575;&#1578; &#1578;&#1580;&#1575;&#1585;&#1610;&#1577; ">للـبيــع  
 </label>
                <div id="cont2" class="content_tab"title=" &#1604;&#1604;&#1605;&#1589;&#1606;&#1593;&#1610;&#1606;  –  &#1608;&#1575;&#1604;&#1605;&#1589;&#1583;&#1585;&#1610;&#1606;  –  &#1608;&#1578;&#1580;&#1575;&#1585; &#1575;&#1604;&#1580;&#1605;&#1604;&#1577;  –  &#1608;&#1578;&#1580;&#1575;&#1585; &#1575;&#1604;&#1578;&#1580;&#1586;&#1574;&#1577;  –  &#1608;&#1605;&#1602;&#1583;&#1605;&#1608; &#1575;&#1604;&#1582;&#1583;&#1605;&#1575;&#1578; &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1610;&#1577;  ">
                  <img class="heading" src="head2.png"/>
                  <div class="text-box">
                    <div class="text-box-left">
                      <img src="1.png"/>
                    </div>
                    <div class="text-box-right">
                      <h2> <a href="  "title="  &#1573;&#1606;&#1590;&#1605; &#1604;&#1571;&#1607;&#1605; 10,0000 - &#1576;&#1575;&#1574;&#1593; &#1608;&#1605;&#1588;&#1578;&#1585;&#1609; &#1605;&#1589;&#1585;&#1609; &#1608;&#1593;&#1585;&#1576;&#1609; &#1608;&#1593;&#1575;&#1604;&#1605;&#1609; - &#1601;&#1609; &#1571;&#1603;&#1576;&#1585; &#1605;&#1606;&#1589;&#1577; &#1571;&#1593;&#1605;&#1575;&#1604; - &#1604;&#1604;&#1576;&#1610;&#1593; &#1608;&#1575;&#1604;&#1588;&#1585;&#1575;&#1569; - &#1576;&#1610;&#1606; &#1575;&#1604;&#1588;&#1585;&#1603;&#1575;&#1578; - &#1601;&#1609; &#1605;&#1589;&#1585; &#1608;&#1575;&#1604;&#1605;&#1606;&#1591;&#1601;&#1577; &#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577; "style="text-decoration:none;text-align:right;">كتالوج شامل  لكل منتجاتك +  </a></h2>
                      <p> </p>
                    </div>
                  </div>
                  <div class="text-box">
                    <div class="text-box-left">
                      <img src="2.png"/>
                    </div>
                    <div class="text-box-right">
                      <h2><a href=""title=" &#1573;&#1606;&#1588;&#1609;&#1569; &#1589;&#1601;&#1581;&#1575;&#1578; - &#1571;&#1593;&#1605;&#1575;&#1604;&#1603; &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1610;&#1577; –  &#1605;&#1608;&#1602;&#1593;&#1603; &#1575;&#1604;&#1605;&#1589;&#1594;&#1585; / &#1605;&#1578;&#1580;&#1585;&#1603;  – &#1575;&#1604;&#1584;&#1609; &#1610;&#1581;&#1578;&#1608;&#1609; &#1593;&#1604;&#1609; &#1571;&#1607;&#1605; &#1605;&#1606;&#1578;&#1580;&#1575;&#1578;&#1603; &#1608;&#1582;&#1583;&#1605;&#1575;&#1578;&#1603; &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1610;&#1577; –  &#1601;&#1609; &#1587;&#1608;&#1602; &#1605;&#1589;&#1585; &#1593;&#1604;&#1609; &#1575;&#1604;&#1575;&#1606;&#1578;&#1585;&#1606;&#1578; &#1576;&#1610;&#1606; &#1571;&#1607;&#1605; &#1578;&#1580;&#1575;&#1585; &#1608;&#1605;&#1589;&#1575;&#1606;&#1593; &#1605;&#1589;&#1585; &#1608;&#1575;&#1604;&#1605;&#1606;&#1591;&#1602;&#1577; ">  ويب سايت لشركتك بسوق التجارة + <p></p>
                    </div>
                  </div>
                  <div class="text-box">
                    <div class="text-box-left">
                      <img src="3.png"/>
                    </div>
                    <div class="text-box-right">
                      <h2><a href=""title=" &#1587;&#1580;&#1604; - &#1571;&#1607;&#1605; &#1575;&#1604;&#1571;&#1589;&#1606;&#1575;&#1601; - &#1575;&#1604;&#1578;&#1609; &#1578;&#1602;&#1608;&#1605; &#1576;&#1576;&#1610;&#1593;&#1607;&#1575; -  &#1604;&#1578;&#1578;&#1604;&#1602;&#1609; &#1573;&#1588;&#1593;&#1575;&#1585;&#1575;&#1578; –  &#1591;&#1604;&#1576;&#1575;&#1578; &#1588;&#1585;&#1575;&#1569; &#1580;&#1575;&#1607;&#1586;&#1577; –  &#1601;&#1609; &#1576;&#1585;&#1610;&#1583;&#1603; - &#1608;&#1593;&#1604;&#1609; &#1575;&#1604;&#1605;&#1608;&#1576;&#1575;&#1610;&#1604;     "> طلبات شراء لمنتجاتك +</a></h2>
                      <p> </p>
                    </div>
                  
                    </div>
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
    //$(window).load(function() {
    //$(".loader_img").fadeOut("slow");
    //});
    $("#popup_close").click(function () {
        $(".loader_img").fadeOut("slow");
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '')
      { ?>
        $.post("/UpdateTheSession.php", {"id": "2"});
        <?php
      } ?>
    });
  </script>
  <!--<div id="fb-root"></div>
  <script>
    !function (e, n, t) {
        var o, c = e.getElementsByTagName(n)[0];
        e.getElementById(t) || (o = e.createElement(n), o.id = t, o.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0", c.parentNode.insertBefore(o, c))
    }(document, "script", "facebook-jssdk")
  </script>-->
  <?php
    if (get_page_settings('25') == 'manual')
    {
        $sql_order = " order by pc_order,pc_name";
    }
    else
    {
        $sql_order = " order by pc_name";
    }
    ?>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/style123.css?t=<?php echo rand(); ?>" type="text/css" rel="stylesheet"/ >

  <div class=wrapper>

    <?php include "includes/header.php"; ?>
    
    <script>
document.addEventListener("DOMContentLoaded", function() {
    // البحث عن الـ header
    var header = document.querySelector('header');
    if (header) {
        header.style.marginBottom = '0';
        header.style.paddingBottom = '0';
    }
    
    // البحث عن أول عنصر في المحتوى
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
                <i class="fa fa-list-ul" style=color:#fff ></i>
                <span style="display:inline-table;color:#fff;">أسواق تجـارتى</span>
                </a>
              </h3>
            </div>
            <div id="block_navigation"title=" &#1578;&#1589;&#1606;&#1610;&#1601;&#1575;&#1578; - &#1603;&#1604; &#1605;&#1580;&#1575;&#1604;&#1575;&#1578; - &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1577; ">
              <div id="pull" style="display:none;">
                <a href="#">
                <img class="menu-img" alt="" src="css/img/my-marketA.png">
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
                    if ($totalvideo > 0)
                    {
                        while ($row_video = mysqli_fetch_object($resvideo))
                        {
                            $cv_video_link = $row_video->adv_link;
                            $adv_redirect = $row_video->adv_redirect;
                            $chklink = explode("://", $cv_video_link);
                            if ($chklink[0] == "http" || $chklink[0] == "https")
                            {
                                $id = '';
if (preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $cv_video_link, $matches)) {
    $id = $matches[1];
}
                                $width = '100%';
                                $height = '181';
                                $iframe2show = '<iframe class="hidden-xs" width="100%" height="181"
                                                                                     src="https://www.youtube.com/embed/' . $id . '" frameborder="0"
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
                    <?php echo $iframe2show; ?>
                    <div class="iframebox">
                      <h2><i class="fa fa-play"></i><a href="<?php echo $adv_redirect; ?>"
                        target="_blank"><?php echo substr(($bnsprof_compname) , 0, 22) . ".." ?></a>
                      </h2>
                      <p><?php echo substr(($bnsprof_address1) , 0, 30) . ".." ?>
                    </div>
                  </li>
                  <?php
                    } ?><?php
                    } ?>
                </ul>
              </div>
              <div class="verifiedbox_bottom" title=" &#1571;&#1606;&#1588;&#1585; &#1571;&#1607;&#1605; - &#1601;&#1610;&#1583;&#1610;&#1608;&#1607;&#1575;&#1578; &#1588;&#1585;&#1603;&#1575;&#1578;&#1603; - &#1590;&#1605;&#1606; &#1576;&#1585;&#1608;&#1601;&#1575;&#1610;&#1604; &#1588;&#1585;&#1603;&#1578;&#1603; ">
                <h4><a href="company-video.php"> للنشر  <span> فيديو شركتك </span> حمل هنا </a></h4>
              </div>
            </div>
            <div class="col-lg-12 col-xs-12 col-sm-12 list-top mobile-slider" title="&#1604;&#1605;&#1608;&#1585;&#1583;&#1610;&#1606; &#1605;&#1576;&#1576;&#1610;&#1593;&#1575;&#1578; &#1575;&#1604;&#1580;&#1605;&#1604;&#1577; - &#1573;&#1606;&#1588;&#1609;&#1569; &#1581;&#1602;&#1610;&#1576;&#1577; &#1605;&#1606;&#1578;&#1580;&#1575;&#1578;&#1603; - &#1604;&#1604;&#1593;&#1585;&#1590; &#1593;&#1604;&#1609; &#1578;&#1580;&#1575;&#1585;&#1577; &#1575;&#1604;&#1578;&#1580;&#1586;&#1574;&#1577;  ">
              <h1>
                <a href=#>
                <img alt="" src="images/wholesaler.png">
                </a>
              </h1>
              <h5>إنشىء الآن</h5>
              <a href="membership_plans.php" target=_>
                <div class="showcase">صفحات منتجاتك </div>
              </a>
              <p>التوزيع داخل وخارج المدن
            </div>
            <div class="col-lg-12 col-xs-6 col-sm-12 map-tops">
              <div class="map"><a href="#"><a href="#"><img alt="" src="images/map.png"></a></a></div>
            </div>
            <div class="col-lg-12 col-xs-6 col-sm-12 list-top mobile-hide">
              <div class="seniorbox">
                <div class="siniorlistbox">
                  <div class="siconbox"><img alt="" src=images/left-icon.png></div>
                  <div class="scontentbox">
                    <h2>SENIOR <span> عضــوية </span></h2>
                  </div>
                  <div class="clear"></div>
                </div>
                <ul>
                  <li>> <a href="#">B2B مينى سايت للشركة بنظام  </a>
                  <li>> <a href="#">عرض كل منتجات الشركة </a>
                  <li>> <a href="#">الترتيب الثانى قى عرض المنتجات</a>
                  <li>> <a href="#">دخول شامل لطلبات الشراء الجاهزة</a>
                  <li>> <a href="#">مساحات إعلانية مجانية للمنتجات </a>
                  <li>> <a href="#">فيديوهات عن الشركة ومنتجاتها</a></li>
                  <p><a href="membership_plans.php"> تعـلم المزيد <span>> ></span></a>
                </ul>
                <h3><a href="membership_plans.php">إشترك الآن </a></h3>
         
            
              </div>
              
              
              <?php if (preg_match('/mobile|android|iphone|ipad/i', $_SERVER['HTTP_USER_AGENT'])): ?>
    <div style="clear:both; width:100%;"></div>
<?php endif; ?>
 
            </div>
            <div class="col-lg-12 col-xs-6 mid-tops">
              <?php
                $banner = GetHomeBanner('left', $strconutnry);
                if ($banner != "")
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
            <div class="clear"></div>
          </div>
          <div class="col-xs-12" id="midcenter">
            <style>
              /* Center the loader */
              #$.LoadingOverlay{
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
              0% { -webkit-transform: rotate(0deg); }
              100% { -webkit-transform: rotate(360deg); }
              }
              @keyframes spin {
              0% { transform: rotate(0deg); }
              100% { transform: rotate(360deg); }
              }
              /* Add animation to "page content" */
              .animate-bottom {
              position: relative;
              -webkit-animation-name: animatebottom;
              -webkit-animation-duration: 1s;
              animation-name: animatebottom;
              animation-duration: 1s
              }
              @-webkit-keyframes animatebottom {
              from { bottom:-100px; opacity:0 } 
              to { bottom:0px; opacity:1 }
              }
              @keyframes animatebottom { 
              from{ bottom:-100px; opacity:0 } 
              to{ bottom:0; opacity:1 }
              }
            </style>
            <div id="$.LoadingOverlay"></div>
            <div class="slider r_css"  >
              <div class="yahoo_slider" style="width:100%;opacity:0;"id="myDiv">
                <ul id="newsslider" style="width:100%;">
                  <?php
                    $sqllogo = "select * from yahoo_slider where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
                    $rslogo = mysqli_query($con, $sqllogo);
                    if (mysqli_num_rows($rslogo) > 0)
                    {
                        while ($rowlogo = mysqli_fetch_object($rslogo))
                        {
                            $adv_img = $rowlogo->adv_img;
                            $logopath = "upload/yahoo_slider/" . $adv_img;
                            $adv_link = $rowlogo->adv_link;
                            $adv_title = $rowlogo->adv_title;
                            $adv_description = Show_shortcontent($rowlogo->adv_description, 22);
                            $adv_imagewidth = $rowlogo->adv_imagewidth;
                            $adv_imageheight = $rowlogo->adv_imageheight;
                    ?>
                  <li>
                    <a href="<?php echo $adv_link; ?>" target="_blank" style="width:100%;"><img
                      alt="<?php echo $adv_title; ?>" src="<?php echo $logopath; ?>"
                      style="width:100% !important;"></a>
                    <h3><a href="<?php echo $adv_link; ?>"
                      target="_blank"><?php echo $adv_title; ?></a></h3>
                    <p><a href="<?php echo $adv_link; ?>" style="color:#gray"></a>
                  </li>
                  <?php
                    }
                    }
                    else
                    {
                    echo '';
                    }
                    ?>
                </ul>
              </div>
            </div>
            </script>
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
                  <p><a href="membership_plans.php">Learn More <span>> ></span></a>
                </ul>
                <h3><a href="membership_plans.php">Upgrade Now</a></h3>
              </div>
            
            
            
            
            
            
            
            
            
            
            
            
            </div>
            <div class="country-wrapper-with-verfier">
              <div class="countrybox">
                <div class="countrubox_top"title=" &#1576;&#1575;&#1574;&#1593;&#1610;&#1606; &#1608;&#1605;&#1588;&#1578;&#1585;&#1610;&#1610;&#1606; ">
                  <div class="countrubox_heading global-page">
                    <div class="global-icon-search">
                      <div class="globalicon"title=" GLOBAL PAGE" ><b></b> <a href="#" onclick="unsetCountryLocation();"><img alt=Global
                          src="images/Untit.png"></a>
                      </div>
                      <h2><span>  دول عربية مشاركة  </span></h2>
                    </div>
                    <div class="search">
                      <input id="search" name="search" class="textbox" placeholder="إبحـث عن البلـد">
                      <input id="submit" name="submit" type="submit" value="Subscribe">
                      <script>$("#submit").click(function () {
                        var c = document.getElementById("search").value, e = "cname=" + c;
                        $.ajax({
                            type: "POST",
                            url: "search_country.php",
                            data: e,
                            cache: !1,
                            success: function (c) {
                                $("#response").html(c)
                            }
                        })
                        })
                        // $(document).ready(function(){
                        // setTimeout(function(){ $('#myDiv').css('opacity','1');$('#$.LoadingOverlay').css('opacity','0'); }, 1000);

                          // function changeHeaderDeferred() {
                          //     var myDiv = document.getElementById("myDiv");
                          //     var $.LoadingOverlay = document.getElementById("$.LoadingOverlay");
                              
                          //     setTimeout(function changeHeader() {
                          //       myDiv.style.opacity = "1";
                          //       $.LoadingOverlay.style.opacity = "0";
                          //       return false;
                          //     }, 1000);

                          //     return false;
                          //   }
                          //   changeHeaderDeferred();
                        // });
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
                          <td><span class="asia"><b>Asia   :</b></span>
                          <td>
                            <ul class="country">
                              <li><a href="#" onclick="setCountryLocation(225);"><img alt=""
                                src="images/uae.jpg">
                                <span style="color:#4163a9;">UAE<span></a>
                              <li><a href="#" onclick="setCountryLocation(187)"><img alt=""
                                src="images/Saudi-Arabia.jpg">
                                <span style="color:#4163a9">Saudi Arb.<span></a>
                              <li><a href="#" onclick="setCountryLocation(112)"><img alt=""
                                src="images/Kuwait.jpg">
                                <span style="color:#4163a9">Kuwait<span></a>
                              <li><a href="#" onclick="setCountryLocation(173)"><img alt=""
                                src="images/jordan.jpg">
                                <span style="color:#4163a9">Jordan<span></a>
                              <li>
                                <a href="#" onclick="setCountryLocation(116)">
                                  <img alt=""
                             </ul>
                      </table>
                    </div>
                    <div class="cnt1">
                    <table>
                    <tr>
                    <td><span class="africa"><b>Africa:</b></span>
                    <td>
                    <ul class="country">
                    <li><a href="#" onclick="setCountryLocation(63)">
                    <img alt="" src="images/flag01.png"> <span
                      style="color:#4163a9">Egypt<span></a>
                    <li><a href="#" onclick="setCountryLocation(202)"><img alt=""
                    </ul>
                    </table>
                    </div>
                  </div>
                
                </div>
              </div>
              <div class=verifiedbox_supplierbox>
   <h3>شركـات لهـا وجـود حقيـقى </h3></a><p> <<  أكبـر عشـرة آلاف شركـة ومصتـع فى مصـر شاهد تفاصيل العضويات 

      <span class="fright"</a></span><href="membership_plans.php"> </a></span> 
              <div class=clear></div>
              <ul>
              <li><a href="membership_plans.php" class="tooltip1" ><img alt=""
                src="images/verified01.jpg" title="  &#1605;&#1608;&#1585;&#1583; - &#1587;&#1576;&#1608;&#1606;&#1587;&#1608;&#1585; - &#1575;&#1604;&#1571;&#1593;&#1604;&#1609; - &#1605;&#1586;&#1575;&#1610;&#1575; ">
              <span><i>SPONSOR عضـوية </i></span></a>
              <li><a href="membership_plans.php" class="tooltip1"><img alt=""
                src="images/verified02.jpg">
              <span><i>SENIOR Supplier</i></span></a>
              <li><a href="membership_plans.php" class="tooltip1"><img alt=""
                src="images/verified03.jpg">
              <span><i>JUNIOR Supplier</i></span></a>
              </ul>
              </div>
          
            </div>
            <div class="space21"></div>
            <div class="countrubox_top2"title="  &#1588;&#1575;&#1607;&#1583; - &#1575;&#1604;&#1605;&#1606;&#1578;&#1580;&#1575;&#1578; - &#1608;&#1575;&#1604;&#1605;&#1608;&#1585;&#1583;&#1610;&#1606; ">
           <div class="countrubox_heading desktop-only">
              
               
                <h2><a href="product-sel-cat.php"><span></span>إعرض تجارتك +</a></h2>
              </div>
              <div class="list-rights desktop-only"title="  &#1573;&#1593;&#1585;&#1590; - &#1605;&#1606;&#1578;&#1580;&#1575;&#1578;&#1603; &#1608;&#1582;&#1583;&#1605;&#1575;&#1578;&#1603; - &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1610;&#1577; ">
               <h2><a href="dir.php#main_cat"title="View All Products & Suppliers"><span> منتجات الشركات </span></a></h2>
                
                </div>
                 <div class="clear"></div>
            </div>
              <div class="demobox desktop-only">  <!-- أضف الكلاس هنا -->
         
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
        (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1)))
    )";
    $sql_so_ck = " and (
        (so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1)))
    )";
    $sql_br_ck = " and (
        (br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
        or
        (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1)))
    )";
} else {
    $sql_pd_ck = " and (
        (pd_preferred_buyer_location='any')
        or
        (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
    )";
    $sql_so_ck = " and (
        (so_preferred_buyer_location='any')
        or
        (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
    )";
    $sql_br_ck = " and (
        (br_preferred_supplier_location='any')
        or
        (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
    )";
}

if (isset($_COOKIE['loc_id']) && $_COOKIE['loc_id'] != "") {
    $sql_prd = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24";
} else {
    $sql_prd = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1' and pd_image!=''" . $sql_pd_ck . " order by usr_id desc LIMIT 24";
}

if ($res_prd = mysqli_query($con, $sql_prd)) {
    $total_rows = mysqli_num_rows($res_prd);
    $re_rows = $total_rows % 1;
    $indx1 = 1;
?>
<ul id="products-suppliers">
    <?php while ($row_prd = mysqli_fetch_object($res_prd)) {
        if ($indx1 > 30) break;
        if ($indx1 % 3 == 1) echo '<li class="main-slick-wrapper-item">';
        
        $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
        $sql_icon = "select sip.mst_icon, sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_bprof->bnsprof_id;
        $get_icon = mysqli_query($con, $sql_icon);
    ?>
    <div class="inner-main-wrapper">
        <a class="slick-product-wrapper" href="company/products.php?c=<?php echo rand(1000, 9999) . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $row_prd->pd_subcat_id; ?>#<?php echo $row_prd->pd_id; ?>" target="_blank" style="text-decoration:none;color:#000">
            <div class="slick-product-image">
                <?php if ($row_prd->pd_image && file_exists('upload/myproduct/thumb/' . $row_prd->pd_image)): ?>
                    <img alt="<?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?>" src="<?php echo 'upload/myproduct/thumb/'.$row_prd->pd_image; ?>" class="img-responsive black" title="<?php echo ucwords($row_prd->pd_title); ?>">
                <?php endif; ?>
                <?php 
                $logo = !empty($row_prd->pd_imagelogo) ? explode(',', $row_prd->pd_imagelogo) : [];
                if (!empty($logo[0]) && file_exists('upload/myproduct/' . $logo[0])): 
                ?>
                <img alt="<?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?>" src="upload/myproduct/<?php echo $logo[0]; ?>" style="position: absolute;bottom: 59px; left: 6px; width: 60px; height: 60px;" class="img-responsive" title="<?php echo ucwords($row_prd->pd_title); ?>">
                <?php endif; ?>
            </div>
            <div class="matterbox">
                <div class="icon-pic-with-heading">
                    <div class="icon_pic">
                        <?php if (mysqli_num_rows($get_icon) > 0) {
                            $title = 'Junior';
                            $icon = mysqli_fetch_array($get_icon);
                            if (strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
                                $title = 'Senior';
                            } elseif (strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
                                $title = 'Sponsor';
                            }
                        ?>
                            <img alt="" src="admin/images/<?php echo $icon['mst_icon']; ?>" class="img-responsive" title="<?php echo strtoupper($title); ?>" style="width:18px;height:15px">
                        <?php } else { ?>
                            <img alt="" src="images/slider-icon01.jpg" class="img-responsive" title="JUNIOR">
                        <?php } ?>
                    </div>
                    <div class="ihover-wrapper">
                        <h3 class="ihoves">
                            <?php 
                            if (isset($row_prd->pd_title) && strlen($row_prd->pd_title) > 20) {
                                echo substr($row_prd->pd_title, 0, 20) . '...';
                            } else {
                                echo $row_prd->pd_title ?? '';
                            }
                            ?>
                        </h3>
                        <div class="auction_hover">
                            <p><?php echo ucwords($row_prd->pd_title ?? ''); ?></p>
                        </div>
                    </div>
                </div>
                <div class="rightmatter">
                    <p><span class="nam"><?php echo get_country_name($row_prd->country ?? 0); ?></span><br></p>
                    <p>MOQ: <span class="nam"><?php echo $row_prd->pd_min_order_qty ?? '0'; ?><?php echo $row_prd->mu_name ?? ''; ?></span><br></p>
                    <p><?php echo $row_prd->cn_currency ?? ''; ?><span style="font-size:11px!important" class="nam"><?php echo $row_prd->pd_fob_price ?? '0'; ?> /</span><?php echo $row_prd->mu_name ?? ''; ?></p>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>
            </div>
        </a>
    </div>
    <?php
        if ($indx1 % 3 == 0) echo '</li>';
        $indx1++;
    } // end while
    if ($re_rows == 1) echo '</li>';
    ?>
</ul>
<?php
} // end if ($res_prd)
?>    
 
 
 
                    </div>
                  </div>
                </div>
                <div class="learnmores">
                  <p><a href=dir.php#main_cat target=_blank>شاهد كل تصنيفات التجارة  <span>  >></span></a>
                </div>
              </div>
            </div>
            <div class="demobox oyee">
              <div class="countrubox_top">
                <div class="countrubox_heading" title="   &#1593;&#1585;&#1608;&#1590; &#1576;&#1610;&#1593; - &#1582;&#1575;&#1589;&#1577; - &#1605;&#1581;&#1583;&#1608;&#1583;&#1577;  ">
                    
                    
                  <h2><a href="post-sell-offer.php"><span> أنشـر </span>عروض خاصة +</a></h2>
                  
                  
                </div>
                <div class="list-rights"title="   &#1571;&#1606;&#1588;&#1585; - &#1593;&#1585;&#1608;&#1590; &#1576;&#1610;&#1593; - &#1582;&#1575;&#1589;&#1577; - &#1605;&#1581;&#1583;&#1608;&#1583;&#1577;  ">
                    
                     <h2> عروض بيع <a href="sale-offers.php"><span>خاصة  </span></a></h2>
                  
                  
                  
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
                      if ($res_so->num_rows > 0)    
                      {
                          $re_rows = $total_rows % 1;
                      ?>
                    <ul id="temporary-slides">
                      <?php
                        while ($row_so = mysqli_fetch_object($res_so))
                        {
                        
                        ?>                
                      <?php if ($indx1 % 2 == 1)
                        { ?>  
                      <li class="main-slick-wrapper-item">
                        <?php } ?>
                        <?php
                          $row_bprof = null;
                          
if (isset($row_prd->pd_uid) && !empty($row_prd->pd_uid)) {
    $result = mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $row_bprof = mysqli_fetch_object($result);
    }
}
                          $sql_icon = "select sip.mst_icon ,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_so->bnsprof_id;
                          $get_icon = mysqli_query($con,$sql_icon) or die(mysql_error());
                          ?>
                        <a class="slick-product-wrapper"
                          href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5($row_so->so_id); ?>"
                          target="_blank" style="text-decoration:none;color:#000">
                          <div class="slick-product-image">
                            <img class="black"
                              alt="<?php echo ucwords(substr($row_so->so_service, 0, 25)); ?>"
                              src="upload/sale_offer/thumb/<?php echo $row_so->so_pic; ?>"
                              class="img-responsive black"
                              title="<?php echo ucwords($row_so->so_service); ?>">
                          </div>
                          <div class="matterbox">
                            <div class="icon-pic-with-heading">
                              <div class="icon_pic"><?php if (mysqli_num_rows($get_icon) > 0)
                                {
                                    $title = 'Junior';
                                    $icon = mysqli_fetch_array($get_icon);
                                    if (strpos(strtolower($icon['mst_name']) , 'senior') !== false || strpos(strtolower($icon['mst_name']) , 'senier') !== false)
                                    {
                                        $title = 'Senior';
                                    }
                                    else if (strpos(strtolower($icon['mst_name']) , 'sponsor') !== false || strpos(strtolower($icon['mst_name']) , 'sponser') !== false)
                                    {
                                        $title = 'Sponsor';
                                    }
                                    $title = 'Junior';
                                    $icon = mysqli_fetch_array($get_icon);
                                ?><img alt=""
                                src="admin/images/<?php echo ($icon['mst_icon'] != '') ? $icon['mst_icon'] : 'slider-icon01.jpg'; ?>"
                                class="img-responsive"
                                title="<?php echo strtoupper($title); ?>"
                                style="width:18px;height:15px"><?php
                                }
                                else
                                { ?>
                                <img alt="" src="images/slider-icon01.jpg"
                                  class="img-responsive"
                                  title="JUNIOR"><?php
                                  } ?>
                              </div>
                              <div class="ihover-wrapper">
                                <h3 class="ihoves"><?php echo ucwords(substr($row_so->so_service, 0, 20)); ?><?php if (strlen($row_so->so_service) > 21)
                                  { ?>...<?php
                                  } ?></h3>
                                <div class="auction_hover">
                                  <p><?php echo ucwords($row_so->so_service); ?></p>
                                </div>
                              </div>
                            </div>
                            <div class="rightmatter">
                              <p class="color-red"><?php
                                //  $sql_cat="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row_so->so_pc_id."')";
                                $sql_cat = "select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id=(select pc_parent_id from product_category where pc_id='" . $row_so->so_pc_id . "'))";
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
                      }
                      else
                      {
                      ?><?php
                      }
                      ?>
         
                  </div>
                  <div class="learnmores">
                    <p><a href="sale-offers.php" target="_blank">كل العروض الخاصة   <span>  >></span></a>
                  </div>
                </div>
              </div>
            </div>
            <div class="center-top">
              <?php
                $banner = GetHomeBanner('middle');
                if ($banner != "")
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
              <?php
                PrintLeadingProduct();
                PrintHPProduct();
                ?>
              <?php
                $sqllogo = "select * from supplier_logo where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
                $rslogo = mysqli_query($con, $sqllogo);
                if (mysqli_num_rows($rslogo) > 0)
                {
                ?>
              <div class="demobox">
                <div class="countrubox_top4">
                  <div class="countrubox_heading">
                    <div class="countryheadingboxleft"title="  &#1571;&#1607;&#1605; &#1575;&#1604;&#1588;&#1585;&#1603;&#1575;&#1578; &#1575;&#1604;&#1585;&#1575;&#1574;&#1583;&#1607; &#1601;&#1609; &#1605;&#1589;&#1585; &#1608;&#1575;&#1604;&#1605;&#1606;&#1591;&#1602;&#1577; - &#1575;&#1604;&#1578;&#1609; &#1578;&#1593;&#1605;&#1604; &#1576;&#1582;&#1591;&#1577; - &#1576;&#1585;&#1608;&#1605;&#1608; &#1571;&#1593;&#1605;&#1575;&#1604; ">
                        
                        
                   
                      <h2><a href="membership_plans.php"><span class="black-color">إشترك الآن </span>كشركة رائدة</a></h2>
                      
                    </div>
                  </div>
                  <div class="list-rights"title="  &#1571;&#1590;&#1601; &#1588;&#1585;&#1603;&#1578;&#1603; &#1575;&#1604;&#1609; - &#1582;&#1583;&#1605;&#1577; &#1576;&#1585;&#1608;&#1605;&#1608; &#1571;&#1593;&#1605;&#1575;&#1604; - &#1608;&#1578;&#1593;&#1585;&#1601; &#1593;&#1604;&#1609; &#1575;&#1604;&#1588;&#1585;&#1608;&#1591; ">
                      
                      
                       <h3><a href="dir.php"><span class="black-color">PROMO فى خطة </span> </a></h3>
                    
                    
                  </div>
                  <div class="clear"></div>
                </div>
                <div class="white_bg hello1">
                  <div class="welcome_desc">
                    <div class="course_demo">
                      <div id="sponsors">
                        <?php
                          while ($rowlogo = mysqli_fetch_object($rslogo))
                          {
                              $adv_img = $rowlogo->adv_img;
                              $logopath = "upload/supplier_logo/" . $adv_img;
                              $adv_link = $rowlogo->adv_link;
                          ?>
                        <div><a href="<?php echo $adv_link; ?>"target=_blank><img alt=""src="<?php echo $logopath; ?>"class="img-responsive"></a></div>
                        <?php
                          }
                          /*else
                                                       {
                                                         echo '';
                                                       }*/
                          ?>
                      </div>
                    </div>
                  </div>
                </div>
  
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
                  $banner = GetHomeBanner('bottom', $strconutnry);
                  if ($banner != "")
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
            <?php
              } ?> 
          </div>
        </div>
        
        <?php if (preg_match('/mobile|android|iphone|ipad/i', $_SERVER['HTTP_USER_AGENT'])): ?>
    <div style="clear:both; width:100%;"></div>
<?php endif; ?>
  
  
  
  
  
  
  
  
  
        
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
            if ($cnt_br > 0)
            {
            ?>
          <div class="leftleads">
              
            <h2><a href="buyleads.php"title="&#1591;&#1604;&#1576;&#1575;&#1578; &#1588;&#1585;&#1575;&#1569; - &#1605;&#1606; &#1605;&#1588;&#1578;&#1585;&#1610;&#1610;&#1606; - &#1581;&#1602;&#1610;&#1602;&#1610;&#1610;&#1606;"style="font-weight:700"> طلبات شراء اليوم  <i class="fa fa-caret-right"></i></a></h2>
            
          </div>
          <div class="bgc1 brd bx2">
            <?php
              $x = (int)$cnt_br1;
              $c = (int)0;
              while ($x != 0)
              {
                  $x = (int)$x / 10;
                  $c = (int)$c + 1;
              }
              $str = "";
              for ($i = $c;$i <= 4;$i++)
              {
                  $str = $str . '0';
              }
              //echo $str;
              
              ?>
            <div class="rightnumber"><span class="number-count"><?php echo $str . $cnt_br1; ?></span><span class="off tic1"><?php echo $cnt_br1; ?></span></div>
            <script>
              $(function(){
              var val=$('.number-count').html().split('');
              // console.log(val);
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
                while ($row_br = mysqli_fetch_object($res_br))
                {
                ?>
              <div class="popular-post-grid">
                <h3><a href="buyleads-details.php?id=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_br->br_id); ?>"target="_blank"><?php echo ucwords(stripslashes($row_br->br_pd_name)); ?></a></h3>
                <div class="tendersbox">
                  <div class="verifiedbox">
                    <div class="cover"><img alt=""src="images/tick.png"> مشترى متحقق منه</div>
                    <div class="date"><?php if ($row_br->br_estimate_qty != '0' && $row_br->br_estimate_qty != '')
                      { ?><b> الكمية </b><?php echo $row_br->br_estimate_qty; ?><?php echo measurement_unit($row_br->br_estimate_qty_unit); ?>  <?php
                      } ?></div>
                      
                      
                      
                      
                      
                      
                  </div>
                  <div class="flagbox">
                    <?php
                      $contyname = get_country_name($row_br->country);
                      $cntryflag = get_country_flag($row_br->country);
                      if ($cntryflag != "")
                      {
                          $flag2show = '<img src="images/country_flag/' . $cntryflag . '" alt="">';
                      }
                      ?>
                    <ul>
                      <li style="height:auto"><a href="#"><?php echo $contyname . " " . $flag2show; ?></a>
                    </ul>
                    <div class="date"><span><?php
                      if ($row_br->br_preferred_supplier_location == '')
                      {
                          echo get_country_name($row_br->country);
                      }
                      else
                      {
                          if ($row_br->br_preferred_supplier_location == 'any')
                          {
                              echo "Anywhere";
                          }
                          else if ($row_br->br_preferred_supplier_location == 'abroad')
                          {
                              echo "Foreign";
                          }
                          else if ($row_br->br_preferred_supplier_location == 'domestic')
                          {
                              echo get_country_name($row_br->country);
                          }
                          else if ($row_br->br_preferred_supplier_location == 'my_city' && $row_br->bnsprof_city != '0')
                          {
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
                <p><a href="buyleads.php" target="_blank"> << <span >كل طلبات الشراء</span></a>
              </div>
            </div>
            <div class="clear"></div>
          </div>
          <br>
          <?php
            } ?>
          <?php
            if ($cn_id != "")
            {
                $strtender = " AND user.country='$cn_id'";
            }
            else
            {
                $strtender = "";
            }
            $sqltender = "select tnd_id,tnd_heading,tnd_due_date,tnd_preferred_location,country from tender,user where tnd_status='1' and tnd_approval_status = 1 and tender.tnd_usr_id=user.usr_id $strtender AND tender.tnd_due_date >= curdate() order by tnd_publish_date DESC LIMIT 4";
            //echo $sqltender;
            $rstender = mysqli_query($con, $sqltender) or die("Error" . mysqli_erorr());
            if ($cn_id != "")
            {
                $strauction = " AND user.country='$cn_id'";
            }
            else
            {
                $strauction = "";
            }
            $sqltender = "select auc_id,auc_heading,auc_due_date,auc_preferred_location,country from auction,user where auc_status='1' and auction.auc_usr_id=user.usr_id $strauction  AND auction.auc_due_date >= curdate() and auc_approval_status =1 order by auc_publish_date DESC LIMIT 5";
            $rsauction = mysqli_query($con, $sqltender) or die("Error" . mysqli_erorr());
            ?>
          <?php if (mysqli_num_rows($rstender) || mysqli_num_rows($rsauction))
            { ?>
          <div class="sap_tabs second-sap-tabs">
            <div id="horizontalTab" style="display:block;width:100%;margin:0;">
              <ul class="resp-tabs-list">
                <?php if (mysqli_num_rows($rstender))
                  { ?>
                <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><a href="post-tender.php"><span><b>مناقصات</b></span></a><?php
                  } ?>
                  <?php if (mysqli_num_rows($rsauction))
                    { ?>
                <li class="resp-tab-item aria-controls=tab_item-1" role="tab"><a href="post-auction.php"><span><b>Auctions</b></span></a></li>
                <?php
                  } ?>
                <div class="clear"></div>
              </ul>
              <div class="resp-tabs-container">
                <div class="resp-tab-content tab-1" aria-labelledby="tab_item-0">
                  <?php
                    if (mysqli_num_rows($rstender))
                    {
                        echo '<ul class="tab_img">';
                        while ($rowtender = mysqli_fetch_object($rstender))
                        {
                            $tnd_heading = $rowtender->tnd_heading;
                            $tnd_due_date = $rowtender->tnd_due_date;
                    ?>
                  <li>
                    <div class="popular-post-grids">
                      <div class="popular-post-grid">
                        <h3><a href="tender-details.php?id=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($rowtender->tnd_id); ?>"target="_blank"><?php echo $tnd_heading; ?></a></h3>
                        <div class="tendersbox">
                          <div class="verifiedbox">
                            <div class="cover"><img alt=""src="images/tick.png"> مشترى متحقق منه </div>
                            <div class="date"><b>Due Date: </b><?php echo $tnd_due_date; ?></div>
                          </div>
                          <div class="flagbox">
                            <?php
                              $contyname = get_country_name($rowtender->country);
                              $cntryflag = get_country_flag($rowtender->country);
                              if ($cntryflag != "")
                              {
                                  $flag2show = '<img src="images/country_flag/' . $cntryflag . '" alt="">';
                              }
                              ?>
                            <ul>
                              <li><a href="#"><?php echo $contyname . " " . $flag2show; ?></a>
                            </ul>
                            <div class="date"><span><?php
                              if ($rowtender->tnd_preferred_location == '')
                              {
                                  echo get_country_name($rowtender->country);
                              }
                              else
                              {
                                  if ($rowtender->tnd_preferred_location == 'any')
                                  {
                                      echo "Anywhere";
                                  }
                                  else if ($rowtender->tnd_preferred_location == 'abroad')
                                  {
                                      echo "Foreign";
                                  }
                                  else if ($rowtender->tnd_preferred_location == 'domestic')
                                  {
                                      echo get_country_name($rowtender->country);
                                  }
                                  else if ($rowtender->tnd_preferred_location == 'my_city' && $rowtender->country != '0')
                                  {
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
                  <?php if (mysqli_num_rows($rstender) || mysqli_num_rows($rsauction))
                    { ?>
                  <div class="learnmore">
                    <p><a href="#">View all <?php if (mysqli_num_rows($rstender))
                      { ?><a href="tenders.php">Tenders</a> / <?php
                      } ?><?php if (mysqli_num_rows($rsauction))
                      { ?><a href="auctions.php">Auctions</a></a><?php
                      } ?>
                  </div>
                  <div class="tabbotton"><a href="#"><span>Publish</span> <a href="post-tender.php">Tender</a>/</a> <a href="post-auction.php">Auction</a> <span>FREE</span></div>
                  <?php
                    } ?>
                </div>
                <div class="resp-tab-content tab-1"aria-labelledby="tab_item-1">
                  <ul class="tab_img">
                    <?php
                      if (mysqli_num_rows($rsauction))
                      {
                          echo '<ul class="tab_img">';
                          while ($rowaution = mysqli_fetch_object($rsauction))
                          {
                              $tnd_heading = $rowaution->auc_heading;
                              $auc_due_date = $rowaution->auc_due_date;
                      ?>
                    <li>
                      <div class="popular-post-grids">
                        <div class="popular-post-grid">
                          <h3><a href="auction-details.php?id=<?php echo rand(1000, 9999) . md5($rowaution->auc_id); ?>"target="_blank"><?php echo $tnd_heading; ?></a></h3>
                          <div class="tendersbox">
                            <div class="verifiedbox">
                              <div class="cover"><img alt=""src="images/tick.png"> مشترى متحقق منه</div>
                              <div class="date"><b>Due Date: </b><?php echo $auc_due_date; ?></div>
                            </div>
                            <div class="flagbox">
                              <?php
                                $contyname = get_country_name($rowaution->country);
                                $cntryflag = get_country_flag($rowaution->country);
                                if ($cntryflag != "")
                                {
                                    $flag2show = '<img src="images/country_flag/' . $cntryflag . '" alt="">';
                                }
                                ?>
                              <ul>
                                <li><a href="#"><?php echo $contyname . " " . $flag2show; ?></a>
                              </ul>
                              <div class="date"><span><?php
                                if ($rowaution->auc_preferred_location == '')
                                {
                                    echo get_country_name($rowaution->country);
                                }
                                else
                                {
                                    if ($rowaution->auc_preferred_location == 'any')
                                    {
                                        echo "Anywhere";
                                    }
                                    else if ($rowaution->auc_preferred_location == 'abroad')
                                    {
                                        echo "Foreign";
                                    }
                                    else if ($rowaution->auc_preferred_location == 'domestic')
                                    {
                                        echo get_country_name($rowaution->country);
                                    }
                                    else if ($rowaution->auc_preferred_location == 'my_city' && $rowaution->country != '0')
                                    {
                                        echo get_city_name($rowaution->country);
                                    }
                                }
                                ?></span></div>
                            </div>
                            
                            
 //================================== part 2 ================                           
                            
                            
                            
                            
                            
                            
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
          <div id="horizontalTab1" style="display:block; width:100%;margin:0">
            <ul class="resp-tabs-list">
              <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"title=" &#1604;&#1604;&#1588;&#1585;&#1575;&#1569; ">
                <span>
                  <h5>للشــراء</h5>
                </span>
              <li class="resp-tab-item" aria-controls="tab_item-1" role="tab"title=" &#1604;&#1604;&#1576;&#1610;&#1593; ">
                <span>
                  <h5>للبيــــع </h5>
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
                        <div class="post-img"><a href="#"><img alt=""
                          src="images/email-icon.jpg"
                          class="img-responsive"></a>
                        </div>
                        <div class="post-text">
                          <a href="post-buy-req.php" class="pp-title">أنشر طلبات شراء +</a>
                          <p>وتلقى تسعييرات من موردين وأختار مايناسبك
                        </div>
                        <div class="clear"></div>
                      </div>
                    </div>
                  <li>
                    <div class="popular-post-grids">
                      <div class="popular-post-grid">
                        <div class="post-img"><a href="#"><img alt=""
                          src="images/search.jpg"
                          class="img-responsive"></a>
                        </div>
                        <div class="post-text">
                          <a href="search_adv.php" class="pp-title">إبحث عن أى شىء لشركتك تجده فورا</a>
                          <p>وإرسل للموردين طلباتك يستجيبون لك فورا
                        </div>
                        <div class="clear"></div>
                      </div>
                    </div>
                  <li>
                    <div class="popular-post-grids">
                      <div class="popular-post-grid">
                        <div class="post-img"><a href="#"><img alt="" src="images/bell.jpg"
                          class="img-responsive"></a>
                        </div>
                        <div class="post-text">
                          <a href="manage-selloffer-alert.php" class="pp-title">إشترك بالإشعارات التجارية +</a>
                          <p>وتلقى كل المنتجات ذات الصلة فى بريدك
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
                        <div class="post-img"><a href="#"><img alt=""
                          src="images/boxrect.png"
                          class="img-responsive"></a>
                        </div>
                        <div class="post-text">
                          <a href="product-sel-cat.php" class="pp-title">إعرض منتجاتك / خدماتك التجارية +</a>
                          <p>وتلقى إستجابات من مشتريين محليين وعالميين
                        </div>
                        <div class="clear"></div>
                      </div>
                    </div>
                  <li>
                    <div class="popular-post-grids">
                      <div class="popular-post-grid">
                        <div class="post-img"><a href="#"><img alt=""
                          src="images/criclearrow.png"
                          class="img-responsive"></a>
                        </div>
                        <div class="post-text">
                          <a href="product-sel-cat.php" class="pp-title">إنشىء صفحة منتجاتك وخدماتك</a>
                          <p>وسوق لشركتك أونلاين محليا وعالميا
                        </div>
                        <div class="clear"></div>
                      </div>
                    </div>
                  <li>
                    <div class="popular-post-grids">
                      <div class="popular-post-grid">
                        <div class="post-img"><a href="#"><img alt="" src="images/bell.jpg"
                          class="img-responsive"></a>
                        </div>
                        <div class="post-text">
                          <a href="manage-buylead-alert.php" class="pp-title">إشترك فى إشعارات طلبات الشراء</a>
                          <p>وتلقى فى بريدك طلبات شراء من مصر والحارج 
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
              <div class="scontentbox"title=" " >
                <h2>Sponsor <span> عضـوية </span></h2>
              </div>
              <div class="clear"></div>
            </div>
            <ul>
              <li>> <a href="#">دخول حصرى على بيانات طلبات الشراء </a>
              <li>> <a href="#">عرض منتجاتك الأولى فى الترتيب </a>
              <li>> <a href="#">ويب سايت مصغر لعرض تجارة العضو </a>
              <li>> <a href="#">رابط خاص لعرض موقعك الأصلى</a>
              <li>> <a href="#">علامة خاصة بجانب منتجاتك كمورد رائد لها</a>
              <li>> <a href="#">خدمة تسجيل وإدخال مجانية لمنتجات العضو</a>
              <li>> <a href="#">خدمة تغطية خاصة للأحداث التجارية للعضو</a></li>
              <p style="padding-right:15px"><a href="membership_plans.php">تعـلم المزيـد <span>> ></span></a>
            </ul>
            <h3><a href="membership_plans.php">أحصل على كل مزايا المنصة</a></h3>
          </div>
          <?php
            $sql_testi = "select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
            $res_testi = mysqli_query($con, $sql_testi);
            if (mysqli_num_rows($res_testi) > 0)
            {
            ?>
          <div class="testimonialbox desktop-hide">
            <div class="testimonialbg">
              <h2>Buyer Speaks</h2>
              <?php while ($row_testi = mysqli_fetch_object($res_testi))
                { ?>
              <div class="arrow_box">
                <p><i><span>“</span><?php echo stripslashes($row_testi->testi_details); ?>
                  <span class="spacecomma">&#65533;?</span></i>
              </div>
              <div class="clear"></div>
              <div class="testiwriter">
                <div class="pic1"><img alt=""
                  src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>">
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
            if (mysqli_num_rows($res_testi) > 0)
            {
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
              <p><i style="font-weight:700"><?php echo stripslashes($row_testi->testi_details); ?></i><br><br><a
                href="membership_plans.php" class="fright" style="padding-right:15px;">Learn
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
              if (mysqli_num_rows($res_testi3) > 0)
              {
              ?>
            <div class="testimonialbox">
              <div class="testimonialbg">
                <h2>Supplier Speaks</h2>
                <?php
                  while ($row_testi3 = mysqli_fetch_object($res_testi3))
                  {
                  ?>
                <div class="arrow_box">
                  <p>
                    <i><span>“</span><?php echo stripslashes($row_testi3->testi_details); ?>
                    <span class="spacecomma">&#65533;?</span></i>
                </div>
                <div class="clear"></div>
                <div class="testiwriter">
                  <div class="pic1"><img alt=""
                    src="upload/testimonial_img/<?php echo $row_testi3->testi_image; ?>">
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
              if (mysqli_num_rows($res_testi) > 0)
              {
              ?>
            <div class="testimonialbox">
              <div class="testimonialbg">
                <h2>Buyer Speaks</h2>
                <?php while ($row_testi = mysqli_fetch_object($res_testi))
                  { ?>
                <div class="arrow_box">
                  <p>
                    <i><span>“</span><?php echo stripslashes($row_testi->testi_details); ?>
                    <span class="spacecomma">&#65533;?</span></i>
                </div>
                <div class="clear"></div>
                <div class="testiwriter">
                  <div class="pic1"><img alt=""
                    src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>">
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
  <div class="clear"></div>
  </div>
  </div><?php include "includes/footer.php"; ?>
  <style>
    .page-header-col2-intro {
    border-left: 2px solid #237abf;
    }
  </style>
  <script>
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
  <script>
  $(document).ready(function(){
    $('#business-services,#EgyptMART-leading,#sponsors,#temporary-slides,#products-suppliers').slick({
          centerMode: true,
          centerPadding: '30px',
          slidesToShow: 5,
          autoplay:false,
          autoplaySpeed:3000,
          responsive: [
              {
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
    function PrintHPProduct()
    {
        global $con;
    
        if (isset($_COOKIE['loc_id']))
        {
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
        }
        else
        {
            $sql_pd_ck = " and (
                                        (pd_preferred_buyer_location='any')
                                        or
                                        (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
                                        )";
            $sql_so_ck = " and (
                                        (so_preferred_buyer_location='any')
                                        or
                                       (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
                                        )";
            $sql_br_ck = " and (
                                        (br_preferred_supplier_location='any')
                                        or
                                        (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
                                        )";
        }
       if (isset($_COOKIE['loc_id']) && $_COOKIE['loc_id'] != "")
        {
            $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_lp_slider = 1  and pd_image!=''" . $sql_pd_ck . " order by rand()";
        }
        else
        {
            $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'  and pd_lp_slider = 1  and pd_image!=''" . $sql_pd_ck . " order by rand()";
        }
    
        //$sqlleading = "select * from prodservice_slider,country where  slider_supplier_country=cn_id and adv_status='1' and adv_type='2' $strconutnry";
        //echo $sqlleading;
        
        /**
         $sql_pd_ck = " and (
         (pd_preferred_buyer_location='any')
         or
         (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
         )";
         $sqlleading =  "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and  pd_image!='' and  pd_lp_slider = 1 " . $sql_pd_ck . " " ; //pd_status = 1 and
         // echo $sqlleading ;
         *
         */
        $rsleading = mysqli_query($con, $sqlleading);
        $totalbaneer = mysqli_num_rows($rsleading);
        // echo $totalbaneer ;
        $rembaner = $totalbaneer % 2;
        if ($totalbaneer > 0)
        { ?>                                        
  <div class="demobox">
    <div class="countrubox_top3">
      <div class="countrubox_heading">
        <div class="mainflagbox">
          <div class="membershipicon2"><a href="#"><img alt=""src="images/membership_icon03.png"></a></div>
        </div>
        <div class="countryheadingboxleft" title=" &#1571;&#1607;&#1605; &#1605;&#1602;&#1583;&#1605;&#1608; - &#1575;&#1604;&#1582;&#1583;&#1605;&#1575;&#1578; &#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1610;&#1577; - &#1601;&#1609; &#1605;&#1606;&#1589;&#1577; &#1587;&#1608;&#1602; &#1605;&#1589;&#1585; &#1593;&#1604;&#1609; &#1575;&#1604;&#1575;&#1606;&#1578;&#1585;&#1606;&#1578; ">
            
       
        <h2><a href="product-sel-cat.php" target="_blank"><span>أنشر  </span> خدمات +</a></h2> 


          
        </div>
      </div>
      <div class="list-rights"title=" &#1571;&#1606;&#1588;&#1585; - &#1582;&#1583;&#1605;&#1575;&#1578; &#1578;&#1580;&#1575;&#1585;&#1610;&#1577;  ">
          
        
           <h3><a href="sign-in.php"><span class="black-color"> </span> خدمات تجارية </a></h3>
        
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
                $ccc = 1;
                while ($rowleading = mysqli_fetch_object($rsleading))
                {
                   if($indx > 30) {break;}
                    $pd_id = $rowleading->pd_id;
                    $pd_image = $rowleading->pd_image;
                    $pd_title = $rowleading->pd_title;
                    $adv_icon = '';
                
                    if ($indx % 2 == 1)
                    {
                        echo '<li>';
                    }
                    $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $rowleading->pd_uid . "' limit 1"));
                    $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_bprof->bnsprof_id;
                ?>
              <div class="slick-wrapper">
                <a class="slick-product-wrapper" href="company/products.php?c=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $rowleading->pd_subcat_id; ?>#<?php echo $rowleading->pd_id; ?>" target=_blank>
                  <div id="bs" class="demobox">
                    <div class="slick-product-image">
                    <?php 
                      $product_img = $rowleading->pd_image ? $rowleading->pd_image : '';
                    ?>
                        <?php if( $product_img && file_exists( 'upload/myproduct/thumb/' . $product_img ) ): ?>
                      <img alt="" src="<?php echo 'upload/myproduct/thumb/'.$product_img; ?>" class="black" style="max-width:115px" title="<?php echo ucwords($rowleading->pd_title); ?>">
                        <?php endif; ?>
                    </div>
                    <div class="matterbox">
                      <div class="icon-pic-with-heading">

                      <?php if($adv_icon && file_exists('image/'.$adv_icon)): ?>
                        <div class="icon_pic"><img alt=""src="images/<?php echo $adv_icon; ?>"class="img-responsive" width="18"></div>
                      <?php endif; ?>

                        <div class="ihover-wrapper">
                          <h3 class="ihoves">
                            <?php if (strlen($pd_title) > 20)
                              {
                                  echo substr($pd_title, 0, 20) . '...';
                              }
                              else
                              {
                                  echo $pd_title;
                              } ?>
                          </h3>
                          <div class="auction_hover">
                            <p><?php echo $pd_title; ?></p>
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
                  if ($indx % 2 == 0)
                  {
                      echo '</div>';
                  }
                  $indx++;
                  }
                  if ($rembaner == 1)
                  {
                  echo '</div>';
                  }
                  ?>
              </div>
            </div>
   
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php
    }
    
    }
    function PrintLeadingProduct()
    {
    global $con;
    ?>
  <div class="booking"title=" &#1590;&#1593; &#1607;&#1606;&#1575; - &#1573;&#1593;&#1604;&#1575;&#1606;&#1575;&#1578; &#1575;&#1604;&#1605;&#1587;&#1575;&#1581;&#1575;&#1578; &#1575;&#1604;&#1582;&#1575;&#1589;&#1577; &#1576;&#1588;&#1585;&#1603;&#1578;&#1603; - &#1604;&#1604;&#1593;&#1585;&#1590; &#1593;&#1604;&#1609; &#1571;&#1607;&#1605; 10.000 &#1588;&#1585;&#1603;&#1577;"><a href="advertise-with-us.php"> Advertise Here Now for <span>FREE</span>
    Kindly Contact Advertisements Team </a>
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
          if ($totalvideo > 0)
          {
              while ($row_video = mysqli_fetch_object($resvideo))
              {
                  $cv_video_link = $row_video->adv_link;
                  $adv_redirect = $row_video->adv_redirect;
                  $chklink = explode("://", $cv_video_link);
                  if ($chklink[0] == "http" || $chklink[0] == "https")
                  {
                      preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $cv_video_link, $matches);
                      $id = $matches[1];
                      $width = '100%';
                      $height = '181';
                      $iframe2show = '<iframe class="hidden-xs" width="100%" height="181"
                                                                       src="https://www.youtube.com/embed/' . $id . '" frameborder="0"
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
          <?php echo $iframe2show; ?>
          <div class="iframebox">
            <h2><i class="fa fa-play"></i>
              <a href="<?php echo $adv_redirect; ?>"
                target="_blank"><?php echo substr(($bnsprof_compname) , 0, 22) . ".." ?></a>
            </h2>
            <p><?php echo substr(($bnsprof_address1) , 0, 30) . ".." ?>
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
    <a href="membership_plans.php" target=_>
      <div class="showcase">Products Showcase</div>
    </a>
    <p>Distribute in Your City

  </div>
  <?php
    if (isset($_COOKIE['loc_id']))
    {
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
    }
    else
    {
        $sql_pd_ck = " and (
                                    (pd_preferred_buyer_location='any')
                                    or
                                    (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
                                    )";
        $sql_so_ck = " and (
                                    (so_preferred_buyer_location='any')
                                    or
                                    (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
                                    )";
        $sql_br_ck = " and (
                                    (br_preferred_supplier_location='any')
                                    or
                                    (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . ($location_geo_country ?? '') . "')))
                                    )";
    }
    if (isset($_COOKIE['loc_id']) && $_COOKIE['loc_id'] != "")
    {
        $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_pck_dets = 1  and pd_image!=''" . $sql_pd_ck . " order by rand()";
    }
    else
    {
        $sqlleading = "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'  and pd_pck_dets = 1  and pd_image!=''" . $sql_pd_ck . " order by rand()";
    }
    
    //$sqlleading = "select * from prodservice_slider,country where  slider_supplier_country=cn_id and adv_status='1' and adv_type='2' $strconutnry";
    //echo $sqlleading;
    
    /**
     $sql_pd_ck = " and (
     (pd_preferred_buyer_location='any')
     or
     (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
     )";
     $sqlleading =  "select * from products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and  pd_image!='' and  pd_lp_slider = 1 " . $sql_pd_ck . " " ; //pd_status = 1 and
     // echo $sqlleading ;
     *
     */
    $rsleading = mysqli_query($con, $sqlleading);
    $totalbaneer = mysqli_num_rows($rsleading);
    // echo $totalbaneer ;
    $rembaner = $totalbaneer % 2;
    if ($totalbaneer > 0)
    { ?>
  <div class="demobox">
    <div class="booking mobile-hide"><a href="advertise-with-us.php"> Advertise Here Now for <span>FREE</span> Kindly Contact Advertisements Team </a></div>
    <div class="clear"></div>
    <div class="countrubox_top2">
      <div class="countrubox_heading">
        <div class="mainflagbox">
          <div class="membershipicon"><a href="membership_plans.php"><img alt=""src="images/membership-icon01.png"></a></div>
          <div class="membershipicon"><a href="membership_plans.php"><img alt=""src="images/membership_icon02.png"></a></div>
        </div>
        <div class="countryheadingboxleft" title="  &#1571;&#1607;&#1605; &#1605;&#1606;&#1578;&#1580;&#1575;&#1578; - &#1575;&#1604;&#1588;&#1585;&#1603;&#1575;&#1578; &#1575;&#1604;&#1585;&#1575;&#1574;&#1583;&#1577; - &#1605;&#1606;&#1589;&#1577; &#1587;&#1608;&#1602; &#1605;&#1589;&#1585; &#1593;&#1604;&#1609; &#1575;&#1604;&#1573;&#1606;&#1578;&#1585;&#1606;&#1578; ">
            
            
       
         <h2><a href="advertise-with-us.php" target="_blank"><span>أنشر </span>تجارتك +</a></h2> 
          
        </div>
      </div>
      <div class="list-rights"title="  &#1571;&#1606;&#1588;&#1585; &#1573;&#1593;&#1604;&#1575;&#1606;&#1575;&#1578; - &#1575;&#1604;&#1605;&#1608;&#1585;&#1583;&#1610;&#1606; &#1608;&#1585;&#1608;&#1575;&#1583; &#1575;&#1604;&#1589;&#1606;&#1575;&#1593;&#1577; &#1608;&#1575;&#1604;&#1578;&#1580;&#1575;&#1585;&#1577; - &#1605;&#1580;&#1575;&#1606;&#1575; ">
          
             <h3><a href="#"><span class="black-color">  شركات رائدة  </span> </a></h3>
       
        
        
      </div>
      <div class="clear"></div>
    </div>
    <div class="wrapper-container">
      <div class="white_bg">
        <div class="welcome_desc">
          <div class="course_demo">
            <ul id="EgyptMART-leading">
              <?php
                $indx = 1;
                while ($rowleading = mysqli_fetch_object($rsleading))
                {
                  if($indx > 30) {break;}
                    $pd_id = $rowleading->pd_id;
                    $pd_image = $rowleading->pd_image;
                    $pd_title = $rowleading->pd_title;
                    $adv_icon = '';
                
                    if ($indx % 3 == 1)
                    {
                        echo '<div>';
                    }
                    $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $rowleading->pd_uid . "' limit 1"));
                    $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = " . $row_bprof->bnsprof_id;
                ?>
              <a class="slick-product-wrapper" href="company/products.php?c=<?php echo rand(1000, 9999) /* was 10, if below 1000 gives empty page; by webxtor */ . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10, 99999) . $rowleading->pd_subcat_id; ?>#<?php echo $rowleading->pd_id; ?>" target=_blank>
                <div class="demobox">
                  
                  <?php if( $rowleading->pd_image && file_exists('upload/myproduct/thumb/' . $rowleading->pd_image) ): ?>
                    <div class="slick-product-image">
                    <img alt="" src="<?php echo 'upload/myproduct/thumb/'.$rowleading->pd_image; ?>" class="black" style="max-width:115px" title="<?php echo ucwords($rowleading->pd_title); ?>">
                    </div>
                  <?php endif; ?>
                  
                  <div class="matterbox">
                    <div class="icon-pic-with-heading">

                    <?php if($adv_icon): ?>
                      <div class="icon_pic"><img alt=""src="images/<?php echo $adv_icon; ?>"class="img-responsive" width="18"></div>
                    <?php endif; ?>

                      <div class="ihover-wrapper">
                        <h3 class="ihoves">
                          <?php echo ucwords(substr($pd_title, 0, 15)); ?><?php if (strlen($pd_title) > 15)
                            { ?>...<?php
                            } ?>
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
                if ($indx % 3 == 0)
                {
                    echo '</div>';
                }
                $indx++;
                }
                if ($rembaner == 1)
                {
                echo '</div>';
                }
                ?>
            </ul>
        
          </div>
        </div>
        <div class="clear" style="height:1px"></div>
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
for (var i=0; i<vidDefer.length; i++) {
if(vidDefer[i].getAttribute('data-src')) {
vidDefer[i].setAttribute('src',vidDefer[i].getAttribute('data-src'));
} }  }
window.onload = init;
</script>

<script>
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.mobile-click').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let parent = this.closest('.ptag');
                if (parent) {
                    parent.classList.toggle('open');
                }
            });
        });
    }
</script>

