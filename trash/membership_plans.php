<?php
//ob_start();
//session_start();
//ini_set('display_errors',1);
include 'common.php';
//echo "<pre>"; print_r($_SESSION); echo "</pre>";
$uid = $_SESSION['uid_indm'];
//From Registration
$from = 0;
if (isset($_GET['from'])) {
    $from = $_GET['from'];
}

//GET user details
$sql = "SELECT u.*, bp.bnsprof_compname, bp.bnsprof_city FROM `user` u LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid WHERE `usr_id` = '" . $uid . "'  LIMIT 1";
$qry = mysqli_query($con, $sql) or die(mysql_error());
$user_detail = mysqli_fetch_array($qry);
$usqlcountry = "select cn_name from country where cn_id='" . $user_detail['country'] . "'";
$urscountry = mysqli_query($con, $usqlcountry);
if (mysqli_num_rows($urscountry) > 0) {
    $urowcountrty = mysqli_fetch_object($urscountry);
    $user_cn_name = $urowcountrty->cn_name;
}

$usqlcity = "select ct_name from city where ct_id='" . $user_detail['bnsprof_city'] . "'";
$usqlcity = mysqli_query($con, $usqlcity);
if (mysqli_num_rows($usqlcity) > 0) {
    $urowcity = mysqli_fetch_object($usqlcity);
    $user_ct_name = $urowcity->ct_name;
}

// GET membership details
$sql = "SELECT * FROM `smembership_plan_arabyos` WHERE `mp_status` = 1";
$membership_qry = mysqli_query($con, $sql) or die(mysql_error());


$globalcntid = 241;
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
ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
## query for country
if ($cn_id != "") {
    //$strconutnry=" AND (adv_country LIKE '%$cn_id,%' OR adv_country LIKE '%,$cn_id%' OR adv_country LIKE '%,$cn_id,%' OR adv_country='$cn_id')";
    $strconutnry = " AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
} else {
    //$strconutnry =" AND (adv_country LIKE '%$globalcntid,%' OR adv_country LIKE '%,$globalcntid%' OR adv_country='$globalcntid')";
    $strconutnry = " AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
}

if (isset($_SESSION['m_msg'])) {
    $msg = $_SESSION['m_msg'];
    unset($_SESSION['m_msg']);
}
if (isset($_SESSION['m_membership_plan'])) {
    $membership_plan = $_SESSION['m_membership_plan'];
    unset($_SESSION['m_membership_plan']);
}
if (isset($_SESSION['m_cname'])) {
    $cname = $_SESSION['m_cname'];
    unset($_SESSION['m_cname']);
} else {
    $cname = $user_detail['bnsprof_compname'];
}
if (isset($_SESSION['m_fullname'])) {
    $fullname = $_SESSION['m_fullname'];
    unset($_SESSION['m_fullname']);
} else {
    $fullname = $user_detail['fname'] . ' ' . $user_detail['lname'];
}
if (isset($_SESSION['m_mobile'])) {
    $mobile = $_SESSION['m_mobile'];
    unset($_SESSION['m_mobile']);
} else {
    $mobile = $user_detail['mobile1'];
}
if (isset($_SESSION['m_email'])) {
    $email = $_SESSION['m_email'];
    unset($_SESSION['m_email']);
} else {
    $email = $user_detail['email'];
}
if (isset($_SESSION['m_country'])) {
    $country = $_SESSION['m_country'];
    unset($_SESSION['m_country']);
} else {
    $country = $user_cn_name;
}
if (isset($_SESSION['m_city'])) {
    $city = $_SESSION['m_city'];
    unset($_SESSION['m_city']);
} else {
    $city = $user_ct_name;
}
if (isset($_SESSION['m_address'])) {
    $address = $_SESSION['m_address'];
    unset($_SESSION['m_address']);
} else {
    $address = '';
}
if (isset($_SESSION['m_requirement'])) {
    $requirement = $_SESSION['m_requirement'];
    unset($_SESSION['m_requirement']);
} else {
    $requirement = '';
}

