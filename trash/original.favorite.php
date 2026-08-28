<?php
ob_start();
session_start();
include 'lib/connect.php';
require_once 'common.php';

$uid = '';
if(isset($_SESSION['uid_indm'])){
 $uid=$_SESSION['uid_indm'];
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<!--<meta name="viewport" content="initial-scale=0, maximum-scale=1">-->
<meta name="viewport" content="width=1200">
<title>Index</title>
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
<link href="zomImage/js/jquery-photo-enlarger/css/jquery-photo-enlarger.css" rel="stylesheet" type="text/css">
<link href="css/slidebars.css" rel="stylesheet" type="text/css">
<link href="css/ctstyle.css" rel="stylesheet" type="text/css">
</head>

<body id="top">
<header class="container-fluid" style="position:fixed; width:100%; z-index:10;"> 
  <!-- Top Blue Bar-->
  <div class="row top-bar" id="topbar">
    <ul>
      <?php
    if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != ''){
       $ur_qu = 'select * from user where usr_id = '.$_SESSION['uid_indm'];
      $res_qu = mysql_query($ur_qu);
      $resu = mysql_fetch_array($res_qu, MYSQL_ASSOC);
      echo '<li><span>Welcome </span>'.$resu['name_prefix'].' '.$resu['fname'].'</li>';
    }else{ ?>
       <li><a href="sign-in.php" style="margin-left:18px;">Sign in</a> | <a href="create_account.php">Join Free</a></li>
    <?php } ?>
      <li>
        <label style="font-weight:900;">
          <select style="color:#FF0;" name="form" onchange="location = this.options[this.selectedIndex].value;">
           <option>My ARABYOS</option>
           <option value="my-dashboard.php">My Dashboard</option>
           <option value="my-enquiries.php">My Index</option>
            <option value="buyleads.php">Buy Leads</option>
            <option value="image-gallery.php">Image Gallary</option>
            <?php if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != ''){
              echo '<option value="favorite.php">My Favorites</option>';
              echo '<option value="logout.php">Sign Out</option>';
            } ?>
          </select>
        </label>
      </li>
      <li class="dropdown"> <a class="ar-lebel" id="message" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <img src="images/envolap.png" width="25"/> <span class="label label-yellow">4</span> </a>
        <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="message">
          <li><a href="#">alert one</a></li>
          <li><a href="#">alert two</a></li>
          <li><a href="#">alert three</a></li>
          <li><a href="#">alert four</a></li>
        </ul>
      </li>
    </ul>
    <ul class="text-right" id="top-center">
      <li> Credit : <a href="#" class="txt-bold txt-yellow" style="font-weight:900 ; font-size:13px;"><b>0</b></a> </li>
      <li > <a href="#">Buy Credit</a> </li>
      <li class="dropdown" id="buy"> <a class="ar-lebel" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <b>BUY</b> <i class="fa fa-chevron-down" style="font-size:10px;"></i> </a>
        <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="buy">
            <li><a href="post-buy-req.php">Post your Buy Requirement </a></li>
            <li><a href="manage-selloffer-alert.php">Manage Sell Offer Alert</a></li>
            <li><a href="search_adv.php">Serach Products & Suppliers</a></li>
        </ul>
      </li>
      <li class="dropdown" id="sell" style="margin-right:0px;"> <a class="ar-lebel"  data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="margin-right:0px;"> <b>SELL</b> <i class="fa fa-chevron-down" style="font-size:10px;"></i> </a>
        <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="sell">
            <li><a href="buyleads.php">Latest Buy Leads</a></li>
            <li><a href="tenders.php">Tenders</a></li>
          <li><a href="auctions.php">Auctions</a></li>
          <li><a href="create-free-website.php">Create your Free Catalog</a></li>
          <li><a href="product-sel-cat.php">Display Free Products</a></li>
          <li><a href="post-sell-offer.php">Post Sell Offers</a></li>
          <li><a href="manage-buylead-alert.php">Manage By Lead Alerts</a></li>
        </ul>
      </li>
    </ul>
    <ul class="text-right" id="top-right">
        <li > <a href="#">Help Line : <b style="font-weight:900; font-family: 'Comic Sans MS', cursive;"> +2012209704444</b></a> </li>
      <li> <a href="#" class="txt-bold txt-yellow"><b>Why ARABYOS</b></a> </li>
      <li style="margin-right:0px;"> <a href="help.php" style="margin-right:18px;">Help</a> </li>
    </ul>
  </div>
  
  <!--Mobile Top bar-->
  <div class="row top-bar" id="mobile-topbar">
    <ul>
      <li class="dropdown" style="padding-top:2px;">
       <a class="ar-lebel" data-target="signin" href="signin" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-user"></i> <i class="fa fa-chevron-down" style="font-size:10px;"></i> <a>
        <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="signin" style="width:auto;">
          <li><a href="#">Sign In</a></li>
          <li><a href="#">Join Free</a></li>
        </ul>
      </li>
      <li class="dropdown"> <a class="ar-lebel" data-target="myArabyos" href="myArabyos" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <b>My Arabyos</b> <i class="fa fa-chevron-down" style="font-size:10px;"></i> <a>
        <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="myArabyos" style="width:auto;">
          <li><a href="#">Item1</a></li>
          <li><a href="#">Item1</a></li>
        </ul>
      </li>
      <li class="dropdown"> <a class="ar-lebel" id="message" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <img src="images/envolap.png" width="25"/> <span class="label label-yellow">4</span> </a>
        <ul class="dropdown-menu ar-dropdown-menu" aria-labelledby="message">
          <li><a href="#">alert one</a></li>
          <li><a href="#">alert two</a></li>
          <li><a href="#">alert three</a></li>
          <li><a href="#">alert four</a></li>
        </ul>
      </li>
    </ul>
    <ul class="text-center">
      
    
      <li class="dropdown" id="buy"> <a class="ar-lebel" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <b>BUY</b> <i class="fa fa-chevron-down" style="font-size:10px;"></i> </a>
        <ul class="dropdown-menu dropdown-menu-right ar-dropdown-menu" aria-labelledby="buy">
          <li><a href="#">item one</a></li>
          <li><a href="#">item two</a></li>
          <li><a href="#">item four</a></li>
        </ul>
      </li>
      <li class="dropdown" id="sell" style="margin-right:0px;"> <a class="ar-lebel"  data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="margin-right:0px;"> <b>SELL</b> <i class="fa fa-chevron-down" style="font-size:10px;"></i> </a>
        <ul class="dropdown-menu dropdown-menu-right ar-dropdown-menu" aria-labelledby="sell">
          <li><a href="#">item</a></li>
          <li><a href="#">item two</a></li>
          <li><a href="#">item three</a></li>
          <li><a href="#">item four</a></li>
        </ul>
      </li>
    </ul>
    <ul class="text-right">
    	 <li class="dropdown" id="buy"> <a class="ar-lebel" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-shopping-cart"></i> <i class="fa fa-chevron-down" style="font-size:10px;"></i> </a>
        <ul class="dropdown-menu dropdown-menu-right ar-dropdown-menu" aria-labelledby="buy">
          <li><a href="#">Buy Credit</a></li>
          
        </ul>
      </li>
      <li>
      	<a href="#" class="txt-bold txt-yellow" style="font-size:13px;">0</a>
      </li>
      <li class="dropdown"> <a class="ar-lebel" id="aboutus" data-target="#" href="#" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-info-circle"></i> </a>
        <ul class="dropdown-menu ar-dropdown-menu dropdown-menu-right" aria-labelledby="aboutus" style="width:auto;">
          <li><a href="help.php">Help</a></li>
          <li><a href="#">Why Arabyos</a></li>
          <li><a href="#"><b>HelpLine: +2012209704444</b></a></li>
        </ul>
      </li>
    </ul>
  </div>
  
  <!--Main Header-->
  <div class="row">
    <div class="col-lg-12">
      <div class=" ar-header ar-header-box">
        <table class="table">
          <tr>
            <td class="ar-header-box" id="ar-header-box"><ul class="margin-bottom-0 text-center">
                <li><span class="h4 txt-bold txt-purple" style="font-size:22px;">Global <img src="images/glob.png" width="22"/> </span></li>
                <li class="" style="font-size:10px;">
                  <label>
                    <select>
                      <option selected disabled>Change Country</option>
                      <option>India</option>
                      <option>USA</option>
                    </select>
                  </label>
                </li>
                <li class="ar-logo"> <img src="images/logo.png" alt="logo" /></li>
              </ul></td>
            <td class="ar-header-box2" ><table class="table">
                <tr>
                    <td><a href="dir.php" style="padding-left:2px;">Products & Services </a> <span class="V-divider">|</span></td>
                    <td><a href="sale-offers.php">Sale Offers </a> <span class="V-divider">|</span></td>
                    <td><a href="buyleads.php">Buy Requests </a> <span class="V-divider">|</span></td>
                    <td><a href="tenders.php" style="padding-right:0px; margin-right:-10px;">Tenders</a></td>
                  <td></td>
                </tr>
                <tr>
                  <form>
                    <td colspan="4" class="ar-search"><div class="input-group input-group-lg " style="width:100%;">
                        <div class="input-group-btn">
                          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="padding:3px;">
                          <div class="inner-btn" style="padding:8px">Products <span class="caret"></span></div>
                          </button>
                          <ul class="dropdown-menu" id="products">
                              <li><a href="#">Suppliers</a>
                              </li><li><a href="#">Something else here</a></li>
                            <li><a href="#">Products</a></li>
                            <li><a href="buyleads.php">Buy Leads</a></li>
                            <li><a href="tenders.php">Tenders</a></li>
                            <li><a href="auctions.php">Auction</a></li>
                          </ul>
                        </div>
                        <!-- /btn-group -->
                        <input type="text" class="form-control" aria-label="...">
                        <span class="input-group-btn">
                        <button class="btn btn-default btn-primary btn-blue" type="button"><i class="fa fa-search" style="font-size:23.4px;"></i></button>
                        </span> </div>
                      
                      <!-- /input-group -->
                      
                      <div class="text-right margin-top-5" style="margin-top:5px;"> <a href="#" class="txt-xs" style="padding-right:0px;">Advance Search</a> </div></td>
                    <!--<td>
                            	<span class="txt-nerrow or">Or</span>
                            </td>-->
                    <td style="vertical-align:top; text-align:center;"><button class="btn btn-lg btn-yellow txt-bold  box-shadow" style="margin-top:-4px;" type="button" > <img src="images/post.png" width="219"/></button></td>
                  </form>
                </tr>
              </table></td>
            <td class="ar-header-box3"> 
            	<ul class="margin-bottom-0 text-center" style="border-left:2px solid #0CC; padding-left:4px;">
                <li class="txt-bold"><span style="padding-right:4px;">English </span>|<span style="padding-left:4px;"> العربية</span></li>
                <li > <img src="images/googletranslate.png"  style="border:1px solid #d7d7d7; border-right:0px; width:20px; padding:2px; margin-right:-3px;"/>
                  <label>
                    <select style="padding:1px 2px 2px; border:1px solid #d7d7d7; border-left:0px; font-size:10px; " class="txt-nerrow">
                      <option selected disabled>Select Language</option>
                      <option>English</option>
                      <option>Arabic</option>
                    </select>
                  </label>
                </li>
                <li class="margin-top-5"> <a type="button"  data-toggle="modal" data-target=".postRequirement" class="h4 txt-bold txt-orange modal-btn" style="cursor:pointer;"><img src="images/post-business.png" width="205"/></a> </li>
                <li> <span>Get <big class="txt-bold">Domestic</big> or <big class="txt-bold">Global</big> Enquiries</span> </li>
              </ul></td>
          </tr>
        </table>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</header

