<?php
/**
 * File: footer.php
 * Version: PHP 8.3
 * Description: تذييل الصفحة الرئيسي - يحتوي على روابط سريعة، حقوق النشر، ونماذج البحث
 */

// بدء المخزن المؤقت
ob_start();

// تضمين الملفات الأساسية
include "common.php";

// Google Adsense - عرض إعلانات جوجل العشوائية
$sql_ga = "SELECT * FROM google_adsense WHERE ga_status = '1' ORDER BY RAND() LIMIT 1";
$res_ga = mysqli_query($con, $sql_ga);

if (mysqli_num_rows($res_ga) > 0) {
    $row_ga = mysqli_fetch_object($res_ga);
    ?>
    <div style="text-align:center;padding-top:5px;padding-bottom:5px;">
        <?php echo $row_ga->ga_content; ?>
    </div>
    <?php
}
?>
<!-- footer start -->

        </div>
    </div>
</div>
<footer class="footer">
    <!-- footer-searchsec start -->
    <div class="footer-searchsec">
        <div class="footer-searchsec-left">
            <div class="footer-searchsec-left-head">
                <div class="srchBx">
                    <h1 class="cd-headline clip is-full-width">
                        <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper">
                            <b class="is-visible">Find SERVICE providers of assessed suppliers<span class="blinking-cursor" style="color: red">!</span></b>
                            <b class="is-hidden">Post Business SERVICES Requests, Get Offers <span class="blinking-cursor" style="color: red">!</span></b>
                        </span>
                    </h1>
                </div>
            </div>
            <div class="footer-searchsec-left-form">
                <form autocomplete="off" name="searchForm" action="search.php" onsubmit="return validsearch()" method="GET" id="hdr_frm">
                    <input type="hidden" id="rctyp" name="rctyp" value="Products"/>
                    <div>
                        <div class="footer-searchsec-left-form-col1">
                            <p style="font size: 10px; weight:bold;" title=" - إبحث عن الاف من - الخدمات التجارية لتجارتك ">Services</p>
                        </div>
                        <div class="footer-searchsec-left-form-col2" title=" - إبحث عن الاف من - الخدمات التجارية للتجارة ">
                            <input type="text" id="search-box2" name="keywords" 
                                   placeholder="Search for any Business Services"
                                   class="footer-searchsec-left-form-col2-input"
                                   value="<?php echo isset($_GET['keywords']) ? htmlspecialchars($_GET['keywords']) : ''; ?>"/>
                        </div>
                        <div class="footer-searchsec-left-form-col3">
                            <input type="submit" value="" class="footer-searchsec-left-form-col3-btn"/>
                        </div>
                        <div id="suggesstion-box2" class="suggesstion-box2" 
                             style="width: calc(100% - 48%); position: absolute; left: 0; z-index: 1; margin-top: 51px; padding-left: 23%;"></div>
                    </div>
                </form>
                
                <script>
                    $(document).ready(function() {
                        // يمكن إضافة كود إضافي هنا
                    });
                    
                    $(document).on('keyup', "#search-box2", function() {
                        $.ajax({
                            type: "POST",
                            url: "read_business_services.php",
                            data: 'searchkey=' + $(this).val(),
                            beforeSend: function() {
                                // يمكن إضافة مؤشر تحميل هنا
                            },
                            success: function(data) {
                                $(".suggesstion-box2").show();
                                $(".suggesstion-box2").html(data);
                                $("#search-box2").css("background", "#FFF");
                            },
                            error: function() {
                                console.log("خطأ في الاتصال بالخادم");
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
            <a href="post-buy-req.php?select=bs" target="_blank" class="footer-searchsec-right-btn" 
               title="أنشر طلبات شراء - للخدمات التجارية - وتلقى عروض بيع لها">
                Post Services Requests
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
                if (!empty($footerlogo)) {
                    $footerlogo2show = "sitelogo/" . $footerlogo;
                } else {
                    $footerlogo2show = "images/footer-intro-left-logo4.png";
                }
                ?>
                <a href="#" title="سوق مصر على الإنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع فى مصر والمنطقة العربية">
                    <img src="<?php echo htmlspecialchars($footerlogo2show); ?>" alt="" style="max-width:170px; max-height:108px;"/>
                </a>
            </div>
            <div class="footer-intro-left-text">
                <ul>
                    <li><a href="about_us.php" title="ماتريد معرفته عن منصة سوق مصر على الانترنت">About Us</a></li>
                    <li><a href="contact_us.php" title="أكتب لنا أى شكاوى أو إقتراحات وسوف نرد عليك">Complaint</a></li>
                    <li><a href="contact_us.php" title="أكتب لنا ماتريد وسوف نرد عليك">Feedback</a></li>
                    <li><a href="contact_us.php" title="أكتب لنا ماتريد وسوف نرد عليك">Contact Us</a></li>
                    <li><a href="help.php" title="طريقة عمل منصة سوق مصر على الإنترنت">How it works</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-intro-right">
            <!-- footer-intro-right start -->
            <div class="footer-intro-right-col" title="أدوات المشترى - شركات الشراء">
                <h2>Buyers Tools</h2>
                <ul>
                    <li><a href="post-buy-req.php" title="إطلب سعر أصناف شراء">Request for Quotation</a></li>
                    <li><a href="manage-selloffer-alert.php" title="سجل أصناف - عروض بيع - مرغوبة الشراء لأعمالك وتلقى إشعاراتها فى بريدك">Manage Sale offer Alerts</a></li>
                    <li><a href="search_adv.php" title="إبحث وأكتشف ألاف من الأعمال التجارية والمنتجات والخدمات التجارية">Search Products / Services</a></li>
                    <li><a href="post-tender.php" title="إنشر - مناقصات - وتلقى عطاءات من مناقصين متحرى عنهم">Post Tenders & Get Bidders</a></li>
                </ul>
            </div>
            
            <div class="footer-intro-right-col" title="أدوات البائع - الشركات الموردة">
                <h2>Suppliers Tools</h2>
                <ul>
                    <li><a href="product-add.php" title="إعرض خدماتك التجارية / وتلقى إستفسارات شراء من داخل وخارج مصر">Display Your Business Categories</a></li>
                    <li><a href="create-free-website.php" title="إنشىء - موقع مصغر - لأعمالك التجارية وسجل منتجات وبروفايل الشركة">Create Website on EgyptMART</a></li>
                    <li><a href="manage-buylead-alert.php" title="أحدث طلبات الشراء">Latest Buyleads Alerts</a></li>
                    <li><a href="post-sell-offer.php" title="سجل عروض بيع خاصة - وتلقى إستفسارات شراء">Post Business Ads FREE</a></li>
                    <li><a href="post-auction.php" title="أنشر - مزايدات - وتلقى عطاءات">Post Auctions & Get Bidders</a></li>
                </ul>
            </div>
            
            <div class="footer-intro-right-col" title="حلول التسويق الألكترونى التى تقدمها المنصة لأعضائها">
                <h2>EgyptMART Solutions</h2>
                <ul>
                    <li><a href="membership_plans.php" title="إشتركات وخطط إستفادة أعضاء المنصة">Companies Memberships</a></li>
                    <li><a href="my-enquiries.php" title="صندوق بريد إستفسارات المتعاملين مع تجارتى">Business Inquiries</a></li>
                    <li><a href="manage-purchased-buyleads.php" title="بيانات طلبات الشراء الجاهزة التى أقوم بشرائها">Trade Leads For Me</a></li>
                    <li><a href="favorite.php" title="سجل أصناف منتجات بيع - شراء - وتلقى فى بريدك أحدث الاشعارات بخصوصها">Products Of Interest Alerts</a></li>
                    <li><a href="advertise-with-us.php" title="إحجز معنا مساحات إعلانية">Advertise With Us</a></li>
                </ul>
            </div>
            
            <div class="footer-intro-right-col" title="الخطط والخدمات المجانية التى تقدمها المنصة لأعضائها">
                <h2>Leader Suppliers Plan</h2>
                <ul>
                    <li><a href="membership_plans.php" title="أحصل على عضوية - برومو - مجانية - كشركة رائدة">FREE PROMO PLAN</a></li>
                    <li><a href="advertise-with-us.php" title="أحصل على إعلانات مجانية - كشركة رائدة">FREE Banner Ads</a></li>
                    <li><a href="contact_us.php" title="أحصل على رسائل تجارية مجانية لكل أعضاء المنصة - كشركة رائدة">FREE Bulk Newsletters</a></li>
                    <li><a href="contact_us.php" title="أحصل على تغطية مجانية لأحداثك التجارية - كشركة رائدة">FREE Events News</a></li>
                </ul>
            </div>
            <div class="clear"></div>
            
            <div style="clear: both; margin-top: 15px;">
                <?php
                $get_url = "SELECT * FROM connect_us WHERE id = 1 LIMIT 1";
                $ures = mysqli_query($con, $get_url);
                $urls = $ures ? mysqli_fetch_array($ures) : [];
                ?>
                <div class="footer-intro-social">
                    <p>Connect with us:</p>
                    <ul>
                        <li><a href="<?php echo isset($urls['twitter']) ? htmlspecialchars($urls['twitter']) : '#'; ?>" target="_blank"><i class="fa fa-twitter-square"></i></a></li>
                        <li><a href="<?php echo isset($urls['google']) ? htmlspecialchars($urls['google']) : '#'; ?>" target="_blank"><i class="fa fa-linkedin-square"></i></a></li>
                        <li><a href="<?php echo isset($urls['fb']) ? htmlspecialchars($urls['fb']) : '#'; ?>" target="_blank"><i class="fa fa-facebook-square"></i></a></li>
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
            <a href="terms.php">Terms of Use</a> | 
            <a href="privacy.php">Privacy Policy</a> | 
            <a href="contact_us.php">Link to Us</a>
        </p>
    </div>
    <div class="clear"></div>
</div>
<!-- copyright-row close // -->

<!-- scroll to top and feedback button -->
<div class="fixed-div"> 
    <a href="#top"><img src="images/up.png" width="50" alt="العودة للأعلى"/></a> 
    <a href="contact_us.php"><img src="images/complaint.png" width="50" alt="تواصل معنا"/></a> 
</div>

<!-- start of right Tabs -->
<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#horizontalTab').easyResponsiveTabs({
            type: 'default',
            width: 'auto',
            fit: true
        });
        
        $('#horizontalTab1').easyResponsiveTabs({
            type: 'default',
            width: 'auto',
            fit: true
        });
    });
    
    $(document).on('click', '.stop_redirect', function(e) {
        e.preventDefault();
        var id = $(this).attr('data-id');
        $('.r_' + id).show();
    });
</script>

<!-- start of verticle menu -->
<script src="js/cust.js?v=<?php echo date('ymdhis'); ?>"></script>
<!-- End of verticle menu // -->

<!-- Animation text slider -->
<script src="js/bgSlider-v1.js" type="text/javascript"></script>
<!-- Animation text slider // -->

<script src="js/bootstrap.min.js"></script>
<script src="js/bootstrap-modal-popover.js?v=<?php echo date('ymdhis'); ?>" type="text/javascript"></script>

<!-- navigation -->
<link rel="stylesheet" href="css/cssmenu.css" type="text/css"/>
<script src="js/script.js?t=<?php echo rand(); ?>" type="text/javascript"></script>
<!-- navigation // -->

<!--Start of Tawk.to Script-->
<script type="text/javascript">
    var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
    (function() {
        var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];
        s1.async = true;
        s1.src = 'https://embed.tawk.to/584a3ee48a20fc0cac4f7e93/default';
        s1.charset = 'UTF-8';
        s1.setAttribute('crossorigin', '*');
        s0.parentNode.insertBefore(s1, s0);
    })();
    
    (function($) {
        setTimeout(function() { 
            $('#myDiv').css('opacity', '1');
            $('#r_loading').css('opacity', '0'); 
        }, 3000);
        
        $(document).on('click', '.home-header .toplinksbar ul li span', function() {
            $('.home-header .toplinksbar ul li span').removeClass('active');
            $(this).addClass('active');
        });
    }(jQuery));
</script>

<script>
    $(".poplink").popover({
        html: true,
        placement: "top",
        trigger: "hover",
        content: function() {
            return $(".ffffff").html();
        }
    });
    
    function selectAlertPro(val) {
        window.open(val, 'newwindow', 'width=700,height=400,menubar=true');
        return false;
    }
    
    function selectAlertTender(val) {
        window.focus();
        window.open(val);
    }
    
    $(document).on('click', '.test_bus', function(event) {
        window.open($(this).data('href'), 'test');
    });
</script>
<!--End of Tawk.to Script-->

<link href="css/type.css" rel="stylesheet" type="text/css"/>

<!-- Modal for image zoom -->
<div id="image_modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content" style="margin-top: 10vh;">
            <img id="dyn_image" style="width: 100%;" alt="تكبير الصورة">
        </div>
    </div>
</div>

<script type="text/javascript">
    function zoom_image(ele) {
        var imagex = $(ele).data('img');
        console.log(imagex);
        $('#dyn_image').attr('src', imagex);
        $('#image_modal').modal('show');
    }
    
    function open_chat() {
        Tawk_API.toggle();
    }
</script>

<!-- جميع أنماط CSS الطويلة موجودة في ملفات منفصلة - تم نقلها -->
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>
</body>
</html>