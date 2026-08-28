<style>
body {
  background-color: rgb(237, 242, 245);
}
.page-header-col1-row2-col4 h1{
  text-align: left;
  font-size: 14px;
}
</style>
 
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
               // $("#cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
               location.reload();
           }
       });
   }
   function unsetCountryLocation() {
       $.post("unsetCountryLocation.php", function (data) {
           // $("#cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
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
    <?php $cid;
    if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
            $uid = $_SESSION['uid_indm'];
      $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join user u on sip.mp_id = u.usr_mp_id where u.usr_id = ".$uid;
      $get_icon = mysql_query($sql_icon) or die(mysql_error());
      $sql="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$uid."' and status = '1'";
      $res=mysqli_query($con, $sql);
      $row=mysqli_fetch_object($res);
      $cid=rand(1000,9999).md5($row->bnsprof_id);
      ?>
        <li><span class="pp1"><span
            class="tlc">Welcome: </span><?php echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname');
      if($row->bnsprof_compname !=''){
      ?> <span>
          <?php if(mysql_num_rows($get_icon) > 0){
        $title = 'Junior';
        $icon = mysql_fetch_array($get_icon);
        if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
        $title = 'Senior';
        }
        else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
        $title = 'Sponsor';
        }
          ?>
          <a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;"  alt=""/></a>
          <?php }
      ?>
          </span>
          <?php } ?>
          </span> </li>
        <?php } else { ?>
        <li><a href="sign-in.php" target="_top" rel="nofollow">Sign in</a></li>
        |
        <li><a href="create_account.php" target="_top" rel="nofollow">Join Free &nbsp;|</a></li>
        <?php } ?>
        <li class="dropdown dropdown1"  style="z-index: 100;"> <a data-target="myArabyos"  class="dropbtn1" href="" data-toggle="dropdown" role="button"
               aria-haspopup="true" aria-expanded="false" > <b class="txt-yellow" style="font-weight:900;">M<span class="s-small">Y</span> <?php echo getWebSiteName(); ?></b> <i class="fa fa-chevron-down"></i> </a><span class="linebr" style="color: black"> |</span> <a href="my-enquiries.php"> <img width="25" src="images/envolap.png"/>
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
            <!--<li><a href="compare.php">Compare</a></li>-->
            <li><a href="favorite.php">My Favorite</a></li>
            <li><a href="image-gallery.php">Image Gallery</a></li>
            <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
            <li><a href="logout.php">Sign Out</a></li>
            <?php } ?>
          </ul>
        </li>
      </ul>
    
    <ul class="text-left" id="top-center">
      <?php if(getUserInfo($uid, 'usr_mp_id') < 4){ ?>
       <!-- <li style="color: orange; padding-left:3px;" > Credit : <a href="#" class="txt-bold txt-yellow" style="font-weight:900 ; font-size:13px; color: orange"> <b style="color: white"><?php echo (getUserInfo($uid, 'usr_credit') > 0)?getUserInfo($uid, 'usr_credit'):'0'; ?></b></a> </li>
        <li><a href="subscription.php" style="margin:0px; padding:0px;">| &nbsp;Buy Credit</a></li>-->
        <?php }   ?>
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
    <li><a href="company/index.php?c=<?php echo $cid; ?>" class="txt-yellow" style=" color:#fff450;font-weight:700;">My Website</a></li>
    <?php } ?>
    <li class="contact_photooo"><a style="margin-left: 25px;" href="<?php echo BASE_URL ?>my-contactdetails.php">
      <?php if(user_info($uid,'image')!=""){ ?>
      <img  src="<?php echo 'data:image/jpg;base64,'.base64_encode( getUserInfo($uid,'profileImage'));?>"  width="30" id="profilephoto" height="30"> <span class="user-name-topbar"><?php echo getUserInfo($uid, 'fname'); ?></span>
      <?php } else{ ?>
      <img src="http://arabyos.com/images/upload.png"  width="30" id="profilephoto" height="30"><span class="user-name-topbar"><?php echo getUserInfo($uid, 'fname'); ?></span>
      <?php } ?>
      </a>
    </li>
       <li class="dropdown dropdown1"> <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> Buy <i class="fa fa-chevron-down"></i> </a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur" aria-labelledby="buy">
            <li><a href="post-buy-req.php">Post Your Buy Requirement</a></li>
            <li><a href="search_adv.php">Search Product &amp; Suppliers</a></li>
            <li><a href="manage-selloffer-alert.php">Manage Sell Offer Alerts</a></li>
            <li><a href="post-auction.php">Post FREE Auctions</a></li>
          </ul>
        </li>
      <li class="dropdown dropdown1" id="sell"> <a class="ar-lebel dropbtn1" data-target="#" href="#" data-toggle="dropdown"
               role="button" aria-haspopup="true" aria-expanded="false"
               > Sell <i class="fa fa-chevron-down"></i> </a>
          <ul class="dropdown-menu ar-dropdown-menu dropdown-content1 dropdown-menur " aria-labelledby="sell">
            <li><a href="product-add.php">Display Products / Services</a></li>
            <li><a href="create-free-website.php">Create Website</a></li>
            <li><a href="buyleads.php">Latest Buy Requests</a></li>
            <li><a href="http://www.arabyos.com/post-sell-offer.php">Post a Temporary Sale Offer</a></li>
            <li><a href="manage-buylead-alert.php">Manage Buy Requests Alerts</a></li>
            <li><a href="post-tender.php">Post FREE Tenders</a></li>
          </ul>
        </li>
    </ul>

    <ul class="text-right" id="top-right">
      <li><a href="#" style="margin-right:22px;">Help Line : <b style="font-weight:900;"> <span class="txt-yellow">+</span>2012209704444</b></a> </li>
      <li> <a href="#" class=" txt-yellow" style="margin-right:22px;"><b class="txt-yellow" style="font-weight:900;">Why ARABYOS</b></a> </li>
      <li style="padding-right:3px;"> <a href="#">Help</a> </li>
    </ul>
    </div>
  </div>
