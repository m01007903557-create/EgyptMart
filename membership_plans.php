<?php
declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

$uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
$from = isset($_GET['from']) ? (int)$_GET['from'] : 0;

// =============================================
// جلب بيانات المستخدم
// =============================================
$user_detail = [];
$user_cn_name = '';
$user_ct_name = '';

if ($uid > 0) {
    $sql = "SELECT u.*, bp.bnsprof_compname, bp.bnsprof_city 
            FROM user u 
            LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid 
            WHERE u.usr_id = ? 
            LIMIT 1";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_detail = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!empty($user_detail['country'])) {
        $country_sql = "SELECT cn_name FROM country WHERE cn_id = ? LIMIT 1";
        $stmt_country = mysqli_prepare($con, $country_sql);
        mysqli_stmt_bind_param($stmt_country, 'i', $user_detail['country']);
        mysqli_stmt_execute($stmt_country);
        $country_result = mysqli_stmt_get_result($stmt_country);
        $country_row = mysqli_fetch_object($country_result);
        $user_cn_name = $country_row->cn_name ?? '';
        mysqli_stmt_close($stmt_country);
    }
    
    if (!empty($user_detail['bnsprof_city'])) {
        $city_sql = "SELECT ct_name FROM city WHERE ct_id = ? LIMIT 1";
        $stmt_city = mysqli_prepare($con, $city_sql);
        mysqli_stmt_bind_param($stmt_city, 'i', $user_detail['bnsprof_city']);
        mysqli_stmt_execute($stmt_city);
        $city_result = mysqli_stmt_get_result($stmt_city);
        $city_row = mysqli_fetch_object($city_result);
        $user_ct_name = $city_row->ct_name ?? '';
        mysqli_stmt_close($stmt_city);
    }
}

// =============================================
// جلب خطط العضوية
// =============================================
$membership_plans = [];
$membership_plans_array = [];
$membership_count = 0;

$sql_mp = "SELECT mp_id, mp_name, mp_amount, mp_status FROM membership_plan WHERE mp_status = '1'";
$result_mp = mysqli_query($con, $sql_mp);
while ($row = mysqli_fetch_object($result_mp)) {
    $membership_plans[] = $row;
}

// جلب خطط العضوية للعرض في النموذج
$sql_smp = "SELECT mp_id, mst_name, mp_amount FROM smembership_plan WHERE mp_status = '1'";
$result_smp = mysqli_query($con, $sql_smp);
while ($row = mysqli_fetch_object($result_smp)) {
    $membership_plans_array[] = $row;
    $membership_count++;
}

// تحديد معرفات الخطط الرئيسية
$senior_id = 10;
$senior_amount = 0.00;
$sponsor_id = 11;
$sponsor_amount = 0.00;

foreach ($membership_plans as $plan) {
    if ($plan->mp_id == $senior_id) {
        $senior_amount = (float)$plan->mp_amount;
    }
    if ($plan->mp_id == $sponsor_id) {
        $sponsor_amount = (float)$plan->mp_amount;
    }
}

// تحديد معرفات خطط smembership
$s_senior_id = 0;
$s_senior_amount = 0.00;
$s_sponsor_id = 0;
$s_sponsor_amount = 0.00;

foreach ($membership_plans_array as $plan) {
    if (stripos($plan->mst_name ?? '', 'senior') !== false) {
        $s_senior_id = $plan->mp_id;
        $s_senior_amount = (float)$plan->mp_amount;
    }
    if (stripos($plan->mst_name ?? '', 'sponsor') !== false) {
        $s_sponsor_id = $plan->mp_id;
        $s_sponsor_amount = (float)$plan->mp_amount;
    }
}

// =============================================
// استرجاع بيانات الجلسة
// =============================================
$msg = $_SESSION['m_msg'] ?? '';
$membership_plan = $_SESSION['m_membership_plan'] ?? '';
$cname = $_SESSION['m_cname'] ?? ($user_detail['bnsprof_compname'] ?? '');
$fullname = $_SESSION['m_fullname'] ?? (($user_detail['fname'] ?? '') . ' ' . ($user_detail['lname'] ?? ''));
$email = $_SESSION['m_email'] ?? ($user_detail['email'] ?? '');
$mobile = $_SESSION['m_mobile'] ?? ($user_detail['mobile1'] ?? '');
$country = $_SESSION['m_country'] ?? $user_cn_name;
$city = $_SESSION['m_city'] ?? $user_ct_name;
$address = $_SESSION['m_address'] ?? '';
$requirement = $_SESSION['m_requirement'] ?? '';

