<footer class="footer">
    <!-- footer-searchsec start -->
    <div class="footer-searchsec">
        <div class="footer-searchsec-left">
            <div class="footer-searchsec-left-head">
            <div class="srchBx">
                  <h1 class="cd-headline clip is-full-width">
                   <span style="width: 548.16px; overflow: hidden; color:#165edb; font-family: Arial narrow;" class="cd-words-wrapper" >
                   <b class="is-hidden">Find Service Providers of an Assessed Suppliers <span class="blinking-cursor" style="color: gray">!</span></b>
                     <b class="is-visible">Find Service Providers of an Assessed Suppliers<span class="blinking-cursor" style="color: gray">!</span></b>
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
                        <option  value="Products" selected>Service</option>
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
						<div id="msg" style="width: 90%;font-size:16px;text-align: center;"></div>
						<br>
                    </div>
                </form>

                <div class="clear"></div>
            </div>
        </div>
        <div class="footer-searchsec-right">
            <a href="post-buy-req.php"  target="_blank" class="footer-searchsec-right-btn">Post Services Requests</a>
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
					   $footerlogo2show = "images/footer-intro-left-logo4.png";
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
                    <li><a href="create-free-website.php">Create Website on ARABYOS</a></li>
                    <li><a href="buyleads.php">Latest Buy Leads </a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>ARABYOS Soluations</h2>
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
                $get_url = "SELECT * FROM `connect_us` WHERE id =1";
                $ures = $con->query($get_url);
                $urls = $ures->fetch_assoc();
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

<!-- scroll to top and feedback button -->
<div class="fixed-div"> <a href="#top"><img src="images/up.png" width="50"/></a> <a href="#"><img src="images/complaint.png" width="50"/></a> </div>
