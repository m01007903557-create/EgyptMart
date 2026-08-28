<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
<style>
body{
    font-family: 'Tajawal', sans-serif;
}
</style>
<!--<link href="css/style123.css" type="text/css" rel="stylesheet" / >-->
<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>
<?php include "css/custom.php"; ?>
<!--<link href="css/custom.css?ver=1.1" type="text/css" rel="stylesheet"/ >-->
<script type="text/javascript">
  function showmymenu() {
      $("#mn1").show();
  }
  function hidemymenu() {
      $("#mn1").hide();
  }
  function showLocMenu() {
      $("#changeLocation").show();
  }
  function hideLocMenu() {
      $("#changeLocation").hide();
  }
  function showbuymenu() {
      $("#buymnu").show();
  }
  function hidebuymenu() {
      $("#buymnu").hide();
  }
  function showsellmenu() {
      $("#sellmnu").show();
  }
  function hidesellmenu() {
      $("#sellmnu").hide();
  }
</script>
<script>
  function showsrchm() {
      $("#smnu").show();
  }
  function hidesrchm() {
      $("#smnu").hide();
  }
  function OutboundLink(type) {
      if (type == 'buy_lead') {
          $("#a1").html("Buy Leads");
      } else if (type == 'tender') {
          $("#a1").html("Tender");
      } else if (type == 'auction') {
          $("#a1").html("Auction");
      } else {
          $("#a1").html(type);
      }
      $("#rctyp").val(type);
      $("#smnu").hide();
  }
</script>
<script>
  function validsearch() {
      /*var keywords = document.getElementById('keywords');
      if (keywords.value == '' || keywords.value == null) {
          alert("Please enter a valid text to search.");
          return false;
      }*/
      $('.loading-text').removeClass('hide').addClass('show');
  }
  function gotFocus() {
      var keywords = $("input#keywords").val();
      if (keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search' || keywords == 'Enter Tender to search') {
          $("input#keywords").val('')
      }
  }
  function lostFocus() {
      var type = $("#keyword_type").val();
      var keywords = $("input#keywords").val();
      if (type == 'Products' && (keywords == '' || keywords == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search')) {
          $("input#keywords").val('Search Product');
      } else if (type == 'Buy Leads' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Supplier to search')) {
          $("input#keywords").val('Enter Buy Lead to search');
      } else if (type == 'Suppliers' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search')) {
          $("input#keywords").val('Enter Supplier to search');
      } else if (type == 'Tender' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Tender to search')) {
          $("input#keywords").val('Enter Tender to search');
      }
  }
  function setCountryLocation(id)
  {
      $.post("setCountryLocation.php", {loc_id: id}, function (data)
      {
          if (data != 0) {
              //    $("#cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
              location.reload();
          }
      });
  }
  function unsetCountryLocation() {
      $.post("unsetCountryLocation.php", function (data) {
          //    $("#cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
          location.reload();
      });
  }
  
  $(window).scroll(function () {
      var height = $(window).scrollTop();
  //alert(height);
      if (height > 150) {
          $('#topbar').addClass('fixed-position');
      } else {
  
          $('#topbar').removeClass('fixed-position');
      }
  });
  $(document).ready(function () {
      var viewportWidth;
      $(window).resize(function () {
          viewportWidth = $(window).width();
  
      });
  
  
  
      $('.mobile-click').click(function (e) {
          if ($(window).width() < 767) {
              e.preventDefault();
              $('.typography_3_colm').toggle();
          }
      });
  
  });
  $(window).load(function () {
  if (typeof(flexslider) !== 'undefined'){
      $('.flexslider').flexslider({
          animation: "slide",
          controlNav: "thumbnails"
      });
  }
  
  });
  
  
  
  
</script>
<script type="test/javascript">
  function showcontent(x){
  if(window.XMLHttpRequest) {
  xmlhttp = new XMLHttpRequest();
  } else {
  xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
  }
  xmlhttp.onreadystatechange = function() {
  if(xmlhttp.readyState == 1) {
  document.getElementById('content').innerHTML = "<img src='images/loadingif.gif' />";
  }
  if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
  document.getElementById('content').innerHTML = xmlhttp.responseText;
  }
  }
  xmlhttp.open('POST', x+'.html', true);
  xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
  xmlhttp.send(null);
  }