<!-- Main Container-->
<div class="container-fluid ar-container close-any" style="padding-top:230px;"> 
  <!-- Tab /Mobile Menu-->
  
  <!--<div class="col-sm-12 ar-box" style="padding-right:23px; width:100%;" id="business-alert-mobile">
          
          	 <span class="h4" style="font-size:18px; display:inline-block"><img src="images/bell.png" width="20"/> <b class="txt-orange">Business Alerts</b></span> 
          
          <b class="h5 txt-purple txt-bold margin-left-5" > Get timely updates in your inbox for</b>
          <b class="h4 txt-orange text-center margin-left-5"> "Rice"</b>
         
            <button type="submit" class="btn btn-sm btn-warning border-radius-0 margin-left-10 pull-right" style="padding:0 3px;">Confirm</button>
          
        </div>-->
        
        
  
  <div class="row"> 
  
    <div class="col-lg-12 compared-container">
     	 <header style="margin-bottom:30px; border-bottom:1px solid #000; padding-bottom:5px;">
      		<h5>My Favourite Products</h5>
     	</header>
  
 
 <div id="carousel-example-generic" class="carousel slide" data-ride="carousel">
  <!-- Indicators -->
  

  <!-- Wrapper for slides -->
  <div class="carousel-inner" role="listbox">
  <?php 
    if(isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != ''){
      $myfev = array();
          $fev_query = 'select pd_id from favorite where usr_id = '.$_SESSION['uid_indm'];
          $fev_query_result = mysql_query($fev_query);
          while( $favrow=mysql_fetch_array($fev_query_result, MYSQL_ASSOC)){
            $myfev[] = $favrow['pd_id'];
          }

      $view_product ='select * from products where pd_id IN ('.implode(",", $myfev).')';
      $run_query= mysql_query($view_product);
      $favcount = 0; $first = true;
      while( $row=mysql_fetch_array($run_query, MYSQL_ASSOC)){ 
        if($favcount == 0){ ?>
        <div class="item <?php echo ($first)?'active':''; $first = false; ?>"><?php } ?>
          <div class="col-md-3 compared-box">
                  <div class="text-right"><a href="#"><i class="fa fa-times"></i> </a></div>
                  <header style="padding:5px;">
                      <a href="#" class="h4">
                        <?php echo $row['pd_title']; ?>
                        </a>
                    </header>
                    <figure class="img-box" >
                      <div class="ribbon"><img src="images/sponsor.png"/></div>
                    <div class="zoomthis"> <img src="<?php echo '/arabyos/upload/myproduct/'.$row['pd_image']; ?>" class="zoomthis" alt="Rice"/> </div>
                    </figure>
                    <section>
                      <table>
                          <tr>
                              <td><img src="images/4.png"/></td>
                                <td><a href="#" class="h5">Egyptian International Trade Co.</a></td>
                                <td></td>
                            </tr>
                            <tr>
                              <td><img src="images/flags/Egypt.png"/></td>
                                <td><a href="#" class="h5">Egypt-Gharbia-Tanta</a></td>
                                <td></td>
                            </tr>
                            <tr>
                              <td></td>
                                <td><span class="txt-blue h5">Wholesaler</span></td>
                                <td></td>
                            </tr>
                            <tr>
                              <td></td>
                                <td><span class="txt-bold txt-red" style="font-size:16px;"><?php echo $row['pd_fob_price']; ?></span> USD</td>
                                <td></td>
                            </tr>
                            <tr>
                              <td></td>
                                <td><span class="txt-bold txt-red" style="font-size:16px;"><?php echo $row['pd_min_order_qty'] ?></span> Min Order</td>
                                <td></td>
                            </tr>
                            <tr>
                              <td><img src="images/mobile.png"/></td>
                                <td><a href="#" class="txt-black h4">+20-1220974444</a> </td>
                                <td></td>
                            </tr>
                            <tr>
                              <td><input type="checkbox"/></td>
                                <td><input type="text" placeholder="Send Enquiry" style="width:100%;"/></td>
                                <td>Chat<img src="images/chat.png" style="width:20px; height:20px; margin-left:5px;"/></td>
                            </tr>
                        </table>
                    </section>
                </div>
        <?php $favcount++; if($favcount == 4){ $favcount = 0; ?>
           </div>
       <?php }
        } if($favcount != 0 ) { ?></div> <?php }
    }
    else{
      header('location:sign-in.php');
    } ?>
  </div>
    
 

  <!-- Controls -->
  <a class="left carousel-control" href="#carousel-example-generic" role="button" data-slide="prev" style="text-align:left;">
    <span class="slider-left" aria-hidden="false"> <i class="fa fa-chevron-left"></i> </span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#carousel-example-generic" role="button" data-slide="next" style="text-align:right;">
    <span class="slider-right" aria-hidden="false"> <i class="fa fa-chevron-right"></i> </span>
    <span class="sr-only">Next</span>
  </a>
