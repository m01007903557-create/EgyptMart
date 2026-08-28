<script type="text/javascript">
   function showmymenu() {
       $("#mn1").show();
   }
   function hidemymenu() {
       $("#mn1").hide();
   }
   function showLocMenu() {
       $("#arabyos-changeLocation").show();
   }
   function hideLocMenu() {
       $("#arabyos-changeLocation").hide();
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
               // $("#arabyos-cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
               location.reload();
           }
       });
   }
   function unsetCountryLocation() {
       $.post("unsetCountryLocation.php", function (data) {
           // $("#arabyos-cnlocation").html('<img src="images/country_flag/'+data+'" alt="" class="w4" align="top" height="15" width="20"/>');
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
  <div class="row arabyos-top-bar" id="topbar">
  <div class="arabyos-maincontainer">
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
               aria-haspopup="true" aria-expanded="false" > <b class="arabyos-txt-yellow" style="font-weight:900;">M<span class="s-small">Y</span> <?php echo getWebSiteName(); ?></b> <i class="fa fa-chevron-down"></i> </a><span class="linebr" style="color: black"> |</span> <a href="my-enquiries.php"> <img width="25" src="images/envolap.png"/>
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
    
    <ul class="arabyos-text-left" id="top-center">
      <?php if(getUserInfo($uid, 'usr_mp_id') < 4){ ?>
       <!-- <li style="color: orange; padding-left:3px;" > Credit : <a href="#" class="txt-bold arabyos-txt-yellow" style="font-weight:900 ; font-size:13px; color: orange"> <b style="color: white"><?php echo (getUserInfo($uid, 'usr_credit') > 0)?getUserInfo($uid, 'usr_credit'):'0'; ?></b></a> </li>
        <li><a href="subscription.php" style="margin:0px; padding:0px;">| &nbsp;Buy Credit</a></li>-->
        <?php }   ?>
        <?php if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') { ?>
    <li><a href="company/index.php?c=<?php echo $cid; ?>" class="arabyos-txt-yellow" style=" color:#fff450;font-weight:700;">My Website</a></li>
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

    <ul class="arabyos-text-right arabyos-tstleft" id="arabyos-top-right">
      <li><a href="#" style="margin-right:22px;" style="">Help Line : <b style="font-weight:900;"> <span class="arabyos-txt-yellow">+</span>20-1220974444</b></a> </li>
      <li> <a href="#" class=" arabyos-txt-yellow" style="margin-right:22px;"><b class="arabyos-txt-yellow" style="font-weight:900;">Why ARABYOS</b></a> </li>
      <li style="padding-right:3px;"> <a href="#">Help</a> </li>
    </ul>
    </div>
  </div>
<!-- End of topbar // -->
<!-- Start of top_bg_navigation -->
<div id="menu">
<ul  class="arabyos-tstleft">
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
    <li class="displayFlex" style="display: inline-flex;"><a href="<?php echo BASE_URL ?>my-contactdetails.php" ><b>Welcome:<?php echo getUserInfo($uid, 'name_prefix') . "&nbsp;" . getUserInfo($uid, 'fname');?></b></a>
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
   <li class="displayFlex" style="display: inline-flex;"><a href="sign-in.php" target="_top" rel="nofollow"><b>Sign in</b></a><a href="create_account.php" target="_top" rel="nofollow"><b> | Join Free</b></a></li>
   <?php }?>
   <li class="active">
     <a id="message" class="ar-lebel" aria-expanded="false" aria-haspopup="true" role="button" data-toggle="dropdown" href="#" data-target="#">
     <a href="my-enquiries.php"><img width="25" src="images/envolap.png" alt="" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>My Arabyos</b>
          <?php
            if(isset($_SESSION['uid_indm'])){
            $query_pag_num = "SELECT count(*) AS count from message,user where msg_to='".$_SESSION['uid_indm']."' and msg_from=usr_id and msg_to_status='1'"; // Total records
            $result_pag_num = mysqli_query($con, $query_pag_num);
            $row = mysqli_fetch_array( $result_pag_num);
            $count = $row['count'];
            echo '<span class="label label-yellow"  style="margin-right: 83%;">'.$count.'</span>';
          }
          else{
            echo '<span class="label label-yellow" style="margin-right: 83%;">0</span>';
          }
            ?>
          </a>
     </a> 
      <ul class="menu-sub">
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
   <li><a href="#">Help Line: <b><ins>+</ins>2012209704444</a></b></li>
   <li class="active"><a href="#" style="border-right:0;"><b>Why Arabyos</b></a></li> 
   <li><a href="#">Help</a></li>
</ul>
</div>
<!-- menu close // -->




<div class="container-fluid">
<!-- page-header start -->
<header class="my-header">
  <div class="row">
  <!-- column 1 -->
  <div class="col-md-3 col-sm-2">
    <div class="row"><div class="col-md-12 col-sm-12"><img alt="fdfdf" src="images/page-header-col1_mapbg.jpg" class="globeimg1"></div></div>
    <div class="row">
      <div class="col-md-12 col-sm-12 text-center product-select">
         <a href="product-sel-cat.php" class="arabyos-post-product-btn">
         Post Your Products<br/><small>Get <strong>Domestic</strong> or <strong>Global</strong> Enquiries</small> 
         </a>  
      </div>
    </div>
  </div>

  <!-- column 2 -->
  <div class="col-md-5 col-sm-5 second_column">
    <div class="row">
    <div class="col-md-6 col-sm-6 account_set text-center">Account Setting</div>
    <div class="col-md-6 col-sm-6 text-center">
        <div id="google_translate_element"></div>
        <script type="text/javascript">
          function googleTranslateElementInit() {
              new google.translate.TranslateElement({
                  pageLanguage: 'en',
                  layout: google.translate.TranslateElement.InlineLayout.SIMPLE
              }, 'google_translate_element');
          }
        </script> 
        <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
        <p class="cb"></p>
      </div>
    </div>
    <div class="row second_row">
      <div class="col-md-3 col-sm-3" id="arabyos-cnlocation">
        <?php
          //secho "<pre>";print_r($_COOKIE);"</pre>";
                                  if (isset($_COOKIE['loc_id'])) {
          //echo "<pre>";print_r($_COOKIE);"</pre>";
          //echo get_country_flag($_COOKIE['loc_id']);
        ?>
        <span><?php echo get_country_name($_COOKIE['loc_id']); ?></span>&nbsp; <img src="images/country_flag/<?php echo get_country_flag($_COOKIE['loc_id']); ?>"  alt="<?php echo get_country_name($_COOKIE['loc_id']); ?>" class="w4" align="top" height="16" width="23" title="<?php echo get_country_name($_COOKIE['loc_id']); ?>"/>
        <?php } else { ?>
        <span style="font-weight: bold; font-size: 12px;  color: darkcyan;  font-family: Arial Black;">Global</span> &nbsp; <img src="images/country_flag/Global$download.png" alt="Global" class="w4" align="top" height="30" width="30"/>
        <?php } ?>
        <div class="arabyos-page-header-col1-row1-col1-row2-form">
              <div onmouseover = "showLocMenu();" onmouseout = "hideLocMenu()"> <a class="un" style="border-left:none; font-size: 9px; color:#0f2399; 
              "> <span style="color: black;">Change</span> Country 
                <!--  <i class="fa fa-chevron-down"></i>--> 
                &nbsp;<span class="arw"><b>&or;</b></span> </a>
                <div class="sub_menu" style="display:none; left:0; top:105px; right:0;" id="arabyos-changeLocation">
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
      
      <div class="col-md-3 col-sm-3 logo_style">
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
          <a href="index.php"><img src="<?php echo $toplogo2show;?>" alt=""  class="logoa" style="max-width: 190px;" /></a> 
          <!--<p>Arabs Home &amp; Global Trade</p>--> 
        </div>
    </div>

    <div class="row third_row">
      <div class="col-md-12 col-sm-12 search_content">
        <div class="arabyos-srchBx">
            <h2 class="cd-headline clip is-full-width text-center"> <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper" > <b class="is-hidden">Find anything for your business instantly<span class="blinking-cursor" style="color: gray">!</span></b> <b class="is-hidden">Source >> Supply >> Grow  your Business <span class="blinking-cursor" style="color: gray">!</span> </b> <b class="is-visible">Create your Domestic &amp; Global website <span class="blinking-cursor" style="color: gray">!</span></b> </span> </h2>
          </div>
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
     <div class="search_tabbing">
      <ul class="nav nav-tabs search_tab" role="tablist" id="rctyp">
      <li role="presentation" class="active"><a href="#products" alt="Products" class="searchTabs" aria-controls="products" role="tab" data-toggle="tab">Products</a></li>
      <li role="presentation"><a href="#supplier" alt="Suppliers" class="searchTabs" aria-controls="supplier" role="tab" data-toggle="tab">Suppliers</a></li>
      <li role="presentation"><a href="#leads" alt="buy_lead" class="searchTabs" aria-controls="leads" role="tab" data-toggle="tab">Buy Leads</a></li>
      <li role="presentation"><a href="#tender" alt="tender" class="searchTabs" aria-controls="tender" role="tab" data-toggle="tab">Tenders</a></li>
      </ul>  
     </div>

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
               
    </div>

  </div>

  <!-- column 3 -->
    <div class="col-md-2 col-sm-2">
    <div class="row">
      <div class="col-md-12 col-sm-12 arabyos-home-ba text-center">
        <h3 style="font-size:20px;display: inline-flex;"><img class="img-responsive " src="images/bell.png" width="18px" style="height: 20px;">&nbsp;Business Alerts</h3>
        <p>Get timely updates in your inbox for <br> favorite products & services</p>
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
      <div class="arabyos-page-header-col1-row1-col4-row2">
        <div class="arabyos-page-header-col1-row1-col4-row2-checkbox">
          <label class="radio">
            <input id="radio1" type="radio" name="radios" value="manage-selloffer-alert.php">
            <span class="outer"><span class="inner"></span></span><a href="#" style="color: black">Buyer</a> </label>
          <label class="radio" style="margin-left:0px; font-size: 17px">
            <input id="radio2" type="radio" name="radios" value="manage-buylead-alert.php" checked>
            <span class="outer"><span class="inner"></span></span><a href="#" style="color: black">Supplier</a> </label>
        </div>
        <div class="arabyos-page-header-col1-row1-col4-row2-link"><a id="sub" onclick="return sub();" href="#">Subscribe Now</a></div>
        <h1 class="arabyos-justclick">Just a click away</h1>
      </div>
      </div>
      <div class="row arabyos-post_request-btn">
        <div class="col-md-12 col-sm-12"> 
          <!-- page-header-col1-row2-col4 start -->
         
          <a href="post-buy-req.php" class="arabyos-post-buy-req-btn">
         Post Buy Requirements<br/><small>Get <strong>Quotes</strong> from <strong>Verified Suppliers</strong></small>          
         </a> 
          
        </div>
      </div>
    </div>
    <!-- column 4 -->
    <div class="col-md-3 col-sm-3">
    <div class="row">
      <div class="col-md-12 col-sm-12 text-center android">
         <i class="fa fa-mobile"></i> <span>Android - Apple - Windows - Blackberry</span> 
      </div>
       <div class="col-md-12 col-sm-12 text-center android-img">
          <img src="images/page-header-col2-intro-pic.jpg" alt=""/>
        </div>
    </div>
    <div class="row">
      <div class="col-md-12 col-sm-12 text-center post-btn2">
         <a href="product-sel-cat.php" class="arabyos-post-product-btn">
         Post Business Services<br/><small>Get <strong>Domestic</strong> or <strong>Global</strong> Enquiries</small> 
         </a>  
      </div>
    </div>
      
    </div>

</div>

</header>
<!-- page-header close // -->
</div>
<style type="text/css">

.container-fluid {
  padding-right: 0px; 
  padding-left: 0px; 
}
.second_column{
  margin-left: -15px;
}
/* top bar css */
.arabyos-top-bar {
    background-color: #237abf;
    line-height: 43px;
    color: #fff;
    position: relative;
    z-index: 10;
    margin: auto;
    box-shadow: 0 5px 2px rgba(102, 102, 102, .5);
}
.arabyos-maincontainer {
    margin: 0 auto;
    clear: both;
}
.arabyos-text-left {
    text-align: right!important;
    margin: 0 0 0 -14% !important;
}
.arabyos-text-right {
    text-align: right;
    margin-left: 11%;
}
.arabyos-top-bar ul {
    list-style: none;
    margin-bottom: 0px;
    float: left;
    width: 32%;
}
.arabyos-top-bar ul li {
    display: inline-block;
    margin-left: 15px;
    /*margin-right: 15px;*/
}
.dropdown-content1 a, .page-header-col1-row2-col1 h1 a, .arabyos-top-bar ul li a, a, a:hover, body a:hover, li a .dropbtn1 {
    text-decoration: none;
}.dropdown-content1 a, .page-header-col1-row2-col1 h1 a, .arabyos-top-bar ul li a, a, a:hover, body a:hover, li a .dropbtn1 {
  text-decoration:none
}
.ar-dropdown-menu {
  color:#333;
  padding:0;
  margin:0;
  border-radius:0
}
.arabyos-top-bar .ar-dropdown-menu li {
  padding:0;
  display:block;
  margin:6px 0;
  position:relative
}
.arabyos-top-bar .ar-dropdown-menu li a {
  color:#00F;
  display:block;
  padding:2px 8px;
  margin:0;
  font-size:12px;
  text-align:left
}
.arabyos-top-bar .ar-dropdown-menu li a:hover {
  color:#fff;
  background-color:#308fda
}
li a .dropbtn1 {
  color:#fff;
  text-align:center;
  padding:14px 16px
}
li:hover.dropdown1 {
  background-color:#fff;
/*  border:1px solid #000;*/
  border-top-width:0;
  border-bottom-width:0
}
li:hover.dropdown1>a {
  color:#000;
  font-weight:700
}
li.dropdown1>a>b {
  color:#fff450
}
li:hover.dropdown1>a>b {
  color:#000
}
.dropdown-content1 {
  display:none;
  position:absolute;
  background-color:#f9f9f9;
  min-width:175px;
  box-shadow:0 8px 16px 0 rgba(0, 0, 0, .2);
  margin-left:-1px!important;
  border:1px solid #000;
  border-top-width:0
}
.dropdown-content1 a {
  color:#000;
  padding:12px 16px;
  display:block;
  text-align:left
}
.dropdown-content1 a:hover {
  background-color:#000
}
.dropdown1:hover .dropdown-content1 {
  display:block
}
.arabyos-tstleft li > a {
    color: #da4b20 !important;
}
.arabyos-tstleft li > a > b {
    color: #fff !important;
}
.menu-sub li > a {
 color: #fff !important; 
}
.displayFlex{display: inline-flex;}

.arabyos-top-bar ul li a {
    color: #fff;
    text-decoration: none;
    margin: 0 4px;
    font-size: 12px;
    font-weight: 100;
    outline: none;
}
#menu {
    width: 100%;
    background: #237abf;
    width: 100%;
    font-family: Arial, Helvetica, sans-serif, Tahoma;
    box-shadow: 0 5px 2px rgba(102, 102, 102, 0.2);
    z-index: 999999999;
    display: none;
}

/* end of top bar css*/

/*

@charset "utf-8";
/* CSS Document */

#homeicon{font-size:25px;}
.menupad{padding:13px 20px 11px 20px !important;}

#menu{ width:100%; background:#237abf; width: 100%;font-family: Arial, Helvetica, sans-serif, Tahoma;box-shadow: 0 5px 2px rgba(102, 102, 102, 0.2); z-index:999999999; display:none;}

#menu > ul > li > a b{ padding:0px; margin:0px}

#menu ul,
#menu ul li,
#menu ul li a,
#menu #menu-button {
 margin:0px;
  padding: 0;
  border: 0;
  list-style: none;
  line-height: 1;
  display: block;
  position: relative;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}
