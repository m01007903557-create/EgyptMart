<?php
declare(strict_types=1);

/**
 * File: header.php
 * Version: 3.0.0 (PHP 8.3)
 * Description: Full header with all original features
 */

if (!defined('ACCESS_ALLOWED') && basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    exit('Direct access not allowed');
}

ob_start();

// Get user data
$uid = $_SESSION['uid_indm'] ?? '';
$cid = '';
$row = null;
$count = 0;

if (!empty($uid)) {
    try {
        $sql = "SELECT u.*, b.* FROM user u LEFT JOIN business_profile b ON u.usr_id = b.bnsprof_uid WHERE u.usr_id = ? AND u.status = '1'";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        if ($row && isset($row->bnsprof_id)) {
            $cid = rand(1000, 9999) . md5((string)$row->bnsprof_id);
        }
        mysqli_stmt_close($stmt);
        
        // Get message count
        $query_pag_num = "SELECT COUNT(*) AS count FROM message WHERE msg_to = ? AND msg_to_status = '1'";
        $stmt_count = mysqli_prepare($con, $query_pag_num);
        mysqli_stmt_bind_param($stmt_count, 'i', $uid);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $row_count = mysqli_fetch_assoc($result_count);
        $count = (int)($row_count['count'] ?? 0);
        mysqli_stmt_close($stmt_count);
        
    } catch (Exception $e) {
        error_log("Error in header query: " . $e->getMessage());
    }
}
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
<style>
body{ font-family: 'Tajawal', sans-serif; }
</style>
<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>
<?php include "css/custom.php"; ?>

<script type="text/javascript">
function showmymenu() { $("#mn1").show(); }
function hidemymenu() { $("#mn1").hide(); }
function showLocMenu() { $("#changeLocation").show(); }
function hideLocMenu() { $("#changeLocation").hide(); }
function showbuymenu() { $("#buymnu").show(); }
function hidebuymenu() { $("#buymnu").hide(); }
function showsellmenu() { $("#sellmnu").show(); }
function hidesellmenu() { $("#sellmnu").hide(); }
function showsrchm() { $("#smnu").show(); }
function hidesrchm() { $("#smnu").hide(); }

function OutboundLink(type) {
    if (type == 'buy_lead') { $("#a1").html("Buy Leads"); }
    else if (type == 'tender') { $("#a1").html("Tender"); }
    else if (type == 'auction') { $("#a1").html("Auction"); }
    else { $("#a1").html(type); }
    $("#rctyp").val(type);
    $("#smnu").hide();
}