</div>
  
  
  <!--Slider Close-->
  
  <div class="row">
  		<div class="container">
        	<div class="row" style="background-color:#c5e4f8; padding:5px;">
            	<div class="col-md-3" style="padding-top:7px;">
                	<span class="h4"> My Favourite Products </span>
                </div>
                <div class="col-md-2" style="padding-top:7px;">
                	<label>
                    	<input type="checkbox" style="vertical-align:sub;"/> Select All
                    </label>
                </div>
                <div class="col-md-7">
                	<button class="btn btn-sm border-radius-0 btn-default"><span class="h5">Add/Edit Folder</span></button>
                    
                    <button class="btn btn-sm border-radius-0 btn-default"><span class="h5">Delete</span></button>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
  </div>
    </div>
    <div class="clearfix"></div>
  </div>
</div>
<footer class="container-fluid">
  <div class="col-lg-12 ar-footer">
    <div class="row">
      <div class="col-lg-2 "> </div>
      <div class="col-lg-10" style="padding-left:67px;">
        <h4 class="txt-bold" style="color:#1e79bf; font-size:21px;"><b>Find Business Services of an Assessed Suppliers</b></h4>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-2 "> </div>
      <div class="col-lg-10 ar-search" style="padding-left:67px;">
        <form class="margin-top-5">
          <div class="col-lg-6" style="padding-left:0px;" >
            <div class="input-group input-group-lg" >
              <div class="input-group-btn">
                <button type="button" class="btn btn-default" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="border-color:#1d7abf;">Services </button>
              </div>
              <!-- /btn-group -->
              <input type="text" class="form-control" aria-label="..."  style="border-color:#1d7abf;">
              <span class="input-group-btn">
              <button class="btn btn-default btn-primary" type="button" style="border-color:#1d7abf;"><i class="fa fa-search" style="font-size:19.8px;"></i></button>
              </span> </div>
          </div>
          <div class="col-lg-5" style="padding-left:0px; ">
            <button class="btn btn-lg btn-yellow txt-bold  box-shadow" type="button"  data-toggle="modal" data-target=".postRequirement" style="padding:7px;"><img src="images/postRequest.png" width="200px"/></button>
          </div>
          <div class="clearfix"></div>
          <!-- /input-group -->
          
        </form>
      </div>
      <div class="clearfix"></div>
    </div>
    <div class="row margin-top-10">
      <div class="col-lg-3">
        <div class="col-lg-12 footer-logo">
          <table class="table">
            <tr>
              <td style="vertical-align:top; text-align:center;"><img src="images/footer-logo.jpg" width="140px"/></td>
              <td><a href="about_us.php">About Us</a> <a href="#">Complaints</a> <a href="#">Feedback</a> <a href="#">Our Agents</a> <a href="sign-in.php">Contact Us</a> <a href="help.php">Help</a></td>
            </tr>
          </table>
        </div>
      </div>
      <div class="col-lg-9 footer-list">
        <ul>
          <li>Buyers Tools</li>
          <li><a href="post-buy-req.php">Post Buy Requirment </a></li>
          <li><a href="manage-selloffer-alert.php">Manage Sell Offer Alerts </a></li>
          <li><a href="search_adv.php">Search Products / Services </a></li>
        </ul>
        <ul>
          <li>Suppliers Tools</li>
          <li><a href="product-sel-cat.php">Post Products - FREE </a></li>
          <li><a href="create-free-website.php">Create Website on ARABYOS</a></li>
          <li><a href="buyleads.php">Latest Buy Leads </a></li>
        </ul>
        <ul>
          <li>ARABYOS Soluations</li>
          <li><a href="#">Premium Membership </a></li>
          <li><a href="#">Trade Leads For Me</a></li>
          <li><a href="#">Advertise with us </a></li>
        </ul>
        <ul>
          <li>Tenders / Auctions</li>
          <li><a href="">Latest Tenders </a></li>
          <li><a href="#">Latest Auctions </a></li>
          <li><a href="#">Mange Tenders Alerts</a></li>
          <li><a href="#">Mange Auctions Alerts </a></li>
        </ul>
        <div class="col-lg-9 text-right social"> <span class="txt-lg" style="color:#1e79bf;"><b>Connect with us :</b></span> <a href="#"> <i class="fa fa-twitter twitter"></i> </a> <a href="#"> <i class="fa fa-google-plus google"></i> </a> <a href="#"> <i class="fa fa-facebook facebook"></i> </a> </div>
        <div class="clearfix"></div>
      </div>
    </div>
  </div>