#menu:after,
#menu > ul:after {
  content: ".";
  display: block;
  clear: both;
  visibility: hidden;
  line-height: 0;
  height: 0;
}
#menu #menu-button {
  display: none;
}

#menu > ul > li {
  float: left;
}
#menu.align-center > ul {
  font-size: 0;
  text-align: center;
}
#menu.align-center > ul > li {
  display: inline-block;
  float: none;
}
#menu.align-center ul ul {
  text-align: left;
}
#menu.align-right > ul > li {
  float: right;
}
#menu.align-right ul ul {
  text-align: right;
}
#menu > ul > li > a {
  padding: 8px 20px 8px 20px;
  font-size: 14px;font-family: Arial, Helvetica, sans-serif, Tahoma;
  text-decoration: none;
  text-transform: none;
  color: #fff; box-shadow:0;
  -webkit-transition: color .2s ease;
  -moz-transition: color .2s ease;
  -ms-transition: color .2s ease;
  -o-transition: color .2s ease;
  transition: color .2s ease;
}
#menu > ul > li:hover > a,
#menu > ul > li.active > a {color: #fff450; }
#menu > ul > li > a span{ color:#fff450; padding: 0 20px 0 5px}
#menu > ul > li > a ins{ color:#fff450; padding: 0 5px 0 0px; font-weight:bold; text-decoration:none;}
#menu > ul > li > a i{ color:#f4fafa; padding:0 5px 0 0px; margin:0px;}
#menu > ul > li > a i:hover{ color:#fff450;}

