<?php
   error_reporting(0);
   ob_start();
   session_start();
   
   include 'common.php';
   
   $uid=$_SESSION['uid_indm'];
   $globalcntid = 243;
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
     $cn_id = 0;
     $cn_name="Global";
   }
   ini_set('display_errors', 1);
   error_reporting(E_ALL & ~E_NOTICE);
   ## query for country
   if($cn_id!="" && $cn_id > 0)
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Welcome :: ARABYOS</title>
<link href="css/new/css/bootstrap.css" rel='stylesheet' type='text/css' />
<script src="js/new_js/jquery.min.js" type="text/javascript"></script>
<!-- Custom Theme files -->
<link href="css/new/css/style.css" rel="stylesheet" type="text/css" />
<link href="css/new/css/custom.css" rel="stylesheet" type="text/css" />
<link href=css/im-style-v1.css rel=stylesheet>
<link href="css/new/css/responsive.css" rel="stylesheet" type="text/css" />
<!-- Custom Theme files //  -->

<link href="fonts/font-awesome.css" rel="stylesheet" type="text/css" />

<!--[if IE]> <script src="js/new_js/html5.js"></script> <![endif]-->

<!-- start of verticle menu -->
<link href="css/new/css/verticle-menu.css" rel="stylesheet" type="text/css" />
<!-- End of verticle menu -->

<!-- Start of video/testimonial slider -->
<script src="js/new_js/responsiveslides.min.js"></script>
<script>
    $(function () {
	
	 // Slideshow 1
      $("#slider").responsiveSlides({
      	auto: true,
      	nav: false,
      	speed: 500,
        namespace: "callbacks",
        pager: true,
      });
	  	  
    });
</script>
<!-- End of video/testimonial slider // -->

<!-- Start of yahoo slider -->
<link type="text/css" rel="stylesheet" href="css/new/css/theme.css"/>
<script type="text/javascript" src="js/new_js/jquery.accessible-news-slider.js"></script>
<script type="text/javascript">
// when the DOM is ready, conv the feed anchors into feed content
jQuery(document).ready(function() {

	jQuery('#newsslider').accessNews({
	
	});

	jQuery('#newsslider2').accessNews({
		title : "BREAKING NEWS:",
		subtitle:"stories from the internet",
		speed : "slow",
		slideBy : 5,
		slideShowInterval: 100000,
		slideShowDelay: 100000
	});

});
</script>
<!-- End of yahoo slider // -->

</head>
<body>
<!-- Start of wrapper -->
<div class="wrapper">
<?php include "includes/responsive_header.php"; ?>

 
 
<!-- Start of rowbanner -->  
<div class="toplist">
 <div class="middle-bar">
   
    <!-- Start of rowbanner --> 
   <div class="centertopbanner">
     <div class="middle mid-content">
       <h3>Banner Place <br/> Stands out only When Admin adds</h3>
      <div class="clear"></div>
   </div> 
   </div>
   <!-- End of rowbanner // --> 
 
 </div>
</div>
<!-- End of rowbanner // --> 

