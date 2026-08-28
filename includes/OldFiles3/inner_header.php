<header class="page-header">
               <div class="page-header-col11">
                  <!-- page-header-col1 start -->
                  <div class="col-md-9 page2-header2-col1-row1">
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
                              <b>Global</b> &nbsp; <img src="images/country_flag/Global$download.png" alt="Global" class="w4"
                                 align="top" height="25" width="25"/>
                              <?php } ?>
                           </div>
                           <div class="page-header-col1-row1-col1-row2-form">
                              <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()">
                                 <a class="un" style="border-left:none; font-size: 10px;">
                                    <span style="color: black"title="إختار بلد الصناعة">Change</span> Country
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
                                                      align="top" height="16" width="16"/>
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
                        <a href="index.php"title="سوق مصر على الإنترنت - أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع فى مصر والمنطقة العربية"><img src="images/Mlogo.png" alt="" style="max-width:190px; max-height:85px;"/></a>
                        <!--<p>Arabs Home &amp; Global Trade</p>-->
                     </div>
                     <!-- page-header-col1-row1-col2 close// -->
                     <!-- page-header-col1-row1-col3 close// -->
                  </div>
                  <!-- col-md-9 close// -->
                  <div class="page2-header2-col1-row2">
                     <!-- page-header-col1-row2 start -->
                     <div class="page2-header2-col1-row2-col2">
                       <div class="toplinksbar">
                       <ul >
                           <li><a href="dir.php"title="بوب أب">Products & Services</a></li>
                           <li><a href="sale-offers.php"title="بوب أب">Sale Offers</a></li>
                           
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
         				  <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select1">
                        <option value="Suppliers">Suppliers</option>
                        <option  value="Products" selected>Products</option>
                        
                        <!--<option value="auction">Auction</option>-->
                     </select>
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
                       <a href="post-buy-req.php" target="_blank" class="footer-searchsec-right-btn head-post-buy-req-btn">Post Buy Requirements</a><!--<div class="zoomin2">
                           <a href="post-buy-req.php">
                              <img src="images/Unreq.png" />
                           
                                 <a href="post-buy-req.php" class="page-header-col1-row2-col4-btn">
                                 <p>Post Buy Requests</p>
                                 <span>Get <ins>Quotes </ins> from <ins>Verified Suppliers</ins></span> </a>
                           </a>
                        </div>-->
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
                       <a href="product-sel-cat.php?select=bs" class="post-product-btn post-product-btn-inner">
         Post Business Services<small>Get <strong>Domestic</strong> or <strong>Global</strong> Enquiries</small>          
         </a>
         <!-- <a href="product-sel-cat.php" class="zoomin3">
                        <img src="images/PostServise.jpg " 	/>
                        </a>-->
                        <!--<h2><a href="product-sel-cat.php">Post Your Services</a></h2>
                           <p>Get <span>Domestic</span> or <span>Global</span> Enquiries</p> -->
                     </div>
                  </div>
                  <div class="clear"></div>
               </div>
               <div class="clear"></div>
            </header>