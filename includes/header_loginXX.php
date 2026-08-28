<?php
//ini_set('MAX_EXECUTION_TIME', -1);
?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
<link href="css/style.css?t=<?php echo rand(); ?>"  rel="stylesheet" type="text/css"/>
<link href="css/style123.css?t=<?php echo rand(); ?>klmn" type="text/css" rel="stylesheet" />
<link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css"/>
<link href="css/bootstrap.buyleads-new.min.css" rel="stylesheet" type="text/css"/>
<link href="css/main-style.css?r=<?php echo time(); ?>" rel="stylesheet" type="text/css"/><!--?v3.17 removed by webxtor on 28 June 2018 to avoid cache-->
<link href="css/im-style-v1.css" rel="stylesheet" type="text/css"/>
<link href="css/new_responsive.css" rel="stylesheet" type="text/css"/>
<link href="../css/main.css" rel="stylesheet" type="text/css"/>
<!-- Start of wrapper -->
<script src="js/slick.js" type="text/javascript" charset="utf-8"></script>
<link rel="stylesheet" type="text/css" href="css/slick.css">
<link rel="stylesheet" type="text/css" href="css/slick-theme.css">
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
    $(".center").slick({ dots: true,
    infinite: true,
    centerMode: true,
    slidesToShow: 5,
    slidesToScroll: 3
    });
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
    
    </style>
    <?php
    /**
    * Created by PhpStorm.
    * User: Long
    * Date: 12/18/2015
    * Time: 11:49 PM
    */
    ?>
    <div class="main-warpp">
        <!-- Top Blue Bar-->
        <?php include "includes/inner_top_bar.php"; ?>
        <!-- End of topbar // -->
        <div class="maincontainertop">
            <!-- page-header start -->
            <header class="page-header site-main-header ">
                <!-- page-header-col1 close// -->
                <div class="page2-header2-col2">
                    <div class="page2-header2-col1-row1-col3">
                        <!-- page-header-col1-row1-col3 start -->
                        <div id="google_translate_element" style="margin: 0px auto 10px;"></div>
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
                        <!-- <p class="cb"></p>-->
                    </div>
                    
                    <div class="page-header-col2-intro">
                        <div class="page-header-col2-intro-texts">
                            
                            
                            
                            
                            <!-- <a href="product-sel-cat.php" class="zoomin3">
                                    <img src="images/PostServise.jpg "  />
                            </a>-->
                            <!--<h2><a href="product-sel-cat.php">Post Your Services</a></h2>
                            <p>Get <span>Domestic</span> or <span>Global</span> Enquiries</p> -->
                  
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="page-header-col11">
                    <!-- page-header-col1 start -->
                    <div class="col-md-9 page2-header2-col1-row1">
                        <!-- col-md-9 start -->
                        <div class="page2-header2-col1-row1-col1">
                            <!-- page-header-col1-row1-col1 start -->
                               <div class="page2-header2-col1-row1-col1_row2">

                                <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
                                    
                                 <?php if (isset($_COOKIE['loc_id'])) { ?>
                                    <span style="weight:700px; color: darkcyan;">      </span>
                                    <?php } ?>
                                </div>
                                <div class="page-header-col1-row1-col1-row2-form">
                                    
                          <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()">
                                      </a> 
                                           
                                        <div class="sub_menu" style="display:none;width: 170px !important;left: -15px !important;top: 50px !important;" id="changeLocation" >
                                            <ul>
                                            <li style="width:100%;">
                                                   
                                                   <?php
                                                    $numCun = count(explode(",", getActiveCountryList())); 
                                                   
                                                    $sql_cnLoc = "select * from country where cn_id in(" . getActiveCountryList() . ")";
                                                    $res_cnLoc = mysqli_query($con, $sql_cnLoc);
                                                    ?>
                                                    <table style="width:100%;padding:1px;">
                                                        <tr>
                                                            <td align="center">
                                                                
                                                                   
                                                            <a title="Global" style="cursor:pointer;" onclick="unsetCountryLocation();">
                                                            <?php
                                                            $cn = 1;
                                                            while ($row_cnLoc = mysqli_fetch_object($res_cnLoc)) {
                                                            if ($cn % 3 == 0) {
                                                            $cn = 0;
                                                            ?>
                                                        </tr>
                                                        <tr>
                                                            <?php }
                                                            ?>
                                                            <td align="center">
                                                                
                                                              </td>
                                                            <?php
                                                            $cn++;
                                                            }
                                                            ?>  
                                                                
                                                            
                                                            <?php while ($cn <= 3) { ?>
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
                       
                       
                       <div class="page2-header2-col1-row1-col2">
                            <?php
                            $toplogo = GettingSite_Setting('unit-logo');
                            if ($toplogo != "") {
                            $toplogo2show = "sitelogo/" . $toplogo;
                            } else {
                            $toplogo2show = "images/Mlogo.png";
                            }
                            ?>
                            <a href="/" title="أول منصة الكترونية لمبيعات الجملة / التصدير / الخدمات التجارية .. لأهم 10,000 شركة ومصنع فى مصر والمنطقة العربية "><img src="/images/Mlogo.png
" alt="" style="max-width:190px; max-height:85px;"/></a>
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
                                    
                                    
                                    
                                    
                                    
                                </ul></div>
                                <!-- <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script> -->
                                <script>
                                <!-- Gajendra Code End -->
                                
                                </script>
                                
                                </div>
                            
                            
                            
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
                            $("                                input#keywords").val(data);
                            })                                ;
                            });
                            });
                            </script>
                            <!-- page-header-col1-row2 close// -->
                        </div>
                        <div class="clear"></div>
                    </header>
                    <!-- page-header                                          close // -->
                </div>
            </div>
            <script>
            $(document).ready(function () {
                // setTimeout(function () {
                //     var lang = $(".goog-te-menu-value span:first").text();
                //     if (lang == 'Arabic') {
                //         $('input').css('direction', 'rtl');
                //         $('textarea').css('direction', 'rtl');
                //         $('.page-header input').css('direction', 'ltr');
                //         $('.page-header textarea').css('direction', 'ltr');
                //     }
                // }, 5000);

                function changeHeaderLangDeferred() {
                    var lang = $(".goog-te-menu-value span:first").text();

                    if (lang == 'Arabic') {
                        setTimeout(function changeHeader() {
                            $('input').css('direction', 'rtl');
                            $('textarea').css('direction', 'rtl');
                            $('.page-header input').css('direction', 'ltr');
                            $('.page-header textarea').css('direction', 'ltr');
                        
                            return false;
                        }, 5000);
                    }
                    return false;
                }
                // changeHeaderLangDeferred();

                $('#btnSearch').click(function (event) {
                    $('.loading-text').removeClass('hide').addClass('show');
                });
            });
            </script>