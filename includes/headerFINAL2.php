<?php
$yasirRequestedPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? 'index.php', PHP_URL_PATH));
$yasirUseHomepageHeader = ($yasirRequestedPage === '' || $yasirRequestedPage === 'index.php');
if (!$yasirUseHomepageHeader) {
    include __DIR__ . '/header-internal-yasir.php';
    return;
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
<style>
body{
    font-family: 'Tajawal', sans-serif;
}
</style>
<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>
<?php include "css/custom.php"; ?>
<script type="text/javascript">
  function showmymenu() {
      $("#mn1").show();
  }
  function hidemymenu() {
      $("#mn1").hide();
  }
  function showLocMenu() {
      $("#changeLocation").addClass("is-open");
  }
  function hideLocMenu() {
      $("#changeLocation").removeClass("is-open");
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
<nav class="mobile-quick-strip" aria-label="Quick links">
  <a href="buyleads.php"><span>طلبات شراء</span></a>
  <a href="sale-offers.php"><span>عروض بيع</span></a>
  <a href="membership_plans.php"><span>العضويات</span></a>
  <a href="advertise-with-us.php"><span>إعلانات</span></a>
</nav>
<script>
window.emScenarioKeyword = function(text) {
    return String(text || '').replace(/\s+/g, ' ').trim();
};
window.emScenarioToggle = function(button, inputId, modeId) {
    var input = document.getElementById(inputId);
    var mode = document.getElementById(modeId);
    var active = !button.classList.contains('active');
    button.classList.toggle('active', active);
    if (mode) mode.value = active ? 'scenario' : 'standard';
    if (input) input.placeholder = active ? 'اكتب طلبك كاملا: أحتاج شراء كمية كبيرة من الفول مع توصيل سريع' : (input.getAttribute('data-default-placeholder') || input.placeholder);
    return false;
};
window.emScenarioSubmit = function(form, inputId, modeId) {
    var input = document.getElementById(inputId);
    var mode = document.getElementById(modeId);
    if (input && mode && mode.value === 'scenario') {
        input.value = emScenarioKeyword(input.value);
    }
    return typeof validsearch === 'function' ? validsearch() : true;
};
document.addEventListener('click', function(event) {
    var tradeMenuButton = event.target.closest('#topbar .top-lft .dropdown1 .dropbtn1');
    var tradeMenu = event.target.closest('#topbar .top-lft .dropdown1');
    var openMenu = document.querySelector('#topbar .top-lft .dropdown1.open');
    if (tradeMenuButton) {
        event.preventDefault();
        document.querySelectorAll('#topbar .dropdown1.open').forEach(function(item) {
            if (item !== tradeMenuButton.parentNode) item.classList.remove('open');
        });
        tradeMenuButton.parentNode.classList.toggle('open');
        return;
    }
    if (openMenu && !tradeMenu) {
        openMenu.classList.remove('open');
    }
});
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth > 768) {
        document.querySelectorAll('#topbar .top-mid .dropdown1, #topbar .top-lft .dropdown1').forEach(function(menu) {
            menu.addEventListener('mouseenter', function() {
                document.querySelectorAll('#topbar .dropdown1.open').forEach(function(item) {
                    if (item !== menu) item.classList.remove('open');
                });
                menu.classList.add('open');
            });
            menu.addEventListener('mouseleave', function() {
                menu.classList.remove('open');
            });
        });
        var locationControl = document.querySelector('.home-header .page-header-col1-row1-col1_row2');
        var locationMenu = document.getElementById('changeLocation');
        if (locationControl && locationMenu) {
            locationControl.addEventListener('mouseenter', function() {
                locationMenu.classList.add('is-open');
            });
            locationControl.addEventListener('mouseleave', function() {
                locationMenu.classList.remove('is-open');
            });
        }
    }
    var topbar = document.getElementById('topbar');
    if (!topbar || topbar.querySelector('.mobile-trade-toggle')) return;
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'mobile-trade-toggle';
    button.setAttribute('aria-label', 'Menu');
    button.innerHTML = '<i class="fa fa-bars"></i>';
    var panel = document.createElement('div');
    panel.className = 'mobile-trade-panel';
    panel.innerHTML = '<a href="my-dashboard.php">مفاتيــح إدارة المنصــة</a><a href="my-enquiries.php">صنـــدوق رسائلى داخل المنصة</a><a href="favorite.php">صفحــة منتجـــاتى المفضلــة</a><a href="post-buy-req.php">أنشر طلب شراء</a><a href="product-add.php">أضف منتج</a>';
    topbar.appendChild(button);
    topbar.appendChild(panel);
    button.addEventListener('click', function(event) {
        event.preventDefault();
        panel.classList.toggle('is-open');
    });
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.mobile-trade-toggle') && !event.target.closest('.mobile-trade-panel')) {
            panel.classList.remove('is-open');
        }
    });
});
</script>
<div class="container top-bar " id="topbar">
  <div class="row">
    <div class="header-fixed-container">
      <a class="desktop-site-logo" href="index.php" aria-label="EgyptMART">
        <img src="images/Mlogo.png" alt="EgyptMART">
      </a>
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
          <li class="account-user-only"><span class="account-welcome">مرحبا : </span><bdi class="top-user-name"><?php
            echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname'); ?></bdi>
          </li>
          
          <?php } else { ?>
          <li class="account-access">
            <button type="button" class="open-auth-popup account-access-button" data-auth-tab="login" aria-label="تسجيل الدخول أو إنشاء حساب" title="تسجيل الدخول أو إنشاء حساب" style="direction:ltr!important;flex-direction:row!important;">
              <img src="images/yasir-account-icon.png" alt="">
              <span class="account-access-label">الحساب :</span>
            </button>
          </li>
          
          
          
          
          
          
          
          
          <?php } ?>
          <li class="dropdown dropdown1"  style="z-index: 100;"title=" عملى على سوق مصر ">
            <a data-target="myEgyptmart"  class="dropbtn1" href="#" onclick="if(window.innerWidth<=768){this.parentNode.classList.toggle('open');return false;}" data-toggle="dropdown" role="button"
              aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">تجـارتى على المنصة</span> </b> <i class="fa fa-chevron-down"></i> </a><span class="linebr" style="color: black"> </span> <a href="my-enquiries.php"> <img width="25" src="images/envolap.png"/>
            <?php
              if (isset($_SESSION['uid_indm'])) {
                  $count = function_exists('getCombinedNotificationCount')
                      ? getCombinedNotificationCount((int)$_SESSION['uid_indm'])
                      : 0;
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
           
            
         
      </div>
    </div>
  </div>
</div>
<?php if (!isset($_SESSION['uid_indm']) || $_SESSION['uid_indm'] == '') { ?>
<style>
#authPopup.auth-popup-overlay {
    align-items: center !important;
    background: rgba(0, 0, 0, .72) !important;
    bottom: 0 !important;
    display: none !important;
    justify-content: center !important;
    left: 0 !important;
    padding: 10px !important;
    position: fixed !important;
    right: 0 !important;
    top: 0 !important;
    z-index: 2147483000 !important;
}
#authPopup.auth-popup-overlay.is-open {
    display: flex !important;
}
#authPopup .auth-popup-card {
    background: #fff !important;
    border-radius: 6px !important;
    box-shadow: 0 24px 70px rgba(0,0,0,.35) !important;
    box-sizing: border-box !important;
    color: #263238 !important;
    max-height: calc(100vh - 36px) !important;
    direction: rtl !important;
    max-width: 480px !important;
    overflow: auto !important;
    padding: 10px 12px 11px !important;
    position: relative !important;
    text-align: right !important;
    width: 100% !important;
}
#authPopup .auth-popup-methods {
    display: grid !important;
    gap: 10px !important;
    grid-template-columns: 1fr 1fr !important;
    margin-bottom: 8px !important;
}
#authPopup .auth-popup-method {
    align-items: center !important;
    border: 1px solid #d5dadd !important;
    border-radius: 5px !important;
    cursor: pointer !important;
    display: flex !important;
    gap: 6px !important;
    padding: 10px !important;
}
#authPopup .auth-popup-method input { margin: 0 !important; }
#authPopup .auth-popup-mobile-row { display: none !important; }
#authPopup .auth-popup-mobile-row.is-active { display: block !important; }
#authPopup .auth-popup-email-row { display: none; }
#authPopup .auth-popup-otp-row {
    align-items: stretch !important;
    display: grid !important;
    gap: 8px !important;
    grid-template-columns: 108px minmax(0, 1fr) 92px !important;
}
#authPopup .auth-popup-otp-row input {
    flex: 1 1 auto !important;
    margin-bottom: 0 !important;
    min-width: 0 !important;
}
#authPopup .auth-popup-otp-btn {
    background: #eef5f7 !important;
    border: 1px solid #b9ccd3 !important;
    color: #0a4353 !important;
    cursor: pointer !important;
    flex: 0 0 92px !important;
    font-weight: 800 !important;
    height: 42px !important;
    line-height: 40px !important;
    min-width: 92px !important;
    padding: 0 8px !important;
    text-align: center !important;
    white-space: nowrap !important;
}
#authPopup .auth-popup-panel { display: none !important; }
#authPopup .auth-popup-panel.active { display: block !important; }
#authPopup .auth-popup-tabs {
    direction: ltr !important;
    display: grid !important;
    gap: 8px !important;
    grid-template-columns: 1fr 1fr !important;
    margin: 0 22px 9px !important;
}
#authPopup .auth-popup-tab {
    background: transparent !important;
    border: 0 !important;
    color: #0a4353 !important;
    cursor: pointer !important;
    font-weight: 800 !important;
    min-height: 34px !important;
}
#authPopup .auth-popup-tab {
    direction: rtl !important;
}
#authPopup .auth-popup-tab.active { border-bottom: 3px solid #00566b !important; }
#authPopup .auth-popup-close {
    background: transparent !important;
    border: 0 !important;
    color: #455a64 !important;
    cursor: pointer !important;
    font-size: 28px !important;
    position: absolute !important;
    left: 14px !important;
    right: auto !important;
    top: 8px !important;
}
#authPopup h3 {
    color: #333 !important;
    font-size: 18px !important;
    font-weight: 800 !important;
    margin: 0 0 10px !important;
    text-align: center !important;
}
#authPopup .auth-popup-panel {
    margin: 0 auto !important;
    max-width: 470px !important;
}
#authPopup input[type="text"],
#authPopup input[type="password"] {
    border: 1px solid #bfc8cd !important;
    box-sizing: border-box !important;
    height: 40px !important;
    margin: 0 0 8px !important;
    padding: 0 12px !important;
    width: 100% !important;
}
#authPopup .auth-popup-google {
    align-items: center !important;
    background: #fff !important;
    border: 1px solid #d5dadd !important;
    color: #333 !important;
    display: flex !important;
    flex-direction: row !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    gap: 10px !important;
    height: 42px !important;
    justify-content: center !important;
    padding: 0 12px !important;
    text-align: center !important;
    text-decoration: none !important;
}
#authPopup .auth-popup-google-mark {
    background: transparent !important;
    border: 0 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    display: inline-block !important;
    height: 20px !important;
    margin: 0 !important;
    padding: 0 !important;
    width: 20px !important;
}
#authPopup .auth-popup-country {
    display: none !important;
}
#authPopup .auth-country-picker {
    direction: ltr !important;
    min-width: 0 !important;
    position: relative !important;
}
#authPopup .auth-popup-form .auth-country-toggle {
    align-items: center !important;
    background: #f8fafc !important;
    border: 1px solid #bfc8cd !important;
    border-radius: 4px !important;
    color: #263238 !important;
    display: flex !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    height: 42px !important;
    justify-content: space-between !important;
    line-height: 1.2 !important;
    min-width: 0 !important;
    overflow: hidden !important;
    padding: 0 7px !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
    width: 100% !important;
}
#authPopup .auth-country-menu {
    background: #fff !important;
    border: 1px solid #bfc8cd !important;
    border-radius: 5px !important;
    box-shadow: 0 10px 28px rgba(0,0,0,.22) !important;
    display: none !important;
    bottom: 45px !important;
    left: auto !important;
    min-width: 250px !important;
    padding: 7px !important;
    position: absolute !important;
    right: 0 !important;
    top: auto !important;
    z-index: 2147483010 !important;
}
#authPopup .auth-country-menu.is-open { display: block !important; }
#authPopup .auth-country-search {
    direction: rtl !important;
    height: 36px !important;
    margin: 0 0 6px !important;
}
#authPopup .auth-country-list {
    border: 1px solid #e1e6e8 !important;
    direction: rtl !important;
    height: 112px !important;
    max-height: 112px !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    width: 100% !important;
}
#authPopup .auth-popup-form .auth-country-option {
    align-items: center !important;
    background: #fff !important;
    border: 0 !important;
    border-bottom: 1px solid #edf0f2 !important;
    border-radius: 0 !important;
    color: #263238 !important;
    direction: rtl !important;
    display: flex !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    gap: 7px !important;
    height: 37px !important;
    justify-content: flex-start !important;
    padding: 0 7px !important;
    text-align: right !important;
    width: 100% !important;
}
#authPopup .auth-popup-form .auth-country-option:hover,
#authPopup .auth-popup-form .auth-country-option:focus {
    background: #eef7f9 !important;
    color: #00566b !important;
}
#authPopup .auth-country-option[hidden] { display: none !important; }
#authPopup .auth-country-empty {
    color: #60747b !important;
    display: none;
    font-size: 13px !important;
    padding: 12px 5px !important;
    text-align: center !important;
}
#authPopup .auth-popup-form button,
#authPopup .auth-popup-primary {
    background: #00566b !important;
    border: 0 !important;
    color: #fff !important;
    cursor: pointer !important;
    font-weight: 800 !important;
    height: 44px !important;
    width: 100% !important;
}
#authPopup .auth-popup-form button.is-disabled,
#authPopup .auth-popup-form button:disabled {
    cursor: not-allowed !important;
    opacity: 0.55 !important;
}
#authPopup .auth-popup-form .auth-popup-otp-btn {
    background: #eef5f7 !important;
    border: 1px solid #b9ccd3 !important;
    color: #0a4353 !important;
    flex: 0 0 92px !important;
    height: 42px !important;
    line-height: 40px !important;
    min-width: 92px !important;
    padding: 0 8px !important;
    white-space: nowrap !important;
    width: auto !important;
}
#authPopup .auth-popup-divider {
    border-top: 1px solid #e0e0e0 !important;
    margin: 18px 0 10px !important;
    text-align: center !important;
}
#authPopup .auth-popup-divider span {
    background: #fff !important;
    color: #777 !important;
    padding: 0 20px !important;
    position: relative !important;
    top: -11px !important;
}
#authPopup .auth-popup-choice {
    display: flex !important;
    gap: 28px !important;
    margin-bottom: 18px !important;
}
#authPopup .auth-popup-optin {
    display: flex !important;
    gap: 8px !important;
    font-size: 12px !important;
    line-height: 1.5 !important;
    margin: 8px 0 !important;
	}
