<?php
/**
 * File: footer.php
 * Version: 3.0.0 (PHP 8.3)
 * Description: Footer with all original features
 */
declare(strict_types=1);

// Google Adsense - استخدام prepared statement
$sql_ga = "SELECT * FROM google_adsense WHERE ga_status = '1' ORDER BY RAND() LIMIT 1";
$stmt_ga = mysqli_prepare($con, $sql_ga);
$ga_content = '';

if ($stmt_ga) {
    mysqli_stmt_execute($stmt_ga);
    $result_ga = mysqli_stmt_get_result($stmt_ga);
    
    if (mysqli_num_rows($result_ga) > 0) {
        $row_ga = mysqli_fetch_object($result_ga);
        $ga_content = $row_ga->ga_content ?? '';
    }
    mysqli_stmt_close($stmt_ga);
}

if (!empty($ga_content)): ?>
<div style="text-align:center;padding-top:5px;padding-bottom:5px;">
    <?php echo $ga_content; ?>
</div>
<?php endif; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Tajawal', sans-serif;
}
</style>
<link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>

<!-- footer start -->
</div> <!-- إغلاق wrapper من header -->
</div> <!-- إغلاق main-warpp من header -->

<footer class="footer">
    <!-- footer-searchsec start -->
    <div class="footer-searchsec">
        <div class="footer-searchsec-left">
            <div class="footer-searchsec-left-head">
                <div class="srchBx">
                    <h1 class="cd-headline clip is-full-width">
                        <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper">
                            <b class="is-visible"><span class="blinking-cursor" style="color: red">!</span> إبحث عن مقدمى خدمات تجارية تم تقييمهم عن طريق المنصة</b>
                            <b class="is-hidden"><span class="blinking-cursor" style="color: red">!</span> أنشر طلب تسعير خدمة تجارية وتلقى أسعار من عدة شركات</b>
                        </span>
                    </h1>
                </div>
            </div>
            
            <div class="footer-searchsec-left-form">
                <form autocomplete="off" name="searchForm" action="search.php" onsubmit="return validsearch()" method="GET" id="hdr_frm">
                    <input type="hidden" id="rctyp" name="rctyp" value="Products"/>
                    <div>
                        <div class="footer-searchsec-left-form-col1">
                            <p style="font-size: 10px; font-weight:bold;" title="Search Business Services">خــدمات</p>
                        </div>
                        <div class="footer-searchsec-left-form-col2" title="Search for any Business Services">
                            <input type="text" id="search-box2" name="keywords" 
                                   placeholder="إبحث عن الاف من الخدمات التجارية للتجارة >>"
                                   class="footer-searchsec-left-form-col2-input"/>
                        </div>
                        <div class="footer-searchsec-left-form-col3">
                            <input type="submit" value="" class="footer-searchsec-left-form-col3-btn"/>
                        </div>
                        <div id="suggesstion-box2" class="suggesstion-box2" 
                             style="width: calc(100% - 48%);position: absolute;left: 0;z-index: 1;margin-top: 51px;padding-left: 23%;">
                        </div>
                    </div>
                </form>
                
                <script>
                $(document).on('keyup', "#search-box2", function() {
                    $.ajax({
                        type: "POST",
                        url: "read_business_services.php",
                        data: { searchkey: $(this).val() },
                        success: function(data) {
                            $(".suggesstion-box2").show().html(data);
                            $("#search-box2").css("background", "#FFF");
                        }
                    });
                });
                
                function selectService(id, val) {
                    $("#search-box2").val(val);
                    $("#buss_serv_cat").val(id);
                    $(".suggesstion-box2").hide();
                }
                </script>
                
                <div class="clear"></div>
            </div>
        </div>
        
        <div class="footer-searchsec-right">
            <a href="post-buy-req.php?select=bs" target="_blank" class="footer-searchsec-right-btn" title="Post Services Requests">
                أنشر طلبات شراء خدمات
            </a>
        </div>
        <div class="clear"></div>
    </div>
    <!-- footer-searchsec close// -->
    
    <div class="footer-intro">
        <!-- footer-intro start -->
        <div class="footer-intro-left">
            <div class="footer-intro-left-logo">
                <?php
                $footerlogo = GettingSite_Setting('unit-logo-footer');
                $footerlogo2show = !empty($footerlogo) ? "sitelogo/" . $footerlogo : "images/footerlogo4.png";
                ?>
                <a href="index.php" title="سوق مصر على الإنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع فى مصر والمنطقة العربية">
                    <img src="<?php echo htmlspecialchars($footerlogo2show); ?>" alt="" style="max-width:170px; max-height:108px;"/>
                </a>
            </div>
            
            <div class="footer-intro-left-text">
                <ul>
                    <li><a href="about_us.php" title="About Us">عن المنصة</a></li>
                    <li><a href="contact_us.php" title="Complaint">الشـكاوى</a></li>
                    <li><a href="contact_us.php" title="Feedback">رأيك فى المنصة</a></li>
                    <li><a href="contact_us.php" title="Contact Us">تواصل معنا</a></li>
                    <li><a href="help.php" title="How it works">طريفة عمل المنصة</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-intro-right">
            <!-- footer-intro-right start -->
            <div class="footer-intro-right-col" title="Buyers Tools">
                <h2>أدوات المشترى</h2>
                <ul>
                    <li><a href="post-buy-req.php" title="Request for Quotation">طلب تسعير أصناف شراء</a></li>
                    <li><a href="search_adv.php" title="Search Products / Services">إبحث وأكتشف ألاف الأعمال</a></li>
                    <li><a href="manage-selloffer-alert.php" title="Manage Sale offer Alerts">تسجيل إشعارات إستقبال عروض بيع</a></li>
                    <li><a href="myproduct-buy.php" title="List Regular Buy Requirements">تسجيل طلبات الشراء المعتادة</a></li>
                    <li><a href="post-tender.php" title="Post Tenders & Get Bidders">إنشر مناقصات وتلقى عطاءات</a></li>
                </ul>
            </div>
            
            <div class="footer-intro-right-col" title="Suppliers Tools">
                <h2>أدوات البائع</h2>
                <ul>
                    <li><a href="product-add.php" title="Display Your Business Categories">إعرض أعمالك التجارية</a></li>
                    <li><a href="membership_plans.php" title="Create Free Website">إنشىء موقع أونلاين لتجارتك</a></li>
                    <li><a href="manage-buylead-alert.php" title="Latest Buyleads Alerts">شاهد أحدث طلبات الشراء</a></li>
                    <li><a href="post-sell-offer.php" title="Post Business Ads FREE">سجل إعلانات بيع خاصة</a></li>
                    <li><a href="manage-selloffer-alert.php" title="Products Of Interest Alerts">إشعارات إستقبال طلبات شراء</a></li>
                    <li><a href="myproduct-sell.php" title="List Regular Company Products">تسجيل منتجات الشركة الدائمة</a></li>
                    <li><a href="post-auction.php" title="Post Auctions & Get Bidders">أنشر مزايدات وتلقى عطاءات</a></li>
                </ul>
            </div>
            
            <div class="footer-intro-right-col" title="EgyptMART Solutions">
                <h2>حلول التسويق الألكترونى</h2>
                <ul>
                    <li><a href="why_egyptmart.php" title="Companies Memberships">أسباب هامة للإشتراك بالمنصة</a></li>
                    <li><a href="membership_plans.php" title="Companies Memberships">إشتركات أعضاء المنصة</a></li>
                    <li><a href="manage-purchased-buyleads.php" title="Trade Leads For Me">طلبات الشراء الجاهزة</a></li>
                    <li><a href="my-enquiries.php" title="Business Inquiries">إستفسارات عن تجارتى فى بريدى</a></li>
                    <li><a href="advertise-with-us.php" title="Advertise With Us">إحجز مساحات إعلانية</a></li>
                </ul>
            </div>
            
            <div class="footer-intro-right-col" title="Leader Suppliers Plan">
                <h2>خطط أشتراك الشركات الرائدة</h2>
                <ul>
                    <li><a href="membership_plans.php" title="FREE PROMO PLAN">عضوية برومو مجانية</a></li>
                    <li><a href="advertise-with-us.php" title="FREE Banner Ads">إعلانات مجانية كشركة رائدة</a></li>
                    <li><a href="contact_us.php" title="FREE Bulk Newsletters">رسائل بريد الكتروني بالجملة</a></li>
                    <li><a href="contact_us.php" title="FREE Events News">تغطية مجانية لأحداثك التجارية</a></li>
                </ul>
            </div>
            
            <div class="clear"></div>
            
            <div style="clear: both;margin-top: 15px;">
                <?php
                $get_url = "SELECT * FROM connect_us WHERE id = 1 LIMIT 1";
                $stmt_url = mysqli_prepare($con, $get_url);
                $twitter_url = '';
                $google_url = '';
                $fb_url = '';
                
                if ($stmt_url) {
                    mysqli_stmt_execute($stmt_url);
                    $result_url = mysqli_stmt_get_result($stmt_url);
                    if (mysqli_num_rows($result_url) > 0) {
                        $urls = mysqli_fetch_assoc($result_url);
                        $twitter_url = $urls['twitter'] ?? '#';
                        $google_url = $urls['google'] ?? '#';
                        $fb_url = $urls['fb'] ?? '#';
                    }
                    mysqli_stmt_close($stmt_url);
                }
                ?>
                
                <div class="footer-intro-social">
                    <p>Connect with us:</p>
                    <ul>
                        <li><a href="<?php echo htmlspecialchars($twitter_url); ?>" target="_blank"><i class="fa fa-twitter-square"></i></a></li>
                        <li><a href="<?php echo htmlspecialchars($google_url); ?>" target="_blank"><i class="fa fa-linkedin-square"></i></a></li>
                        <li><a href="<?php echo htmlspecialchars($fb_url); ?>" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
                    </ul>
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
        <p>Copyright &copy; <?php echo date("Y"); ?> <?php echo htmlspecialchars(get_page_settings(4) ?? ''); ?>. All rights reserved</p>
    </div>
    <div class="copyright-row-col2">
        <p>
            <a href="terms.php">شروط الاستخدام</a> | 
            <a href="privacy.php">سياسة الخصوصية</a> | 
            <a href="contact_us.php">Link to Us</a>
        </p>
    </div>
    <div class="clear"></div>