</script>
<?php
  /**
   * Created by PhpStorm.
   * User: Long
   * Date: 12/18/2015
   * Time: 11:49 PM
   */
  function getLocationInfoByIp1() {
      $client = @$_SERVER['HTTP_CLIENT_IP'];
      $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
      $remote = @$_SERVER['REMOTE_ADDR'];
      $result = array('country' => '', 'city' => '');
      if (filter_var($client, FILTER_VALIDATE_IP)) {
          $ip = $client;
      } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
          $ip = $forward;
      } else {
          $ip = $remote;
      }
      //$ip = "1.0.63.255";
      $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
      if ($ip_data && $ip_data->geoplugin_countryName != null) {
          $result['country'] = $ip_data->geoplugin_countryCode;
          $result['city'] = $ip_data->geoplugin_city;
      }
      return $result;
  }
  
  //$location = getLocationInfoByIp1();//webxtor 2021Jan25: NOT USED TAKES 16 SECONDS!!
  ?>
<!-- Top Blue Bar-->
<div class="container top-bar " id="topbar">
  <div class="row">
    <div class="header-fixed-container">
      <div class="col-sm-12 col-lg-4 top-lft">
        <ul>
          <?php
            $cid;
            if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
                $uid = $_SESSION['uid_indm'];
                $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join user u on sip.mp_id = u.usr_mp_id where u.usr_id = " . $uid;
                $get_icon = mysqli_query($con,$sql_icon) or die(mysqli_error());
                $sql = "select * from user,business_profile where usr_id=bnsprof_uid and usr_id='" . $uid . "' and status = '1'";
                $res = mysqli_query($con, $sql);
                $row = mysqli_fetch_object($res);
                $cid = rand(1000, 9999) . md5($row->bnsprof_id);
                ?>
          <li><span class="pp1"><span
            class="tlc"> مرحبا </span><?php
            echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname');
            if ($row->bnsprof_compname != '') {
                ?> <span>
            <?php
              if (mysqli_num_rows($get_icon) > 0) {
              if(get_membership_expired()!=true){
                  $title = 'Junior';
                  $icon = mysqli_fetch_array($get_icon);
                  if ((strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false)) {
                      $title = 'Senior';
                  } else if ((strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false)) {
                      $title = 'Sponsor';
                  }
                  ?>
            <a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;"
              alt=""/></a>
            <?php }}
              ?>
            </span>
            <?php } ?>
            </span> 
          </li>
          
          <?php } else { ?>
          <li><a href="sign-in.php#loginform" target="_top" rel="nofollow" style=" font-family:GE SS Two"title=" Sign in "> سجل دخول </a></li>
          |
          <li><a href="create_account.php#signupform" target="_top" rel="nofollow" style=" font-family:GE SS Two"   title=" Join Free"  >إنشىء حساب مجانا &nbsp;|</a></li>
          
          
          
          
          
          
          
          
          <?php } ?>
          <li class="dropdown dropdown1"  style="z-index: 100;"title=" عملى على سوق مصر ">
            <a data-target="myEgyptmart"  class="dropbtn1" href="" data-toggle="dropdown" role="button"
              aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">تجـارتى على المنصة</span> </b> <i class="fa fa-chevron-down"></i> </a><span class="linebr" style="color: black"> |</span> <a href="my-enquiries.php"> <img width="25" src="images/envolap.png"/>
            <?php
              if (isset($_SESSION['uid_indm'])) {
                  $query_pag_num = "SELECT count(*) AS count from message,user where msg_to='" . $_SESSION['uid_indm'] . "' and msg_from=usr_id and msg_to_status='1'"; // Total records
                  $result_pag_num = mysqli_query($con, $query_pag_num);
                  $row = mysqli_fetch_array($result_pag_num);
                  $count = $row['count'];
                  echo '<span class="label label-yellow">' . $count . '</span>';
              } else {
                  echo '<span class="label label-yellow">0</span>';
              }
              ?>
            </a>
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="myEgyptmart" style="width:101%; z-index: -1;">
              <li><a href="my-dashboard.php"style=" font-family:GE SS Two"title=" My Dashboard "> مفاتيــح إدارة المنصــة </a></li>
              <li><a href="my-enquiries.php"style=" font-family:GE SS Two"title="My Inbox"> صنـــدوق رسائلى داخل المنصة</a></li>
              <li><a href="favorite.php"style=" font-family:GE SS Two"title="My Favorites"> صفحــة منتجـــاتى المفضلــة </a></li>
              
              <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
              <li><a href="logout.php"style=" font-family:GE SS Two"title="Sign Out">تسجيل خروج</a></li>
              <?php } ?>
            </ul>
          </li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-4 top-mid">
        <ul>
          <?php if (getUserInfo($uid, 'usr_mp_id') < 4) { ?>
          <!-- <li style="color: orange; padding-left:3px;" > Credit : <a href="#" class="txt-bold txt-yellow" style="font-weight:900 ; font-size:13px; color: orange"> <b style="color: white"><?php echo (getUserInfo($uid, 'usr_credit') > 0) ? getUserInfo($uid, 'usr_credit') : '0'; ?></b></a> </li>
            <li><a href="subscription.php" style="margin:0px; padding:0px;">| &nbsp;Buy Credit</a></li>-->
          <?php } ?>
          <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
          <li><a href="company/index.php?c=<?php echo $cid; ?>" class="txt-yellow" style=" font-family:GE SS Two"title=" My B2B Website" > معروضاتى
       </a>                    
          </li>
          
          
          
          
          
          
            <?php } ?>
          </li>
          <span style="margin-left:40px;">
            <li class="dropdown dropdown1">
              <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"style="color:yellow; font-family:GE SS Two;"title="Buy">إشترى <i class="fa fa-chevron-down"></i> </a>
              <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="buy">
                <li><a href="post-buy-req.php"style=" font-family:GE SS Two;"title="Post Your Buy Requirement">أنشر طلبات تسعيير لمشترياتك</a></li>
                <li><a href="search_adv.php"style=" font-family:GE SS Two;"title=" Search Product & Suppliers">  إبحث عن منتجات وخدمات  </a></li>
                <li><a href="manage-selloffer-alert.php"style=" font-family:GE SS Two;" title=" Manage Sale Notifications"> سجل اشعارات فرص بيع  </a></li> 
                <li><a href="post-tender.php"style=" font-family:GE SS Two;"title=" Post Tenders FREE "> أنشر مناقصات مجانا  </a></li>
              </ul>
            </li>
            <li class="dropdown dropdown1" id="sell">
              <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown"
                role="button" aria-haspopup="true" aria-expanded="false"style=" color:yellow;font-family:GE SS Two;"
                title="Sell" > بيــع <i class="fa fa-chevron-down"></i> </a>
              <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur " aria-labelledby="sell">
                <li><a href="product-add.php"style=" font-family:GE SS Two;"title=" Display Products / Services "> إعرض منتجات أو خدمات  </a></li>
                <li><a href="membership_plans.php"style=" font-family:GE SS Two;"title="Create B2B Website"  > إنشىء صفحات أعمالك   </a></li>
                <li><a href="buyleads.php"style=" font-family:GE SS Two;"title="Latest Buy Requests">أحدث طلبات الشراء </a></li>
                <li><a href="http://egyptmart.shop/post-sell-offer.php"style=" font-family:GE SS Two;"title="  Post Sale Offers "> سجل عروض بيع خاصـة </a></li>
                <li><a href="manage-buylead-alert.php"style=" font-family:GE SS Two;"title=" Manage Buy Notifications">  سجل إشعارات طلبات شراء </a></li>
                <li><a href="post-auction.php"style=" font-family:GE SS Two;"title=" Post Auctions FREE "> أنشر مزايدات مجانا  </a></li>
              </ul>
            </li>
            
        </span>
       <a href=" " 
   style="color: #fff; background: #007bff; padding: 8px 15px; border-radius: 5px; text-decoration: none;">
   نسخة تجريبية