#authPopup .auth-popup-accept {
    align-items: flex-start !important;
    display: flex !important;
    font-size: 12px !important;
    gap: 7px !important;
    line-height: 1.55 !important;
    margin: 8px 0 !important;
}
#authPopup .auth-popup-accept input {
    flex: 0 0 auto !important;
    margin: 3px 0 0 !important;
}
#authPopup .auth-popup-accept a,
#authPopup .auth-popup-link {
    color: #07596b !important;
    font-weight: 800 !important;
    text-decoration: underline !important;
}
#authPopup .auth-popup-otp-status {
    background: #eef8fb !important;
    border: 1px solid #b9dce6 !important;
    color: #0a4353 !important;
    display: none;
    font-size: 13px !important;
    line-height: 1.5 !important;
    margin: 10px 0 0 !important;
    padding: 9px 10px !important;
}
#authPopup .auth-popup-code-row {
    display: none !important;
    gap: 8px !important;
    margin-top: 10px !important;
}
#authPopup .auth-popup-code-row.is-visible {
    display: none !important;
}
#authPopup .auth-popup-code-row input {
    flex: 1 1 auto !important;
    margin-bottom: 0 !important;
}
#authPopup .auth-popup-form .auth-popup-verify-btn {
    flex: 0 0 118px !important;
    height: 42px !important;
    line-height: 40px !important;
    padding: 0 12px !important;
    white-space: nowrap !important;
    width: auto !important;
}
#authPopup .auth-popup-switch {
    background: transparent !important;
    border: 0 !important;
    color: #0a4353 !important;
    cursor: pointer !important;
    display: block !important;
    font-weight: 800 !important;
    margin: 7px auto 0 !important;
}
@media (max-width: 540px) {
    #authPopup.auth-popup-overlay { padding: 8px !important; }
    #authPopup .auth-popup-card { max-width: 320px !important; padding: 9px 8px 10px !important; }
    #authPopup .auth-popup-tabs { grid-template-columns: 1fr 1fr !important; margin: 0 16px 7px !important; }
    #authPopup h3 { font-size: 16px !important; margin-bottom: 8px !important; }
    #authPopup .auth-popup-method { padding: 7px !important; }
    #authPopup .auth-popup-google { height: 39px !important; }
    #authPopup .auth-popup-choice { flex-direction: column !important; gap: 8px !important; }
    #authPopup .auth-popup-otp-row { grid-template-columns: 100px minmax(0, 1fr) !important; }
    #authPopup .auth-popup-form .auth-popup-otp-btn {
        grid-column: 1 / -1 !important;
        min-width: 0 !important;
        width: 100% !important;
    }
}
.otp-verify-overlay {
    align-items: center;
    background: rgba(0, 16, 22, .78);
    display: none;
    inset: 0;
    justify-content: center;
    padding: 16px;
    position: fixed;
    z-index: 2147483600;
}
.otp-verify-overlay.is-open { display: flex; }
.otp-verify-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 18px 55px rgba(0,0,0,.35);
    color: #183943;
    direction: rtl;
    font-family: GE SS Two, Tajawal, Arial, sans-serif;
    max-width: 610px;
    padding: 24px 28px 26px;
    position: relative;
    text-align: right;
    width: 100%;
}
.otp-verify-close {
    background: transparent;
    border: 0;
    color: #75858b;
    cursor: pointer;
    font: 34px/1 Arial, sans-serif;
    left: 18px;
    padding: 0;
    position: absolute;
    top: 15px;
}
.otp-verify-card h3 {
    color: #153f4c;
    font-size: 25px;
    margin: 2px 0 20px;
}
.otp-verify-card p {
    color: #60747b;
    font-size: 15px;
    line-height: 1.8;
    margin: 0 0 10px;
}
.otp-verify-mobile {
    color: #153f4c;
    direction: ltr;
    font-size: 17px;
    font-weight: 800;
    margin: 8px 0 18px;
    text-align: right;
}
.otp-verify-label {
    color: #41575e;
    display: block;
    font-size: 15px;
    font-weight: 800;
    margin-bottom: 9px;
}
.otp-verify-digit {
    border: 1px solid #cbd6da;
    border-radius: 7px;
    color: #143d49;
    direction: ltr;
    display: block;
    font: 700 22px/1 Arial, sans-serif;
    height: 52px;
    letter-spacing: 10px;
    margin: 0 0 16px;
    padding: 0 16px;
    text-align: center;
    width: 100%;
}
.otp-verify-digit:focus {
    border-color: #06677b;
    box-shadow: 0 0 0 3px rgba(6,103,123,.13);
    outline: 0;
}
.otp-verify-resend {
    background: transparent;
    border: 0;
    color: #08758b;
    cursor: pointer;
    font-size: 14px;
    padding: 4px 0 14px;
    text-decoration: underline;
}
.otp-verify-status {
    color: #a2342d;
    display: none;
    font-size: 13px;
    margin-bottom: 10px;
}
.otp-verify-submit {
    background: #075064;
    border: 0;
    border-radius: 4px;
    color: #fff;
    cursor: pointer;
    font-size: 18px;
    font-weight: 800;
    height: 52px;
    width: 100%;
}
@media (max-width: 540px) {
    .otp-verify-card { max-width: 340px; padding: 20px 14px 18px; }
    .otp-verify-card h3 { font-size: 20px; margin-bottom: 14px; }
    .otp-verify-card p { font-size: 13px; }
    .otp-verify-digit { height: 47px; }
}
</style>
<div class="auth-popup-overlay" id="authPopup" aria-hidden="true">
  <div class="auth-popup-card" role="dialog" aria-modal="true">
    <button type="button" class="auth-popup-close" aria-label="Close">&times;</button>
    <div class="auth-popup-tabs">
      <button type="button" class="auth-popup-tab active" data-auth-target="signup">إنشاء حساب</button>
      <button type="button" class="auth-popup-tab" data-auth-target="login">سجل دخول</button>
    </div>
    <div class="auth-popup-panel active" data-auth-panel="signup">
      <h3>أنشئ حسابك</h3>
      <a href="google-login.php" class="auth-popup-google"><svg class="auth-popup-google-mark" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.482h4.844a4.14 4.14 0 0 1-1.797 2.716v2.258h2.909c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.468-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A8.999 8.999 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.963 10.707A5.41 5.41 0 0 1 3.681 9c0-.592.102-1.167.282-1.707V4.961H.956A8.997 8.997 0 0 0 0 9c0 1.452.347 2.827.956 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.441 1.346l2.581-2.582C13.464.891 11.427 0 9 0A8.999 8.999 0 0 0 .956 4.961l3.007 2.332C4.672 5.164 6.656 3.579 9 3.579z"/></svg> التسجيل بواسطة Google</a>
      <div class="auth-popup-divider"><span>أو</span></div>
      <div class="auth-popup-methods">
        <label class="auth-popup-method"><input type="radio" name="home_join_method" value="mobile" checked> رقم الجوال</label>
        <label class="auth-popup-method"><input type="radio" name="home_join_method" value="email"> البريد الإلكتروني</label>
      </div>
      <form method="GET" action="create_account.php#signupform" class="auth-popup-form">
        <div class="auth-popup-mobile-row is-active">
          <label>رقم الجوال *</label>
          <div class="auth-popup-otp-row">
            <div class="auth-country-picker">
              <button type="button" class="auth-country-toggle" aria-haspopup="listbox" aria-expanded="false"><span>🇪🇬 +20 مصر</span><span>⌄</span></button>
              <div class="auth-country-menu">
                <input type="text" class="auth-country-search" autocomplete="off" placeholder="ابحث عن الدولة أو الرمز">
                <select class="auth-country-list" size="3" aria-label="نتائج البحث عن الدولة"></select>
                <div class="auth-country-empty">لا توجد دولة مطابقة</div>
              </div>
              <select class="auth-popup-country" name="mobile_country_code" aria-label="اختر الدولة ورمز الاتصال">
                <option value="20" data-country="eg">🇪🇬 +20 مصر</option>
              </select>
            </div>
            <input type="tel" name="mobile_local" inputmode="tel" autocomplete="tel-national" placeholder="رقم الجوال">
            <input type="hidden" name="mobile" value="">
            <button type="button" class="auth-popup-otp-btn">إرسال الرمز</button>
          </div>
          <div class="auth-popup-code-row">
            <input type="text" name="otp_code" inputmode="numeric" maxlength="6" placeholder="أدخل رمز التحقق">
            <button type="button" class="auth-popup-verify-btn">تحقق</button>
          </div>
          <div class="auth-popup-otp-status"></div>
        </div>
        <div class="auth-popup-email-row">
          <label>البريد الإلكتروني *</label>
          <input type="text" name="email" placeholder="أدخل بريدك الإلكتروني">
        </div>
        <label class="auth-popup-optin"><input type="checkbox" name="notify_optin" value="1" checked> أريد استقبال تنبيهات البريد والواتساب للفرص المناسبة</label>
        <label class="auth-popup-accept"><input type="checkbox" name="accept_terms" value="1" required> <span>أوافق على <a href="terms.php" target="_blank">الشروط والأحكام</a> و<a href="privacy.php" target="_blank">سياسة الخصوصية</a></span></label>
        <button type="submit">متابعة</button>
      </form>
      <button type="button" class="auth-popup-switch" data-auth-target="login">لديك حساب بالفعل؟ سجل الدخول</button>
    </div>
    <div class="auth-popup-panel" data-auth-panel="login">
      <h3>مرحباً بعودتك</h3>
      <a href="google-login.php" class="auth-popup-google"><svg class="auth-popup-google-mark" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.482h4.844a4.14 4.14 0 0 1-1.797 2.716v2.258h2.909c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.468-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A8.999 8.999 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.963 10.707A5.41 5.41 0 0 1 3.681 9c0-.592.102-1.167.282-1.707V4.961H.956A8.997 8.997 0 0 0 0 9c0 1.452.347 2.827.956 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.441 1.346l2.581-2.582C13.464.891 11.427 0 9 0A8.999 8.999 0 0 0 .956 4.961l3.007 2.332C4.672 5.164 6.656 3.579 9 3.579z"/></svg> تسجيل الدخول بواسطة Google</a>
      <div class="auth-popup-divider"><span>أو</span></div>
      <form method="POST" action="login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI'] ?? 'index.php'); ?>" class="auth-popup-form">
        <label>البريد الإلكتروني *</label>
        <input type="text" name="email" placeholder="أدخل البريد الإلكتروني" required>
        <label>كلمة المرور *</label>
        <input type="password" name="pass" placeholder="أدخل كلمة المرور" required>
        <label class="auth-popup-optin"><input type="checkbox" name="notify_optin" value="1" checked> تذكر بيانات الدخول واستقبال التنبيهات المناسبة</label>
        <button type="submit" name="login" value="1">تسجيل الدخول</button>
      </form>
      <a href="forgot-password.php" class="auth-popup-link">هل نسيت كلمة المرور؟</a>
      <button type="button" class="auth-popup-switch" data-auth-target="signup">إنشاء حساب جديد</button>
    </div>
  </div>