</div>
<!-- copyright-row close // -->

<!-- scroll to top and feedback button -->
<div class="fixed-div">
    <a href="#top"><img src="images/up.png" width="50"/></a>
    <a href="contact_us.php"><img src="images/complaint.png" width="50"/></a>
</div>

<!-- ========== JavaScript Libraries (مرة واحدة فقط) ========== -->
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
<script src="js/cust.js?v=<?php echo date('ymdhis'); ?>"></script>
<script src="js/bgSlider-v1.js" type="text/javascript"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-modal-popover.js?v=<?php echo date('ymdhis'); ?>" type="text/javascript"></script>
<script src="js/script.js?t=<?php echo rand(); ?>" type="text/javascript"></script>

<!-- ========== Unified Scripts (بدون تكرار) ========== -->
<script type="text/javascript">
// Tawk.to Script
var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
(function() {
    var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
    s1.async = true;
    s1.src = 'https://embed.tawk.to/584a3ee48a20fc0cac4f7e93/default';
    s1.charset = 'UTF-8';
    s1.setAttribute('crossorigin', '*');
    s0.parentNode.insertBefore(s1, s0);
})();

// Popover
$(".poplink").popover({
    html: true,
    placement: "top",
    trigger: "hover",
    content: function() {
        return $(".ffffff").html();
    }
});