// مسح بيانات الجلسة
unset($_SESSION['m_msg'], $_SESSION['m_membership_plan'], $_SESSION['m_cname'], 
      $_SESSION['m_fullname'], $_SESSION['m_email'], $_SESSION['m_mobile'], 
      $_SESSION['m_country'], $_SESSION['m_city'], $_SESSION['m_address'], 
      $_SESSION['m_requirement']);

// =============================================
// كلاس إضافة متطلبات العضوية
// =============================================
class AddMembershipRequirement
{
    public $cname;
    public $fullname;
    public $email;
    public $mobile;
    public $country;
    public $city;
    public $address;
    public $requirement;
    public $membership_plan;
    public $msg;
    public $plans;
    private $con;

    public function __construct($membership_plan, $cname, $fullname, $email, $mobile, $country, $city, $address, $requirement, $con)
    {
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
        $this->con = $con;
    }

    public function valid(): bool
    {
        if ($this->membership_plan == "" || $this->membership_plan == ",") {
            $this->msg = '<font color="#CC0000" style="margin-top:10px;"><b>Please select at least one membership plan.</b></font>';
            return false;
        } elseif ($this->cname == "") {
            $this->msg = '<font color="#CC0000">Please enter company name.</font>';
            return false;
        } elseif ($this->fullname == "") {
            $this->msg = '<font color="#CC0000">Please enter your name.</font>';
            return false;
        } elseif ($this->email == "") {
            $this->msg = '<font color="#CC0000">Please enter your email address</font>';
            return false;
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->msg = '<font color="#CC0000">Please enter valid email address</font>';
            return false;
        } elseif ($this->mobile == "") {
            $this->msg = '<font color="#CC0000">Please enter your mobile number</font>';
            return false;
        } elseif ($this->country == "") {
            $this->msg = '<font color="#CC0000">Please enter country</font>';
            return false;
        } elseif ($this->city == "") {
            $this->msg = '<font color="#CC0000">Please enter city</font>';
            return false;
        } elseif ($this->requirement == "") {
            $this->msg = '<font color="#CC0000">Please enter the requirement</font>';
            return false;
        }

        return true;
    }

    public function set_session(): void
    {
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

    public function add(): void
    {
        $uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
        
        $sql = "INSERT INTO membership_requirements
                (mp_user_id, mp_id, company_name, name, email, mobile, country, city, address, requirement, status, updated_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())";
        
        $stmt = mysqli_prepare($this->con, $sql);
        mysqli_stmt_bind_param($stmt, 'iissssssss', 
            $uid, $this->membership_plan, $this->cname, $this->fullname, 
            $this->email, $this->mobile, $this->country, $this->city, 
            $this->address, $this->requirement
        );
        
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        $this->msg = '<font color="#087017">تم إرسال متطلباتك لإدارة المنصة وسوف يرد عليك الأدمن خلال وقت قصير</font>';
        
        $plan_ids = explode(",", $this->membership_plan);
        $plan_names = [];
        
        foreach ($plan_ids as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) {
                $plan_sql = "SELECT mst_name FROM smembership_plan WHERE mp_id = ? LIMIT 1";
                $plan_stmt = mysqli_prepare($this->con, $plan_sql);
                mysqli_stmt_bind_param($plan_stmt, 'i', $pid);
                mysqli_stmt_execute($plan_stmt);
                $plan_result = mysqli_stmt_get_result($plan_stmt);
                $plan_row = mysqli_fetch_assoc($plan_result);
                if ($plan_row) {
                    $plan_names[] = $plan_row['mst_name'];
                }
                mysqli_stmt_close($plan_stmt);
            }
        }
        
        $this->plans = implode(',', $plan_names);
        
        // إرسال البريد الإلكتروني للمستخدم
        $to = $this->email;
        $subject = "Membership Plan Requirement on " . get_page_settings(4);
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();
        
        ob_start();
        include __DIR__ . "/email/membership_req.php";
        $message1 = ob_get_clean();
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        $headers .= "Reply-To: $from_email\r\n";
        
        mail($to, $subject, $message1, $headers);
        
        // إرسال البريد الإلكتروني للإدارة
        $admin_to = get_adminemail();
        $admin_subject = "Membership Plan Requirement on " . get_page_settings(4);
        
        ob_start();
        include __DIR__ . "/email/membership_req.php";
        $message2 = ob_get_clean();
        
        mail($admin_to, $admin_subject, $message2, $headers);
    }
}