</div>
<div class="otp-verify-overlay" id="otpVerifyPopup" aria-hidden="true">
  <div class="otp-verify-card" role="dialog" aria-modal="true" aria-labelledby="otpVerifyTitle">
    <button type="button" class="otp-verify-close" aria-label="إغلاق">&times;</button>
    <h3 id="otpVerifyTitle">التحقق من رقم جوالك</h3>
    <p>للحفاظ على أمان حسابك، يرجى إدخال كلمة المرور المؤقتة (OTP) التي أرسلناها عبر واتساب إلى الرقم أدناه.</p>
    <div class="otp-verify-mobile" id="otpVerifyMobile"></div>
    <label class="otp-verify-label">كلمة المرور لمرة واحدة *</label>
    <input class="otp-verify-digit" inputmode="numeric" autocomplete="one-time-code" maxlength="6" aria-label="رمز التحقق المكون من ستة أرقام" placeholder="أدخل رمز التحقق">
    <button type="button" class="otp-verify-resend">إعادة إرسال كلمة المرور لمرة واحدة (OTP)</button>
    <div class="otp-verify-status"></div>
    <button type="button" class="otp-verify-submit">متابعة</button>
  </div>
</div>
<script>
  (function () {
      var verified = <?php echo (!empty($_SESSION['otp_verified']) || !empty($_SESSION['uid_indm'])) ? 'true' : 'false'; ?>;
      window.egyptmartOtpVerified = verified;
      var pending = null;
      var otpPopupRequestId = '';
      var otpPopupMobile = '';
      function qs(selector, root) { return (root || document).querySelector(selector); }
      function qsa(selector, root) { return Array.prototype.slice.call((root || document).querySelectorAll(selector)); }
      var countryCodes = [["af","93"],["ax","358"],["al","355"],["dz","213"],["as","1"],["ad","376"],["ao","244"],["ai","1"],["ag","1"],["ar","54"],["am","374"],["aw","297"],["ac","247"],["au","61"],["at","43"],["az","994"],["bs","1"],["bh","973"],["bd","880"],["bb","1"],["by","375"],["be","32"],["bz","501"],["bj","229"],["bm","1"],["bt","975"],["bo","591"],["ba","387"],["bw","267"],["br","55"],["io","246"],["vg","1"],["bn","673"],["bg","359"],["bf","226"],["bi","257"],["kh","855"],["cm","237"],["ca","1"],["cv","238"],["bq","599"],["ky","1"],["cf","236"],["td","235"],["cl","56"],["cn","86"],["cx","61"],["cc","61"],["co","57"],["km","269"],["cg","242"],["cd","243"],["ck","682"],["cr","506"],["ci","225"],["hr","385"],["cu","53"],["cw","599"],["cy","357"],["cz","420"],["dk","45"],["dj","253"],["dm","1"],["do","1"],["ec","593"],["eg","20"],["sv","503"],["gq","240"],["er","291"],["ee","372"],["sz","268"],["et","251"],["fk","500"],["fo","298"],["fj","679"],["fi","358"],["fr","33"],["gf","594"],["pf","689"],["ga","241"],["gm","220"],["ge","995"],["de","49"],["gh","233"],["gi","350"],["gr","30"],["gl","299"],["gd","1"],["gp","590"],["gu","1"],["gt","502"],["gg","44"],["gn","224"],["gw","245"],["gy","592"],["ht","509"],["hn","504"],["hk","852"],["hu","36"],["is","354"],["in","91"],["id","62"],["ir","98"],["iq","964"],["ie","353"],["im","44"],["il","972"],["it","39"],["jm","1"],["jp","81"],["je","44"],["jo","962"],["kz","7"],["ke","254"],["ki","686"],["xk","383"],["kw","965"],["kg","996"],["la","856"],["lv","371"],["lb","961"],["ls","266"],["lr","231"],["ly","218"],["li","423"],["lt","370"],["lu","352"],["mo","853"],["mg","261"],["mw","265"],["my","60"],["mv","960"],["ml","223"],["mt","356"],["mh","692"],["mq","596"],["mr","222"],["mu","230"],["yt","262"],["mx","52"],["fm","691"],["md","373"],["mc","377"],["mn","976"],["me","382"],["ms","1"],["ma","212"],["mz","258"],["mm","95"],["na","264"],["nr","674"],["np","977"],["nl","31"],["nc","687"],["nz","64"],["ni","505"],["ne","227"],["ng","234"],["nu","683"],["nf","672"],["kp","850"],["mk","389"],["mp","1"],["no","47"],["om","968"],["pk","92"],["pw","680"],["ps","970"],["pa","507"],["pg","675"],["py","595"],["pe","51"],["ph","63"],["pl","48"],["pt","351"],["pr","1"],["qa","974"],["re","262"],["ro","40"],["ru","7"],["rw","250"],["ws","685"],["sm","378"],["st","239"],["sa","966"],["sn","221"],["rs","381"],["sc","248"],["sl","232"],["sg","65"],["sx","1"],["sk","421"],["si","386"],["sb","677"],["so","252"],["za","27"],["kr","82"],["ss","211"],["es","34"],["lk","94"],["bl","590"],["sh","290"],["kn","1"],["lc","1"],["mf","590"],["pm","508"],["vc","1"],["sd","249"],["sr","597"],["sj","47"],["se","46"],["ch","41"],["sy","963"],["tw","886"],["tj","992"],["tz","255"],["th","66"],["tl","670"],["tg","228"],["tk","690"],["to","676"],["tt","1"],["tn","216"],["tr","90"],["tm","993"],["tc","1"],["tv","688"],["ug","256"],["ua","380"],["ae","971"],["gb","44"],["us","1"],["uy","598"],["vi","1"],["uz","998"],["vu","678"],["va","39"],["ve","58"],["vn","84"],["wf","681"],["eh","212"],["ye","967"],["zm","260"],["zw","263"]];
      function countryFlag(iso) {
          return String(iso || '').toUpperCase().replace(/[A-Z]/g, function (letter) {
              return String.fromCodePoint(127397 + letter.charCodeAt(0));
          });
      }
      function populateCountryCodes() {
          var select = qs('#authPopup select[name="mobile_country_code"]');
          if (!select || select.options.length > 1) return;
          select.innerHTML = '';
          var arabicNames = typeof Intl !== 'undefined' && Intl.DisplayNames ? new Intl.DisplayNames(['ar'], {type: 'region'}) : null;
          var englishNames = typeof Intl !== 'undefined' && Intl.DisplayNames ? new Intl.DisplayNames(['en'], {type: 'region'}) : null;
          countryCodes.forEach(function (item) {
              var option = document.createElement('option');
              option.value = item[1];
              option.setAttribute('data-country', item[0]);
              var iso = item[0].toUpperCase();
              var arabic = arabicNames ? arabicNames.of(iso) : iso;
              var english = englishNames ? englishNames.of(iso) : iso;
              option.setAttribute('data-arabic', arabic);
              option.setAttribute('data-english', english);
              option.textContent = countryFlag(item[0]) + ' +' + item[1] + ' ' + arabic;
              if (item[0] === 'eg') option.selected = true;
              select.appendChild(option);
          });
          buildCountryList();
          updateCountryToggle();
      }
      function buildCountryList() {
          var select = qs('#authPopup select[name="mobile_country_code"]');
          var list = qs('#authPopup .auth-country-list');
          if (!select || !list || list.children.length) return;
          Array.prototype.forEach.call(select.options, function (option) {
              var button = document.createElement('option');
              button.value = option.value;
              button.className = 'auth-country-option';
              button.setAttribute('role', 'option');
              button.setAttribute('data-value', option.value);
              button.setAttribute('data-country', option.getAttribute('data-country') || '');
              button.setAttribute('data-search', [
                  option.textContent,
                  option.getAttribute('data-arabic') || '',
                  option.getAttribute('data-english') || '',
                  option.getAttribute('data-country') || '',
                  '+' + option.value
              ].join(' ').toLowerCase());
              button.textContent = option.textContent;
              list.appendChild(button);
          });
      }
      function updateCountryToggle() {
          var select = qs('#authPopup select[name="mobile_country_code"]');
          var label = qs('#authPopup .auth-country-toggle span');
          if (select && label && select.options.length) label.textContent = select.options[select.selectedIndex].textContent;
      }
      function closeCountryMenu() {
          var menu = qs('#authPopup .auth-country-menu');
          var toggle = qs('#authPopup .auth-country-toggle');
          if (menu) menu.classList.remove('is-open');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
      }
      function filterCountries(query) {
          var term = String(query || '').toLowerCase().replace(/^\s+|\s+$/g, '');
          var visible = 0;
          qsa('#authPopup .auth-country-option').forEach(function (option) {
              var show = !term || (option.getAttribute('data-search') || '').indexOf(term) !== -1;
              option.hidden = !show;
              if (show) visible++;
          });
          var empty = qs('#authPopup .auth-country-empty');
          if (empty) empty.style.display = visible ? 'none' : 'block';
      }
      function fullMobile() {
          var code = String((qs('#authPopup select[name="mobile_country_code"]') || {}).value || '').replace(/\D/g, '');
          var local = String((qs('#authPopup input[name="mobile_local"]') || {}).value || '').replace(/\D/g, '').replace(/^0+/, '');
          var full = code + local;
          var hidden = qs('#authPopup input[name="mobile"]');
          if (hidden) hidden.value = full;
          return full;
      }
      function showPopup(tab) {
          var popup = qs('#authPopup');
          if (!popup) return;
          popup.className = 'auth-popup-overlay is-open';
          popup.setAttribute('aria-hidden', 'false');
          switchTab(tab || 'signup');
      }
      function hidePopup() {
          var popup = qs('#authPopup');
          if (!popup) return;
          popup.className = 'auth-popup-overlay';
          popup.setAttribute('aria-hidden', 'true');
      }
      function switchTab(tab) {
          qsa('#authPopup .auth-popup-tab').forEach(function (item) {
              item.classList.toggle('active', item.getAttribute('data-auth-target') === tab);
          });
          qsa('#authPopup .auth-popup-panel').forEach(function (item) {
              item.classList.toggle('active', item.getAttribute('data-auth-panel') === tab);
          });
          updateContinue();
      }
      function updateContinue() {
          var signup = qs('#authPopup .auth-popup-panel[data-auth-panel="signup"]');
          var submit = qs('button[type="submit"]', signup);
          var method = qs('#authPopup input[name="home_join_method"]:checked');
          var block = signup && signup.classList.contains('active') && method && method.value === 'mobile' && !window.egyptmartOtpVerified;
          if (submit) {
              submit.disabled = !!block;
              submit.classList.toggle('is-disabled', !!block);
          }
      }
      function status(message) {
          var box = qs('#authPopup .auth-popup-otp-status');
          if (box) {
              box.style.display = 'block';
              box.textContent = message;
          }
      }
      function maskedMobile(mobile) {
          var digits = String(mobile || '').replace(/\D/g, '');
          if (digits.length < 7) return digits;
          return '+' + digits.slice(0, 3) + ' ' + digits.slice(3, 5) + ' *** ' + digits.slice(-4);
      }
      function otpPopupStatus(message) {
          var box = qs('#otpVerifyPopup .otp-verify-status');
          if (!box) return;
          box.textContent = message || '';
          box.style.display = message ? 'block' : 'none';
      }
      function openOtpPopup(mobile, requestId) {
          var popup = qs('#otpVerifyPopup');
          if (!popup) return;
          otpPopupMobile = mobile;
          otpPopupRequestId = requestId || '';
          var mobileBox = qs('#otpVerifyMobile');
          if (mobileBox) mobileBox.textContent = maskedMobile(mobile);
          var codeInput = qs('#otpVerifyPopup .otp-verify-digit');
          if (codeInput) codeInput.value = '';
          otpPopupStatus('');
          popup.classList.add('is-open');
          popup.setAttribute('aria-hidden', 'false');
          if (codeInput) codeInput.focus();
      }
      function closeOtpPopup() {
          var popup = qs('#otpVerifyPopup');
          if (!popup) return;
          popup.classList.remove('is-open');
          popup.setAttribute('aria-hidden', 'true');
      }
      function request(url, data, done) {
          var xhr = new XMLHttpRequest();
          xhr.open('POST', url, true);
          xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
          xhr.onreadystatechange = function () {
              if (xhr.readyState !== 4) return;
              var json = {};
              try { json = JSON.parse(xhr.responseText || '{}'); } catch (e) {}
              done(json, xhr.status);
          };
          xhr.send(Object.keys(data).map(function (key) {
              return encodeURIComponent(key) + '=' + encodeURIComponent(data[key] || '');
          }).join('&'));
      }
      function continueAction() {
          var action = pending;
          pending = null;
          if (!action) return;
          if (action.type === 'form' && action.form) {
              hidePopup();
              action.form.submit();
              return;
          }
          if (action.type === 'link' && action.element) {
              hidePopup();
              setTimeout(function () {
                  action.element.click();
              }, 80);
          }
      }
      document.addEventListener('click', function (e) {
          var countryToggle = e.target.closest ? e.target.closest('.auth-country-toggle') : null;
          if (countryToggle) {
              e.preventDefault();
              var countryMenu = qs('#authPopup .auth-country-menu');
              var opening = countryMenu && !countryMenu.classList.contains('is-open');
              closeCountryMenu();
              if (opening && countryMenu) {
                  countryMenu.classList.add('is-open');
                  countryToggle.setAttribute('aria-expanded', 'true');
                  var countrySearch = qs('#authPopup .auth-country-search');
                  if (countrySearch) {
                      countrySearch.value = '';
                      filterCountries('');
                      countrySearch.focus();
                  }
              }
              return;
          }
          var countryOption = e.target.closest ? e.target.closest('.auth-country-option') : null;
          if (countryOption) {
              e.preventDefault();
              var countrySelect = qs('#authPopup select[name="mobile_country_code"]');
              if (countrySelect) countrySelect.value = countryOption.getAttribute('data-value') || '';
              updateCountryToggle();
              fullMobile();
              closeCountryMenu();
              return;
          }
          if (!e.target.closest || !e.target.closest('.auth-country-picker')) closeCountryMenu();
          var trigger = e.target.closest ? e.target.closest('.open-auth-popup') : null;
          if (trigger) {
              e.preventDefault();
              showPopup(trigger.getAttribute('data-auth-tab') || 'signup');
              return;
          }
          var close = e.target.closest ? e.target.closest('.auth-popup-close') : null;
          if (close) {
              e.preventDefault();
              hidePopup();
              return;
          }
          var tab = e.target.closest ? e.target.closest('.auth-popup-tab, .auth-popup-switch') : null;
          if (tab && tab.getAttribute('data-auth-target')) {
              e.preventDefault();
              switchTab(tab.getAttribute('data-auth-target'));
              return;
          }
          var otpButton = e.target.closest ? e.target.closest('.auth-popup-otp-btn') : null;
          if (otpButton) {
              e.preventDefault();
              e.stopImmediatePropagation();
              var mobile = fullMobile();
              if (mobile.length < 7) {
                  var mobileInput = qs('#authPopup input[name="mobile_local"]');
                  if (mobileInput) mobileInput.focus();
                  return;
              }
              otpButton.textContent = 'جارٍ الإرسال...';
              otpButton.disabled = true;
              status('جارٍ إرسال رمز التحقق عبر واتساب...');
              request('send_otp.php', {mobile: mobile}, function (res) {
                  var codeInput = qs('#authPopup input[name="otp_code"]');
                  if (res.status === 'success') {
                      if (codeInput) {
                          codeInput.setAttribute('data-request-id', res.request_id || '');
                          codeInput.value = '';
                          codeInput.focus();
                      }
                      otpButton.textContent = 'إعادة إرسال الرمز';
                      otpButton.disabled = false;
                      status('تم إرسال رمز التحقق عبر واتساب. أدخل الرمز للمتابعة.');
                      openOtpPopup(mobile, res.request_id || '');
                  } else {
                      otpButton.textContent = 'إرسال الرمز';
                      otpButton.disabled = false;
                      status(res.msg || 'تعذر إرسال رمز التحقق. يرجى المحاولة مرة أخرى.');
                  }
                  updateContinue();
              });
              return;
          }
          var verifyButton = e.target.closest ? e.target.closest('.auth-popup-verify-btn') : null;
          if (verifyButton) {
              e.preventDefault();
              e.stopImmediatePropagation();
              var mobileValue = fullMobile();
              var code = (qs('#authPopup input[name="otp_code"]') || {}).value || '';
              var codeField = qs('#authPopup input[name="otp_code"]');
              code = code.replace(/^\s+|\s+$/g, '');
              if (!code) {
                  if (codeField) codeField.focus();
                  return;
              }
              verifyButton.textContent = 'جارٍ التحقق...';
              verifyButton.disabled = true;
              request('verify_otp.php', {
                  request_id: codeField ? codeField.getAttribute('data-request-id') || '' : '',
                  mobile: mobileValue,
                  code: code
              }, function (res) {
                  if (res.status === 'success') {
                      verified = true;
                      window.egyptmartOtpVerified = true;
                      window.dispatchEvent(new CustomEvent('egyptmart:otp-verified'));
                      verifyButton.textContent = 'تم التحقق';
                      status('تم التحقق من رقم الجوال. جارٍ المتابعة...');
                      updateContinue();
                      if (res.logged_in && res.redirect) {
                          window.location.href = res.redirect;
                          return;
                      }
                      setTimeout(continueAction, 450);
                  } else {
                      verifyButton.textContent = 'تحقق';
                      verifyButton.disabled = false;
                      status(res.msg || 'رمز التحقق غير صحيح. حاول مرة أخرى.');
                  }
              });
              return;
          }
          var otpPopupClose = e.target.closest ? e.target.closest('.otp-verify-close') : null;
          if (otpPopupClose) {
              e.preventDefault();
              closeOtpPopup();
              return;
          }
          var otpPopupResend = e.target.closest ? e.target.closest('.otp-verify-resend') : null;
          if (otpPopupResend) {
              e.preventDefault();
              var resendButton = qs('#authPopup .auth-popup-otp-btn');
              if (resendButton) resendButton.click();
              return;
          }
          var otpPopupSubmit = e.target.closest ? e.target.closest('.otp-verify-submit') : null;
          if (otpPopupSubmit) {
              e.preventDefault();
              var popupCode = String((qs('#otpVerifyPopup .otp-verify-digit') || {}).value || '').replace(/\D/g, '');
              if (popupCode.length !== 6) {
                  otpPopupStatus('يرجى إدخال رمز التحقق المكون من ستة أرقام.');
                  return;
              }
              otpPopupSubmit.disabled = true;
              otpPopupSubmit.textContent = 'جارٍ التحقق...';
              request('verify_otp.php', {
                  request_id: otpPopupRequestId,
                  mobile: otpPopupMobile,
                  code: popupCode
              }, function (res) {
                  if (res.status === 'success') {
                      verified = true;
                      window.egyptmartOtpVerified = true;
                      window.dispatchEvent(new CustomEvent('egyptmart:otp-verified'));
                      var inlineCode = qs('#authPopup input[name="otp_code"]');
                      if (inlineCode) inlineCode.value = popupCode;
                      status('تم التحقق من رقم الجوال بنجاح.');
                      updateContinue();
                      otpPopupSubmit.textContent = 'تم التحقق';
                      if (res.logged_in && res.redirect) {
                          window.location.href = res.redirect;
                          return;
                      }
                      setTimeout(function () {
                          closeOtpPopup();
                          var signupForm = qs('#authPopup .auth-popup-panel[data-auth-panel="signup"] form');
                          if (pending) continueAction();
                          else if (res.redirect) {
                              window.location.href = res.redirect;
                          } else if (signupForm) {
                              fullMobile();
                              signupForm.submit();
                          }
                      }, 450);
                  } else {
                      otpPopupSubmit.disabled = false;
                      otpPopupSubmit.textContent = 'متابعة';
                      otpPopupStatus(res.msg || 'رمز التحقق غير صحيح أو منتهي الصلاحية.');
                  }
              });
              return;
          }
          var actionLink = e.target.closest ? e.target.closest('a[href*="post-buy-req.php"], a[href*="product-sel-cat.php"], a[href*="manage-buylead-alert.php"], a[href*="manage-selloffer-alert.php"], a[href*="quotationRequest"], a[href*="sendenquiry-form.php"], a[href*="sendLeadEnquiry"], a[href*="sendmessage"], a[href*="enquiry"], a[href*="enq"], a.ajax, .btn-enquiry, .send-enquiry, .sendEnquiry, .contact-seller, .contactSupplier, .post-buy-req-btn') : null;
          if (actionLink && !verified && !actionLink.closest('#authPopup') && !actionLink.closest('.mobile-quick-strip')) {
              e.preventDefault();
              pending = {type: 'link', element: actionLink};
              showPopup('signup');
          }
      }, true);
      document.addEventListener('input', function (e) {
          if (e.target.classList && e.target.classList.contains('auth-country-search')) {
              filterCountries(e.target.value);
              return;
          }
          if (e.target.classList && e.target.classList.contains('otp-verify-digit')) {
              e.target.value = String(e.target.value || '').replace(/\D/g, '').slice(0, 6);
          }
          if (e.target.name === 'mobile_local' || e.target.name === 'mobile_country_code') fullMobile();
      });
      document.addEventListener('submit', function (e) {
          var form = e.target;
          if (!form) return;
          if (form.closest('#authPopup')) {
              var signup = form.closest('.auth-popup-panel[data-auth-panel="signup"]');
              var method = qs('#authPopup input[name="home_join_method"]:checked');
              if (signup && method && method.value === 'mobile' && !verified) {
                  e.preventDefault();
                  e.stopImmediatePropagation();
                  status('Please verify the OTP sent on WhatsApp before continuing.');
                  var codeInput = qs('#authPopup input[name="otp_code"]');
                  if (codeInput) codeInput.focus();
              }
              return;
          }
          var action = String(form.getAttribute('action') || '');
          var isSearch = action.indexOf('search.php') !== -1 || form.id === 'hdr_frm' || form.id === 'homeSmartSearchForm' || form.name === 'searchForm' || form.name === 'searchForm2';
          if (isSearch && !verified) {
              e.preventDefault();
              e.stopImmediatePropagation();
              pending = {type: 'form', form: form};
              showPopup('signup');
          }
      }, true);
      document.addEventListener('change', function (e) {
          if (e.target && e.target.classList && e.target.classList.contains('auth-country-list')) {
              var resultOption = e.target.options[e.target.selectedIndex];
              var countrySelect = qs('#authPopup select[name="mobile_country_code"]');
              if (resultOption && countrySelect) countrySelect.value = resultOption.value;
              updateCountryToggle();
              fullMobile();
              closeCountryMenu();
              return;
          }
          if (e.target && e.target.name === 'home_join_method') {
              var isMobile = e.target.value === 'mobile';
              var mobileRow = qs('#authPopup .auth-popup-mobile-row');
              var emailRow = qs('#authPopup .auth-popup-email-row');
              if (mobileRow) mobileRow.classList.toggle('is-active', isMobile);
              if (emailRow) emailRow.style.display = isMobile ? 'none' : 'block';
              updateContinue();
          }
      }, true);
      document.addEventListener('DOMContentLoaded', function () {
          populateCountryCodes();
          fullMobile();
          updateContinue();
      });
      populateCountryCodes();
      setTimeout(updateContinue, 0);
  })();
