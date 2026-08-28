<?php
  //Google Adsense
  $sql_ga="select * from google_adsense where ga_status='1' order by rand() limit 1";
  $res_ga=mysqli_query($con, $sql_ga);
  if(mysqli_num_rows($res_ga)>0)
  {
      $row_ga=mysqli_fetch_object($res_ga);
      ?>
<div style="text-align:center;padding-top:5px;padding-bottom:5px;"><?php echo $row_ga->ga_content;  ?></div>


    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
<style>
body{
font-family: 'Cairo', sans-serif;
}
</style>

<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>




<?php
  }
  ?>
<!-- footer start -->

  </div>
</div>
<footer class="footer">
  <!-- footer-searchsec start -->
  <div class="footer-searchsec">
    <div class="footer-searchsec-left">
      <div class="footer-searchsec-left-head">
        <div class="srchBx">
          <h1 class="cd-headline clip is-full-width">
            <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper" >
            <b class="is-visible"><span class="blinking-cursor" style="color: red">!</span>  إبحث عن مقدمى خدمات تجارية تم تقييمهم عن طريق المنصة </b>
            <b class="is-hidden"><span class="blinking-cursor" style="color: red">!</span>  أنشر طلب تسعير خدمة تجارية وتلقى أسعار من عدة شركات </b>
            </span>
          </h1>
        </div>
        <!--<h1>إبحث عن أى خدمات تجارية تحتاجها تجارتك </h1>-->
      </div>
      <div class="footer-searchsec-left-form">
        <!--<form action="search.php">-->
        <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm" >
          <input type="hidden" id="rctyp" name="rctyp" value="Products"/>
          <div>
            <div class="footer-searchsec-left-form-col1">
              <!--<select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                <option value="Suppliers">Suppliers</option>
                <option  value="Products" selected>Servic</option>
                <option value="buy_lead">Buy Leads</option>
                <option value="tender">Tender</option>
                <!--<option value="auction">Auction</option>
                </select>
                -->
              <p style="font size: 10px; weight;bold; " title="Search Business Services ">خــدمات</p>
            </div>
            <div class="footer-searchsec-left-form-col2"title=" Search for any Business Services ">
              <input type="text" id="search-box2" name="keywords" placeholder=" إبحث عن الاف من الخدمات التجارية للتجارة  >> "
                class="footer-searchsec-left-form-col2-input"/>
            </div>
            <div class="footer-searchsec-left-form-col3">
              <input type="submit" value="" class="footer-searchsec-left-form-col3-btn"/>
            </div>
            <div id="suggesstion-box2" class="suggesstion-box2" style="width: calc(100% - 48%);position: absolute;left: 0;z-index: 1;margin-top: 51px;padding-left: 23%;"></div>
          </div>
        </form>
        <script>
          $(document).ready(function(){
             
          });
          $(document).on('keyup',"#search-box2",function(){
                  $.ajax({
                  type: "POST",
                  url: "read_business_services.php",
                  data:'searchkey='+$(this).val(),
                  beforeSend: function(){
                     // $(".suggesstion-box2").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
                  },
                  success: function(data){
                      $(".suggesstion-box2").show();
                      $(".suggesstion-box2").html(data);
                      $("#search-box2").css("background","#FFF");
                  }
                  });
              });
          function selectService(id,val) {
          //alert(val); return false; 
          $("#search-box2").val(val);
          $("#buss_serv_cat").val(id);
          $(".suggesstion-box2").hide();
          
          }
          
        </script>
        <div class="clear"></div>
      </div>
    </div>
    <div class="footer-searchsec-right">
      <a href="post-buy-req.php?select=bs"  target="_blank" class="footer-searchsec-right-btn"title=" Post Services Requests  ">أنشر طلبات شراء خدمات </a>
    </div>
    <div class="clear"></div>
  </div>
  <!-- footer-searchsec close// -->
  <div class="footer-intro">
    <!-- footer-intro start -->
    <div class="footer-intro-left">
      <div class="footer-intro-left-logo">
        <?php
          $footerlogo=GettingSite_Setting('unit-logo-footer');
          if($footerlogo!="")
          {
            $footerlogo2show = "sitelogo/".$footerlogo;
          }
          else
          {
             $footerlogo2show = "images/footerlogo4.png";
          }
          ?>
        <a href="index.php"title="سوق مصر على الإنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع فى مصر والمنطقة العربية"><img src="<?php echo $footerlogo2show;?>" alt="" style="max-width:170px; max-height:108px;"/></a>
      </div>
      <div class="footer-intro-left-text">
        <ul>
          <li><a href="about_us.php"title="About Us ">عن المنصة </a></li>
          <li><a href="contact_us.php"title="Complaint ">الشـكاوى</a></li>
          <li><a href="contact_us.php"title="Feedback">رأيك فى المنصة</a></li>
          <!--<li><a href="privacy.php"title="Privacy & Policy">سياسة الخصوصية</a></li>
            <li><a href="terms.php"title="Tems & Conditions">الشروط والاحكام</a></li>-->
	          <li><a href="contact_us.php"title="Contact Us"> تواصل معنا </a></li>
	          <li><a href="help.php"title=" How it works ">طريفة عمل المنصة </a></li>
	          <li><a href="buyleads.php"title="Latest Buy Requests">طلبات شراء</a></li>
	          <li><a href="sale-offers.php"title="Sale Offers">عروض بيع</a></li>
	          <li><a href="membership_plans.php"title="Membership Plans">العضويات</a></li>
	          <li><a href="advertise-with-us.php"title="Advertise With Us">إعلانات</a></li>
	        </ul>
      </div>
    </div>
    <div class="footer-intro-right">
      <!-- footer-intro-right start -->
      <div class="footer-intro-right-col"title="Buyers Tools">
        <h2>أدوات المشترى</h2>
        <ul>
          <li><a href="post-buy-req.php"title="Request for Quotation">طلب تسعير أصناف شراء</a></li>
           <li><a href="search_adv.php"title=" Search Products / Services ">إبحث وأكتشف ألاف الأعمال </a></li>
          <li><a href="manage-selloffer-alert.php"title="Manage Sale offer Alerts "> تسجيل إشعارات إستقبال عروض بيع </a></li>
          <li><a href="myproduct-buy.php"title=" List Regular Buy Requirements"> تسجيل طلبات الشراء المعتادة </a></li>
           <li><a href="post-tender.php"title="Post Tenders & Get Bidders "> إنشر مناقصات وتلقى عطاءات  </a></li>
        </ul>
      </div>
      <div class="footer-intro-right-col"title=" Suppliers Tools ">
        <h2>أدوات البائع</h2>
        <ul>
          <li><a href="product-add.php"title=" Display Your Business Categories  "> إعرض أعمالك التجارية  </a></li>
          <li><a href="membership_plans.php "title=" Create Free Website ">إنشىء موقع أونلاين لتجارتك </a></li>
          <li><a href="manage-buylead-alert.php"title="Latest Buyleads Alerts">شاهد أحدث طلبات الشراء</a></li>
          <li><a href="post-sell-offer.php"title=" Post Business Ads FREE "> سجل إعلانات بيع خاصة</a></li>
           <li><a href="manage-selloffer-alert.php"title=" Products Of Interest Alerts ">إشعارات إستقبال طلبات شراء </a></li>
        <li><a href="myproduct-sell.php"title=" List Regular Company Products"> تسجيل منتجات الشركة الدائمة </a></li>
          <li><a href="post-auction.php"title="Post Auctions & Get Bidders">أنشر مزايدات وتلقى عطاءات </a></li>
        </ul>
      </div>
      <div class="footer-intro-right-col"title=" EgyptMART Solutions ">
        <h2>حلول التسويق الألكترونى </h2>
        <ul>
           <li><a href="why_egyptmart.php"title="Companies Memberships ">أسباب هامة للإشتراك بالمنصة</a></li>
          <li><a href="membership_plans.php"title="Companies Memberships ">إشتركات أعضاء المنصة</a></li>
          <li><a href="manage-purchased-buyleads.php"title=" Trade Leads For Me">طلبات الشراء الجاهزة </a></li>
          <li><a href="my-enquiries.php"title="Business Inquiries"> إستفسارات عن تجارتى فى بريدى </a></li>
                <li><a href="advertise-with-us.php"title="Advertise With Us">إحجز مساحات إعلانية  </a></li>
        </ul>
      </div>
      <div class="footer-intro-right-col"title=" Leader Suppliers Plan ">
        <h2>خطط أشتراك الشركات الرائدة</h2>
        <ul>
          <li><a href="membership_plans.php"title=" FREE PROMO PLAN "> عضوية برومو مجانية</a></li>
          <li><a href="advertise-with-us.php"title="FREE Banner Ads">إعلانات مجانية كشركة رائدة  </a></li>
          <li><a href="contact_us.php"title="FREE Bulk Newsletters">رسائل بريد الكتروني بالجملة</a></li>
          <li><a href="contact_us.php"title=" FREE Events News "> تغطية مجانية لأحداثك التجارية </a></li>
        </ul>
      </div>
      
      
      <div class="clear"></div>
      <div style="clear: both;margin-top: 15px;">
        <?php
          $get_url = "SELECT * FROM connect_us WHERE id =1";
          $ures = mysqli_query($con, $get_url);
          $urls=mysqli_fetch_array($ures);
          ?>
        <div class="footer-intro-social">
          <p>Connect with us:</p>
          <ul>
            <li><a href="<?=$urls['twitter'];?>" target="_blnak"><i class="fa fa-twitter-square"></i></a></li>
            <li><a href="<?=$urls['google'];?>" target="_blnak"><i class="fa fa-linkedin-square"></i></a></li>
            <li><a href="<?=$urls['fb'];?>" target="_blnak"><i class="fa fa-facebook-square"></i></a></li>
          </ul>
        </div>
      
      
      
        </div>
      </div>
    </div>
    <!-- footer-intro-right close// -->
    <div class="clear"></div>
  </div>
  <!-- footer-intro close// -->
