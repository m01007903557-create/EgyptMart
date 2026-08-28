<?php
error_reporting(0);
ob_start();
session_start();

include 'common.php';
$uid=$_SESSION['uid_indm'];
$globalcntid = 241;
if(isset($_COOKIE['loc_id']))
{
	## get Country id by
	$cn_id = $_COOKIE['loc_id'];
	$sqlcountry = "select cn_name from country where cn_id='$cn_id'";
	$rscountry = mysqli_query($con,$sqlcountry);
	if(mysqli_num_rows($rscountry) > 0)
	{
	  $rowcountrty = mysqli_fetch_object($rscountry);
	  $cn_name = $rowcountrty->cn_name;
	}
}
else
{
	$cn_id = 0;$cn_name="Global";
}
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
## query for country
if($cn_id!="")
 {
	 //$strconutnry=" AND (adv_country LIKE '%$cn_id,%' OR adv_country LIKE '%,$cn_id%' OR adv_country LIKE '%,$cn_id,%' OR adv_country='$cn_id')";
	 $strconutnry=" AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
 }
 else
 {
	 //$strconutnry =" AND (adv_country LIKE '%$globalcntid,%' OR adv_country LIKE '%,$globalcntid%' OR adv_country='$globalcntid')";
	 $strconutnry=" AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
 }
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?php echo getSiteTitle(); ?>">
    <meta name="keywords" content="<?php echo get_page_settings(2); ?>">
    <meta name="description" content="<?php echo get_page_settings(3); ?>">
    <title>Welcome :: <?php echo getSiteTitle(); ?></title>
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css'/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <!-- Custom Theme files -->
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
    <!-- Custom Theme files //  -->

    <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
    <link href="css/im-style-v1.css" rel="stylesheet" type="text/css"/>

    <!--[if IE]>
    <script src="js/html5.js"></script> <![endif]-->

    <!-- start of verticle menu -->
    <link href="css/verticle-menu.css" rel="stylesheet" type="text/css"/>
    <!-- End of verticle menu -->
    <!-- Start of yahoo slider -->
    <link type="text/css" rel="stylesheet" href="css/theme.css"/>
  <!-- <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">-->

    <script type="text/javascript" src="js/jquery.accessible-news-slider.js"></script>

 <link href="css/type.css" rel="stylesheet" type="text/css"/>
    <!-- Start of video/testimonial slider -->
    <script src="js/responsiveslides.min.js"></script>
    <script>
        $(function () {
            // Slideshow 1
            $("#slider").responsiveSlides({
                auto: true,
                nav: false,
                speed: 500,
                namespace: "callbacks",
                pager: true
            });

        });
    </script>
    <!-- End of video/testimonial slider // -->

<!--<script>
	document.onreadystatechange = function () {
  var state = document.readyState
  if (state == 'interactive') {
       document.getElementById('contents').style.visibility="hidden";
  } else if (state == 'complete') {
      setTimeout(function(){
         document.getElementById('interactive');
         document.getElementById('load').style.visibility="hidden";
         document.getElementById('contents').style.visibility="visible";
      },1000);
  }
}
</script>-->

<!--<style>
	#load{
    width:100%;
    height:100%;
    position:fixed;
    z-index:9999;
    background:url("images/loading1.gif") no-repeat center center rgb(255, 255, 255)
}
</style>-->
    <script type="text/javascript">
        // when the DOM is ready, conv the feed anchors into feed content
        jQuery(document).ready(function () {

            jQuery('#newsslider').accessNews({});

            jQuery('#newsslider2').accessNews({
                title: "BREAKING NEWS:",
                subtitle: "stories from the internet",
                speed: "slow",
                slideBy: 5,
                slideShowInterval: 100000,
                slideShowDelay: 100000
            });

        });
    </script>
    <!-- End of yahoo slider // -->

<style>

</style>
</head>

<body style="background-color: #EDF2F5" onload="showcontent(main)">
 <!--<div id="load"></div>-->
    <!--<div id="contents">-->

<div id="fb-root"></div>
<script>
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
</script>


