    <?php

    //Google Adsense
    $sql_ga="select * from google_adsense where ga_status='1' order by rand() limit 1";
    $res_ga=mysqli_query($con, $sql_ga);
    if(mysqli_num_rows($res_ga)>0)
    {
        $row_ga=mysqli_fetch_object($res_ga);
        ?>
        <div style="text-align:center;padding-top:5px;padding-bottom:5px;"><?php echo $row_ga->ga_content;  ?></div>
        <?php
    }
    ?>
<!-- footer start -->
<footer class="footer">
    <!-- footer-searchsec start -->
    <div class="footer-searchsec">
        <div class="footer-searchsec-left">
            <div class="footer-searchsec-left-head">
            <div class="srchBx">
                  <h1 class="cd-headline clip is-full-width">
                   <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper" >
                  <b class="is-hidden">Find Service Providers of assessed suppliers<span class="blinking-cursor" style="color: red">!</span></b>
                  <b class="is-visible">Find Service Providers of assessed suppliers<span class="blinking-cursor" style="color: red">!</span></b>
                      </span>
                     </h1>
               </div>
                <!--<h1>Find Service Providers of an Assessed Suppliers</h1>-->
            </div>
            <div class="footer-searchsec-left-form">
                <!--<form action="search.php">-->
           <!--      <script>
                  $(document).ready(function(){
                      $("#search-box1").keyup(function(){
                          $.ajax({
                          type: "POST",
                          url: "readproducts.php",
                          data:'keyword='+$(this).val(),
                          beforeSend: function(){
                              $("#search-box1").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
                          },
                          success: function(data){
                              $("#suggesstion-box1").show();
                              $("#suggesstion-box1").html(data);
                              $("#search-box1").css("background","#FFF");
                          }
                          });
                      });
                  });

                  function selectCountry(val) {
                  $("#search-box1").val(val);
                  $("#suggesstion-box1").hide();
                  }
               </script>-->
                <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                <input type="hidden" id="rctyp" name="rctyp" value="Products"/>
                    <div class="footer-searchsec-left-form-col1">

                       <!--<select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                        <option value="Suppliers">Suppliers</option>
                        <option  value="Products" selected>Servic</option>
                        <option value="buy_lead">Buy Leads</option>
                        <option value="tender">Tender</option>
                        <!--<option value="auction">Auction</option>
                     </select>
                -->
                           <p>Services</p>

                    </div>
                    <div class="footer-searchsec-left-form-col2">
                        <input type="text" id="search-box1" name="keywords" placeholder="Search for any Business Services"
                               class="footer-searchsec-left-form-col2-input"/>
                               <div id="suggesstion-box1"></div>
                    </div>
                    <div class="footer-searchsec-left-form-col3">
                        <input type="submit" value="" class="footer-searchsec-left-form-col3-btn"/>
                    </div>
                </form>

                <div class="clear"></div>
            </div>
        </div>
        <div class="footer-searchsec-right">
            <a href="post-buy-req.php?select=bs"  target="_blank" class="footer-searchsec-right-btn">Post Services Requests</a>
        </div>
        <div class="clear"></div>
    </div><!-- footer-searchsec close// -->
    <div class="footer-intro"><!-- footer-intro start -->
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
             $footerlogo2show = "images/footer-intro-left-logo02.png";
          }
          ?>
       <a href="#"><img src="<?php echo $footerlogo2show;?>" alt="" style="max-width:170px; max-height:108px;"/></a>
            </div>
            <div class="footer-intro-left-text">
                <ul>
                    <li><a href="about_us.php">About Us</a></li>
                    <li><a href="contact_us.php">Complaints</a></li>
                    <li><a href="contact_us.php">Feedback</a></li>
                    <!--<li><a href="privacy.php">Privacy & Policy</a></li>
                    <li><a href="terms.php">Tems & Conditions</a></li>-->
                     <li><a href="contact_us.php">Contact Us</a></li>
                    <li><a href="help.php">Help</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-intro-right"><!-- footer-intro-right start -->
            <div class="footer-intro-right-col">
                <h2>Buyers Tools</h2>
                <ul>
                    <li><a href="post-buy-req.php">Post Buy Requirements</a></li>
                    <li><a href="manage-selloffer-alert.php">Manage Sale offer Alerts</a></li>
                    <li><a href="search_adv.php">Search Products / Services</a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>Suppliers Tools</h2>
                <ul>
                    <li><a href="product-add.php">Post Products - FREE</a></li>
                    <li><a href="create-free-website.php">Create Website on EgyptMART</a></li>
                    <li><a href="buyleads.php">Latest Buy Leads </a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>EgyptMART Soluations</h2>
                <ul>
                    <li><a href="membership_plans.php">Premium Membership</a></li>
                    <li><a href="manage-purchased-buyleads.php">Trade Leads For Me</a></li>
                 <!--   <li><a href="manage-auction-alert.php">Advertise with us </a></li>-->
                       <li><a href="advertise-with-us.php">Advertise with us </a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>Tenders / Auctions</h2>
                <ul>
                    <li><a href="tenders.php">Latest Tenders</a></li>
                    <li><a href="manage-tender-alert.php">Mange Tenders Alerts</a></li>
                    <li><a href="auctions.php">Latest Auctions</a></li>
                    <li><a href="manage-auction-alert.php">Mange Auctions Alerts</a></li>
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
                        <li><a href="<?=$urls['google'];?>" target="_blnak"><i class="fa fa-google-plus-square"></i></a></li>
                        <li><a href="<?=$urls['fb'];?>" target="_blnak"><i class="fa fa-facebook-square"></i></a></li>
                    </ul>
                </div>
            </div>

        </div><!-- footer-intro-right close// -->
        <div class="clear"></div>


    </div><!-- footer-intro close// -->