</footer>
<!-- footer close// -->
<div class="copyright-row">
  <!-- copyright-row start -->
  <div class="copyright-row-col1">
    <p>Copyright &copy; <?php echo date("Y"); ?> <?php echo get_page_settings(4);?>. All rights reserved</p>
  </div>
  <div class="copyright-row-col2">
    <p><a href="terms.php">شروط الاستخدام</a> | <a href="privacy.php"> سياسة الحصوصية </a> | <a href="contact_us.php">Link to Us</a></p>
  </div>
  <div class="clear"></div>
</div>
<!-- copyright-row close // -->
<!-- scroll to top and feedback button -->
<div class="fixed-div"> <a href="#top"><img src="images/up.png" width="50"/></a> <a href="contact_us.php"><img src="images/complaint.png" width="50"/></a> </div>
<!--</div>-->
<!-- start of right Tabs -->
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
<script type="text/javascript">
  $(document).ready(function () {
      $('#horizontalTab').easyResponsiveTabs({
          type: 'default', //Types: default, vertical, accordion
          width: 'auto', //auto or any width like 600px
          fit: true   // 100% fit in a container
      });
  $(document).on('click','.stop_redirect',function(e){
  e.preventDefault();
  var id = $(this).attr('data-id');
  
  $('.r_'+id).show();
  
  })
  });
</script>
<script type="text/javascript">
  $(document).ready(function () {
      $('#horizontalTab1').easyResponsiveTabs({
          type: 'default', //Types: default, vertical, accordion
          width: 'auto', //auto or any width like 600px
          fit: true   // 100% fit in a container
      });
  });
</script>
<!-- End of right Tabs // -->
<!-- start of verticle menu -->
<script src="js/cust.js?v=<?php echo date('ymdhis');?>"></script>
<!-- End of verticle menu // -->
<!-- Animation text slider
  <link rel="stylesheet" href="css/imNew-v6.css" type="text/css"/>-->
<!--<script src="js/im-style-vn6.3.js" type="text/javascript"></script>-->
<script src="js/bgSlider-v1.js" type="text/javascript"></script>
<!-- Animation text slider // -->
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-modal-popover.js?v=<?php echo date('ymdhis'); ?>" type="text/javascript" ></script>
<!-- navigation  -->
<link rel="stylesheet" href="css/cssmenu.css" type="text/css"/>
<!--<link href="css/responsive1.css" rel="stylesheet" type="text/css"/>-->
<script src="js/script.js?t=<?php echo rand(); ?>" type="text/javascript"></script>
<!-- navigation // -->
<?php /*//webxtor 2021Jan25<?php */ ?>
<!--Start of Tawk.to Script-->
<script type="text/javascript">
  var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
  (function(){
  var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
  s1.async=true;
  s1.src='https://embed.tawk.to/584a3ee48a20fc0cac4f7e93/default';
  s1.charset='UTF-8';
  s1.setAttribute('crossorigin','*');
  s0.parentNode.insertBefore(s1,s0);
  })();
  
  (function($){
  // setTimeout(function(){ $('#myDiv').css('opacity','1');$('#r_loading').css('opacity','0'); }, 3000);

  function changeHeaderDeferred() {
      var myDiv = document.getElementById("myDiv");
      var r_loading = document.getElementById("r_loading");
      
      setTimeout(function changeHeader() {
        if (myDiv) myDiv.style.opacity = "1";
        if (r_loading) r_loading.style.opacity = "0";
        return false;
      }, 1000);

      return false;
    }
    changeHeaderDeferred();

  $(document).on('click', '.home-header .toplinksbar ul li span', function() {
      $('.home-header .toplinksbar ul li span').removeClass('active');
      $(this).addClass('active');
  });
    }(jQuery))
</script>
<script>
  $(".poplink").popover({
    //var jdfbh = $(this).attr('id');
      html: true,
      placement: "top",
      trigger: "hover",
      content: function () {
          return $(".ffffff").html();
      }
  }); 
      function selectAlertPro(val)
      {
        
        window.open(val, 
                           'newwindow', 
                           'width=700,height=400,menubar=true'); 
                return false;
       //window.open(val); return false;
        
      }
      function selectAlertTender(val){
        window.focus();
        window.open(val);
      }
    $(document).on('click','.test_bus',function(event){
    
           window.open($(this).data('href'), 'test');
    });
</script>
<!--End of Tawk.to Script-->

<link href="css/type.css" rel="stylesheet" type="text/css"/>


