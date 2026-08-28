	<style>

	.countrytwo{

	float: left;

list-style: none;

margin: 0;

padding: 0;

width: 325px;

position: absolute;

left: 31em !important;

top: 9em !important;

height: 177px !important;

overflow-y: scroll;

width: 32% !important;

border-bottom: 2px solid #006bb1;

border-left: 2px solid #006bb1;

border-right: 2px solid #006bb1;

z-index: 1;

background-color: white;

border-radius: 3px;

}
@media (max-width: 1300px) and (min-width: 1280px) {
  .footer .footer-searchsec {
      max-width: 860px !important;
  }
  .footer-searchsec-left {
    width: calc(100% - 35%) !important;
  }
  .footer-searchsec-right {
    margin-left: 28px !important;
  }
}
@media (min-width: 1301px) {
  .footer-searchsec-right {
    margin-left: 38px !important;
  }
}
</style>



<?php
	



   include 'common.php';

   $uid=$_SESSION['uid_indm'];

   //GET user details

	   $sql = "SELECT u.*, bp.bnsprof_compname, bp.bnsprof_city FROM `user` u LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid WHERE `usr_id` = '".$uid."'  LIMIT 1";

	   $qry = mysqli_query($con, $sql) or die(mysql_error());

	   $user_detail = mysqli_fetch_array( $qry);

	   $usqlcountry = "select cn_name from country where cn_id='". $user_detail['country']."'";

	   $urscountry = mysqli_query($con,$usqlcountry);

	   if(mysqli_num_rows($urscountry) > 0)

	   {

		  $urowcountrty = mysqli_fetch_object($urscountry);

		  $user_cn_name = $urowcountrty->cn_name;

	   }



	   $usqlcity = "select ct_name from city where ct_id='". $user_detail['bnsprof_city']."'";

	   $usqlcity = mysqli_query($con,$usqlcity);

	   if(mysqli_num_rows($usqlcity) > 0)

	   {

		  $urowcity = mysqli_fetch_object($usqlcity);

		  $user_ct_name = $urowcity->ct_name;

	   }



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

	if(isset($_SESSION['m_msg'])){	$msg=$_SESSION['m_msg'];	unset($_SESSION['m_msg']); }

		//if(isset($_SESSION['m_membership_plan'])){	$membership_plan=$_SESSION['m_membership_plan'];	unset($_SESSION['m_membership_plan']);	}

		if(isset($_SESSION['m_cname'])){	$cname=$_SESSION['m_cname'];	unset($_SESSION['m_cname']);	}else { $cname=$user_detail['bnsprof_compname']; }

		//echo $cname;

		if(isset($_SESSION['m_fullname'])){	$fullname=$_SESSION['m_fullname'];	unset($_SESSION['m_fullname']);	}else { $fullname=$user_detail['fname'].' '.$user_detail['lname']; }

		if(isset($_SESSION['m_mobile'])){	$mobile=$_SESSION['m_mobile'];	unset($_SESSION['m_mobile']);	}else { $mobile=$user_detail['mobile1']; }

		if(isset($_SESSION['m_email'])){	$email=$_SESSION['m_email'];	unset($_SESSION['m_email']);	}else { $email=$user_detail['email']; }

		if(isset($_SESSION['m_country'])){	$country=$_SESSION['m_country'];	unset($_SESSION['m_country']);	}else { $country=$user_cn_name; }

		if(isset($_SESSION['m_city'])){	$city=$_SESSION['m_city'];	unset($_SESSION['m_city']);	}else { $city= $user_ct_name; }

		if(isset($_SESSION['m_address'])){	$address=$_SESSION['m_address'];	unset($_SESSION['m_address']);	}else { $address=''; }

		if(isset($_SESSION['m_requirement'])){	$requirement=$_SESSION['m_requirement'];	unset($_SESSION['m_requirement']);	}else { $requirement=''; }



class addMembershipRequirement{



	var $cname;

	var $fullname;

	var $email;

	var $mobile;

	var $country;

	var $city;

	var $address;

	var $requirement;

	//var $membership_plan;

	var $msg;

	var $plans;



	function __construct($cname, $fullname, $email, $mobile, $country, $city, $address, $requirement)

	{

		global $con;

		//$this->membership_plan=$membership_plan;

		$this->cname=$cname;

		$this->fullname=$fullname;

		$this->email=$email;

		$this->mobile=$mobile;

		$this->country=$country;

		$this->city=$city;

		$this->address=$address;

		$this->requirement=$requirement;

		$this->plans = '';

	}