</footer><!-- footer close// -->
<div class="copyright-row"><!-- copyright-row start -->
    <div class="copyright-row-col1">
        <p>Copyright &copy; <?php echo date("Y"); ?> <?php echo get_page_settings(4);?>. All rights reserved</p>
    </div>
    <div class="copyright-row-col2">
        <p><a href="terms.php">Terms of Use</a> | <a href="privacy.php">Privacy Policy</a> | <a href="contact_us.php">Link to Us</a></p>
    </div>
    <div class="clear"></div>
</div>
<!-- copyright-row close // -->

<!-- scroll to top and feedback button -->
<div class="fixed-div"> <a href="#top"><img src="images/up.png" width="50"/></a> <a href="#"><img src="images/complaint.png" width="50"/></a> </div>

<!--</div>-->
<!-- start of right Tabs -->
<script src="js/new_js/easyResponsiveTabs.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#horizontalTab').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true   // 100% fit in a container
        });
    });
</script>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#horizontalTab1').easyResponsiveTabs({
            type: 'default', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true   // 100% fit in a container
        });
    });
</script>
<!-- End of right Tabs // -->

<!-- start of verticle menu -->
<script src="js/new_js/cust.js"></script>
<!-- End of verticle menu // -->

<!-- Animation text slider
<link rel="stylesheet" href="css/new_css/imNew-v6.css" type="text/css"/>-->
<!--<script src="js/im-style-vn6.3.js" type="text/javascript"></script>-->
<script src="js/new_js/bgSlider-v1.js" type="text/javascript"></script>
<!-- Animation text slider // -->

<script src="js/new_js/bootstrap.min.js"></script>

<!-- navigation  -->
<link rel="stylesheet" href="css/new_css/cssmenu.css" type="text/css"/>
<!--<link href="css/responsive1.css" rel="stylesheet" type="text/css"/>-->
<script src="js/new_js/script.js" type="text/javascript"></script>
<!-- navigation // -->

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
</script>

<!--End of Tawk.to Script-->

<link href="css/new_css/type.css" rel="stylesheet" type="text/css"/>

<!--<script type="text/javascript" src="http://workfromhomecompanies.net/its/ehabfa/livechat/php/app.php?widget-init.js"></script>-->
</body>
</html>