</footer>
<footer class="container-fluid footer-bottom">
  <div class="col-lg-6 "> <b>Copyright  All rights reserved © 2015 ARABYOS.</b> </div>
  <div class="col-lg-6 text-right"> <a href="terms.php" class="txt-black">Term of Use</a> | <a href="privacy.php" class="txt-black">Privacy Policy </a> | <a href="contact_us.php" class="txt-black">Link to Us</a> </div>
</footer>
<!---->

<div class="fixed-div"> <a href="#top"><img src="images/up.png" width="50"/></a> <a href="#"><img src="images/complaint.png" width="50"/></a> </div>
<!--Modal-->
<div class="modal fade postRequirement" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    
      <div class="col-lg-12 popup-box" style="float:none;">
      	<img class="girl-img" src="images/girl.png"/> 
        <div class="col-lg-12 popup-sub-box">
          <header>
            <h3 style="color:#fff;">Submit Buy Requirement For</h3>
            <h3 style="color:#f58238;">"Rice"</h3>
          </header>
          <section class="col-lg-12">
            <div class="col-lg-12" style="padding:0px; border:1px solid #a094c7; position:relative;" ><!--style="border:1px solid #a094c7;"-->
            <textarea style="width:100%; max-width:100%; min-height:150px; max-height:150px; border:none; background-color:transparent; position:relative; z-index:5;" id="table-input1" ></textarea>
             <table  id="sideAdTable1" style="width:100%; position:absolute; top:0px;">
                <tr>
                  <td><i class="fa fa-exclamation-triangle" style="color:#ba2025; font-size:18px;"></i></td>
                  <td class="h4 " style="font-size:18px;"> Enter Product/Service Specifications </td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Application of Product</td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Product Features</td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Material - Product Packaging</td>
                </tr>
                <tr>
                  <td></td>
                  <td>- Any Special Requirement</td>
                </tr>
              </table>
            </div>
            <div class="col-lg-12 margin-top-10 margin-bottom-10">
              <button class="btn btn-lg btn-warning" style="padding:3px 5px;"> <b class="txt-bold">Get Instant Quote Now</b><br>
              <small>For many veryfied Suppliers </small> </button>
            </div>
            <div>
              <ul>
                <li>Your Contact Information</li>
                <li>Tame Sami</li>
                <li>Egypt - Cairo</li>
                <li>+20 8620005556</li>
                <li>rawag.elyom@yahoo.com</li>
              </ul>
            </div>
          </section>
        </div>
        <div class="clearfix"></div>
      </div>
      </div>
  </div>
