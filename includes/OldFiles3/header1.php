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
    }

    function gotFocus() {
        var keywords = $("input#keywords").val();
        if (keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search') {
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

<?php
/**
 * Created by PhpStorm.
 * User: Long
 * Date: 12/18/2015
 * Time: 11:49 PM
 */
?>

<!-- Top Blue Bar-->
    <div class="row top-bar" id="topbar">
        <div class="maincontainer">
            <ul>
                <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
                    $uid = $_SESSION['uid_indm'];
                    ?>
                    <li><span class="pp1"><span
                                class="tlc">Welcome: </span><?php echo user_info($uid, 'name_prefix') . "&nbsp;" . user_info($uid, 'fname'); ?></span>
                    </li>
                <?php } else { ?>
                <li><a href="sign-in.php" target="_top" rel="nofollow" style="margin-left:8px;">Sign in</a></li>|
                <li><a href="create_account.php" target="_top" rel="nofollow">Join Free &nbsp;|</a></li>
                <?php } ?>
                <li class="dropdown">
                    <a data-target="myArabyos" href="" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false" style="margin-left:15px;">
                        <b class="txt-yellow" style="font-weight:900;">M<span class="s-small">Y</span> <?php echo getWebSiteName(); ?></b> <i class="fa fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="myArabyos" style="width:auto;">
                        <li><a href="my-dashboard.php">My Dashboard</a></li>
                        <li><a href="my-enquiries.php">My Inbox</a></li>
                        <li><a href="buyleads.php">Buy Leads</a></li>
                        <li><a href="image-gallery.php">Image Gallery</a></li>
                        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
                        <li></li><a href="logout.php">Sign Out</a></li>
                        <?php } ?>
                    </ul>

                </li>
                <li class="ar-lebel" >
                    <a href="my-enquiries.php">
                        <img width="25" src="images/envolap.png"/>
                        <span class="label label-yellow">0</span>
                    </a>
                </li>
            </ul>
            <ul class="text-left" id="top-center">
                <li style="margin: 0 0 0 35px"> Credit : <a href="#" class="txt-bold txt-yellow" style="font-weight:900 ; font-size:13px;">
                    <b class="txt-yellow">0</b></a></li>
                <li><a href="subscription.php" style="margin:0px; padding:0px;">| &nbsp;Buy Credit</a></li>
                <li class="dropdown" id="buy">
                    <a class="ar-lebel" data-target="#" href="#" data-toggle="dropdown" role="button"
                       aria-haspopup="true" aria-expanded="false">
    Buy <i class="fa fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="buy">
                        <li><a href="auctions.php">Latest Auctions</a></li>
                        <li><a href="manage-selloffer-alert.php">Manage Sell Offer Alerts</a></li>
                        <li><a href="post-buy-req.php">Post Your Buy Requirement</a></li>
                        <li><a href="search_adv.php">Search Product &amp; Suppliers</a></li>
                    </ul>
                </li>
                <li class="dropdown" id="sell">
                    <a class="ar-lebel" data-target="#" href="#" data-toggle="dropdown"
                                                  role="button" aria-haspopup="true" aria-expanded="false"
                                                  style="margin-left:15px;">
                        Sell <i class="fa fa-chevron-down"></i>
                    </a>
                    <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="sell">
                        <li><a href="buyleads.php">Latest Buy Leads</a></li>
                        <li><a href="tenders.php">Latest Tendors</a></li>
                        <li><a href="create-free-website.php">Create Free Website</a></li>
                        <li><a href="product-add.php">Display Free Products</a></li>
                        <li><a href="#">Post a Temporary Sale Offer</a></li>
                        <li><a href="manage-buylead-alert.php">Managed Buy Lead Alerts</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="text-right" id="top-right">
                <li><a href="help.php" style="margin-right:22px;">Help Line : <b style="font-weight:900;"> <span
                        class="txt-yellow"></span><?php echo get_page_settings(21); ?></b></a></li>
                <li><a href="#" class=" txt-yellow" style="margin-right:22px;"><b class="txt-yellow"
                                                                                  style="font-weight:900;">Why
                    ARABYOS</b></a></li>
                <li style="padding-right:3px;"><a href="#">Help</a></li>
            </ul>
        </div>
    </div>
    <!-- End of topbar // -->



