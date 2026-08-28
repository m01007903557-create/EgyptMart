

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
       if (keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search' || keywords == 'Enter Supplier to search' || keywords == 'Enter Tender to search') {
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
<script type="test/javascript">

    function showcontent(x){

      if(window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
      } else {
        xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
      }

      xmlhttp.onreadystatechange = function() {
        if(xmlhttp.readyState == 1) {
            document.getElementById('content').innerHTML = "<img src='images/loadingif.gif' />";
        }
        if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
          document.getElementById('content').innerHTML = xmlhttp.responseText;
        } 
      }

      xmlhttp.open('POST', x+'.html', true);
      xmlhttp.setRequestHeader('Content-type','application/x-www-form-urlencoded');
      xmlhttp.send(null);

    }
    </script>

<?php
   /**
    * Created by PhpStorm.
    * User: Long
    * Date: 12/18/2015
    * Time: 11:49 PM
    */
   function getLocationInfoByIp1(){
    $client  = @$_SERVER['HTTP_CLIENT_IP'];
    $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
    $remote  = @$_SERVER['REMOTE_ADDR'];
    $result  = array('country'=>'', 'city'=>'');
    if(filter_var($client, FILTER_VALIDATE_IP)){
        $ip = $client;
    }elseif(filter_var($forward, FILTER_VALIDATE_IP)){
        $ip = $forward;
    }else{
        $ip = $remote;
    }
    //$ip = "1.0.63.255";
    $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));    
    if($ip_data && $ip_data->geoplugin_countryName != null){
        $result['country'] = $ip_data->geoplugin_countryCode;
        $result['city'] = $ip_data->geoplugin_city;
    }
    return $result;
}

$location = getLocationInfoByIp1();
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
         <li><a href="sign-in.php" target="_top" rel="nofollow" style="margin-left:8px;">Sign in</a></li>
         |
         <li><a href="create_account.php" target="_top" rel="nofollow">Join Free &nbsp;|</a></li>
         <?php } ?>
         <li class="dropdown dropdown1">
            <a data-target="myArabyos"  class="dropbtn1" href="" data-toggle="dropdown" role="button"
               aria-haspopup="true" aria-expanded="false" >
            <b class="txt-yellow" style="font-weight:900;">M<span class="s-small">Y</span> <?php echo getWebSiteName(); ?></b> <i class="fa fa-chevron-down"></i>
            
            </a><span class="linebr" style="color: black"> |</span>  <a href="my-enquiries.php">
            <img width="25" src="images/envolap.png"/>
            <?php 
            if(isset($_SESSION['uid_indm'])){
            $query_pag_num = "SELECT count(*) AS count from message,user where msg_to='".$_SESSION['uid_indm']."' and msg_from=usr_id and msg_to_status='1'"; // Total records
            $result_pag_num = mysqli_query($con, $query_pag_num);
            $row = mysqli_fetch_array( $result_pag_num);
            $count = $row['count'];
            echo '<span class="label label-yellow">'.$count.'</span>';
          }
          else{
            echo '<span class="label label-yellow">0</span>';
          }
            ?>
            </a>
            
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="myArabyos" style="width:101%; z-index: -1;">
               <li><a href="my-dashboard.php">My Dashboard</a></li>
               <li><a href="my-enquiries.php">My Inbox</a></li>
               <li><a href="buyleads.php">Buy Leads</a></li>
               <li><a href="image-gallery.php">Image Gallery</a></li>
            
            <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
         <li><a href="logout.php">Sign Out</a></li>
         </li>
         <?php } ?>        
         </li>
          </ul>
        <!-- <li class="ar-lebel" >
           <a href="my-enquiries.php">
            <img width="25" src="images/envolap.png"/>
            <span class="label label-yellow">0</span>
            </a>
         </li>-->
      </ul>
      <ul class="text-left" id="top-center">
         <li style="color: orange" > Credit : <a href="#" class="txt-bold txt-yellow" style="font-weight:900 ; font-size:13px; color: orange">
            <b style="color: white">0</b></a>
         <!--   <b class="txt-yellow">0</b></a>-->
         </li>
         <li><a href="subscription.php" style="margin:0px; padding:0px;">| &nbsp;Buy Credit</a></li>
         <li class="dropdown dropdown1" id="buy">
            <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">  Buy <i class="fa fa-chevron-down"></i>
            </a>
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="buy">
               <li><a href="post-buy-req.php">Post Your Buy Requirement</a></li>
               <li><a href="search_adv.php">Search Product &amp; Suppliers</a></li>
               <li><a href="manage-selloffer-alert.php">Manage Sell Offer Alerts</a></li>
               <li><a href="auctions.php">Latest Auctions</a></li>
            </ul>
         </li>
         <li class="dropdown dropdown1" id="sell">
            <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown"
               role="button" aria-haspopup="true" aria-expanded="false"
               >
            Sell <i class="fa fa-chevron-down"></i>
            </a>
            <ul class="dropdown-menu ar-dropdown-menu dropdown-content1" aria-labelledby="sell">               
               <li><a href="product-add.php">Display Free Products</a></li>
               <li><a href="create-free-website.php">Create Free Website</a></li>
               <li><a href="buyleads.php">Latest Buy Leads</a></li>
               <li><a href="#">Post a Temporary Sale Offer</a></li>
               <li><a href="manage-buylead-alert.php">Manage Buy Lead Alerts</a></li>
               <li><a href="tenders.php">Latest Tenders</a></li>
            </ul>
         </li>
      </ul>
     
      <ul class="text-right" id="top-right">
         <li><a href="help.php" style="margin-right:22px; color:orange ">Help Line : <b style="font-weight:900; color: white"> <span
            class="txt-yellow"></span><?php echo get_page_settings(21); ?></b></a></li>
         <li><a href="manage-purchased-buyleads.php" class=" txt-yellow" style="margin-right:22px;"><b class="txt-yellow"  style="font-weight:900;">Why  ARABYOS</b></a>
         </li>
         <li style="padding-right:3px;"><a href="help.php">Help</a></li>
      </ul>
   </div>