<?php
if(get_page_settings('25')=='manual')
{
    $sql_order=" order by pc_order,pc_name";
}
else
{
    $sql_order=" order by pc_name";
}
?>
<!-- Start of wrapper -->
<div class="wrapper">

    <?php	include "includes/header.php";	?>

    <!-- Start of middlesection -->
    <div class="middlesection">
        <div class="maincontainer">
            <div class="demobox">
                <div id="leftsection">
                <h3 style="margin-left: 10PX"><a href="dir.php#main_cat"><i class="fa fa-list-ul" style="color:#FF4500 "></i>&nbsp;<span>A</span>ll Categories</a></h3>

                    <div id="block_navigation">
                        <div id="pull" style="display: none;">
                            <a href="#"> <i class="icon-reorder"></i>Menu</a>
                        </div>

                        <ul class="navigation ptag">

                            <?php
                            $sql_dd_mnu = "select pc_id,pc_name,pc_image from product_category where pc_parent_id = '0' and pc_status = '1' " . $sql_order;
                            $res_dd_mnu = mysqli_query($con, $sql_dd_mnu);

                            while ($row_dd_mnu = mysqli_fetch_object($res_dd_mnu)) {
                            ?>
                            <li class="ptag">
                            <?php
                            if( $row_dd_mnu->pc_name == "Business Services" )
                            { ?>

			  	 <a  href="dir.php#main_cat_   ptag<?php echo $row_dd_mnu->pc_id; ?>" style="color:red; font-family: Arial Black">  <!--<i class="icon-list"></i>-->
                                <?php echo $row_dd_mnu->pc_name; ?>
                                 <span class="main_links_span"></span>
                                 </a>

			 <?php

			  } else {
			 	?>

                                <a  href="dir.php#main_cat_   ptag<?php echo $row_dd_mnu->pc_id; ?>">  <!--<i class="icon-list"></i>-->
                                <?php echo $row_dd_mnu->pc_name; ?>
                                 <span class="main_links_span"></span>
                                 </a>
<?php  }

                            ?>
                                <div class="typography_3_colm">
                                    <div class="colm_3_container">
                                        <?php
                                        $sql_dd_cmnu = "select pc_id, pc_sort_name from product_category where pc_parent_id = '" . $row_dd_mnu->pc_id . "' and pc_status = '1' " . $sql_order;
                                        $res_dd_cmnu = mysqli_query($con, $sql_dd_cmnu);

                                        $item_cnt = mysqli_num_rows($res_dd_cmnu);
                                        //echo var_dump($item_cnt);

                                        $res_dd_cmnu_arr = array();
                                        while ($row_dd_cmnu = mysqli_fetch_object($res_dd_cmnu)) {
                                            $row_scnt = mysqli_fetch_object(mysqli_query($con, "select count(*) as cnt from product_category where pc_parent_id = '" . $row_dd_cmnu->pc_id . "' and pc_status = '1'"));
                                            $res_dd_cmnu_arr[] = $row_dd_cmnu;
                                        }

                                        if($item_cnt  >= 1) $res_col_arr = array_chunk($res_dd_cmnu_arr, ceil($item_cnt / 3));
                                            foreach($res_col_arr as $r){ ?>
                                            <div class="colmn_3_fullwidth">
                                                <ol class="some_links  ptaga">
                                                <?php if(is_array($r)){
                                                    for($i = 0 ; $i < count($r) ; $i++){ ?>
                                                         <li><a class="ptaga"  href="products.php?c=<?php echo md5($r[$i]->pc_id); ?>"><?php echo ucwords($r[$i]->pc_sort_name); ?></a></li>
                                                    <?php }
                                                }else{ ?>
                                                    <li ><a class="ptaga" href="products.php?c=<?php echo md5($r['pc_id']); ?>"><?php echo ucwords($r['pc_sort_name']); ?></a></li>
                                                <?php } ?>
                                                    </ol>
                                                </div>
                                            <?php } ?>
                                    </div>
                                </div>
                            </li>
                            <?php } ?>
                             <p><a href="dir.php#main_cat" target="_blank">View All Categories <span>&gt;&gt;</span></a></p>
                        </ul>
                    </div>

                    <div class="list-top">
                        <h1><a href="#"><img src="images/wholesaler.jpg" alt=""/></a></h1>
                        <h5>Set Your</h5>

                        <a href="create-free-website.php" target="_"><div class="showcase">Products Showcase</div></a>
                        <p>Distribute in Your City</p>

                    </div>

                    <div class="map-tops">
                        <div class="map"><a href="#"><a href="#"><img src="images/map.png" alt=""/></a></a></div>
                    </div>

                    <div class="list-top">
                        <div class="seniorbox">
                            <div class="siniorlistbox">
                                <div class="siconbox"><img src="images/left-icon.png" alt=""/></div>
                                <div class="scontentbox"><h2> Senior <span>Supplier</span></h2></div>
                                <div class="clear"></div>
                            </div>

                            <ul>
                                <li>&gt; <a href="#">Premium Company Websites</a></li>
                                <li>&gt; <a href="#">Product ShowCase</a></li>
                                <li>&gt; <a href="#">Product Top Rank</a></li>
                                <li>&gt; <a href="#">Full Access to Buy Leads</a></li>
                                <li>&gt; <a href="#">Free Banner Advisements</a></li>
                                <li>&gt; <a href="#">Company video</a></li>
                                <p><a href="membership_plans.php" style="fright">Learn More <span>&gt; &gt;</span></a></p>
                            </ul>

                            <h3><a href="membership_plans.php">Upgrade Now</a></h3>
                        </div>
                    </div>


                    <div class="mid-tops">

						<?php
						$banner=GetHomeBanner('left',$strconutnry);
						if($banner!="")
						{
					      echo '<div class="middle mid-center" style="padding:0;">';
						  echo $banner;
						  echo '</div>';
						}
						else
						{
							// echo '<div class="middle mid-center">';
							//echo ' <h2>Advisement Place</h2>';
							 //echo '</div>';
						}
						?>
						<div class="clear"></div>

                    </div>
                    <div class="clear"></div>
                </div>

                <div id="midcenter">

                    <!-- Start of slider -->
                    <div class="slider">
                        <div class="yahoo_slider">

                            <ul id="newsslider">
                             <?php
							$sqllogo = "select * from yahoo_slider where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
							$rslogo = mysqli_query($con,$sqllogo);
							if(mysqli_num_rows($rslogo) > 0)
							{
								while($rowlogo= mysqli_fetch_object($rslogo))
								{
									$adv_img = $rowlogo->adv_img;
									$logopath = "upload/yahoo_slider/".$adv_img;
									$adv_link = $rowlogo->adv_link;
									$adv_title = $rowlogo->adv_title;
									$adv_description = Show_shortcontent($rowlogo->adv_description,22);
									$adv_imagewidth = $rowlogo->adv_imagewidth;
									$adv_imageheight = $rowlogo->adv_imageheight;
							?>


							<li>
								<a href="<?php echo $adv_link;?>" target="_blank"><img src="<?php echo $logopath;?>"
								alt="<?php echo $adv_title;?>" /></a>

								<h3><a href="<?php echo $adv_link;?>" target="_blank"><?php echo $adv_title;?></a></h3>

								<p><?php echo $adv_description;?><br/><a href="<?php echo $adv_link;?>"> &raquo; read more</a></p>
							</li>
								<?php } }
							else
							{
								echo '';
							}
							?>
                            </ul>
                        </div>
                        <div class="video_slider">

                            <!-- Start of slider -->
                            <div class="slider">
                                <ul class="rslides" id="slider">
								<?php
								## get the top compnay video
								$sqlvideo="select * from video_slider where adv_status='1' $strconutnry";
                                $resvideo=mysqli_query($con,$sqlvideo);
								$totalvideo = mysqli_num_rows($resvideo);
								if($totalvideo > 0)
								{
									while($row_video=mysqli_fetch_object($resvideo))
                                    {
										$cv_video_link = $row_video->adv_link;
										$adv_redirect = $row_video->adv_redirect;
										$chklink = explode("://",$cv_video_link);
										if($chklink[0]=="http" || $chklink[0]=="https")
										{
											preg_match('/[\\?\\&]v=([^\\?\\&]+)/',$cv_video_link,$matches);
											$id = $matches[1];
											$width = '100%';
											$height = '181';
											$iframe2show ='<iframe width="100%" height="181"
                                                src="https://www.youtube.com/embed/'.$id.'" frameborder="0"
                                                allowfullscreen></iframe>';
										}
										else
										{
											$iframe2show = $cv_video_link;
										}
										$bnsprof_compname = $row_video->adv_title;
										$bnsprof_address1 = $row_video->adv_description;
									?>
										 <li><?php echo $iframe2show;?>
                                        <div class="iframebox">
                                            <h2><i class="fa fa-play"></i><a href="<?php echo $adv_redirect;?>" target="_blank"><?php echo substr(($bnsprof_compname),0,22).".."?></a></h2>

                                            <p><?php echo substr(($bnsprof_address1),0,30).".."?></p>
                                        </div>
                                    </li>
									<?php }?>
								<?php } ?>
								</ul>
                            </div>
                            <!-- End of slider // -->

                            <div class="verifiedbox_bottom">
                              <h4><a href="company-video.php">   Post  <span> FREE  </span>Company Video</a></h4>

                                <div class="verifiedbox_supplierbox">
                                    <h3>Verified Suppliers</h3>

                                    <p>Selected Supplier from around the world  <span class="fright"><a href="membership_plans.php">Learn More &gt; &gt;</a></span></p>



                                    <div class="clear"></div>
                                    <ul>
                                        <li><a href="membership_plans.php" class="tooltip1"><img src="images/verified01.jpg" alt=""/>
                                        <span> <i>SPONSOR Supplier</i>