/*
.ar-lebel {position: relative;}
.ar-lebel img{ margin:-2px 5px 0 0px;}
*/

#menu > ul > li.has-sub > a {
  padding-right: 30px;
}
#menu > ul > li.has-sub > a::after {
  position: absolute;
  top: 12px;
  right: 15px;
  width: 6px;
  height: 6px;box-shadow: 0 5px 2px rgba(102, 102, 102, 0.2); z-index:99;
  border-bottom: 1px solid #ffffff;
  border-right: 1px solid #ffffff;
  content: "";
  -webkit-transform: rotate(45deg);
  -moz-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  -o-transform: rotate(45deg);
  transform: rotate(45deg);
  -webkit-transition: border-color 0.2s ease;
  -moz-transition: border-color 0.2s ease;
  -ms-transition: border-color 0.2s ease;
  -o-transition: border-color 0.2s ease;
  transition: border-color 0.2s ease;
}
#menu > ul > li.has-sub:hover > a::after {
  border-color: #fff450;
}

#menuarrow > a::after {
  position: absolute;
  top: 24px;
  left: 10px;
  width: 4px; 
  height: 4px;
  border-bottom: 1px solid #ffffff;
  border-right: 1px solid #ffffff;
  content: "";
  -webkit-transform: rotate(-223deg) !important;
  -moz-transform: rotate(-223deg) !important;
  -ms-transform: rotate(-223deg) !important;
  -o-transform: rotate(-223deg) !important;
  transform: rotate(-223deg) !important;
  -webkit-transition: border-color 0.2s ease;
  -moz-transition: border-color 0.2s ease;
  -ms-transition: border-color 0.2s ease;
  -o-transition: border-color 0.2s ease;
  transition: border-color 0.2s ease;
}