<!-- End of topbar // -->
<!-- Start of top_bg_navigation -->
<div id="menu">
<ul>
<?php $cid;
if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
        $uid = $_SESSION['uid_indm'];
  $sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join user u on sip.mp_id = u.usr_mp_id where u.usr_id = ".$uid;
  $get_icon = mysql_query($sql_icon) or die(mysql_error());
  $sql="select * from user,business_profile where usr_id=bnsprof_uid and usr_id='".$uid."' and status = '1'";
  $res=mysqli_query($con, $sql);
  $row=mysqli_fetch_object($res);
  $cid=rand(1000,9999).md5($row->bnsprof_id);
  ?>
    <li class="displayFlex" style="display: inline-flex;"><a href="<?php echo BASE_URL ?>my-contactdetails.php" >Welcome:<?php echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname');?></a>
  <?php if($row->bnsprof_compname !=''){
  ?>
      <?php if(mysql_num_rows($get_icon) > 0){
    $title = 'Junior';
    $icon = mysql_fetch_array($get_icon);
    if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
    $title = 'Senior';
    }
    else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
    $title = 'Sponsor';
    }
      ?>
      <a href="company/index.php?c=<?php echo $cid; ?>"><img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo strtoupper($title); ?>" style="width:18px; height:15px;border:0;"  alt=""/></a>
      <?php }
  ?>
     
      <?php } ?> </li>
    <?php } else { ?>
   <li class="displayFlex" style="display: inline-flex;"><a href="sign-in.php" target="_top" rel="nofollow">Sign in</a><a href="create_account.php" target="_top" rel="nofollow"> | Join Free</a></li>
   <?php }?>
   <li class="active">
     <a id="message" class="ar-lebel" aria-expanded="false" aria-haspopup="true" role="button" data-toggle="dropdown" href="#" data-target="#">
     <img width="25" src="images/envolap.png" alt="" /> <b>My Arabyos</b>
     </a> 
      <ul>
        <li><a href="my-dashboard.php">My Dashboard</a></li>
        <li><a href="my-enquiries.php">My Inbox</a></li>
        <li><a href="buyleads.php">Buy Leads</a></li>
        <!--<li><a href="compare.php">Compare</a></li>-->
        <li><a href="favorite.php">My Favorite</a></li>
        <li><a href="image-gallery.php">Image Gallery</a></li>
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
        <li><a href="logout.php">Sign Out</a></li>
        <?php } ?>
      </ul>
      <div class="clear"></div>
   </li>
   <li><a href="#">Help Line: <ins>+</ins>2012209704444</a></li>
   <li class="active"><a href="#" style="border-right:0;"><b>Why Arabyos</b></a></li> 
   <li><a href="#">Help</a></li>