</script>
<?php } ?>
<div class="maincontainertop ">
  <!-- page-header start -->
  <?php
    $yasirCurrentPage = basename(parse_url($_SERVER['REQUEST_URI'] ?? 'index.php', PHP_URL_PATH));
    $yasirIsHomepage = ($yasirCurrentPage === '' || $yasirCurrentPage === 'index.php');
  ?>
  <header class="page-header<?php echo $yasirIsHomepage ? ' home-header' : ''; ?>" style="background-image:linear-gradient(rgba(255,255,255, 0.45), rgba(255,255,255, 0.45)),url(images/headerbg.jpg); background-repeat:no-repeat; background-size:cover;">
      <video class="home-header-video-bg" autoplay muted loop playsinline preload="auto">
        <source src="uploads/home-hero-video.mp4" type="video/mp4">
      </video>
      
      
           <div class="headertop-custom-box">

     <div class="headertop-custom-box-left" style="position: absolute; top: 5px; left: 10px; z-index: 99; width: 250px;">
    
</div>
      <!--      <div class="headertop-custom-box-left">
        <img alt="fdfdf" src="images/ page-header-col1_mapbg.jpg " class="globeimg1">
        
        </div>-->
      <div class="headertop-custom-box-middle">
        <div class="page-header-col1-row1" style="padding:0;">
          <!-- col-md-9 start -->
          <div class="page-header-col1-row1-col1 col-xs-6">
            <div class="page-header-col1-row1-col1_row">
             
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
                <img src="images/country_flag/Global$download.png" style="height:25px !important;width:25px!important;" alt="Global" class="w4"
                  align="top" height="30" width="30"/>
                <?php } ?>
              </div>
              <div class="page-header-col1-row1-col1-row2-form">
                <div>
                  <a class="un" onclick="document.getElementById('changeLocation').classList.toggle('is-open'); return false;" style="border-left:none; font-size: 9px; color:#0f2399; 
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
           
          </div>
          <!-- page-header-col1-row1-col2 close// -->
          <div class="page-header-col1-row1-col3">
           
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
                
                <label class="radio Buyer-radio" style="font-size: 16px">
                <input id="radio2" type="radio" name="radios" value="manage-buylead-alert.php" checked >
                
             </div>
              
              <h2 class="justclick"title="Just a Click Away"><br>   </h2>
            </div> 
          </div>
          <!-- page-header-col1-row1-col4 close// --> 
        </div>
        <div class=" header-mid header-mid-custom-box">
          <div class="home-main-caption">
            <span>تربط بين المورد والمشترى وتوفر مستلزمات الأعمال</span>
            <span>ترسل طلبات شراء للموردين</span>
            <span>تنشر المنتجات على وسائل التواصل</span>
          </div>
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
	                        if ($("#smartSearchMode").hasClass("active")) {
	                            $("#suggesstionBoxs").hide().html("");
	                            return;
	                        }
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
	                    $("#smartSearchMode").click(function () {
	                        $(this).toggleClass("active");
	                        var active = $(this).hasClass("active");
	                        $("#search_mode").prop("checked", active).val("scenario");
	                        if (active) {
	                            $("#search-box1").attr("placeholder", "اكتب طلبك كاملا: أحتاج شراء كمية كبيرة من الفول مع توصيل سريع");
	                            $("#suggesstionBoxs").hide().html("");
	                        } else {
	                            $("#suggesstionBoxs").hide().html("");
	                            $("#search-box1").attr("placeholder", "  إبحــث بالعربى أو الإنجليزى >> منتجات وخدمات >> مصر والعالم ");
	                        }
	                    });
		                    $("#hdr_frm").submit(function (e) {
		                        if (!$("#smartSearchMode").hasClass("active")) {
		                            return true;
		                        }
		                        e.preventDefault();
		                        submitSmartSearchRequest();
		                        return false;
		                    });
		                });
		                function scenarioKeywords(text) {
		                    return $.trim(String(text || "").replace(/\s+/g, " "));
		                }
		                function scenarioSearchUrl(text) {
		                    return "search.php?keywords=" + encodeURIComponent(scenarioKeywords(text)) + "&rctyp=Products&search_mode=scenario";
		                }
		                function submitSmartSearchRequest() {
	                    var requestText = $.trim($("#search-box1").val());
	                    var searchType = $("#rctyp").val() || $("ul.search_tab li.active").attr("alt") || "Products";
	                    if (!requestText) {
	                        alert("Please enter your request.");
	                        return false;
	                    }
	                    $("#smartSearchMode").addClass("active loading").attr("aria-busy", "true");
	                    $(".loading-text").removeClass("hide").addClass("show");
	                    $("#suggesstionBoxs").show().html('<div class="smart-scenario-list"><span>جارى تحليل طلبك وعرض نتائج السوق...</span></div>');
	                    $.ajax({
	                        type: "POST",
	                        url: "ai-search.php",
	                        dataType: "json",
	                        data: {
	                            request_text: requestText,
	                            rctyp: searchType,
	                            page_url: window.location.href
	                        },
	                        success: function (response) {
	                            if (response && response.success && response.redirect_url) {
	                                window.location.href = response.redirect_url;
	                                return;
	                            }
		                            window.location.href = scenarioSearchUrl(requestText);
		                        },
		                        error: function () {
		                            window.location.href = scenarioSearchUrl(requestText);
		                        }
		                    });
	                    return false;
	                }
	                function selectCountry(val) {
                    //alert(val); return false; 
                    $("#search-box1").val(val);
                    $("#suggesstionBoxs").hide();
                }
              </script>
              <div class="page-header-col1-row2-col2-form" style="margin:0!important;max-width:none!important;width:100%!important;">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs search_tab" role="tablist" id="rctyp">
                  <li role="presentation" class="active" alt="Products"><a href="#products" alt="Products" class="searchTabs" aria-controls="products" role="tab" data-toggle="tab"title="Find Products & Services" >  إبحــث عن أى منتجــات </a></li>
                  <li role="presentation" alt="Suppliers"><a href="#supplier" alt="Suppliers" class="searchTabs" aria-controls="supplier" role="tab" data-toggle="tab"title="Find Suppliers" >إبحـث عن شركات وموردين</a></li>
                </ul>
                <!-- Tab panes -->   
                <div class="tab-content search_cont" style="float:none!important;margin:0!important;max-width:none!important;width:100%!important;">
                  <div role="tabpanel" class="tab-pane active" id="supplier" style="float:none!important;margin:0!important;max-width:none!important;width:100%!important;">
	                    <form autocomplete="off" name="searchForm" action="search.php" onsubmit="return typeof validsearch==='function'?validsearch():true;" method="GET" id="hdr_frm" style="display:flex!important;float:none!important;height:58px!important;margin:0!important;max-width:none!important;width:100%!important;">
	                      <input type="checkbox" id="search_mode" name="search_mode" value="scenario" class="scenario-mode-checkbox"/>
	                      <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                        <option value="Suppliers">Suppliers</option>
                        <option  value="Products" selected>Products</option>
                        <option value="buy_lead">Buy Leads</option>
                        <option value="tender">Tender</option>
                        <!--<option value="auction">Auction</option>-->
                      </select>
	                      <input type="text" id="search-box1" name="keywords" style="font-weight:900;text-color:black;text-align:center; border:1px solid;box-shadow: 1px 2px 4px #595959;" placeholder="  إبحــث بالعربى أو الإنجليزى >> منتجات وخدمات >> مصر والعالم "
	                        class="page-header-col1-row2-col2-form-input topsearch_placeholder_cont search-box"  onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo htmlspecialchars($_GET['keywords'] ?? ''); ?>"
	                        style="border: 1px solid #000;width:90%" />
	                      <label for="search_mode" id="smartSearchMode" class="smart-search-mode" title="AI search" aria-label="AI search" style="animation:none!important;background:transparent!important;background-color:transparent!important;background-image:none!important;border:0!important;box-shadow:none!important;opacity:1!important;transform:none!important;transition:none!important;"><img src="images/yasir-ai-lens-red.svg" alt="AI" style="animation:none!important;background:transparent!important;background-color:transparent!important;box-shadow:none!important;filter:none!important;opacity:1!important;transform:none!important;transition:none!important;"></label>
	                      <span class="loading-text hide"><img src="/assets/img/Spinner-200px.gif" style="width: 48px;height: 48px;"></span>
	                      <div id="suggesstionBoxs" class="suggesstionBoxs"></div>
	                      <input type="submit" id="btnSearch" value="" class="page-header-col1-row2-col2-form-btn" style="background:#079448 url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.8' stroke-linecap='round'%3E%3Ccircle cx='10.5' cy='10.5' r='6.5'/%3E%3Cpath d='M15.5 15.5L21 21'/%3E%3C/svg%3E&quot;) center/31px 31px no-repeat!important;border:0!important;border-radius:12px 0 0 12px!important;height:58px!important;left:auto!important;margin:0!important;min-width:62px!important;position:static!important;right:auto!important;width:62px!important;"/>
	                    </form>
	                  </div>
	                </div>
	                <div class="clear"></div>
	              </div>
	            </div>
            <div class="home-fixed-points" aria-label="خدمات سوق مصر">
              <span>أرسل صور منتجاتك عبر WhatsApp</span>
              <span>ننشئ رابط منتجاتك الموحد</span>
              <span>وننشر منتجاتك تلقائياً على EgyptMart وFacebook وInstagram وLinkedIn وTelegram</span>
              <span>وتستقبل طلبات شراء منتجاتك على واتسابك</span>
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
           
          </div>
          <div class="page-header-col2-intro">
            <div class="page-header-col2-intro-pic">
             
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
    <style>
      .desktop-site-logo,
      .home-fixed-caption,
      .home-fixed-points {
        display: none;
      }
      @media (min-width: 769px) {
        .mobile-quick-strip {
          align-items: center !important;
          direction: rtl !important;
          display: flex !important;
          height: 46px !important;
          justify-content: flex-start !important;
          padding: 0 14px !important;
        }
        .mobile-quick-strip a {
          background: #103944 !important;
          border: 1px solid rgba(255,255,255,.25) !important;
          border-radius: 4px !important;
          color: #fff !important;
          font-family: GE SS Two, Tajawal, sans-serif !important;
          font-size: 13px !important;
          font-weight: 800 !important;
          margin-left: 6px !important;
          padding: 7px 14px !important;
        }
        #topbar {
          direction: rtl !important;
          height: 48px !important;
          min-height: 48px !important;
          position: relative !important;
        }
        #topbar .header-fixed-container {
          align-items: center !important;
          direction: rtl !important;
          display: grid !important;
          grid-template-columns: minmax(300px, 1fr) 300px minmax(220px, 1fr) !important;
          height: 48px !important;
          position: relative !important;
          width: 100% !important;
        }
        #topbar .top-lft {
          direction: rtl !important;
          float: none !important;
          grid-column: 1 !important;
          justify-self: end !important;
          text-align: right !important;
          width: auto !important;
        }
        #topbar .top-lft > ul {
          align-items: center !important;
          direction: rtl !important;
          display: flex !important;
          justify-content: flex-start !important;
          margin: 0 !important;
          padding: 0 12px !important;
          white-space: nowrap !important;
        }
        #topbar .top-mid {
          float: none !important;
          grid-column: 2 !important;
          justify-self: center !important;
          padding: 0 !important;
          width: 300px !important;
        }
        #topbar .top-mid > ul,
        #topbar .top-mid > ul > span {
          align-items: center !important;
          direction: rtl !important;
          display: flex !important;
          justify-content: center !important;
          margin: 0 !important;
          padding: 0 !important;
        }
        #topbar .top-mid .dropdown1 {
          margin: 0 10px !important;
        }
        #topbar .top-rht {
          display: none !important;
        }
        #topbar .desktop-site-logo {
          align-items: center !important;
          display: flex !important;
          grid-column: 3 !important;
          height: 44px !important;
          justify-self: start !important;
          margin-left: 12px !important;
          overflow: hidden !important;
          width: 190px !important;
        }
        #topbar .desktop-site-logo img {
          display: block !important;
          height: auto !important;
          max-height: 42px !important;
          object-fit: contain !important;
          width: 188px !important;
        }
        .home-header {
          min-height: 560px !important;
          overflow: hidden !important;
          position: relative !important;
        }
        .home-header .headertop-custom-box,
        .home-header .headertop-custom-box-middle,
        .home-header .header-mid-custom-box,
        .home-header .page-header-col1-row2 {
          height: 100% !important;
          inset: 0 !important;
          position: absolute !important;
          width: 100% !important;
        }
        .home-header .page-header-col1-row1 {
          height: 100% !important;
          position: relative !important;
        }
        .home-header .page-header-col1-row1-col1 {
          float: none !important;
          height: auto !important;
          left: 5% !important;
          padding: 0 !important;
          position: absolute !important;
          top: 185px !important;
          width: 180px !important;
          z-index: 12 !important;
        }
        .home-header .page-header-col1-row1-col1-row2-form,
        .home-header #cnlocation {
          text-align: center !important;
        }
        .home-header #cnlocation:before {
          color: #fff !important;
          content: "🌐  Global" !important;
          display: block !important;
          direction: ltr !important;
          font-family: Arial, sans-serif !important;
          font-size: 15px !important;
          font-weight: 800 !important;
          margin-bottom: 5px !important;
          text-shadow: 0 2px 8px rgba(0,0,0,.95) !important;
        }
        .home-header .home-fixed-caption {
          color: #fff !important;
          display: block !important;
          direction: rtl !important;
          font-family: GE SS Two, Tajawal, sans-serif !important;
          font-size: clamp(27px, 3vw, 42px) !important;
          font-weight: 900 !important;
          left: 24% !important;
          line-height: 1.35 !important;
          position: absolute !important;
          right: 7% !important;
          text-align: center !important;
          text-shadow: 0 3px 12px rgba(0,0,0,.9) !important;
          top: 118px !important;
          z-index: 10 !important;
        }
        .home-header .margintop {
          float: none !important;
          left: 24% !important;
          margin: 0 !important;
          padding: 0 !important;
          position: absolute !important;
          right: 7% !important;
          top: 335px !important;
          width: auto !important;
          z-index: 15 !important;
        }
        .home-header .page-header-col1-row2-col2-form {
          margin: 0 auto !important;
          max-width: 900px !important;
          width: 100% !important;
        }
        .home-header #hdr_frm {
          direction: ltr !important;
          display: flex !important;
          height: 58px !important;
          position: relative !important;
          width: 100% !important;
        }
        .home-header #btnSearch {
          flex: 0 0 62px !important;
          height: 58px !important;
          left: 0 !important;
          order: 1 !important;
          position: static !important;
          right: auto !important;
          width: 62px !important;
        }
        .home-header #smartSearchMode {
          align-items: center !important;
          background: #fff !important;
          border: 0 !important;
          display: flex !important;
          flex: 0 0 58px !important;
          height: 58px !important;
          justify-content: center !important;
          left: auto !important;
          order: 2 !important;
          padding: 4px !important;
          position: static !important;
          right: auto !important;
          top: auto !important;
          width: 58px !important;
        }
        .home-header #smartSearchMode img {
          display: block !important;
          height: 48px !important;
          object-fit: contain !important;
          width: 48px !important;
        }
        .home-header #search-box1 {
          flex: 1 1 auto !important;
          height: 58px !important;
          min-width: 0 !important;
          order: 3 !important;
          padding: 0 18px !important;
          width: auto !important;
        }
        .home-header .page-header-col1-row2-col2-form-select {
          flex: 0 0 118px !important;
          height: 58px !important;
          order: 4 !important;
          position: static !important;
          width: 118px !important;
        }
        .home-header .srchBx {
          margin: 18px auto 0 !important;
          max-width: 900px !important;
          position: relative !important;
          width: 100% !important;
        }
        .home-header .srchBx h2,
        .home-header .srchBx h2 span,
        .home-header .srchBx h2 b {
          color: #fff !important;
          font-size: 24px !important;
          text-align: center !important;
          text-shadow: 0 2px 10px rgba(0,0,0,.95) !important;
        }
        .home-header .home-fixed-points {
          bottom: 6px !important;
          direction: rtl !important;
          display: grid !important;
          gap: 10px !important;
          grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
          left: 12% !important;
          position: absolute !important;
          right: 12% !important;
          z-index: 12 !important;
        }
        .home-header .home-fixed-points span {
          background: transparent !important;
          border: 0 !important;
          border-radius: 0 !important;
          color: #fff !important;
          font-size: 14px !important;
          font-weight: 800 !important;
          padding: 6px 10px !important;
          text-align: center !important;
          text-shadow: 0 2px 8px rgba(0,0,0,.95) !important;
        }
        .home-header .post-prod-left,
        .home-header .page-header-col1-row2-col4,
        .home-header .headertop-custom-box-right {
          display: none !important;
        }
      }
      @media (max-width: 768px) {
        .mobile-quick-strip {
          display: none !important;
        }
        #topbar .desktop-site-logo,
        .home-header .home-fixed-caption,
        .home-header .home-fixed-points {
          display: none !important;
        }
      }
    </style>
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
  
  float:left;
  }
  .maincontainertop
  {
  z-index:1003 !important;
  }
  .page-header-col1-row2-col2-form
  {
  
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
      
  });