// =============================================
// معالجة نموذج الإرسال
// =============================================
if (isset($_POST['Mb_Submit'])) {
    $membership_plan_str = '';
    
    if (isset($_POST['membership_plan']) && is_array($_POST['membership_plan'])) {
        $membership_plan_str = implode(",", array_map('intval', $_POST['membership_plan']));
    }
    
    $adn = new AddMembershipRequirement(
        $membership_plan_str,
        trim($_POST['cname'] ?? ''),
        trim($_POST['fullname'] ?? ''),
        trim($_POST['email'] ?? ''),
        trim($_POST['mobile'] ?? ''),
        trim($_POST['country'] ?? ''),
        trim($_POST['city'] ?? ''),
        trim($_POST['address'] ?? ''),
        trim($_POST['requirement'] ?? ''),
        $con
    );
    
    if ($adn->valid()) {
        $adn->add();
    } else {
        $adn->set_session();
    }
    
    $_SESSION['m_msg'] = $adn->msg;
    $msg = $adn->msg;
    
    if (strpos($adn->msg, 'shortly') > 0 && $from == 1) {
        header("Location: thankyou.php?from=2");
        exit;
    } elseif ($from > 0) {
        header("Location: membership_plans.php?from=" . $from);
        exit;
    }
}
?>
<!DOCTYPE HTML>
<html lang="ar" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    
    <link href="css/bootstrap.css" rel='stylesheet' type='text/css' />
    <link href="css/style.css" rel="stylesheet" type="text/css" />
    <link href="css/responsive1.css" rel="stylesheet" type="text/css" />
    <link href="fonts/font-awesome.css" rel="stylesheet" type="text/css" />
    <link href="css/im-style-v1.css" rel="stylesheet" type="text/css" />
    <link href="css/verticle-menu.css" rel="stylesheet" type="text/css" />
    <link type="text/css" rel="stylesheet" href="css/theme.css" />
    <link href="css/type.css" rel="stylesheet" type="text/css" />
    <link href="css/style123.css" type="text/css" rel="stylesheet" />
    
    <script src="js/jquery.min.js" type="text/javascript"></script>
    <script src="js/jquery.accessible-news-slider.js"></script>
    <script src="js/responsiveslides.min.js"></script>
    
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
        
        .zoomin1 img {
            height: 78px;
            width: 219px;
            -webkit-transition: all 0.5s ease;
            -moz-transition: all 0.5s ease;
            -ms-transition: all 0.5s ease;
            transition: all 0.5s ease;
        }
        .zoomin1 img:hover {
            width: 229px;
            height: 88px;
        }
        .zoomin2 img {
            height: 66px;
            width: 200px;
            -webkit-transition: all 0.5s ease;
            -moz-transition: all 0.5s ease;
            -ms-transition: all 0.5s ease;
            transition: all 0.5s ease;
        }
        .zoomin2 img:hover {
            width: 210px;
            height: 77px;
        }
        .zoomin3 img {
            height: 41px;
            width: 235px;
            -webkit-transition: all 0.5s ease;
            -moz-transition: all 0.5s ease;
            -ms-transition: all 0.5s ease;
            transition: all 0.5s ease;
        }
        .zoomin3 img:hover {
            width: 245px;
            height: 50px;
        }
    </style>
    
    <script>
    $(function() {
        $("#slider").responsiveSlides({
            auto: true,
            nav: false,
            speed: 1000,
            namespace: "callbacks",
            pager: true
        });
        
        $('#newsslider').accessNews({});
        $('#newsslider2').accessNews({
            title: "BREAKING NEWS:",
            subtitle: "stories from the internet",
            speed: "slow",
            slideBy: 5,
            slideShowInterval: 100000,
            slideShowDelay: 100000
        });
    });
    
    <?php if ($msg != '' && $from == 0): ?>
    jQuery(document).ready(function() {
        jQuery("html,body").animate({ scrollTop: jQuery(".Mbtn").offset().top }, "fast");
    });
    <?php endif; ?>
    
    function showmymenu() { $("#mn1").show(); }
    function hidemymenu() { $("#mn1").hide(); }
    function showLocMenu() { $("#changeLocation").show(); }
    function hideLocMenu() { $("#changeLocation").hide(); }
    function showbuymenu() { $("#buymnu").show(); }
    function hidebuymenu() { $("#buymnu").hide(); }
    function showsellmenu() { $("#sellmnu").show(); }
    function hidesellmenu() { $("#sellmnu").hide(); }
    function showsrchm() { $("#smnu").show(); }
    function hidesrchm() { $("#smnu").hide(); }
    
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
    
    function validsearch() {
        var keywords = document.getElementById('keywords');
        if (keywords.value == '' || keywords.value == null) {
            alert("Please enter a valid text to search.");
            return false;
        }
    }
    
    function setCountryLocation(id) {
        $.post("setCountryLocation.php", { loc_id: id }, function(data) {
            if (data != 0) {
                location.reload();
            }
        });
    }
    
    function unsetCountryLocation() {
        $.post("unsetCountryLocation.php", function(data) {
            location.reload();
        });
    }
    
    function validMembershipForm() {
        var cname = document.getElementById('cname');
        var fullname = document.getElementById('fullname');
        var email = document.getElementById('email');
        var mobile = document.getElementById('mobile');
        var country = document.getElementById('country');
        var city = document.getElementById('city');
        var requirement = document.getElementById('requirement');
        var mobile_pattern = /^([0-9_\ \-\/\(\)\.\+]{10,18})$/;
        var email_pattern = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        
        var msgContact = "";
        var valid = true;
        
        if (cname.value == "" || cname.value == null) {
            msgContact = 'Please enter your company name';
            cname.focus();
            valid = false;
        } else if (!isNaN(cname.value)) {
            msgContact = 'Please enter your valid company name';
            cname.value = "";
            cname.focus();
            valid = false;
        } else if (fullname.value == "" || fullname.value == null) {
            msgContact = 'Please enter your name';
            fullname.focus();
            valid = false;
        } else if (!isNaN(fullname.value)) {
            msgContact = 'Please enter your valid name';
            fullname.value = "";
            fullname.focus();
            valid = false;
        } else if (email.value == "" || email.value == null) {
            msgContact = "Please enter your email address";
            email.focus();
            valid = false;
        } else if (!email_pattern.test(email.value)) {
            msgContact = "Please enter valid email address";
            email.value = "";
            email.focus();
            valid = false;
        } else if (mobile.value == "" || mobile.value == null) {
            msgContact = 'Please enter your mobile number';
            mobile.focus();
            valid = false;
        } else if (!mobile_pattern.test(mobile.value)) {
            msgContact = "Please enter correct mobile number";
            mobile.value = "";
            mobile.focus();
            valid = false;
        } else if (country.value == "" || country.value == null) {
            msgContact = "Please enter your country";
            country.focus();
            valid = false;
        } else if (city.value == "" || city.value == null) {
            msgContact = "Please enter your city";
            city.focus();
            valid = false;
        } else if (requirement.value == "" || requirement.value == null) {
            msgContact = "Please enter your requirements";
            requirement.focus();
            valid = false;
        }
        
        if (!valid) {
            document.getElementById("msg").style.color = "red";
            document.getElementById('msg').innerHTML = msgContact;
        }
        
        return valid;
    }
    </script>