</div>

<!--------================= Side Menu =================================----------------->
<div off-canvas="sb-1 left push" class="side_bar" style="z-index:10;">
  <ul>
    <li> <a class="side_menu" data-toggle="collapse" href="#Business" aria-expanded="false" aria-controls="collapseExample"> Business Type <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="Business">
        <div class="side_menu">
          <ul>
            <li> <a href="" >Manufacturer</a></li>
            <li> <a href="" >Exporter</a></li>
            <li> <a href="" >Wholesaler</a></li>
            <li> <a href="" >Retailer</a></li>
          </ul>
        </div>
      </div>
    </li>
    <li> <a class="side_menu" data-toggle="collapse" href="#Membership" aria-expanded="false" aria-controls="collapseExample"> Membership Type <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="Membership">
        <div class="side_menu">
          <ul>
            <li><a href="" > <img src="images/4.png" width="20"/> SPONSOR Supplier</a></li>
            <li><a href="" > <img src="images/5.png" width="20"/> SENIOR Supplier</a></li>
            <li><a href="" > <img src="images/6.png" width="20"/> Veryfied JUNIOR</a></li>
          </ul>
        </div>
      </div>
    </li>
    <li> <a class="side_menu" data-toggle="collapse" href="#Evaluation" aria-expanded="false" aria-controls="collapseExample"> Evaluation Tools <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="Evaluation">
        <div class="side_menu">
          <ul>
            <li> Min Qty
              <input type="text" width="80"/>
            </li>
            <li> <a href="#brochures" data-toggle="collapse">Currency Converter <i class="fa fa-caret-down pull-right"></i> </a>
              <div class="collapse list-group-submenu submenu" id="brochures"> <a href="#" >USD</a> <a href="#" >INR</a> <a href="#" >UAE</a> </div>
            </li>
            <li> <a href=""><img src="images/chat.png" width="18" > Online</a></li>
          </ul>
        </div>
      </div>
    </li>
    <li> <a href="" class="text-uppercase"> Sell Offers</a></li>
    <li> <a href="" class="text-uppercase"> Buy Leads</a></li>
    <li> <a href=""class="text-uppercase"> Post Sale Offers </a> </li>
    <li> <a href=""class="text-uppercase"> Buy Request </a></li>
    <li> <a class="side_menu" data-toggle="collapse" href="#Supplier-mobile" aria-expanded="false" aria-controls="collapseExample"> Supplier Countries <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="Supplier-mobile">
        <div class="side_menu">
          <form>
            <div class="checkbox">
              <label>
                <input type="checkbox" value="">
                <img src="images/flags/Afghanistan.png" alt="Afghanistan-flag"/> <span>Afghanistan (55)</span> </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" value="">
                <img src="images/flags/Algeria.png" alt="Algeria-flag"/> <span>Algeria (55)</span> </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" value="">
                <img src="images/flags/Bahrain.png" alt="Bahrain-flag"/> <span>Bahrain (55)</span> </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" value="">
                <img src="images/flags/Egypt.png" alt="Egypt-flag"/> <span>Egypt (55)</span> </label>
            </div>
            <div class="checkbox">
              <label>
                <input type="checkbox" value="">
                <img src="images/flags/Equatorial-Guinea.png" alt="Equatorial-Guinea-flag"/> <span>Equatorial Guinea (55)</span> </label>
            </div>
            <div class="form-group text-left">
              <button type="submit" class="btn btn-sm btn-warning border-radius-0" style="padding:0 3px; margin-right:10px;">Confirm</button>
              <button type="reset" class="btn btn-sm btn-link border-radius-0 txt-bold txt-black"  style="padding:0 3px;">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </li>
    <li> <a class="side_menu" data-toggle="collapse" href="#Categories-mobile" aria-expanded="false" aria-controls="collapseExample">  Categories  <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="Categories-mobile">
        <div class="side_menu">
          	<form>
          <div class="checkbox">
            <label>
              <input type="checkbox" value="">
              <span>Rice (55)</span> </label>
          </div>
          <div class="checkbox">
            <label>
              <input type="checkbox" value="">
              <span>Orgainic Grain (55)</span> </label>
          </div>
          <div class="checkbox">
            <label>
              <input type="checkbox" value="">
              <span>Corn (55)</span> </label>
          </div>
          <div class="checkbox">
            <label>
              <input type="checkbox" value="">
              <span>Basmati Rice (55)</span> </label>
          </div>
          <div class="checkbox">
            <label>
              <input type="checkbox" value="">
              <span>Yello Corn (55)</span> </label>
          </div>
        </form>	
        </div>
      </div>
    </li>
    
    <li> <a class="side_menu" data-toggle="collapse" href="#Related-mobile" aria-expanded="false" aria-controls="collapseExample">  Related Categories  <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="Related-mobile">
        <div class="side_menu">
          	<ul>
                <li> <a href="#">Dried Fruit </a> </li>
                <li> <a href="#">Frozen Fruit </a> </li>
                <li> <a href="#">Fresh Fruit </a> </li>
                <li> <a href="#">Preserved Fruit </a> </li>
            
            
          </ul>	
        </div>
      </div>
    </li>
    
    <li class="Select-language"> <a class="side_menu" data-toggle="collapse" href="#ara-language" aria-expanded="false" aria-controls="collapseExample">  Select Language  <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="ara-language">
        <div class="side_menu">
          	<ul>
                <li> <a href="#">English </a> </li>
                <li> <a href="#">Arabic</a> </li>
             
            
            
          </ul>	
        </div>
      </div>
    </li>
    
    <li class="Change-country"> <a class="side_menu" data-toggle="collapse" href="#ara-country" aria-expanded="false" aria-controls="collapseExample">  Change Country  <i class="fa fa-caret-down pull-right"></i> </a>
      <div class="collapse" id="ara-country">
        <div class="side_menu">
          	<ul>
                <li> <a href="#">India </a> </li>
                <li> <a href="#">USA</a> </li>
             
            
            
          </ul>	
        </div>
      </div>
    </li>
  </ul>