</script>
<?php  include('style.php'); ?>
<style>
@media (min-width: 769px) {
  html,
  body {
    max-width: 100% !important;
    overflow-x: hidden !important;
  }
  .home-header .headertop-custom-box-middle {
    left: 0 !important;
    margin: 0 !important;
    max-width: none !important;
    right: 0 !important;
    width: 100% !important;
  }
  #topbar .top-mid li[title*="سوق التصدير"] {
    display: none !important;
  }
  .home-header .margintop {
    left: 24% !important;
    max-width: none !important;
    right: 7% !important;
    width: auto !important;
  }
  .home-header .page-header-col1-row2-col2-form {
    max-width: none !important;
    width: 100% !important;
  }
  .home-header .page-header-col1-row1-col1-row2-form > div > a.un {
    align-items: center !important;
    background: rgba(255,255,255,.88) !important;
    border-radius: 12px !important;
    color: #17333b !important;
    cursor: pointer !important;
    display: flex !important;
    font-size: 0 !important;
    height: 22px !important;
    justify-content: center !important;
    margin: 2px auto 0 !important;
    padding: 0 !important;
    width: 34px !important;
  }
  .home-header .page-header-col1-row1-col1-row2-form > div > a.un:after {
    content: "⌄" !important;
    display: block !important;
    font-family: Arial, sans-serif !important;
    font-size: 17px !important;
    line-height: 18px !important;
  }
  .home-header #changeLocation {
    background: #fff !important;
    border: 1px solid #cbd5da !important;
    border-radius: 6px !important;
    box-shadow: 0 5px 18px rgba(0,0,0,.35) !important;
    display: none !important;
    height: auto !important;
    left: 0 !important;
    margin: 0 !important;
    min-width: 230px !important;
    max-height: 230px !important;
    overflow: auto !important;
    padding: 4px 6px !important;
    position: absolute !important;
    right: auto !important;
    top: 48px !important;
    width: 230px !important;
    z-index: 99999 !important;
  }
  .home-header .page-header-col1-row1-col1-row2-form > div:hover #changeLocation,
  .home-header .page-header-col1-row1-col1-row2-form > div:focus-within #changeLocation,
  .home-header #changeLocation.is-open {
    display: block !important;
  }
  .home-header #changeLocation ul {
    margin: 0 !important;
    padding: 0 !important;
  }
  .home-header #changeLocation table,
  .home-header #changeLocation tbody {
    display: block !important;
    width: 100% !important;
  }
  .home-header #changeLocation tr {
    align-items: center !important;
    display: flex !important;
    justify-content: center !important;
    width: 100% !important;
  }
  .home-header #changeLocation td {
    display: block !important;
    flex: 0 0 30px !important;
    padding: 2px !important;
  }
  #topbar .top-mid > ul > span > li > a,
  #topbar .top-lft > ul > li > a,
  #topbar .top-lft > ul > li > span {
    align-items: center !important;
    color: #fff !important;
    display: inline-flex !important;
    font-family: GE SS Two, Tajawal, sans-serif !important;
    font-size: 14px !important;
    min-height: 30px !important;
  }
  #topbar .top-mid .dropdown-menu,
  #topbar .top-lft .dropdown-menu {
    background: #fff !important;
    border: 1px solid #ccd5da !important;
    border-radius: 5px !important;
    box-shadow: 0 10px 28px rgba(0,0,0,.28) !important;
    direction: rtl !important;
    min-width: 245px !important;
    padding: 5px 0 !important;
    text-align: right !important;
    top: 100% !important;
    z-index: 100000 !important;
  }
  #topbar .top-mid .dropdown-menu li,
  #topbar .top-lft .dropdown-menu li {
    display: block !important;
    height: auto !important;
    margin: 0 !important;
    width: 100% !important;
  }
  #topbar .top-mid .dropdown-menu li a,
  #topbar .top-lft .dropdown-menu li a {
    background: #fff !important;
    color: #17262d !important;
    display: block !important;
    font-family: GE SS Two, Tajawal, sans-serif !important;
    font-size: 13px !important;
    line-height: 1.5 !important;
    min-height: 0 !important;
    padding: 9px 13px !important;
    text-align: right !important;
    white-space: normal !important;
  }
  #topbar .top-mid .dropdown-menu li a:hover,
  #topbar .top-lft .dropdown-menu li a:hover {
    background: #07596b !important;
    color: #fff !important;
  }
  #topbar .desktop-site-logo {
    left: 12px !important;
    margin: 0 !important;
    position: absolute !important;
    top: 2px !important;
  }
  #topbar .top-user-name {
    direction: ltr !important;
    display: inline-block !important;
    unicode-bidi: isolate !important;
  }
  .home-header .header-mid.header-mid-custom-box {
    bottom: 0 !important;
    height: 100% !important;
    left: 0 !important;
    margin: 0 !important;
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
    width: 100% !important;
  }
  .home-header .header-mid.header-mid-custom-box .page-header-col1-row2 {
    bottom: 0 !important;
    display: block !important;
    height: 100% !important;
    left: 0 !important;
    margin: 0 !important;
    position: absolute !important;
    right: 0 !important;
    top: 0 !important;
    width: 100% !important;
  }
  .home-header .header-mid.header-mid-custom-box .margintop {
    display: block !important;
    height: auto !important;
    left: 24% !important;
    margin: 0 !important;
    max-width: none !important;
    padding: 0 !important;
    position: absolute !important;
    right: 7% !important;
    top: 335px !important;
    transform: none !important;
    width: auto !important;
  }
  .home-header #hdr_frm,
  .home-header #btnSearch,
  .home-header #smartSearchMode,
  .home-header #search-box1,
  .home-header .page-header-col1-row2-col2-form-select {
    height: 58px !important;
  }
  .home-header #hdr_frm {
    margin: 0 !important;
  }
  .home-header #smartSearchMode,
  .home-header #smartSearchMode.active,
  .home-header #smartSearchMode.loading {
    background: #fff !important;
    border: 0 !important;
    box-shadow: none !important;
  }
  .home-header #smartSearchMode:before,
  .home-header #smartSearchMode:after {
    content: none !important;
    display: none !important;
  }
  .home-header #smartSearchMode img {
    background: #fff !important;
    border: 0 !important;
    border-radius: 0 !important;
    height: 48px !important;
    margin: 0 !important;
    width: 48px !important;
  }
  .home-header .srchBx,
  .home-header .srchBx h2,
  .home-header .srchBx .cd-words-wrapper {
    height: 48px !important;
    line-height: 48px !important;
    overflow: hidden !important;
  }
  .home-header .srchBx .cd-words-wrapper {
    display: block !important;
    position: relative !important;
    width: 100% !important;
  }
  .home-header .srchBx .cd-words-wrapper b {
    font-family: GE SS Two, Tajawal, sans-serif !important;
    font-size: 22px !important;
    font-weight: 800 !important;
    left: 0 !important;
    line-height: 48px !important;
    right: 0 !important;
    text-align: center !important;
    top: 0 !important;
    transform: none !important;
    white-space: nowrap !important;
    width: 100% !important;
  }
  .home-header .srchBx .cd-words-wrapper b.is-hidden {
    display: none !important;
    opacity: 0 !important;
  }
  .home-header .srchBx .cd-words-wrapper b.is-visible {
    display: block !important;
    opacity: 1 !important;
  }
}
@media (max-width: 768px) {
  .mobile-quick-strip,
  #topbar .desktop-site-logo,
  .home-header .home-fixed-caption,
  .home-header .home-fixed-points {
    display: none !important;
  }
}
</style>
<style>
.account-access-button {
  align-items: center !important;
  background: transparent !important;
  border: 0 !important;
  cursor: pointer !important;
  display: inline-flex !important;
  height: 38px !important;
  justify-content: center !important;
  padding: 4px !important;
  width: 38px !important;
}
.account-access-button img {
  display: block !important;
  filter: invert(1) !important;
  height: 28px !important;
  object-fit: contain !important;
  width: 28px !important;
}
#topbar .dropdown1.open > .dropdown-menu {
  display: block !important;
  opacity: 1 !important;
  visibility: visible !important;
  z-index: 100000 !important;
}
@media (min-width: 769px) {
  .mobile-quick-strip {
    height: 34px !important;
    min-height: 34px !important;
    padding: 0 10px !important;
  }
  .mobile-quick-strip a {
    border-radius: 4px !important;
    font-size: 12px !important;
    line-height: 1 !important;
    margin-left: 5px !important;
    padding: 5px 11px !important;
  }
  #topbar .account-access {
    align-items: center !important;
    display: flex !important;
    height: 44px !important;
    margin: 0 8px !important;
  }
  #topbar .account-user-only {
    color: #fff !important;
    font-size: 14px !important;
    font-weight: 800 !important;
    margin: 0 10px !important;
  }
  .home-header .home-main-caption {
    color: #fff !important;
    direction: rtl !important;
    display: flex !important;
    flex-direction: column !important;
    font-family: GE SS Two, Tajawal, sans-serif !important;
    font-size: 27px !important;
    font-weight: 900 !important;
    left: 50% !important;
    line-height: 1.35 !important;
    position: absolute !important;
    text-align: center !important;
    text-shadow: 0 3px 12px rgba(0,0,0,.95) !important;
    top: 92px !important;
    transform: translateX(-50%) !important;
    width: 700px !important;
    z-index: 14 !important;
  }
  .home-header .home-main-caption span {
    color: #fff !important;
    display: block !important;
  }
  .home-header .header-mid.header-mid-custom-box .margintop {
    left: 50% !important;
    max-width: 700px !important;
    right: auto !important;
    top: 308px !important;
    transform: translateX(-50%) !important;
    width: 700px !important;
  }
  .home-header .page-header-col1-row2-col2-form {
    max-width: 700px !important;
    width: 700px !important;
  }
  .home-header #hdr_frm {
    border: 2px solid rgba(255,255,255,.95) !important;
    border-radius: 14px !important;
    box-shadow: 0 6px 18px rgba(0,0,0,.28) !important;
    overflow: hidden !important;
  }
  html body .home-header #btnSearch {
    background: #079448 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='32' height='32' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.8' stroke-linecap='round'%3E%3Ccircle cx='10.5' cy='10.5' r='6.5'/%3E%3Cpath d='M15.5 15.5L21 21'/%3E%3C/svg%3E") center / 31px 31px no-repeat !important;
    border: 0 !important;
    border-radius: 12px 0 0 12px !important;
  }
  .home-header #search-box1 {
    border: 0 !important;
    box-shadow: none !important;
  }
  .home-header #smartSearchMode {
    border-left: 1px solid #dbe2e5 !important;
  }
  .home-header #smartSearchMode.active img,
  .home-header #smartSearchMode.loading img {
    filter: invert(20%) sepia(94%) saturate(4617%) hue-rotate(352deg) brightness(84%) contrast(91%) !important;
  }
  .home-header .srchBx {
    display: none !important;
  }
  .home-header .home-fixed-points {
    bottom: 8px !important;
    gap: 4px 26px !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    left: 50% !important;
    right: auto !important;
    transform: translateX(-50%) !important;
    width: 940px !important;
  }
  .home-header .home-fixed-points span {
    align-items: center !important;
    display: flex !important;
    font-size: 13px !important;
    justify-content: flex-start !important;
    min-height: 28px !important;
    padding: 2px 4px !important;
    text-align: right !important;
  }
  .home-header .home-fixed-points span:before {
    align-items: center !important;
    background: #12a34a !important;
    border-radius: 50% !important;
    color: #fff !important;
    content: "✓" !important;
    display: inline-flex !important;
    flex: 0 0 18px !important;
    font-family: Arial, sans-serif !important;
    font-size: 12px !important;
    height: 18px !important;
    justify-content: center !important;
    margin-left: 8px !important;
    text-shadow: none !important;
    width: 18px !important;
  }
  .home-header .home-fixed-points span:after {
    content: none !important;
    display: none !important;
  }
  .home-header #changeLocation {
    max-height: none !important;
    overflow: visible !important;
    padding: 8px !important;
    width: 232px !important;
  }
  .home-header #changeLocation.is-open {
    display: block !important;
  }
  .home-header #changeLocation table {
    display: block !important;
    margin: 0 !important;
    width: 100% !important;
  }
  .home-header #changeLocation tbody {
    display: grid !important;
    gap: 6px !important;
    grid-template-columns: repeat(4, 1fr) !important;
    width: 100% !important;
  }
  .home-header #changeLocation tr {
    display: contents !important;
  }
  .home-header #changeLocation td {
    align-items: center !important;
    display: flex !important;
    height: 34px !important;
    justify-content: center !important;
    padding: 2px !important;
    width: auto !important;
  }
  .home-header #changeLocation td:empty {
    display: none !important;
  }
  .home-header #changeLocation td a {
    align-items: center !important;
    border-radius: 4px !important;
    display: flex !important;
    height: 30px !important;
    justify-content: center !important;
    width: 42px !important;
  }
  .home-header #changeLocation td a:hover {
    background: #edf4f6 !important;
  }
  .home-header #changeLocation td img {
    display: block !important;
    height: 20px !important;
    margin: 0 !important;
    max-width: 28px !important;
    object-fit: contain !important;
    width: 28px !important;
  }
}
@media (max-width: 768px) {
  .home-main-caption,
  .home-header .home-fixed-points {
    display: none !important;
  }
  #topbar .account-access-label {
    display: none !important;
  }
  #topbar .account-access-button {
    height: 34px !important;
    width: 34px !important;
  }
  #topbar .account-access-button img {
    height: 24px !important;
    width: 24px !important;
  }
  .home-header {
    min-height: 500px !important;
    width: 100% !important;
  }
  .home-header .header-mid.header-mid-custom-box .margintop {
    left: 12px !important;
    margin: 0 !important;
    max-width: none !important;
    right: 12px !important;
    top: 190px !important;
    transform: none !important;
    width: auto !important;
  }
  .home-header .page-header-col1-row2-col2-form {
    margin: 0 !important;
    max-width: none !important;
    width: 100% !important;
  }
  .home-header .search_tab {
    display: flex !important;
    float: none !important;
    margin: 0 !important;
    width: 100% !important;
  }
  .home-header .search_tab > li {
    flex: 1 1 50% !important;
    float: none !important;
    margin: 0 !important;
    width: 50% !important;
  }
  .home-header .search_tab > li > a {
    font-size: 12px !important;
    padding: 8px 3px !important;
    text-align: center !important;
  }
  .home-header #hdr_frm {
    border: 1px solid #dfe5e7 !important;
    border-radius: 11px !important;
    box-sizing: border-box !important;
    direction: ltr !important;
    display: flex !important;
    height: 50px !important;
    margin: 0 !important;
    max-width: 100% !important;
    overflow: hidden !important;
    width: 100% !important;
  }
  html body .home-header #btnSearch {
    background: #079448 url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='28' height='28' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2.8' stroke-linecap='round'%3E%3Ccircle cx='10.5' cy='10.5' r='6.5'/%3E%3Cpath d='M15.5 15.5L21 21'/%3E%3C/svg%3E") center / 27px 27px no-repeat !important;
    border: 0 !important;
    border-radius: 9px 0 0 9px !important;
    flex: 0 0 50px !important;
    height: 50px !important;
    min-width: 50px !important;
    order: 1 !important;
    position: static !important;
    width: 50px !important;
  }
  .home-header #smartSearchMode {
    align-items: center !important;
    background: #fff !important;
    border: 0 !important;
    border-left: 1px solid #e0e5e7 !important;
    display: flex !important;
    flex: 0 0 48px !important;
    height: 50px !important;
    justify-content: center !important;
    max-width: 48px !important;
    min-width: 48px !important;
    order: 2 !important;
    padding: 4px !important;
    position: static !important;
    width: 48px !important;
  }
  .home-header #smartSearchMode img {
    height: 40px !important;
    margin: 0 !important;
    object-fit: contain !important;
    width: 40px !important;
  }
  .home-header #search-box1 {
    border: 0 !important;
    box-shadow: none !important;
    flex: 1 1 auto !important;
    font-size: 12px !important;
    height: 50px !important;
    min-width: 0 !important;
    order: 3 !important;
    padding: 0 8px !important;
    position: static !important;
    width: auto !important;
  }
  .home-header .page-header-col1-row2-col2-form-select {
    display: none !important;
  }
}


