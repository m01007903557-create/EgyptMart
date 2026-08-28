<?php
@ob_start();
include 'common.php';
$_SESSION["popup"] = 1;
$uid = $_SESSION['uid_indm'];
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>

        <title><?php echo getSiteTitle(); ?></title>
        <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
        <meta name="title" content="<?php echo getSiteTitle(); ?>">
            <meta name="keywords" content="<?php echo get_page_settings(2); ?>">
                <meta name="description" content="<?php echo get_page_settings(3); ?>">
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">



                        <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet"/>
                        <link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
                        <link href="css/jf-1.css" type="text/css" rel="stylesheet">
                            <link href="css/login.css" rel="stylesheet" type="text/css">

                                <script type="text/javascript" src="js/jquery-1.2.1.min.js"></script>
                                <style>
                                    @media(max-width:560px){

                                        html body footer .footer-searchsec-right {width:60% !important;   } 
                                        #m4t1 a img {	width: 100%;}
                                        #lfm table td a img {max-width: 100%;}
                                    }

                                </style>
                                </head>

                                <body class="search-show-box">



                                    <?php include "includes/header_new.php"; ?>




<!--		<div class="bt"><img src="images/z.gif" alt="<?php echo getWebSiteName(); ?>" width="1" height="1"></div>-->

                                    <!-- Header End Here::-->

                                    <script type="text/javascript" src="js/uservalid.js"></script>
                                    <div class="mt12 c3 ml "></div>
                                    <!--login:start-->
                                    <div class="log1 hidden-xs">
                                        <div class="log2 f1">
                                            <h1 class="lh1"
                                                title="هل أنت عضو فى منصة  -  سوق مصر على الإنترنت -  و أرابيوس دوت كوم ؟  ">
                                                Not an <?php echo getWebSiteName(); ?> member yet?</h1>
                                            <h2 class="lh2 mt5"
                                                title=" ! إنضم لأهم 10,000 مصنع ومصدر وتاجر جملة وشركة خدمات -  مصريين وعرب     ">
                                                Join Top 10,000 Arabian Suppliers & Buyers!
                                            </h2>
                                            <div class="log3 log4 bnr mt20 bnr"
                                                title="  إنشىء - مجانا -  صفحات أعمالك التجارية - لمصر والعرب - وحدد أماكن ترويج تجارتك ">
                                                <p class="log6">1 - Create your Products / Services B2B Website</p>
                                            </div>
                                            <div class="log3 log5 bnr mt20 bnr"
                                                title=" أنشر مجانا - طلبات شراء - وعروض بيع -  لشركتك   ">
                                                <p class="log6">2 - Post Buy Requirements and Sell Offers</p>
                                            </div>
                                            <div class="log3 log7 bnr mt20 bnr"
                                                title=" تلقى - إستفسارات - وطلبات شراء فى - بريدك وعلى موبايلك ">
                                                <p class="log6">3 - View Business Inquiries and Buy Requests</p>
                                            </div>
                                            <div class="log8"
                                                title=" إنشىء - موقع مصغر لشركتك -  فى سوق من 10,000 شركة  ">
                                                <ul class="f1">
                                                    <li>Company Website</li>
                                                    <li>Industry Newsletter</li>
                                                    <li>Company Profile</li>
                                                    <li><em>more...</em></li>
                                                </ul>
                                                <a href="create_account.php" class="log9 f2 fw"
                                                    title=" لم تسجل من قبل ؟  سجل مجانا الآن  ">Join Free</a>
                                                <div class="c3"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--login form:start-->
                                    <div class="log16 f1" id="loginform"><div class="log17" id="sg"title=" سجل دخول "><div class="miil fr"></div>Sign In</div>
                                        <form method="POST" action="login.php?redirect=<?php echo $_GET['redirect']; ?>" name="loginform" onSubmit="return userr_validate()" id="lfm">
                                            <?php if ($_SESSION['errr_msg'] != '') { ?>
                                                <div class="ma ebox1 log3 bnr fw lh" id="errr">
                                                    <?php echo $_SESSION['errr_msg']; //unset($_SESSION['errr_msg']); ?>
                                                </div>
                                            <?php } ?>
                                            <div class="fw mt12"title="  ضع - إيميل الدخول المسجل -  أو رقم الموبايل   ">Email Id / Mobile Number</div>
                                            <div class="mt5">
                                                <input tabindex="1" name="email" id="email" class="un" type="text" value="<?php echo $_GET['email']; ?>">
                                            </div>
                                            <div class="fw mt12"title=" ضع كلمة السر  ">Password</div>
                                            <div class="mt5"><input value="123456" tabindex="2" name="pass" id="pass" class="un" type="password"></div> 
                                            <div class="mt5 fp"title=" هل نسيت كلمة السر ؟  إضغط هنا - أكتب إيميلك - إستلم  رسالة  فى بريدك - أعد تعيين كلمة سر "><a tabindex="4" href="forgot-password.php">Forgot Password?</a></div>
                                            <div class="mt20"title=" سجل دخول  ">
                                                <input value="Sign In" tabindex="3" name="login" id="login" class="log18 fw" type="submit" style="cursor:pointer;">
                                            </div>
                                            <div class="c3 log19 mt20"></div>
                                            <div style="width:100%; padding-top: 20px; padding-left:0;">
                                                <table style="padding-left: 20px; width:100%;">
                                                    <tr>
                                                        
                                                    </tr>
                                                   <!-- <tr>
                                                        <td><?php include 'sociallogin/twit_log.php'; ?></td>
                                                        <td><?php include 'sociallogin/linkedin_log.php'; ?></td>
                                                    </tr> -->
                                                </table>    
                                            </div>
                                            <div class="c3 log19 mt20"></div>
                                            <div class="log20"title=" لم تسجل من قبل ؟  سجل مجانا الآن  " >Welcome, New User <a tabindex="5" href="create_account.php#signupform" class="fw">Sign up</a></div></form></div>
                                    <!--login form:ends-->
                                    <div class="c3">&nbsp;</div>
                                    <!-- <div id="m4t1" align="center">

                                        <?php
                                        $sql_adv = "select * from advertisement where adv_imagewidth='728' and adv_imageheight='90' and adv_status='1' order by rand() limit 1";
                                        $res_adv = mysqli_query($con, $sql_adv);
                                        if (mysqli_num_rows($res_adv) > 0) {
                                            $row_adv = mysqli_fetch_object($res_adv);
                                            ?><a href="//<?php echo $row_adv->adv_link; ?>" target="_blank"><img src="upload/advertisement/<?php echo $row_adv->adv_img; ?>" width="728" height="90"/></a><?php
                                        } else {
                                            ?>
                                            <img src="upload/advertisement/728-90-advertisement.png" border="0" vspace="0" width="728" height="90" hspace="0">
                                            <?php } ?>

                                    </div> -->
                                    <div class="c3">&nbsp;</div>
                                    <!--footer:start-->

                                    <script>
                                        $(document).ready(function(){
                                            $('.main-warpp').toggleClass('hidden-xs');
                                        });
                                    </script>
                                    <?php include 'includes/footer.php'; ?>