</div>

<!--- Close side bar div---> 
<script src="js/jquery.js"></script> 
<script src="js/bootstrap.min.js"></script> 
<script src="js/zoom-master/jquery.zoom.js"></script> 
<script src="zomImage/js/jquery-photo-enlarger/jquery-photo-enlarger.js"></script> 
<script src="zomImage/js/EventEmitter.js"></script> 
<script src="zomImage/js/eventie.js"></script> 
<script src="zomImage/js/imageloader.js"></script> 
<script src="zomImage/js/main.js"></script> 
<script src="js/slidebars.js"></script> 
<script>
			( function ( $ ) {
				$( document ).ready( function () {
					// Create a new instance of Slidebars
					var controller = new slidebars();
					
					// Initialize Slidebars
					controller.init();
					
					/**
					 * Control Classes
					 */
					
					
					// Toggle left
					$( '.toggle-left' ).on( 'click', function ( event ) {
						// Stop default behaviour and propagation
						event.preventDefault();
						event.stopPropagation();
						
						// Toggle Slidebar
						controller.toggle( 'sb-1' );
					} );
					
					
					// Close any
					$( '.close-any' ).on( 'click', function ( event ) {
						// Check if a Slidebar is open
						if ( controller.active( 'slidebar' ) ) {
							// Stop default behaviour and propagation
							event.preventDefault();
							event.stopPropagation();
							
							// Close any Slidebar
							controller.close();
						}
					} );
				} );
			} ) ( jQuery );
		</script> 