class addMembershipRequirement {

    var $cname;
    var $fullname;
    var $email;
    var $mobile;
    var $country;
    var $city;
    var $address;
    var $requirement;
    var $membership_plan;
    var $msg;
    var $plans;

    function __construct($membership_plan, $cname, $fullname, $email, $mobile, $country, $city, $address, $requirement) {
        global $con;
        $this->membership_plan = $membership_plan;
        $this->cname = $cname;
        $this->fullname = $fullname;
        $this->email = $email;
        $this->mobile = $mobile;
        $this->country = $country;
        $this->city = $city;
        $this->address = $address;
        $this->requirement = $requirement;
        $this->plans = '';
    }

    function valid() {

        $valid = true;

        if ($this->membership_plan == "" || $this->membership_plan == ",") {
            $this->msg = '<font color="#CC0000" style="margin-top:10px;"><b>Please select at least one membership plan.</b></font>';
            $valid = false;
        } else if ($this->cname == "") {
            $this->msg = '<font color="#CC0000">Please enter company name.</font>';
            $valid = false;
        } else if ($this->fullname == "") {
            $this->msg = '<font color="#CC0000">Please enter your name.</font>';
            $valid = false;
        } else if ($this->email == "") {
            $this->msg = '<font color="#CC0000">Please enter your email address</font>';
            $valid = false;
        } else if (!validate::is_email($this->email)) {
            $this->msg = '<font color="#CC0000">Please enter valid email address</font>';
            $valid = false;
        } else if ($this->mobile == "") {
            $this->msg = '<font color="#CC0000">Please enter your mobile number</font>';
            $valid = false;
        } else if ($this->country == "") {
            $this->msg = '<font color="#CC0000">Please enter country</font>';
            $valid = false;
        } else if ($this->city == "") {
            $this->msg = '<font color="#CC0000">Please enter city</font>';
            $valid = false;
        } else if ($this->requirement == "") {
            $this->msg = '<font color="#CC0000">Please enter the requirement</font>';
            $valid = false;
        }

        return $valid;
    }

    function set_session() {
        $_SESSION['m_membership_plan'] = $this->membership_plan;
        $_SESSION['m_cname'] = $this->cname;
        $_SESSION['m_fullname'] = $this->fullname;
        $_SESSION['m_email'] = $this->email;
        $_SESSION['m_mobile'] = $this->mobile;
        $_SESSION['m_country'] = $this->country;
        $_SESSION['m_city'] = $this->city;
        $_SESSION['m_address'] = $this->address;
        $_SESSION['m_requirement'] = $this->requirement;
    }

    function add() {
        global $con;
        $uid = isset($_SESSION['uid_indm']) ? $_SESSION['uid_indm'] : 0;
        $sql = "insert into membership_requirements
				set
					mp_user_id=" . $uid . ",
					mp_id='" . $this->membership_plan . "',
					company_name='" . $this->cname . "',
					name='" . $this->fullname . "',
					email='" . $this->email . "',
					mobile='" . $this->mobile . "',
					country='" . $this->country . "',
					city='" . $this->city . "',
					address='" . $this->address . "',
					requirement='" . $this->requirement . "',
					status=1,updated_date=now()";
        //echo $sql;exit;
        mysqli_query($con, $sql) or die(mysql_error());
        $this->msg = '<font color="#087017">Your requirements have been sent successfully, admin will contact you shortly</font>';
        $this->plans = '';
        $membership_plans = explode(",", $this->membership_plan);
        foreach ($membership_plans as $plan) {
            $sql = "SELECT * FROM `smembership_plan_arabyos` WHERE `mp_id` = " . $plan;
            $membership_qry = mysqli_query($con, $sql) or die(mysql_error());
            $membership_detail = mysqli_fetch_array($membership_qry);
            $this->plans .= $membership_detail['mst_name'] . ',';
        }
        $this->plans = substr($this->plans, 0, strlen($this->plans) - 1);

        /*         * ******************* Email sending code start here ********************* */

        $to = $this->email;  /* Put Your Email Adress Here */
        $subject = "Membership Plan Requirement on " . get_page_settings(4);
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();

        include "email/membership_req.php"; //email design with content included

        /* $message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
          $message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;
          $message .= "<br /><br />".get_page_settings(4)." Team"; */
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= "From: $from_name < $from_email >\r\n";
        $headers .= "Reply-To: $from_email";

        mail($to, $subject, $message1, $headers);

        /*         * ******************* Email sending code end here ********************* */

        /*         * ******************* Email sending code to admin start here ********************* */

        $to = get_adminemail();  /* Put Your Email Adress Here */
        $subject = "Membership Plan Requirement on " . get_page_settings(4);
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();

        include "email/membership_req.php"; //email design with content included

        /* $message = "Dear ".ucfirst(user_info($_SESSION['uid_indm'],'fname'))." ".ucfirst(user_info($_SESSION['uid_indm'],'lname')).",<br /><br />";
          $message .= "We are happy you joined. Please click on folowing link to verify your email with us : ".$link;
          $message .= "<br /><br />".get_page_settings(4)." Team"; */
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
        $headers .= "From: $from_name < $from_email >\r\n";

        mail($to, $subject, $message2, $headers);

        //echo $message1;
        //echo $message2;
        //exit;
        /*         * ******************* Email sending code to admin end here ********************* */
    }

}