</div>
<!-- End of topbar // -->
<div class="maincontainer">
   <!-- page-header start -->
   <header class="page-header">
      <div class="page-header-col1">
         <!-- page-header-col1 start -->
         <div class="col-md-9 page-header-col1-row1">
            <!-- col-md-9 start -->
            <div class="page-header-col1-row1-col1">
               <!-- page-header-col1-row1-col1 start -->
               <div class="page-header-col1-row1-col1_row">
                  <p><a href="my-contactdetails.php">Account Setting</a></p>
               </div>
               <div class="page-header-col1-row1-col1_row2">
                  <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
                     <?php
                        if (isset($_COOKIE['loc_id'])) { ?>
                     <span style="font-weight:bold; font-size:21px; color: darkcyan; font-family: Arial Black"><?php echo get_country_name($_COOKIE['loc_id']); ?></span>&nbsp;
                     <img src="images/country_flag/<?php echo get_country_flag($_COOKIE['loc_id']); ?>"
                        alt="<?php echo get_country_name($_COOKIE['loc_id']); ?>" class="w4" align="top" height="16"
                        width="23" title="<?php echo get_country_name($_COOKIE['loc_id']); ?>"/>
                     <?php } else { ?>
                     <b>Global</b> &nbsp; <img src="images/country_flag/Global$download.png" alt="Global" class="w4"
                        align="top" height="30" width="30"/>
                     <?php } ?>
                  </div>
                  <div class="page-header-col1-row1-col1-row2-form">
                     <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()">
                        <a class="un" style="border-left:none; font-size: 10px; color:#0f2399; font-weight: bold;font-family: arial black;">
                           <span style="color: black;">Change</span> Country
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
                                       //echo $location['country']."--".$row_cnLoc->cn_code."<br/>";
                                       /* if($location['country'] == $row_cnLoc->cn_code){ ?>
                                       <script>
                                        setCountryLocation(<?php echo $row_cnLoc->cn_id ?>);
                                        </script>
                                       <?php } */
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
            <div class="page-header-col1-row1-col2">
               <?php 
                  $toplogo=GettingSite_Setting('logo');
                  if($toplogo!="")
                  {
                  	$toplogo2show = "sitelogo/".$toplogo;
                  }
                  else
                  {
                     $toplogo2show = "images/page-header-col1-row1-col2-logo.png";	
                  }
                  ?>
               <a href="index.php"><img src="<?php echo $toplogo2show;?>" alt="" style="max-width:190px; max-height:108px;"/></a>
               <!--<p>Arabs Home &amp; Global Trade</p>-->
            </div>
            <!-- page-header-col1-row1-col2 close// -->
            <div class="page-header-col1-row1-col3">
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
            <!-- page-header-col1-row1-col3 close// -->
            <div class="page-header-col1-row1-col4">
               <!-- page-header-col1-row1-col4 start -->
               <div class="page-header-col1-row1-col4-row1">
               <img src="images/busine.png" width="218px"/>
                 <!-- <h2>Business Alerts</h2>
                  <p>Get timely updates in your inbox for favorite products & services</p>-->
               </div>
               <script>
                  function sub(){
                      var location ="";
                      if (document.getElementById('radio1').checked) {
                        location = document.getElementById("radio1").value;
                      }
                      if (document.getElementById('radio2').checked) {
                        location = document.getElementById("radio2").value;
                      }
                      window.location=location;
                  }
               </script>
               <div class="page-header-col1-row1-col4-row2">
                  <div class="page-header-col1-row1-col4-row2-checkbox">
                     <label class="radio">
                     <input id="radio1" type="radio" name="radios" value="manage-selloffer-alert.php">
                     <span class="outer"><span class="inner"></span></span><a href="#" style="color: black">Buyer</a>
                     </label>
                     <label class="radio" style="margin-left:0px; font-size: 17px">
                     <input id="radio2" type="radio" name="radios" value="manage-buylead-alert.php" checked>
                     <span class="outer"><span class="inner"></span></span><a href="#" style="color: black">Supplier</a>
                     </label>
                  </div>
                  <div class="page-header-col1-row1-col4-row2-link"><a id="sub" onclick="return sub();" href="#">Subscribe Now</a></div>
               </div>
            </div>
            <!-- page-header-col1-row1-col4 close// -->
         </div>
         <!-- col-md-9 close// -->
         <div class="clear"></div>
         <div class="page-header-col1-row2">
            <!-- page-header-col1-row2 start -->
            <div class="page-header-col1-row2-col1 zoomin1">
               <a href="product-sel-cat.php"> <img src="images/Postproducts.jpg" /></a>
               <!-- <h1><a href="product-sel-cat.php">Display Your Prouducts</a></h1>
                  <a href="product-sel-cat.php"> <p>Get <span>Home & Global</span> Enquiries </p></a> -->
            </div>
            <div class="page-header-col1-row2-col2">              
               <div class="srchBx">
                  <h1 class="cd-headline clip is-full-width">
                   <span style="width: 548.16px; overflow: hidden; color:#165EDB; font-family: Arial narrow;" class="cd-words-wrapper" > 
                   <b class="is-hidden">Find anything for your Business <span class="blinking-cursor" style="color: gray">!</span></b>
                    <b class="is-hidden">Source >> Supply >> Grow  Business <span class="blinking-cursor" style="color: gray">!</span> </b>
                     <b class="is-visible">Create your Home &amp; Global website <span class="blinking-cursor" style="color: gray">!</span></b>
                      </span>
                     </h1>
               </div>
               <!-- <script src="https://code.jquery.com/jquery-2.1.1.min.js" type="text/javascript"></script> -->
               <script>
                 $(document).ready(function(){
	$("#search-box").keyup(function(){
		$.ajax({
		type: "POST",
		url: "readproducts.php",
		data:'keyword='+$(this).val(),
		beforeSend: function(){
			$("#search-box").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
		},
		success: function(data){
			$("#suggesstion-box").show();
			$("#suggesstion-box").html(data);
			$("#search-box").css("background","#FFF");
		}
		});
	});
});