@media (min-width: 769px) {
  .mobile-quick-strip {
    height: 28px !important;
    min-height: 28px !important;
    padding-block: 0 !important;
  }
  .mobile-quick-strip a {
    font-size: 11px !important;
    min-height: 24px !important;
    padding: 4px 10px !important;
  }
  #topbar .top-lft > ul {
    gap: 12px !important;
    justify-content: flex-start !important;
    padding-right: 0 !important;
  }
  #topbar .top-lft {
    left: auto !important;
    position: absolute !important;
    right: 0px !important;
    top: 0 !important;
    width: auto !important;
  }
  #topbar {
    overflow: visible !important;
    z-index: 10050 !important;
  }
  #topbar .header-fixed-container,
  #topbar .row,
  #topbar .top-mid > ul,
  #topbar .top-mid > ul > span,
  #topbar .top-lft > ul {
    overflow: visible !important;
  }
  #topbar .top-lft .dropdown1 > a[href="my-enquiries.php"] {
    margin-inline: 12px 4px !important;
  }
  #topbar .account-user-only {
    align-items: center !important;
    direction: rtl !important;
    display: inline-flex !important;
    gap: 4px !important;
    margin-right: 18px !important;
  }
  #topbar .account-welcome {
    color: #fff !important;
    display: inline !important;
    font-weight: 500 !important;
  }
  #topbar .account-access-button {
    direction: rtl !important;
    gap: 7px !important;
    padding-inline: 4px 8px !important;
    width: auto !important;
  }
  #topbar .account-access-label {
    color: #fff !important;
    display: inline-block !important;
    font-family: Tajawal, "GE SS Two", Arial, sans-serif !important;
    font-size: 14px !important;
    font-weight: 500 !important;
    white-space: nowrap !important;
  }
  #topbar .top-mid .dropdown1:hover > .dropdown-menu,
  #topbar .top-lft .dropdown1:hover > .dropdown-menu,
  #topbar .top-mid .dropdown1:focus-within > .dropdown-menu,
  #topbar .top-lft .dropdown1:focus-within > .dropdown-menu {
    display: block !important;
    opacity: 1 !important;
    visibility: visible !important;
    z-index: 100000 !important;
  }
  #topbar .top-mid .dropdown1 > .dropbtn1,
  #topbar .top-mid .dropdown1:hover > .dropbtn1,
  #topbar .top-mid .dropdown1:focus-within > .dropbtn1,
  #topbar .top-lft .dropdown1 > .dropbtn1,
  #topbar .top-lft .dropdown1:hover > .dropbtn1,
  #topbar .top-lft .dropdown1:focus-within > .dropbtn1 {
    background: transparent !important;
    color: #f2b544 !important;
    text-decoration: none !important;
  }
  .home-header .home-main-caption {
    font-family: Tajawal, "GE SS Two", Arial, sans-serif !important;
    font-size: clamp(24px, 1.6vw, 30px) !important;
    font-weight: 400 !important;
    line-height: 1.4 !important;
    text-align: right !important;
    top: 132px !important;
    width: 620px !important;
  }
  .home-header .home-main-caption > span {
    color: #fff !important;
    display: block !important;
    text-align: right !important;
    width: 100% !important;
  }
  .home-header .header-mid.header-mid-custom-box .margintop {
    max-width: 620px !important;
    top: 284px !important;
    width: 620px !important;
  }
  .home-header .page-header-col1-row2-col2-form {
    max-width: 620px !important;
    width: 620px !important;
  }
  .home-header .home-fixed-points {
    bottom: auto !important;
    gap: 3px 12px !important;
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    top: 392px !important;
    width: 620px !important;
  }
  .home-header .home-fixed-points > span {
    align-items: center !important;
    display: flex !important;
    font-family: Tajawal, "GE SS Two", Arial, sans-serif !important;
    font-size: 12px !important;
    font-weight: 500 !important;
    justify-content: flex-start !important;
    line-height: 1.4 !important;
    min-width: 0 !important;
    white-space: normal !important;
  }
  .home-header .home-fixed-points span:before {
    content: none !important;
    display: none !important;
  }
  .home-header .home-fixed-points > span:before {
    align-items: center !important;
    background: #12a34a !important;
    border-radius: 50% !important;
    color: #fff !important;
    content: "✓" !important;
    display: inline-flex !important;
    flex: 0 0 16px !important;
    font-family: Arial, sans-serif !important;
    font-size: 11px !important;
    height: 16px !important;
    justify-content: center !important;
    margin-left: 7px !important;
    text-shadow: none !important;
    width: 16px !important;
  }
  .home-header #cnlocation:before {
    color: #fff !important;
    content: "🌐 Global" !important;
    direction: ltr !important;
    display: block !important;
    font-family: Arial, sans-serif !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    margin-bottom: 3px !important;
    text-shadow: 0 2px 7px rgba(0,0,0,.9) !important;
  }
  .home-header .page-header-col1-row1-col1-row2-form .arw {
    display: none !important;
  }
  .home-header .page-header-col1-row1-col1-row2-form > div > a.un:after {
    content: none !important;
    display: none !important;
  }
  .home-header .page-header-col1-row1-col1-row2-form > div > a.un {
    display: none !important;
  }
  .home-header #cnlocation img[alt="Global"] {
    display: none !important;
  }
  .home-header .page-header-col1-row1-col1_row2:hover #changeLocation,
  .home-header .page-header-col1-row1-col1_row2:focus-within #changeLocation {
    display: block !important;
  }
}
#topbar {
                        top:0 !important;
                        }
</style>
<link rel="stylesheet" href="css/yasir-home-final.css?v=20260726j23klmnpqrst">
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