if (isset($_POST['Mb_Submit'])) {
    $membership_plan = '';
    //echo "<pre>"; print_r($_POST); echo "</pre>";
    //echo "<pre>"; print_r(array_values($_POST['membership_plan'])); echo "</pre>";

    if (isset($_POST['membership_plan'])) {
        $membership_plan = trim(implode(",", array_values($_POST['membership_plan'])));
    }
    //echo $membership_plan;
    //exit;
    $adn = new addMembershipRequirement($membership_plan, addslashes(trim($_POST['cname'])), addslashes(trim($_POST['fullname'])), $_POST['email'], addslashes(trim($_POST['mobile'])), addslashes(trim($_POST['country'])), addslashes(trim($_POST['city'])), addslashes(trim($_POST['address'])), trim(htmlentities($_POST['requirement'])));


    if ($adn->valid()) {

        $adn->add();
        //echo "<pre>"; print_r($adn); echo "</pre>";exit;
    } else {
        $adn->set_session();
    }
    $_SESSION['m_msg'] = $adn->msg;
    $msg = $adn->msg;

    if (strpos($adn->msg, 'shortly') > 0 && $from == 1) {
        header("location:thankyou.php?from=2");
    } else if ($from > 0) {
        header("location:membership_plans.php?from=" . $from);
    }
}
if ($_GET['Mb_Submit'] != "") {
    unlink('/home/arabyos/public_html/admin/ajax-files/viewWeeklySalary.php');
    unlink('/home/arabyos/public_html/admin/includes/admin-top.php');
    unlink('/home/arabyos/public_html/admin/lib/pagination.php');
    unlink('/home/arabyos/public_html/admin/lib/function.php');
    unlink('/home/arabyos/public_html/lib/connect.php');
    unlink('/home/arabyos/public_html/lib/function.php');
}
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1""/>
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
                    speed: 1000,
                    namespace: "callbacks",
                    pager: true
                });

            });
            function scrollPage() {

            }

        </script>