function selectCountry(val) {
$("#search-box").val(val);
$("#suggesstion-box").hide();
}
               </script>
               <div class="page-header-col1-row2-col2-form">
                  <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                     <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                        <option value="Suppliers">Suppliers</option>
                        <option  value="Products" selected>Products</option>
                        <option value="buy_lead">Buy Leads</option>
                        <option value="tender">Tender</option>
                        <!--<option value="auction">Auction</option>-->
                     </select>
                     <!--
                        <input type="hidden" id="keyword_type" name="keyword_type"
                               value="<?php if ($_GET['rctyp'] != "" && $_GET['rctyp'] == "buy_lead") {
                           echo "Buy Leads";
                           } elseif ($_GET['rctyp'] != "") {
                           echo $_GET['rctyp'];
                           } else { ?>Products<?php } ?>"/> -->
                     <input type="text" id="search-box" name="keywords" placeholder="Source product / service to find suppliers"
                        class="page-header-col1-row2-col2-form-input"  onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo $_GET['keywords']; ?>"  
                        />
                     <div id="suggesstion-box"></div>
                     <input type="submit" id="btnSearch" value="" class="page-header-col1-row2-col2-form-btn"/>
                     <!-- <input type="hidden" name="rctyp" id="rctyp" value="<?php if ($_GET['rctyp'] != "") {
                        echo $_GET['rctyp'];
                        } else { ?>Products<?php } ?>"/>-->
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
                     <span><a href="search_adv.php">Advanced Search</a></span>
                  </p>
               </div>
            </div>
            <!-- page-header-col1-row2-col2 close// -->
            <div class="page-header-col1-row2-col3">
               <p>or</p>
            </div>
            <div class="page-header-col1-row2-col4 ">
               <!-- page-header-col1-row2-col4 start -->
               <h1>Just a click away</h1>
               <div class="zoomin2" >
                  <!--<a id="light"  href="post-buy-req.php">    </a>
                    <a id="dark"  href="post-buy-req.php"> </a>-->
                    <img  id="aks" src="images/light.png"/>
               </div>
            </div> 
            <!-- page-header-col1-row2-col4 close// -->
            <div class="clear"></div>
         </div>
       <!--  <link rel="stylesheet" href="css/jquery.autocomplete.css" type="text/css"/>-->
       <!--  <script type="text/javascript" src="js/jquery.autocomplete2.js"></script>-->
        <!-- <script type="text/javascript">
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
         </script>-->
         <!-- page-header-col1-row2 close// -->
      </div>
      <!-- page-header-col1 close// -->
      <div class="page-header-col2">
         <div class="page-header-col2-head">
            <i class="fa fa-mobile"></i>
            <span>Android - Apple - Windows - Blackberry</span>
         </div>
         <div class="page-header-col2-intro">
            <div class="page-header-col2-intro-pic"><img src="images/page-header-col2-intro-pic.jpg" alt=""/>
            </div>
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

