
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
<link href="css/style.css"  rel="stylesheet" type="text/css"/>
<link href="css/style123.css" type="text/css" rel="stylesheet" />
<link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
<link href="css/bootstrap.css" rel="stylesheet" type="text/css"/>
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css"/>

<link href="css/main-style.css?v4.30" rel="stylesheet" type="text/css"/>
 
 <!-- Start of wrapper -->

      <div class="wrapper">
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
                }
                else if (type == 'tender') {
                    $("#a1").html("Tender");
                }
                else if (type == 'auction') {
                    $("#a1").html("Auction");
                }
                else {
                    $("#a1").html(type);
                }

                $("#rctyp").val(type);
                $("#smnu").hide();
            }
         </script>
         <script>
            function validsearch() {
                var keywords = document.getElementById('keywords');
                  
                if (keywords.value == '' || keywords.value == null) {
                    alert("Please enter a valid text to search.");
                    return false;
                }
                $('#search-box11').val($.trim($('#search-box11').val()));alert($.trim($('#search-box11').val()));
            }

            function gotFocus() {
                var keywords = $("input#keywords").val();
                if (keywords == 'Enter product / service to search' || keywordstopsearch_bar == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search') {
                    $("input#keywords").val('')
                }
            }
            function lostFocus() {
                var type = $("#keyword_type").val();
                var keywords = $("input#keywords").val();
                if (type == 'Products' && (keywords == '' || keywords == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search')) {
                    $("input#keywords").val('Search Product');
                }
                else if (type == 'Buy Leads' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Supplier to search')) {
                    $("input#keywords").val('Enter Buy Lead to search');
                }
                else if (type == 'Suppliers' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search')) {
                    $("input#keywords").val('Enter Supplier to search');
                }
				else if (type == 'Tender' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Tender to search')) {

           $("input#keywords").val('Enter Tender to search');

       }
            }

            function setCountryLocation(id)
            {
                $.post("setCountryLocation.php", {loc_id: id}, function (data)
            {
                    if (data != 0) {
                        //	$("#cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
                        location.reload();
                    }
                });
            }
            function unsetCountryLocation() {
                $.post("unsetCountryLocation.php", function (data) {
                    //	$("#cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
                    location.reload();
                });
            }

         </script>
         <style type="text/css">
            .zoomin1 img { height: 78px; width: 219px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }
            .zoomin1 img:hover { width: 229px; height: 88px;  }
            .zoomin2 img { height: 66px; width: 200px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; margin: 15px 15px;
            }
            .zoomin2 img:hover { width: 210px; height:77px; }
            .zoomin3 img { height: 41px; width: 235px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }
            .zoomin3 img:hover { width: 245px; height:50px; }
         </style>
         <?php
            /**
             * Created by PhpStorm.
             * User: Long
             * Date: 12/18/2015
             * Time: 11:49 PM
             */
            ?>
         <!-- Top Blue Bar-->
<?php	include "includes/inner_top_bar.php";	?>
         <!-- End of topbar // -->
        <div class="maincontainertop"
            <!-- page-header start -->
            
  <header class="page-header">
               <div class="page-header-col11">
                  <!-- page-header-col1 start -->
                  <div class="col-md-3 col-sm-3 page2-header2-col1-row1">
                     <!-- col-md-9 start -->
                     <div class="page2-header2-col1-row1-col1">
                        <!-- page-header-col1-row1-col1 start -->
                        <div class="page2-header2-col1-row1-col1_row2">
                           <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
							   
                              <?php 
                                 if (isset($_COOKIE['loc_id'])) { ?>
                              <span style="weight:700px; color: darkcyan;"><?php echo get_country_name($_COOKIE['loc_id']); ?></span>&nbsp;
                              <img src="images/country_flag/<?php echo get_country_flag($_COOKIE['loc_id']); ?>"
                                 alt="<?php echo get_country_name($_COOKIE['loc_id']); ?>" class="w4" align="top" height="16"
                                 width="23" title="<?php echo get_country_name($_COOKIE['loc_id']); ?>"/>
                              <?php } else { ?>
                              <b>Global</b> &nbsp; <img src="images/country_flag/Global$download.png" alt="Global" class="w4" style="height:25px !important;width:25px!important;"
                                 align="top" height="25" width="25"/>
                              <?php } ?>
                           </div>
                           <div class="page-header-col1-row1-col1-row2-form">
                              <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()">
                                 <a class="un" style="border-left:none; font-size: 10px;">
                                    <span style="color: black">Change</span> Country
                                    <!--  <i class="fa fa-chevron-down"></i>-->
                                    &nbsp;<span class="arw"><b>&or;</b></span>
                                 </a>
                                 <div class="sub_menu" style="display:none;" id="changeLocation">
                                    <ul>
                                       <li>
                                          <?php
                                             $numCun = count(explode(",", getActiveCountryList()));
                                             $sql_cnLoc = "select * from country where cn_id in(" . getActiveCountryList() . ")";
                                             $res_cnLoc = mysqli_query($con, $sql_cnLoc);
                                             ?>
                                          <table style="width:100%;padding:1px;">
                                             <tr>
                                                <td align="center">
                                                   <a title="Global" style="cursor:pointer;" onclick="unsetCountryLocation();">
                                                   <img src="images/country_flag/Global$download.png" alt="Global" class="w4"
                                                      align="top" height="16" width="16" style="height:25px !important;width:25px!important;"/>
                                                   </a>
                                                </td>
                                                <?php
                                                   $cn = 1;
                                                   while ($row_cnLoc = mysqli_fetch_object($res_cnLoc)){
                                                   if ($cn % 4 == 0){
                                                   $cn = 0; ?>
                                             </tr>
                                             <tr>
                                                <?php }
                                                   ?>
                                                <td align="center">
                                                   <a title="<?php echo $row_cnLoc->cn_name; ?>" style="cursor:pointer;"
                                                      onclick="setCountryLocation(<?php echo $row_cnLoc->cn_id ?>);">
                                                   <img
                                                      src="images/country_flag/<?php echo get_country_flag($row_cnLoc->cn_id); ?>"
                                                      alt="<?php echo $row_cnLoc->cn_name; ?>" class="w4" align="top"
                                                      height="15" width="20"/>
                                                   </a>
                                                </td>
                                                <?php
                                                   $cn++;
                                                   } ?>
                                                <?php
                                                   while ($cn <= 3) { ?>
                                                <td>&nbsp;</td>
                                                <?php $cn++;
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
                     <div class="page2-header2-col1-row1-col2">
                        <?php
                           $toplogo=GettingSite_Setting('unit-logo');
                           if($toplogo!="")
                           {
                           	$toplogo2show = "sitelogo/".$toplogo;
                           }
                           else
                           {
                              $toplogo2show = "images/Mlogo.png";
                           }
                           ?>
                        <a href="index.php"><img src="images/Mlogo.png" alt="" style="max-width:190px; max-height:85px;"/></a>
                        <!--<p>Arabs Home &amp; Global Trade</p>-->
                     </div>
                     <!-- page-header-col1-row1-col2 close// -->
                     <!-- page-header-col1-row1-col3 close// -->
                  </div>
                  <!-- col-md-9 close// -->
                  <div class="page2-header2-col1-row2 col-md-6 col-sm-6">
                     <!-- page-header-col1-row2 start -->
                     <div class="page2-header2-col1-row2-col2">
                       <div class="toplinksbar">
                       <ul >
                           <li><a href="dir.php">Products & Services</a></li>
                           <li><a href="sale-offers.php">Sale Offers</a></li>
                           <li><a href="buyleads.php">Buy Requests</a></li>
                           <li><a href="tenders.php">Tenders</a></li>
                        </ul></div>
                        <!-- <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script> -->
                        <script>
                           $(document).ready(function(){
                               $("#search-box11").keyup(function(){
                                   $.ajax({
                                   type: "POST",
                                   url: "readproducts.php",
                                   data:'keyword='+$(this).val(),
                                   beforeSend: function(){
                                       $("#search-box11").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
                                   },
                                   success: function(data){
                                       $("#suggesstion-box").show();
                                       $("#suggesstion-box").html(data);
                                       $("#search-box11").css("background","#FFF");
                                   }
                                   });
                               });
                           });

                           function selectCountry(val) {
                           $("#search-box11").val(val);
                           $("#suggesstion-box").hide();
                           }
                        </script>
                        <div class="top_search">
                              <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                              <div class="topsearch_bar">
                                  
                    <?php 
                        if($_GET["rctyp"] != "tender"){
                    ?>
                            <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select1">
                                <option value="Suppliers">Suppliers</option>
                                <option  value="Products" selected>Products</option>
                                <option value="buy_lead">Buy Leads</option>
                                <option value="tender">Tender</option>
                                <!--<option value="auction">Auction</option>-->
                             </select>
                    <?php
                        }else{
                    ?>
                            <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select1">
                                <option value="Suppliers">Suppliers</option>
                                <option  value="Products">Products</option>
                                <option value="buy_lead">Buy Leads</option>
                                <option value="tender" selected>Tender</option>
                                <!--<option value="auction">Auction</option>-->
                             </select>
                    <?php
                        }
                    ?>
                              </div>
                              <div class="topsearch_placeholder">
                                 <input type="text" id="search-box11" name="keywords" placeholder="Search for any Business  Services" onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo $_GET['keywords']; ?>"   class="topsearch_placeholder_cont "/>
                              </div>
                               <div id="suggesstion-box"></div>
                              <div class="topsearch_searchbtn">
                                  <input type="submit" id="btnSearch" value="" class="topsearch-searchbtn"/>
                              </div>
                           </form>
                           <div class="clear"></div>
                        </div>
                        <div class="page-header-col1-row2-col2-links">
                           <p><span><a href="search_adv.php">Advanced Search</a></span></p>
                        </div>
                     </div>
                     <!-- page-header-col1-row2-col2 close// -->
                     <div class="page2-header2-col1-row2-col4 ">
                        <!-- page-header-col1-row2-col4 start -->
                        <div class="zoomin2">
                           <a href="post-buy-req.php">
                              <img src="images/Unreq.png" />
                              <!--
                                 <a href="post-buy-req.php" class="page-header-col1-row2-col4-btn">
                                 <p>Post Buy Requests</p>
                                 <span>Get <ins>Quotes </ins> from <ins>Verified Suppliers</ins></span> </a>-->
                           </a>
                        </div>
                     </div>
                     <!-- page-header-col1-row2-col4 close// -->
                     <div class="clear"></div>
                  </div>
                  <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css"/>
                  <script type="text/javascript" src="js/jquery.autocomplete2.js"></script>
                  <script type="text/javascript">
                     $(document).ready(function () {
                         lostFocus();
                         $('#keywords').keydown(function () {
                             var type = $("#keyword_type").val();
                             $("#keywords").autocomplete("autocomplete.php", {
                                     selectFirst: true,
                                     extraParams: {type: type},
                                     width: 407
                                 })
                                 .result(function (event, data, formatted) {
                                     $("input#keywords").val(data);
                                 });
                         });
                     });
                  </script>
                  <!-- page-header-col1-row2 close// -->
               </div>
               <!-- page-header-col1 close// -->
               <div class="page2-header2-col2">
                  <div class="page2-header2-col1-row1-col3">
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
                  <div class="page-header-col2-intro">
                     <div class="page-header-col2-intro-texts">
                        <a href="product-sel-cat.php" class="zoomin3">
                        <img src="images/PostServise.jpg " 	/>
                        </a>
                        <!--<h2><a href="product-sel-cat.php">Post Your Services</a></h2>
                           <p>Get <span>Domestic</span> or <span>Global</span> Enquiries</p> -->
                     </div>
                  </div>
                  <div class="clear"></div>
               </div>
               <div class="clear"></div>
            </header>
            <!-- page-header close // -->
         </div>
<script>
$(document).ready(function() {
setTimeout(function(){
     var lang = $(".goog-te-menu-value span:first").text();
     if(lang == 'Arabic') {
$('input').css('direction', 'rtl');
$('textarea').css('direction', 'rtl');
$('.page-header input').css('direction', 'ltr');
$('.page-header textarea').css('direction', 'ltr');

}
},5000);
});
</script>