<!-- Start of middlesection -->
<div class="middlesection">
 <div class="maincontainer">
 <div class="demobox">
  <div id="leftsection">  
     <div id="block_navigation">
		<div id="pull" style="display: none;">
			<a href="#"> <i class="icon-reorder"></i>Menu</a>
		</div>
	<ul class="navigation">
		<h3><a href="#"><i class="fa fa-list-ul"></i><span>A</span>ll Categories</a></h3>
		    <li><a href="#">
			 <i class="icon-list"></i><span>A</span>griculture <span class="main_links_span"></span></a>
             
             <div class="typography_3_colm">
					<div class="colm_3_container">
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a href="#">Agricultural Growing Media</a></li>
                                <li class="topnone"><a href="#">Agricultural Waste</a></li>
                                <li class="topnone"><a href="#">Agrochemicals</a></li>
                                <li class="topnone"><a href="#">Animal Products</a></li>
                                <li class="topnone"><a href="#">Beans</a></li>
                                <li class="topnone"><a href="#">Coffee Beans</a></li>
                                <li class="topnone"><a href="#">Farm Machinery &amp; Equipment</a></li>
                                <li class="topnone"><a href="#">Feed</a></li>
							</ol>
						</div>
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
							   <li class="topnone"><a href="#">Fresh Seafood</a></li>
                               <li class="topnone"><a href="#">Fruit</a></li>
                               <li class="topnone"><a href="#">Garden Tools</a></li>
                               <li class="topnone"><a href="#">Grain</a></li>
                               <li class="topnone"><a href="#">Grain Products</a></li>
                               <li class="topnone"><a href="#">Mushrooms &amp; Truffles</a></li>
                               <li class="topnone"><a href="#">Nuts &amp; Kernels</a></li>
                               <li class="topnone"><a href="#">Organic Produce</a></li>
							</ol>
						</div>
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
							   <li class="topnone"><a href="#">Ornamental Plants</a></li>
                               <li class="topnone"><a href="#">Other Agriculture</a></li>
                               <li class="topnone"><a href="#">Other Agriculture Products</a></li>
                               <li class="topnone"><a href="#">Other Beans</a></li>
                               <li class="topnone"><a href="#">Plant &amp; Animal Oil</a></li>
                               <li class="topnone"><a href="#">Plant Seeds &amp; Bulbs</a></li>
                               <li class="topnone"><a href="#">Timber Raw Materials</a></li>
                               <li><a href="#">Vegetables</a></li>
							</ol>
						</div>
					</div>
				</div>
             
             
             
             
             </li>
         
         <li>
			<a href="#"><i class="icon-reorder"></i><span>A</span>pparel<span class="main_links_span"></span></a>
				<div class="typography_3_colm">
					<div class="colm_3_container">
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a>Translation Ready</a></li>
								<li><a>Custom Widgets</a></li>
								<li><a>Font Options</a></li>
								<li><a>Elastic Sliders</a></li>
								<li><a>User Reviews</a></li>
							</ol>
						</div>
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a>Translation Ready</a></li>
								<li><a>Custom Widgets</a></li>
								<li><a>Font Options</a></li>
								<li><a>Elastic Sliders</a></li>
								<li><a>User Reviews</a></li>
							</ol>
						</div>
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a href="#">Translation Ready</a></li>
								<li><a href="#">Custom Widgets</a></li>
								<li><a href="#">Font Options</a></li>
								<li><a href="#">Elastic Sliders</a></li>
								<li><a href="#">User Reviews</a></li>
							</ol>
						</div>
					</div>
				</div>
			</li>
         <li><a href="#"><i class="icon-list"></i><span>A</span>utomobiles &amp; Motorcycles<span class="main_links_span"></span></a>
          
           <div class="typography_3_colm">
					<div class="colm_3_container">
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a>Translation Ready</a></li>
								<li><a>Custom Widgets</a></li>
								<li><a>Font Options</a></li>
								<li><a>Elastic Sliders</a></li>
								<li><a>User Reviews</a></li>
							</ol>
						</div>
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a>Translation Ready</a></li>
								<li><a>Custom Widgets</a></li>
								<li><a>Font Options</a></li>
								<li><a>Elastic Sliders</a></li>
								<li><a>User Reviews</a></li>
							</ol>
						</div>
						<div class="colmn_3_fullwidth">
							<ol class="some_links">
								<li><a href="#">Translation Ready</a></li>
								<li><a href="#">Custom Widgets</a></li>
								<li><a href="#">Font Options</a></li>
								<li><a href="#">Elastic Sliders</a></li>
								<li><a href="#">User Reviews</a></li>
							</ol>
						</div>
					</div>
				</div>
         
         </li>
         <li><a href="#"><i class="icon-list"></i><span>B</span>eauty &amp; Personal Care<span class="main_links_span"></span></a></li>
         <li class="active"><a href="#"><i class="icon-list"></i><span class="red"> <span class="br-blue">B</span>usiness Services</span> <span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>C</span>hemicals<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>C</span>omputer<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>C</span>onstruction &amp; Real Estate<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>C</span>onsumer Electronics<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>E</span>lectronic Components<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>E</span>yewear, Jewelry, Watch<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>F</span>ashion Accessories<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>F</span>ood & Beverage<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>F</span>urniture<span class="main_links_span"></span></a></li>	
         
         <li><a href="#"><i class="icon-list"></i><span>G</span>ifts & Crafts<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>H</span>ealth & Medical<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>H</span>ome Appliances<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>H</span>ome &amp; Garden<span class="main_links_span"></span></a></li>	
         <li><a href="#"><i class="icon-list"></i><span>L</span>ights &amp; Lighting<span class="main_links_span"></span></a></li>
         
         <li><a href="#"><i class="icon-list"></i><span>L</span>uggages, Bags, Cases<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>M</span>achinery<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>M</span>echanical Parts<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>M</span>inerals &amp; Metallurgy<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>P</span>ackging &amp; Printing<span class="main_links_span"></span></a></li>
         
         <li><a href="#"><i class="icon-list"></i><span>S</span>ecurity &amp; Protection<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>S</span>hoes &amp; Accessories<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>S</span>ports &amp; Entertainment<span class="main_links_span"></span></a></li>
         <li><a href="#"><i class="icon-list"></i><span>T</span>extiles &amp; Leather Products<span class="main_links_span"></span></a></li>
         
         <p><a href="#">View All Categories <span>&gt;&gt;</span></a></p>	
    </ul>
</div>

<div class="list-top">
        <h1><a href="#"><img src="images/wholesaler.jpg" alt="" /></a></h1>
 <h5>Set Your</h5>
 <div class="showcase">Products Showcase</div>
 <p>Distribute in Your City</p>
         
  </div> 