<div class="maincontainer">
        <!-- page-header start -->
        <header class="page-header">
            <div class="page-header-col1"><!-- page-header-col1 start -->
                <div class="col-md-9 page-header-col1-row1"><!-- col-md-9 start -->
                    <div class="page-header-col1-row1-col1"><!-- page-header-col1-row1-col1 start -->
                        <div class="page-header-col1-row1-col1_row">
                            <p><a href="my-contactdetails.php">Account Setting</a></p></div>
                        <div class="page-header-col1-row1-col1_row2">
                            <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
                                <?php
                                if (isset($_COOKIE['loc_id'])) { ?>
                                    <span style="weight:700px; color: darkcyan;"><?php echo get_country_name($_COOKIE['loc_id']); ?></span>&nbsp;
                                    <img src="images/country_flag/<?php echo get_country_flag($_COOKIE['loc_id']); ?>"
                                        alt="<?php echo get_country_name($_COOKIE['loc_id']); ?>" class="w4" align="top" height="16"
                                        width="23" title="<?php echo get_country_name($_COOKIE['loc_id']); ?>"/>
                                <?php } else { ?>
                                    <b>Global</b>
                                <?php } ?>
                            </div>
                            <div class="page-header-col1-row1-col1-row2-form">
                                <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()">
                                    <a class="un" style="border-left:none;">Change Country
                                        <span class="arw">&#9660;</span></a>
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
                                                                <img src="images/country_flag/Globe.png" alt="Global" class="w4"
                                                                     align="top" height="16" width="16"/>
                                                            </a>
                                                        </td>
                                                        <?php
                                                        $cn = 1;
                                                        while ($row_cnLoc = mysqli_fetch_object($res_cnLoc)){
                                                        if ($cn % 4 == 0){
                                                        $cn = 0; ?></tr>
                                                    <tr><?php }
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
                                                            <td>&nbsp;</td><?php $cn++;
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
                    <div class="page-header-col1-row1-col2">
					<?php 
					$toplogo=GettingSite_Setting('unit-logo');
					
					if($toplogo!="")
					{
						$toplogo2show = "sitelogo/".$toplogo;
					}
					else
					{
					   $toplogo2show = "images/page-header-col1-row1-col2-logo.png";	
					}
					?>
                        <a href="#"><img src="<?php echo $toplogo2show;?>" alt="" style="max-width:190px; max-height:85px;"/></a>

                        <p>Arabs Home &amp; Global Trade</p>
                    </div><!-- page-header-col1-row1-col2 close// -->
                    <div class="page-header-col1-row1-col3"><!-- page-header-col1-row1-col3 start -->
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
</div><!-- page-header-col1-row1-col3 close// -->
<div class="page-header-col1-row1-col4"><!-- page-header-col1-row1-col4 start -->
                        <div class="page-header-col1-row1-col4-row1">
                            <h2>Business Alerts</h2>

                            <p>Get timely updates in your inbox for favorite products & services</p>
                        </div>
                        <div class="page-header-col1-row1-col4-row2">
                            <div class="page-header-col1-row1-col4-row2-checkbox">
                                <label class="radio" style="margin-left:5px;">
                                    <input id="radio1" type="radio" name="radios" checked>
                                    <span class="outer"><span class="inner"></span></span><a href="manage-selloffer-alert.php">Buyer</a>
                                </label>
                                <label class="radio" style="margin-left:3px;">
                                    <input id="radio1" type="radio" name="radios" checked>
                                    <span class="outer"><span class="inner"></span></span><a href="manage-buylead-alert.php">Supplier</a>
                                </label>
                            </div>
                            <div class="page-header-col1-row1-col4-row2-link"><a href="#">Subscribe Now</a></div>
                        </div>
                    </div><!-- page-header-col1-row1-col4 close// -->
                </div><!-- col-md-9 close// -->
                <div class="clear"></div>
                <div class="page-header-col1-row2"><!-- page-header-col1-row2 start -->
                    <div class="page-header-col1-row2-col1">
                        <h1><a href="product-sel-cat.php">Display Your Prouducts</a></h1>

                       <a href="product-sel-cat.php"> <p>Get <span>Home & Global</span> Enquiries </p></a>
                    </div>
                    <div class="page-header-col1-row2-col2"><!-- page-header-col1-row2-col2 start -->

                        <div class="page-header-col1-row2-col2-head">

                            <!--
                            <h2>Source anything for your Business Instantly</h2>
-->
                            <h1 class="cd-headline clip is-full-width">
                             <span class="cd-words-wrapper">
                              <b class="is-hidden">Find Anything for Your Business Instantly</b>
                              <b class="is-hidden">Source > Supply > Grow Your Business</b>
                              <b class="is-visible">Create Your Home &amp; Global Website on ARABYOS.</b>
                             </span>
                            </h1>
                        </div>

                        <div class="page-header-col1-row2-col2-form">
                            <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                                <select class="page-header-col1-row2-col2-form-select">
                                    <option value="Suppliers">Suppliers</option>
                                    <option value="Products">Products</option>
                                    <option value="buy_lead">Buy Leads</option>
                                    <option value="tender">Tender</option>
                                    <option value="auction">Auction</option>
                                </select>
                                <input type="hidden" id="keyword_type" name="keyword_type"
                                       value="<?php if ($_GET['rctyp'] != "" && $_GET['rctyp'] == "buy_lead") {
                                           echo "Buy Leads";
                                       } elseif ($_GET['rctyp'] != "") {
                                           echo $_GET['rctyp'];
                                       } else { ?>Products<?php } ?>"/>
                                <input type="text" name="nm" placeholder="Source product /service to find suppliers"
                                       class="page-header-col1-row2-col2-form-input"  onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo $_GET['keywords']; ?>"
                                />
                                <input type="submit" id="btnSearch" value="" class="page-header-col1-row2-col2-form-btn"/>
                                <input type="hidden" name="rctyp" id="rctyp" value="<?php if ($_GET['rctyp'] != "") {
                                    echo $_GET['rctyp'];
                                } else { ?>Products<?php } ?>"/>
                            </form>
                            <div class="clear"></div>
                        </div>
                        <div class="page-header-col1-row2-col2-links">
                            <p>
							Top Searches : 
							<?php 
							$topsrchlist="";
							$sqlsrch = "SELECT COUNT(searchid) AS theCount, keyword,url from search_history GROUP BY keyword ORDER BY theCount DESC LIMIT 5";
							$rssrch= mysqli_query($con,$sqlsrch) or die("Error".mysqli_error());
							if(mysqli_num_rows($rssrch) > 0)
							{
								while($rowsrch=mysqli_fetch_object($rssrch))
								{
									$keyword = $rowsrch->keyword;
									//$url = $rowsrch->url;
                                                                        $urlarr = explode("?",$rowsrch->url);
									$url = "search.php?".$urlarr[1];
									if($topsrchlist=="")
									{
										$topsrchlist = '<a href="'.$url.'" target="_blank">'.$keyword.'</a>';
									}
									else
									{
										$topsrchlist .= " , ".'<a href="'.$url.'" target="_blank">'.$keyword.'</a>';
									}
								}
								echo $topsrchlist;
							}
							?>
                                <span><a href="search_adv.php">Advanced Search</a></span></p>
                        </div>
                    </div><!-- page-header-col1-row2-col2 close// -->
