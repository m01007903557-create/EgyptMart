<?php
error_reporting(0);
ob_start();
session_start();
set_time_limit(600);
include 'common.php';
$uid = $_SESSION['uid_indm'];
$globalcntid = 243;
if (isset($_COOKIE['loc_id'])) {
    ## get Country id by
    $cn_id = $_COOKIE['loc_id'];
    $sqlcountry = "select cn_name from country where cn_id='$cn_id'";
    $rscountry = mysqli_query($con, $sqlcountry);
    if (mysqli_num_rows($rscountry) > 0) {
        $rowcountrty = mysqli_fetch_object($rscountry);
        $cn_name = $rowcountrty->cn_name;
    }
} else {
    $cn_id = 0;
    $cn_name = "Global";
}

// Line 2
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
      <style>body{overflow-x:hidden;margin:0;padding:0}a{text-decoration:none !important}::placeholder{color:#c84124;font-size:14px}.header{background:#227abf;padding:10px;width:100%;overflow-x:hidden}ul{padding-inline-start:0}.header ul{display:flex}.header nav li{display:inline-block;padding:10px}.header nav li img{width:200px;height:40px}.header nav li a{color:#fff;font-size:16px;text-transform:capitalize;text-decoration:none}.header form{display:flex}.header form input{width:250px;height:40px;padding-left:20px}.header form button{width:100px;border-top-right-radius:8px;border-bottom-right-radius:8px;background-color:#c84124;font-size:17px;color:#fff;text-transform:capitalize;transition:background-color .5s ease-in-out}.header form select{-webkit-appearance:none;-webkit-border-radius:0px;padding-left:20px;width:120px;border-top-left-radius:8px;border-bottom-left-radius:8px;border-right:1px solid #c84124;text-transform:capitalize;background:#fff}input,select,textarea,button{outline:none;border:none}button,select{cursor:pointer !important}textarea,input{cursor:text}.second-sec-header ul:nth-child(2){padding-right:50px}.second-sec-header ul{float:left}.second-sec-header ul li{display:inline-block;font-weight:400;font-size:17px}.second-sec-header ul li:nth-child(2){padding-right:150px}.second-sec-header ul li a{color:#fff;text-decoration:none;text-transform:capitalize;padding:20px;cursor:pointer}.li-track{float:right !important;display:block;position:relative}.body-content{display:flex}.body-content-sec-1{min-width:250px;max-width:250px}.body-content-sec-1 ul{padding-inline-start:0;overflow:hidden;margin-left:10px}.body-content-sec-1 ul li{list-style:none;padding-bottom:10px;padding-top:10px;width:100%;padding-left:10px;transition:background-color .5s ease-in-out !important;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}.body-content-sec-1 ul li a{text-decoration:none;text-transform:capitalize;color:#222}.header-left{margin-left:20px;color:#FFF;padding-top:20px !important}.body-content-sec-2{position:relative}.content-sec-2{margin-left:40px;margin-right:25px}.content-sec-2 li{list-style:none;display:inline-block;margin-bottom:20px}.continent{text-transform:capitalize;color:#227abf;font-weight:bold;font-size:20px;letter-spacing:2px;text-shadow:0 0 1px #c84124;margin-right:20px}.newsletter-cont{width:100%;border-top:1px solid whitesmoke;border-bottom:1px solid whitesmoke;position:relative;overflow:hidden;padding-top:50px;padding-bottom:50px}.newsletter-cont .news-header{float:left;padding-left:30px}.newsletter-cont form{float:right;padding-right:100px}.newsletter-cont form input{width:350px !important;height:40px;border-top-left-radius:7px;border-bottom-left-radius:7px;border:1px solid #e6e6e6;padding-left:30px}.newsletter-cont form button{width:100px;border-top-right-radius:7px;color:#fff;font-size:15px;border-bottom-right-radius:7px;background:#c84124;height:44px;transition:background-color  .5s ease-in-out}.news-letters{font-size:23px;padding-bottom:7px;text-shadow:0 1px #c84124}.top-catr{padding-left:40px}.top-catr ul li{display:inline-block;width:150px !important;height:auto;padding-top:20px;box-shadow:0 0 1px gray !important;padding-bottom:20px;margin-bottom:10px;border:1px solid #e7e2e2;margin-right:30px;overflow:hidden}footer{display:flex;padding-left:70px;padding-top:40px}.top-catr ul li img{width:100%;height:auto}.lastest-arrival{padding:13px 20px;margin-bottom:55px;background-color:#f4f4f4;border-bottom:1px solid #c84124;width:100%;overflow:hidden}.lastest-arrival h2{font-size:20px;font-weight:600;margin:0;float:left;float:left}.lastest-arrival ul li{display:inline-block;float:right;text-transform:capitalize;padding-left:15px;padding-right:7em}.arrival-cont ul{padding-left:40px;padding-right:40px;overflow:hidden}.arrival-cont ul li{width:20%;float:left;list-style:none;padding-bottom:20px}.arrival-cont-- ul{padding-left:40px;padding-right:40px}.arrival-cont-- ul li{width:25%;float:left;list-style:none;padding-bottom:20px}.arrival-cont-supplies ul{padding-left:40px;padding-right:40px}.arrival-cont-supplies ul li{width:20%;float:left;list-style:none;padding-bottom:20px}ul{padding-left:40px}.arrival-cont ul li{width:20%;float:left;list-style:none;padding-bottom:20px}.background{background-color:whitesmoke;margin-bottom:0;width:150px;height:100px;background-size:cover;background-position:center}.name-of-product{color:#c84124;margin:0;font-weight:400;padding:0 0 5px;font-size:15px;line-height:1.3;text-transform:capitalize}.owners{font-weight:bold;font-size:14px}.arrive-status{padding-left:20px;width:calc(100% - 100px);cursor:pointer}.unit{padding-bottom:4px;padding-top:5px;cursor:pointer}.unit span:nth-child(1){font-weight:200px;font-size:12px}.sizes span:nth-child(1){font-weight:200px;font-size:12px}.search-country{float:right !important}.search-country input{width:200px !important;height:35px;border-top-left-radius:7px;border-bottom-left-radius:7px;border:1px solid #e6e6e6;padding-left:30px}.search-country button{width:80px;border-top-right-radius:7px;color:#fff;font-size:15px;border-bottom-right-radius:7px;background:#c84124;height:39px;transition:background-color  .5s ease-in-out}.partners{font-size:20px;text-transform:capitalize}.global{display:flex;width:100%;margin-bottom:0}.cont-col{width:400px}.country-name{font-size:16px;color:#227abf}.country-flag img{width:30px;margin-top:10px}.product ul li{width:25% !important}.pop{display:none}.body-content-sec-1 ul li:hover{background:#227abf !important;cursor:pointer !important}.body-content-sec-1 ul li:hover a{color:#fff !important}.body-content-sec-1 ul li:nth-child(1):hover{background:none !important;cursor:default !important}
</style>
    <!-- <link rel="stylesheet" href="css/combine.css"> -->
    <title><?php echo getSiteTitle(); ?></title>
</head>
<body>
<!-- pop -->
<div class="pop">
    <div class="sub-pop-cont">
        <!-- cancel -->
        <div class="icon-div"></div>
        <!-- welcome -->
        <div class="welcome-cont">
            <div class="greeting">
              <b>  Welcome to Egpytmart</b>
            </div>
            <div class="suggest-user">
                <span>New User</span>
                <span><a>Join Now!</a></span>
            </div>
        <div class="iscont">
            <div class="for-buyers">
                for buyers
                </div>
                <div class="for-sellers">
                for sellers
                    </div>
                    </div>
                        

        </div>
     
                
          <!-- content  for buyers-->
     <div class="cont-for-buyers">
          <!--  -->
         <div class="heading">
             manufacturer-exporter-wholedaler-retialer-service Provider

         </div>
         <ol>
             <li><div class="subheading">Creat Your EgptyMART Business Website</div>
            <div class="about-sub-heading">get business listing and promote your product / services to thousand of members buyer companies in egypt and worldwide.</div>
            </li>

            <li class="mt"><div class="subheading">Start Location Business!</div>
                <div class="about-sub-heading">display your product / services/tenders offfers according to thousand of members buyer companies in egypt and worldwide.</div>
                </li>
                <li class="mt"><div class="subheading">Manage By Lead Alert</div>
                    <div class="about-sub-heading">get business listing and promote your product / services to thousand of members buyer companies in egypt and worldwide.</div>
                    </li>
         </ol>
         <!--  -->
                
        </div>


          <!-- content  for sellers-->
     <div class="cont-for-sellers">
            <!--  -->
           <div class="heading">
               manufacturer-exporter-wholedaler-retialer-service Provider
  
           </div>
           <ol>
               <li><div class="subheading">Creat Your EgptyMART Business Website</div>
              <div class="about-sub-heading">get business listing and promote your product / services to thousand of members buyer companies in egypt and worldwide.</div>
              </li>
  
              <li class="mt"><div class="subheading">Start Location Business!</div>
                  <div class="about-sub-heading">display your product / services/tenders offfers according to thousand of members buyer companies in egypt and worldwide.</div>
                  </li>
                  <li class="mt"><div class="subheading">Manage By Lead Alert</div>
                      <div class="about-sub-heading">get business listing and promote your product / services to thousand of members buyer companies in egypt and worldwide.</div>
                      </li>
           </ol>
           <!--  -->
                  
          </div>
        
    </div>
   
    
</div>
<!-- pop end -->
<!-- scroll to top -->
<div class="scroll-top">
 
</div>



    
    <!-- header -->
    <header class="header">
    <!-- first sec -->
<div class="first-sec-header">
    <nav>
        <ul>

            <li><a href=""><img src="img/logo.png" alt="" srcset=""></a></li>
            <!--  -->
            <li>
                <form action="search.php">
                    <select name="rctyp" id="rctyp">
                        <option name="Products">Products</option>
                        <option name="Suppliers">Suppliers</option>
                        <option name="buy_lead">Buy Leads</option>
                        <option value="Tenders">Tenders</option>
                      
                        
                       
                       

                    </select>
                    <input type="search" class="header-input-search" placeholder="Search By Category">
                    <button class="search-header" name="keywords">search</button>
                </form>
            </li>
            <!--  -->
            <li class="header-left">
                +234506006606060
            </a>
            </li>
            <!--  -->
            <li class="header-left"><a href="">login</a></li>
            <li class="header-left">profile</a></li>
            <li class="header-left">wassup</a></li>
           
        </ul>
    </nav>

</div>
    <!-- secon sec -->
    <div class="second-sec-header">
        <ul>
            <li></li>
            <li><a href=""> shop by categories</a></li>
            <li><a>home</a></li>
            <li><a>about us</a></li>
            <li><a>buy</a></li>
            <li><a>sell</a></li>
           
            
        </ul>
        <ul   class="li-track" >
        <li><a href="">track your order</a></li>
        </ul>
    
    </div>
    <!-- header end -->
    </header>



    <!-- body contenent -->
    <section class="body-content">
        <!-- side-contnt -->
        <div class="body-content-sec-1">
             
            <ul>
                    <li>MY MARKETS  </li>



                    <li class="agr"><a href="">Agriculture</a>
                    <!-- agriculture hover -->
                    <div class="div-hover agriculture-hover">
                        <div class="col1">
                            <ul>
                                <li><a href="agriculture">agriculture waste</a></li>
                                <li><a href="beans">beans</a></li>
                                <li><a href="feed">feed</a></li>
                                <li><a href="garden">garden tools</a></li>
                                <li><a href="mushrooms">mushrooms & truffles</a></li>
                                <li><a href="ornamaental">ornamaental plant</a></li>
                                <li><a href="other">other beans</a></li>
                                <li><a href="vegetables">vegetables</a></li>
                            </ul>
                        </div>
                        <div class="col1">
                            <ul>
                                <li><a href="agrochemicals">agrochemicals</a></li>
                                <li><a href="coffee">coffee beans</a></li>
                                <li><a href="fresh">fresh seafood</a></li>
                                <li><a href="grain">grain</a></li>
                                <li><a href="nuts">nuts & kenels</a></li>
                                <li><a href="other">other agriculture</a></li>
                                <li><a href="plant">plant and animal oil</a></li>
                            </ul>
                        </div>
                        <div class="col1">
                            <ul>
                                <li><a href="animal">animal product</li>
                                <li><a href="farm">farm & machinery equipment</li>
                                <li><a href="fruit">fruit</li>
                                <li><a href="grain">grain product</li>
                                <li><a href="oragnic">oragnic produce</li>
                                <li><a href="other">other agricultural product</li>
                                <li><a href="plant">plant seeds & bulbs</li>
                            </ul>
                        </div>
                    </div>

                    </li>









                    



                    <li><a href=""><a>apparel</a></li>
                    <li><a href=""> <a>automobile motocycle</a></li>
                    <li><a href="">beauty and personal com</a></li>
                    <li><a href=""> Business services</a></li>
                    <li><a href="">chemicals</a></li>
                    <li><a href="">computer hardwares</a></li>
                    <li><a href=""><a>constrution and real estate</a></li>
                    <li><a href=""> <a>consumer electronic</a></li>
                    <li><a href="">electrical equipment</a></li>
                    <li><a href=""> electronic component</a></li>
                    <li><a href="">energy</a></li>
                    <li><a href="">environment</a></li>
                    <li><a href=""><a>fashion accesories</a></li>
            </ul>
        </div>
        <!-- main content -->
        <div class="body-content-sec-2">
            <!-- search country -->
                <div class="lastest-arrival global">
                    <div class="cont-col">
                       <b>Global Page <img src="img/world.png" alt=""></b>
                       <b class="partners">partners Suppliers</b>
                </div>
                        <ul>
                            <li><form action="" class="search-country">
                                    <form action=""><input type="text" placeholder="Search Country"><button>Search</button></form>
                            </form></li>
                            
                          
                            
                        </ul>
                    </div>
                     <!-- search country  emd-->
                   
                    
            <ul class="content-sec-2">
                <li><div class="continent">asia :</div></li>
                <li class="b-right"><div class="country-name"> UAE</div><div class="country-flag"><img src="img/uae.jpg" alt="" srcset=""></div></li>
                <li  class="b-right"><div class="country-name"> Kuwait</div><div class="country-flag"><img src="img/Kuwait.jpg" alt="" srcset=""></div></li>
            
                <li  class="b-right"><div class="country-name"> Saudi Arabia</div><div class="country-flag"><img src="img/Saudi-Arabia.jpg" alt="" srcset=""></div></li>
                <li><div class="country-name"> Jordan</div><div class="country-flag"><img src="img/jordan.jpg" alt="" srcset=""></div></li>
              </ul>
              <ul class="content-sec-2" style="display: flex">
                    <li><div class="continent">africa :</div></li>
                    <li><div class="country-name"> Egpty</div><div class="country-flag"><img src="img/egpty.png" alt="" srcset=""></div></li>
                 </ul>



                 <?php
                                        if (isset($_COOKIE['loc_id'])) {
                                            $sql_pd_ck = " and (
                                    (pd_preferred_buyer_location='domestic' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (pd_preferred_buyer_location='any' and pd_uid in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                            /*
                                              (pd_preferred_buyer_location='my_city' and pd_uid in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and pd_uid in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                              or
                                              (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                             */
                                            $sql_so_ck = " and (
                                    (so_preferred_buyer_location='domestic' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (so_preferred_buyer_location='any' and so_usr_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                            /*
                                              (so_preferred_buyer_location='my_city' and so_usr_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and so_usr_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                              or
                                              (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                             */
                                            $sql_br_ck = " and ((br_preferred_supplier_location='domestic' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (br_preferred_supplier_location='any' and br_u_id in(select distinct usr_id from user where country='" . $_COOKIE['loc_id'] . "'))
                                    or
                                    (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_cn_id='" . $_COOKIE['loc_id'] . "' LIMIT 1))))";
                                            /*
                                              (br_preferred_supplier_location='my_city' and br_u_id in(select distinct bnsprof_uid from business_profile where bnsprof_city=(select ct_id from city where ct_name like '".getCityCode()."')) and br_u_id in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                              or
                                              (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country='".$_COOKIE['loc_id']."'))
                                             */
                                        } else {
                                            $sql_pd_ck = " and (
                                    (pd_preferred_buyer_location='any')
                                    or
                                    (pd_preferred_buyer_location='abroad' and pd_uid not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
                                    )";
                                            $sql_so_ck = " and (
                                    (so_preferred_buyer_location='any')
                                    or
                                    (so_preferred_buyer_location='abroad' and so_usr_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
                                    )";
                                            $sql_br_ck = " and (
                                    (br_preferred_supplier_location='any')
                                    or
                                    (br_preferred_supplier_location='abroad' and br_u_id not in(select distinct usr_id from user where country=(select cn_id from country where cn_code='" . $location_geo_country . "')))
                                    )";
                                        }
                                        if ($_COOKIE['loc_id'] != "") {
                                            $sql_prd = "select pd_id, pd_subcat_id, pd_uid, pd_title,pd_fob_price,pd_fob_price2,pd_unit,cn_id,cn_name,mu_id,cn_code,mu_name from  products,measurement_unit,country,user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24";
                                        } else {
                                            $sql_prd = "select pd_id, pd_subcat_id, pd_uid, pd_title,pd_fob_price,pd_fob_price2,pd_unit,cn_id,cn_name,mu_id,cn_code,mu_name from  products,measurement_unit,country, user where mu_id=pd_unit and user.usr_id = products.pd_uid and pd_currency=cn_id and pd_status='1'   and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24";
                                        }
                                        // die($sql_prd);
                                        $res_prd = mysqli_query($con, $sql_prd);
                                        
?>
            
        
          <!-- product and supply -->
          <div class="lastest-arrival">
                <h2>View Products & Suppliers</h2>
                <ul>
                    <li class="inNeed">Display Your Product</li>
                    
                  
                    
                </ul>
            </div>


<!-- arrival content -->
<div class="arrival-cont--">
        <ul>

        <?php

while($res_prd_res = mysqli_fetch_assoc($res_prd)) {

?>
                <li>
                    <div style="display: flex;">
                        <div class="background" style="background-image:url('img/leading-projuct1.jpg') !important">
    
                        </div>
                        <div class="arrive-status">
                         <div class="name-of-product">
                         <a><?= $res_prd_res['pd_title'] ;?><a>
                         </div>
                         <div class="owners">
                             <a href=""><?= $res_prd_res['cn_name'] ;?></a>
                         </div>
                         <div class="unit">
                             <span>MOQ:</span>
                             <span><?= $res_prd_res['pd_unit'] ;?><?= $res_prd_res['mu_name'] ;?></span>
                         </div>
                         <div class="sizes">
                             <span><?= $res_prd_res['cn_code'] ;?> / <?= $res_prd_res['pd_unit'] ;?><?= $res_prd_res['mu_name'] ;?></span>
                         </div>
                        </div>
    
                    </div>
                </li>
            <?php
    // print_r($res_prd_res);
}

?>
    <!--  -->
    
    <!--  -->
    
        </ul>



    </div>
    </section>
    

        
        <!-- temporary sales offer adds -->
        <section>
     
        <div class="lastest-arrival">
                <h2>Temporary Sales Offer Ads</h2>
                <ul>
                    <li class="inNeed">Post Sales Offer Ads</li>
                    
                  
                    
                </ul>
            </div>
   
            <!-- temporary goods -->

<div class="arrival-cont">
        <ul>
                <li>
                    <div style="display: flex;">
                        <div class="background" style="background:url('img/leading-projuct1.jpg') !important">
    
                        </div>
                        <div class="arrive-status">
                         <div class="name-of-product">
                         <a>casual shoes<a>
                         </div>
                         <div class="owners">
                             <a href="">Egypt</a>
                         </div>
                         <div class="unit">
                             <span>MQQ:</span>
                             <span>1unit</span>
                         </div>
                         <div class="sizes">
                             <span>
                                 Len1 
                             </span>
                             <span> / Dozen</span>
                         </div>
                        </div>
    
                    </div>
                </li>
    <!--  -->
    
        </ul>



    </div>
    </section>

        

    
    
   


   
    
     <!-- newletter -->
     <div class="newsletter-cont">
            <div class="news-header">
                <div class="news-letters">Newsletters</div>
                <div class="content-letter">Suscribe to get information about product and coupon</div>
            </div>
             <form action=""><input type="text" placeholder="Email Address"><button>Suscribe</button></form>
         </div>


   
</div>
<!-- new arrival -->
<section>
<div class="lastest-arrival">
    <h2>EgyptMart Leading Product</h2>
    <ul>
        <li> <span style="margin-left:30px !important">Post Premium Ads</span></li>
        
      
        
    </ul>
</div>

<!-- arrival content -->
<div class="arrival-cont">
    <ul>
            <li>
                <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-projuct1.jpg') !important">

                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>

                </div>
            </li>
<!--  -->
<li>
        <div style="display: flex;">
            <div class="background" style="background:url('img/leading-projuct2.jpg') !important">

            </div>
            <div class="arrive-status">
             <div class="name-of-product">
             <a>casual shoes<a>
             </div>
             <div class="owners">
                 <a href="">Egypt</a>
             </div>
             <div class="unit">
                 <span>MQQ:</span>
                 <span>1unit</span>
             </div>
             <div class="sizes">
                 <span>
                     Len1 
                 </span>
                 <span> / Dozen</span>
             </div>
            </div>

        </div>
    </li>
<!--  -->  <li>
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-projuct3.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->  <li>
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-projuct4.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->  <li>
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-projuct5.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->  <li>
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-projuct6.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->  <li>
    
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-product8.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->
<!--  -->  <li>
    
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-product8.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->
<!--  -->  <li>
    
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-product8.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->
<!--  -->  <li>
    
        <div style="display: flex;">
                <div class="background" style="background:url('img/leading-product8.jpg') !important">

                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>

            </div>
        </li>
<!--  -->

    </ul>
</div>

</section>

<!-- leading product -->
<section>
<div class="lastest-arrival">
        <h2>Loyal Business Services</h2>
        <ul>
            <li>Post Business Services</li>
            
          
            
        </ul>
    </div>
    
    <!-- arrival content -->
    <div class="arrival-cont">
        <ul>
                <li>
                    <div style="display: flex;">
                        <div class="background" style="background:url('img/leading-projuct1.jpg') !important">
    
                        </div>
                        <div class="arrive-status">
                         <div class="name-of-product">
                         <a>casual shoes<a>
                         </div>
                         <div class="owners">
                             <a href="">Egypt</a>
                         </div>
                         <div class="unit">
                             <span>MQQ:</span>
                             <span>1unit</span>
                         </div>
                         <div class="sizes">
                             <span>
                                 Len1 
                             </span>
                             <span> / Dozen</span>
                         </div>
                        </div>
    
                    </div>
                </li>
    <!--  -->
    <li>
            <div style="display: flex;">
                <div class="background" style="background:url('img/leading-projuct2.jpg') !important">
    
                </div>
                <div class="arrive-status">
                 <div class="name-of-product">
                 <a>casual shoes<a>
                 </div>
                 <div class="owners">
                     <a href="">Egypt</a>
                 </div>
                 <div class="unit">
                     <span>MQQ:</span>
                     <span>1unit</span>
                 </div>
                 <div class="sizes">
                     <span>
                         Len1 
                     </span>
                     <span> / Dozen</span>
                 </div>
                </div>
    
            </div>
        </li>
    <!--  -->  <li>
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-projuct3.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->  <li>
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-projuct4.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->  <li>
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-projuct5.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->  <li>
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-projuct6.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->  <li>
        
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-product8.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->
    <!--  -->  <li>
        
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-product8.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->
    <!--  -->  <li>
        
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-product8.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->
    <!--  -->  <li>
        
            <div style="display: flex;">
                    <div class="background" style="background:url('img/leading-product8.jpg') !important">
    
                    </div>
                    <div class="arrive-status">
                     <div class="name-of-product">
                     <a>casual shoes<a>
                     </div>
                     <div class="owners">
                         <a href="">Egypt</a>
                     </div>
                     <div class="unit">
                         <span>MQQ:</span>
                         <span>1unit</span>
                     </div>
                     <div class="sizes">
                         <span>
                             Len1 
                         </span>
                         <span> / Dozen</span>
                     </div>
                    </div>
    
                </div>
            </li>
    <!--  -->
    
        </ul>
    </div>
    </section>

    



<script>
    window.addEventListener("scroll", ()=>{
        let isTop=document.querySelector(".scroll-top");
// isTop.addEventListener("click", isFunctionTop);
// function isFunctionTop(){
    
// }
if(window.innerHeight>800 || document.body.clientHeight>800){
isTop.style="opacity:1;"



}
else{
    isTop.style.opacity="0"
}
    })



// productss




</script>
</body>
</html>