	function valid(){



		$valid=true;



		/*if($this->membership_plan=="" || $this->membership_plan==",")

		{

			$this->msg='<font color="#CC0000">Please select atleast a membership plan.</font>';

			$valid=false;

		}

		else*/ if($this->cname=="")

		{

			$this->msg='<font color="#CC0000">Please enter company name.</font>';

			$valid=false;

		}

		else if($this->fullname=="")

		{

			$this->msg='<font color="#CC0000">Please enter your name.</font>';

			$valid=false;

		}

		else if($this->email=="")

		{

			$this->msg= '<font color="#CC0000">Please enter your email address</font>';

			$valid=false;

		}

		else if (!validate::is_email($this->email))

		{

			$this->msg= '<font color="#CC0000">Please enter valid email address</font>';

			$valid=false;

		}

		else if($this->mobile=="")

		{

			$this->msg= '<font color="#CC0000">Please enter your mobile number</font>';

			$valid=false;

		}

		else if($this->country=="")

		{

			$this->msg= '<font color="#CC0000">Please enter country</font>';

			$valid=false;

		}

		else if($this->city=="")

		{

			$this->msg= '<font color="#CC0000">Please enter city</font>';

			$valid=false;

		}



		else if($this->requirement=="")

		{

			$this->msg= '<font color="#CC0000">Please enter the requirement</font>';

			$valid=false;

		}



		return $valid;

	}



	function set_session()

	{

		//$_SESSION['m_membership_plan']=$this->membership_plan;

		$_SESSION['m_cname']=$this->cname;

		$_SESSION['m_fullname']=$this->fullname;

		$_SESSION['m_email']=$this->email;

		$_SESSION['m_mobile']=$this->mobile;

		$_SESSION['m_country']=$this->country;

		$_SESSION['m_city']=$this->city;

		$_SESSION['m_address']=$this->address;

		$_SESSION['m_requirement']=$this->requirement;

	}



	function add()

	{

        global $con;

		$uid = isset($_SESSION['uid_indm'])?$_SESSION['uid_indm']:0;

		$sql="insert into membership_requirements

				set

					mp_user_id=".$uid.",

					mp_id='Advertisement Request',

					company_name='".$this->cname."',

					name='".$this->fullname."',

					email='".$this->email."',

					mobile='".$this->mobile."',

					country='".$this->country."',

					city='".$this->city."',

					address='".$this->address."',

					requirement='".$this->requirement."',

					status=1,updated_date=now()";

					//echo $sql;exit;

		mysqli_query($con, $sql) or die(mysql_error());

		$this->msg='<font color="#087017">Your requirements have been sent successfully, sales team will contact you shortly.</font>';

		$this->plans = 'Advertisement Request';



		/********************* Email sending code start here **********************/



		$to = $this->email;  /*Put Your Email Adress Here*/

		$subject = "Advertisement Requirement on ".get_page_settings(4);

		$from_name = get_page_settings(4);

		$from_email = get_adminemail();

        $plan = "Advertisement";

		include "email/membership_req.php"; //email design with content included



		/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";

		$message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;

		$message .= "<br /><br />".get_page_settings(4)." Team";*/

		$headers  = "MIME-Version: 1.0\r\n";

	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

    	$headers .= "From: $from_name < $from_email >\r\n";

    	$headers .= "Reply-To: $from_email";

       

		mail($to, $subject, $message1, $headers);



		/********************* Email sending code end here **********************/



		/********************* Email sending code to admin start here **********************/



		$to = get_adminemail();  /*Put Your Email Adress Here*/

		$subject = "Advertisement Requirement on ".get_page_settings(4);

		$from_name = get_page_settings(4);

		$from_email = get_adminemail();

		

		include "email/membership_req.php"; //email design with content included



		/*$message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";

		$message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;

		$message .= "<br /><br />".get_page_settings(4)." Team";*/

		$headers  = "MIME-Version: 1.0\r\n";

	    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

    	$headers .= "From: $from_name < $from_email >\r\n";



		mail($to, $subject, $message2, $headers);



		//echo $message1;

		//echo $message2;

		//exit;

		/********************* Email sending code to admin end here **********************/



	}

}

if(isset($_POST['Mb_Submit']))