</a> 
        
        
    </li>    
        
        


        
       <li class="dropdown dropdown1"  style="z-index: 100;"title="  سوق التصدير  ">
            <a data-target="myEgyptmart"  class="dropbtn1" href="" data-toggle="dropdown" role="button"
              aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">ARAB EXPORT</span> </b> <i class="fa fa-chevron-down"></i> 
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="myEgyptmart" style="width:101%; z-index: -1;">
              <li><a href="http://arab-mart.com"style=" font-family:GE SS Two"title="المنصة باللغة الإنجليزية للمورديين الدوليين "><style=" font-family:Arial Bold"></style> Arab-MART.com سوق العرب  </a></li>
   
      </div>
      <div class="col-sm-6 col-lg-4 top-rht">

        <ul class="text-right tstleft">
          <!--<li style="padding-right:3px;"><a href="http://egyptmart.shop/company/products.php?c=3654fa3a3c407f82377f55c19c5d403335c7&amp;sc=179742556">Member</a></li>-->

          <!--<li><a href="https://wa.me/<?php echo get_page_settings(21); ?>" style="color:orange "><img src="images/mobile.png" width="25px"></a><a href="help.php" style="color:orange "><b style="font-weight:900; color: white"> <span
            class="txt-yellow"></span><?php echo get_page_settings(21); ?></b></a></li>-->
           
            
          <li><a href="why_egyptmart.php" class=" txt-yellow"><b class="txt-yellow"  style="font-weight:900;"title="Why  EgyptMART"> 
  فوائـد الإشتراك  </b></a> </li>
          <li style=""><a href="help.php"style=" font-family:GE SS Two"title="How It Works ?">كيف تعمل المنصة ؟</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<!-- End of topbar // -->