#menu ul ul {
  position: absolute;
  left: -9999px;z-index:999;
}

#menuarrow > a
{text-align:right;}

#menu li:hover > ul {
  left: auto;
}
#menu.align-right li:hover > ul {
  right: 0;
}
#menu ul ul ul {
  margin-left: 100%;
  top: 0;
  z-index:9999;
}
.leftmenu {
  margin-left: -100% !important;
  top: 0;
}
#menu.align-right ul ul ul {
  margin-left: 0;
  margin-right: 100%;
}
#menu ul ul li {
  height: 0;
  -webkit-transition: height .2s ease;
  -moz-transition: height .2s ease;
  -ms-transition: height .2s ease;
  -o-transition: height .2s ease;
  transition: height .2s ease;
}
#menu ul li:hover > ul > li {
  height: 32px;
}
#menu ul ul li a {
  padding: 15px 20px;
  width: 160px; 
  font-size: 15px;
  text-decoration: none;
  color: #444; border:#ddd 1px solid;border-left:0;border-right:0;border-bottom:0;
  background:#fff; 
  -webkit-transition:.5s;
  -moz-transition:.5s;
  -ms-transition:.5s;
  -o-transition:.5s;
  transition:.5s;
 
}
#menu ul ul li:hover > a,
#menu ul ul li a:hover {
  color: #237abf;

}
#menu ul ul li.has-sub > a::after {
  position: absolute;
  top: 13px;
  right: 10px;
  width: 4px;
  height: 4px;
  border-bottom: 1px solid #6f7071;
  border-right: 1px solid #6f7071;
  content: "";
  -webkit-transform: rotate(-45deg);
  -moz-transform: rotate(-45deg);
  -ms-transform: rotate(-45deg);
  -o-transform: rotate(-45deg);
  transform: rotate(-45deg);
  -webkit-transition: border-color 0.2s ease;
  -moz-transition: border-color 0.2s ease;
  -ms-transition: border-color 0.2s ease;
  -o-transition: border-color 0.2s ease;
  transition: border-color 0.2s ease;
}
#menu.align-right ul ul li.has-sub > a::after {
  right: auto;
  left: 10px;
  border-bottom: 0;
  border-right: 0;
  border-top: 1px solid #dddddd;
  border-left: 1px solid #dddddd;
}
#menu ul ul li.has-sub:hover > a::after {
  border-color: #ee2d24;
}
@media all and (max-width: 1149px), only screen and (-webkit-min-device-pixel-ratio: 2) and (max-width: 1024px), only screen and (min--moz-device-pixel-ratio: 2) and (max-width: 1024px), only screen and (-o-min-device-pixel-ratio: 2/1) and (max-width: 1024px), only screen and (min-device-pixel-ratio: 2) and (max-width: 1024px), only screen and (min-resolution: 192dpi) and (max-width: 1024px), only screen and (min-resolution: 2dppx) and (max-width: 1024px) {
  #menu {
    width: 100%;display: block;
  }
  #menu ul {
    width: 100%;
    display: none;
  }
  #menu.align-center > ul,
  #menu.align-right ul ul {
    text-align: left;
  }
  #menu ul li,
  #menu ul ul li,
  #menu ul li:hover > ul > li {
    width: 100%;
    height: auto;
    border-top: 1px solid rgba(120, 120, 120, 0.15);
  }
  #menu ul li a,
  #menu ul ul li a {
    width: 100%;
  }
  #menu > ul > li,
  #menu.align-center > ul > li,
  #menu.align-right > ul > li {
    float: none;
    display: block;
  }
  #menu ul ul li a {
    padding: 20px 20px 20px 20px;
    font-size: 15px;
    color: #fff; 
    background: none;
  }
  #menu ul ul li:hover > a,
  #menu ul ul li a:hover {
    color: #fff450;
  }
  #menu ul ul ul li a {
    padding-left: 40px;
  }
  #menu ul ul,
  #menu ul ul ul {
    position: relative;
    left: 0;
    right: auto;
    width: 100%;
    margin: 0;
  }
  #menu > ul > li.has-sub > a::after,
  #menu ul ul li.has-sub > a::after {
    display: none;
  }
  #menu-line {
    display: none;
  }
  #menu #menu-button {
    display: block;
    padding: 15px;
    color: #fff;
  font-family: 'Roboto Condensed', sans-serif;
    cursor: pointer;
    font-size: 17px; font-weight:bold;
    text-transform: uppercase;
  }
  #menu #menu-button::after {
    content: '';
    position: absolute;
    top: 25px;
    right: 20px;
    display: block;
    width: 15px;
    height: 2px;
    background: #fff;
  }
  #menu #menu-button::before {
    content: '';
    position: absolute;
    top: 15px;
    right: 20px;
    display: block;
    width: 15px;
    height: 7px;
    border-top: 2px solid #fff;
    border-bottom: 2px solid #fff;
  }
  #menu .submenu-button {
    position: absolute;
    z-index: 10;
    right: 0;
    top: -6px;
    display: block;
    border-left: 1px solid rgba(120, 120, 120, 0.15);
    height: 50px;
    width: 50px;
    cursor: pointer;
  }
  #menu .submenu-button::after {
    content: '';
    position: absolute;
    top: 15px;
    left: 26px;
    display: block;
    width: 1px;
    height: 11px;
    background: #fff;
    z-index: 99;
  }
  #menu .submenu-button::before {
    content: '';
    position: absolute;
    left: 21px;
    top: 20px;
    display: block;
    width: 11px;
    height: 1px;
    background: #fff;
    z-index: 99;
  }
  #menu .submenu-button.submenu-opened:after {
    display: none;
  }
  #menu ul ul ul {
  margin-left:0 !important;
  top: 0;
}

