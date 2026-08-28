<?php
/**
 * advertise-with-us.php - صفحة طلب المساحات الإعلانية
 * تم ترقيته لـ PHP 8.3
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
$msg = '';
$from = 0;

// جلب بيانات المستخدم
$user_detail = [];
$user_cn_name = '';
$user_ct_name = '';

if ($uid > 0) {
    $sql = "SELECT u.*, bp.bnsprof_compname, bp.bnsprof_city 
            FROM `user` u 
            LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid 
            WHERE u.`usr_id` = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user_detail = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
    }
    
    if (!empty($user_detail['country'])) {
        $usqlcountry = "SELECT cn_name FROM country WHERE cn_id = ?";
        $ustmt = mysqli_prepare($con, $usqlcountry);
        if ($ustmt) {
            mysqli_stmt_bind_param($ustmt, 'i', $user_detail['country']);
            mysqli_stmt_execute($ustmt);
            $uresult = mysqli_stmt_get_result($ustmt);
            if (mysqli_num_rows($uresult) > 0) {
                $urow = mysqli_fetch_object($uresult);
                $user_cn_name = $urow->cn_name ?? '';
            }
            mysqli_stmt_close($ustmt);
        }
    }
    
    if (!empty($user_detail['bnsprof_city'])) {
        $usqlcity = "SELECT ct_name FROM city WHERE ct_id = ?";
        $ustmt = mysqli_prepare($con, $usqlcity);
        if ($ustmt) {
            mysqli_stmt_bind_param($ustmt, 'i', $user_detail['bnsprof_city']);
            mysqli_stmt_execute($ustmt);
            $uresult = mysqli_stmt_get_result($ustmt);
            if (mysqli_num_rows($uresult) > 0) {
                $urow = mysqli_fetch_object($uresult);
                $user_ct_name = $urow->ct_name ?? '';
            }
            mysqli_stmt_close($ustmt);
        }
    }
}

$globalcntid = 241;
$cn_id = 0;
$cn_name = "Global";

if (isset($_COOKIE['loc_id'])) {
    $cn_id = (int)$_COOKIE['loc_id'];
    $sqlcountry = "SELECT cn_name FROM country WHERE cn_id = ?";
    $stmt = mysqli_prepare($con, $sqlcountry);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $cn_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_object($result);
            $cn_name = $row->cn_name ?? 'Global';
        }
        mysqli_stmt_close($stmt);
    }
}

ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);

// بناء شرط الدولة للإعلانات
if ($cn_id != "") {
    $strconutnry = " AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
} else {
    $strconutnry = " AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
}

// استعادة بيانات الجلسة
if (isset($_SESSION['m_msg'])) {
    $msg = $_SESSION['m_msg'];
    unset($_SESSION['m_msg']);
}

$cname = isset($_SESSION['m_cname']) ? $_SESSION['m_cname'] : ($user_detail['bnsprof_compname'] ?? '');
$fullname = isset($_SESSION['m_fullname']) ? $_SESSION['m_fullname'] : (isset($user_detail['fname'], $user_detail['lname']) ? $user_detail['fname'] . ' ' . $user_detail['lname'] : '');
$mobile = isset($_SESSION['m_mobile']) ? $_SESSION['m_mobile'] : ($user_detail['mobile1'] ?? '');
$email = isset($_SESSION['m_email']) ? $_SESSION['m_email'] : ($user_detail['email'] ?? '');
$country = isset($_SESSION['m_country']) ? $_SESSION['m_country'] : $user_cn_name;
$city = isset($_SESSION['m_city']) ? $_SESSION['m_city'] : $user_ct_name;
$address = isset($_SESSION['m_address']) ? $_SESSION['m_address'] : '';
$requirement = isset($_SESSION['m_requirement']) ? $_SESSION['m_requirement'] : '';

// تعريف الكلاس (مرة واحدة فقط)
if (!class_exists('addMembershipRequirement')) {
    class addMembershipRequirement
    {
        public $cname;
        public $fullname;
        public $email;
        public $mobile;
        public $country;
        public $city;
        public $address;
        public $requirement;
        public $msg;
        public $plans;

        function __construct($cname, $fullname, $email, $mobile, $country, $city, $address, $requirement)
        {
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

        function valid()
        {
            $valid = true;

            if ($this->cname == "") {
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

        function set_session()
        {
            $_SESSION['m_cname'] = $this->cname;
            $_SESSION['m_fullname'] = $this->fullname;
            $_SESSION['m_email'] = $this->email;
            $_SESSION['m_mobile'] = $this->mobile;
            $_SESSION['m_country'] = $this->country;
            $_SESSION['m_city'] = $this->city;
            $_SESSION['m_address'] = $this->address;
            $_SESSION['m_requirement'] = $this->requirement;
        }

        function add()
        {
            global $con;
            $uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
            
            $sql = "INSERT INTO membership_requirements
                    SET mp_user_id = ?,
                        mp_id = 'Advertisement Request',
                        company_name = ?,
                        name = ?,
                        email = ?,
                        mobile = ?,
                        country = ?,
                        city = ?,
                        address = ?,
                        requirement = ?,
                        status = 1,
                        updated_date = NOW()";
            
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'issssssss', $uid, $this->cname, $this->fullname, $this->email, $this->mobile, $this->country, $this->city, $this->address, $this->requirement);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
            
            $this->msg = '<font color="#087017">Your requirements have been sent successfully, sales team will contact you shortly.</font>';
            $this->plans = 'Advertisement Request';

            // إرسال البريد الإلكتروني للمستخدم
            $to = $this->email;
            $subject = "Advertisement Requirement on " . get_page_settings(4);
            $from_name = get_page_settings(4);
            $from_email = get_adminemail();
            $plan = "Advertisement";
            include "email/membership_req.php";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= "From: $from_name < $from_email >\r\n";
            $headers .= "Reply-To: $from_email";
            mail($to, $subject, $message1, $headers);

            // إرسال البريد الإلكتروني للإدارة
            $to = get_adminemail();
            $subject = "Advertisement Requirement on " . get_page_settings(4);
            $from_name = get_page_settings(4);
            $from_email = get_adminemail();
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= "From: $from_name < $from_email >\r\n";
            mail($to, $subject, $message2, $headers);
        }
    }
}

if (isset($_POST['Mb_Submit'])) {
    $adn = new addMembershipRequirement(
        addslashes(trim($_POST['cname'])),
        addslashes(trim($_POST['fullname'])),
        $_POST['email'],
        addslashes(trim($_POST['mobile'])),
        addslashes(trim($_POST['country'])),
        addslashes(trim($_POST['city'])),
        addslashes(trim($_POST['address'])),
        addslashes(trim($_POST['requirement']))
    );

    if ($adn->valid()) {
        $adn->add();
    } else {
        $adn->set_session();
    }
    $_SESSION['m_msg'] = $adn->msg;
    $msg = $adn->msg;
    if (strpos($adn->msg, 'shortly') > 0 && $from == 1) {
        header("location: thankyou.php?from=2");
    }
}
?>

<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css'/>
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <link href="css/style.css" rel="stylesheet" type="text/css"/>
    <link href="css/responsive1.css" rel="stylesheet" type="text/css"/>
    <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css"/>
    <link href="css/im-style-v1.css" rel="stylesheet" type="text/css"/>
    <!--[if IE]>
    <script src="js/html5.js"></script> <![endif]-->
    <link href="css/verticle-menu.css" rel="stylesheet" type="text/css"/>
    <link type="text/css" rel="stylesheet" href="css/theme.css"/>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script type="text/javascript" src="js/jquery.accessible-news-slider.js"></script>
    <link href="css/type.css" rel="stylesheet" type="text/css"/>
    <script src="js/responsiveslides.min.js"></script>
    <script>
        $(function () {
            $("#slider").responsiveSlides({
                auto: true,
                nav: false,
                speed: 500,
                namespace: "callbacks",
                pager: true
            });
        });
    </script>
    <script type="text/javascript">
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
    <style>
        .countrytwo {
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
            .footer .footer-searchsec { max-width: 860px !important; }
            .footer-searchsec-left { width: calc(100% - 35%) !important; }
            .footer-searchsec-right { margin-left: 28px !important; }
        }
        @media (min-width: 1301px) {
            .footer-searchsec-right { margin-left: 38px !important; }
        }
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
    if (get_page_settings('25') == 'manual') {
        $sql_order = " order by pc_order,pc_name";
    } else {
        $sql_order = " order by pc_name";
    }
    ?>
    <?php include "includes/header_new.php"; ?>
    <div class="wrapper">
        <script>
            function showsrchm() { $("#smnu").show(); }
            function hidesrchm() { $("#smnu").hide(); }
            function OutboundLink(type) {
                if (type == 'buy_lead') { $("#a1").html("Buy Leads"); }
                else if (type == 'tender') { $("#a1").html("Tender"); }
                else if (type == 'auction') { $("#a1").html("Auction"); }
                else { $("#a1").html(type); }
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
            function setCountryLocation(id) {
                $.post("setCountryLocation.php", {loc_id: id}, function (data) {
                    if (data != 0) {
                        location.reload();
                    }
                });
            }
            function unsetCountryLocation() {
                $.post("unsetCountryLocation.php", function (data) {
                    location.reload();
                });
            }
        </script>
        <style type="text/css">
            .zoomin1 img { height: 78px; width: 219px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }
            .zoomin1 img:hover { width: 229px; height: 88px; }
            .zoomin2 img { height: 66px; width: 200px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }
            .zoomin2 img:hover { width: 210px; height: 77px; }
            .zoomin3 img { height: 41px; width: 235px; -webkit-transition: all 0.5s ease; -moz-transition: all 0.5s ease; -ms-transition: all 0.5s ease; transition: all 0.5s ease; }
            .zoomin3 img:hover { width: 245px; height: 50px; }
        </style>
        <!-- <link type="text/css" rel="stylesheet" href="css/style123.css"/> -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Tajawal&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Cairo', sans-serif; }
        </style>
        <link href="../fonts/GE_SS_Two_Light.otf" rel="stylesheet" type="text/css"/>

        <div class="middlesection1">
            <div class="maincontainer">
                <div class="maincontent1" style="min-height: 2930px;">
                    <div class="maincontent1top"></div>
                    <div class="section0_pager1">
                        <div class="page2-header2-div1">
                            <div class="list_page2">
                                <ul id="nav">
                                    <li><a href="why_egyptmart.php" title="Why EgyptMART?">فوائد الإشتراك فى سوق الشركات</a></li>
                                    <li><a href="membership_plans.php" title="Membership Plans">خطط العضوية على المنصة</a></li>
                                    <li class="active"><a href="advertise-with-us.php" title="Advertise with Us">المساحات الإعلانية على المنصة</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="clear"></div>
                        <div class="page2-header2-divmiddle">
                            <p class="good_resp col-md-12 col-sm-12 col-xs-12" title="Advertise with"><span><img src="images/arlogo.png"> EgyptMART ضع إعلانات شركتك معنا على</span></p>
                            <div class="clear"></div>
                            <div class="page2-header2-divmiddle-left col-md-9 col-sm-12 col-xs-12">
                                <img src="images/fullbanner.png" class="leftimg">
                                <img src="images/maximize.png" class="righting">
                            </div>
                            <div class="page2-header2-divmiddle-right col-md-3 col-sm-12 col-xs-12">
                                <?php
                                $sql_testi3 = "SELECT * FROM testimonials WHERE testi_type='supplier' AND testi_status='1' ORDER BY RAND() DESC LIMIT 1";
                                $res_testi3 = mysqli_query($con, $sql_testi3);
                                if (mysqli_num_rows($res_testi3) > 0) {
                                ?>
                                <div class="testimonialbox22">
                                    <div class="testimonialbg">
                                        <h2>Supplier Speaks &nbsp;&nbsp; <img src="images/cir.png" width="25px"></h2>
                                        <?php while ($row_testi3 = mysqli_fetch_object($res_testi3)) { ?>
                                        <div class="arrow_box">
                                            <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($row_testi3->testi_details), ENT_QUOTES, 'UTF-8'); ?><span class="spacecomma">&rdquo;</span></i></p>
                                        </div>
                                        <div class="clear"></div>
                                        <div class="testiwriter">
                                            <div class="pic1"><img src="upload/testimonial_img/<?php echo htmlspecialchars($row_testi3->testi_image, ENT_QUOTES, 'UTF-8'); ?>" alt=""/></div>
                                            <div class="pic-info">
                                                <h5><?php echo htmlspecialchars($row_testi3->testi_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <p><a href="#"><?php echo htmlspecialchars(get_country_name($row_testi3->testi_cn_id), ENT_QUOTES, 'UTF-8'); ?></a></p>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="page3-header2-left col-md-9 col-sm-12 col-xs-12">
                            <table width="100%" style="border-collapse: separate; border-spacing: 4px;">
                                <tr class="tblhead">
                                    <th><p style="font-weight: bold;font-size: 20px;padding: 1px 6px 1px 30px;" title="Advertisment page :">صفحة الإعلان</p></th>
                                    <th><p>بالبيكسل /<span> المقاس</span></p></th>
                                    <th><p>أماكن الإعلانات</p></th>
                                    <th><p>أسعار غير العضو</p></th>
                                </tr>
                                <!-- جدول الإعلانات يبقى كما هو -->
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Global/Homepage">إعلانات الصفحة الرئيسية للمنصة</td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Top Banner">البانر الرئيسى</td>
                                    <td class="td2">$350/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Middle Banner">بانر وسط</td>
                                    <td class="td2">$300/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Bottom Banner">بوتون يمين</td>
                                    <td class="td2">$250/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">120*600</td>
                                    <td class="td2m" title="Left Sky Scrapper">سكاى سكرابر يسار</td>
                                    <td class="td2">$350/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Countries/Homepages">إعلانات الصفحة الرئيسية لكل بلد</td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Top Banner">البانر الرئيسى العلوى</td>
                                    <td class="td2">$300/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Middle Banner">إعلان بانر يمين</td>
                                    <td class="td2">$250/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Bottom Banner">إعلان بوتون</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">120*600</td>
                                    <td class="td2m" title="Left Sky Scrapper">إعلان سكاى سكرابر يسار</td>
                                    <td class="td2">$300/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Buy Leads Pages">إعلانات صفحة طلبات الشراء</td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Bottom Banner">إعلان بوتون</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">120*600</td>
                                    <td class="td2m" title="Right Sky Scrapper">سكاى سكرابر يمين</td>
                                    <td class="td2">$250/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Sales Offers Pages">إعلانات بصفحة عروض البيع</td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Top Banner">بانر رئيسى أعلى الصفح</td>
                                    <td class="td2">$300/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Tenders Pages">إعلانات صفحة المناقصات</td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Bottom Banner">إعلان بوتون</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">120*600</td>
                                    <td class="td2m" title="Right Sky Scrapper">إعلان سكاى سكرابر - يمين</td>
                                    <td class="td2">$250/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1" title="">إعلانات بصفحات الموردين</td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Bottom Banner">إعلان بوتون</td>
                                    <td class="td2">$150/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">120*600</td>
                                    <td class="td2m" title="Right Sky Scrapper">إعلان سكاى سكرابر يمين</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Search Walls Pages">إعلانات صفحات نتائج البحث</td>
                                    <td class="td2">468*90</td>
                                    <td class="td2m" title="Middle Category Banner">بانر وسط طبقا للتصنيف</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Right Category Sky Scrapper">إعلان سكاى سكرابر يمين</td>
                                    <td class="td2">$250/month</td>
                                </tr>
                                <tr class="tblhead">
                                    <td class="td1"></td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Right Button">إعلان بوتون يمين</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="Products Directory Pages">إعلانات صفحات دليل المنتجات الرئيسى</td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Right Button">إعلان بوتون يمين</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                                <tr class="tblhead"><td class="td3"></td></tr>
                                <tr class="tblhead">
                                    <td class="td1" title="My EgyptMART Page">إعلانات صفحة لوحة مفاتيح المنصة</td>
                                    <td class="td2">125*125</td>
                                    <td class="td2m" title="Right Button">إعلان بوتون يمين</td>
                                    <td class="td2">$200/month</td>
                                </tr>
                            </table>
                        </div>

                        <div class="page3-header2-right col-md-3 col-sm-12 col-xs-12">
                            <div class="imgs">
                                <img src="images/adv1.png" />
                                <img src="images/adv2.png" class="adme" />
                            </div>
                        </div>

                        <div class="clear"></div>

                        <div class="page3-header2-divlast">
                            <p><span>شهر واحد مجانا بحد أقصى إعلان واحد - </span> ( للشركات الرائدة فى كل صناعة ) *</p>
                            <div class="page3-header2-divlast-img">
                                <div class="divlast-img1">
                                    <img src="images/adv3.png">
                                    <img src="images/adv4.png">
                                </div>
                                <div class="divlast-img2"><img src="images/adv5.png"></div>
                            </div>
                            <div class="clear"></div>
                            <div class="sections_page">
                                <hr style="border-top: 1px solid black;" />
                                <div class="section2_page">
                                    <div class="suit_your_requirments">
                                        <div class="section2_page_div11">
                                            <h4>: إختار نوع العضوية التى تتناسب مع أعمالك </h4>
                                            <p>: العضوية جونيور مجانية بينما يوجد بعض الرسوم الرمزية 15$ التى يتم تطبيقها على بعض الدول للأسباب التالية<br />
                                            1. عملية التحقق من الوجود الفعلى للشركة &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2. قيام المنصة بعمل بعض من تحرير بروفايل الشركة
                                            </p>
                                        </div>
                                        <?php
                                        $sql_testi = "SELECT * FROM testimonials WHERE testi_type='buyer' AND testi_status='1' ORDER BY RAND() DESC LIMIT 1";
                                        $res_testi = mysqli_query($con, $sql_testi);
                                        if (mysqli_num_rows($res_testi) > 0) {
                                        ?>
                                        <div class="testimonialbox12">
                                            <div class="testimonialbg">
                                                <h2>Buyer Speaks&nbsp;&nbsp; <img src="images/sqr.png" width="25px"></h2>
                                                <?php while ($row_testi = mysqli_fetch_object($res_testi)) { ?>
                                                <div class="arrow_box">
                                                    <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($row_testi->testi_details), ENT_QUOTES, 'UTF-8'); ?><span class="spacecomma">&rdquo;</span></i></p>
                                                </div>
                                                <div class="clear"></div>
                                                <div class="testiwriter">
                                                    <div class="pic1"><img src="upload/testimonial_img/<?php echo htmlspecialchars($row_testi->testi_image, ENT_QUOTES, 'UTF-8'); ?>" alt=""/></div>
                                                    <div class="pic-info">
                                                        <h5><?php echo htmlspecialchars($row_testi->testi_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                        <p><a href="#"><?php echo htmlspecialchars(get_country_name($row_testi->testi_cn_id), ENT_QUOTES, 'UTF-8'); ?></a></p>
                                                    </div>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php } ?>
                                    </div>
                                    <?php
                                    $senior_id = 10;
                                    $senior_amount = 0.00;
                                    $sponsor_id = 11;
                                    $sponsor_amount = 0.00;
                                    
                                    $sql_mp = "SELECT * FROM membership_plan WHERE mp_status='1'";
                                    $res_mp = mysqli_query($con, $sql_mp);
                                    while ($row = mysqli_fetch_object($res_mp)) {
                                        if ($row->mp_id == $senior_id) $senior_amount = $row->mp_amount;
                                        if ($row->mp_id == $sponsor_id) $sponsor_amount = $row->mp_amount;
                                    }
                                    ?>
                                    <div class="upgrader">
                                        <div class="upgrade1">
                                            <h4><img src="images/1544273079VERIFIED2.gif"> JUNIOR<br /><span style="font-size: 16px;"> Supplier</span></h4>
                                            <p class="upgradepara1">مجانا كما جاء بالتفاصيل</p>
                                            <p class="upgradepara2">المجانية PROMO<span>&nbsp; خطة <br/> <span style="font-size: 13px; color:#0a569b;">للشركات الكبرى الرائدة</span></span></p>
                                            <p class="upgradepara1">حتى سنة تجريبية</p>
                                            <p class="upgradepara2">كتالوج التصدير / الجملة</p>
                                            <p class="upgradepara1">$15,00 مع خدمة إنشاء كتالوج التصدير والتشغيل</p>
                                            <p class="Mbtn1"><a href="<?php echo ($uid > 0) ? '#shift' : 'http://arabyos.com/create_account.php'; ?>">JUNIOR PROMO أحصل على خطة</a></p>
                                        </div>
                                        <div class="upgrade2">
                                            <img src="images/ribbon.png" style="left:27.2em;position: absolute;bottom: 16.3em;" />
                                            <h4><img src="images/cir.png"> SENIOR <br /><span style="font-size: 19px;">Plan Member</span></h4>
                                            <p class="upgradepara3">رخصة إشتراك مدى الحياه <br /><span>عرض لمدة يوم واحد</span></p>
                                            <p class="upgradepara4">$<?php echo $senior_amount; ?> <span>&nbsp; / مدى الحياة <br>نشر وتسويق عدد 9 منتجات <br /> <span>$<?php echo round($senior_amount / 9, 2); ?> / للمنتج</span></span></p>
                                            <p class="upgradepara3">SENIOR طبقا لفوائد الخطة المذكورة</p>
                                            <p class="Mbtn3"><a href="payment-option.php?id=<?php echo rand(10000, 99999) . md5($senior_id); ?>">إدفع الإشتراك</a></p>
                                            <?php if (getUserInfo($uid, 'usr_mp_id') == $senior_id) { ?>
                                                <p class="Mbtn3"><a href="#" onClick="event.preventDefault();alert('Kindly be noted that you are already a SENIOR member');">إشتراك</a></p>
                                            <?php } ?>
                                        </div>
                                        <div class="upgrade1">
                                            <h4><img src="images/sqr.png"> SPONSOR<br /><span style="font-size: 16px;">Plan Member</span></h4>
                                            <p class="upgradepara1">إقرأ فوائد الخطة عاليه <br> ! يستمتع بكل مزايا البوابة</p>
                                            <p class="upgradepara2">$<?php echo $sponsor_amount; ?><span>&nbsp;/year</span></p>
                                            <p class="upgradepara1">$<?php echo round($sponsor_amount / 12, 2); ?><span>&nbsp;/month</span></p>
                                            <?php if (getUserInfo($uid, 'usr_mp_id') == $sponsor_id) { ?>
                                                <p class="Mbtn3"><a href="#" onClick="event.preventDefault();alert('Kindly be noted that you are already a SPONSOR member');">إشتراك</a></p>
                                            <?php } else { ?>
                                                <p class="Mbtn2"><a href="payment-option.php?id=<?php echo rand(10000, 99999) . md5($sponsor_id); ?>">إشتراك</a></p>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="verification_process" style="text-align: right;">
                                        <h4>عملية التحقق من الوجود الفعلى للشركة</h4>
                                        <div class="verification_process_div1">
                                            <p>الخطوة الأولى <br /> &nbsp;&nbsp;&nbsp;<span>التأكد من الوجود الفعلى والقانونى للشركة عن طريق الحكومات المحلية</span></p>
                                        </div>
                                        <div class="verification_process_div2">
                                            <p>الخطوة الثانية<br /> &nbsp;&nbsp;<span>التأكد من قيام الشخص المسئول والمفوض عن الشركة بالقيام بعملية إدخال منتجات / بروفايل الشركة</span></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="clear"></div>
                                <div class="sections_page">
                                    <div class="clear"></div>
                                    <div class="section2_page"></div>
                                    <hr style="border-top: 1px solid black;"/>
                                    <div class="section3_page" title="Tell us your Advertisement Requirements :">
                                        <h4>أكتب لنا عن متطلبات إعلانات المساحات وسوف نرد على إستفساراتك</h4>
                                        <p id="section2_p1" style="text-align:left;" title="For Leader Suppliers: Publish FREE Banners ?! .. kindly contact our team via below form">مساحات إعلانات مجانية للموردين الرواد فى كل مجال &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span><a href="why_egyptmart.php" style="color: blue; text-align:left;" title="Learn about Membership Plans">تعلم المزيد عن خطط الإشتراك بالمنصة</a></span></p>
                                        <div class="midcont" style="float: left;">
                                            <form action="" method="post" name="membership_form">
                                                <div class="bag" id="shift">
                                                    <label>إسم الشركة <i>*</i><b></b></label><br>
                                                    <input type="text" class="mtxtbx" name="cname" id="cname" value="<?php echo htmlspecialchars($cname, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                                    <label>إسم المتواصل <i>*</i><b></b></label><br>
                                                    <input type="text" class="mtxtbx" name="fullname" id="fullname" value="<?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>" required><br/>
                                                    <label>إيميل المستخدم <i>*</i><b></b></label><br>
                                                    <input type="text" class="mtxtbx" name="email" id="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                                    <label>موبايل المستخدم <i>*</i><b></b></label><br>
                                                    <input type="text" class="mtxtbx" name="mobile" id="mobile" value="<?php echo htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8'); ?>" required><br/>
                                                    <label>بلد المستخدم <i>*</i><b></b></label><br>
                                                    <input type="text" class="mtxtbx" name="country" id="country" value="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                                    <label>مدينة المستخدم <i>*</i><b></b></label><br>
                                                    <input type="text" class="mtxtbx" name="city" id="city" value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" required><br/>
                                                    <label>عنوان المستخدم</label><br>
                                                    <input type="text" class="mtxtbx" name="address" id="address" value="<?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>"><br>
                                                    <label>متطلبات المستخدم من إدارة المنصة <i>*</i><b></b></label><br>
                                                    <textarea rows="5" cols="5" name="requirement" id="requirement" class="mtxtArea" required><?php echo htmlspecialchars($requirement, ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <br/><br/>
                                                </div>
                                                <br/>
                                                <input type="submit" name="Mb_Submit" class="Mbtn" value="إرسـل طلبـك الآن">
                                                <div id="msg" style="font-size: 16px; width: 80%; text-align: center;"><?php echo htmlspecialchars($msg, ENT_QUOTES, 'UTF-8'); ?></div>
                                            </form>
                                            <br/>
                                        </div>
                                        <div class="midright" style="float: right; padding-left: 0px;">
                                            <?php
                                            $sql_testi2 = "SELECT * FROM testimonials WHERE testi_type='buyer' AND testi_status='1' ORDER BY RAND() DESC LIMIT 1";
                                            $res_testi2 = mysqli_query($con, $sql_testi2);
                                            if (mysqli_num_rows($res_testi2) > 0) {
                                            ?>
                                            <div class="testimonialbox22">
                                                <div class="testimonialbg">
                                                    <h2>Buyer Speaks&nbsp;&nbsp; <img src="images/sqr.png" width="25px"></h2>
                                                    <?php while ($row_testi2 = mysqli_fetch_object($res_testi2)) { ?>
                                                    <div class="arrow_box">
                                                        <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($row_testi2->testi_details), ENT_QUOTES, 'UTF-8'); ?><span class="spacecomma">&rdquo;</span></i></p>
                                                    </div>
                                                    <div class="clear"></div>
                                                    <div class="testiwriter">
                                                        <div class="pic1"><img src="upload/testimonial_img/<?php echo htmlspecialchars($row_testi2->testi_image, ENT_QUOTES, 'UTF-8'); ?>" alt=""/></div>
                                                        <div class="pic-info">
                                                            <h5><?php echo htmlspecialchars($row_testi2->testi_name, ENT_QUOTES, 'UTF-8'); ?></h5>
                                                            <p><a href="#"><?php echo htmlspecialchars(get_country_name($row_testi2->testi_cn_id), ENT_QUOTES, 'UTF-8'); ?></a></p>
                                                        </div>
                                                    </div>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <?php } ?>
                                            <br/><br/><br/><br/>
                                            <img src="images/shaik.png" class="sekh" style="float: right;"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('includes/footer.php'); ?>
</body>
</html>