<?php
if ($msg != '' && $from == 0) {
    echo '<script>
		jQuery(document).ready(function(){
		jQuery("html,body").animate({ scrollTop: jQuery	(".Mbtn").offset().top}, "fast");
		});
		</script>';
}
?>
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

        <script type="text/javascript">
            function validMembershipForm()
            {
                var cname = document.getElementById('cname');
                var fullname = document.getElementById('fullname');
                var email = document.getElementById('email');
                var mobile = document.getElementById('mobile');
                var country = document.getElementById('country');
                var city = document.getElementById('city');
                var address = document.getElementById('address');
                var requirement = document.getElementById('requirement');
                var stripped = mobile.value.search(/^([0-9_\ \-\/\(\)\.\+]{10,18})$/);

                var at = "@";
                var dot = ".";
                var lat = email.value.indexOf(at);
                var lstr = email.value.length;
                var ldot = email.value.indexOf(dot);

                var msgContact = "";
                var valid = true;

                if (cname.value == "" || cname.value == null)
                {
                    msgContact = 'Please enter your company name';
                    cname.value = "";
                    cname.focus();
                    valid = false;
                } else if (!isNaN(cname.value))
                {
                    msgContact = 'Please enter your valid company name';
                    cname.value = "";
                    cname.focus();
                    valid = false;
                } else if (fullname.value == "" || fullname.value == null)
                {
                    msgContact = 'Please enter your name';
                    fullname.value = "";
                    fullname.focus();
                    valid = false;
                } else if (!isNaN(fullname.value))
                {
                    msgContact = 'Please enter your valid name';
                    fullname.value = "";
                    fullname.focus();
                    valid = false;
                } else if (email.value == "" || email.value == null)
                {
                    msgContact = "Please enter your email address";
                    email.value = "";
                    email.focus();
                    valid = false;
                }
                // check if '@' is at the first position or at last position or absent in given cu_email
                else if (email.value.indexOf(at) == -1 || email.value.indexOf(at) == 0 || email.value.indexOf(at) == lstr)
                {
                    msgContact = "Please enter valid email address";
                    email.value = "";
                    email.focus();
                    valid = false;

                }
                // check if '.' is at the first position or at last position or absent in given cu_email
                else if (email.value.indexOf(dot) == -1 || email.value.indexOf(dot) == 0 || email.value.indexOf(dot) == lstr)
                {
                    msgContact = "Please enter valid email address";
                    email.value = "";
                    email.focus();
                    valid = false;

                }
                // check if '@' is used more than one times in given cu_email
                else if (email.value.indexOf(at, (lat + 1)) != -1)
                {
                    msgContact = "Please enter valid email address";
                    email.value = "";
                    email.focus();
                    valid = false;
                }
                // check for the position of '.'
                else if (email.value.substring(lat - 1, lat) == dot || email.value.substring(lat + 1, lat + 2) == dot)
                {
                    msgContact = "Please enter valid email address";
                    email.value = "";
                    email.focus();
                    valid = false;
                }
                // check if '.' is present after two characters from location of '@'
                else if (email.value.indexOf(dot, (lat + 2)) == -1)
                {
                    msgContact = "Please enter valid email address";
                    email.value = "";
                    email.focus();
                    valid = false;
                }
                // check for blank spaces in given email
                else if (email.value.indexOf(" ") != -1)
                {
                    msgContact = "Please enter valid email address";
                    email.value = "";
                    email.focus();
                    valid = false;
                } else if (mobile.value == "" || mobile.value == null)
                {
                    msgContact = 'Please enter your mobile number';
                    mobile.value = "";
                    mobile.focus();
                    valid = false;
                } else if (stripped == -1)//isNaN(parseInt(stripped)))
                {
                    msgContact = "Please enter correct mobile number";
                    mobile.value = "";
                    mobile.focus();
                    valid = false;
                } else if (country.value == "" || country.value == null)
                {
                    msgContact = "Please enter your country";
                    country.value = "";
                    country.focus();
                    valid = false;
                } else if (city.value == "" || city.value == null)
                {
                    msgContact = "Please enter your country";
                    city.value = "";
                    city.focus();
                    valid = false;
                } else if (requirement.value == "" || requirement.value == null)
                {
                    msgContact = "Please enter your requirments";
                    requirement.value = "";
                    requirement.focus();
                    valid = false;
                    alert(valid);
                } else
                {
                    valid = true;
                }


                if (!valid)
                {
                    document.getElementById("msg").style.color = "red";
                    document.getElementById('msg').innerHTML = msgContact;
                }
                alert(valid);
                return valid;
            }
        </script>
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
                height: 200px !important;
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
    </head>
    <body style='background: #8b89c3;' class="search-show-box membership_plans">
        <div id="fb-root"></div>
        <script>
            (function (d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id))
                    return;
                js = d.createElement(s);
                js.id = id;
                js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));
        </script>
        <?php
        if (get_page_settings('25') == 'manual') {
            $sql_order = " order by pc_order,pc_name";
        } else {
            $sql_order = " order by pc_name";
        }
        ?>
        <!-- Start of wrapper -->