</head>
<body style='background: #8b89c3;' class="search-show-box membership_plans">
    <div id="fb-root"></div>
    
    <?php include __DIR__ . "/includes/header_new.php"; ?>
    
    <div class="wrapper">
        <div class="middlesection1">
            <div class="maincontainer">
                <div class="maincontent1" style="min-height: 2720px">
                    <div class="maincontent1top"></div>
                    
                    <div class="section0_pager1">
                        <div class="page2-header2-div1">
                            <div class="list_page2">
                                <ul id="nav">
                                    <li><a href="why_egyptmart.php" title="Why EgyptMART?">فوائد النشر فى سوق الشركات</a></li>
                                    <li class="active"><a href="membership_plans.php" title="Membership Plans">خطط العضوية على المنصة</a></li>
                                    <li><a href="advertise-with-us.php" title="Advertise with Us">حجز المساحات الإعلانيـة</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="page2-header2-div2">
                            <div class="page2-header2-heading">
                                <ul>
                                    <li><a href="" class="transp"><span class="transp">1</span> Register Your Business Profile</a></li>
                                    <li><a href="" class="transp"><span>2</span> Select Membership Type</a></li>
                                    <li><a href="" class="transp"><span class="transp">3</span> Create Your Account on ARABYOS</a></li>
                                </ul>
                            </div>
                            
                            <div class="page2-header2-left" style="overflow-x:auto;">
                                <table width="100%" style="border-collapse: collapse; border-spacing: 4px;">
                                    <tr class="tblhead">
                                        <th>
                                            <p style="font-weight: bold; font-size: 20px; background-color: rgb(243, 109, 73); padding: 1px 6px 1px 30px; color: white;" title="Membership Previlages">
                                                مزايا كل عضوية على المنصة
                                            </p>
                                        </th>
                                        <th><p>JUNIOR <img src="images/pyra.png" alt="Junior"></p></th>
                                        <th><p>SENIOR <img src="images/cir.png" alt="Senior"></p></th>
                                        <th><p>SPONSOR <img src="images/sqr.png" alt="Sponsor"></p></th>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Sell / Buy">: البيع والشراء محليا أو دوليا أو مدن</td></tr>
                                    <tr>
                                        <td class="td1" title="Sell / Buy on Domestic Marketplaces">البيع والشراء داخل أسواق بلدك المحلية فقط</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Sell / Buy on Global Marketplaces">البيع والشراء خارج بلدك للتصدير والإستيراد</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Sell / Buy on Cities Marketplaces">البيع والشراء داخل مدينتك والمدن القريبة</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Sell / Buy on Multi-language">البيع والشراء مستخدما لغات متعددة</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Buyers Access Privileges">: بيانات طلبات الشراء الجاهزة</td></tr>
                                    <tr>
                                        <td class="td1" title="Access to buyers requests Contacts">الحصول على بيانات إتصال طلبات الشراء</td>
                                        <td class="td2">بشراء نقاط كريديت</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Quote to Buying Requests">مراسلة طلبات الشراء المنشورة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Exclusive Access to Buying Requests">معـرفة بيانات إتصال طلبات الشراء المنشورة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Access to buyers requests Contacts">الحصول على بيانات إتصال المناقصات / المزايدات</td>
                                        <td class="td2">بشراء نقاط كريديت</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Let Buyers Find Yours Products">: الوصول لمشترين منتجاتك وخدماتك</td></tr>
                                    <tr>
                                        <td class="td1" title="Premium Company Website">موقع الشركات المصغر للشركة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Rank of Suppliers to Find your Products">ترتيب ظهور المنتجات /الخدمات كى تظهر أولا</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Special Products Exposure">عرض المنتجات فى أماكن هامة بالمنصة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Post Ads/Sale Offers">نشر إعلانات وعروض بيع خاصة للشركة</td>
                                        <td class="td2">1/1</td>
                                        <td class="td2">5/1</td>
                                        <td class="td2">بلا حدود</td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Online Products Listings">عدد المنتجات المسموح للشركة بعرضها</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2">9</td>
                                        <td class="td2">بلا حدود</td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Products Showcase">إستخدام حقيبة منتجات الشركة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2">بلا حدود</td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Customize Website">عمل شكل خاص للموقع المصغر للشركة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Link to the Company Website">نشر عنوان موقع الشركة الأصلى</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Gain Trust for More Buyers">: علامة تجارية لكسب ثقة المشتريين</td></tr>
                                    <tr>
                                        <td class="td1" title="Onsite Authentication and Verification">التحقق خاص من الوجود الحقيقى للشركة</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="JUNIOR Verified Sign">علامة الثقة جونيور مع إسم المورد ومنتجاته</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Premium Senior Supplier Sign">علامة الثقة سينيور بجانب إسم المورد ومنتجاته</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Premium Sponsor Supplier Sign">علامة الثقة سبونسور بجانب إسم المورد ومنتجاته</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Onsite Company Photo & Video">صور وفيديوهات عن الشركة ومنتجاتها</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Powerful Business Tools">: أدوات قوية لتسويق الأعمال التجارية</td></tr>
                                    <tr>
                                        <td class="td1" title="Post Buy Requirements for Business">نشر طلبات شراء وتلقى أفضل أسعار</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="My EgyptMART(Backend tool)">صفحات لممتلى للشركات لإدارة بروفايل ومنتجات وعروض الشركة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Private Client Service">خدمات خاصة للشركات الرائدة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Product Posting Service - Premium">خدمة خاصة لتسجيل منتجات الشركات</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Tenders/Auctions">: المناقصات والمزايدات</td></tr>
                                    <tr>
                                        <td class="td1" title="Publish your Tenders / Auctions">نشر المناقصات والمزايدات</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="Getting latest Tenders / Auctions Alerts">أحدث إشعارات للمناقصات والمزايدات</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Special Promotions">: ترويج خاص لكبار الموردين</td></tr>
                                    <tr>
                                        <td class="td1" title="Trade Show Promotions">ترويج المعارض التجارية للمورد</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="FREE e.mails Marketing">تسويق الشركات برسائل ترويجية</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    <tr>
                                        <td class="td1" title="FREE Advertising Banners">إعلانات مساحات للعرض بالمنصة</td>
                                        <td class="td2"><img src="images/cross.png" alt="No"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                    
                                    <tr><td class="td3" title="Special Promotions">: إشعارات البيع والشراء</td></tr>
                                    <tr>
                                        <td class="td1" title="Trade Show Promotions">تلقى أحدث الإشعارات على الميل والموبايل</td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                        <td class="td2"><img src="images/tick.png" alt="Yes"></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div class="page2-header2-right">
                                <?php
                                $testi_sql = "SELECT * FROM testimonials WHERE testi_type = 'supplier' AND testi_status = '1' ORDER BY RAND() DESC LIMIT 1";
                                $testi_result = mysqli_query($con, $testi_sql);
                                if (mysqli_num_rows($testi_result) > 0):
                                    while ($testi = mysqli_fetch_object($testi_result)):
                                ?>
                                <div class="testimonialbox22">
                                    <div class="testimonialbg">
                                        <h2>Supplier Speaks &nbsp;&nbsp; <img src="images/cir.png" width="25px" alt="Supplier"></h2>
                                        <div class="arrow_box">
                                            <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($testi->testi_details ?? ''), ENT_QUOTES, 'UTF-8'); ?><span class="spacecomma">&rdquo;</span></i></p>
                                        </div>
                                        <div class="clear"></div>
                                        <div class="testiwriter">
                                            <div class="pic1">
                                                <img src="upload/testimonial_img/<?php echo htmlspecialchars($testi->testi_image ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($testi->testi_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="pic-info">
                                                <h5><?php echo htmlspecialchars($testi->testi_name ?? '', ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <p><a href="#"><?php echo htmlspecialchars(get_country_name((int)($testi->testi_cn_id ?? 0)), ENT_QUOTES, 'UTF-8'); ?></a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                                
                                <div class="imgs">
                                    <img src="images/meeting.png" alt="Meeting"><br>
                                    <img class="imgs_ing2" src="images/mappoint.png" style="height:128px" alt="Map">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clear"></div>
                    
                    <div class="sections_page">
                        <hr style="border-top: 1px solid black;">
                        
                        <div class="section2_page">
                            <div class="suit_your_requirments">
                                <div class="section2_page_div11" style="text-align: right;">
                                    <h4>: إختار نوع العضوية التى تتناسب مع أعمالك</h4>
                                    <p>: العضوية جونيور مجانية بينما يوجد بعض الرسوم الرمزية $15 التى يتم تطبيقها على بعض الدول للأسباب التالية<br>
                                        1. عملية التحقق من الوجود الفعلى للشركة &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 2. قيام المنصة بعمل بعض من تحرير بروفايل الشركة
                                    </p>
                                </div>
                                
                                <?php
                                $testi2_sql = "SELECT * FROM testimonials WHERE testi_type = 'buyer' AND testi_status = '1' ORDER BY RAND() DESC LIMIT 1";
                                $testi2_result = mysqli_query($con, $testi2_sql);
                                if (mysqli_num_rows($testi2_result) > 0):
                                    while ($testi2 = mysqli_fetch_object($testi2_result)):
                                ?>
                                <div class="testimonialbox12">
                                    <div class="testimonialbg">
                                        <h2>Buyer Speaks &nbsp;&nbsp; <img src="images/sqr.png" width="25px" alt="Buyer"></h2>
                                        <div class="arrow_box">
                                            <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($testi2->testi_details ?? ''), ENT_QUOTES, 'UTF-8'); ?><span class="spacecomma">&rdquo;</span></i></p>
                                        </div>
                                        <div class="clear"></div>
                                        <div class="testiwriter">
                                            <div class="pic1">
                                                <img src="upload/testimonial_img/<?php echo htmlspecialchars($testi2->testi_image ?? '', ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($testi2->testi_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>
                                            <div class="pic-info">
                                                <h5><?php echo htmlspecialchars($testi2->testi_name ?? '', ENT_QUOTES, 'UTF-8'); ?></h5>
                                                <p><a href="#"><?php echo htmlspecialchars(get_country_name((int)($testi2->testi_cn_id ?? 0)), ENT_QUOTES, 'UTF-8'); ?></a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    endwhile;
                                endif; 
                                ?>
                            </div>
                            
                            <div class="upgrader">
                                <div class="upgrade1">
                                    <h4><img src="images/1544273079VERIFIED2.gif" alt="Junior"> JUNIOR<br><span style="font-size: 16px;">Supplier</span></h4>
                                    <p class="upgradepara1">مجانا كما جاء بالتفاصيل</p>
                                    <p class="upgradepara2">المجانية PROMO<span>&nbsp; خطة<br> <span style="font-size: 13px; color:#0a569b;">للشركات الكبرى الرائدة</span></p>
                                    <p class="upgradepara1">حتى سنة تجريبية</p>
                                    <p class="upgradepara2">كتالوج التصدير / الجملة</p>
                                    <p class="upgradepara1">$15.00 مع خدمة إنشاء كتالوج التصدير والتشغيل</p>
                                    
                                    <p class="Mbtn1">
                                        <a href="<?php echo ($uid > 0) ? '#shift' : 'create_account.php'; ?>">JUNIOR PROMO أحصل على خطة</a>
                                    </p>
                                </div>
                                
                                <div class="upgrade2">
                                    <img src="images/ribbon.png" style="left:27.2em; position:absolute; bottom:16.3em;" alt="Ribbon">
                                    <h4><img src="images/cir.png" alt="Senior"> SENIOR <br><span style="font-size: 19px;">Plan Member</span></h4>
                                    <p class="upgradepara3">رخصة إشتراك مدى الحياه<br><span>عرض لمدة يوم واحد</span></p>
                                    <p class="upgradepara4">
                                        $<?php echo number_format($senior_amount, 2); ?> <span>&nbsp; / مدى الحياة<br>نشر وتسويق عدد 9 منتجات<br> <span>$<?php echo $senior_amount > 0 ? number_format($senior_amount / 9, 2) : '0.00'; ?> / للمنتج</span>
                                    </p>
                                    <p class="upgradepara3">SENIOR طبقا لفوائد الخطة المذكورة</p>
                                    <?php if (getUserInfo($uid, 'usr_mp_id') == $s_senior_id): ?>
                                        <p class="Mbtn3"><a href="#" onClick="event.preventDefault();alert('Kindly be noted that you are already a SENIOR member');">إشتراك</a></p>
                                    <?php else: ?>
                                        <p class="Mbtn3"><a href="payment-option.php?id=<?php echo rand(10000, 99999) . md5((string)$s_senior_id); ?>">إدفع الإشتراك</a></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="upgrade1">
                                    <h4><img src="images/sqr.png" alt="Sponsor"> SPONSOR<br><span style="font-size: 16px;">Plan Member</span></h4>
                                    <p class="upgradepara1">إقرأ فوائد الخطة عاليه<br>! يستمتع بكل مزايا البوابة</p>
                                    <p class="upgradepara2">$<?php echo number_format($sponsor_amount, 2); ?><span>&nbsp;/year</span></p>
                                    <p class="upgradepara1">$<?php echo $sponsor_amount > 0 ? number_format($sponsor_amount / 12, 2) : '0.00'; ?><span>&nbsp;/month</span></p>
                                    <?php if (getUserInfo($uid, 'usr_mp_id') == $s_sponsor_id): ?>
                                        <p class="Mbtn2"><a href="#" onClick="event.preventDefault();alert('Kindly be noted that you are already a SPONSOR member');">إشتراك</a></p>
                                    <?php else: ?>
                                        <p class="Mbtn2"><a href="payment-option.php?id=<?php echo rand(10000, 99999) . md5((string)$s_sponsor_id); ?>">إشتراك</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="clear"></div>
                            
                            <div class="verification_process" style="text-align: center;">
                                <h4>عملية التحقق من الوجود الفعلى للشركة</h4>
                                <div class="verification_process_div2">
                                    <p>الخطوة الثانية<br>&nbsp;&nbsp;<span>التأكد من قيام الشخص المسئول والمفوض عن الشركة بالقيام بعملية إدخال منتجات / بروفايل الشركة</span></p>
                                </div>
                                <div class="verification_process_div1">
                                    <p>الخطوة الأولى<br>&nbsp;&nbsp;&nbsp;<span>التأكد من الوجود الفعلى والقانونى للشركة عن طريق الحكومات المحلية</span></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="clear"></div>
                        <hr style="border-top: 1px solid black;">
                        
                        <div class="section3_page" id="shift">
                            <form action="" method="post" name="membership_form" onsubmit="return validMembershipForm();">
                                <h4>لاتتردد فى كتابة وإرسال متطلباتك .. حتى نتمكن من تقديم أحسن أنواع العضوية لك</h4>
                                <p id="section2_p1">
                                    ! تواصل مع إدارة المنصة عبر النموزج التالى وسوف ترد على اسئلتك خلال وقت قصير&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <span><a name="best" href="#" style="color:blue">تعـرف على مزايا كل نوع عضوية >></a></span>
                                </p>
                                
                                <p id="section2_p2">
                                    : &nbsp;&nbsp;&nbsp; إختار خطة العمل
                                    <span>
                                        <?php if (!empty($membership_plans_array)): ?>
                                            <?php 
                                            $selected_plans = !empty($membership_plan) ? explode(",", $membership_plan) : [];
                                            foreach ($membership_plans_array as $plan):
                                                $checked = in_array((string)$plan->mp_id, $selected_plans) ? 'checked' : '';
                                            ?>
                                            <span class="checkbox-membersip">
                                                <input type="checkbox" name="membership_plan[]" value="<?php echo (int)$plan->mp_id; ?>" <?php echo $checked; ?>>
                                                &nbsp;<?php echo htmlspecialchars($plan->mst_name ?? '', ENT_QUOTES, 'UTF-8'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                            </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </span>
                                </p>
                                
                                <div class="midcont" style="float: left;">
                                    <div class="bag">
                                        <label>إسم الشركة (مطلوب)</label><br>
                                        <input type="text" class="mtxtbx" name="cname" id="cname" value="<?php echo htmlspecialchars($cname, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                        
                                        <label>الإسم (مطلوب)</label><br>
                                        <input type="text" class="mtxtbx" name="fullname" id="fullname" value="<?php echo htmlspecialchars($fullname, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                        
                                        <label>العنوان البريدى (مطلوب)</label><br>
                                        <input type="email" class="mtxtbx" name="email" id="email" value="<?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                        
                                        <label>رقم الموبايل (مطلوب)</label><br>
                                        <input type="text" class="mtxtbx" name="mobile" id="mobile" value="<?php echo htmlspecialchars($mobile, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                        
                                        <label>إسم البلد (مطلوب)</label><br>
                                        <input type="text" class="mtxtbx" name="country" id="country" value="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                        
                                        <label>إسم المدينة (مطلوب)</label><br>
                                        <input type="text" class="mtxtbx" name="city" id="city" value="<?php echo htmlspecialchars($city, ENT_QUOTES, 'UTF-8'); ?>" required><br>
                                        
                                        <label>العنوان</label><br>
                                        <input type="text" class="mtxtbx" name="address" id="address" value="<?php echo htmlspecialchars($address, ENT_QUOTES, 'UTF-8'); ?>"><br>
                                        
                                        <label>المتطلبات (مطلوب)</label><br>
                                        <textarea rows="5" cols="5" name="requirement" id="requirement" class="mtxtArea" required><?php echo htmlspecialchars($requirement, ENT_QUOTES, 'UTF-8'); ?></textarea><br><br>
                                    </div>
                                    <br>
                                    <input type="submit" class="Mbtn" name="Mb_Submit" value="إرسل متطلباتك الآن">
                                    <div id="msg" style="width:80%; font-size:16px; text-align:center; margin-top:10px; font-weight:bold;"><?php echo $msg; ?></div>
                                    <br>
                                </div>
                                
                                <div class="sekh" style="float:right; padding-left:20px;">
                                    <br><br>
                                    <img src="images/greenmap.png" style="width:100%;" alt="Map">
                                    <br><br><br><br><br><br><br><br>
                                    <img src="images/shaik.png" style="padding-left:16px; width:100%;" alt="Sheikh">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>