</span></a></li>
                                        <li><a href="membership_plans.php" class="tooltip1"><img src="images/verified02.jpg" alt=""/>
                                         <span> <i>SENIOR Supplier</i>
</span></a></li>
                                        <li><a href="membership_plans.php" class="tooltip1"><img src="images/verified03.jpg" alt=""/>
                                        <span> <i>JUNIOR Supplier </i>
</span></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of slider // -->

                    <div class="countrybox">
                        <div class="countrubox_top">
                            <div class="countrubox_heading">
							<div class="globalicon">Global Page <a href="#" onclick="unsetCountryLocation();"><img src="images/Untit.png" alt="Global"/></a></div>
							<h2>Top <span>Arabian Marketplaces</span></h2></div>

                            <div class="search">

                                <input type="text" id="search" name="search" class="textbox" placeholder="Search Country"/>
                                <input type="submit" value="Subscribe" id="submit" name="submit"/>

                            <script type="text/javascript">
                                $("#submit").click(function(){
                                    var cn = document.getElementById("search").value;
                                     var dataString = 'cname='+ cn;
                                $.ajax({
                                    type: "POST",
                                    url: "search_country.php",
                                    data: dataString,
                                    cache: false,
                                    success: function(data){
                                        $('#response').html(data);
                                    }
                                });
                                });
                            </script>
                                <div id="response"></div>
                            </div>
                            <div class="clear"></div>
                        </div>

 				<div class="cnt1">
 					<table>
	<tr>
		<td><span><b>Asia&nbsp;&nbsp;&nbsp;:</b></span></td>
		<td> <ul class="country">
                          <!--  <span><b>Asia</b></span>-->

							<li><a href="#" onclick="setCountryLocation(225);"><img src="images/uae.jpg" alt=""> UAE</a></li>
							<li><a href="#" onclick="setCountryLocation(187);"><img src="images/Saudi-Arabia.jpg" alt=""> Saudi Arb. </a></li>
							<li><a href="#" onclick="setCountryLocation(112);"><img src="images/Kuwait.jpg" alt=""> Kuwait</a></li>
							<li><a href="#" onclick="setCountryLocation(173);"><img src="images/Qatar.jpg" alt=""> Qatar</a></li>
							<li><a href="#" onclick="setCountryLocation(108);"><img src="images/jordan.jpg" alt=""> Jordan</a></li>
							<li><a href="#"  onclick="setCountryLocation(116);"><img src="images/Lebanon.jpg" alt=""> Lebanon</a></li>
							<li><a href="#" onclick="setCountryLocation(237);"><img src="images/yemen.jpg" alt=""> Yemen</a></li>
							<li><a href="#" onclick="setCountryLocation(101);"><img src="images/iraq.jpg" alt=""> Iraq</a></li>
							<li><a href="#" onclick="setCountryLocation(208);"><img src="images/Syria.jpg" alt=""> Syria</a></li>
							<li><a href="#" onclick="setCountryLocation(163);"><img src="images/Palestine.jpg" alt=""> Palestine</a></li>
                      		  </ul> </td>
	</tr>
</table>
					</div>

                 			     <!--<h5>Asia</h5>-->


		   		<div class="cnt1" >
		   		<table>
	<tr>
		<td> <span><b>Africa:</b></span></td>
		<td>			<ul class="country">

							<li><a href="#" onclick="setCountryLocation(63);"><img src="images/flag01.png" alt=""> Egypt</a></li>
							<li><a href="#" onclick="setCountryLocation(202);"><img src="images/sudan.jpg" alt=""> Sudan</a></li>
							<li><a href="#" onclick="setCountryLocation(119);"><img src="images/Libya.jpg" alt=""> Libya</a></li>
							<li><a href="#" onclick="setCountryLocation(142);"><img src="images/morroco.jpg" alt=""> Morocco</a></li>
							<li><a href="#" onclick="setCountryLocation(3);"><img src="images/flag02.png" alt=""> Algeria</a></li>
							<li><a href="#" onclick="setCountryLocation(217);"><img src="images/Tunisia.jpg" alt=""> Tunisia</a></li>
							<li><a href="#" onclick="setCountryLocation(133);"><img src="images/Mauritania.jpg" alt=""> Mauritania</a></li>
							<li><a href="#" onclick="setCountryLocation(58);"><img src="images/Djibouti.jpg" alt=""> Djibouti</a></li>
							<li><a href="#" onclick="setCountryLocation(196);"><img src="images/Somalia.jpg" alt=""> Somalia</a></li>
							<li><a href="#" onclick="setCountryLocation(49);"><img src="images/Comoros.jpg" alt=""> Comoros</a></li>
                        </ul></td>
	</tr>