<div class="map-tops">
   <div class="map"><a href="#"><a href="#"><img src="images/map.jpg" alt="" /></a></a></div> 
  </div>
  
  <div class="list-top">
     <div class="seniorbox">
        <div class="siniorlistbox">
          <div class="siconbox"><img src="images/left-icon.png" alt="" /></div>
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
           <p><a href="#" style="fright">Learn More <span>&gt; &gt;</span></a></p>
         </ul>
         
         <h3><a href="#">Upgrade Now</a></h3>
     </div>
  </div>


  <div class="mid-tops">
   <div class="middle mid-center">
       <h2>Advisement Place</h2>
      <div class="clear"></div>
   </div> 
  </div>
  <div class="clear"></div>     
 </div>
  
        <div id="midcenter">
        
         <!-- Start of slider -->
         <div class="slider">
            <div class="yahoo_slider">
            
             <ul id="newsslider">
		<li>
			<a href="#"><img src="images/luca-farulli-master-multimedia.jpg" alt="Luca Farulli" /></a>
			<h3><a href="#">Luca Farulli interview</a></h3>
			<p>Luca Farulli, professor of Aesthetics at the Academy of Fine Arts of Venice, is the owner of the course of aesthetics of digital art and coordinator <br /><a href="#"> &raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/diego-mencarelli-master-multimedia.jpg" alt="Diego Mencarelli" /></a>
			<h3><a href="#" title="This should be the title text">Diego Mencarelli interview</a></h3>
			<p>Diego Mencarelli, new media consultant at Unicoop Tirreno, co-teaches a course on human-machine interface design, in particular the module dedicated <br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a class="title" href="#"><img src="images/luca-bonacorsi-master-multimedia.jpg" alt="Luca Bonacorsi" /></a>
			<h3><a href="#">Luca Bonacorsi interview</a></h3>
			<p>Luca Bonacorsi, consultant and exp training cified Adobe / Macromedia, is professor of Rich Internet Applications II, a course designed <br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a  href="#"><img src="images/luigi-ciorciolini-master-multimedia.jpg" alt="Luigi Ciorciolini" /></a>
			<h3><a href="#">Luigi Ciorciolini interview</a></h3>
			<p>Luigi Ciorciolini, film and video maker, since different editions of the course is a professor of communication by images in the period<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/luca-rosati-master-multimedia.jpg" alt="Luca Rosati" /></a>
			<h3><a href="#">Luca Rosati interview.</a></h3>
			<p>Luca Rosati is one of the leading exps on information architecture in Italy. Since two years is among the organizers of the Italian IA Summit<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/filippocasale-master-multimedia.jpg" alt="Filippo Casale" /></a>
			<h3><a href="#">Filippo Casale interview</a></h3>
			<p>Filippo Casale is a professor of Maya from the first edition of the Master. Instructor cified for Autodesk Maya software, Filippo is one of the most successful.<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/tiziano-fruet-master-multimedia1.jpg" alt="Tiziano Fruet" /></a>
			<h3><a href="#">Tiziano Fruet interview</a></h3>
			<p>Tiziano Fruet is the new teacher of the course of Graphic Design II at the Master. Tiziano is Adobe Italy Guru  since 2004, Adobe Cified Exp and Adobe Cified Instructor.<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/sante-j-achille-master-multimedia-firenze.jpg" alt="Sante J. Achille" /></a>
			<h3><a href="#">Sante J. Achille interview</a></h3>
			<p>Sante J. Achilles is two years teaches a course on Search Engine Optimization. Sante is a professional with national and international experience in consulting for the search.<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/luca-farulli-master-multimedia.jpg" alt="Luca Farulli" /></a>
			<h3><a href="#">Luca Farulli interview</a></h3>
			<p>Luca Farulli, professor of Aesthetics at the Academy of Fine Arts of Venice, is the owner of the course of aesthetics of digital art and coordinator<br /><a href="#"> &raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/diego-mencarelli-master-multimedia.jpg" alt="Diego Mencarelli" /></a>
			<h3><a href="#" title="This should be the title text">Diego Mencarelli interview</a></h3>
			<p>Diego Mencarelli, new media consultant at Unicoop Tirreno, co-teaches a course on human-machine interface design, in particular the module dedicated.<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a class="title" href="#"><img src="images/luca-bonacorsi-master-multimedia.jpg" alt="Luca Bonacorsi" /></a>
			<h3><a href="#">Luca Bonacorsi interview</a></h3>
			<p>Luca Bonacorsi, consultant and exp training cified Adobe / Macromedia, is professor of Rich Internet Applications II, a course designed to learn of Adobe Flex <br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a  href="#"><img src="images/luigi-ciorciolini-master-multimedia.jpg" alt="Luigi Ciorciolini" /></a>
			<h3><a href="#">Luigi Ciorciolini interview</a></h3>
			<p>Luigi Ciorciolini, film and video maker, since different editions of the course is a professor of communication by images in the period of specialization in video post-production<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/luca-rosati-master-multimedia.jpg" alt="Luca Rosati" /></a>
			<h3><a href="#">Luca Rosati interview.</a></h3>
			<p>Luca Rosati is one of the leading exps on information architecture in Italy. Since two years is among the organizers of the Italian IA Summit and teaches<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/filippocasale-master-multimedia.jpg" alt="Filippo Casale" /></a>
			<h3><a href="#">Filippo Casale interview</a></h3>
			<p>Filippo Casale is a professor of Maya from the first edition of the Master. Instructor cified for Autodesk Maya software, Filippo is one of the most successful 3D artist.<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/tiziano-fruet-master-multimedia1.jpg" alt="Tiziano Fruet" /></a>
			<h3><a href="#">Tiziano Fruet interview</a></h3>
			<p>Tiziano Fruet is the new teacher of the course of Graphic Design II at the Master. Tiziano is Adobe Italy Guru  since 2004, Adobe Cified Exp and Adobe Cified.<br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/sante-j-achille-master-multimedia-firenze.jpg" alt="Sante J. Achille" /></a>
			<h3><a href="#">Sante J. Achille interview</a></h3>
			<p>Sante J. Achilles is two years teaches a course on Search Engine Optimization. Sante is a professional with national and international experience in consulting <br /><a href="#">&raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/luca-farulli-master-multimedia.jpg" alt="Luca Farulli" /></a>
			<h3><a href="#">Luca Farulli interview</a></h3>
			<p>Luca Farulli, professor of Aesthetics at the Academy of Fine Arts of Venice, is the owner of the course of aesthetics of digital art and coordinator.<br /><a href="#"> &raquo; read more</a></p>
		</li>
		<li>
			<a href="#"><img src="images/diego-mencarelli-master-multimedia.jpg" alt="Diego Mencarelli" /></a>
			<h3><a href="#" title="This should be the title text">Diego Mencarelli interview</a></h3>
			<p>Diego Mencarelli, new media consultant at Unicoop Tirreno, co-teaches a course on human-machine interface design, in particular the module dedicated to accessibility<br /><a href="#">&raquo; read more</a></p>
		</li>
	</ul>
            </div>
            <div class="video_slider">
            
            <!-- Start of slider -->
              <div class="slider">
				 <ul class="rslides" id="slider">
					 <li>
                        <iframe width="100%" height="181" src="https://www.youtube.com/embed/e2HETguEHec" frameborder="0" allowfullscreen></iframe>						 
						 <div class="iframebox">
                          <h2><i class="fa fa-play"></i> Egyption industry Carpets</h2>
                           <p>Displaying its latest quotes to get suppliers and buyers.</p>
                        </div>	
					  </li>
					  
                       <li>	
                       <iframe width="100%" height="181" src="https://www.youtube.com/embed/sbiyXq6kzL8" frameborder="0" allowfullscreen></iframe>				 <div class="iframebox">
                           <h2><i class="fa fa-play"></i> Egyption industry Carpets</h2>
                           <p>Displaying its latest quotes to get suppliers and buyers.</p>
                        </div>	 
					  </li>
                      
                       <li>
                       <iframe width="100%" height="181" src="https://www.youtube.com/embed/F3SX8bXr85o" frameborder="0" allowfullscreen></iframe>				 <div class="iframebox">
                           <h2><i class="fa fa-play"></i> Egyption industry Carpets</h2>
                           <p>Displaying its latest quotes to get suppliers and buyers.</p>
                        </div>	
					  </li>
				  </ul>
			   </div>
               <!-- End of slider // -->
               
               <div class="verifiedbox_bottom">
                 <h4><span>Upload</span> Company video <span>Free</h4>
                  <div class="verifiedbox_supplierbox">
                    <h3>Verified Suppliers</h3>
                    <p>Selected Supplier from around the world </p>
                    <p><span class="fright">Learn More &gt; &gt;</span></p>
                    <div class="clear"></div>
                    <ul>
                      <li><a href="#"><img src="images/verified01.jpg" alt="" /></a></li>
                      <li><a href="#"><img src="images/verified02.jpg" alt="" /></a></li>
                      <li><a href="#"><img src="images/verified03.jpg" alt="" /></a></li>
                    </ul>
                  </div>
               </div>
            </div>
         </div>
          <!-- End of slider // -->  
        
          <div class="countrybox">
            <div class="countrubox_top">
              <div class="countrubox_heading"><h2>Top <span>Countries Marketplaces</span></h2></div>
              <div class="search">	  
				<input type="text" name="search" class="textbox" placeholder="Search Country" />
				<input type="submit" value="Subscribe" id="submit" name="submit" />
				<div id="response"> </div>
		     </div>
              <div class="clear"></div>
            </div>
            
              <ul class="country">
                 <li><a href="#"><span><b>Egypt</b></span> <img src="images/flag01.png" alt="" /></a></li>
                 <li><a href="#">Algeria <img src="images/flag02.png" alt="" /></a></li>
                 <li><a href="#">Sudan <img src="images/sudan.jpg" alt="" /></a></li>
                 
                  <li><a href="#">Iraq <img src="images/iraq.jpg" alt="" /></a></li>
                 <li><a href="#">Morocco <img src="images/morroco.jpg" alt="" /></a></li>
                 <li><a href="#">Saudi Arb. <img src="images/Saudi-Arabia.jpg" alt="" /></a></li>
                 
                  <li><a href="#">Yemen <img src="images/yemen.jpg" alt="" /></a></li>
                 <li><a href="#">Syria <img src="images/Syria.jpg" alt="" /></a></li>
                 <li><a href="#">Tunisia <img src="images/Tunisia.jpg" alt="" /></a></li>
                 
                  <li><a href="#">Somalia <img src="images/Somalia.jpg" alt="" /></a></li>
                 <li><a href="#">UAE <img src="images/uae.jpg" alt="" /></a></li>
                 <li><a href="#">Jordan <img src="images/jordan.jpg" alt="" /></a></li>
                 
                  <li><a href="#">Libya <img src="images/Libya.jpg" alt="" /></a></li>
                 <li><a href="#">Palestine <img src="images/Palestine.jpg" alt="" /></a></li>
                 <li><a href="#">Lebanon <img src="images/Lebanon.jpg" alt="" /></a></li>
                 
                  <li><a href="#">Oman <img src="images/Oman.jpg" alt="" /></a></li>
                 <li><a href="#">Kuwait <img src="images/Kuwait.jpg" alt="" /></a></li>
                 <li><a href="#">Mauritania <img src="images/Mauritania.jpg" alt="" /></a></li>
                 
                 <li><a href="#">Qatar <img src="images/Qatar.jpg" alt="" /></a></li>
                 <li><a href="#">Bahrain <img src="images/Bahrain.jpg" alt="" /></a></li>
                 <li><a href="#">Djibouti <img src="images/Djibouti.jpg" alt="" /></a></li>
                 
                 <li><a href="#">Comoros <img src="images/Comoros.jpg" alt="" /></a></li>
                 <li><a href="#">Egypt <img src="images/flag01.png" alt="" /></a></li>
                 <li><a href="#">Algeria <img src="images/flag02.png" alt="" /></a></li>
             
                     
                
               </ul>
          </div>
            <div class="space21"></div>
	           <!-- Strat of first banner -->
               <div class="countrubox_top2">
                <div class="countrubox_heading"><h2>View <a href="#"><span>Products &amp; Suppliers</span></a></h2></div>
                 <div class="list-rights">	  
				  <h2><a href="#"><span>List</span> Your Products</a></h2>
		        </div>
               <div class="clear"></div>
             </div>
             
		         <div class="demobox">
				     <div class="col-md-12">
                      <div class="white_bg">
                      <div class="clear" style="height:5px;"></div>
				     	 <div class="welcome_desc">
				            <div class="course_demo">
					          <ul id="flexiselDemo1">	
								<li><img src="images/v1.jpg" class="img-responsive" />
                                <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                                </li>
								<li>
                                <img src="images/v2.jpg" class="img-responsive" />
                                <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                               </li>
                               
								<li>
                                  <img src="images/v3.jpg" class="img-responsive" />
                                  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon03.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                               </li>
								<li>
                                  <img src="images/v4.jpg" class="img-responsive" />
                                  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                                </li>
								<li>
                                  <img src="images/v1.jpg" class="img-responsive" />
                                  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                                </li>	    	  	       	   	    	
							</ul>
				<script type="text/javascript">
			$(window).load(function() {
				$("#flexiselDemo1").flexisel({
					visibleItems: 4,
					animationSpeed: 1000,
					autoPlay: true,
					autoPlaySpeed: 3000,    		
					pauseOnHover: true,
					enableResponsiveBreakpoints: true,
			    	responsiveBreakpoints: { 
			    		portrait: { 
			    			changePoint:480,
			    			visibleItems: 1
			    		}, 
			    		landscape: { 
			    			changePoint:640,
			    			visibleItems: 2
			    		},
			    		tablet: { 
			    			changePoint:768,
			    			visibleItems: 2
			    		}
			    	}
			    });
			    
			});
		</script>
		<script type="text/javascript" src="js/new_js/jquery.flexisel.js"></script>
	         </div>
             
             <div class="learnmores">
               <p><a href="#">View All Categories <span>&gt;&gt;</span></a></p>
             </div>
	       </div>
		</div>
       </div>
	</div>
   <!-- End of first banner // -->
   
    <!-- Strat of second banner -->       
		 <div class="demobox">
           <div class="countrubox_top">
                <div class="countrubox_heading"><h2>Temporary <a href="#"><span>Sale Offers Ads</span></a></h2></div>
                 <div class="list-rights">	  
				  <h2><a href="#"><span>Post</span> Sale Offers Ads</a></h2>
		        </div>
               <div class="clear"></div>
            </div>
            
             <div class="blank_bg">
		    <div class="welcome_desc">
				            <div class="course_demo">
					          <ul id="flexiselDemo2">	
								<li><img src="images/v1.jpg" class="img-responsive" />
                                <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                                </li>
								<li>
                                <img src="images/v2.jpg" class="img-responsive" />
                                <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                               </li>
                               
								<li>
                                  <img src="images/v3.jpg" class="img-responsive" />
                                  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon03.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                               </li>
								<li>
                                  <img src="images/v4.jpg" class="img-responsive" />
                                  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                                </li>
								<li>
                                  <img src="images/v1.jpg" class="img-responsive" />
                                  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
                                </li>	    	  	       	   	    	
							</ul>
				<script type="text/javascript">
			$(window).load(function() {
				$("#flexiselDemo2").flexisel({
					visibleItems: 4,
					animationSpeed: 1000,
					autoPlay: true,
					autoPlaySpeed: 3000,    		
					pauseOnHover: true,
					enableResponsiveBreakpoints: true,
			    	responsiveBreakpoints: { 
			    		portrait: { 
			    			changePoint:480,
			    			visibleItems: 1
			    		}, 
			    		landscape: { 
			    			changePoint:640,
			    			visibleItems: 2
			    		},
			    		tablet: { 
			    			changePoint:768,
			    			visibleItems: 2
			    		}
			    	}
			    });
			    
			});
		</script>
	      </div>
            <div class="learnmores">
               <p><a href="#">View all sale Offers <span>&gt;&gt;</span></a></p>
             </div>
	     </div>
       </div>
	</div>
	
	<!-- End of second banner // --> 
    
      <div class="center-top">
        <div class="middle mid-content">
          <h3>Banner Place</h3>
         <div class="clear"></div>
       </div> 
      </div>
            <!-- Start of Third banner --> 
	         <div class="demobox">
               <div class="booking">
                  <a href="#">You Can Advise Here <span>FREE</span> in Primacy Booking</a>
                </div>
                <div class="clear"></div>
              <div class="countrubox_top2">
                <div class="countrubox_heading">
                 <div class="countryheadingboxleft">
                   <h3><a href="#">ARABYOS Leading Products</a></h3>
                 </div>
                  <div class="mainflagbox">
                   <div class="membershipicon"><a href="#"><img src="images/membership-icon01.png" alt="" /></a></div>
                   <div class="membershipicon"><a href="#"><img src="images/membership_icon02.png" alt="" /></a></div>
                  </div>
                
                </div>
                 <div class="list-rights">	  
				  <h2><a href="#"><span>Post</span> Premium Ads</a></h2>
		         </div>
                 <div class="clear"></div>
               </div>
				     <div class="col-md-12">
                     <div class="white_bg">
                     <div class="clear" style="height:5px;"></div>
				     	 <div class="welcome_desc">
				            <div class="course_demo">
							
					          <ul id="flexiselDemo4">	
								<li>
								 <div class="demobox">
								   <img src="images/v1.jpg" class="black" alt="" height="125" />
								   <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								  
								  <div class="demobox">
								   <img src="images/v1.jpg" class="black" alt="" height="125" />
								  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								  </div>
								  
								</li>
								
								<li>
								<div class="demobox">
								  <img src="images/v2.jpg" class="black" alt="" height="125" />
								    <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								  <img src="images/v2.jpg" class="black" alt="" height="125" />
								    <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
								
								<li>
								<div class="demobox">
								 <img src="images/v3.jpg" class="black" alt="" height="125" />
								 <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon03.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								 <img src="images/v3.jpg" class="black" alt="" height="125" />
								  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon03.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
								
								<li>
								<div class="demobox">
								<img src="images/v4.jpg" class="black" alt="" height="125" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								<img src="images/v4.jpg" class="black" alt="" height="125" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
								
								<li>
								<div class="demobox">
								<img src="images/v5.jpg" class="black" alt="" height="125" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								<img src="images/v5.jpg" class="black" alt="" height="125" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
										    	  	       	   	    	
							</ul>
							
							
							
							
				<script type="text/javascript">
			$(window).load(function() {
				$("#flexiselDemo4").flexisel({
					visibleItems: 4,
					animationSpeed: 1000,
					autoPlay: true,
					autoPlaySpeed: 3000,    		
					pauseOnHover: true,
					enableResponsiveBreakpoints: true,
			    	responsiveBreakpoints: { 
			    		portrait: { 
			    			changePoint:480,
			    			visibleItems: 1
			    		}, 
			    		landscape: { 
			    			changePoint:640,
			    			visibleItems: 2
			    		},
			    		tablet: { 
			    			changePoint:768,
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
                
                <div class="countryheadingboxleft">
                    <h3><a href="#"><span>Loyal</span> Business Services</a></h3>
                 </div>
                 
                 <div class="mainflagbox">
                   <div class="membershipicon2"><a href="#"><img src="images/membership_icon03.png" alt="" /></a></div>
                 </div>
                
                
                </div>
                 <div class="list-rights">	  
				  <h2><a href="#"><span>Post</span> Business Services</a></h2>
		         </div>
                 <div class="clear"></div>
               </div>
				     <div class="col-md-12">
                     <div class="bottom_bg">
				     	 <div class="welcome_desc">
				            <div class="course_demo">
					          <ul id="flexiselDemo5">	
								<li>
								 <div class="demobox">
								   <img src="images/v1.jpg" class="blue" alt="" />
								   <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								  
								  <div class="demobox">
								   <img src="images/v1.jpg" class="blue" alt="" />
								  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								  </div>
								  
								</li>
								
								<li>
								<div class="demobox">
								  <img src="images/v2.jpg" class="blue" alt="" />
								    <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								  <img src="images/v2.jpg" class="blue" alt="" />
								    <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
								
								<li>
								<div class="demobox">
								 <img src="images/v3.jpg" class="blue" alt="" />
								 <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon03.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								 <img src="images/v3.jpg" class="blue" alt="" />
								  <div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon03.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
								
								<li>
								<div class="demobox">
								<img src="images/v4.jpg" class="blue" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								<img src="images/v4.jpg" class="blue" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon01.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
								
								<li>
								<div class="demobox">
								<img src="images/v3.jpg" class="blue" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								
								<div class="demobox">
								<img src="images/v3.jpg" class="blue" />
								<div class="matterbox">
                                  <div class="icon_pic"><img src="images/slider-icon02.jpg" class="img-responsive" alt="" /></div>
                                  <div class="rightmatter">
                                    <h3>Free Shipping Watch</h3>
									<p>MOQ: 50 Pyeces<br></p>
                                    <p>US: <span><ins>$</ins>90</span> / PIECES</p>
                                    <div class="clear"></div>
                                  </div>
									<div class="clear"></div>
								</div>
								</div>
								</li>
										    	  	       	   	    	
							</ul>
							
							
							
							
				<script type="text/javascript">
			$(window).load(function() {
				$("#flexiselDemo5").flexisel({
					visibleItems: 4,
					animationSpeed: 1000,
					autoPlay: true,
					autoPlaySpeed: 3000,    		
					pauseOnHover: true,
					enableResponsiveBreakpoints: true,
			    	responsiveBreakpoints: { 
			    		portrait: { 
			    			changePoint:480,
			    			visibleItems: 1
			    		}, 
			    		landscape: { 
			    			changePoint:640,
			    			visibleItems: 2
			    		},
			    		tablet: { 
			    			changePoint:768,
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
		      <h2><a href="#"><span>Add</span> Your Logo</a></h2>
		   </div>
         <div class="clear"></div>
       </div>
	   
	   <div class="whitefooter">
	      <ul>
		     <li><a href="#"><img src="images/footer-logo01.jpg" class="img-responsive" alt="" /></a></li>
			 <li><a href="#"><img src="images/footer-logo02.jpg" class="img-responsive" alt="" /></a></li>
			 <li><a href="#"><img src="images/footer-logo03.jpg" class="img-responsive" alt="" /></a></li>
			 <li><a href="#"><img src="images/footer-logo04.jpg" class="img-responsive" alt="" /></a></li>
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
         -->		
       </div>	
    <div class="clear"></div>
    
    	  <div class="subscribenow"><h3><a href="#">Subscribe NOW &gt; &gt;</a></h3></div>	
		
<!-- Start of rowbanner --> 
   <div class="mid-top">
     <div class="middle mid-content">
       <h3>Banner Place Stands out only When Admin adds</h3>
      <div class="clear"></div>
   </div> 
   </div>
   <!-- End of rowbanner // --> 
 
 </div>
 
 
        <div id="rightsection">
        
          <div class="buyleads">
            <div class="leftleads"><h2><a href="#">Buy Leads &nbsp;<i class="fa fa-caret-right"></i></a></h2></div>
             <div class="rightnumber"><a href="#"><img src="images/numbers.jpg" alt="" /></a></div>
             <div class="clear"></div>
            <div class="buybox">
            
              <div class="popular-post-grid">
                                <h3>Fresh Vegetables</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Quantity :</b> 3455 bag(s).</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>
                            
                            <div class="popular-post-grid">
                                <h3>Apple</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Quantity :</b> 3455 Dozen(s).</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>
                            
                            <div class="popular-post-grid">
                                <h3>White Rice</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Quantity :</b> 3455 Dozen(s).</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>
                            
                             <div class="popular-post-grid">
                                <h3>High Quality Nodles</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Quantity :</b> 3455 Kilogram(s).</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>  
                            
                            
                           <div class="learnmore"><p><a href="#">All Live Buy Leads <span>&gt;&gt;</span></a></p></div>
            
            </div>
            <div class="clear"></div>
          </div>
		
		<!-- Start of Tabs -->
		<div class="sap_tabs">	
			 <div id="horizontalTab" style="display: block; width: 100%; margin: 0px;">
			  <ul class="resp-tabs-list">
				  <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><span><b>Tenders</b></span></li>
				  <li class="resp-tab-item" aria-controls="tab_item-1" role="tab"><span><b>Auctions</b></span></li>
				  <div class="clear"></div>
			  </ul>				  	 
				<div class="resp-tabs-container">
					<div class="tab-1 resp-tab-content" aria-labelledby="tab_item-0">
						<ul class="tab_img">
						 <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <h3>Provision of Digitization Capacity Building Consulting</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Date of Document :</b> 20 Apr 2014</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
                          
                          <li>
                           <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <h3>مناقصة توريد 1000 قطعة ثوب قماش حرير لصناعة من غزل المحلة لقطاع مصانع الغزل</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Date of Document :</b> 20 Apr 2014</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
                          
                          <li>
                           <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <h3>Provision of Digitization Capacity Building Consulting</h3>
                              <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Date of Document :</b> 20 Apr 2014</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                              </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
						<div class="clear"></div>
                        <div class="learnmore"><p><a href="#">View all <a href="Tender">Tender</a> / <a href="Auction">Auctions</a> </a></p></div>
                        <div class="tabbotton">
                         <a href="Tender"><span>Publish</span> Tender/</a>
                         <a href="Auction">Auction</a> 
                          <span>FREE</span>
                        </div>
					 </ul>
                   
				  </div>	
					 <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-1">
						<ul class="tab_img">
						 <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <h3>Provision of Digitization Capacity Building Consulting</h3>
                                <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Date of Document :</b> 20 Apr 2014</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
                          
                          <li>
                           <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <h3>Provision of Digitization Capacity Building Consulting</h3>
                                <div class="tendersbox">
                                  <div class="verifiedbox">
                                   <div class="cover"><img src="images/tick.png" alt="" /> Verified &amp; Updated</div>
                                    <div class="date"><b>Date of Document :</b> 20 Apr 2014</div>
                                  </div>
                                  <div class="flagbox">
                                    <ul>
                                      <li><a href="#">Lebanon <img src="images/lebnaan.png" alt="" /></a></li>
                                    </ul>
                                    
                                    <div class="date"><span>Foreign</span></div>
                                  </div>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
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
				  <li class="resp-tab-item" aria-controls="tab_item-0" role="tab"><span><h5>For Buying</h5></span></li>
				  <li class="resp-tab-item" aria-controls="tab_item-1" role="tab"><span><h5>For Supplying</h5></span></li>
				  <div class="clear"></div>
			  </ul>				  	 
				<div class="resp-tabs-container" style="border:#e4e4e4 1px solid;">
					<div class="tab-1 resp-tab-content" aria-labelledby="tab_item-0">
						<ul class="tab_img">
						 <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <div class="post-img">
                                    <a href="#"><img src="images/email-icon.jpg" class="img-responsive" alt="" /></a>                                </div>
                                <div class="post-text">
                                    <a class="pp-title" href="single.html"> Send us your Buy Requirement</a>
                                    <p>Receive Responses from pre-verified and qualified suppliers.</p>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
                          
                          <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <div class="post-img">
                                    <a href="#"><img src="images/search.jpg" class="img-responsive" alt="" /></a>                                </div>
                                <div class="post-text">
                                    <a class="pp-title" href="single.html"> Search for a product</a>
                                    <p>Send enquiries directly to the Suppliers of your Choice.</p>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
                          
                          <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <div class="post-img">
                                    <a href="#"><img src="images/bell.jpg" class="img-responsive" alt="" /></a>                                </div>
                                <div class="post-text">
                                    <a class="pp-title" href="single.html"> subscribe to Trade Alerts</a>
                                    <p>Get updates on relevant products and sell offers directly in your email.</p>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
						<div class="clearfix"></div>
					</ul>
				  </div>	
					 <div class="tab-1 resp-tab-content" aria-labelledby="tab_item-1">
						
                        <ul class="tab_img">
						 <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <div class="post-img">
                                    <a href="#"><img src="images/email-icon.jpg" class="img-responsive" alt="" /></a>                                </div>
                                <div class="post-text">
                                    <a class="pp-title" href="single.html"> Send us your Buy Requirement</a>
                                    <p>Receive Responses from pre-verified and qualified suppliers.</p>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
                          
                          <li>
                          <div class="popular-post-grids">
                            <div class="popular-post-grid">
                                <div class="post-img">
                                    <a href="#"><img src="images/search.jpg" class="img-responsive" alt="" /></a>                                </div>
                                <div class="post-text">
                                    <a class="pp-title" href="single.html"> Search for a product</a>
                                    <p>Send enquiries directly to the Suppliers of your Choice.</p>
                                </div>
							   <div class="clear"></div>
						    </div>
                           </div>
                          </li>
						<div class="clearfix"></div>
					</ul>
                        
                        
                        
				  </div>	
						
			   </div>
			 </div>
          </div>
		
		
		<!-- End of Tabs // -->
		
		
		<!-- Start of seniorbox -->
		 <div class="seniorbox">
			<div class="sponsorbox">
              <div class="siconbox"><img src="images/right-icon.png" alt="" /></div>
              <div class="scontentbox"><h2> Sponsor <span>Supplier</span></h2></div>
              <div class="clear"></div>
            </div>
			 <ul>
			   <li>&gt; <a href="#">Exclusive Access to Buying Requests</a></li>
			   <li>&gt; <a href="#">Rank of Buyers to Find Your Products</a></li>
			   <li>&gt; <a href="#">Customized Website</a></li>
			   <li>&gt; <a href="#">Link to the company website</a></li>
			   <li>&gt; <a href="#">Premium Sponsor Supplier Sign</a></li>
			   <li>&gt; <a href="#">Product Posting Service</a></li>
			   <li>&gt; <a href="#">Email Marketing</a></li>
			   <p style="padding-right:15px;"><a href="#">Learn More <span>&gt; &gt;</span></a></p>
			 </ul>
			 <h3><a href="#">Request All Privileges </a></h3>
		  </div>
		<!-- End of seniorbox // -->
        
        <div class="testimonialbox">
		  <div class="testimonialbg">
		    <h2>Buyer Speaks</h2>
			
			 <div class="arrow_box">
			 <p><span>&ldquo;</span> <i>I am happy that i'm buyer member in ARABYOS, I could Finally find my domestic and global requirements, it was great support to my business.</i> <span class="spacecomma">&rdquo;</span></p>
			</div>
            <div class="clear"></div>
                         
          <div class="testiwriter">
           <div class="pic1"><img src="images/pr1.png" alt=""/></div>
           <div class="pic-info">
             <h5>Ebraham Khodair</h5>
              <p><a href="#">Germany</a></p>
           </div>	
         </div> 
              
              
		  </div>
		</div>
		
		<!-- Start of juniorbox-->
		 <div class="juniorbox">
			<div class="trianglebox">
              <div class="boxlefts"><img src="images/jonior-icon.png" alt="" /></div>
               <div class="boxrights"><h2>Junior <span>Supplier</span> </br>
<span>Trust Sign</span></h2></div>
               <div class="clear"></div>
            </div>
			 <p><i>Assessed Suppliers onsite operation checked by ARABYOS.com. legal status and existance to increase global &amp; domestic buyer confidence.</i></br></br>
			 <a href="#" class="fright" style="padding-right:15px;">Learn More <span>&gt; &gt;</span></a></p>
			 <div class="clear"></div>
			 <h3><a href="#">Request All Privileges </a></h3>
		  </div>
		<!-- End of juniorbox // -->
        
        <div class="testimonialbox">
		  <div class="testimonialbg">
		    <h2>Buyer Speaks</h2>
            
            <div class="arrow_box">
			 <p><span>&ldquo;</span> <i>I am happy that i'm buyer member in ARABYOS, I could Finally find my domestic and global requirements, it was great support to my business.</i> <span class="spacecomma">&rdquo;</span></p>
			</div>
            <div class="clear"></div>
                         
          <div class="testiwriter">
           <div class="pic1"><img src="images/pr1.png" alt=""/></div>
           <div class="pic-info"><h5>Ebraham Khodair</h5>
             <p><a href="#">Germany</a></p>
           </div>	
         </div>

		  </div>
		</div>
		
		
		
		
		
		</div>
        <div class="clear"></div>
</div>

   </div>
  <div class="clear"></div>
</div>
 <!-- End of middlesection // --> 
 
</div>
<!-- End of wrapper // -->

<!-- footer start -->
 <footer class="footer">
   <!-- footer-searchsec start -->
     <div class="footer-searchsec">
         <div class="footer-searchsec-left">
             <div class="footer-searchsec-left-head">
                 <h1>Find Service Providers of an Assessed Suppliers</h1>
                </div>
                <div class="footer-searchsec-left-form">
                 <form>
                     <div class="footer-searchsec-left-form-col1"><p>Services</p></div>
                        <div class="footer-searchsec-left-form-col2">
                         <input type="text" name="nm" placeholder="Search for any Business  Services" class="footer-searchsec-left-form-col2-input" />
                        </div>
                        <div class="footer-searchsec-left-form-col3">
                         <input type="submit" value="" class="footer-searchsec-left-form-col3-btn" />
                        </div>
                    </form>
                <div class="clear"></div>
                </div>
            </div>
            <div class="footer-searchsec-right">
             <a href="#" class="footer-searchsec-right-btn">Post Services Requests</a>
            </div>
        <div class="clear"></div>
        </div><!-- footer-searchsec close// -->
        <div class="footer-intro"><!-- footer-intro start -->
         <div class="footer-intro-left">
             <div class="footer-intro-left-logo"><a href="#"><img src="images/footer-intro-left-logo02.png" alt="" /></a></div>
                <div class="footer-intro-left-text">
                 <ul>
                     <li><a href="#">About Us</a></li>
                        <li><a href="#">Complaints</a></li>
                        <li><a href="#">Feedback</a></li>
                        <li><a href="#">Our Agents</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Help</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-intro-right"><!-- footer-intro-right start -->
             <div class="footer-intro-right-col">
                 <h2>Buyers Tools</h2>
                    <ul>
                     <li><a href="#">Post Buy Requirment</a></li>
                        <li><a href="#">Manage Sale offer Alerts</a></li>
                        <li><a href="#">Search Products / Services</a></li>
                    </ul>
                </div>
                <div class="footer-intro-right-col">
                 <h2>Suppliers Tools</h2>
                    <ul>
                     <li><a href="#">Post Products - FREE</a></li>
                        <li><a href="#">Create Website on ARABYOS</a></li>
                        <li><a href="#">Latest Buy Leads </a></li>
                    </ul>
                </div>
                <div class="footer-intro-right-col">
                 <h2>ARABYOS Soluations</h2>
                    <ul>
                     <li><a href="#">Premium Membership</a></li>
                        <li><a href="#">Trade Leads For Me</a></li>
                        <li><a href="#">Advertise with us </a></li>
                    </ul>
                </div>
                <div class="footer-intro-right-col">
                 <h2>Tenders / Auctions</h2>
                    <ul>
                     <li><a href="#">Latest Tenders</a></li>
                        <li><a href="#">Mange Tenders Alerts</a></li>
                        <li><a href="#">Latest Auctions</a></li>
                        <li><a href="#">Mange Auctions Alerts</a></li>
                    </ul>
                </div>
            <div class="clear"></div>
            <div class="footer-intro-social">
             <p>Conect with us :</p>
                <ul>
                 <li><a href="#"><i class="fa fa-twitter-square"></i></a></li>
                    <li><a href="#"><i class="fa fa-google-plus-square"></i></a></li>
                    <li><a href="#"><i class="fa fa-facebook-square"></i></a></li>
                </ul>
            </div>
            </div><!-- footer-intro-right close// -->
        <div class="clear"></div>
        </div><!-- footer-intro close// -->
    </footer><!-- footer close// -->
    <div class="copyright-row"><!-- copyright-row start -->
     <div class="copyright-row-col1">
         <p>Copyright  All rights reserved &copy; 2015 ARABYOS.</p>
        </div>
        <div class="copyright-row-col2">
         <p><a href="#">Terms of Use</a> | <a href="#">Privacy Policy</a> | <a href="#">Link to Us</a></p>
        </div>
    <div class="clear"></div>
    </div>
    <!-- copyright-row close // -->
</div>
<!-- start of right Tabs -->






<script src="js/new_js/easyResponsiveTabs.js" type="text/javascript"></script>
		    <script type="text/javascript">
			    $(document).ready(function () {
			        $('#horizontalTab').easyResponsiveTabs({
			            type: 'default', //Types: default, vertical, accordion           
			            width: 'auto', //auto or any width like 600px
			            fit: true   // 100% fit in a container
			        });
			    });
</script>
<script type="text/javascript">
$(document).ready(function () {
	$('#horizontalTab1').easyResponsiveTabs({
		type: 'default', //Types: default, vertical, accordion           
		width: 'auto', //auto or any width like 600px
		fit: true   // 100% fit in a container
	});
});
</script>	
<!-- End of right Tabs // -->


<script>
  $('#myTabs a').click(function (e) {
  e.preventDefault()
  $(this).tab('show')
})
</script>
<!-- start of verticle menu -->
<script src="js/new_js/cust.js"></script>
<!-- End of verticle menu // -->

<!-- Animation text slider -->
<link rel="stylesheet" href="css/new/css/imNew-v6.css" type="text/css" /> 
<script src="js/new_js/im-style-vn6.3.js" type="text/javascript"></script>
<script src="js/new_js/bgSlider-v1.js" type="text/javascript"></script>
<!-- Animation text slider // -->

<script src="js/new_js/bootstrap.min.js"></script>

<!-- navigation  -->
<link rel="stylesheet" href="css/new/css/cssmenu.css" type="text/css" />
<script src="js/new_js/script.js" type="text/javascript"></script>
<!-- navigation // -->
</body>
</html>