#menuarrow > a
{text-align:left;}

}


.my-header {
    border-top: none;
    border-radius: 0 0 0px 0px;
    -moz-border-bottom-colors: none;
    -moz-border-left-colors: none;
    -moz-border-right-colors: none;
    -moz-border-top-colors: none;
    background-color: #fff;
    position: relative;
}

.my-header {
    border: 1px solid #d2daf3;
    box-shadow: 0 0 3px #1366b1!important;
}
  a{
    outline: none;
    color: #646464;
    text-decoration: none!important;
  }
  .globeimg1{
    height: 165px;
    max-width: inherit;
    width: 290px;
    margin-top: -13px;
  }
  .container-fluid .row{
    /*background-color: #fff;*/
    padding: 10px;
  }
  .container-fluid .row .arabyos-post-product-btn{
    /*text-align: left;*/
    font-size: 17px;
    font-weight: 900;
    color: #d14233;
    display: block;
    border-radius: 4px;
    padding: 0;
    margin-top: 35px;
    transform: scale(1);
    position: relative;
    bottom: 20px;
  }
  .container-fluid .row .arabyos-post-product-btn:hover{
    color: red;
transform: scale(1.1);
  }
  #arabyos-cnlocation{
    margin-top:60px;
  }
#arabyos-cnlocation>span{
    font-size: 21px;
    color: #2a3e94 !important;
    font-family: arial;
    font-weight: 700;
}
  small{
    color: black;
  }
  .sub_menu{
    position: absolute;
    /* top: 69px; */
    /* right: -1px; */
    display: none;
    border: 1px solid #a7a7a7;
    border-radius: 0;
    background: #fff;
    border-top: 0;
    /* left: 132px; */
    z-index: 999;
    list-style: none
    width: 185px;
  }
  #arabyos-changeLocation {
      top: 50px !important;
  }
  ul{
    list-style: none;
    padding: 0;
    margin: 0;
  }
  .account_set{
    font-weight: 700;
    font-size: 10px;
  }
  .arabyos-home-ba h3 {
    font-size: 17px !important;
    font-weight: 900;
    color: #C45231 !important;
    /*margin-left: 30px;*/
}
.arabyos-home-ba p{
    font-size: 10px !important;
    width: width;
    font-weight: 700;
    text-align: center;
    margin-left: 0 !important;
    letter-spacing: -0.3px;

}
.arabyos-page-header-col1-row1-col4-row2-checkbox .radio {
    display: inline-block;
    margin: 0;
    cursor: pointer;
}