<div class="maincontainertop ">
  <!-- page-header start -->
  <header class="page-header home-header" style="background-image:linear-gradient(rgba(255,255,255, 0.45), rgba(255,255,255, 0.45)),url(images/headerbg.jpg); background-repeat:no-repeat; background-size:cover;">
      
      
   <div class="headertop-custom-box">
        
     <div class="headertop-custom-box-left" style="position: absolute; top: 5px; left: 10px; z-index: 99; width: 250px;">
    <img alt="map" src="/images/page-header-col1_mapbg.jpg" style="width: 100%; height: auto; display: block;">
</div>
      <!--      <div class="headertop-custom-box-left">
        <img alt="fdfdf" src="images/ page-header-col1_mapbg.jpg " class="globeimg1">
        
        </div>-->
      <div class="headertop-custom-box-middle">
        <div class="page-header-col1-row1" style="padding:0;">
          <!-- col-md-9 start -->
          <div class="page-header-col1-row1-col1 col-xs-6">
            <div class="page-header-col1-row1-col1_row">
              <p><a href="my-dashboard.php"title="My Dashboard">لوحة مفاتيح المنصة</a></p>
            </div>
            <!-- page-header-col1-row1-col1 start -->
            <div class="page-header-col1-row1-col1_row2">
              <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
                <?php
                  echo "<!--<pre>";print_r($_COOKIE);echo "</pre>-->";
                                                  if (isset($_COOKIE['loc_id'])) {
                  echo "<!--<pre>";print_r($_COOKIE);"</pre>";
                  echo get_country_flag($_COOKIE['loc_id']).'-->';
                                                      ?>
                <span><?php echo get_country_name($_COOKIE['loc_id']); ?></span>&nbsp; <img src="images/country_flag/<?php echo get_country_flag($_COOKIE['loc_id']); ?>"
                  alt="<?php echo get_country_name($_COOKIE['loc_id']); ?>" class="w4" align="top" height="16"
                  width="23" title="<?php echo get_country_name($_COOKIE['loc_id']); ?>"/>
                <?php } else { ?>
                <span style="font-weight: bold; font-size: 20px;  color: darkcyan;  font-family: Arial Black;">Global</span> &nbsp; <img src="images/country_flag/Global$download.png" style="height:25px !important;width:25px!important;" style="height:25px !important;width:25px!important;"   alt="Global" class="w4"
                  align="top" height="30" width="30"/>
                <?php } ?>
              </div>
              <div class="page-header-col1-row1-col1-row2-form">
                <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()">
                  <a class="un" style="border-left:none; font-size: 9px; color:#0f2399; 
                    ">
                    <span style="color: black;">غـير بلـد البحـث <!--  <i class="fa fa-chevron-down"></i>--> 
                    &nbsp;<span class="arw"><b>&or;</b></span> 
                  </a>
                  <style>
                    #changeLocation{
                      display:none; left:0 !important; top:-30px !important; right:0;
                    }
                    @media (min-width: 991px){
                        #changeLocation {
                            top: 20px !important;
                        }
                    }
                  </style>
                  <div class="sub_menu" id="changeLocation">
                    <ul>
                      <li>
                        <?php
                          $numCun = count(explode(",", getActiveCountryList()));
                          $sql_cnLoc = "select * from country where cn_id in(" . getActiveCountryList() . ")";
                          $res_cnLoc = mysqli_query($con, $sql_cnLoc);
                          ?>
                        <table style="width:100%;padding:1px;" class="table-responsive">
                          <tr>
                            <td align="center"><a title="Global" style="cursor:pointer;" onclick="unsetCountryLocation();"> <img src="images/country_flag/Global$download.png" alt="Global" class="w4"
                              align="top" height="25" width="25"/> </a></td>
                            <?php
                              $cn = 1;
                              while ($row_cnLoc = mysqli_fetch_object($res_cnLoc)) {
                                  if ($cn % 4 == 0) {
                                      $cn = 0;
                                      ?>
                          </tr>
                          <tr>
                            <?php
                              }
                              //echo $location['country']."--".$row_cnLoc->cn_code."<br/>";
                              /* if($location['country'] == $row_cnLoc->cn_code){ ?>
                            <script>
                              setCountryLocation(<?php echo $row_cnLoc->cn_id ?>);
                            </script>
                            <?php } */
                              ?>
                            <td align="center"><a title="<?php echo $row_cnLoc->cn_name; ?>" style="cursor:pointer;"
                              onclick="setCountryLocation(<?php echo $row_cnLoc->cn_id ?>);"> <img
                              src="images/country_flag/<?php echo get_country_flag($row_cnLoc->cn_id); ?>"
                              alt="<?php echo $row_cnLoc->cn_name; ?>" class="w4" align="top"
                              height="20" width="25"/> </a></td>
                            <?php
                              $cn++;
                              }
                              ?>
                            <?php while ($cn <= 5) { ?>
                            <td>&nbsp;</td>
                            <?php
                              $cn++;
                              }
                              ?>
                          </tr>
                        </table>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- page-header-col1-row1-col1 close // --> 
          <!-- page-header-col1-row1-col2 start -->
          <div class="page-header-col1-row1-col2 col-xs-6">
            <?php
              $toplogo = GettingSite_Setting('logo');
              if ($toplogo != "") {
                  $toplogo2show = "sitelogo/" . $toplogo;
              } else {
                  $toplogo2show = "images/page-header-col1-row1-col2-logo.png";
              }
              ?>
            <a href="index.php"title="سوق مصر على ال
         إنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع - فى مصر والمنطقة العربية"><img src="sitelogo/logo6744egyptmart logo SHOP copy.png" 
         alt=""  class="logoa" /></a> 
          </div>
          <!-- page-header-col1-row1-col2 close// -->
          <div class="page-header-col1-row1-col3">
            <!-- page-header-col1-row1-col3 start -->
            <div id="google_translate_element"></div>
            <script type="text/javascript">
              function googleTranslateElementInit() {
                  new google.translate.TranslateElement({
                      pageLanguage: 'en',
                      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                  }, 'google_translate_element');
              }
            </script> 
            <script type="text/javascript"
              src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
            <p class="cb"></p>
          </div>
          <!-- page-header-col1-row1-col3 close// -->
          <div class="page-header-col1-row1-col4 col-xs-12" style="padding:0;">
            <!-- page-header-col1-row1-col4 start -->
            <div class="page-header-col1-row1-col4-row1 col-xs-6 home-ba">
              <!--<img src="images/busine.png" width="100%"/> -->
              <h3 style="font-size:30px;"title="Business Alerts"><img class="img-responsive " src="images/bell.png" width="18px" style="margin-right:5px;"title="Business Alerts"> إشعـارات  تجـارية</h3>
              <p class="text-center" style="font-size: 10px"> تـلـقــــى إشــعـــــــارات فــــى بـريـــــدك <br>  عن المنتجات المفضـلة لتجارتـك      </p>
            </div>
            <script>
              function sub() {
                  var location = "";
                  if (document.getElementById('radio1').checked) {
                      location = document.getElementById("radio1").value;
                  }
                  if (document.getElementById('radio2').checked) {
                      location = document.getElementById("radio2").value;
                  }
                  window.location = location;
              }
            </script>
            <div class="page-header-col1-row1-col4-row2  col-xs-6 home-buyer-seller ; align:center;">
              <div class="page-header-col1-row1-col4-row2-checkbox">
                <label class="radio">
                <input id="radio1" type="radio" name="radios" value="manage-selloffer-alert.php"> 
                <span class="outer"><span class="inner"></span></span><a href="#" style="color: black" title="سجل أسماء المنتجات / الخدمات التى تهتم بشرائها - لكى تتلقى أحدث اشعارات عنها فى بريدك "> شــراء </a> </label>
                <label class="radio Buyer-radio" style="font-size: 16px">
                <input id="radio2" type="radio" name="radios" value="manage-buylead-alert.php" checked >
                <span class="outer"><span class="inner"></span></span><a href="#" style="color: black align:center;"title="سجل أسماء المنتجات / الخدمات التى تببيعها - لكى تتلقى فى بريدك أحدث إشعارات - طلبات عرض الأسعار المرسلة عنها  "> بيــــع</a> </label>
              </div>
              <div class="page-header-col1-row1-col4-row2-link"><a id="sub" onclick="return sub();" href="sign-in.php"title="Subscribe NOW"> ســجـــل  الآن</a></div>
              <h2 class="justclick"title="Just a Click Away"><br>   </h2>
            </div> 
          </div>
          <!-- page-header-col1-row1-col4 close// --> 
        </div>
        <div class=" header-mid header-mid-custom-box">
          <div class="post-prod-left">
            <!-- <a href="product-sel-cat.php"> <img src="images/Postproducts.png" /></a> -->
            <!-- <h1><a href="product-sel-cat.php">Display Your Products</a></h1>
              <a href="product-sel-cat.php"> <p>Get <span>Home & Global</span> Inquiries </p></a> --> 
            <a href="product-sel-cat.php" class="post-product-btn"title=" Display Your Business "> 
        إعرض هـنا منتجاتـك للبيع <small>وتلقــى استفسـارات شــراء محليا ودوليا  <strong></strong>  <strong></strong></small> 
            </a>         
          </div>
          <div class="page-header-col1-row2">
            <!-- page-header-col1-row2 start -->
            <div class="col-lg-7 col-md-6 col-xs-12 margintop">
              <!-- <div class="srchBx">
                <h2 class="cd-headline clip is-full-width text-center"> <span style="width: 100%; overflow: hidden; color:Grey; font-family: Arial narrow;" class="cd-words-wrapper" > <b class="is-hidden">
                إنضم لسوق أهم 10,000 شركة ومصنع فى مصر والمنطقة العربية
                <span class="blinking-cursor" style="color: Grey">!</span></b> <b class="is-hidden">Source > Supply > Export > Grow Your Business ..<span class="blinking-cursor" style="color: Grey">!</span> </b> <b class="is-visible">Create Your Domestic &amp; Global Business WEBSITE<span class="blinking-cursor" style="color: Grey">!</span></b> </span> </h2>
                
                </div> -->
              <!-- <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script> --> 
              <script>
                $(document).ready(function () {
                
                    $('.searchTabs').click(function () {
                        var TabVal = $(this).attr('alt'); //alert(TabVal);
                        var optionValue = $(this).attr('alt');
                        $('#rctyp option').removeAttr('selected');
                
                        $('#rctyp option[value=' + optionValue + ']').attr('selected', 'selected');
                
                        var PlaceholdVAl = "";
                
                        if (TabVal == 'Products') {
                
                            PlaceholdVAl = "إبحــث عن منتجات وخدمات تجارية من المنبـع أو بإسم المـورد  >>";
                        } else if (TabVal == 'Suppliers') {
                
                            PlaceholdVAl = "إبحــث عن مــوردين بأسمـاء الشركات أو منتجـات المورديــن    >> ";
                        } else if (TabVal == 'buy_lead') {
                
                            PlaceholdVAl = "إبحــث عن طلبات شراء لأعمالك من المنبـع >>";
                        } else if (TabVal == 'tender') {
                
                            PlaceholdVAl = "إبحــث عن مناقصات أو مزايدات لأعمالك >>";
                        }
                        $("#search-box1").attr("placeholder", PlaceholdVAl);
                
                    });
                
                    $("#search-box1").keyup(function () {
                        var getDrpDwnVal = $("ul.search_tab li.active").attr("alt");
                        console.log("Here is your dropdown value: " + getDrpDwnVal);
                        if (getDrpDwnVal == 'Suppliers') {
                            var fileName = "readsuppliers.php";
                        } else if (getDrpDwnVal == 'Products') {
                            var fileName = "readproducts.php";
                        } else if (getDrpDwnVal == 'Buy Leads') {
                            var fileName = "read_leads.php";
                        } else {
                            var fileName = "read_tenders.php";
                        }
                        //alert(getDrpDwnVal+' '+fileName);return false;
                        $.ajax({
                            type: "POST",
                            url: fileName,
                            data: 'keyword=' + $(this).val(),
                            beforeSend: function () {
                                $(".search-box").css("background", "#FFF url(377.gif) no-repeat 165px");
                            },
                            success: function (data) {  //alert(data);return false;
                                $("#suggesstionBoxs").show();
                                $("#suggesstionBoxs").html(data);
                                $("#search-box1").css("background", "#FFF");
                            }
                        });
                    });
                });
                function selectCountry(val) {
                    //alert(val); return false; 
                    $("#search-box1").val(val);
                    $("#suggesstionBoxs").hide();
                }
              </script>
              <div class="page-header-col1-row2-col2-form">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs search_tab" role="tablist" id="rctyp">
                  <li role="presentation" class="active" alt="Products"><a href="#products" alt="Products" class="searchTabs" aria-controls="products" role="tab" data-toggle="tab"title="Find Products & Services" >  إبحــث عن أى منتجــات </a></li>
                  <li role="presentation" alt="Suppliers"><a href="#supplier" alt="Suppliers" class="searchTabs" aria-controls="supplier" role="tab" data-toggle="tab"title="Find Suppliers" >إبحـث عن شركات وموردين</a></li>
                </ul>
                <!-- Tab panes -->   
                <div class="tab-content search_cont">
                  <div role="tabpanel" class="tab-pane active" id="supplier">
                    <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                      <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                        <option value="Suppliers">Suppliers fdf df</option>
                        <option  value="Products" selected>Products</option>
                        <option value="buy_lead">Buy Leads</option>
                        <option value="tender">Tender</option>
                        <!--<option value="auction">Auction</option>-->
                      </select>
                      <input type="text" id="search-box1" name="keywords" style="font-weight:900;text-color:black;text-align:center; border:1px solid;box-shadow: 1px 2px 4px #595959;" placeholder="  إبحــث بالعربى أو الإنجليزى >> منتجات وخدمات >> مصر والعالم "
                        class="page-header-col1-row2-col2-form-input topsearch_placeholder_cont search-box"  onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo htmlspecialchars($_GET['keywords'] ?? ''); ?>"
                        style="border: 1px solid #000;width:90%" />
                      <span class="loading-text hide"><img src="/assets/img/Spinner-200px.gif" style="width: 48px;height: 48px;"></span>
                      <div id="suggesstionBoxs" class="suggesstionBoxs"></div>
                      <input type="submit" id="btnSearch" value="" class="page-header-col1-row2-col2-form-btn"/>
                    </form>
                  </div>
                </div>
                <div class="clear"></div>
              </div>
              <!-- added by webcast -->
              <div class="srchBx">
                <h2 class="cd-headline clip is-full-width text-center"> <span style="width: 100%; overflow: hidden; color:#404040; font-family: GE_SS_TEXT_LIGHT;" class="cd-words-wrapper" > <b class="is-hidden"><span class="blinking-cursor" style="color: red">!                       </span> إنضم لسوق أهم 10,000 شركة ومصنع فى مصر والعرب </b> <b class="is-hidden"><span class="blinking-cursor" style="color: red">!  </span> تجارى وتصدير - أونلاين - محلى ودولى -  
 آلاف المنتجات       </b> <b class="is-visible"><span class="blinking-cursor" style="color: red ">! </span>  إنشىء الآن صفحة أعمالك وإستقبل طلبات شراء تنتظرك </b> </span> </h2>
              </div>
            </div>
            <div class="page-header-col1-row2-col4 ">
              <!-- page-header-col1-row2-col4 start -->
              <a href="post-buy-req.php" class="post-buy-req-btn" style="margin-top: 56px;"title=" Post Buy Requirements and Get Quotes from Verified Suppliers">  أنشـر طلـب تسعير وشـراء   <small>  وتلقـى أقـل عــروض بيـع له    <strong style="font-weight:900;color:#2320da;"></strong>  <strong style="font-weight:900;"> </strong>  </small>                           
              </a> ـ
            </div>
            <!-- page-header-col1-row2-col4 close// -->
            <div class="clear"></div>
          </div>
        </div>
      </div>
      <div class="headertop-custom-box-right">
        <div class="">
          <div class="page-header-col2-head">
            <i class="fa fa-mobile"></i> <span>Android - Windows - 360 Degree Visibility </span> 
          </div>
          <div class="page-header-col2-intro">
            <div class="page-header-col2-intro-pic">
              <img src="images/page-header-col2-intro-pic.jpg" alt=""/>
            </div>
            <div class="page-header-col2-intro-texts">
              <a href="product-sel-cat.php?select=bs" class="post-product-btn" id="business-btn"title=" Post Business Services and Get Domestic or Global Inquiries">إعرض هنا خدماتك التجارية للبيع<small>وتلقـى إستفســارات شـراء من الداخــل والخــارج <strong></strong> <strong></strong> </small>          
              </a>
              <!-- <a href="product-sel-cat.php" class="zoomin3"> <img src="images/PostServise.jpg "  /> </a>--> 
              <!--<h2><a href="product-sel-cat.php">Post Your Services</a></h2>
                <p>Get <span>Domestic</span> or <span>Global</span> Inquiries</p> --> 
            </div>
          </div>
        </div>
        <!-- page-header-col1 close// -->
        <div class="clear"></div>
      </div>
      <div class="clear"></div>
    </div>
  </header>
  <!-- page-header close // --> 