{

	$adn=new addMembershipRequirement(addslashes(trim($_POST['cname'])),addslashes(trim($_POST['fullname'])), $_POST['email'], addslashes(trim($_POST['mobile'])),	addslashes(trim($_POST['country'])),addslashes(trim($_POST['city'])),addslashes(trim($_POST['address'])),addslashes(trim($_POST['requirement'])));





	if($adn->valid())

	{



		$adn->add();

		//echo "<pre>"; print_r($adn); echo "</pre>";exit;

	}

	else

	{

		$adn->set_session();

	}

	$_SESSION['m_msg']=$adn->msg;

	$msg = $adn->msg;

	if(strpos($adn->msg, 'shortly') > 0 && $from == 1){

		header("location:thankyou.php?from=2");

	}

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

      <title><?php echo getSiteTitle(); ?></title>

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

      <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

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

   <body style="background: #8b89c3;" class="search-show-box">

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
<?php include "includes/header_new.php"; ?>
      <div class="wrapper" style="overflow-x: hidden;">

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

         <style type="text/css">

            .zoomin1 img { height: 78px; width: 219px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }

            .zoomin1 img:hover { width: 229px; height: 88px;  }

            .zoomin2 img { height: 66px; width: 200px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }

            .zoomin2 img:hover { width: 210px; height:77px; }

            .zoomin3 img { height: 41px; width: 235px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }

            .zoomin3 img:hover { width: 245px; height:50px; }

         </style>

         <?php

            /**

             * Created by PhpStorm.

             * User: Long

             * Date: 12/18/2015

             * Time: 11:49 PM

             */

            ?>

         <!-- Top Blue Bar-->



         <!-- End of rowbanner // -->

         <link type="text/css" rel="stylesheet" href="css/style123.css"/>

         <!-- Start of middlesection -->

         <div class="middlesection1">

            <div class="maincontainer">

               <div class="maincontent1" style="min-height: 2930px;">

                  <div class="maincontent1top">  </div>



                  <div class="section0_pager1">



               <!--      <img src="images/M2logo.png" style=" margin: 23px 1px 21px 34px;">-->



                     <div class=" page2-header2-div1">

                       <!-- <div class="why_ara"  > Why  ARABYOS ?</div>-->

                        <div class="list_page2">

                           <ul id="nav">

                              <li ><a href="why_ARABYOS.php">Why  ARABYOS ?</a></li>

                              <li ><a href="membership_plans.php">Membership Plans</a></li>

                              <li class="active"> <a href="advertise-with-us.php">Advertise  with  Us</a></li>

                           </ul>

                        </div>

                     </div>

                     <div class="clear"></div>

                     <div class="page2-header2-divmiddle">

             <p class="good_resp col-md-12 col-sm-12 col-xs-12"> Advertise with <span><img src=" images/arlogo.png"> ARABYOS.com </span></p>

			 <div class="clear"></div>

                     <div   class="page2-header2-divmiddle-left col-md-9 col-sm-12 col-xs-12">

                     	<img src="images/fullbanner.png" class="leftimg" >

                     	<img src="images/maximize.png" class="righting">



                     	</div>

                     	<div   class="page2-header2-divmiddle-right col-md-3 col-sm-12 col-xs-12">

                     	 <?php

                    $sql_testi3 = "select * from testimonials WHERE testi_type='supplier' and testi_status='1' order by rand() desc limit 1";

                    $res_testi3 = mysqli_query($con, $sql_testi3);

                    if (mysqli_num_rows($res_testi3) > 0) {



                    ?>



                        <div class="testimonialbox22">

                           <div class="testimonialbg">

                              <h2>Supplier Speaks  &nbsp;&nbsp; <img src="images/cir.png" width="25px"></h2>

                                 <?php

							 while($row_testi3 = mysqli_fetch_object($res_testi3))

							 {

							 ?>

                              <div class="arrow_box">

                                <p> <i><span>&ldquo;</span><?php echo stripslashes($row_testi3->testi_details); ?><span class="spacecomma">&rdquo;</span></i>

                                    </p>

                                 <!--<p> <i><span>&ldquo;</span>I am happy that I am a buyer member in ARABYOS, I could finally find my domestic and global requirement, it was great support to my business.<span class="spacecomma">&rdquo;</span></i>

                                 </p>-->

                              </div>

                              <div class="clear"></div>

                              <div class="testiwriter">

                                 <div class="pic1"><img src="upload/testimonial_img/<?php echo $row_testi3->testi_image; ?>"  alt=""/></div>

                                 <div class="pic-info">

                                    <h5><?php echo $row_testi3->testi_name; ?></h5>

                                    <p><a href="#"><?php echo get_country_name($row_testi3->testi_cn_id); ?></a></p>

                                 </div>

                              </div>

                               <?php }?>

                           </div>

                        </div>

                         <?php }?>



                     	   <!--     <div class="testimonialbox22">

                           <div class="testimonialbg">

                              <h2>Supplier Speaks&nbsp;&nbsp; <img src="images/cir.png" width="19px"></h2>

                              <div class="arrow_box">

                                 <p> <i><span>&ldquo;</span>I am happy that I am a buyer member in ARABYOS, I could finally find my domestic and global requirement, it was great support to my business.<span class="spacecomma">&rdquo;</span></i>

                                 </p>

                              </div>

                              <div class="clear"></div>

                              <div class="testiwriter">

                                 <div class="pic1"><img src="upload/testimonial_img/TESTIIMG-9637120x120.jpg" alt=""/></div>

                                 <div class="pic-info">

                                    <h5>Jack Smith</h5>

                                    <p><a href="#">Germany</a></p>

                                 </div>

                              </div>

                           </div>

                        </div>-->

                     	                     	</div>



                     </div>



                    

                        <div class="page3-header2-left col-md-9 col-sm-12 col-xs-12">



                        <table width="100%" style="border-collapse: separate; border-spacing: 4px;" >

                        <tr class="tblhead">

		<th><p  style="font-weight: bold;font-size: 20px;;padding: 1px 6px 1px 30px;">Advertisment page :</p></th>

		<th><p> Sizes/<span>Pixels</span></p></th>

		<th> <p>Locations</p></th>

		<th> <p>Subscriptions</p></th>



	</tr>

	<tr class="tblhead">

		<td class="td3"></td>

	</tr>

	<tr class="tblhead">

		<td class="td1">Global/Homepage</td>

		<td class="td2">468* 90</td>

		<td class="td2m">Top Banner</td>

		<td class="td2">$350/month</td>

		</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">468* 90</td>

		<td class="td2m">Middle Banner</td>

		<td class="td2">$300/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">468* 90</td>

		<td class="td2m">Bottom Banner</td>

		<td class="td2">$250/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">120*600</td>

		<td class="td2m">Left Sky Scrapper</td>

		<td class="td2">$ 350/month</td>

	</tr>



		<tr class="tblhead">

		<td class="td3"></td>

		</tr>

	<tr class="tblhead">

		<td class="td1">Countries/Homepages</td>

		<td class="td2">468* 90</td>

		<td class="td2m">Top Banner</td>

		<td class="td2">$300/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"> </td>

		<td class="td2">468* 90</td>

		<td class="td2m">Middle Banner</td>

		<td class="td2">$250/month</td>

	</tr>

       <tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">468* 90</td>

		<td class="td2m">Bottom Banner</td>

		<td class="td2">$200/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">120*600</td>

		<td class="td2m">Left Sky Scrapper</td>

		<td class="td2">$300/month</td>

	</tr>



	<tr class="tblhead">

			<td class="td3"></td>

	</tr>



	<tr class="tblhead">

		<td class="td1">Buy Leads Pages</td>

		<td class="td2">125*125</td>

		<td class="td2m">Bottom Banner</td>

		<td class="td2">$200/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">120*600</td>

		<td class="td2m">Right Sky Scrapper</td>

		<td class="td2">$250/month</td>

	</tr>



	<tr class="tblhead">

		<td class="td3"></td>

	</tr>





	<tr class="tblhead">

		<td class="td1">Sales Offers Pages</td>

		<td class="td2">468* 90</td>

		<td class="td2m">Top Banner</td>

		<td class="td2">$300/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td3"></td>

		</tr>



	<tr class="tblhead">

		<td class="td1"> Tenders Pages</td>

		<td class="td2">125*125</td>

		<td class="td2m">Bottom Banner</td>

		<td class="td2">$200/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">120*600</td>

		<td class="td2m"> Right Sky Scrapper</td>

		<td class="td2">$250/month</td>

	</tr>



	<tr class="tblhead">

		<td class="td1">Auction Pages</td>

		<td class="td2">125*125</td>

		<td class="td2m">Bottom Banner</td>

		<td class="td2">$150/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">120*600</td>

		<td class="td2m">Right Sky Scrapper</td>

		<td class="td2">$200/month</td>

	</tr>

		<tr class="tblhead">

		<td class="td3"></td>

		</tr>





	<tr class="tblhead">

		<td class="td1">Search Walls Pages</td>

		<td class="td2">468* 90</td>

		<td class="td2m">Middle Category Banner</td>

		<td class="td2">$200/month</td>

	</tr>



	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">125*125</td>

		<td class="td2m">Right Category Sky Scrapper</td>

		<td class="td2">$250/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td1"></td>

		<td class="td2">125*125</td>

		<td class="td2m">Right Button</td>

		<td class="td2">$200/month</td>

	</tr>









	<tr class="tblhead">

		<td class="td3"></td>

		</tr>



	<tr class="tblhead">

		<td  class="td1">Products Directory Pages</td>

		<td class="td2">125*125</td>

		<td class="td2m">Right Button</td>

		<td class="td2">$200/month</td>

	</tr>

	<tr class="tblhead">

		<td class="td3"></td>

		</tr>

	<tr class="tblhead">

		<td  class="td1">My ARABYOS Page</td>

		<td class="td2">125*125</td>

		<td class="td2m">Right Button</td>

		<td class="td2">$200/month</td>

	</tr>



</table>





                        </div>

                      <div  class="page3-header2-right col-md-3 col-sm-12 col-xs-12" >





<div class="imgs">

	<img src="images/adv1.png" />

	<img src="images/adv2.png" class="adme" />



</div>

</div>

                     

   <div class="clear"></div>

<div class="page3-header2-divlast">



	<p><span>*one FREE month</span> (for beginners Members)</p>



	<div class="page3-header2-divlast-img">

		<div class=" divlast-img1"><img src="images/adv3.png">

		<img src="images/adv4.png"></div>

		<div  class=" divlast-img2"><img src="images/adv5.png"></div>

	</div>

</div>

                  </div>

		<div class="clear"></div>



 		  <div class="sections_page">

		  <div class="clear"></div>



                  <div class="section2_page" >

                     <div class="suit_your_requirments" style="height: 250px;width: 100%;margin-top: 20px;">

                        <div class="section2_page_div11" >

                           <h4>Tell us your Membership Plan to suit your requirements:</h4>

                           <p>Verified JUNIOR Membership is FREE while there be some token administrative charges in few cases due to:<br/>

                              1. On Site Verification Cost &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	2. Product Edit Services.

                           </p>

                        </div>



                     </div>



                    <?php

					 // GET membership details

	   $sql = "SELECT * FROM `smembership_plan` WHERE `mp_status` = 1";

	   $membership_qry = mysqli_query($con, $sql) or die(mysql_error());

$senior_id = 0;

$senior_amount = 0.00;

$sponsor_id = 0;

$sponsor_amount = 0.00;

$membership_plans_array = array();

while($row = mysqli_fetch_object($membership_qry)){

if(strpos(strtolower($row->mst_name), 'senior') !== false) {

$senior_id = $row->mp_id;

$senior_amount = $row->mp_amount;

$row->mst_name = 'SENIOR';

}

if(strpos(strtolower($row->mst_name), 'sponsor') !== false || strpos(strtolower($row->mst_name), 'sponser') !== false) {

$sponsor_id = $row->mp_id;

$sponsor_amount = $row->mp_amount;

$row->mst_name = 'SPONSOR';

}

else if(strpos(strtolower($row->mst_name), 'junior') !== false || strpos(strtolower($row->mst_name), 'verified') !== false) {

$row->mst_name = 'JUNIOR (listing 4 products)';

}



array_push($membership_plans_array, $row);

} ?>



						 <div class="upgrader">
                                    <div class="upgrade1">
                                        <h4><img src="images/pyra.png"> JUNIOR<br/><span style="font-size: 16px;">Supplier Member</span></h4>
                                        <p  class="upgradepara1">$00<span> &nbsp;/month</span></p>
                                        <p  class="upgradepara2" >$00<span>&nbsp;/year</span></p>
                                       <!-- <input type="submit"  class="Mbtn1" value="Join NOW">-->
                                        <p  class="Mbtn1" >
                                            <a href="<?php echo ($uid > 0) ? '#shift' : 'http://arabyos.com/create_account.php'; ?>" >Join NOW</a>
                                        </p>
                                    </div>
                                    <div class="upgrade2" >
                                        <img src="images/ribbon.png" style="left:27.2em;position: absolute;bottom: 16.3em;" />
                                        <h4> <img src="images/cir.png" > SENIOR <br/><span  style="font-size: 19px;">Supplier Member</span></h4>
                                        <p class="upgradepara3"> $<?php echo round($senior_amount / 12, 2); ?> /month<br/><a href="#best"><span>Request For Best Quote ??</span></a></p>
                                        <p class="upgradepara4"> $ <?php echo round($senior_amount, 2); ?> /year<br/><a href="#best"><span>Request For Best Quote ??</span></a></p>
                                   <!--<input type="submit"  class="Mbtn3" value="Upgrade">-->
<?php if (getUserInfo($uid, 'usr_mp_id') == $senior_id) { ?>
                                            <p  class="Mbtn3" ><a href="" onClick="event.preventDefault();alert('Kindly be noted that you are already a SENIOR member');">Upgrade</a></p>
<?php } else { ?>
                                            <p  class="Mbtn3" ><a href="annual_subscription.php?id=<?php echo rand(10000, 99999) . md5($senior_id); ?>" >Upgrade</a></p>
                                        <?php } ?>
                                    </div>
                                    <div class="upgrade1">
                                        <h4><img src="images/sqr.png"> SPONSOR<br/><span style="font-size: 16px;">Supplier Member</span></h4>
                                        <p class="upgradepara1"> $275<span>&nbsp;/month</span></p>
                                        <p  class="upgradepara2"  > $ 3000<span>&nbsp;/year</span></p>
                                        <!--<input type="submit"  class="Mbtn2" value="Upgrade">-->
							   <p  class="Mbtn2" ><a href="annual_subscription.php?id=<?php echo rand(10000,99999).md5($sponsor_id ); ?>" >Upgrade</a></p>

							</div>

						 </div>

                    <div class="clear"></div>

                     <div class="verification_process " style="height: 80px;width: 100%;margin-top: 20px;">

                        <h4>Verification Process</h4>

                        <div class=" verification_process_div1">

                           <p>Step 1:<br/> &nbsp;&nbsp;<span>Check with the local government to verify your company &nbsp;&nbsp;is legally registerd and is currently operational</span></p>

                        </div>

                        <div class=" verification_process_div2">

                           <p >Step 2:<br/>&nbsp;&nbsp; <span>Check if your designated contact person is an emplyee and &nbsp;&nbsp; is authorized to represent your company on arabyos.com</span></p>

                        </div>

                     </div>



                  </div>

                  <hr  style="border-top: 1px solid black;"/>

                  <div class="section3_page">

                     <h4>Tell us your Advertisement Request :</h4>

                     <p id="section2_p1" >For Leader Suppliers: Publish FREE Banners ?! .. kindly contact our team via below form&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span ><a href="why_ARABYOS.php" style="color: blue"> Learn about Membership  Plans >></a></span>

                     </p>



                     <div class="midcont" style="float: left;">

                        <form  action="" method="post" name="membership_form">



                    <div class="bag" id="shift">

                           <label> Company Name (required)</label></label><br>

                           <input type="text" class="mtxtbx" name="cname"  id="cname" value="<?php echo $cname; ?>" required><br>

                           <label> Name (required)</label><br>

                           <input type="text" class="mtxtbx" name="fullname" id="fullname"  value="<?php echo $fullname; ?>" required><br/>

                           <label>Email (required)</label><br>

                           <input type="text" class="mtxtbx" name="email" id="email"  value="<?php echo $email; ?>" required><br>

                           <label>Mobile (required)</label><br>

                           <input type="text"  class="mtxtbx" name="mobile" id="mobile"  value="<?php echo $mobile; ?>"required><br/>

                           <label> Country (required)</label><br>

                           <input type="text"  class="mtxtbx" name="country" id="country" value="<?php echo $country; ?>" required><br>

                           <label> City (required)</label><br>

                           <input type="text" class="mtxtbx" name="city" id="city" value="<?php echo $city; ?>"  required><br/>

                           <label> Address</label> <br>

                           <input type="text"class="mtxtbx" name="address" id="address"  value="<?php echo $address; ?>"><br>

                           <label> Requirement (required</label>)<br>

                           <textarea rows="5" cols="5" name="requirement" id="requirement"  class="mtxtArea" required><?php echo $requirement; ?></textarea>

                           <br/><br/>

                           </div><br/>

                           <input type="submit"   name="Mb_Submit" class="Mbtn" value="Submit your Request Now">

						   <div id="msg" style="font-size: 16px; width: 80%; text-align: center;"><?php echo $msg; unset($_SESSION['m_msg']); ?></div>

						   <?php

		  if($msg != '' && $from == 0){

		echo '<script>

		jQuery(document).ready(function(){

		jQuery("html,body").animate({ scrollTop: jQuery	(".Mbtn").offset().top}, "fast");

		});

		</script>';

	} ?>

                        </form>

                        <br/>

                     </div>

                     <div class="midright" style=" float: right; padding-left: 0px;">



                        <?php

                    $sql_testi = "select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";

                    $res_testi = mysqli_query($con, $sql_testi);

                    if (mysqli_num_rows($res_testi) > 0) {



                    ?>

                        <div class="testimonialbox22">

                           <div class="testimonialbg">

                              <h2>Buyer Speaks&nbsp;&nbsp; <img src="images/sqr.png" width="25px"></h2>

                                 <?php while($row_testi = mysqli_fetch_object($res_testi)){?>





                              <div class="arrow_box">

                                <p> <i><span>&ldquo;</span><?php echo stripslashes($row_testi->testi_details); ?><span class="spacecomma">&rdquo;</span></i>

                                    </p>

                                 <!--<p> <i><span>&ldquo;</span>I am happy that I am a buyer member in ARABYOS, I could finally find my domestic and global requirement, it was great support to my business..<span class="spacecomma">&rdquo;</span></i>-->

                                 </p>

                              </div>

                              <div class="clear"></div>

                              <div class="testiwriter">

                                 <div class="pic1"><img src="upload/testimonial_img/<?php echo $row_testi->testi_image; ?>" alt=""/>



                                 <!--<img src="upload/testimonial_img/TESTIIMG-9637120x120.jpg" alt=""/>--></div>

                                 <div class="pic-info">



                                    <h5> <?php echo $row_testi->testi_name; ?>

                                   <!-- Ebraham Khodair--></h5>

                                    <p><a href="#"><?php echo get_country_name($row_testi->testi_cn_id); ?><!--Germany--></a></p>

                                 </div>

                              </div>

                              <?php }?>

                           </div>

                        </div>

                         <?php } ?>





                           	  <!--      <div class="testimonialbox22">

                           <div class="testimonialbg">

                              <h2>Buyer Speaks&nbsp;&nbsp; <img src="images/sqr.png" width="19px"></h2>

                              <div class="arrow_box">

                                 <p> <i><span>&ldquo;</span>I am happy that I am a buyer member in ARABYOS, I could finally find my domestic and global requirement, it was great support to my business.<span class="spacecomma">&rdquo;</span></i>

                                 </p>

                              </div>

                              <div class="clear"></div>

                              <div class="testiwriter">

                                 <div class="pic1"><img src="upload/testimonial_img/TESTIIMG-9637120x120.jpg" alt=""/></div>

                                 <div class="pic-info">

                                    <h5>Jack Smith</h5>

                                    <p><a href="#">Germany</a></p>

                                 </div>

                              </div>

                           </div>

                        </div>-->

                        <br/><br/><br/><br/>

                        <img src="images/shaik.png" class="sekh" style="float: right;"/>



                     </div>

                  </div>

</div>

               </div>

            </div>

            <!-- End of contentpage // -->

         </div>

      </div>



      <!-- End of middlesection // -->

      <!-- End of wrapper // -->



<!-- footer start -->

<footer class="footer">

     <!-- footer-searchsec start -->
    <div class="footer-searchsec">
        <div class="footer-searchsec-left">
            <div class="footer-searchsec-left-head">
            <div class="srchBx">
                  <h1 class="cd-headline clip is-full-width">
                   <span style="width: 100%; overflow: hidden; color:gray; font-family: Arial narrow;" class="cd-words-wrapper" >
                  <b class="is-hidden">Find Service Providers of assessed suppliers<span class="blinking-cursor" style="color: red">!</span></b>
                  <b class="is-visible">Find Service Providers of assessed suppliers<span class="blinking-cursor" style="color: red">!</span></b>
                      </span>
                     </h1>
               </div>
                <!--<h1>Find Service Providers of an Assessed Suppliers</h1>-->
            </div>
            <div class="footer-searchsec-left-form">
                <!--<form action="search.php">-->
           <!--      <script>
                  $(document).ready(function(){
                      $("#search-box1").keyup(function(){
                          $.ajax({
                          type: "POST",
                          url: "readproducts.php",
                          data:'keyword='+$(this).val(),
                          beforeSend: function(){
                              $("#search-box1").css("background","#FFF url(LoaderIcon.gif) no-repeat 165px");
                          },
                          success: function(data){
                              $("#suggesstion-box1").show();
                              $("#suggesstion-box1").html(data);
                              $("#search-box1").css("background","#FFF");
                          }
                          });
                      });
                  });

                  function selectCountry(val) {
                  $("#search-box1").val(val);
                  $("#suggesstion-box1").hide();
                  }
               </script>-->
                <form autocomplete="off" name="searchForm" action="search.php" onSubmit="return validsearch()" method="GET" id="hdr_frm">
                <input type="hidden" id="rctyp" name="rctyp" value="Products"/>
                    <div class="footer-searchsec-left-form-col1">

                       <!--<select id="rctyp" name="rctyp" class="page-header-col1-row2-col2-form-select">
                        <option value="Suppliers">Suppliers</option>
                        <option  value="Products" selected>Servic</option>
                        <option value="buy_lead">Buy Leads</option>
                        <option value="tender">Tender</option>
                        <!--<option value="auction">Auction</option>
                     </select>
                -->
                           <p>Services</p>

                    </div>
                    <div class="footer-searchsec-left-form-col2">
                        <input type="text" id="search-box1" name="keywords" placeholder="Search for any Business Services"
                               class="footer-searchsec-left-form-col2-input"/>
                               <div id="suggesstion-box1"></div>
                    </div>
                    <div class="footer-searchsec-left-form-col3">
                        <input type="submit" value="" class="footer-searchsec-left-form-col3-btn"/>
                    </div>
                </form>

                <div class="clear"></div>
            </div>
        </div>
        <div class="footer-searchsec-right">
            <a href="post-buy-req.php"  target="_blank" class="footer-searchsec-right-btn">Post Services Requests</a>
        </div>
        <div class="clear"></div>
    </div><!-- footer-searchsec close// -->

    <div class="footer-intro"><!-- footer-intro start -->

        <div class="footer-intro-left">

            <div class="footer-intro-left-logo">

			      <?php

					$footerlogo=GettingSite_Setting('unit-logo-footer');

					if($footerlogo!="")

					{

						$footerlogo2show = "sitelogo/".$footerlogo;

					}

					else

					{

					   $footerlogo2show = "images/footer-intro-left-logo4.png";

					}

					?>

			 <a href="#"><img src="<?php echo $footerlogo2show;?>" alt="" style="max-width:170px; max-height:108px;"/></a>

            </div>
                    <div class="footer-intro-left-text">
                        <ul>
                            <li><a href="about_us.php">About Us</a></li>
                            <li><a href="contact_us.php">Complaints</a></li>
                            <li><a href="contact_us.php">Feedback</a></li>
                            <!--<li><a href="privacy.php">Privacy & Policy</a></li>
                            <li><a href="terms.php">Tems & Conditions</a></li>-->
                            <li><a href="contact_us.php">Contact Us</a></li>
                            <li><a href="help.php">How it works</a></li>
                        </ul>
            </div>
        </div>
        <div class="footer-intro-right"><!-- footer-intro-right start -->
            <div class="footer-intro-right-col">
                <h2>Buyers Tools</h2>
                <ul>
                    <li><a href="post-buy-req.php">Request for Quotation</a></li>
                    <li><a href="manage-selloffer-alert.php">Manage Sale offer Alerts</a></li>
                    <li><a href="search_adv.php">Search Products / Services</a></li>
                    <li><a href="post-tender.php">Post Tenders & Get Bidders</a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>Suppliers Tools</h2>
                <ul>
                    <li><a href="product-add.php">Display Your Business Categories</a></li>
                    <li><a href="create-free-website.php">Create Website on ARABYOS</a></li>
                    <li><a href="manage-buylead-alert.php">Latest Buyleads Alerts</a></li>
                    <li><a href="post-sell-offer.php">Post Business Ads FREE</a></li>
                    <li><a href="post-auction.php">Post Auctions & Get Bidders</a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>ARABYOS Solutions</h2>
                <ul>
                    <li><a href="membership_plans.php">Companies Memberships</a></li>
                    <li><a href="my-enquiries.php">Business Inquiries</a></li>
                    <li><a href="manage-purchased-buyleads.php">Trade Leads For Me</a></li>
                     <li><a href="favorite.php">Products Of Interest</a></li>
                 <!--   <li><a href="manage-auction-alert.php">Advertise with us </a></li>-->
                       <li><a href="advertise-with-us.php">Advertise With Us </a></li>
                </ul>
            </div>
            <div class="footer-intro-right-col">
                <h2>Leader Suppliers Privilages</h2>
                <ul>
                    <li><a href="membership_plans.php">FREE PROMO PLAN</a></li>
                    <li><a href="advertise-with-us.php">FREE Banners Ads</a></li>
                    <li><a href="contact_us.php">FREE Bulk Newsletters</a></li>
                    <li><a href="contact_us.php">FREE Events News</a></li>
                </ul>
                    </div>

            

            </div>

        </div><!-- footer-intro-right close// -->

        <div class="clear"></div>

    </div><!-- footer-intro close// -->

</footer><!-- footer close// -->

<div class="copyright-row"><!-- copyright-row start -->

    <div class="copyright-row-col1">

        <p>Copyright &copy; <?php echo date("Y"); ?> <?php echo get_page_settings(4);?>. All rights reserved</p>

    </div>

    <div class="copyright-row-col2">

        <p><a href="terms.php">Terms of Use</a> | <a href="privacy.php">Privacy Policy</a> | <a href="contact_us.php">Link to Us</a></p>

    </div>

    <div class="clear"></div>

</div>

<!-- copyright-row close // -->
<div class="fixed-div"> <a href="#top"><img src="images/up.png" width="50"/></a> <a href="contact_us.php"><img src="images/complaint.png" width="50"/></a> </div>


</div>

<!-- start of right Tabs -->

<script src="js/easyResponsiveTabs.js" type="text/javascript"></script>

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



<!-- start of verticle menu -->

<script src="js/cust.js"></script>

<!-- End of verticle menu // -->



<!-- Animation text slider

<link rel="stylesheet" href="css/imNew-v6.css" type="text/css"/>-->

<!--<script src="js/im-style-vn6.3.js" type="text/javascript"></script>-->

<script src="js/bgSlider-v1.js" type="text/javascript"></script>

<!-- Animation text slider // -->



<script src="js/bootstrap.min.js"></script>



<!-- navigation  -->

<link rel="stylesheet" href="css/cssmenu.css" type="text/css"/>

<script src="js/script.js" type="text/javascript"></script>

<!-- navigation // -->



<!--Start of Tawk.to Script-->

<script type="text/javascript">

var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();

(function(){

var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];

s1.async=true;

s1.src='https://embed.tawk.to/584a3ee48a20fc0cac4f7e93/default';

s1.charset='UTF-8';

s1.setAttribute('crossorigin','*');

s0.parentNode.insertBefore(s1,s0);

})();

</script>

<!--End of Tawk.to Script-->

<!--<script type="text/javascript" src="http://workfromhomecompanies.net/its/ehabfa/livechat/php/app.php?widget-init.js"></script>-->

</body>

</html>