.arabyos-page-header-col1-row1-col4-row2-checkbox .radio .outer {
    width: 16px;
    height: 16px;
    display: block;
    float: left;
    margin: 4px 5px 10px 0;
    border: 1.4pt solid #2a2a2a;
    border-radius: 50%;
    background-color: #fff;
}
.arabyos-page-header-col1-row1-col4-row2-checkbox .radio .inner {
    -webkit-transition: all .25s ease-in-out;
    transition: all .25s ease-in-out;
    width: 6px;
    height: 6px;
    -webkit-transform: scale(0);
    -ms-transform: scale(0);
    transform: scale(0);
    display: block;
    margin: 4px;
    border-radius: 50%;
    background-color: #f05323;
    opacity: 0;
}
.arabyos-page-header-col1-row1-col4-row2-checkbox .radio input {
    width: 1px;
    height: 1px;
    opacity: 0;
}
.arabyos-page-header-col1-row1-col4-row2-checkbox .radio input:checked+.outer .inner {
    -webkit-transform: scale(1);
    -ms-transform: scale(1);
    transform: scale(1);
    opacity: 1;
}
.arabyos-page-header-col1-row1-col4-row2-link a {
    font-weight: 700;
    font-size: 15px;
    color: #da4b20;
}
.headertop-custom-box-middle h1.arabyos-justclick {
    font-size: 16px;
    text-align: center;
}