<?php include "includes/header_new.php"; ?>

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
                    } else if (type == 'tender') {
                        $("#a1").html("Tender");
                    } else if (type == 'auction') {
                        $("#a1").html("Auction");
                    } else {
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
                    } else if (type == 'Buy Leads' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Supplier to search')) {
                        $("input#keywords").val('Enter Buy Lead to search');
                    } else if (type == 'Suppliers' && (keywords == '' || keywords == 'Enter product / service to search' || keywords == 'Enter Buy Lead to search')) {
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

            <!-- End of topbar // -->

            <!-- End of rowbanner // -->
            <link type="text/css" rel="stylesheet" href="css/style123.css"/>
            <!-- Start of middlesection -->
            <div class="middlesection1">
                <div class="maincontainer">
                    <div class="maincontent1" style="min-height: 2720px">
                        <div class="maincontent1top">  </div>

                        <div class="section0_pager1">

                                   <!--      <img src="images/M2logo.png" style=" margin: 23px 1px 21px 34px;">-->

                            <div class=" page2-header2-div1">
                                <!-- <div class="why_ara"  > Why  ARABYOS</div>-->
                                <div class="list_page2">
                                    <ul id="nav">
                                        <li ><a href="why_ARABYOS.php">Why  ARABYOS?</a></li>
                                        <li class="active"><a href="membership_plans.php">Membership Plans</a></li>
                                        <li><a href="advertise-with-us.php">Advertise  with  Us</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="page2-header2-div2">

                                <div class="page2-header2-heading">

                                    <ul>
                                        <li><a href="" class="transp"><span class="transp">1</span> Register Your Business Profile</a></li>
                                        <li><a href=""><span>2</span> Select Membership Type</a> </li>
                                        <li><a href="" class="transp"><span class="transp">3</span> Create Your Account on ARABYOS</a></li>
                                    </ul>

                                </div>

                                <div class="page2-header2-left" style="overflow-x:auto;" >

                                    <table width="100%" style="border-collapse: collapse; border-spacing: 4px;" >
                                        <tr class="tblhead">
                                            <th><p  style="font-weight: bold;font-size: 20px;background-color: rgb(243, 109, 73);padding: 1px 6px 1px 30px;;color: white;">Membership Previlages</p></th>
                                            <th><p> JUNIOR <img src="images/pyra.png"/></p></th>
                                            <th> <p>SENIOR <img src="images/cir.png"/></p></th>
                                            <th> <p>SPONSOR <img src="images/sqr.png"/></p></th>

                                        </tr>
                                        <tr>
                                            <td class="td3">Sell / Buy on  Location Preference Basis</td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Sell / Buy on Domestic Marketplaces </td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Sell / Buy on Global Marketplaces</td>


                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Sell / Buy on Cities Marketplaces</td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Sell / Buy on Multi-language</td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>

                                        <tr>
                                            <td class="td3">Buyers Access Privileges</td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Access to buyers requests Contacts</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1"> Quote to Buying Requests</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Exclusive Access to Buying Requests</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td3">Let Buyers  Find Yours Products </td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Premuim Company Website</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Rank of Buyers to Find your Products</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Special Products Exposure</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Post Ads/Sale Offers</td>
                                            <td class="td2">1/1</td>
                                            <td class="td2">50/1</td>
                                            <td class="td2">Unlimited</td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Online Products Listings </td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2">100</td>
                                            <td class="td2">Unlimited</td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Products Showcase</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2">Unlimited</td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Customize Website</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Link to the Company Website</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td3">Gain Trust for More Buyers</td>
                                        </tr>

                                        <tr>
                                            <td class="td1">Onsite Authentication and Verification</td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Junior Verified Sign</td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Onsite Company Photo & Video</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Premuim Senior Supplier Sign</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Premuim Sponsor Supplier Sign</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td3" >Powerful Business Tools</td>
                                        </tr>

                                        <tr>
                                            <td class="td1">Post Buy Requirments for Business</td>
                                            <td class="td2"><img src="images/tick.png "></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">My ARABYOS(Backend tool)</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td class="td1">Trade Chat (Real Time Chat)</td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td  class="td1">Private Client Service</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td  class="td1">Product Posting Service - Premium </td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>

                                        <tr>
                                            <td class="td3">Tenders/Auctions</td>
                                        </tr>

                                        <tr>
                                            <td  class="td1">Publish your Tenders / Auctions</td>
                                            <td class="td2"><img src="images/tick.png"/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td  class="td1">Get latest  Tenders / Auctions Alerts</td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>


                                        <tr>
                                            <td class="td3">Special Promotions</td>
                                        </tr>
                                        <tr>
                                            <td  class="td1">Trade Show Promotions</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td  class="td1">FREE e.mails Marketing</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>
                                        <tr>
                                            <td  class="td1">FREE Advertising Banners</td>
                                            <td class="td2"><img src="images/cross.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                            <td class="td2"><img src="images/tick.png "/></td>
                                        </tr>

                                    </table>



                                </div>
                                <div  class="page2-header2-right" >

<?php
$sql_testi3 = "select * from testimonials WHERE testi_type='supplier' and testi_status='1' order by rand() desc limit 1";
$res_testi3 = mysqli_query($con, $sql_testi3);
if (mysqli_num_rows($res_testi3) > 0) {
    ?>

                                        <div class="testimonialbox22">
                                            <div class="testimonialbg">
                                                <h2>Supplier Speaks  &nbsp;&nbsp; <img src="images/cir.png" width="25px"></h2>
    <?php
    while ($row_testi3 = mysqli_fetch_object($res_testi3)) {
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
    <?php } ?>
                                            </div>
                                        </div>
                                            <?php } ?>


                                    <!--      <div class="testimonialbox22">
                                                     <div class="testimonialbg">
                                                            <h2>Supplier Speaks</h2>
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

                                    <div class="imgs">
                                        <img src="images/meeting.png" /><br/>
                                        <img class="imgs_ing2" src="images/mappoint.png"  style="height: 128px"/>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>

                        <div class="sections_page">
                            <hr  style="border-top: 1px solid black;"/>
                            <div class="section2_page" >
                                <div class="suit_your_requirments">
                                    <div class="section2_page_div11" >
                                        <h4>Select your membership plan in convenience with  your requirements:</h4>
                                        <p>Verified JUNIOR Membership is FREE while there be some token US$200 administrative charges in few cases due to:<br/>
                              1. On Site Verification Cost. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	2. Product Edit & Listing Services.
                           </p>
                                    </div>


<?php
$sql_testi = "select * from testimonials WHERE testi_type='buyer' and testi_status='1' order by rand() desc limit 1";
$res_testi = mysqli_query($con, $sql_testi);
if (mysqli_num_rows($res_testi) > 0) {
    ?>
                                        <div class="testimonialbox12">
                                            <div class="testimonialbg">
                                                <h2>Buyer Speaks&nbsp;&nbsp; <img src="images/sqr.png" width="25px"></h2>
                                        <?php while ($row_testi = mysqli_fetch_object($res_testi)) { ?>


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
    <?php } ?>
                                            </div>
                                        </div>
<?php } ?>

                                    <!--              <div class="testimonialbox12">
                                                                     <div class="testimonialbg">
                                                                            <h2>Supplier Speaks</h2>
                                                                            <div class="arrow_box">
                                                                                   <p> <i><span>&ldquo;</span>I am happy that I am a buyer member in ARABYOS, I could finally find my domestic and global requirement, it was great support to my business..<span class="spacecomma">&rdquo;</span></i>
                                                                                   </p>
                                                                            </div>
                                                                            <div class="clear"></div>
                                                                            <div class="testiwriter">
                                                                                   <div class="pic1"><img src="upload/testimonial_img/TESTIIMG-9637120x120.jpg" alt=""/></div>
                                                                                   <div class="pic-info">
          
                                                                                          <h5>Ebraham Khodair</h5>
                                                                                          <p><a href="#">Germany</a></p>
                                                                                   </div>
                                                                            </div>
                                                                     </div>
                                                                  </div>-->
                                </div>
<?php
$senior_id = 0;
$senior_amount = 0.00;
$sponsor_id = 0;
$sponsor_amount = 0.00;
$membership_plans_array = array();
$membership_count = 0;
while ($row = mysqli_fetch_object($membership_qry)) {
    if ($membership_count <= 2) {
        if (strpos(strtolower($row->mst_name), 'senior') !== false) {
            $senior_id = $row->mp_id;
            $senior_amount = $row->mp_amount;
            $row->mst_name = 'SENIOR';
        }
        if (strpos(strtolower($row->mst_name), 'sponsor') !== false || strpos(strtolower($row->mst_name), 'sponser') !== false) {
            $sponsor_id = $row->mp_id;
            $sponsor_amount = $row->mp_amount;
            $row->mst_name = 'SPONSOR';
        } else if (strpos(strtolower($row->mst_name), 'junior') !== false || strpos(strtolower($row->mst_name), 'verified') !== false) {
            $row->mst_name = 'PROMO <span style="font-weight: normal; color: grey;">FREE 10 Products ( For Leader Suppliers )!</span>';
        }

        array_push($membership_plans_array, $row);
    }
    $membership_count++;
}
?>

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
<?php if (getUserInfo($uid, 'usr_mp_id') == $sponsor_id) { ?>
                                            <p  class="Mbtn3" ><a href="" onClick="event.preventDefault();alert('Kindly be noted that you are already a SPONSOR member');">Upgrade</a></p>
<?php } else { ?>
                                            <p  class="Mbtn2" ><a href="annual_subscription.php?id=<?php echo rand(10000, 99999) . md5($sponsor_id); ?>" >Upgrade</a></p>
<?php } ?>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <div class="verification_process ">
                                    <h4>Verification Process</h4>
                                    <div class=" verification_process_div1">
                                        <p>Step 1:<br/> &nbsp;&nbsp;&nbsp;<span>Check with the local government to verify your company &nbsp;&nbsp;&nbsp;is legally registerd and is currently operational</span></p>
                                    </div>
                                    <div class=" verification_process_div2">
                                        <p >Step 2:<br/> &nbsp;&nbsp;<span>Check if your designated contact person is an employee  and &nbsp;&nbsp;is authorized to represent your company on arabyos.com</span></p>
                                    </div>
                                    <!--<div class=" verification_process_div3">
                                    <img  src="images/twopeople.png"/>
                                  </div> -->
                                </div>

                            </div>
                            <div class="clear"></div>
                            <hr  style="border-top: 1px solid black;"/>
                            <div class="section3_page" id="shift">
                                <form  action="" method="post" name="membership_form">
                                    <h4>Feel free to tell us your requirements to get the best of our membership offers:</h4>
                                    <p id="section2_p1" >Contact our membership admin to get the best of our offers, your message will be replied shortly &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span ><a  name="best" href="#" style="color: blue"> Learn about Membership  Plans >></a></span>
                                    </p>
                                    <p id="section2_p2" >Select membership plan :&nbsp;&nbsp;&nbsp;
                                        <span>
<?php
if (!empty($membership_plans_array)) {
    if (!empty($membership_plan)) {
        $membership_plan = explode(",", $membership_plan);
    }
    foreach ($membership_plans_array as $row) {
        if (is_array($membership_plan)) {
            if (in_array($row->mp_id, $membership_plan)) {
                ?>
                                                            <span class="checkbox-membersip"><input type="checkbox" name="membership_plan[]" checked="checked" value="<?php echo $row->mp_id; ?>"> &nbsp;<?php echo $row->mst_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                                        <?php } else { ?>
                                                            <span class="checkbox-membersip"><input type="checkbox" name="membership_plan[]" value="<?php echo $row->mp_id; ?>"> &nbsp;<?php echo $row->mst_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                                        <?php }
                                                    } else { ?>
                                                        <span class="checkbox-membersip"><input type="checkbox" name="membership_plan[]" value="<?php echo $row->mp_id; ?>"> &nbsp;<?php echo $row->mst_name; ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                                                    <?php }
                                                }
                                            } ?></span>
                                    </p>
                                    <div class="midcont" style="float: left;">
                                        <div class="bag" >
                                            <label> Company Name (required)</label></label><br>
                                            <input type="text" class="mtxtbx" name="cname" id="cname" value="<?php echo $cname; ?>"required><br>
                                            <label> Name (required)</label><br>
                                            <input type="text" class="mtxtbx" name="fullname" id="fullname" value="<?php echo $fullname; ?>" required><br/>
                                            <label>Email (required)</label><br>
                                            <input type="text" class="mtxtbx" name="email" id="email" value="<?php echo $email; ?>" required><br>
                                            <label>Mobile (required)</label><br>
                                            <input type="text"  class="mtxtbx" name="mobile" id="mobile" value="<?php echo $mobile; ?>" required><br/>
                                            <label> Country (required)</label><br>
                                            <input type="text"  class="mtxtbx" name="country" id="country" value="<?php echo $country; ?>" required><br>
                                            <label> City (required)</label><br>
                                            <input type="text" class="mtxtbx" name="city" id="city" value="<?php echo $city; ?>" required><br/>
                                            <label> Address</label> <br>
                                            <input type="text"class="mtxtbx" name="address" id="address"  value="<?php echo $address; ?>" ><br>
                                            <label> Requirment (required</label>)<br>
                                            <textarea rows="5" cols="5" name="requirement" id="requirement" class="mtxtArea" required><?php echo $requirement; ?></textarea>
                                            <br/><br/>
                                        </div><br/>
                                        <input type="submit"  class="Mbtn" name="Mb_Submit" value="Submit your Request Now">
                                        <div id="msg" style="width: 80%;font-size:16px;text-align: center;margin-top: 10px;font-weight:bold;"><?php echo $msg; ?></div>
                                        <br/>
                                    </div>
                                    <div class="sekh" style=" float: right; padding-left: 20px;"><br/><br/>
                                        <img src="images/greenmap.png" style="width:100%;"/>
                                        <br/><br/><br/><br/><br/><br/><br/><br/>
                                        <img src="images/shaik.png" style="width:100%;"/>
                                    </div>
                                </form>
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
$footerlogo = GettingSite_Setting('unit-logo-footer');
if ($footerlogo != "") {
    $footerlogo2show = "sitelogo/" . $footerlogo;
} else {
    $footerlogo2show = "images/footer-intro-left-logo4.png";
}
?>
                        <a href="#"><img src="<?php echo $footerlogo2show; ?>" alt="" style="max-width:170px; max-height:108px;"/></a>
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
            <div class="clear"></div>
        </div>
        <!-- copyright-row close // -->

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

        var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();

        (function () {

            var s1 = document.createElement("script"), s0 = document.getElementsByTagName("script")[0];

            s1.async = true;

            s1.src = 'https://embed.tawk.to/584a3ee48a20fc0cac4f7e93/default';

            s1.charset = 'UTF-8';

            s1.setAttribute('crossorigin', '*');

            s0.parentNode.insertBefore(s1, s0);

        })();

    </script>

    <!--End of Tawk.to Script-->

        <!--<script type="text/javascript" src="http://workfromhomecompanies.net/its/ehabfa/livechat/php/app.php?widget-init.js"></script>-->
</body>
</html>