</div>
<!-- Start of rowbanner -->
<div class="middlesection">
  <div class="maincontainer">
    <div class="demobox">
      <div id="leftsection">
        <!--   left--> &nbsp;
        <div class="clear"></div>
      </div>
      <div class="col-xs-12" id="midcenter">
        <?php
          $banner = GetHomeBanner('top', $strconutnry);
          if ($banner != "") {
              echo '<div class="middle mid-content" style="padding:0;">';
              echo $banner;
              echo '</div>';
          } else {
              //echo '<div class="middle mid-content">';
              //echo ' <h3>Banner Place</h3>';
              // echo '</div>';
          }
          ?>  
      </div>
      <div id="rightsection">
        <!-- right--> &nbsp;
      </div>
    </div>
    <div class="clear"></div>
      </div>
</div>

<!-- End of rowbanner // -->
<style>
  .img-responsive
  {
  /*width:14px;*/
  float:left;
  }
  .maincontainertop
  {
  z-index:1003 !important;
  }
  .page-header-col1-row2-col2-form
  {
  /*    display:none;*/
  }
  .page-header-col1-row1-col4-row2-checkbox
  {
  width:100% !important;
  }
  .justclick {
  font-size: 14px;
  font-weight: 900;
  color: #7e7e7e;
  word-spacing: 0;
  letter-spacing: 0;
  margin-top: 8px;
  }
  @media (min-device-width:769px) and (max-device-width:1450px){
  .page-header-col2-intro {
  border-left: 2px solid #237abf !important;
  height: 126px !important;
  }
  }
  span.loading-text.show {
  position: absolute;
  top: 28px;
  right: 55px;
  color: red;
  font-size: 20px;
  }
  @media (width:1024px) {
  .home-ba h3 {
  text-align: center !important;
  }
  .page-header-col1-row1-col4-row1 p {
  text-align: center !important;
  }
  .home-buyer-seller .page-header-col1-row1-col4-row2-checkbox , .page-header-col1-row1-col4-row2-link {
  margin-left: 10px !important;
  }
  .headertop-custom-box-middle h1.justclick {
  margin-left: 20px !important;
  }
  }
</style>
<script type="text/javascript">
  $(document).on('ready', function () {
      $(".center").slick({
          dots: true,
          infinite: true,
          centerMode: true,
          slidesToShow: 5,
          slidesToScroll: 3
      });
      /*$('#btnSearch').click(function(event) {
          $('.loading-text').removeClass('hide').addClass('show');
      });*/
  });
</script>
<?php  include('style.php'); ?>
<!--<script>
  $(window).scroll(function() {
  
  if ($(this).scrollTop()>0)
  {
  $('.page-header-col1-row2-col2-form').fadeIn();
  $('.srchBx').css('margin-top','3px');
  }
  else
  {
  $('.page-header-col1-row2-col2-form').fadeOut();
  $('.srchBx').css('margin-top','66px');
  }
  });
  </script>-->