function validsearch() {
    $('.loading-text').removeClass('hide').addClass('show');
    return true;
}
function gotFocus() {
    var keywords = $("input#keywords").val();
    if (keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search' || keywords == 'Enter Tender to search') {
        $("input#keywords").val('');
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
function setCountryLocation(id) {
    $.post("setCountryLocation.php", {loc_id: id}, function(data) {
        if (data != 0) { location.reload(); }
    });
}
function unsetCountryLocation() {
    $.post("unsetCountryLocation.php", function(data) { location.reload(); });
}

$(window).scroll(function () {
    var height = $(window).scrollTop();
    if (height > 150) {
        $('#topbar').addClass('fixed-position');
    } else {
        $('#topbar').removeClass('fixed-position');
    }
});
$(document).ready(function () {
    var viewportWidth;
    $(window).resize(function () { viewportWidth = $(window).width(); });
    $('.mobile-click').click(function (e) {
        if ($(window).width() < 767) {
            e.preventDefault();
            $('.typography_3_colm').toggle();
        }
    });
});
$(window).load(function () {
    if (typeof(flexslider) !== 'undefined') {
        $('.flexslider').flexslider({
            animation: "slide",
            controlNav: "thumbnails"
        });
    }
});
</script>
<script type="text/javascript">
function showcontent(x){
    if(window.XMLHttpRequest) { xmlhttp = new XMLHttpRequest(); }
    else { xmlhttp = new ActiveXObject('Microsoft.XMLHTTP'); }
    xmlhttp.onreadystatechange = function() {
        if(xmlhttp.readyState == 1) { document.getElementById('content').innerHTML = "<img src='images/loadingif.gif' />"; }
        if(xmlhttp.readyState == 4 && xmlhttp.status == 200) { document.getElementById('content').innerHTML = xmlhttp.responseText; }
    }
    xmlhttp.open('POST', x+'.html', true);
    xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    xmlhttp.send(null);
}
</script>

<!-- Top Blue Bar -->
<div class="container top-bar " id="topbar">
  <div class="row">
    <div class="header-fixed-container">
      <div class="col-sm-12 col-lg-4 top-lft">
        <ul>
          <?php if (!empty($uid)): ?>
          <li><span class="pp1"><span class="tlc"> مرحبا </span><?php echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname'); if (!empty($row->bnsprof_compname)) { ?> <span><a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/slider-icon01.jpg" style="width:18px; height:15px;border:0;" alt=""/></a></span><?php } ?></span></li>
          <?php else: ?>
          <li><a href="sign-in.php#loginform" target="_top" rel="nofollow" style=" font-family:GE SS Two"title=" Sign in "> سجل دخول </a></li> | <li><a href="create_account.php#signupform" target="_top" rel="nofollow" style=" font-family:GE SS Two" title=" Join Free" >إنشىء حساب مجانا &nbsp;|</a></li>
          <?php endif; ?>
          <li class="dropdown dropdown1" style="z-index: 100;"title=" عملى على سوق مصر ">
            <a data-target="myEgyptmart" class="dropbtn1" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">تجـارتى على المنصة</b> <i class="fa fa-chevron-down"></i> </a><span class="linebr" style="color: black"> |</span> <a href="my-enquiries.php"> <img width="25" src="images/envolap.png"/><?php echo '<span class="label label-yellow">' . $count . '</span>'; ?></a>
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="myEgyptmart" style="width:101%; z-index: -1;">
              <li><a href="my-dashboard.php"style=" font-family:GE SS Two"title=" My Dashboard "> مفاتيــح إدارة المنصــة </a></li>
              <li><a href="my-enquiries.php"style=" font-family:GE SS Two"title="My Inbox"> صنـــدوق رسائلى داخل المنصة</a></li>
              <li><a href="favorite.php"style=" font-family:GE SS Two"title="My Favorites"> صفحــة منتجـــاتى المفضلــة </a></li>
              <?php if (!empty($uid)) { ?><li><a href="logout.php"style=" font-family:GE SS Two"title="Sign Out">تسجيل خروج</a></li><?php } ?>
            </ul>
          </li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-4 top-mid">
        <ul>
          <?php if (!empty($uid)) { ?><li><a href="company/index.php?c=<?php echo $cid; ?>" class="txt-yellow" style=" font-family:GE SS Two"title=" My B2B Website" > معروضاتى</a></li><?php } ?>
          <span style="margin-left:40px;">
            <li class="dropdown dropdown1"><a class="ar-lebel dropbtn1" href="#" data-toggle="dropdown" role="button" style="color:yellow; font-family:GE SS Two;"title="Buy">إشترى <i class="fa fa-chevron-down"></i></a>
              <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="buy">
                <li><a href="post-buy-req.php"style=" font-family:GE SS Two;"title="Post Your Buy Requirement">أنشر طلبات تسعيير لمشترياتك</a></li>
                <li><a href="search_adv.php"style=" font-family:GE SS Two;"title=" Search Product & Suppliers"> إبحث عن منتجات وخدمات </a></li>
                <li><a href="manage-selloffer-alert.php"style=" font-family:GE SS Two;" title=" Manage Sale Notifications"> سجل اشعارات فرص بيع </a></li> 
                <li><a href="post-tender.php"style=" font-family:GE SS Two;"title=" Post Tenders FREE "> أنشر مناقصات مجانا </a></li>
              </ul>
            </li>
            <li class="dropdown dropdown1" id="sell"><a class="ar-lebel dropbtn1" href="#" data-toggle="dropdown" role="button" style=" color:yellow;font-family:GE SS Two;" title="Sell" > بيــع <i class="fa fa-chevron-down"></i> </a>
              <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="sell">
                <li><a href="product-add.php"style=" font-family:GE SS Two;"title=" Display Products / Services "> إعرض منتجات أو خدمات </a></li>
                <li><a href="membership_plans.php"style=" font-family:GE SS Two;"title="Create B2B Website" > إنشىء صفحات أعمالك </a></li>
                <li><a href="buyleads.php"style=" font-family:GE SS Two;"title="Latest Buy Requests">أحدث طلبات الشراء </a></li>
                <li><a href="http://egyptmart.shop/post-sell-offer.php"style=" font-family:GE SS Two;"title=" Post Sale Offers "> سجل عروض بيع خاصـة </a></li>
                <li><a href="manage-buylead-alert.php"style=" font-family:GE SS Two;"title=" Manage Buy Notifications"> سجل إشعارات طلبات شراء </a></li>
                <li><a href="post-auction.php"style=" font-family:GE SS Two;"title=" Post Auctions FREE "> أنشر مزايدات مجانا </a></li>
              </ul>
            </li>
          </span>
          <a href="#" style="color: #fff; background: #007bff; padding: 8px 15px; border-radius: 5px; text-decoration: none;">نسخة تجريبية</a>
          <li class="dropdown dropdown1" style="z-index: 100;"title=" سوق التصدير ">
            <a data-target="myEgyptmart" class="dropbtn1" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">ARAB EXPORT</b> <i class="fa fa-chevron-down"></i> </a>
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="myEgyptmart" style="width:101%; z-index: -1;">
              <li><a href="http://arab-mart.com"style=" font-family:GE SS Two"title="المنصة باللغة الإنجليزية للمورديين الدوليين "> Arab-MART.com سوق العرب </a></li>
            </ul>
          </li>
        </ul>
      </div>
      <div class="col-sm-6 col-lg-4 top-rht">
        <ul class="text-right tstleft">
          <li><a href="why_egyptmart.php" class=" txt-yellow"><b class="txt-yellow" style="font-weight:900;"title="Why EgyptMART"> فوائـد الإشتراك </b></a> </li>
          <li><a href="help.php"style=" font-family:GE SS Two"title="How It Works ?">كيف تعمل المنصة ؟</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Main Header -->
<div class="maincontainertop ">
  <header class="page-header home-header" style="background-image:linear-gradient(rgba(255,255,255, 0.45), rgba(255,255,255, 0.45)),url(images/headerbg.jpg); background-repeat:no-repeat; background-size:cover;">
    <div class="headertop-custom-box">
      <div class="headertop-custom-box-middle">
        <div class="page-header-col1-row1" style="padding:0;">
          <div class="page-header-col1-row1-col1 col-xs-6">
            <div class="page-header-col1-row1-col1_row"><p><a href="my-dashboard.php"title="My Dashboard">لوحة مفاتيح المنصة</a></p></div>
            <div class="page-header-col1-row1-col1_row2">
              <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
                <?php if (isset($_COOKIE['loc_id'])) { ?>
                <span><?php echo get_country_name((int)$_COOKIE['loc_id']); ?></span>&nbsp; <img src="images/country_flag/<?php echo get_country_flag((int)$_COOKIE['loc_id']); ?>" alt="<?php echo get_country_name((int)$_COOKIE['loc_id']); ?>" class="w4" align="top" height="16" width="23" title="<?php echo get_country_name((int)$_COOKIE['loc_id']); ?>"/>
                <?php } else { ?>
                <span style="font-weight: bold; font-size: 20px; color: darkcyan; font-family: Arial Black;">Global</span> &nbsp; <img src="images/country_flag/Global$download.png" style="height:25px !important;width:25px!important;" alt="Global" class="w4" align="top" height="30" width="30"/>
                <?php } ?>
              </div>
              <div class="page-header-col1-row1-col1-row2-form">
                <div onmouseover="showLocMenu();" onmouseout="hideLocMenu()">
                  <a class="un" style="border-left:none; font-size: 9px; color:#0f2399;"><span style="color: black;">غـير بلـد البحـث &nbsp;<span class="arw"><b>&or;</b></span></span></a>
                  <style>#changeLocation{ display:none; left:0 !important; top:-30px !important; right:0; } @media (min-width: 991px){ #changeLocation { top: 20px !important; } }</style>
                  <div class="sub_menu" id="changeLocation">
                    <ul><li><?php $activeCountries = getActiveCountryList(); $sql_cnLoc = "select * from country where cn_id in(" . $activeCountries . ")"; $res_cnLoc = mysqli_query($con, $sql_cnLoc); ?>
                      <table style="width:100%;padding:1px;" class="table-responsive">
                        <tr><td align="center"><a title="Global" style="cursor:pointer;" onclick="unsetCountryLocation();"> <img src="images/country_flag/Global$download.png" alt="Global" class="w4" align="top" height="25" width="25"/> </a></td>
                        <?php $cn = 1; while ($row_cnLoc = mysqli_fetch_object($res_cnLoc)) { if ($cn % 4 == 0) { $cn = 0; echo '</tr><tr>'; } ?>
                        <td align="center"><a title="<?php echo htmlspecialchars($row_cnLoc->cn_name); ?>" style="cursor:pointer;" onclick="setCountryLocation(<?php echo (int)$row_cnLoc->cn_id; ?>);"> <img src="images/country_flag/<?php echo get_country_flag((int)$row_cnLoc->cn_id); ?>" alt="<?php echo htmlspecialchars($row_cnLoc->cn_name); ?>" class="w4" align="top" height="20" width="25"/> </a> </td>
                        <?php $cn++; } while ($cn <= 5) { echo '<td>&nbsp;</td>'; $cn++; } ?>
                      </tr></table>
                    </li></ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="page-header-col1-row1-col2 col-xs-6">
            <a href="index.php"title="سوق مصر على الإنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع - فى مصر والمنطقة العربية"><img src="sitelogo/logo6744egyptmart logo SHOP copy.png" alt="" class="logoa" /></a>
          </div>
          <div class="page-header-col1-row1-col3">
            <div id="google_translate_element"></div>
            <script type="text/javascript">function googleTranslateElementInit() { new google.translate.TranslateElement({ pageLanguage: 'ar', layout: google.translate.TranslateElement.InlineLayout.SIMPLE }, 'google_translate_element'); }</script>
            <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
            <p class="cb"></p>
          </div>
          <div class="page-header-col1-row1-col4 col-xs-12" style="padding:0;">
            <div class="page-header-col1-row1-col4-row1 col-xs-6 home-ba">
              <h3 style="font-size:30px;"title="Business Alerts"><img class="img-responsive " src="images/bell.png" width="18px" style="margin-right:5px;"title="Business Alerts"> إشعـارات تجـارية</h3>
              <p class="text-center" style="font-size: 10px"> تـلـقــــى إشــعـــــــارات فــــى بـريـــــدك <br> عن المنتجات المفضـلة لتجارتـك </p>
            </div>
            <script>function sub() { var location = ""; if (document.getElementById('radio1').checked) { location = document.getElementById("radio1").value; } if (document.getElementById('radio2').checked) { location = document.getElementById("radio2").value; } if (location) { window.location = location; } }</script>
            <div class="page-header-col1-row1-col4-row2 col-xs-6 home-buyer-seller ; align:center;">
              <div class="page-header-col1-row1-col4-row2-checkbox">
                <label class="radio"><input id="radio1" type="radio" name="radios" value="manage-selloffer-alert.php"> <span class="outer"><span class="inner"></span></span><a href="#" style="color: black" title="سجل أسماء المنتجات / الخدمات التى تهتم بشرائها - لكى تتلقى أحدث اشعارات عنها فى بريدك "> شــراء </a> </label>
                <label class="radio Buyer-radio" style="font-size: 16px"><input id="radio2" type="radio" name="radios" value="manage-buylead-alert.php" checked > <span class="outer"><span class="inner"></span></span><a href="#" style="color: black align:center;"title="سجل أسماء المنتجات / الخدمات التى تببيعها - لكى تتلقى فى بريدك أحدث إشعارات - طلبات عرض الأسعار المرسلة عنها "> بيــــع</a> </label>
              </div>
              <div class="page-header-col1-row1-col4-row2-link"><a id="sub" onclick="return sub();" href="sign-in.php"title="Subscribe NOW"> ســجـــل الآن</a></div>
              <h2 class="justclick"title="Just a Click Away"><br></h2>
            </div>
          </div>
        </div>
        <div class=" header-mid header-mid-custom-box">
          <div class="post-prod-left"><a href="product-sel-cat.php" class="post-product-btn"title=" Display Your Business "> إعرض هـنا منتجاتـك للبيع <small>وتلقــى استفسـارات شــراء محليا ودوليا</small> </a></div>
          <div class="page-header-col1-row2">
            <div class="col-lg-7 col-md-6 col-xs-12 margintop">
              <script>
                $(document).ready(function() {
                    $('.searchTabs').click(function() {
                        var TabVal = $(this).attr('alt');
                        var optionValue = $(this).attr('alt');
                        $('#rctyp option').removeAttr('selected');
                        $('#rctyp option[value=' + optionValue + ']').attr('selected', 'selected');
                        var PlaceholdVAl = "";
                        if (TabVal == 'Products') { PlaceholdVAl = "إبحــث عن منتجات وخدمات تجارية من المنبـع أو بإسم المـورد  >>"; }
                        else if (TabVal == 'Suppliers') { PlaceholdVAl = "إبحــث عن مــوردين بأسمـاء الشركات أو منتجـات المورديــن    >> "; }
                        else if (TabVal == 'buy_lead') { PlaceholdVAl = "إبحــث عن طلبات شراء لأعمالك من المنبـع >>"; }
                        else if (TabVal == 'tender') { PlaceholdVAl = "إبحــث عن مناقصات أو مزايدات لأعمالك >>"; }
                        $("#search-box1").attr("placeholder", PlaceholdVAl);
                    });
                    $("#search-box1").keyup(function() {
                        var getDrpDwnVal = $("ul.search_tab li.active").attr("alt");
                        var fileName = "";
                        if (getDrpDwnVal == 'Suppliers') { fileName = "readsuppliers.php"; }
                        else if (getDrpDwnVal == 'Products') { fileName = "readproducts.php"; }
                        else if (getDrpDwnVal == 'Buy Leads') { fileName = "read_leads.php"; }
                        else { fileName = "read_tenders.php"; }
                        $.ajax({
                            type: "POST",
                            url: fileName,
                            data: 'keyword=' + encodeURIComponent($(this).val()),
                            beforeSend: function() { $(".search-box").css("background", "#FFF url(377.gif) no-repeat 165px"); },
                            success: function(data) {
                                $("#suggesstionBoxs").show();
                                $("#suggesstionBoxs").html(data);
                                $("#search-box1").css("background", "#FFF");
                            }
                        });
                    });
                });
                function selectCountry(val) { $("#search-box1").val(val); $("#suggesstionBoxs").hide(); }
              </script>
              <div class="page-header-col1-row2-col2-form">
                <ul class="nav nav-tabs search_tab" role="tablist" id="rctyp">
                  <li role="presentation" class="active" alt="Products"><a href="#products" alt="Products" class="searchTabs" aria-controls="products" role="tab" data-toggle="tab"title="Find Products & Services" > إبحــث عن أى منتجــات </a></li>
                  <li role="presentation" alt="Suppliers"><a href="#supplier" alt="Suppliers" class="searchTabs" aria-controls="supplier" role="tab" data-toggle="tab"title="Find Suppliers" >إبحـث عن شركات وموردين</a></li>
                </ul>
                <div class="tab-content search_cont">
                  <div role="tabpanel" class="tab-pane active" id="supplier">
                    <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                      <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                        <option value="Suppliers">Suppliers</option>
                        <option value="Products" selected>Products</option>
                        <option value="buy_lead">Buy Leads</option>
                        <option value="tender">Tender</option>
                      </select>
                      <input type="text" id="search-box1" name="keywords" style="font-weight:900;text-align:center; border:1px solid;box-shadow: 1px 2px 4px #595959;" placeholder=" إبحــث بالعربى أو الإنجليزى >> منتجات وخدمات >> مصر والعالم " class="page-header-col1-row2-col2-form-input topsearch_placeholder_cont search-box" onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo isset($_GET['keywords']) ? htmlspecialchars($_GET['keywords']) : ''; ?>" style="border: 1px solid #000;width:90%" />
                      <span class="loading-text hide"><img src="/assets/img/Spinner-200px.gif" style="width: 48px;height: 48px;"></span>
                      <div id="suggesstionBoxs" class="suggesstionBoxs"></div>
                      <input type="submit" id="btnSearch" value="" class="page-header-col1-row2-col2-form-btn"/>
                    </form>
                  </div>
                </div>
                <div class="clear"></div>
              </div>
              <div class="srchBx"><h2 class="cd-headline clip is-full-width text-center"> <span style="width: 100%; overflow: hidden; color:#404040; font-family: GE_SS_TEXT_LIGHT;" class="cd-words-wrapper" > <b class="is-hidden"><span class="blinking-cursor" style="color: red">! </span> إنضم لسوق أهم 10,000 شركة ومصنع فى مصر والعرب </b> <b class="is-hidden"><span class="blinking-cursor" style="color: red">! </span> تجارى وتصدير - أونلاين - محلى ودولى - آلاف المنتجات </b> <b class="is-visible"><span class="blinking-cursor" style="color: red ">! </span> إنشىء الآن صفحة أعمالك وإستقبل طلبات شراء تنتظرك </b> </span> </h2></div>
            </div>
            <div class="page-header-col1-row2-col4"><a href="post-buy-req.php" class="post-buy-req-btn" style="margin-top: 56px;"title=" Post Buy Requirements and Get Quotes from Verified Suppliers"> أنشـر طلـب تسعير وشـراء <small> وتلقـى أقـل عــروض بيـع له </small> </a></div>
            <div class="clear"></div>
          </div>
        </div>
      </div>
      <div class="headertop-custom-box-right">
        <div class=""><div class="page-header-col2-head"><i class="fa fa-mobile"></i> <span>Android - Windows - 360 Degree Visibility </span></div>
        <div class="page-header-col2-intro"><div class="page-header-col2-intro-pic"><img src="images/page-header-col2-intro-pic.jpg" alt=""/></div>
        <div class="page-header-col2-intro-texts"><a href="product-sel-cat.php?select=bs" class="post-product-btn" id="business-btn"title=" Post Business Services and Get Domestic or Global Inquiries">إعرض هنا خدماتك التجارية للبيع<small>وتلقـى إستفســارات شـراء من الداخــل والخــارج</small></a></div></div></div>
        <div class="clear"></div>
      </div>
      <div class="clear"></div>
    </div>
  </header>
</div>

<style>
.img-responsive { float:left; }
.maincontainertop { z-index:1003 !important; }
.justclick { font-size: 14px; font-weight: 900; color: #7e7e7e; margin-top: 8px; }
span.loading-text.show { position: absolute; top: 28px; right: 55px; color: red; font-size: 20px; }
@media (min-device-width:769px) and (max-device-width:1450px){ .page-header-col2-intro { border-left: 2px solid #237abf !important; height: 126px !important; } }
@media (width:1024px) { .home-ba h3 { text-align: center !important; } .page-header-col1-row1-col4-row1 p { text-align: center !important; } .home-buyer-seller .page-header-col1-row1-col4-row2-checkbox , .page-header-col1-row1-col4-row2-link { margin-left: 10px !important; } .headertop-custom-box-middle h1.justclick { margin-left: 20px !important; } }
</style>

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
<?php // include('style.php'); ?>

<script>
function get_load_leftdata(page=0) {
    $.ajax({
        url: "ajax_get_leftmenu_again.php",
        type: "POST",
        data: {page: page},
        success: function(resp) {
            $("#left_ajax_geting").html(resp);
            console.log("Left menu loaded");
        },
        error: function(xhr, status, error) {
            console.log("Error loading left menu: " + error);
            $("#left_ajax_geting").html('<li style="color:red;">فشل تحميل القائمة</li>');
        }
    });
}

$(document).ready(function() {
    get_load_leftdata();
    setTimeout(function() { 
        $('#pull').trigger('click'); 
        console.log('click pull'); 
    }, 500);
});
</script>

<?php
$header_content = ob_get_clean();
echo $header_content;
?>
</body>
</html>