.arabyos-justclick {
    font-size: 14px;
    font-weight: 900;
    color: #7e7e7e;
    word-spacing: 0;
    letter-spacing: 0;
    margin-top: 8px;
}
.arabyos-page-header-col1-row1-col4-row2{
  text-align: center;
}
.page-header-col1-row2-col4 {
    float: right;
    margin-top: 0px;
    width: 21%;
    margin-left: 0px;
}
.arabyos-post-buy-req-btn {
    height: 60px;
    vertical-align: middle;
    width: 100%;
    font-weight: 700!important;
    text-align: center;
    font-size: 13px;
    color: #CD1B21 !important;
    display: block;
    border-radius: 4px;
    border: 1px solid #D98432;
    padding: 14px 0;
    /* text-shadow: 0 2px 2px rgba(255,255,255,0.50); */
    box-shadow: none !important;
    box-shadow: 0 1px 6px 1px rgba(0,0,0,0.50);
    transform: scale(1);
    text-decoration: none !important;
    background: #fcc93c;
    background: -moz-linear-gradient(top, #fcc93c 0%, #f9ae1e 100%);
    background: -webkit-linear-gradient(top, #fcc93c 0%,#f9ae1e 100%);
    background: linear-gradient(to bottom, #fcc93c 0%,#f9ae1e 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fcc93c', endColorstr='#f9ae1e',GradientType=0 );
}
.arabyos-post-buy-req-btn:hover {
    transform: scale(1.1);
}
.arabyos-post-product-btn small {
    display: block;
    color: #484646;
    font-weight: 600;
    font-size: 10px;
}
.android .fa{
    font-size: 30px;
    float: left;
}
.android span{
    font-size: 10px;
    font-weight: 700;
    color: #747476;
    float: none;
    margin: 7px 0 0 16px;
    letter-spacing: .2px;
    line-height: 30px;
}
.android-img{
  border-left: 2px solid #237abf;
}
.second_row{
   margin-top: -24px;
}
.logo_style{
  float: left;margin: 13px 68px 0;text-align: center;
}

.cd-headline.clip span,
.cd-words-wrapper,
.cd-words-wrapper b {
    display: inline-block
}
.cd-words-wrapper {
    position: relative;
    text-align: left
}
.arabyos-srchBx
 {
  text-align: center;
  margin-top: 16px;
  /*margin-left:20px;*/
}
.cd-words-wrapper b {
    position: absolute;
    white-space: nowrap;
    left: 0;
    top: 0
}
.cd-words-wrapper b.is-visible {
    position: relative
}
.cntBx,
.dwnArw,
.dwnArw p,
.top_trend {
    position: absolute
}
.no-js .cd-words-wrapper b {
    opacity: 0
}
.no-js .cd-words-wrapper b.is-visible {
    opacity: 1
}
.cd-headline.clip .cd-words-wrapper {
    overflow: hidden;
    vertical-align: top
}

.cd-headline.clip .cd-words-wrapper::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 2px;
    height: 100%;
   
}
.cd-headline.clip b {
    opacity: 0
}
.cd-headline.clip b.is-visible {
  font-size: 31px;
  opacity: 1;
}
.search_tabbing{
    width: 100%;
    padding: 7px 0 0;
    position: relative;
    margin-left: 0px;
}
.nav-tabs.search_tab {
    border-bottom: 0px solid #dddddd;
}
.nav-tabs.search_tab > li {
    margin-bottom: 0px;
}
.nav-tabs.search_tab > li.active > a, .nav-tabs.search_tab > li.active > a:hover, .nav-tabs.search_tab > li.active > a:focus {
    border-right: 0;
    border-left: 0;
    border-bottom: 0;
    border-top: 1px solid #1366b1;
    background-color: #fff;
    color: #333acc;
    cursor: default;
}
.nav-tabs.search_tab > li > a {
    border-right: 0;
    border-left: 0;
    border-bottom: 0;
    border-top: 1px solid #1366b1;
    border-radius: 0;
    border-image: none;
    color: #333acc;
    font-size: 15px;
    font-weight: 700;
    margin-right: 15px;
    padding: 0px 20px;
    position: relative;
}
.nav-tabs.search_tab > li.active a::after {
  background: url("../images/arrow_down.png");
  background-repeat: no-repeat;
  bottom: -14px;
  content: "";
  display: block;
  float: right;
  height: 14px;
  left: 44px;
  position: absolute;
  transform: rotate(0deg);
  width: 20px;
}

.search_cont select {
    display: none;
}

.page-header-col1-row2-col2-form-select {
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 22%;
    float: left;
    color: #000;
    font-size: 15px;
    padding: 18px 10px;
    cursor: pointer;
    background: url(../images/page-header-col1-row1-col1-row2-form-select-bg.png) no-repeat center right;
    border: 2px solid #006bb1;
    outline: none;
    font-weight: 700;
    border-radius: 5px;
    background-position: 88% 53%;
    box-shadow: 0 0 6px #595959;
    height: 65px;
    background-color: #e8eaeb;
}
.my-header .topsearch_placeholder_cont {
    float: left;
    width: 100%;
    height: 45px;
    line-height: 45px;
    border: 2px solid #3953a4;
    text-align: center;
}
.topsearch_placeholder_cont {
    width: 100%;
    padding: 0 8px;
    border: none;
    outline: none;
    font-size: 18px;
    font-weight: bold;
    color: #a7a7a7;
    background: #FAFAFA !important;
}
.page-header-col1-row2-col2-form-btn {
    background: rgba(0, 0, 0, 0) url(../images/page-header-col1-row2-col2-form-btn-c.png) no-repeat scroll left top / 100% auto;
    border: medium none;
    bottom: 0;
    height: 60px;
    outline: 0 none;
    position: absolute;
    right: 0;
    top: auto;
    width: 50px;
}
.page-header-col1-row2-col2-form-btn:hover {
    background: rgba(0, 0, 0, 0) url(../images/page-header-col1-row2-col2-form-btn-c-hover.png) no-repeat scroll left top / 100% auto;
    border: medium none;
    bottom: 0;
    height: 60px;
    outline: 0 none;
    position: absolute;
    right: 0;
    top: auto;
    width: 50px;
}
.third_row{
  margin-top: -20px;
}
#suggesstionBoxs #country-list {
    left: 0 !important;
    width: 100% !important;
    z-index: 999 !important;
}
#country-list, #country-list1 {
    margin: 0;
    height: 177px;
    z-index: 1;
    background-color: #fff;
    border-radius: 3px;
}
#country-list, #country-list1, #state-list {
  float:left;
  list-style:none;
  padding:0;
  overflow-y:scroll;
  border-bottom:2px solid #006bb1;
  border-left:2px solid #006bb1;
  border-right:2px solid #006bb1;
  position:absolute
}
#country-list li, #state-list li {
  border-bottom:#F0F0F0 1px solid
}
#country-list {
    margin-top: 55px!important;
}
#country-list li {
    padding: 10px;
    background: #FAFAFA;
    color: rgba(28, 28, 28, .9);
    font-weight: 600;
}
#country-list li, #state-list li {
    border-bottom: #F0F0F0 1px solid;
}
.label-yellow {
    background-color: red;
    border-radius: 50%;
    bottom: -8px;
    color: #fff !important;
    font-size: 11px;
    font-weight: 700;
    height: 25px;
    line-height: 24px;
    width: 25px !important;
    padding: 0 !important;
    position: absolute;
    right: -10px;
    font-size: 12px !important;
    text-align: center !important;
    line-height: 25px !important;
      padding: 0 4px !important;
}
.search_content{
  margin-top: -15px;
}
.arabyos-post_request-btn{
  margin-top: 50px;
}
.product-select{
  margin-top: 10px;
}
.number-count {
    display: flex;
    margin: 10px;
}
.number-count div {
    line-height: 1px;
    height: 21px;
    padding: 10px 5px;
    margin-right: 2px;
    font-size: 18px;
    font-weight: bold;
    color: #fff;
    background: -webkit-linear-gradient(top, rgba(21, 20, 20, 0.9) 12%, rgba(12, 12, 12, 0.8) 14%, rgb(123, 121, 121) 47%, rgb(86, 85, 85) 76%, rgba(37, 35, 34, 0.98) 97%, rgb(23, 21, 21) 100%);
    background: rgb(101,53,192);
    background: -moz-linear-gradient(top, rgb(101,53,192) 0%, rgb(116,81,188) 52%, rgb(116,81,188) 52%, rgb(106,68,186) 52%, rgb(101,53,192) 100%);
    background: -webkit-linear-gradient(top, rgb(101,53,192) 0%,rgb(116,81,188) 52%,rgb(116,81,188) 52%,rgb(106,68,186) 52%,rgb(101,53,192) 100%);
    background: linear-gradient(to bottom, rgb(101,53,192) 0%,rgb(116,81,188) 52%,rgb(116,81,188) 52%,rgb(106,68,186) 52%,rgb(101,53,192) 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6535c0', endColorstr='#6535c0',GradientType=0 );
    copy: ;
}




@media only screen and (max-width: 768px) {
    .globeimg1{
      width: 100%!important;
    }
    .google_translate_element{
      float: left;
    }
    #arabyos-cnlocation {
      text-align: center;
      margin-top: 0px;
    }
    .logo_style {
      text-align: center!important;
      float: none!important;
      margin: 0!important;
    }
    .cd-headline.clip b.is-visible {
      font-size: 12px!important;
      opacity: 1;
    }
    .container-fluid .row .arabyos-post-product-btn{
      margin-top: 0px;
    }
    .third_row{
      margin-top: 0px;
    }
    .topsearch_placeholder_cont{
      font-size: 13px;
    }
    .nav-tabs.search_tab > li > a {
      font-size: 10px;
      padding: 0;
    }
    .nav-tabs.search_tab > li.active a::after {
      left: 15px;
    }
    .arabyos-top-bar{
      display: none;
    }

}
@media only screen and (min-width: 768px) and (max-width: 1280px) {
  .arabyos-home-ba h3{
    font-size: 15px!important;
  }
  .arabyos-home-ba p {
    font-size: 10px !important;
  }
  .radio, .checkbox {
    padding-left: 0px!important;
  }
  .post-btn2{
    margin-top: -34px;
  }
  small {
    font-size: 63%!important;
  }
}
@media only screen and (min-width: 980px) and (max-width: 1280px) {
  .arabyos-post-buy-req-btn{
    font-size: 12px!important;
  }
}
@media only screen and (min-width: 768px) and (max-width: 980px) {
  .cd-headline.clip b.is-visible {
    font-size: 16px!important;
  }
  .arabyos-post_request-btn {
      margin-top: -20px;
  }
  .arabyos-post-buy-req-btn{
    font-size: 8px!important;
  }
  small {
    font-size: 80%!important;
  }
  .topsearch_placeholder_cont{
    font-size: 12px!important;
  }
}

@media only screen and (min-width: 1440px) and (max-width: 1920px) {
  .logo_style {
    margin: 13px 123px 0!important;
  }
}

@media only screen and (min-width: 768px) and (max-width: 1920px) {
  #menu{
    display: none!important;
  }
}
</style>