// Functions
function selectAlertPro(val) {
    window.open(val, 'newwindow', 'width=700,height=400,menubar=true');
    return false;
}

function selectAlertTender(val) {
    window.focus();
    window.open(val);
}

function zoom_image(ele) {
    var imagex = $(ele).data('img');
    $('#dyn_image').attr('src', imagex);
    $('#image_modal').modal('show');
}

function open_chat() {
    if (typeof Tawk_API !== 'undefined') {
        Tawk_API.toggle();
    }
}

// Document Ready - All initializations in one place
$(document).ready(function() {
    // إظهار القائمة الجانبية
    $('#left_ajax_geting').css('display', 'block');
    
    // تهيئة easyResponsiveTabs
    if ($.fn.easyResponsiveTabs) {
        $('#horizontalTab').easyResponsiveTabs({ type: 'default', width: 'auto', fit: true });
        $('#horizontalTab1').easyResponsiveTabs({ type: 'default', width: 'auto', fit: true });
    }
    
    // تهيئة Yahoo Slider (مرة واحدة فقط)
    if ($.fn.responsiveSlides && $("#newsslider").length) {
        $("#newsslider").responsiveSlides({
            auto: true,
            pager: false,
            nav: false,
            speed: 500,
            timeout: 4000
        });
        console.log("Yahoo Slider initialized");
    }
    
    // تهيئة Slick sliders
    if ($.fn.slick) {
        $('#business-services,#EgyptMART-leading,#sponsors,#temporary-slides,#products-suppliers').slick({
            centerMode: true,
            centerPadding: '30px',
            slidesToShow: 5,
            autoplay: true,
            autoplaySpeed: 3000,
            responsive: [
                { breakpoint: 1024, settings: { slidesToShow: 3 } },
                { breakpoint: 768, settings: { slidesToShow: 2 } },
                { breakpoint: 480, settings: { slidesToShow: 2 } }
            ]
        });
    }
    
   // Change header opacity
    setTimeout(function() {
        var myDiv = document.getElementById("myDiv");
        var r_loading = document.getElementById("r_loading");
        if (myDiv) myDiv.style.opacity = "1";
        if (r_loading) r_loading.style.opacity = "0";
    }, 1000);
});

// Stop redirect for mobile
$(document).on('click', '.stop_redirect', function(e) {
    e.preventDefault();
    var id = $(this).attr('data-id');
    $('.r_' + id).show();
});

// Test bus click
$(document).on('click', '.test_bus', function(event) {
    window.open($(this).data('href'), 'test');
});
</script>

<link href="css/type.css" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" href="css/cssmenu.css" type="text/css"/>

<!-- Image Modal -->
<div id="image_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content" style="margin-top: 10vh;">
            <img id="dyn_image" style="width: 100%;">
        </div>
    </div>
</div>

</body>
</html>