<style>
  .page-header-col1-row1-col4-row1 p {
    text-align: left;
  }

  .home-buyer-seller .page-header-col1-row1-col4-row2-checkbox {
    text-align: center !important;
  }

  .page-header-col1-row1-col4-row2-checkbox .radio.supplier-radio {
    margin-left: 6px;
  }

  td.txt-gray form#minQtyForm {
    position: relative;
  }

  /*#img_disp_logo{
  margin-top: 0px;
  width: 100% !important;
  height: 100% !important;
  left: 0px !important;
  top: 0px !important;
  }
  #img_disp_logo img{
  width: 100% !important;
  height:100% !important;
  margin: 0px 0px 0px 0px !important;
  }*/
  #uploadifive-productlogo_upload {
    width: 100% !important;
  }

  #drop {
    float: none !important;
  }

  /*.maincontainertop*/
  .home-header {
    /*box-shadow: 0 0 8px #252222 !important;*/
    box-shadow: 0 0 30px #252222 !important;
    -webkit-box-shadow: 0 1px 30px rgba(0, 0, 0, 0.95) !important;
    box-shadow: 0 1px 30px rgba(0, 0, 0, 0.95) !important;
    margin-bottom: 28px;
    padding-bottom: 0;
  }

  @media (max-width: 767px) {
    .lft.fl {
      width: 100% !important;
      float: none !important;
    }

    #cssmenu .ar-box {
      margin-bottom: 15px !important;
    }

    .bt {
      margin: 5px 0 8px 0;
    }
  }

  @media (max-width: 1023px) {
    .maincontainertop.clearfix.custom_quick_fix>.col-md-3.col-sm-3.col-xs-12.col-lg-2.prc-left-side .togle_style {
      font-size: 11px !important;
    }

    #cssmenu h4 {
      font-size: 12px;
    }
  }

  @media (min-width: 768px) and (max-width:1023px) {
    .page-header-col1-row1-col4-row1.col-xs-6.home-ba>h3 {
      text-align: right !important;
    }

    .page-header-col1-row1-col4-row1.col-xs-6.home-ba>h3 img.img-responsive {
      float: none;
      display: inline-block;
    }

    .page-header-col1-row1-col4-row1 p {
      text-align: right;
    }

    .home-buyer-seller .page-header-col1-row1-col4-row2-checkbox {
      text-align: right !important;
    }

    .post-buy-req-btn small,
    .post-buy-req-btn small strong {
      font-size: 9px !important;
    }

    .post-buy-req-btn {
      font-size: 15px !important;
    }

    .headertop-custom-box-middle .header-mid .page-header-col1-row2-col4 .post-buy-req-btn {
      margin-top: 53px !important;
      padding-top: 4px;
    }

    .mid-content {
      width: 100%;
      margin-left: 0px;
    }
  }

  @media (min-width: 990px) and (max-width:1024px) {
    .headertop-custom-box-middle .header-mid .page-header-col1-row2-col4 .post-buy-req-btn {
      margin-top: 37px !important;
    }

    .post-prod-left .post-product-btn {
      margin-top: 39px !important;
    }

    .post-buy-req-btn {
      font-size: 15px !important;
    }

    .post-buy-req-btn small,
    .post-buy-req-btn small strong {
      font-size: 11px !important;
    }
  }

  @media (min-width: 1025px) and (max-width: 1100px) {
    .post-buy-req-btn {
      font-size: 15px !important;
    }

    .post-buy-req-btn small,
    .post-buy-req-btn small strong {
      font-size: 11px !important;
    }

    .post-prod-left .post-product-btn {
      margin-top: 57px !important;
    }

    .headertop-custom-box-middle .header-mid .page-header-col1-row2-col4 .post-buy-req-btn {
      margin-top: 52px !important;
    }

    .mid-content {
      width: 100%;
    }
  }

  @media (min-width:1101px) and (max-width: 1279px) {
    .headertop-custom-box-middle .header-mid .page-header-col1-row2-col4 .post-buy-req-btn {
      margin-top: 37px !important;
    }

    .post-buy-req-btn {
      font-size: 13px !important;
    }

    .post-buy-req-btn small,
    .post-buy-req-btn small strong {
      font-size: 11px !important;
    }

    .home-buyer-seller .page-header-col1-row1-col4-row2-checkbox {
      text-align: center;
    }

    .mid-content {
      margin-left: 0px;
      width: 100%;
    }

    .post-prod-left .post-product-btn {
      /*margin-top: 47px !important;*/
      margin-top: 32px !important;
    }

    .page-header-col1-row1-col4-row2-link {
      text-align: left;
    }

    .headertop-custom-box-middle h1.justclick {
      text-align: left;
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-3.col-sm-3.col-xs-12.col-lg-2.prc-left-side .togle_style {
      font-size: 11px !important;
    }

    td.txt-gray form#minQtyForm {
      margin-left: -5px;
    }

    #cssmenu {
      width: 100% !important;
    }
  }

  @media (min-width: 1200px) and (max-width: 1279px) {

    .post-buy-req-btn small,
    .post-buy-req-btn small strong {
      font-size: 9px !important;
    }

    .headertop-custom-box-middle .header-mid .page-header-col1-row2-col4 .post-buy-req-btn {
      margin-top: 86px !important;
    }

    .post-prod-left .post-product-btn {
      margin-top: 30px !important;
    }
  }

  @media (min-width: 1279px) and (max-width: 1363px) {
    .post-prod-left .post-product-btn {
      margin-top: 32px !important;
    }

    #final_result {
      width: 81%;
      max-width: 841px;
    }

    #final_result {
      max-width: 836px;
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-3.col-sm-3.col-xs-12.col-lg-2.prc-left-side .togle_style {
      font-size: 15px !important;
    }

    td.txt-gray form#minQtyForm {
      position: relative;
      margin-left: -80px;
    }

    td.blank-td.hidden-sm.hidden-md.visible-lg {
      left: -81px !important;
    }
  }

  @media (min-width: 1364px) and (max-width: 1919px) {
    .headertop-custom-box .post-product-btn {
      margin-top: 77px;
    }

    .post-prod-left .post-product-btn {
      margin-top: 31px !important;
    }

    #final_result {
      max-width: 910px;
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-3.col-sm-3.col-xs-12.col-lg-2.prc-left-side .togle_style {
      font-size: 16px !important;
    }

    td.txt-gray form#minQtyForm {
      margin-left: -95px;
    }

    td.blank-td.hidden-sm.hidden-md.visible-lg {
      left: -97px !important;
    }
  }

  @media (min-width: 1920px) {
    .headertop-custom-box .post-product-btn {
      margin-top: 83px;
    }

    .post-prod-left .post-product-btn {
      margin-top: 37px !important;
    }

    #final_result {
      max-width: 912px;
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-3.col-sm-3.col-xs-12.col-lg-2.prc-left-side .togle_style {
      font-size: 16px !important;
    }

    td.txt-gray form#minQtyForm {
      margin-left: -95px;
    }

    td.blank-td.hidden-sm.hidden-md.visible-lg {
      left: -97px !important;
    }
  }

  @media screen and (min-width: 768px) {
    .srchBx {
      margin-top: 0 !important;
    }

    .directory-template div.lft {
      padding-right: 15px;
    }

    .directory-template .inner_wrapper .ryt {
      margin-left: 10px;
    }

    .directory-template .r-block-search {
      margin-left: -15px !important;
      position: relative !important;
      top: -10px !important;
    }
  }
  @media (max-width: 480px){
    #country-list {
      left: 20px !important;
      width: 172% !important; 
    }
  }
  @media (max-width: 1300px) and (min-width: 1200px) {
    .home-header {
      padding-top: 7px !important;
    }

    .page-header-col1-row2-col2-form #suggesstionBoxs #country-list {
      margin-top: 4px;
      border-bottom: 1px solid #006bb1;
      border-left: 1px solid #006bb1;
      border-right: 1px solid #006bb1;
    }

    #search-box1:hover,
    #search-box1:focus {
      box-shadow: 0 3px 8px 0 rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.08);
    }

    #search-box11:hover,
    #search-box11:focus {
      box-shadow: 0 3px 8px 0 rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.08);
    }

    .topsearch_placeholder {
      padding: 0;
      margin: 0;
    }

    .topsearch_placeholder_cont {
      width: 100% !important;
      height: 46px;
    }

    .lh2_m2 div {
      background: #FFF;
    }

    .top_search {
      border: 1px solid #3953a4;
    }

    #country-list,
    #country-list1,
    #state-list {
      border-bottom: 1px solid #006bb1;
      border-left: 1px solid #006bb1;
      border-right: 1px solid #006bb1;
    }

    .prdt-sup-ctrl b {
      display: none;
    }

    #topbar .dropdown-menu li {
      width: 100%;
    }

    div.ryt.ser-right {
      /*padding: 0 15px 0 0 !important;*/
    }

    #right-image {
      max-width: 270px !important;
    }

    .bt.cb {
      margin: 0;
    }

    a.txt-black.h4 b {
      font-size: 17px !important;
    }

    #cssmenu {
      width: 210px !important;
    }

    .box-grid {
      border: 1px solid #999;
      height: 485px;
      background: #FFF;
    }

    .col-md-4.style_prevu_kit {
      margin: 10px 0;
    }

    .flt_wd {
      margin-top: -26px;
    }

    .style_prevu_kit:hover .box-grid {
      border: none;
    }

    .active-grid-option .compared-box.compared-box1.style_prevu_kit {
      /*height: 485px;*/
      height: 470px;
    }

    .height44 {
      height: 44px;
    }

    .col-lg-12.col-sm-9.ar-box-1.margin-top-5.padding-bottom-0.padding-top-0 {
      background-color: #fdfdfd;
    }

    .col-md-4.compared-box.compared-box1.style_prevu_kit {
      background: transparent;
    }

    .style_prevu_kit:hover {
      background: #FFF
    }

    .row.fond.active-grid-option {
      padding: 0px
    }

    .row.fond.active-grid-option .style_prevu_kit:nth-child(3n+3) {
      padding-right: 0;
    }

    .row.fond.active-grid-option .style_prevu_kit:nth-child(3n+1) {
      /*padding-left: 0;*/
    }

    .maincontainertop {
      margin: 0 auto;
    }

    .seach-page-inn #cssmenu {
      width: 190px !important;
    }

    /*div.lft.ser-mid { margin: 0 !important;   width: calc(100% - 198px) !important;}*/
    .page2-header2-col1-row2-col2 {
      width: calc(100% - 270px) !important;
    }

    .srchBx {
      margin-left: 0px !important;
      margin-top: 0 !important;
    }

    .cd-words-wrapper b {
      text-align: left !important;
      padding: 0px !important;
      margin: 0px !important;
    }

    #block_navigation .navigation .ptag:hover {
      width: 230px;
    }

    .page-header-col1-row1-col1_row2_pic#cnlocation span {
      line-height: 1
    }

    .user-name-topbar {
      color: #FFF;
    }

    .page-header-col1-row1-col1_row2 {
      margin-left: 39px !important;
      padding: 65px 0 0 !important;
    }

    #topbar .top-lft ul li {
      float: left
    }

    #topbar .top-lft ul {
      line-height: 1;
      padding-top: 15px;
    }
  }

  @media (min-width: 767px) and (max-width: 1024px) {
    .box-grid {
      height: 510px;
    }

    .active-grid-option .compared-box.compared-box1.style_prevu_kit {
      height: 510px;
    }

    .hm1.bbc.search-wrap {
      width: 100% !important
    }

    .style_prevu_kit {
      padding: 0;
    }
  }

  @media (min-width: 0px) and (max-width: 460px) {
    .box-grid {
      height: auto;
    }

    .active-grid-option .compared-box.compared-box1.style_prevu_kit {
      height: auto;
    }

    .page-header-col1-row2-col2-form #suggesstionBoxs #country-list {
      margin-top: 0px;
    }

    .post-buy-req-btn {
      height: auto
    }

    div.lft.ser-mid,
    .hm1.bbc.search-wrap {
      width: 100% !important
    }

    .style_prevu_kit {
      padding: 0;
    }
  }

  .w56.f1.p2b.p14.blr .mt5 .mp1 {
    border: 0 none;
    margin-top: 10px;
    background-image: none;
    background-position: bottom;
  }

  #blform #submitdiv,
  #blform #contact_dtl li,
  #req>ul {
    background: #faf4ff;
    margin: 0 !important;
  }

  #blform #submitdiv {
    padding-bottom: 10px;
  }

  .search-show-box .hm1.bbc .wrapper>.bt {
    display: none;
  }

  .footer-searchsec {
    max-width: 940px;
  }

  .footer-searchsec-left-head h1 {
    border: 0 none;
  }

  #res-mob1 .m_contactdetail {
    width: 68%;
  }

  #res-mob1 table.m_tmt {
    width: 100%;
  }

  #res-mob1 table.m_tmt .m_stax,
  #res-mob1 table.m_tmt .m_pricefnl,
  #res-mob1 table.m_tmt .m_pkgdetails {
    text-align: left;
  }

  #res-mob1 table.m_tmt .m_stax:first-child,
  #res-mob1 table.m_tmt .m_pricefnl:first-child,
  #res-mob1 table.m_tmt .m_pkgdetails:first-child {
    width: 52%;
  }

  .maincontainertop.clearfix.custom_quick_fix>div {
    max-width: 1200px;
    margin-left: auto;
    margin-right: auto;
  }

  #res-mob1 .my_acc_wrapper .col-md-10.my_acc_main {
    width: 83.33333333%;
    display: flex;
    flex-wrap: wrap;
  }

  .ddm span,
  #res-mob1 .my_acc_wrapper .nd2 h2 a,
  #res-mob1 .my_acc_wrapper .nd2 h2 {
    background-color: #cfeaff;
    margin: 0;
    color: #333 !important;
  }

  .seach-page-inn .ar-mid-box li>a>button.btn.border-radius-0.btn-enquiry {
    background: transparent;
    color: blue !important;
  }

  .leftnv {
    width: 180px !important;
    margin: 0px 12px 0 0;
  }

  .row>.f1.nd2.ebh3 {
    height: 122px !important;
  }

  #topbar .top-lft ul li {
    float: none;
  }

  #buy_leads_page .bg.bp1.fl.d1.a6.bo.cate_allign.col-md-3.col-sm-3.col-xs-12 {
    margin-bottom: -2px;
  }

  #buy_leads_page .bg.bp1.fl.d1.a6.bo.cate_allign.col-md-3.col-sm-3.col-xs-12 .my-market {
    padding: 0;
    margin-bottom: 0px;
    display: block;
  }

  #buy_leads_page .main-content.mid.fl.col-md-7.col-sm-7.col-xs-12 {
    padding-left: 0;
  }

  #search_result_page .seach-page-inn #search_result>.row>.col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10,
  .ser-mid div.ar-box-1[class*="col-"] {
    padding-left: 0;
    padding-right: 0;
  }

  #search_result_page .wrapper .main-warpp .maincontainertop .page-header {
    margin: 0 0 20px;
    padding-bottom: 0;
  }

  #search_result_page .seach-page-inn #search_result>.row>.col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .ar-box-1 .table tbody tr:last-child td {
    padding-bottom: 1px !important;
    padding-top: 1px;
  }

  #search_result_page .seach-page-inn .row>.col-lg-12.col-sm-9.ar-box-1.margin-top-5.padding-bottom-0.padding-top-0 .table>thead>tr:last-child>td,
  #search_result_page .seach-page-inn .row>.col-lg-12.col-sm-9.ar-box-1.margin-top-5.padding-bottom-0.padding-top-0 .table>tbody>tr:last-child>td,
  #search_result_page .seach-page-inn .row>.col-lg-12.col-sm-9.ar-box-1.margin-top-5.padding-bottom-0.padding-top-0 .table>tfoot>tr:last-child>td {
    padding: 2px;
  }

  #search_result_page .seach-page-inn .row>.col-lg-12.col-sm-9.ar-box-1.margin-top-5.padding-bottom-0.padding-top-0 .table>tbody>tr>td label {
    margin: 0;
  }

  .search-show-box-buyleads #res_row>#product_slider>.als-viewport>ul>.als-item {
    text-align: center;
  }

  #bl_overlay_layer+div[style*="padding-top:5px;padding-bottom:5px;"],
  .right-acution-side .mid.fl.col-md-12.col-sm-12.col-xs-12>.col-md-3.col-sm-3.col-xs-12[style*="padding: 5px;"] {
    padding: 0 !important;
    background: transparent !important;
  }

  .prc-right-side #final_result .middle-control-part #getcitydata>#scity {
    padding-top: 12px
  }

  .frm.fl {
    position: relative;
  }

  .fl.shd.eto-bg {
    position: absolute;
    right: -10px;
    top: 40px;
  }

  .hm1.bbc.search-wrap .row.fond.active-grid-option {
    background: transparent;
    padding: 0;
  }

  .hm1.bbc.search-wrap .row.fond.active-grid-option .col-md-4.compared-box.compared-box1.style_prevu_kit {
    border: 1px solid #999;
  }

  .hm1.bbc.search-wrap .row.fond.active-grid-option .col-md-4.compared-box.compared-box1.style_prevu_kit:hover .titleLim.box-2 a.h4 {
    color: red !important;
  }

  .page-header-col11 .page2-header2-col1-row2-col4 .footer-searchsec-right-btn {
    font-size: 16px !important;
  }

  #search_result .ar-box-1,
  #search_result .box {
    border: 1px solid #999;
  }

  @media screen and (max-width: 1280px) and (min-width: 1300px) {
    .footer-searchsec {
      max-width: 840px !important;
    }

    .aution-banner-ad {
      width: 96%;
      height: 243px;
      min-width: 90px;
    }
  }

  @media (min-width: 1400px) and (max-width: 1500px) {
    .footer-searchsec-left {
      width: calc(100% - 27%);
    }

    html body .footer-searchsec-right {
      width: 25% !important;
    }
  }

  @media (min-width: 700px) and (max-width: 900px) {
    .footer-searchsec-right-btn {
      font-size: 15px !important;
    }
  }

  @media (min-width: 992px) and (max-width: 1200px) {
    .maincontainertop.clearfix.custom_quick_fix>.col-md-3.col-sm-3.col-xs-12.col-lg-2.prc-left-side {
      width: 208px;
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-9.col-sm-9.col-lg-10.col-xs-12.prc-right-side {
      width: calc(100% - 208px);
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-9.col-sm-9.col-lg-10.col-xs-12.prc-right-side .fa_search {
      right: -19px;
      top: 9px;
    }
  }

  @media (min-width: 700px) and (max-width: 1200px) {
    .footer-searchsec-left {
      width: calc(100% - 27%);
    }

    html body .footer-searchsec-right {
      width: 25% !important;
    }
  }

  @media (min-width: 1200px) and (max-width: 1400px) {
    .footer-searchsec-left {
      width: calc(100% - 330px)
    }

    #advertisement_banner_img {
      width: calc(100% - 31%);
      padding-right: 5%;
      box-sizing: border-box;
    }

    .fside {
      width: 100% !important;
    }
  }

  @media (min-width: 1232px) and (max-width: 1280px) {
    .page2-header2-col1-row2-col2 {
      width: 74% !important;
    }
  }

  @media (min-width: 1279px) and (max-width: 1300px) {
    .page2-header2-col1-row2-col2 {
      width: 68% !important;
    }
  }

  @media (min-width: 1200px) and (max-width: 1300px) {
    #advertisement_banner_img {
      width: calc(100% - 33%);
      padding-right: 7%;
    }

    #buy_leads_page .page2-header2-col1-row2-col2 {
      width: 74% !important;
      margin-left: 0px !important;
    }

    .seach-page-inn #cssmenu {
      width: 207px !important;
    }

    div.lft.ser-mid {
      /*margin: 0 !important;
  width: calc(100% - 228px) !important;*/
    }

    .maincontainertop.clearfix.custom_quick_fix>.col-md-9.col-sm-9.col-lg-10.col-xs-12.prc-right-side .checkbox-inline+.checkbox-inline {
      margin-left: 45px !important;
    }

    .search-show-box-buyleads .maincontainertop.clearfix.custom_quick_fix>.col-md-9.col-sm-9.col-lg-10.col-xs-12.prc-right-side #final_result {
      /*width: calc(100% - 274px) !important;*/
      padding-left: 0;
      padding-right: 0;
    }
  }

  @media (min-width: 1240px) and (max-width: 1300px) {
    #buy_leads_page .page2-header2-col1-row2-col2 {
      width: 75% !important;
      margin-left: 0px !important;
    }
  }

  @media (min-width: 1279px) and (max-width: 1400px) {
    .seach-page-inn #cssmenu {
      width: 209px !important;
    }

    #buy_leads_page .page2-header2-col1-row2-col2 {
      width: 70% !important;
      margin-left: 0px !important;
    }

    .seach-page-inn .ar-mid-box .row .box-2 ul li>a {
      display: block;
    }
  }

  @media (min-width: 1100px) and (max-width: 1400px) {
    #buy_leads_page .lft1.lfl.fl.col-md-3.col-sm-3.col-xs-12 {
      width: 210px !important;
    }

    #buy_leads_page .main-content.mid.fl.col-md-7.col-sm-7.col-xs-12 {
      width: calc(100% - 467px) !important;
    }

    #bafrm form {
      float: left;
      width: 77%;
    }
  }

  @media (min-width: 1200px) {
    .search-show-box-buyleads #res_row>#product_slider>.als-viewport>ul>.als-item {
      text-align: center;
      width: 17% !important;
      margin: 10px 4% !important;
    }

    .tender-right-side {
      width: calc(100% - 253px);
    }
  }

  @media (min-width: 800px) and (max-width: 1024px) {
    .footer-searchsec {
      width: 68% !important;
    }

    .footer-searchsec-right-btn {
      font-size: 13px !important;
    }
  }

  @media (min-width: 1024px) and (max-width: 1200px) {
    .footer-searchsec {
      width: 68% !important;
    }

    .footer-searchsec-right-btn {
      font-size: 15px !important;
    }
  }

  @media (min-width: 1200px) {
    .footer-searchsec-left {
      width: calc(100% - 37%);
    }
  }

  @media (min-width: 992px) {
    .hm1.bbc.search-wrap .row.fond.active-grid-option .col-md-4.compared-box.compared-box1.style_prevu_kit {
      border: 1px solid #999;
      background: #fff;
      margin: 5px;
      width: calc(33.333% - 10px);
    }
  }

  @media (max-width: 500px) {
    #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-lg-3.big-img-box.box-1 {
      width: 100% !important;
    }

    
      #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-xs-6.col-lg-3.big-img-box.box-1 {
        width: 50% !important;
      }

      #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-lg-3.big-img-box.box-1 figure.box {
        min-height: 150px;
        height: 200px;
        width: 200px;
        margin: 10px auto;
      }

      #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-xs-6.col-lg-3.big-img-box.box-1 figure.box {
        min-height: 150px;
        width: 100%;
        margin: 10px auto;
      }

      .search-show-box-buyleads #res_row>#product_slider>.als-viewport>ul>.als-item>a>div[style*="height:0%;"] {
        height: auto !important;
      }

      .search-show-box-buyleads #res_row>#product_slider>.als-viewport>ul>.als-item>a>div[style*="margin-top:16%;"] {
        margin-top: 10px !important;
      }

      .seach-page-inn .big-img-box.box-1 {
        width: 100% !important;
        margin-bottom: 10px;
      }

      .seach-page-inn .ar-box-1 .box-2 {
        width: 99% !important;
      }

      .seach-page-inn .ar-box-1 .col-xs-6.box-2 {
        width: 50% !important;
        padding-right: 5px;
      }

      .seach-page-inn .box {
        height: 200px;
        width: 200px;
        margin-left: auto;
        margin-right: auto;
        border-color: #999;
      }
  }

  @media (max-width: 360px) {
    .container.top-bar>.row {
        margin-left: 0;
        margin-right: 0;
    }
    .margin-bottom-10 {
      margin-bottom: 0 !important;
    }
    .big-img-box.box-1 {
      max-width: 100% !important;
    }
    #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-lg-3.big-img-box.box-1 {
      width: 100% !important;
    }

    
      #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-xs-6.col-lg-3.big-img-box.box-1 {
        width: 100% !important;
      }

      #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-lg-3.big-img-box.box-1 figure.box {
        min-height: 150px;
        height: 200px;
        width: 200px;
        margin: 10px auto;
      }

      #search_result_page #search_result .row .col-lg-12.col-sm-11.col-md-11.ar-box-1.margin-top-10 .row .col-xs-6.col-lg-3.big-img-box.box-1 figure.box {
        min-height: 150px;
        width: 100%;
        margin: 10px auto;
      }

      .search-show-box-buyleads #res_row>#product_slider>.als-viewport>ul>.als-item>a>div[style*="height:0%;"] {
        height: auto !important;
      }

      .search-show-box-buyleads #res_row>#product_slider>.als-viewport>ul>.als-item>a>div[style*="margin-top:16%;"] {
        margin-top: 10px !important;
      }

      .seach-page-inn .big-img-box.box-1 {
        width: 100% !important;
        margin-bottom: 10px;
      }

      .seach-page-inn .ar-box-1 .box-2 {
        width: 99% !important;
      }

      .seach-page-inn .ar-box-1 .col-xs-6.box-2 {
        width: 100% !important;
        padding-right: 15px;
        padding-left: 15px !important;
        padding-top: 0 !important;
      }

      .seach-page-inn .box {
        height: 200px;
        width: 200px;
        margin-left: auto;
        margin-right: auto;
        border-color: #999;
      }
  }
    @media (width:800px) {
      .footer-searchsec-right {
        margin-top: -3px !important;
      }
  }