<div class="page-header-col1-row2-col3">
                        <p>or</p>
                    </div>
                    <div class="page-header-col1-row2-col4"><!-- page-header-col1-row2-col4 start -->
                        <h1>Just a click away</h1>
                        <a href="post-buy-req.php" class="page-header-col1-row2-col4-btn">
                            <p>Post Buy Requests</p>
                            <span>Get <ins>Quotes </ins> from <ins>Verified Suppliers</ins></span> </a></div>
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
                </div><!-- page-header-col1 close// -->
                <div class="page-header-col2">
                                <div class="page-header-col2-head">
                                    <i class="fa fa-mobile"></i>
                                    <span>Android - Apple - Windows - Blackberry</span>
                                </div>
                                <div class="page-header-col2-intro">
                                    <div class="page-header-col2-intro-pic"><img src="images/page-header-col2-intro-pic.jpg" alt=""/>
                                    </div>
                                    <div class="page-header-col2-intro-texts">
                                        <h2><a href="product-sel-cat.php">Post Your Services</a></h2>

                                        <p>Get <span>Domestic</span> or <span>Global</span> Enquiries</p>
                                    </div>
                                </div>
                                <div class="clear"></div>
                            </div>
                <div class="clear"></div>
            </header>
        <!-- page-header close // -->

    </div>


    <!-- Start of rowbanner -->
    <div class="toplist">
        <div class="middle-bar">

            <!-- Start of rowbanner -->
            <div class="centertopbanner">
				
					<?php 
					$banner=GetHomeBanner('top',$strconutnry);
					if($banner!="")
					{ 
				      echo '<div class="middle mid-content" style="padding:0;">';
					  echo $banner;
					  echo '</div>';
					}
					else
					{
						//echo '<div class="middle mid-content">';
						//echo ' <h3>Banner Place</h3>';
						// echo '</div>';
					}
					?>
					<div class="clear"></div>
				   
            </div>
            <!-- End of rowbanner // -->

</div>
    </div>
    <!-- End of rowbanner // -->