</table></div>



                    </div>
                    <div class="space21"></div>
                    <!-- Strat of first banner -->
                    <div class="countrubox_top2">
                        <div class="countrubox_heading"><h2>View <a href="dir.php#main_cat"><span>Products &amp; Suppliers</span></a>
                        </h2></div>
                        <div class="list-rights">
                            <h2><a href="product-sel-cat.php"><span>Display</span> Your Products</a></h2>
                        </div>
                        <div class="clear"></div>
                    </div>

                    <div class="demobox">
                        <div class="col-md-12">
                            <div class="white_bg">
                                <div class="clear" style="height:5px;"></div>
                                <div class="welcome_desc">
				<div class="course_demo">
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


                                            $sql_prd = "select * from products,measurement_unit,country where mu_id=pd_unit and pd_currency=cn_id and pd_status='1' and pd_image!=''" . $sql_pd_ck . " order by rand() LIMIT 24";
                                            $res_prd = mysqli_query( $con,$sql_prd);
                                            if ($res_prd->num_rows > 0) {
                                                ?>
                                                <!--                    <div style="border:1px solid #F5ECFF;border-radius:5px;padding-left:10px;"><h2>Products</h2></div>-->



                                                <?php
                                                $useragent = $_SERVER['HTTP_USER_AGENT']; ?>


                                            <ul id="flexiselDemo1">
                                                    <?php
                                                    while ($row_prd = mysqli_fetch_object($res_prd)) {
                                                        ?>
                                                        <li>

                                                            <?php
                                                            $row_bprof = mysqli_fetch_object(mysqli_query( $con,"select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
						$sql_icon = "select sip.mst_icon,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = ".$row_bprof->bnsprof_id;
						//echo $row_bprof->bnsprof_id;
						//echo $sql_icon;
                        $get_icon = mysql_query($sql_icon) or die(mysql_error());
                                                            ?>
								<a href="company/products.php?c=<?php echo rand(1000, 9999) . md5($row_bprof->bnsprof_id); ?>&sc=<?php echo rand(10000, 99999) . $row_prd->pd_subcat_id; ?>#<?php echo $row_prd->pd_id; ?>" style="text-decoration:none;color:#000" target="_blank">
                                                                <img src="upload/myproduct/thumb/<?php echo $row_prd->pd_image; ?>" alt="<?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?>" title="<?php echo ucwords($row_prd->pd_title); ?>"  class="img-responsive"/>

                                                                <div class="matterbox">
                                                                    <div class="icon_pic">
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
																		<img src="admin/images/<?php echo $icon['mst_icon']; ?>"  title="<?php echo $title; ?>" style="width:18px; height:15px;"
                                                                                               class="img-responsive" alt=""/>
																	<?php }
																	else { ?>
																	<img src="images/slider-icon01.jpg" title="Junior"
                                                                                               class="img-responsive" alt=""/>
																	<?php } ?>																			   </div>
                                                                    <div class="rightmatter">
                                                                        <h3><?php echo ucwords(substr($row_prd->pd_title, 0, 28)); ?></h3>
                                                                      <p>Country: <span class="nam" ><?php echo get_country_name($row_prd->cn_id);?></span><br></p>
                                                                        <p>MOQ: <span class="nam" > <?php echo $row_prd->pd_min_order_qty; ?>&nbsp;<?php echo $row_prd->mu_name; ?> </span><br></p>

                                                                        <p><?php echo $row_prd->cn_currency; ?>&nbsp; <span class="nam"  style="font-size:15px !important; "> <?php echo $row_prd->pd_fob_price ?>/</span><?php echo $row_prd->mu_name; ?></p>
                                                                        <div class="clear"></div>
                                                                    </div>
                                                                    <div class="clear"></div>
                                                                </div>
								                             </a>
                                                        </li>
                                                    <?php } ?>

                                                </ul>



                                                <?php
                                            } else {
                                                ?>

                                            <?php }
                                            ?>

                                        <script type="text/javascript">
                                            $(window).load(function () {
                                                $("#flexiselDemo1").flexisel({
                                                    visibleItems: 4,
                                                    animationSpeed: 1000,
                                                    autoPlay: true,
                                                    autoPlaySpeed: 3000,
                                                    pauseOnHover: true,
                                                    enableResponsiveBreakpoints: true,
                                                    responsiveBreakpoints: {
                                                        portrait: {
                                                            changePoint: 480,
                                                            visibleItems: 1
                                                        },
                                                        landscape: {
                                                            changePoint: 640,
                                                            visibleItems: 2
                                                        },
                                                        tablet: {
                                                            changePoint: 768,
                                                            visibleItems: 2
                                                        }
                                                    }
                                                });

                                            });
                                        </script>
                                        <script type="text/javascript" src="js/jquery.flexisel.js"></script>
                                    </div>

                                    <div class="learnmores">
                                        <p><a href="dir.php#main_cat" target="_blank">View All Categories <span>&gt;&gt;</span></a></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of first banner // -->

                    <!-- Strat of second banner -->
                    <div class="demobox">
                        <div class="countrubox_top">
                            <div class="countrubox_heading"><h2>Temporary <a href="sale-offers.php"><span>Sale Offers Ads</span></a>
                            </h2></div>
                            <div class="list-rights">
                                <h2><a href="post-sell-offer.php"><span>Post</span> Sale Offers Ads</a></h2>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <div class="blank_bg">
                            <div class="welcome_desc">
                                <div class="course_demo">
                                    <?php
                            $sql_so = "select * from sale_offer,user,business_profile where so_usr_id=usr_id and usr_id=bnsprof_uid and so_approval_status='1' and so_status='1' and DATE_ADD(so_approval_date,INTERVAL so_validity DAY)>=now() " . $sql_so_ck . " order by rand()";

        $res_so = mysqli_query($con, $sql_so);
        if ($res_so->num_rows > 0) {
                                        ?>
                                    <ul id="flexiselDemo2">
                                            <?php
                                            while ($row_so = mysqli_fetch_object($res_so)) {
                                                ?>
                                                <li>
                                                    <?php
                                                    $row_bprof = mysqli_fetch_object(mysqli_query($con, "select bnsprof_id from business_profile where bnsprof_uid='" . $row_prd->pd_uid . "' limit 1"));
													$sql_icon = "select sip.mst_icon ,sip.mst_name from smembership_icon_plan sip join plan_member_id pm on sip.mp_id = pm.p_id where pm.b_id = ".$row_so->bnsprof_id;
													 $get_icon = mysql_query($sql_icon) or die(mysql_error());
                                                    ?>
                                                     <a href="saleoffer-details.php?id=<?php echo rand(1000, 9999) . md5($row_so->so_id); ?>"
                               style="text-decoration:none;color:#000" target="_blank">
                                                        <img src="upload/sale_offer/thumb/<?php echo $row_so->so_pic; ?>" alt="<?php echo ucwords(substr($row_so->so_service, 0, 25)); ?>"
                                     title="<?php echo ucwords($row_so->so_service); ?>" class="img-responsive"/>

                                                        <div class="matterbox">
                                                            <div class="icon_pic">
															<?php if(mysql_num_rows($get_icon) > 0){
															$title = 'Junior';
				$icon = mysql_fetch_array($get_icon);

				if(strpos(strtolower($icon['mst_name']), 'senior') !== false || strpos(strtolower($icon['mst_name']), 'senier') !== false) {
				$title = 'Senior';
				}
				else if(strpos(strtolower($icon['mst_name']), 'sponsor') !== false || strpos(strtolower($icon['mst_name']), 'sponser') !== false) {
				$title = 'Sponsor';
				}
				$title = 'Junior';
				$icon = mysql_fetch_array($get_icon);

				?>
																		<img src="admin/images/<?php echo ($icon['mst_icon'] != '')?$icon['mst_icon']:'slider-icon01.jpg'; ?>" style="width:18px; height:15px;"  title="<?php echo $title; ?>" class="img-responsive" alt=""/>
																	<?php }
																	else { ?>
                                                                <img src="images/slider-icon01.jpg" class="img-responsive" alt=""title="Junior" />
																	<?php } ?>
                                                            </div>
                                                            <div class="rightmatter">
                                                                <h3><?php echo ucwords(substr($row_so->so_service, 0, 25)); ?><?php if (strlen($row_so->so_service) > 26) { ?>...<?php } ?></h3>

                                                                <p><?php
                                        //	$sql_cat="select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id='".$row_so->so_pc_id."')";
                                        $sql_cat = "select * from product_category where pc_id=(select pc_parent_id from product_category where pc_id=(select pc_parent_id from product_category where pc_id='" . $row_so->so_pc_id . "'))";
                                        $res_cat = mysqli_query($con, $sql_cat);
                                        $row_cat = mysqli_fetch_object($res_cat);
                                        echo "(" . $row_cat->pc_name . ")"; ?><br></p>

                                                                <p><?php echo substr($row_so->so_description, 0, 25); ?><?php if (strlen($row_so->so_description) > 26) { ?>...<?php } ?></p>
                                                                <div class="clear"></div>
                                                            </div>
                                                            <div class="clear"></div>
                                                        </div>
                                                    </a>
                                                </li>

                                            <?php } ?>

                                        </ul>

                                        <?php
                                    } else {
                                        ?>

                                    <?php }
                                    ?>
                                    <script type="text/javascript">
                                        $(window).load(function () {
                                            $("#flexiselDemo2").flexisel({
                                                visibleItems: 4,
                                                animationSpeed: 1000,
                                                autoPlay: true,
                                                autoPlaySpeed: 3000,
                                                pauseOnHover: true,
                                                enableResponsiveBreakpoints: true,
                                                responsiveBreakpoints: {
                                                    portrait: {
                                                        changePoint: 480,
                                                        visibleItems: 1
                                                    },
                                                    landscape: {
                                                        changePoint: 640,
                                                        visibleItems: 2
                                                    },
                                                    tablet: {
                                                        changePoint: 768,
                                                        visibleItems: 2
                                                    }
                                                }
                                            });

                                        });
                                    </script>
                                </div>
                                <div class="learnmores">
                                    <p><a href="sale-offers.php" target="_blank">View all sale Offers <span>&gt;&gt;</span></a></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- End of second banner // -->

                    <div class="center-top">

						<?php
						$banner=GetHomeBanner('middle');
						if($banner!="")
						{
					       echo '<div class="middle mid-content" style="padding:0;">';
						  echo $banner;
						}
						else
						{
							echo '<div class="middle mid-content">';
							echo ' <h3>Banner Place</h3>';
						}
						?>
						<div class="clear"></div>
					 </div>
                    </div>
                    <!-- Start of Third banner -->
                    <div class="demobox">
                        <div class="booking">
                            <a href="advertise-with-us.php">You Can Advise Here <span>FREE</span> in Primacy Booking</a>
                        </div>
                        <div class="clear"></div>
                        <div class="countrubox_top2">
                            <div class="countrubox_heading">
                               <div class="mainflagbox">
                                    <div class="membershipicon"><a href="membership_plans.php"><img src="images/membership-icon01.png"
                                                                                 alt=""/></a></div>
                                    <div class="membershipicon"><a href="#"><img src="images/membership_icon02.png"
                                                                                 alt=""/></a></div>
                                </div>
                                <div class="countryheadingboxleft">
                                    <h3><a href="#">EgyptMART Leading Products</a></h3>
                                </div>


                            </div>
                            <div class="list-rights">
                                <!--<h2><a href="contact_us.php" target="_blank"><span>Post</span> Premium Ads</a></h2>-->
                                 <h2><a href="advertise-with-us.php" target="_blank"><span>Post</span> Premium Ads</a></h2>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="col-md-12">
                            <div class="white_bg">
                                <div class="clear" style="height:5px;"></div>
                                <div class="welcome_desc">
                                    <div class="course_demo">
									<?php

										$sqlleading = "select * from prodservice_slider where adv_status='1' and adv_type='1' $strconutnry";
										$rsleading = mysqli_query($con,$sqlleading);
										$totalbaneer = mysqli_num_rows($rsleading);
										$rembaner = $totalbaneer%2;
										if($totalbaneer> 0)
										{?>
									     <ul id="flexiselDemo4">
										 <?php
										 $indx=1;
										 while($rowleading = mysqli_fetch_object($rsleading))
											{
												$adv_id = $rowleading->adv_id;
												$pd_image = $rowleading->adv_img;
												$pd_title = $rowleading->adv_title;
												$pd_fob_price = $rowleading->adv_price;
												$adv_imagewidth = $rowleading->adv_imagewidth;
												$adv_imageheight = $rowleading->adv_imageheight;
												$adv_icon = $rowleading->adv_icon;
												$adv_link = $rowleading->adv_link;

												$adv_piece = $rowleading->adv_piece;
												$adv_currency = $currency_symbols[$rowleading->adv_currency];
												if($indx%2==1){ echo '<li>';}
										 ?>
										<a href="<?php echo $adv_link;?>" target="_blank"><div class="demobox">
                                                    <img src="upload/product_slider/<?php if($pd_image!=''){ echo $pd_image; }else{ echo 'noimage.jpg';	} ?>" class="black" alt="" />
                                                    <div class="matterbox">
                                                        <div class="icon_pic"><img src="images/<?php echo $adv_icon;?>"
                                                                                   class="img-responsive" alt="" width="18"/></div>
														<div class="rightmatter">
														<h3><?php echo $pd_title;?></h3>
														<p>Country: <span class="nam" ><?php echo $cn_name;?> </span><br></p>
														<p>MOQ: <span class="nam" > <?php echo $adv_piece;?> Pyeces</span><br></p>
														<p><?php //echo $cn_name;?><span><ins><?php echo $adv_currency;?></ins><?php echo $pd_fob_price;?></span> / PIECES</p>
														<div class="clear"></div>
														</div>

                                                        <div class="clear"></div>
                                                    </div>
                                        </div></a>

									<?php
									if($indx%2==0){ echo '</li>';}
									$indx++;
									}
									if($rembaner==1){ echo '</li>';}
									?>
										</ul><?php }?>
                                        <script type="text/javascript">
                                            $(window).load(function () {
                                                $("#flexiselDemo4").flexisel({
                                                    visibleItems: 4,
                                                    animationSpeed: 1000,
                                                    autoPlay: true,
                                                    autoPlaySpeed: 3000,
                                                    pauseOnHover: true,
                                                    enableResponsiveBreakpoints: true,
                                                    responsiveBreakpoints: {
                                                        portrait: {
                                                            changePoint: 480,
                                                            visibleItems: 1
                                                        },
                                                        landscape: {
                                                            changePoint: 640,
                                                            visibleItems: 2
                                                        },
                                                        tablet: {
                                                            changePoint: 768,
                                                            visibleItems: 2
                                                        }
                                                    }
                                                });

                                            });
                                        </script>
                                    </div>
                                </div>
                                <div class="clear" style="height:1px;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- End of Third banner // -->


                    <!-- Start of fourth banner -->
                    <div class="demobox">
                        <div class="countrubox_top3">
                            <div class="countrubox_heading">
    <div class="mainflagbox">
                                    <div class="membershipicon2"><a href="#"><img src="images/membership_icon03.png"
                                                                                  alt=""/></a></div>
                                </div>
                                <div class="countryheadingboxleft">
                                    <h3><a href="advertise-with-us.php"><span>Loyal</span> Business Services</a></h3>
                                </div>

                            </div>
                            <div class="list-rights">
                                <h2><a href="product-sel-cat.php" target="_blank"><span>Post</span> Business Services</a></h2>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <div class="col-md-12">
                            <div class="bottom_bg">
                                <div class="welcome_desc">
                                    <div class="course_demo">
                                        <?php

										$sqlleading = "select * from prodservice_slider where adv_status='1' and adv_type='2' $strconutnry";
										$rsleading = mysqli_query($con,$sqlleading);
										$totalbaneer = mysqli_num_rows($rsleading);
										$rembaner = $totalbaneer%2;
										if($totalbaneer> 0)
										{?>
									     <ul id="flexiselDemo5">
										 <?php
										 $indx=1;
										 while($rowleading = mysqli_fetch_object($rsleading))
											{
												$adv_id = $rowleading->adv_id;
												$pd_image = $rowleading->adv_img;
												$pd_title = $rowleading->adv_title;
												$pd_fob_price = $rowleading->adv_price;
												$adv_imagewidth = $rowleading->adv_imagewidth;
												$adv_imageheight = $rowleading->adv_imageheight;
												$adv_icon = $rowleading->adv_icon;
												$adv_link = $rowleading->adv_link;
												$adv_piece = $rowleading->adv_piece;
												$adv_currency = $currency_symbols[$rowleading->adv_currency];
												if($indx%2==1){ echo '<li>';}
										 ?>
										 <a href="<?php echo $adv_link;?>" target="_blank"><div class="demobox">
                                                    <img src="upload/service_slider/<?php if($pd_image!=''){ echo $pd_image; }else{ echo 'noimage.jpg';	} ?>" class="black" style="max-width: 115px" alt="" />
                                                    <div class="matterbox">
                                                        <div class="icon_pic"><img src="images/<?php echo $adv_icon;?>"
                                                                                   class="img-responsive" alt="" width="18"/></div>
                                                        <div class="rightmatter">
														<h3><?php echo $pd_title;?></h3>
														<p>Country: <span class="nam"><?php echo $cn_name;?></span><br></p>
														<p>MOQ: <span class="nam"><?php echo $adv_piece;?> Pyeces</span><br></p>
														<p><?php //echo $cn_name;?> <span><ins><?php echo $adv_currency;?></ins><?php echo $pd_fob_price;?></span> / PIECES</p>
														<div class="clear"></div>
														</div>
                                                        <div class="clear"></div>
                                                    </div>
                                                </div></a>

									<?php
									if($indx%2==0){ echo '</li>';}
									$indx++;
									}
									if($rembaner==1){ echo '</li>';}
									?>
										</ul><?php }?>
                                        <script type="text/javascript">
                                            $(window).load(function () {
                                                $("#flexiselDemo5").flexisel({
                                                    visibleItems: 5,
                                                    animationSpeed: 1000,
                                                    autoPlay: true,
                                                    autoPlaySpeed: 3000,
                                                    pauseOnHover: true,
                                                    enableResponsiveBreakpoints: true,
                                                    responsiveBreakpoints: {
                                                        portrait: {
                                                            changePoint: 480,
                                                            visibleItems: 1
                                                        },
                                                        landscape: {
                                                            changePoint: 640,
                                                            visibleItems: 2
                                                        },
                                                        tablet: {
                                                            changePoint: 768,
                                                            visibleItems: 2
                                                        }
                                                    }
                                                });

                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of fourth banner // -->


                    <!-- Start of bottom -->
                    <div class="demobox">
                        <div class="countrubox_top4">
                            <div class="countrubox_heading">
                                <div class="countryheadingboxleft"><h3><a href="#">Sponsors Supplier</a></h3></div>
                            </div>
                            <div class="list-rights">
                             <!--   <h2><a href="contact_us.php" target="_blank"><span>Add</span> Your Logo</a></h2>-->
                                  <h2><a href="contact_us.php" target="_blank"><span>Add</span> Your Logo</a></h2>
                            </div>
                            <div class="clear"></div>
                        </div>

                        <div class="whitefooter">
                            <ul>
							<?php

							$sqllogo = "select * from supplier_logo where adv_status='1' and adv_img!='' $strconutnry order by adv_updated_date desc";
							$rslogo = mysqli_query($con,$sqllogo);
							if(mysqli_num_rows($rslogo) > 0)
							{
								while($rowlogo= mysqli_fetch_object($rslogo))
								{
									$adv_img = $rowlogo->adv_img;
									$logopath = "upload/supplier_logo/".$adv_img;
									$adv_link = $rowlogo->adv_link;
							?>
								<li><a href="<?php echo $adv_link;?>" target="_blank"><img src="<?php echo $logopath;?>" class="img-responsive" alt=""/></a>
                                </li>
								<?php } }
							else
							{
								echo '';
							}
							?>


                            </ul>
                            <div class="clear"></div>
                        </div>

                        <!--
                        <div class="bottomservices">
                          <div class="listproductsleftbox">

                         <div class="bottomproduct">
                           <div class="bottomproduct_top">
                             <div class="star_pic"><a href="#"><img src="images/star01.jpg" alt="" /></a></div>
                             <div class="star_content"><h3>List Your Products / Services</h3></div>
                             <div class="clear"></div>
                           </div>
                           <p>Make Buyers know everything about
              your Product / Services</p>
                         </div>

                          </div>
                          <div class="rightarrowdiv"><i class="fa fa-chevron-right"></i></div>
                        </div>



                        <div class="bottomservices">
                         <div class="listproductsleftbox">

                         <div class="bottomproduct">
                           <div class="bottomproduct_top">
                             <div class="star_pic"><a href="#"><img src="images/star02.jpg" alt="" /></a></div>
                             <div class="star_content"><h3>Get Buy Inquiries</h3></div>
                             <div class="clear"></div>
                           </div>
                           <p>Make Buyers know everything about
              your Product / Services</p>
                         </div>
                          </div>
                          <div class="rightarrowdiv"><i class="fa fa-chevron-right"></i></div>
                        </div>

                        <div class="bottomservices">
                         <div class="listproductsleftbox">
                         <div class="bottomproduct">
                           <div class="bottomproduct_top">
                             <div class="star_pic"><a href="#"><img src="images/star03.jpg" alt="" /></a></div>
                             <div class="star_content"><h3>Double Your Profits</h3></div>
                             <div class="clear"></div>
                           </div>
                           <p>Make Buyers know everything about
              your Product / Services</p>
                         </div>

                          </div>
                        </div>

                    </div>
                    <div class="clear"></div>

                    <div class="subscribenow"><h3><a href="#">Subscribe NOW &gt; &gt;</a></h3></div>

                    <!-- Start of rowbanner -->
                    <div class="mid-top">

						<?php
						$banner=GetHomeBanner('bottom',$strconutnry);
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
							 //echo '</div>';
						}
						?>
						<div class="clear"></div>
                    </div>
                    <!-- End of rowbanner // -->

                </div>
                    </div>


                <div id="rightsection">

                    <div class="buyleads">
                       <!-- <div class="leftleads"><h2><a href="search.php?keywords=processor&rctyp=buy_lead">Buy Leads &nbsp;<i class="fa fa-caret-right"></i></a>-->
                         <div class="leftleads"><h2><a href="buyleads.php"> Live Buy Leads &nbsp;<i class="fa fa-caret-right"></i></a>
                        </h2></div>
                        <?php
                         $sql_br1 = "select * from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand()";
                        $res_br1 = mysqli_query($con, $sql_br1);
                        $cnt_br1 = $res_br1->num_rows;

                        $sql_br = "select * from buy_requirement,user where br_u_id=usr_id and br_approval_status='1' and br_status='1' and br_display_status='1' " . $sql_br_ck . " order by rand() limit 0, 5";
                        $res_br = mysqli_query($con, $sql_br);
                        $cnt_br = $res_br->num_rows;
                        if ($cnt_br > 0) {
                        ?>
                        <div class="bx2 bgc1 brd ">
                            <?php
                            $x = (int)$cnt_br1;
                            $c = (int)0;
                            while ($x != 0) {
                                $x = (int)$x / 10;
                                $c = (int)$c + 1;
                            }
                            $str = "";
                            for ($i = $c; $i <= 6; $i++) {
                                $str = $str . '0';
                            }
                            //		echo $str;
                            ?>
                        <div class="rightnumber">
                            <span class="tic"><?php echo $str . $cnt_br1; ?></span>
                            <span class="tic1 off"><?php echo $cnt_br1; ?></span>
                        </div>
                        <div class="clear"></div>

                     <!--<div style="position: fixed;left: 1em;top: 50%;width: 24em;margin-top: 0em;background-color: #C7C6C8;">
    	<img src="images/caller.png" style="position: absolute;width: 84px;height: 94px;left: 7px;border: 1px solid black; ">
    <div style="margin-left: 7em;"><h2 style="">Dina - <span style="color: black">Workable Agent</span></h2>
    	<p style="padding: 10px 0 8px 12px; font-weight: 600"> 24 Hours Queries  Response</p></div>

        <textarea class="txtar" placeholder="Ask Question" rows="5"></textarea>
        <input type="submit" value="Submit" name="question" class=" btn btn-default pull-right" />
        <br/><br/>

    	</div>-->
                        <div class="buybox">
                            <?php
							while ($row_br = mysqli_fetch_object($res_br)) {
							?>
                            <div class="popular-post-grid">
                                <h3><a href="buyleads-details.php?id=<?php echo rand(1000,9999).md5($row_br->br_id); ?>" target="_blank"><?php echo ucwords(stripslashes($row_br->br_pd_name)); ?></a></h3>

                                <div class="tendersbox">
                                    <div class="verifiedbox">
                                        <div class="cover"><img src="images/tick.png" alt=""/> Verified &amp; Updated
                                        </div>
                                        <div class="date">
                                            <?php if ($row_br->br_estimate_qty != '0' && $row_br->br_estimate_qty != '') { ?>                                  <b>Quantity :</b> <?php echo $row_br->br_estimate_qty; ?>&nbsp;<?php echo measurement_unit($row_br->br_estimate_qty_unit); ?>(s).
                                            <?php } ?>
                                        </div>
                                    </div>
									<div class="flagbox">
									<?php
									 $contyname=get_country_name($row_br->country);
									 $cntryflag = get_country_flag($row_br->country);
									 if($cntryflag!="")
									 {
										 $flag2show = '<img src="images/country_flag/'.$cntryflag.'" alt="">';
									 }
									?>
										<ul>
											<li style="height:auto";><a href="#"><?php echo $contyname." ".$flag2show;?> </a></li>
										</ul>

										<div class="date">
										  <span>
										   <?php
											if($row_br->br_preferred_supplier_location == '')
												{
													echo get_country_name($row_br->country);
												}else
												{
													if ($row_br->br_preferred_supplier_location == 'any') {
														echo "Anywhere";
													} else if ($row_br->br_preferred_supplier_location == 'abroad') {
														echo "Foreign";
													} else if ($row_br->br_preferred_supplier_location == 'domestic') {
														echo get_country_name($row_br->country);
													} else if ($row_br->br_preferred_supplier_location == 'my_city' && $row_br->bnsprof_city != '0') {
														echo get_city_name($row_br->bnsprof_city);
													}
												}
										   ?>
										  </span>
										</div>
                                     </div>

                                </div>
                                <div class="clear"></div>
                            </div>
                            <?php } ?>

                           <!-- <div class="learnmore"><p><a href="search.php?keywords=processor&rctyp=buy_lead" target="_blank">All Live Buy Leads <span>&gt;&gt;</span></a></p></div>-->
  <div class="learnmore"><p><a href="buyleads.php" target="_blank">All Live Buy Leads <span>&gt;&gt;</span></a></p></div>
                        </div>
                        <div class="clear"></div>
                    </div>
                        <?php } ?>

						<br><b>


                    <!-- Start of Tabs -->
                    <div class="sap_tabs">
                        <div id="horizontalTab" style="display: block; width: 100%; margin: 0px;">
                            <ul class="resp-tabs-list">
                                <li class="resp-tab-item" aria-controls="tab_item-0" role="tab">
                                    <a href="tenders.php"><span><b>Tenders</b></span></a></li>
                                <li class="resp-tab-item" aria-controls="tab_item-1" role="tab">
                                <a href="auctions.php"><span><b>Auctions</b></span></a></li>
                                <div class="clear"></div>
                            </ul>
                            <div class="resp-tabs-container">
                                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-0">

                                        <?php
										if($cn_id!="")
										 {
											 $strtender=" AND user.country='$cn_id'";
										 }
										 else
										 {
											 $strtender ="";
										 }
										$sqltender = "select tnd_id,tnd_heading,tnd_docSaleEnd_date,tnd_preferred_location,country from tender,user where tnd_status='1' and tender.tnd_usr_id=user.usr_id $strtender order by tnd_publish_date DESC LIMIT 4";
										$rstender = mysqli_query($con,$sqltender) or die("Error".mysqli_erorr());
										if(mysqli_num_rows($rstender))
										{
											echo '<ul class="tab_img">';
											while($rowtender = mysqli_fetch_object($rstender))
											{
												$tnd_heading = $rowtender->tnd_heading;
												$tnd_docSaleEnd_date= $rowtender->tnd_docSaleEnd_date;

										?>
										<li>
                                            <div class="popular-post-grids">
                                                <div class="popular-post-grid">
                                                    <h3><a href="tender-details.php?id=<?php echo rand(1000,9999).md5($rowtender->tnd_id); ?>" target="_blank"><?php echo $tnd_heading;?></a></h3>

                                                    <div class="tendersbox">
                                                        <div class="verifiedbox">
                                                            <div class="cover"><img src="images/tick.png" alt=""/>
                                                                Verified &amp; Updated
                                                            </div>
                                                            <div class="date"><b>Date of Document :</b> <?php echo $tnd_docSaleEnd_date;?>
                                                            </div>
                                                        </div>
                                                        <div class="flagbox">
									<?php
									 $contyname=get_country_name($rowtender->country);
									 $cntryflag = get_country_flag($rowtender->country);
									 if($cntryflag!="")
									 {
										 $flag2show = '<img src="images/country_flag/'.$cntryflag.'" alt="">';
									 }
									?>
										<ul>
											<li><a href="#"><?php echo $contyname." ".$flag2show;?> </a></li>
										</ul>

										<div class="date">
										  <span>
										   <?php
											if($rowtender->tnd_preferred_location == '')
												{
													echo get_country_name($rowtender->country);
												}else
												{
													if ($rowtender->tnd_preferred_location == 'any') {
														echo "Anywhere";
													} else if ($rowtender->tnd_preferred_location == 'abroad') {
														echo "Foreign";
													} else if ($rowtender->tnd_preferred_location == 'domestic') {
														echo get_country_name($rowtender->country);
													} else if ($rowtender->tnd_preferred_location == 'my_city' && $rowtender->country != '0') {
														echo get_city_name($rowtender->country);
													}
												}
										   ?>
										  </span>
										</div>
                                     </div>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </li>
										<?php } ?>
										<div class="clear"></div>
                                        <div class="learnmore"><p><a href="#">View all <a href="auctions.php">Tender</a> / <a
                                                href="auctions.php">Auctions</a></a></p></div>

                                    </ul>

                                      <div class="tabbotton">
                                            <a href="#"><span>Publish</span> <a href="post-tender.php">Tender</a>/</a>
                                            <a href="post-auction.php">Auction</a>
                                            <span>FREE</span>
                                        </div>
								<?php }?>
                                </div>
                                <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-1">
                                    <ul class="tab_img">
                                        <?php
										if($cn_id!="")
										 {
											 $strauction=" AND user.country='$cn_id'";
										 }
										 else
										 {
											 $strauction ="";
										 }
										$sqltender = "select auc_id,auc_heading,auc_docSaleEnd_date,auc_preferred_location,country from auction,user where auc_status='1' and auction.auc_usr_id=user.usr_id $strauction order by auc_publish_date DESC LIMIT 5";
										$rstender = mysqli_query($con,$sqltender) or die("Error".mysqli_erorr());
										if(mysqli_num_rows($rstender))
										{
											echo '<ul class="tab_img">';
											while($rowaution = mysqli_fetch_object($rstender))
											{
												$tnd_heading = $rowaution->auc_heading;
												$tnd_docSaleEnd_date= $rowaution->auc_docSaleEnd_date;

										?>
										<li>
                                            <div class="popular-post-grids">
                                                <div class="popular-post-grid">
                                                    <h3><a href="auction-details.php?id=<?php echo rand(1000,9999).md5($rowaution->auc_id); ?>" target="_blank"><?php echo $tnd_heading;?></a></h3>

                                                    <div class="tendersbox">
                                                        <div class="verifiedbox">
                                                            <div class="cover"><img src="images/tick.png" alt=""/>
                                                                Verified &amp; Updated
                                                            </div>
                                                            <div class="date"><b>Date of Document :</b> <?php echo $tnd_docSaleEnd_date;?>
                                                            </div>
                                                        </div>
                                                        <div class="flagbox">
									<?php
									 $contyname=get_country_name($rowaution->country);
									 $cntryflag = get_country_flag($rowaution->country);
									 if($cntryflag!="")
									 {
										 $flag2show = '<img src="images/country_flag/'.$cntryflag.'" alt="">';
									 }
									?>
										<ul>
											<li><a href="#"><?php echo $contyname." ".$flag2show;?> </a></li>
										</ul>

										<div class="date">
										  <span>
										   <?php
											if($rowaution->auc_preferred_location == '')
												{
													echo get_country_name($rowaution->country);
												}else
												{
													if ($rowaution->auc_preferred_location == 'any') {
														echo "Anywhere";
													} else if ($rowaution->auc_preferred_location == 'abroad') {
														echo "Foreign";
													} else if ($rowaution->auc_preferred_location == 'domestic') {
														echo get_country_name($rowaution->country);
													} else if ($rowaution->auc_preferred_location == 'my_city' && $rowaution->country != '0') {
														echo get_city_name($rowaution->country);
													}
												}
										   ?>
										  </span>
										</div>
                                     </div>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </li>
								<?php } ?>
								  </ul>
								<?php }?>
                                        <div class="clearfix"></div>
                                    </ul>
                                </div>

                            </div>
                        </div>
                    </div>


                    <!-- End of Tabs // -->

                    <!-- Start of Tabs -->
                    <div class="sap_tabs">
                        <div id="horizontalTab1" style="display: block; width: 100%; margin: 0px;">
                            <ul class="resp-tabs-list">
                                <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><span><h5>For
                                    Buying</h5></span></li>
                                <li class="resp-tab-item" aria-controls="tab_item-1" role="tab"><span><h5>For
                                    Supplying</h5></span></li>
                     %