</style>
<nav class="mobile-bottom-nav" aria-label="Mobile navigation">
  <a href="index.php"><i class="fa fa-home"></i><span>Home</span></a>
  <a href="dir.php#main_cat"><i class="fa fa-th-large"></i><span>Categories</span></a>
  <a href="buyleads.php"><i class="fa fa-list-alt"></i><span>RFQs</span></a>
  <a href="product-sel-cat.php" class="mobile-bottom-nav-add"><i class="fa fa-plus-circle"></i><span>Add</span></a>
  <a href="my-dashboard.php"><i class="fa fa-user-circle"></i><span>Account</span></a>
</nav>
<script>
(function () {
  function cleanScenarioText(text) {
    return String(text || '').replace(/\s+/g, ' ').trim();
  }
  function scenarioUrl(text) {
    return 'search.php?keywords=' + encodeURIComponent(cleanScenarioText(text)) + '&rctyp=Products&search_mode=scenario';
  }
  function postScenario(text, type) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', 'ai-search.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    xhr.onreadystatechange = function () {
      if (xhr.readyState !== 4) return;
      try {
        var response = JSON.parse(xhr.responseText || '{}');
        if (response && response.success && response.redirect_url) {
          window.location.href = response.redirect_url;
          return;
        }
      } catch (e) {}
      window.location.href = scenarioUrl(text);
    };
    xhr.onerror = function () {
      window.location.href = scenarioUrl(text);
    };
    xhr.send('request_text=' + encodeURIComponent(text) + '&keywords=' + encodeURIComponent(text) + '&rctyp=' + encodeURIComponent(type || 'Products') + '&page_url=' + encodeURIComponent(window.location.href));
  }
  function setup(buttonId, inputId, formId, modeId) {
    var button = document.getElementById(buttonId);
    var input = document.getElementById(inputId);
    var form = document.getElementById(formId);
    var mode = document.getElementById(modeId);
    if (!button || !input || !form) return;
    button.onclick = null;
    button.addEventListener('click', function (event) {
      event.preventDefault();
      event.stopImmediatePropagation();
      var active = !button.classList.contains('active');
      button.classList.toggle('active', active);
      if (mode) {
        if (mode.type === 'checkbox') {
          mode.checked = active;
          mode.value = 'scenario';
        } else {
          mode.value = active ? 'scenario' : 'standard';
        }
      }
      input.placeholder = active ? 'اكتب طلبك كاملا: أحتاج شراء كمية كبيرة من الفول مع توصيل سريع' : input.getAttribute('data-default-placeholder') || input.placeholder;
    }, true);
    if (!input.getAttribute('data-default-placeholder')) input.setAttribute('data-default-placeholder', input.placeholder || '');
    form.addEventListener('submit', function (event) {
      if (mode && mode.type === 'checkbox') {
        mode.value = mode.checked ? 'scenario' : 'standard';
      }
      if (!button.classList.contains('active')) return;
      event.preventDefault();
      var text = input.value.replace(/\s+/g, ' ').trim();
      if (!text) {
        input.focus();
        return;
      }
      button.classList.add('loading');
      button.disabled = true;
      button.innerHTML = 'AI...';
      postScenario(text, 'Products');
    }, true);
  }
  function bindAll() {
    setup('smartSearchMode', 'search-box1', 'hdr_frm', 'search_mode');
    setup('smartSearchModeInner', 'search-box11', 'hdr_frm', 'search_mode_inner');
    setup('homeSmartSearchMode', 'keywords_r', 'homeSmartSearchForm', 'home_search_mode');
  }
  bindAll();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindAll);
  }
  var tries = 0;
  var timer = setInterval(function () {
    bindAll();
    tries += 1;
    if (tries > 12) clearInterval(timer);
  }, 500);
})();
</script>
<div id="image_modal" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content" style="margin-top: 10vh;">
      <img id="dyn_image" style="width: 100%;" >
    </div>
  </div>