<script>
    $(document).ready(function(){
        $( ".zoomthis" ).hover(
            function() {
              $( this ).parent().find('.ribbon').hide();
              $(this).css('cursor','crosshair');
            }, function() {
              $( this ).parent().find('.ribbon').show();
            }
          );
        $('.zoomthis').zoom();
        
        $('.thumb').PhotoEnlarger();
    });
</script> 
<script>
	 $(window).bind('scroll', function() {
                if ($(window).scrollTop() > 800) {
                    
                   
                    $('.fixed-div').css('display', 'block');
					
                   
					

                } else {
                    
					
                    $('.fixed-div').css('display', 'none');

                }
            });
			$(window).bind('scroll', function() {
                if ($(window).scrollTop() > 450) {
                    
                  
                    $('#business-alert').css('display', 'block');
					$('#business-alert').css('position', 'fixed');
					$('#business-alert').css('width', '200px');
					$('#business-alert').css('z-index', '9');
					$('#business-alert').css('top', '160px');
					
                   
					

                } else {
                    $('#business-alert').css('position', 'static');
				
                }
            });
			
			 $(window).bind('scroll', function() {
                if ($(window).scrollTop() > 150) {
                    
                   
                    $('#right-image').css('position', 'fixed');
					
					$('#right-image').css('z-index', '9');
					$('#right-image').css('right', '8px');
					$('#right-image').css('top', '160px');
					
					
					
					
                } else {
                    
					$('#right-image').css('position', 'static');
                    
                   

                }
            });
			
			$(window).bind('scroll', function() {
                if ($(window).scrollTop() > 1500) {
                    
                   
                    
					$('#right-image').css('position', 'static');
					
					
					
                } else {
                    
					
                   
                   

                }
            });
			
			
function hide_modal(){
    $('.postRequirement').modal('hide');
} 


$(document).on('click', '.modal-btn', function(){
	 $('.postRequirement').modal('show');
   	 window.setTimeout(hide_modal, 180000);
});


</script> 
<script>
	$( document).on('click','#table-input',function(){ 
		$("#sideAdTable").hide();
		$("body").click(function(){
			$("#sideAdTable").show();
			});
		});
</script>
<script>
	$( document).on('click','#table-input1',function(){ 
		$("#sideAdTable1").hide();
		$("body").click(function(){
			$("#sideAdTable1").show();
			});
		});
</script>
</body>
</html>