</ul>
</div>
<!-- menu close // -->


   <div class="maincontainer">
   <!-- page-header start -->
    <header class="page-header">
     <div class="page-header-col1"><!-- page-header-col1 start -->
         <div class="col-md-9 page-header-col1-row1"><!-- col-md-9 start -->
             <div class="page-header-col1-row1-col1"><!-- page-header-col1-row1-col1 start -->
                 <div class="page-header-col1-row1-col1_row">
               <p><a href="#">Account Setting</a></p></div>
    <div class="page-header-col1-row1-col1_row2">
                     <div class="page-header-col1-row1-col1_row2_pic" id="cnlocation">
              <?php
//secho "<pre>";print_r($_COOKIE);"</pre>";
                        if (isset($_COOKIE['loc_id'])) {
//echo "<pre>";print_r($_COOKIE);"</pre>";
//echo get_country_flag($_COOKIE['loc_id']);
            ?>
              <span><?php echo get_country_name($_COOKIE['loc_id']); ?></span>&nbsp; <img src="images/country_flag/<?php echo get_country_flag($_COOKIE['loc_id']); ?>"
                        alt="<?php echo get_country_name($_COOKIE['loc_id']); ?>" class="w4" align="top" height="16"
                        width="23" title="<?php echo get_country_name($_COOKIE['loc_id']); ?>"/>
              <?php } else { ?>
              <span style="font-weight: bold; font-size: 20px;  color: darkcyan;  font-family: Arial Black;">Global</span> &nbsp; <img src="images/country_flag/Global$download.png" alt="Global" class="w4"
                        align="top" height="30" width="30"/>
              <?php } ?>
            </div>
                        <div class="page-header-col1-row1-col1-row2-form">
              <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()"> <a class="un" style="border-left:none; font-size: 9px; color:#0f2399; 
              "> <span style="color: black;">Change</span> Country 
                <!--  <i class="fa fa-chevron-down"></i>--> 
                &nbsp;<span class="arw"><b>&or;</b></span> </a>
                <div class="sub_menu" style="display:none; left:0; top:105px; right:0;" id="changeLocation">
                  <ul>
                    <li>
                      <?php
                                    $numCun = count(explode(",", getActiveCountryList()));
                                    $sql_cnLoc = "select * from country where cn_id in(" . getActiveCountryList() . ")";
                                    $res_cnLoc = mysqli_query($con, $sql_cnLoc);
                                    ?>
                      <table style="width:100%;padding:1px;" class="table-responsive">
                        <tr>
                          <td align="center"><a title="Global" style="cursor:pointer;" onclick="unsetCountryLocation();"> <img src="images/country_flag/Global$download.png" alt="Global" class="w4"
                                             align="top" height="16" width="16"/> </a></td>
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
                          <td align="center"><a title="<?php echo $row_cnLoc->cn_name; ?>" style="cursor:pointer;"
                                             onclick="setCountryLocation(<?php echo $row_cnLoc->cn_id ?>);"> <img
                                             src="images/country_flag/<?php echo get_country_flag($row_cnLoc->cn_id); ?>"
                                             alt="<?php echo $row_cnLoc->cn_name; ?>" class="w4" align="top"
                                             height="15" width="20"/> </a></td>
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
                 <a href="#"><img src="images/page-header-col1-row1-col2-logo.png" class="hd_logo" alt="" /></a>
                 <p>Arabs Home &amp; Global Trade</p>
                </div><!-- page-header-col1-row1-col2 close// -->

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


                
                <div class="page-header-col1-row1-col4"><!-- page-header-col1-row1-col4 start -->
                 <div class="page-header-col1-row1-col4-row1">
                     <h2>Business Alerts</h2>
                        <p>Get timely updates in your inbox for favorite products & services</p>
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
                         <label class="radio" style="">
                                <input id="radio1" type="radio" name="radios" checked>
                                <span class="outer"><span class="inner"></span></span>Buyer
                            </label>
                            <label class="radio" style="margin-left:3px;">
                               <input id="radio1" type="radio" name="radios" checked>
                                <span class="outer"><span class="inner"></span></span>Supplier
                            </label>
                        </div>
                        <div class="page-header-col1-row1-col4-row2-link"><a href="#">Subscribe Now</a></div>
                        <div class="page-header-col1-row2-col4"><!-- page-header-col1-row2-col4 start -->
                 <h1 class="justclick">Just a click away</h1>
                 </div>
                    </div>
                </div><!-- page-header-col1-row1-col4 close// -->
            </div><!-- col-md-9 close// -->
            <div class="clear"></div>
            <div class="page-header-col1-row2"><!-- page-header-col1-row2 start -->
             <div class="page-header-col1-row2-col1 item">
                 <h1><a href="#">List Your Products</a></h1>
                    <p>Get <span>Home & Global</span> Enquiries </p>
                </div>
                <div class="page-header-col1-row2-col2"><!-- page-header-col1-row2-col2 start -->

                 <div class="page-header-col1-row2-col2-head">
                 
                 <!--
                 <h2>Source anything for your Business Instantly</h2>
                 -->
                 
                 
          
            <h2 class="cd-headline clip is-full-width text-center"> <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper" > <b class="is-hidden">Find anything for your business instantly<span class="blinking-cursor" style="color: gray">!</span></b> <b class="is-hidden">Source >> Supply >> Grow  your Business <span class="blinking-cursor" style="color: gray">!</span> </b> <b class="is-visible">Create your Domestic &amp; Global website <span class="blinking-cursor" style="color: gray">!</span></b> </span> </h2>
          
                 </div>
                 <script>
                 $(document).ready(function(){
           
           $('.searchTabs').click(function(){       
             var TabVal = $(this).attr('alt'); //alert(TabVal);
            var optionValue  = $(this).attr('alt');
            $('#rctyp option').removeAttr('selected');
            
            $('#rctyp option[value='+optionValue+']').attr('selected', 'selected');
            
            var PlaceholdVAl = "";
            
            if( TabVal == 'Products' ){
              
              PlaceholdVAl = "Source Products / Services to find suppliers";
            }else if( TabVal == 'Suppliers' ){
              
              PlaceholdVAl = "Find Suppliers for your business";
            }else if( TabVal == 'buy_lead' ){
              
              PlaceholdVAl = "Find Buy Requests for your business";
            }else if( TabVal == 'tender' ){
              
              PlaceholdVAl = "Find Tenders/ Auctions for your business";
            }
            $("#search-box1").attr("placeholder", PlaceholdVAl);
            
           });
           
  $("#search-box1").keyup(function(){ 
    var getDrpDwnVal = $("ul.search_tab li.active").text();    
    if(getDrpDwnVal == 'Suppliers'){
      var fileName = "readsuppliers.php";
    }
    else if(getDrpDwnVal == 'Products'){
      var fileName = "readproducts.php";      
    }
    else if(getDrpDwnVal == 'Buy Leads'){
      var fileName = "read_leads.php";
      }
    else{
      var fileName = "read_tenders.php";
      }   
     //alert(getDrpDwnVal+' '+fileName);return false;
    $.ajax({
    type: "POST",
    url: fileName,
    data:'keyword='+$(this).val(),
    beforeSend: function(){
      $(".search-box").css("background","#FFF url(377.gif) no-repeat 165px");
    },
    success: function(data){  //alert(data);return false;
      $("#suggesstionBoxs").show();
      $("#suggesstionBoxs").html(data);
      $("#search-box1").css("background","#FFF");
    }
    });
  });
});
function selectCountry(val) {
  //alert(val); return false; 
$("#search-box1").val(val);
$("#suggesstionBoxs").hide();
}
               </script>
                 
                    <div class="page-header-col1-row2-col2-form">


        <ul class="nav nav-tabs search_tab" role="tablist" id="rctyp">
         <li role="presentation" class="active"><a href="#products" alt="Products" class="searchTabs" aria-controls="products" role="tab" data-toggle="tab">Products</a></li>
        <li role="presentation"><a href="#supplier" alt="Suppliers" class="searchTabs" aria-controls="supplier" role="tab" data-toggle="tab">Suppliers</a></li>
        <li role="presentation"><a href="#leads" alt="buy_lead" class="searchTabs" aria-controls="leads" role="tab" data-toggle="tab">Buy Leads</a></li>
        <li role="presentation"><a href="#tender" alt="tender" class="searchTabs" aria-controls="tender" role="tab" data-toggle="tab">Tenders</a></li>
        </ul>

        <!-- Tab panes -->   
        <div class="tab-content search_cont">
        <div role="tabpanel" class="tab-pane active" id="supplier">
        <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
            <select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
            <option value="Suppliers">Suppliers fdf df</option>
            <option  value="Products" selected>Products</option>
            <option value="buy_lead">Buy Leads</option>
            <option value="tender">Tender</option>
            <!--<option value="auction">Auction</option>-->
                    </select>
            <input type="text" id="search-box1" name="keywords" style="text-align:left; border:1px solid;" placeholder="Source Product / Services to find suppliers"
                        class="page-header-col1-row2-col2-form-input topsearch_placeholder_cont search-box"  onfocus="gotFocus();" onblur="lostFocus()" value="<?php echo $_GET['keywords']; ?>"
                       style="border: 1px solid #000;width:90%" />
              <div id="suggesstionBoxs" class="suggesstionBoxs"></div>
              <input type="submit" id="btnSearch" value="" class="page-header-col1-row2-col2-form-btn"/>
          </form>
        </div>
        
        </div>
                    <div class="clear"></div>
                    </div>
                <!-- <div class="page-header-col1-row2-col2-links">
                     <p>Top Seaches : <a href="#">LED Light, men Tshirt,Refigrators etc.</a> 
                        <span><a href="#">Advanced Search</a></span>                        </p>
                    </div>-->
                </div><!-- page-header-col1-row2-col2 close// -->
                <div class="page-header-col1-row2-col3">
                 <!--<p>or</p>-->
                </div>
                <div class="page-header-col1-row2-col4"><!-- page-header-col1-row2-col4 start -->
                
                    <a href="post-buy-req.php" class="page-header-col1-row2-col4-btn item">
                      <p>Post Buy Requirements</p>

                       <h6 style="color: black; font-size: 10px;">Get <span>Quotes</span> from <span>Verified</span> Suppliers</h6>
                                            </a>                </div><!-- page-header-col1-row2-col4 close// -->
            <div class="clear"></div>
            </div>
        <!-- page-header-col1-row2 close// -->
        </div><!-- page-header-col1 close// -->
        <div class="page-header-col2">
         <div class="page-header-col2-head">
             <i class="fa fa-mobile"></i>
             <span>Android - Apple - Windows - Blackberry</span>
            </div>
            <div class="page-header-col2-intro">
             <div class="page-header-col2-intro-pic"><img src="images/page-header-col2-intro-pic.jpg" alt="" /></div>
                <div class="page-header-col2-intro-texts item">
                 <h2><a href="#">Post Business Services</a></h2>
                    <p>Get <span>Domestic</span> or <span>Global</span> Enquiries</p>
                </div>
            </div>
            <div class="clear"></div>
        </div>
    <div class="clear"></div>
    </header>
    <!-- page-header close // -->
    
  </div>
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