</div>
<script type="text/javascript">
  function zoom_image(ele){
  
    var imagex = $(ele).data('img');
  
    console.log(imagex);
  
    $('#dyn_image').attr('src',imagex);
  
    $('#image_modal').modal('show');
  
  
  
  }
  
  
  function open_chat(){
  
    Tawk_API.toggle();
  }
  
</script>
<script>
$(document).ready(function() {
    $('#left_ajax_geting').css('display','block');
});
</script>
<script>
(function(){
  function closestSaleOfferContainer(node) {
    while (node && node !== document) {
      if (node.getElementsByTagName && node.getElementsByTagName('a').length) return node;
      node = node.parentNode;
    }
    return null;
  }
  function offerTokenFromRow(row) {
    var allLinks = row.getElementsByTagName ? row.getElementsByTagName('a') : [];
    var links = [];
    for (var i = 0; i < allLinks.length; i++) {
      var linkHref = allLinks[i].getAttribute('href') || '';
      if (linkHref.indexOf('saleoffer-details.php') !== -1 || linkHref.indexOf('selloffer-details.php') !== -1) {
        links.push(allLinks[i]);
      }
    }
    for (var i = 0; i < links.length; i++) {
      var href = links[i].getAttribute('href') || '';
      var match = href.match(/[?&](?:id|token)=([^&#]+)/);
      if (match && match[1]) return match[1];
    }
    return '';
  }
  function updateSaleOfferImages() {
    var allImgs = document.getElementsByTagName('img');
    var imgs = [];
    for (var x = 0; x < allImgs.length; x++) {
      var src = allImgs[x].getAttribute('src') || '';
      if (src.indexOf('upload/sale_offer/no-image.png') !== -1) {
        imgs.push(allImgs[x]);
      }
    }
    for (var i = 0; i < imgs.length; i++) {
      (function(img){
        if (img.getAttribute('data-saleoffer-lookup') === '1') return;
        var row = closestSaleOfferContainer(img.parentNode);
        if (!row) return;
        var token = offerTokenFromRow(row);
        if (!token) return;
        img.setAttribute('data-saleoffer-lookup', '1');
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/saleoffer-details.php?image_lookup=1&id=' + token, true);
        xhr.onreadystatechange = function(){
          if (xhr.readyState !== 4 || xhr.status !== 200) return;
          var data = null;
          try {
            data = JSON.parse(xhr.responseText);
          } catch (e) {
            data = null;
          }
            if (data && data.success && data.image && data.image.indexOf('no-image.png') === -1) {
              img.src = '/' + data.image.replace(/^\/+/, '');
              img.alt = img.alt || 'Sale Offer Image';
            }
        };
        xhr.send();
      })(imgs[i]);
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateSaleOfferImages);
  } else {
    updateSaleOfferImages();
  }
  setTimeout(updateSaleOfferImages, 1000);
  setTimeout(updateSaleOfferImages, 3000);
  setTimeout(updateSaleOfferImages, 6000);
  setTimeout(updateSaleOfferImages, 10000);
  var saleOfferImageTimerCount = 0;
  var saleOfferImageTimer = setInterval(function(){
    updateSaleOfferImages();
    saleOfferImageTimerCount += 1;
    if (saleOfferImageTimerCount > 20) {
      clearInterval(saleOfferImageTimer);
    }
  }, 1000);
})();
</script>
<style>
body.directory-template .dir-cards-host {
  float: none !important;
  width: calc(100vw - 44px) !important;
  max-width: none;
  margin: 8px auto 18px !important;
  display: grid !important;
  grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
  gap: 12px;
  direction: rtl;
  clear: both;
  padding: 0;
  box-sizing: border-box;
}
body.directory-template .dir-cards-host > .bc {
  display: none !important;
}
body.directory-template .wrapper,
body.directory-template .inner_wrapper {
  max-width: none !important;
  width: calc(100vw - 44px) !important;
}
body.directory-template .inner_wrapper .ryt,
body.directory-template .inner_wrapper .lft,
body.directory-template div.lft {
  width: 100% !important;
  max-width: none !important;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] {
  float: none !important;
  width: auto !important;
  min-height: 0;
  margin: 0 !important;
  padding: 0 !important;
  overflow: hidden;
  background: #ffffff;
  border: 1px solid #8fc8e9;
  border-radius: 8px;
  box-shadow: 0 6px 16px rgba(8, 47, 73, .12);
}
body.directory-template .dir-cards-host > div[id^="main_cat_"]:hover {
  border-color: #0284c7;
  box-shadow: 0 12px 28px rgba(8, 47, 73, .18);
  transform: translateY(-2px);
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] p.p4 {
  display: none;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table {
  width: 100% !important;
  border-collapse: collapse !important;
  background: #f2f9ff;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table tbody,
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table tr {
  display: block;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table tr {
  position: relative;
  min-height: 96px;
  padding: 12px 116px 10px 14px;
  border-bottom: 1px solid #c8e4f6;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table td {
  display: block;
  width: auto !important;
  padding: 0 !important;
  background: transparent !important;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table td:first-child {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 88px !important;
  height: 72px;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table img {
  width: 88px !important;
  height: 72px !important;
  object-fit: cover;
  border-radius: 8px;
  border: 1px solid #9fcae5;
  background: #fff;
  padding: 2px;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table a {
  display: inline-block;
  color: #062f4f !important;
  font-size: 21px;
  font-weight: 800;
  line-height: 1.35;
  text-decoration: none !important;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] > table a:after {
  content: "›";
  display: inline-block;
  margin-right: 8px;
  color: #f97316;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu {
  margin: 0 !important;
  padding: 8px 10px 10px !important;
  list-style: none !important;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 6px;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu li {
  height: auto !important;
  max-height: none !important;
  margin: 0 !important;
  overflow: hidden !important;
  padding: 0 !important;
  list-style: none !important;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu li:nth-child(n+9) {
  display: none !important;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu li > br,
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu li > span {
  display: none !important;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu a {
  display: block;
  min-height: 32px;
  padding: 7px 10px;
  overflow: hidden;
  color: #111827 !important;
  background: #ffffff;
  border: 1px solid #b9dff4;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 800;
  line-height: 1.4;
  text-decoration: none !important;
  text-overflow: ellipsis;
  white-space: nowrap;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu a.txt-blue {
  color: #082f49 !important;
  background: #e3f4ff;
  border-color: #9ed8f8;
}
body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu a:hover {
  color: #fff !important;
  background: #16834f;
  border-color: #16834f;
}
@media (max-width: 767px) {
  body.directory-template .inner_wrapper {
    width: 100% !important;
    padding: 0 10px;
    box-sizing: border-box;
  }
  body.directory-template .dir-cards-host {
    grid-template-columns: 1fr;
    gap: 10px;
    margin-top: 12px !important;
    padding: 0;
  }
  body.directory-template .dir-cards-host > div[id^="main_cat_"] {
    min-height: 0;
  }
  body.directory-template .dir-cards-host > div[id^="main_cat_"] ul.sdu {
    grid-template-columns: 1fr;
  }
}
</style>
<script>
(function(){
  if (!/\/dir\.php$/i.test(window.location.pathname)) return;
  function applyDirectoryCards() {
    var cards = document.querySelectorAll('div[id^="main_cat_"]');
    if (!cards.length) return;
    var host = cards[0].parentNode;
    if (!host || host.className.indexOf('dir-cards-host') !== -1) return;
    host.className += ' dir-cards-host';
    for (var i = 0; i < cards.length; i++) {
      var card = cards[i];
      card.className += ' dir-category-card';
      var img = card.querySelector('img');
      if (img) img.removeAttribute('align');
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', applyDirectoryCards);
  } else {
    applyDirectoryCards();
  }
  setTimeout(applyDirectoryCards, 800);
})();
</script>
<style>
@media (max-width: 768px) {
	  body.homepage-mobile-reveal {
	    background: #e8eaeb !important;
	    overflow-x: hidden;
	    padding-bottom: 76px;
	  }
	  body.homepage-mobile-reveal .middlesection,
	  body.homepage-mobile-reveal .maincontainer,
	  body.homepage-mobile-reveal .inner_wrapper,
	  body.homepage-mobile-reveal .wrapper,
	  body.homepage-mobile-reveal .home-rtl-flow,
	  body.homepage-mobile-reveal #leftsection,
	  body.homepage-mobile-reveal #midcenter,
	  body.homepage-mobile-reveal #rightsection {
	    background: #e8eaeb !important;
	  }
  body.homepage-mobile-reveal .wrapper,
  body.homepage-mobile-reveal .inner_wrapper,
  body.homepage-mobile-reveal .home-rtl-flow {
    box-sizing: border-box !important;
    margin-left: auto !important;
    margin-right: auto !important;
    max-width: 430px !important;
    padding-left: 10px !important;
    padding-right: 10px !important;
    width: 100% !important;
  }
  body.homepage-mobile-reveal #leftsection,
  body.homepage-mobile-reveal #midcenter,
  body.homepage-mobile-reveal #rightsection {
    clear: both !important;
    float: none !important;
    margin: 0 auto 32px !important;
    max-width: 430px !important;
    position: static !important;
    top: auto !important;
    width: 100% !important;
  }
  body.homepage-mobile-reveal .home-rtl-flow {
    display: flex !important;
    flex-direction: column !important;
  }
  body.homepage-mobile-reveal #rightsection {
    display: block !important;
    order: 1 !important;
  }
  body.homepage-mobile-reveal #leftsection {
    display: flex !important;
    flex-direction: column !important;
    order: 2 !important;
  }
  body.homepage-mobile-reveal #midcenter {
    display: block !important;
    order: 3 !important;
  }
  body.homepage-mobile-reveal .allcate,
  body.homepage-mobile-reveal #block_navigation,
  body.homepage-mobile-reveal #left_ajax_geting,
  body.homepage-mobile-reveal .home-mobile-rfq-form,
  body.homepage-mobile-reveal .home-mobile-yahoo-after-rfq,
  body.homepage-mobile-reveal .slider.r_css,
  body.homepage-mobile-reveal .video_slider,
  body.homepage-mobile-reveal .mobile-slider,
  body.homepage-mobile-reveal .desktop-hide,
  body.homepage-mobile-reveal .desktop-only,
  body.homepage-mobile-reveal .list-rights.desktop-only,
  body.homepage-mobile-reveal .countrubox_top2 .desktop-only,
  body.homepage-mobile-reveal .demobox,
  body.homepage-mobile-reveal .demobox.oyee,
  body.homepage-mobile-reveal .course_demo,
  body.homepage-mobile-reveal .center-top,
  body.homepage-mobile-reveal .country-wrapper-with-verfier,
  body.homepage-mobile-reveal .bcg-sap_tabs,
  body.homepage-mobile-reveal .sap_tabs,
  body.homepage-mobile-reveal .seniorbox-box,
  body.homepage-mobile-reveal .hide-in-mobile,
  body.homepage-mobile-reveal .testimonial-2 {
    box-sizing: border-box !important;
    clear: both !important;
    display: block !important;
    float: none !important;
    height: auto !important;
    max-width: 100% !important;
    min-height: 0 !important;
    opacity: 1 !important;
    overflow: visible !important;
    position: relative !important;
    visibility: visible !important;
    width: 100% !important;
  }
  body.homepage-mobile-reveal .home-mobile-rfq-form,
  body.homepage-mobile-reveal .home-mobile-yahoo-after-rfq,
  body.homepage-mobile-reveal .slider.r_css,
  body.homepage-mobile-reveal .video_slider,
  body.homepage-mobile-reveal .mobile-slider,
  body.homepage-mobile-reveal .desktop-hide,
  body.homepage-mobile-reveal .desktop-only,
  body.homepage-mobile-reveal .demobox,
  body.homepage-mobile-reveal .course_demo,
  body.homepage-mobile-reveal .center-top,
  body.homepage-mobile-reveal .country-wrapper-with-verfier,
  body.homepage-mobile-reveal .bcg-sap_tabs,
  body.homepage-mobile-reveal .sap_tabs,
  body.homepage-mobile-reveal .seniorbox-box,
  body.homepage-mobile-reveal .testimonial-2,
  body.homepage-mobile-reveal #block_navigation {
	    background: #f1f1f1 !important;
	    border: 0 !important;
	    border-radius: 8px;
	    box-shadow: 0 1px 0 rgba(15, 23, 42, .06);
	    margin: 0 auto 34px !important;
	    padding: 14px 10px !important;
  }
	  body.homepage-mobile-reveal #block_navigation {
	    order: 1 !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-buytable-first,
	  body.homepage-mobile-reveal #leftsection > .bcg-sap_tabs {
	    margin-bottom: 34px !important;
	    order: 0 !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-rfq-form {
	    order: 2 !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-after-rfq {
	    order: 3 !important;
	  }
		  body.homepage-mobile-reveal .home-mobile-yahoo-after-rfq {
		    background: #f1f1f1 !important;
		    border: 1px solid #d7d7d7 !important;
		    padding: 8px 8px 10px !important;
		    position: relative !important;
		  }
		  body.homepage-mobile-reveal .home-mobile-yahoo-title {
		    color: #333 !important;
		  }
		  body.homepage-mobile-reveal .home-mobile-yahoo-frame {
		    background: #fff !important;
		    border: 1px solid #d8d8d8 !important;
		    box-sizing: border-box !important;
		    padding: 7px !important;
		    position: relative !important;
		  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-track {
	    display: flex !important;
	    min-height: 142px !important;
	    overflow-x: auto !important;
	    overflow-y: hidden !important;
	    scroll-snap-type: x mandatory !important;
	    -webkit-overflow-scrolling: touch !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-track::-webkit-scrollbar {
	    display: none !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-slide {
	    background: #fff !important;
	    display: block !important;
	    flex: 0 0 100% !important;
	    scroll-snap-align: center !important;
	    text-align: center !important;
	    text-decoration: none !important;
	    width: 100% !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-slide.is-active {
	    display: block !important;
	  }
		  body.homepage-mobile-reveal .home-mobile-yahoo-slide img {
		    background: #fff !important;
		    border-radius: 4px !important;
		    display: block !important;
		    height: 130px !important;
		    margin: 0 auto !important;
		    object-fit: contain !important;
		    width: 100% !important;
		  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-slide span {
	    background: #f4f4f4 !important;
	    border: 1px solid #e0e0e0 !important;
	    color: #333 !important;
	    display: block !important;
	    font-size: 13px !important;
	    font-weight: 700 !important;
	    line-height: 1.35 !important;
	    margin-top: 7px !important;
	    min-height: 18px !important;
	    text-align: center !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-nav {
	    align-items: center !important;
	    background: rgba(32, 133, 189, .9) !important;
	    border: 0 !important;
	    border-radius: 50% !important;
	    color: #fff !important;
	    cursor: pointer !important;
	    display: none !important;
	    font-size: 28px !important;
	    font-weight: 800 !important;
	    height: 34px !important;
	    justify-content: center !important;
	    line-height: 1 !important;
	    margin-top: -22px !important;
	    padding: 0 !important;
	    position: absolute !important;
	    top: 50% !important;
	    width: 34px !important;
	    z-index: 2 !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-prev {
	    left: 8px !important;
	  }
	  body.homepage-mobile-reveal .home-mobile-yahoo-next {
	    right: 8px !important;
	  }
	  body.homepage-mobile-reveal .video_slider.mobile-slider {
	    order: 4 !important;
	  }
  body.homepage-mobile-reveal .home-mobile-rfq-form,
  body.homepage-mobile-reveal .home-mobile-yahoo-after-rfq {
    margin-top: 34px !important;
  }
  body.homepage-mobile-reveal .countrubox_top2,
  body.homepage-mobile-reveal .space21 {
    display: block !important;
    height: auto !important;
    margin: 0 auto 28px !important;
    width: 100% !important;
  }
		  body.homepage-mobile-reveal .slider.r_css {
		    display: none !important;
		  }
		  body.homepage-mobile-reveal .yahoo_slider {
		    display: none !important;
		  }
		  body.homepage-mobile-reveal .course_demo .slick-list,
		  body.homepage-mobile-reveal .course_demo .slick-track,
		  body.homepage-mobile-reveal .course_demo .slick-slide {
		    min-height: 150px !important;
		  }
		  body.homepage-mobile-reveal .course_demo,
		  body.homepage-mobile-reveal .nbs-flexisel-container,
		  body.homepage-mobile-reveal .nbs-flexisel-inner,
		  body.homepage-mobile-reveal .slick-slider,
		  body.homepage-mobile-reveal .slick-list,
		  body.homepage-mobile-reveal .slick-track,
		  body.homepage-mobile-reveal .main-slick-wrapper-item {
		    background: #f1f1f1 !important;
		  }
		  body.homepage-mobile-reveal .nbs-flexisel-inner {
		    margin-left: 0 !important;
		    margin-right: 0 !important;
		    overflow: hidden !important;
		    width: 100% !important;
		  }
	  body.homepage-mobile-reveal .nbs-flexisel-nav-left,
	  body.homepage-mobile-reveal .nbs-flexisel-nav-right,
	  body.homepage-mobile-reveal .white_bg > .welcome_desc > .course_demo > .nbs-flexisel-container > .nbs-flexisel-inner .nbs-flexisel-nav-left,
	  body.homepage-mobile-reveal .white_bg > .welcome_desc > .course_demo > .nbs-flexisel-container > .nbs-flexisel-inner .nbs-flexisel-nav-right {
	    align-items: center !important;
	    background: rgba(32, 133, 189, .88) !important;
	    border-radius: 50% !important;
	    color: #fff !important;
		    display: none !important;
	    font-size: 26px !important;
	    font-weight: 800 !important;
	    height: 36px !important;
	    justify-content: center !important;
	    margin-top: -18px !important;
	    top: 50% !important;
	    width: 36px !important;
	    z-index: 20 !important;
	  }
	  body.homepage-mobile-reveal .nbs-flexisel-nav-left,
	  body.homepage-mobile-reveal .white_bg > .welcome_desc > .course_demo > .nbs-flexisel-container > .nbs-flexisel-inner .nbs-flexisel-nav-left {
	    left: 6px !important;
	  }
	  body.homepage-mobile-reveal .nbs-flexisel-nav-right,
	  body.homepage-mobile-reveal .white_bg > .welcome_desc > .course_demo > .nbs-flexisel-container > .nbs-flexisel-inner .nbs-flexisel-nav-right {
	    right: 6px !important;
	  }
	  body.homepage-mobile-reveal .nbs-flexisel-nav-left:before,
	  body.homepage-mobile-reveal .white_bg > .welcome_desc > .course_demo > .nbs-flexisel-container > .nbs-flexisel-inner .nbs-flexisel-nav-left:before {
	    content: "‹";
	  }
	  body.homepage-mobile-reveal .nbs-flexisel-nav-right:before,
	  body.homepage-mobile-reveal .white_bg > .welcome_desc > .course_demo > .nbs-flexisel-container > .nbs-flexisel-inner .nbs-flexisel-nav-right:before {
	    content: "›";
	  }
	  body.homepage-mobile-reveal .course_demo,
	  body.homepage-mobile-reveal .nbs-flexisel-container {
	    width: 100% !important;
	  }
	  body.homepage-mobile-reveal .nbs-flexisel-item {
	    padding: 0 4px !important;
	  }
		  body.homepage-mobile-reveal .nbs-flexisel-item img {
		    background: #fff !important;
		    border: 1px solid #e1e1e1 !important;
		    box-sizing: border-box !important;
		    max-width: 168px !important;
		    padding: 4px !important;
		  }
	  body.homepage-mobile-reveal .slick-slider .slick-prev,
	  body.homepage-mobile-reveal .slick-slider .slick-next {
	    align-items: center !important;
	    background: rgba(32, 133, 189, .88) !important;
	    border: 0 !important;
	    border-radius: 50% !important;
	    color: #fff !important;
	    display: none !important;
	    font-size: 0 !important;
	    font-weight: 800 !important;
	    height: 36px !important;
	    justify-content: center !important;
	    line-height: 1 !important;
	    margin-top: -18px !important;
	    opacity: 1 !important;
	    overflow: hidden !important;
	    padding: 0 !important;
	    position: absolute !important;
	    top: 50% !important;
	    width: 36px !important;
	    z-index: 20 !important;
	  }
	  body.homepage-mobile-reveal .slick-slider .slick-prev {
	    left: 6px !important;
	  }
	  body.homepage-mobile-reveal .slick-slider .slick-next {
	    right: 6px !important;
	  }
	  body.homepage-mobile-reveal .slick-slider .slick-prev:before,
	  body.homepage-mobile-reveal .slick-slider .slick-next:before {
	    color: #fff !important;
	    display: block !important;
	    font-size: 28px !important;
	    line-height: 36px !important;
	    opacity: 1 !important;
	  }
	  body.homepage-mobile-reveal .slick-slider .slick-prev:before {
	    content: "‹" !important;
	  }
	  body.homepage-mobile-reveal .slick-slider .slick-next:before {
	    content: "›" !important;
	  }
	  body.homepage-mobile-reveal .main-slick-wrapper-item {
	    padding-left: 6px !important;
	    padding-right: 6px !important;
	  }
	  body.homepage-mobile-reveal .slick-product-wrapper {
	    background: #fff !important;
	    border: 1px solid #e1e1e1 !important;
	    max-width: 145px !important;
	    padding: 8px 4px !important;
	    width: 145px !important;
	  }
	  body.homepage-mobile-reveal .slick-product-image {
	    background: #fff !important;
	    height: 118px !important;
	    margin-bottom: 6px !important;
	  }
	  body.homepage-mobile-reveal .slick-product-image img {
	    height: 116px !important;
	    max-height: 116px !important;
	    object-fit: contain !important;
	    width: 100% !important;
	  }
  body.homepage-mobile-reveal img,
  body.homepage-mobile-reveal iframe {
    height: auto !important;
    max-width: 100% !important;
  }
  body.homepage-mobile-reveal .slick-product-image img {
    display: block !important;
    min-height: 82px;
    object-fit: contain;
    width: 100% !important;
  }
	  body.homepage-mobile-reveal #rightsection .bcg-sap_tabs,
	  body.homepage-mobile-reveal #rightsection .sap_tabs,
	  body.homepage-mobile-reveal #rightsection .seniorbox-box {
	    margin-bottom: 34px !important;
	  }
	  body.homepage-mobile-reveal .bcg-sap_tabs .leftleads,
	  body.homepage-mobile-reveal .home-mobile-buytable-first .leftleads {
	    background: #3f3f3f !important;
	    border-radius: 0 !important;
	    color: #fff !important;
	    margin: 0 !important;
	    padding: 7px 10px !important;
	  }
	  body.homepage-mobile-reveal .bcg-sap_tabs .leftleads h2,
	  body.homepage-mobile-reveal .bcg-sap_tabs .leftleads h2 a,
	  body.homepage-mobile-reveal .home-mobile-buytable-first .leftleads h2,
	  body.homepage-mobile-reveal .home-mobile-buytable-first .leftleads h2 a {
	    background: #3f3f3f !important;
	    color: #fff !important;
	    font-size: 15px !important;
	    margin: 0 !important;
	    padding: 0 !important;
	  }
	  body.homepage-mobile-reveal .nbs-flexisel-nav-left,
	  body.homepage-mobile-reveal .nbs-flexisel-nav-right,
	  body.homepage-mobile-reveal .slick-slider .slick-prev,
	  body.homepage-mobile-reveal .slick-slider .slick-next {
	    display: none !important;
	  }
	}
</style>
<script>
(function(){
	  var path = window.location.pathname.replace(/\/+$/, '');
	  if (path !== '' && path !== '/' && path !== '/index.php' && path !== '/index') return;
  document.body.className += ' homepage-mobile-reveal';
	  function showHomepageMobileSections() {
	    if (!window.matchMedia || !window.matchMedia('(max-width: 768px)').matches) return;
	    var selectors = [
      '.home-mobile-rfq-form',
      '.home-mobile-yahoo-after-rfq',
      '.desktop-hide',
      '.demobox.oyee',
      '.country-wrapper-with-verfier',
      '.slider.r_css',
      '#rightsection',
      '.bcg-sap_tabs',
      '.sap_tabs',
      '.seniorbox-box.hide-in-mobile',
      '#block_navigation'
    ];
    for (var i = 0; i < selectors.length; i++) {
      var nodes = document.querySelectorAll(selectors[i]);
      for (var j = 0; j < nodes.length; j++) {
        nodes[j].style.display = 'block';
        nodes[j].style.visibility = 'visible';
        nodes[j].style.opacity = '1';
	      }
	    }
	  }
	  function arrangeHomepageMobileSections() {
	    if (!window.matchMedia || !window.matchMedia('(max-width: 768px)').matches) return;
	    var buyTable = document.querySelector('#rightsection .bcg-sap_tabs') || document.querySelector('.bcg-sap_tabs');
	    var categories = document.querySelector('#block_navigation');
	    var left = categories ? categories.closest('#leftsection') : document.querySelector('#leftsection');
	    var right = buyTable ? buyTable.closest('#rightsection') : document.querySelector('#rightsection');
	    var categoryHeading = left ? left.querySelector('.allcate') : null;
	    var categoryStart = categoryHeading || categories;
	    var rfq = document.querySelector('.home-mobile-rfq-form');
	    var yahoo = document.querySelector('.home-mobile-yahoo-after-rfq');
	    if (!left) return;
	    if (buyTable && categoryStart) {
	      if (buyTable.className.indexOf('home-mobile-buytable-first') === -1) {
	        buyTable.className += ' home-mobile-buytable-first';
	      }
	      if (buyTable.parentNode !== left || buyTable.nextElementSibling !== categoryStart) {
	        left.insertBefore(buyTable, categoryStart);
	      }
	    }
	    if (categories && rfq && rfq.previousElementSibling !== categories) {
	      left.insertBefore(rfq, categories.nextSibling);
	    }
	    if (rfq && yahoo && yahoo.previousElementSibling !== rfq) {
	      left.insertBefore(yahoo, rfq.nextSibling);
	    }
	    if (right && !right.querySelector('.bcg-sap_tabs')) {
	      right.style.display = 'none';
	    }
	  }
	  function initMobileYahooSlider() {
	    var boxes = document.querySelectorAll('.home-mobile-yahoo-after-rfq');
	    for (var i = 0; i < boxes.length; i++) {
	      var box = boxes[i];
	      if (box.getAttribute('data-yahoo-ready') === '1') continue;
	      var slides = box.querySelectorAll('.home-mobile-yahoo-slide');
	      if (!slides.length) continue;
	      if (window.matchMedia && window.matchMedia('(max-width: 768px)').matches) {
	        for (var swipeIndex = 0; swipeIndex < slides.length; swipeIndex++) {
	          slides[swipeIndex].style.display = 'block';
	        }
	        var swipePrev = box.querySelector('.home-mobile-yahoo-prev');
	        var swipeNext = box.querySelector('.home-mobile-yahoo-next');
	        if (swipePrev) swipePrev.style.display = 'none';
	        if (swipeNext) swipeNext.style.display = 'none';
	        box.setAttribute('data-yahoo-ready', '1');
	        continue;
	      }
	      var index = 0;
	      var show = function(nextIndex) {
	        index = (nextIndex + slides.length) % slides.length;
	        for (var j = 0; j < slides.length; j++) {
	          if (j === index) {
	            slides[j].className = slides[j].className.replace(/\s*is-active/g, '') + ' is-active';
	            slides[j].style.display = 'block';
	          } else {
	            slides[j].className = slides[j].className.replace(/\s*is-active/g, '');
	            slides[j].style.display = 'none';
	          }
	        }
	      };
	      var prev = box.querySelector('.home-mobile-yahoo-prev');
	      var next = box.querySelector('.home-mobile-yahoo-next');
	      if (prev) {
	        prev.onclick = function(fn) {
	          return function(e) {
	            e.preventDefault();
	            fn(-1);
	          };
	        }(function(step){ show(index + step); });
	      }
	      if (next) {
	        next.onclick = function(fn) {
	          return function(e) {
	            e.preventDefault();
	            fn(1);
	          };
	        }(function(step){ show(index + step); });
	      }
	      show(0);
	      box.setAttribute('data-yahoo-ready', '1');
	    }
	  }
	  function runHomepageMobileUpdates() {
	    showHomepageMobileSections();
	    arrangeHomepageMobileSections();
	    initMobileYahooSlider();
	  }
	  if (document.readyState === 'loading') {
	    document.addEventListener('DOMContentLoaded', runHomepageMobileUpdates);
	  } else {
	    runHomepageMobileUpdates();
	  }
	  setTimeout(runHomepageMobileUpdates, 800);
	  setTimeout(runHomepageMobileUpdates, 2500);
	  window.addEventListener('resize', runHomepageMobileUpdates);
	})();
	</script>
<!--<script type="text/javascript" src="http://workfromhomecompanies.net/its/ehabfa/livechat/php/app.php?widget-init.js"></script>-->
</body>
</html>
