<?php
/**
 * why_egyptmart.php - Membership Information and Requirements Page
 * PHP Version 8.3
 * 
 * @package EgyptMart
 * @author System Admin
 * @copyright 2025 EgyptMart
 */

declare(strict_types=1);

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'common.php';

// Initialize variables with null coalescing operator
$uid = $_SESSION['uid_indm'] ?? 0;
$globalcntid = 241;
$from = (int)($_GET['from'] ?? 0);
$msg = '';
$membership_plan = '';
$cname = $fullname = $email = $mobile = $country = $city = $address = $requirement = '';

// Class definition
class MembershipRequirement {
    private string $cname;
    private string $fullname;
    private string $email;
    private string $mobile;
    private string $country;
    private string $city;
    private string $address;
    private string $requirement;
    private string $membership_plan;
    private string $msg;
    private string $plans;
    private mysqli $db;

    public function __construct(
        string $membership_plan,
        string $cname,
        string $fullname,
        string $email,
        string $mobile,
        string $country,
        string $city,
        string $address,
        string $requirement,
        mysqli $db
    ) {
        $this->membership_plan = $this->sanitizeInput($membership_plan);
        $this->cname = $this->sanitizeInput($cname);
        $this->fullname = $this->sanitizeInput($fullname);
        $this->email = $this->sanitizeInput($email);
        $this->mobile = $this->sanitizeInput($mobile);
        $this->country = $this->sanitizeInput($country);
        $this->city = $this->sanitizeInput($city);
        $this->address = $this->sanitizeInput($address);
        $this->requirement = $this->sanitizeInput($requirement);
        $this->db = $db;
        $this->plans = '';
        $this->msg = '';
    }

    private function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public function validate(): bool {
        if (empty($this->membership_plan) || $this->membership_plan === ",") {
            $this->msg = '<span class="error-msg">من فضلك إختار على الأقل خطة عضوية واحدة لكى تستطيع لإرسال طلبك</span>';
            return false;
        }
        
        if (empty($this->cname)) {
            $this->msg = '<span class="error-msg">من فضلك إدخل إسم الشركة</span>';
            return false;
        }
        
        if (empty($this->fullname)) {
            $this->msg = '<span class="error-msg">من فضلك إدخل إسمك الشخصى كممثل للشركة</span>';
            return false;
        }
        
        if (empty($this->email)) {
            $this->msg = '<span class="error-msg">من فضلك إدخل ايميل الشركة المعتمد</span>';
            return false;
        }
        
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $this->msg = '<span class="error-msg">من فضلك أدخل إيميل صحيح ونشط</span>';
            return false;
        }
        
        if (empty($this->mobile)) {
            $this->msg = '<span class="error-msg">من فضلك أدخل رقم موبايل الشركة</span>';
            return false;
        }
        
        if (empty($this->country)) {
            $this->msg = '<span class="error-msg">من فضلك أدخل إسم بلدك</span>';
            return false;
        }
        
        if (empty($this->city)) {
            $this->msg = '<span class="error-msg">من فضلك أدخل إسم مدينتك</span>';
            return false;
        }
        
        if (empty($this->requirement)) {
            $this->msg = '<span class="error-msg">من فضلك أدخل تفاصيل متطلباتك من إدارة المنصة</span>';
            return false;
        }
        
        return true;
    }

    public function setSessionData(): void {
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

    public function save(): bool {
        global $con;
        $uid = (int)($_SESSION['uid_indm'] ?? 0);
        
        // Use prepared statement for security
        $sql = "INSERT INTO membership_requirements 
                SET mp_user_id = ?, mp_id = ?, company_name = ?, name = ?, 
                    email = ?, mobile = ?, country = ?, city = ?, address = ?, 
                    requirement = ?, status = 1, updated_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            error_log("Prepare failed: " . mysqli_error($this->db));
            $this->msg = '<span class="error-msg">حدث خطأ في النظام. الرجاء المحاولة مرة أخرى.</span>';
            return false;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "isssssssss",
            $uid,
            $this->membership_plan,
            $this->cname,
            $this->fullname,
            $this->email,
            $this->mobile,
            $this->country,
            $this->city,
            $this->address,
            $this->requirement
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("Execute failed: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            $this->msg = '<span class="error-msg">حدث خطأ في حفظ البيانات. الرجاء المحاولة مرة أخرى.</span>';
            return false;
        }
        
        mysqli_stmt_close($stmt);
        
        $this->msg = '<span class="success-msg" title="Your requirements have been sent successfully, sales team will contact you shortly">شكرا تم إرسال متطلباتك بنجاح وسوف نرد عليك فى وقت قصير</span>';
        
        // Get plan names
        $this->plans = $this->getPlanNames();
        
        // Send emails
        $this->sendEmails();
        
        return true;
    }

    private function getPlanNames(): string {
        $planIds = array_map('intval', explode(",", $this->membership_plan));
        $placeholders = implode(',', array_fill(0, count($planIds), '?'));
        
        $sql = "SELECT mst_name FROM smembership_plan WHERE mp_id IN ($placeholders)";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return '';
        }
        
        $types = str_repeat('i', count($planIds));
        mysqli_stmt_bind_param($stmt, $types, ...$planIds);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $plans = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $plans[] = $row['mst_name'];
        }
        
        mysqli_stmt_close($stmt);
        return implode(', ', $plans);
    }

    private function sendEmails(): void {
        $siteName = get_page_settings(4);
        $adminEmail = get_adminemail();
        
        // Email to user
        $this->sendMail(
            $this->email,
            "Membership Plan Requirement on $siteName",
            $siteName,
            $adminEmail,
            'user'
        );
        
        // Email to admin
        $this->sendMail(
            $adminEmail,
            "Membership Plan Requirement on $siteName",
            $siteName,
            $adminEmail,
            'admin'
        );
    }

    private function sendMail(string $to, string $subject, string $fromName, string $fromEmail, string $type): void {
        ob_start();
        include "email/membership_req.php";
        $message = ob_get_clean();
        
        $messageVar = $type === 'user' ? 'message1' : 'message2';
        $$messageVar = $message;
        
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            "From: $fromName <$fromEmail>",
            "Reply-To: $fromEmail"
        ];
        
        mail($to, $subject, $message, implode("\r\n", $headers));
    }

    public function getMessage(): string {
        return $this->msg;
    }
}

// Get user details with prepared statement
$user_detail = [];
$user_cn_name = '';
$user_ct_name = '';

if ($uid > 0) {
    $stmt = mysqli_prepare($con, "SELECT u.*, bp.bnsprof_compname, bp.bnsprof_city 
                                   FROM `user` u 
                                   LEFT JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid 
                                   WHERE `usr_id` = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user_detail = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Get country name
    if (!empty($user_detail['country'])) {
        $stmt = mysqli_prepare($con, "SELECT cn_name FROM country WHERE cn_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_detail['country']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_object($result)) {
            $user_cn_name = $row->cn_name;
        }
        mysqli_stmt_close($stmt);
    }
    
    // Get city name
    if (!empty($user_detail['bnsprof_city'])) {
        $stmt = mysqli_prepare($con, "SELECT ct_name FROM city WHERE ct_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $user_detail['bnsprof_city']);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_object($result)) {
            $user_ct_name = $row->ct_name;
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle location cookie
$cn_id = 0;
$cn_name = "Global";
if (isset($_COOKIE['loc_id'])) {
    $cn_id = (int)$_COOKIE['loc_id'];
    $stmt = mysqli_prepare($con, "SELECT cn_name FROM country WHERE cn_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $cn_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_object($result)) {
        $cn_name = $row->cn_name;
    }
    mysqli_stmt_close($stmt);
}

// Build country query condition
$strconutnry = '';
if ($cn_id > 0) {
    $strconutnry = " AND (adv_country LIKE '%,$cn_id,%' OR adv_country LIKE '%,$cn_id' OR adv_country LIKE '$cn_id,%' OR adv_country='$cn_id')";
} else {
    $strconutnry = " AND (adv_country LIKE '%,$globalcntid,%' OR adv_country LIKE '%,$globalcntid' OR adv_country LIKE '$globalcntid,%' OR adv_country='$globalcntid')";
}

// Get session data
$msg = $_SESSION['m_msg'] ?? '';
unset($_SESSION['m_msg']);

$membership_plan = $_SESSION['m_membership_plan'] ?? '';
unset($_SESSION['m_membership_plan']);

$cname = $_SESSION['m_cname'] ?? ($user_detail['bnsprof_compname'] ?? '');
$fullname = $_SESSION['m_fullname'] ?? (trim(($user_detail['fname'] ?? '') . ' ' . ($user_detail['lname'] ?? '')) ?: '');
$mobile = $_SESSION['m_mobile'] ?? ($user_detail['mobile1'] ?? '');
$email = $_SESSION['m_email'] ?? ($user_detail['email'] ?? '');
$country = $_SESSION['m_country'] ?? $user_cn_name;
$city = $_SESSION['m_city'] ?? $user_ct_name;
$address = $_SESSION['m_address'] ?? '';
$requirement = $_SESSION['m_requirement'] ?? '';

// Clear session data
$sessionKeys = ['m_cname', 'm_fullname', 'm_mobile', 'm_email', 'm_country', 'm_city', 'm_address', 'm_requirement'];
foreach ($sessionKeys as $key) {
    unset($_SESSION[$key]);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['Mb_Submit'])) {
    $membership_plan = '';
    
    if (isset($_POST['membership_plan']) && is_array($_POST['membership_plan'])) {
        $membership_plan = implode(",", array_map('intval', $_POST['membership_plan']));
    }
    
    $requirement = new MembershipRequirement(
        $membership_plan,
        $_POST['cname'] ?? '',
        $_POST['fullname'] ?? '',
        $_POST['email'] ?? '',
        $_POST['mobile'] ?? '',
        $_POST['country'] ?? '',
        $_POST['city'] ?? '',
        $_POST['address'] ?? '',
        $_POST['requirement'] ?? '',
        $con
    );
    
    if ($requirement->validate()) {
        if ($requirement->save()) {
            $_SESSION['m_msg'] = $requirement->getMessage();
            if ($from === 1 && strpos($requirement->getMessage(), 'shortly') !== false) {
                header("Location: thankyou.php?from=2");
                exit;
            } elseif ($from > 0) {
                header("Location: why_EgyptMART.php?from=" . $from);
                exit;
            }
        }
    } else {
        $_SESSION['m_msg'] = $requirement->getMessage();
        $requirement->setSessionData();
        header("Location: why_egyptmart.php" . ($from > 0 ? "?from=" . $from : ""));
        exit;
    }
}

// Get membership plans for display
$senior_id = 10;
$senior_amount = 0.00;
$sponsor_id = 11;
$sponsor_amount = 0.00;

$result = mysqli_query($con, "SELECT mp_id, mp_amount FROM membership_plan WHERE mp_status='1'");
while ($row = mysqli_fetch_object($result)) {
    if ($row->mp_id == $senior_id) {
        $senior_amount = (float)$row->mp_amount;
    }
    if ($row->mp_id == $sponsor_id) {
        $sponsor_amount = (float)$row->mp_amount;
    }
}

// Get testimonials
$testimonial_supplier = null;
$result = mysqli_query($con, "SELECT * FROM testimonials WHERE testi_type='supplier' AND testi_status='1' ORDER BY RAND() LIMIT 1");
if (mysqli_num_rows($result) > 0) {
    $testimonial_supplier = mysqli_fetch_object($result);
}

$testimonial_buyer = null;
$result = mysqli_query($con, "SELECT * FROM testimonials WHERE testi_type='buyer' AND testi_status='1' ORDER BY RAND() LIMIT 1");
if (mysqli_num_rows($result) > 0) {
    $testimonial_buyer = mysqli_fetch_object($result);
}

// Get membership plans for form
$membership_plans_array = [];
$membership_count = 0;
$result = mysqli_query($con, "SELECT * FROM smembership_plan WHERE mp_status='1'");
while ($row = mysqli_fetch_object($result)) {
    if ($membership_count <= 2) {
        if (stripos($row->mp_name ?? '', 'senior lifelong plan') !== false) {
            $row->mst_name = 'SENIOR';
        }
        if (stripos($row->mp_name ?? '', 'sponsor annual subscription') !== false || 
            stripos($row->mp_name ?? '', 'sponser') !== false) {
            $row->mst_name = 'SPONSOR';
        }
        $membership_plans_array[] = $row;
    }
    $membership_count++;
}
?>
<!DOCTYPE HTML>
<html lang="ar" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    <title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
    
    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    
    <!-- Custom Theme files -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/responsive1.css" rel="stylesheet">
    <link href="fonts/font-awesome.css" rel="stylesheet">
    <link href="css/im-style-v1.css" rel="stylesheet">
    <link href="css/verticle-menu.css" rel="stylesheet">
    <link href="css/theme.css" rel="stylesheet">
    <link href="css/type.css" rel="stylesheet">
    <link href="css/style123.css" rel="stylesheet">
    <link href="css/megypt.css" rel="stylesheet">
    
    <!-- jQuery UI -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo&family=Tajawal&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Cairo', 'Tajawal', sans-serif;
            background: #8b89c3;
        }
        
        #country-list {
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

        .page2-header2-col1-row2-col2 .top_search {
            border: 1px solid #3953a4;
        }

        .page2-header2-col1-row2-col2 .top_search:hover {
            drop-shadow: 0 0 10px #ccc;
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
        
        .error-msg {
            color: #CC0000;
            display: block;
            margin: 10px 0;
        }
        
        .success-msg {
            color: #087017;
            display: block;
            margin: 10px 0;
        }
        
        .mtxtbx, .mtxtArea {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .Mbtn, .Mbtn1, .Mbtn2, .Mbtn3 {
            padding: 10px 20px;
            background: #006bb1;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .Mbtn:hover, .Mbtn1:hover, .Mbtn2:hover, .Mbtn3:hover {
            background: #3953a4;
        }
        
        @media (min-width: 900px) and (max-width: 1100px) {
            .page2-header2-div2 p.good_resp {
                margin-left: 0;
                text-align: right;
            }
        }

        @media (max-width: 1000px) {
            .page2-header2-div2 p.good_resp {
                text-align: center;
            }
        }

        @media (min-width: 1200px) and (max-width: 1280px) {
            .page2-header2-col1-row2 .page2-header2-col1-row2-col2 {
                width: 73% !important;
            }

            .footer .footer-searchsec-left {
                width: calc(100% - 277px);
            }

            .footer .footer-searchsec {
                max-width: 846px !important;
            }
        }

        @media (min-width: 1281px) {
            .footer .footer-searchsec-right {
                margin-left: 37px;
            }
        }
    </style>
    
    <script>
        $(function() {
            $("#slider").responsiveSlides({
                auto: true,
                nav: false,
                speed: 500,
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
            const element = $("#a1");
            if (type === 'buy_lead') {
                element.html("Buy Leads");
            } else if (type === 'tender') {
                element.html("Tender");
            } else if (type === 'auction') {
                element.html("Auction");
            } else {
                element.html(type);
            }
            $("#rctyp").val(type);
            $("#smnu").hide();
        }

        function validsearch() {
            const keywords = document.getElementById('keywords');
            if (!keywords.value || keywords.value.trim() === '') {
                alert("Please enter a valid text to search.");
                return false;
            }
            return true;
        }

        function gotFocus() {
            const keywords = $("#keywords").val();
            const placeholders = [
                'Enter product / service to search',
                'Enter Buy Lead to search',
                'Enter Supplier to search'
            ];
            if (placeholders.includes(keywords)) {
                $("#keywords").val('');
            }
        }

        function lostFocus() {
            const type = $("#keyword_type").val();
            const keywords = $("#keywords").val();
            if (type === 'Products' && (!keywords || keywords === 'Enter Buy Lead to search' || keywords === 'Enter Supplier to search')) {
                $("#keywords").val('Search Product');
            } else if (type === 'Buy Leads' && (!keywords || keywords === 'Enter product / service to search' || keywords === 'Enter Supplier to search')) {
                $("#keywords").val('Enter Buy Lead to search');
            } else if (type === 'Suppliers' && (!keywords || keywords === 'Enter product / service to search' || keywords === 'Enter Buy Lead to search')) {
                $("#keywords").val('Enter Supplier to search');
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
            $.post("unsetCountryLocation.php", function() {
                location.reload();
            });
        }
    </script>
</head>
<body>
    <div id="fb-root"></div>
    <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=266965666821363&version=v2.0";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>

    <div class="wrapper">
        <?php include "includes/header_new.php"; ?>
        <div class="middlesection1">
            <div class="maincontainer">
                <div class="maincontent1">
                    <div class="maincontent1top"></div>

                    <div class="section0_pager1">
                        <div class="page2-header2-div1">
                            <div class="list_page2">
                                <ul id="nav">
                                    <li class="active"><a href="why_egyptmart.php" title="Why EgyptMART ?">فوائد النشر فى سوق الشركات</a></li>
                                    <li><a href="membership_plans.php" title="Membership Plans">إشتراكات وخطط العضوية</a></li>
                                    <li><a href="advertise-with-us.php" title="Advertise with Us">حجز المساحات الإعلانية</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="page2-header2-div2">
                            <div class="imagesec" style="width: 73%; margin-left: 1%; float: center;">
                                <img src="images/people.png" style="width: 100%;" alt="People">
                                <p class="good_resp" style="margin-top: -25px" title="Good Reasons to subscribe to EgyptMART.online">
                                    <span><img src="images/arlogo.png" style="width: 42px; height: 32px;" alt="Logo"></span>
                                    <span style="color:blue; font-weight:bold;">أسباب هامة للإشتراك فى المنصة</span>
                                </p>
                            </div>
                            
                            <?php if ($testimonial_supplier): ?>
                            <div class="testimonialbox12">
                                <div class="testimonialbg">
                                    <h2>Supplier Speaks <img src="images/cir.png" width="25px" alt="Circle"></h2>
                                    <div class="arrow_box">
                                        <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($testimonial_supplier->testi_details)); ?><span class="spacecomma">&rdquo;</span></i></p>
                                    </div>
                                    <div class="clear"></div>
                                    <div class="testiwriter">
                                        <div class="pic1">
                                            <img src="upload/testimonial_img/<?php echo htmlspecialchars($testimonial_supplier->testi_image); ?>" alt="<?php echo htmlspecialchars($testimonial_supplier->testi_name); ?>">
                                        </div>
                                        <div class="pic-info">
                                            <h5><?php echo htmlspecialchars($testimonial_supplier->testi_name); ?></h5>
                                            <p><a href="#"><?php echo htmlspecialchars(get_country_name($testimonial_supplier->testi_cn_id)); ?></a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <br><br>
                    <div class="clear"></div>
                    
                    <div class="sections_page">
                        <div class="section1_pager1">
                            <p style="font-size: 22px; text-decoration: underline; margin-bottom: 14px; font-weight: bold" title="Key Strengths">النقاط الرئيسية</p>
                            
                            <div class="section2_div1_pager1">
                                <!-- Left column benefits -->
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/calen.png" alt="Calendar"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Join Top 10,000 Egyptian, Arabian and Global Manufacturers, Wholesalers, Exporters!">
                                            إعرض منتجات وخدمات شركتك مع منتجات أهم عشرة آلاف شركة ومصنع فى حوض دول التجارة الهامة التى تقوم بعرض منتجاتها الآن
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Join first emerging online Egyptian and Arabs marketplaces. Become the first supplier / buyer leader subscriber. Get the maximum privileges. Target more than 40 different Egyptian, Arabian and Global industries and trades and more ...">
                                                إنضم الى أكبر منصة أعمال تجارية تفاعلية للتجار المصريين والعرب كرائد من رواد التجارة داخل وخارج مصر وأحصل على الحد الأقصى من المزايا وإستهدف أكثر من أربعون صناعة مصرية وعربية مختلفة
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/largest.png" alt="Largest"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Source & Supply Products / Services in Egypt, Arab Countries & Worldwide">
                                            أحصل بيانات أى منتج او خدمة تجارية أو شركة تحتاجها تجارتك للاستخدام التجارى بأسعار من المنبع وبأقل الأسعار ودون وسطاء
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Powerful business platform enables suppliers to display their business products and services to their potential buyers in Arabs and global markets and allows genuine buyers to post their buying requests/auctions to select the best quotes / bidders for their offers and more ..">
                                                من خلال منصة قوية تمكن الموردين البائعين من التجار والأفراد من عرض أعمالهم التجارية من منتجات وخدمات تجارية الى المشتريين لها فى كل مكان داخل وخارج مصر وتسمح لمشتريين لهم وجود حقيقى فى الأسواق بأن ينشروا طلبات شرائهم الجاهزة ومزايداتهم ليحصلوا على أفضل عروض بيع لها من الموردين والبائعين الأكفاء لها من المنبع
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/globakl1.png" alt="Global"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Let your Business Targets Particular Cities, Countries & Global Locations">
                                            دع أعمالك التجارية تستهدف مناطق بعينها دون غيرها داخل المدن فقط أو كل البلد للبيع التجارى والجملة أو خارج البلد للإستيراد أو التصدير أو اليهم جميعا
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="It is safe and simple trade solution to target specific countries & cities suppliers and buyers , as an easy platform to target specific domestic or global trade locations :">
                                                المنصة وسيلة سهلة لوصول التاجر الى مناطق محددة من بلاد أو مدن أو مناطق لشراء المنتجات أو عرضها
                                                <br>
                                                <span style="color:blue; font-weight: bold" title="Benefits of Dynamic Mini- WebSite:"> ** مثال للأسواق المستهدفة ** </span>
                                                <ul style="list-style-type:square">
                                                    <li>أسواق دول عربية فقط</li>
                                                    <li>أسواق كل مصر الداخلية فقط</li>
                                                    <li>أسواق مدينتى والمدن القريبة فقط</li>
                                                </ul>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/searchk.png" alt="Search"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Find any Product / Service for your business in just a click away">
                                            إبحث وتواصل وأكتشف فورا ألاف من المنتجات والخدمات التجارية والموردين وطلبات الشراء والمناقصات
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="In just one click, find any ( Products / Business Services / Suppliers / Buy requests / Tenders ) for your business requirements immediately in domestic Egypt or all over the globe markets!">
                                                أحصل فى لحظة أى منتج أو خدمة تجارية تحتاجها تجارتك من أهم مورديها وكذلك إوجد طلبات شراء جاهزة لبيع منتجاتك وخدماتك وإوجد وشارك بالمناقصات أوالمزايدات المنشورة كل ذلك طبقا للمواقع والبلاد التى تحددها للبحث
                                            </span>
                                        </p>
                                        <br>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/pyra.png" alt="Pyramid"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Get a Trust Verified Sign as a JUNIOR Member">
                                            بحصولك على علامة عضو جونيور إكتسب ثقة تجارية فى التجارة الالكترونية على الإنترنت كمورد رائد له وجود حقيقى
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Join Free, become authenticated supplier to increase buyers' confidence, it is mainly FREE for leader suppliers while there may be some token administrative charges in few cases due to:1. On Site Verification Cost 2. Product Edit Services.">
                                                إنضم مجانا وأحصل على علامة عضوية جونيور وهى علامة تعطى تجارثك ثقة كبرى من كل المشاركين بالمنصة والزائرين لها وهى مجانية عدا بعض الرسوم الرمزية 15$ لبعض الشركات بسبب إجراءات التحقق من الوجود الحقيقى للشركة وخدمة إدخال منتجات الشركة وإنشاء البروفايل الخاص بها على المنصة
                                                <br>
                                                <span style="color:blue; font-weight: bold" title="Benefits of Verified:"> ** فوائد التحقق من وجود شركتك **</span>
                                                <ul style="list-style-type:square">
                                                    <li>الحصول على علامة جونيور كمورد متحقق من وجوده على المنصة</li>
                                                    <li>شهادة ثقة لشركتك كمورد أصناف على كل الأونلاين فى كل مكان</li>
                                                    <li>شهادة ثقة من المنصة تؤهلك للإجابة على استفسارات الشراء ودخول المناقصات</li>
                                                    <li>عرض علامة جونيور بجانب منتجاتك يعطيها الثقة والمصداقية</li>
                                                </ul>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/sqr.png" alt="Square"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Become a SPONSOR Member. Get All Our Platform Privileges">
                                            إحصل على عضوية سبونسور وهى أعلى عضوية تحقق بموجبها كل مزايا المنصة المتعددة التالية
                                            <br><br>
                                            <span style="color:blue; font-weight: bold" title="Why you should take this service right now?">
                                                لماذا يجب عليك الحصول على هذه العضوية فورا ؟
                                                <ol type="1">
                                                    <li>لتصبح العضو الراعى الرئيسى لمنصتنا التجارية وراعى أيضا لعشرة آلاف شركه مهمة</li>
                                                    <li>لتحصل على خدمة تسجيل ومتابعة مجانية لتسويق منتجاتك لأهم الصناعات المختلفة</li>
                                                    <li>للحصول مجانا الى كل بيانات طلبات الشراء الجاهزة التى تنتظر عروض أسعارك لشرائهم</li>
                                                    <li>لتحصل على واجهة عرض مميزة لمنتجاتك وخدماتك التجارية كعضو راعى</li>
                                                    <li>لنشر فيديوهات وصور مجانية لأعمالك التجارية تليق بمكانتك كرائد أعمال فى أسواقنا</li>
                                                    <li>لتحصل على ظهور لمنتجاتك مميز وذو أولوية من حيث ترتيب الظهور أولا قبل المنافسين</li>
                                                    <li>لإنشاء رابط مباشر لموقع شركتك على المنصة يحقق دخول زائر المنصة الى موقعك الأصلى</li>
                                                    <li>للحصول على علامة سبونسور أو راعى لمنصتنا وهى الأعلى قيمة ومزايا على الإطلاق</li>
                                                    <li>لتحصل على إمتياز الوصول الى كل بيانات إتصال أعضاء المنصةمن خلال رسائل توجهها شركتك اليهم</li>
                                                    <li>تحصل أيضا على إعلانات بانر مجانا تغطى منتجاتك وأحداثك التجارية الهامة</li>
                                                    <li>الخدمات المجانية لتسجيل منتجاتك</li>
                                                    <li>موقع مصغر بى تو بى مميز يعرض كل وأهم منتجاتك</li>
                                                    <li>نحقق ظهور خاص لمنتجاتك فى محركات البحث</li>
                                                    <li>نشر إعلانات خاصة تروج لمعارضك التجارية التى تشترك بها</li>
                                                    <li>إستهداف خاص لجذب مزيد من المشتريين المحليين والعالميين لمنتجاتك</li>
                                                    <li>تسجيل ونشر عدد لانهائى لمنتجاتك وخدماتك التجارية</li>
                                                </ol>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/resp.png" alt="Responsive"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="A Responsive Website to all Devices Sizes!">
                                            منصة الكترونية تمكنك من عرض أعمالك التجارية على كل الوسائط الالكترونية من تابلت موبايل لابتوب وغيرهم
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="360 degree visibility through all screens wide of mobiles, tablets and laptops to target all your business audiences..">
                                                منصة تمكنك من عرض أعمالك التجارية على كل الوسائط الالكترونية من جميع الأحجام مثال الكمبيوتر واللب توب والتابلت والموبايل لتحقيق تعاظم لرؤية منتجاتك وخدماتك التجارية المختلفة
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img style="height:35px; width:50px;" src="images/free.png" alt="Free"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Free Online Ads. Banners, Skyscrapers etc..">
                                            إنشر مجانا المساحات إلإعلانية المختلفة الخاصة بشركتك مثل البانر والبوتون والسكاى سكرابر لتصل لأكثر أهم عشرة آلاف شركة فى أربعون صناعة
                                            <br><br>
                                            <span>
                                                <ul style="list-style-type:square">
                                                    <li>إعلانات تعظم ادارك المشتريين بالماركة التجارية الخاصة بأعمالك</li>
                                                    <li>إعلانات تربط بينك وبين مختلف الأعمال التجارية التى لها علاقة بعملك</li>
                                                </ul>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/publish.png" alt="Publish"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Publish Tenders / Auctions Free & Set Alerts">
                                            تستطيع نشر المناقصات والمزايدات مجانا وتحصل على عطاءات من أعضاء لهم وجود حقيقى
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Publish your Tenders / Auctions FREE and set categories to get latest Tenders ALERTS notifications to your mail inbox.">
                                                منصة تمكنك من نشر مناقصاتك ومزايداتك مجانا وبحرية وتحصل على عطاءات من مختلف أماكن التجارة حسب تحديدك لها
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="section2_div2_pager1">
                                <!-- Right column benefits -->
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/sal.png" alt="Sales"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Join The Online Business Growth At Latest Years!">
                                            إستفيد من النمو الكبير فى مبيعات الأونلاين فى العالم كله فى الأونة الأخيرة
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="In the belief that the Internet would level the playing field. Join online B2B2C sales growth all over the world. At the latest years, buyers became more responding to the internet promotions & offers and gained online credibility.">
                                                طبقا للاعتقاد السائد بأن كل مبيعات الشركات سوف تتحول تدريجيا الى مبيعات أولاين عن طريق الانترنت فإن تلك المبيعات تضاعفت بشكل ملحوظ فى السنوات الأخيرة حتى أنها انتقلت سريعا من مرحلة التبادل التجارى بين الشركات لتصل الى ان تكون بين الشركات والمستهلك النهائى أيضا على حد السواء
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/dynamic.png" alt="Dynamic"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Promote your business in a huge online presence. Make use for new channels to promote your business in domestic and global markets" title="Get Online Supplier Dynamic Mini-website">
                                            أحصل على موقع مصغر لشركتك ضمن خمسة آلاف موقع لآلاف الموردين
                                            <br><br>
                                            <span style="color: black; font-size: 13px;">
                                                أحصل على موقع مصغر مينى سايت فى سوق الشركات الهامة لعرض بروفايل ومنتجات الشركة وخدماتها على المشتريين من أعضاء المنصة عشرة آلاف عضو بيع تجارى وإحتفظ برابط خاص لموقع إضافى على الإنترنت
                                                <br>
                                                <span style="color:blue; font-weight: bold" title="Benefits of Dynamic Mini- WebSite:">** فوائد موقع بى تو بى المصغر للشركات **</span>
                                                <ul style="list-style-type:square">
                                                    <li>عرض أعمالك التجارية وسط أكثر من عشرة آلاف شريك تجارى فى بلدك والخارج</li>
                                                    <li>تجعل المتعاملين معك والمشتريين يجدون أعمالك التجارية فورا بضغطة زر واحدة</li>
                                                    <li>زووم مركز جدا على كل أعمالك التجارية فى واجهة عرض واحدة</li>
                                                    <li>إستفسارت مجمعة من المشتريين للإستفسار عن منتجاتك من خلال حقيبة منتجات واحدة</li>
                                                </ul>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/support.png" alt="Support"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Get Access to Buyers Requests and Contacts">
                                            يحصل أعضاء خطة برومو وإسبونسور على بيانات مجانية لطلبات شراء جاهزة للتواصل معها
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Get easy access to domestic and global buy requests in Egypt, Arabs and Global markets as a Low cost business solution.">
                                                تحصل على بيانات الاتصال المباشرة للمشتريين الذين قاموا بنشر طلبات شراء جاهزة للمنتجات والخدمات التى تحتاجها أعمالهم وبالتالى يمكنك التواصل معهم بسهولة لتقديم عروضك المنافسة
                                                <br>
                                                <span style="color: blue; font-weight: bold" title="Benefits of Buy Leads:">** فائدة طلبات الشراء الجاهزة **</span>
                                                <ul style="list-style-type:square">
                                                    <li>حفظ طلبات لشراء شركتك المعتادة فى مكان واحدد للحصول الدائم على أفضل عروض لها</li>
                                                    <li>إنشاء وإستخدام قاعدة بيانات للمشتريين لمنتجاتك والمتعاملين من الداخل والخارج</li>
                                                    <li>إنشاء وتطوير قائمة متصلين ومشتريين ومتابعين لأصنافك وتجارتك</li>
                                                </ul>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/product.png" alt="Product"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Business Showcase Catalog / Wholesale Contact">
                                            يحصل أعضاء خطة إسبونسور سينيور وبرومو على كتالوج لمنتجاتهم وسلة طلبات مجمعة تمكن المشتريين من طلب أسعار شامل لعدة منتجات
                                            <br><br>
                                            <span style="color:blacl; font-size: 13px;">
                                                <span style="color: blue; font-weight: bold" title="Benefits of Showcase Catalog:">** فوائد كتالوج حقيبة منتجات الشركة **</span>
                                                <ul style="list-style-type:square">
                                                    <li>تسجيل كل منتجاتك وخدماتك التجارية فى واجهة عرض واحدة</li>
                                                    <li>تسهل البحث والوصول لمنتجاتك لوجودها فى مكان واحد</li>
                                                    <li>عرض مقرب لبروفيل الشركة وإظهار واضح لصور المنتجات</li>
                                                    <li>رابط واضح وسهل لصفحات الموقع المصغر للشركة</li>
                                                </ul>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/get.png" alt="Get"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Get latest Business Offers Notifications">
                                            يحصل أعضاء المنصة على إشعارات تجارية فى صندوق بريدهم طبقا لتحديد إحتياجات تجارتهم بأحدث اصناف التجارة المختارة
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Suppliers & Buyers can get latest updates for latest Products,Services, buy requests and Tenders in their email inbox according to their relevant selected products categories and to their selected location preferences as well.">
                                                سجل بالمنصة أصناف المنتجات والخدمات التى تبيعها الشركة أو تشتريها لتقوم المنصة بإرسال أحدث إشعارات عروض بيع وطلبات شراء الشركات الأخرى التى يهمك نشاطها بشكل مستمر الى بريدك ومحمولك
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/cir.png" alt="Circle"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Upgrade to a SENIOR Premium Supplier">
                                            إحصل على عضوية سينيور وهى أعلى عضوية تحصل بموجبها على كثير من مزايا المنصة المتعددة التالية :
                                            <br><br>
                                            <span style="color:blue; font-weight:bold" title="Why you should upgrade?">
                                                لماذا تأخذ هذه العضوية ؟
                                                <ol type="1">
                                                    <li>لتحصل على تسجيل وعرض ونشر عدد كبير من المنتجات يصل الى مائة منتج</li>
                                                    <li>لتحصل على دخول شامل لبيانات الاتصال بطلبات الشراء والمناقصات</li>
                                                    <li>لتحصل على ظهور متقدم لمنتجاتك يتقدم المنتجات المنافسة</li>
                                                    <li>لتحصل على موقع بى تو بى مصغر لشركتك ورابط للموقع الأصلى لشركتك</li>
                                                    <li>لإنشاء كتالوج خاص لمنتجاتك المتنوعة بكل اللغات فى كا البلاد</li>
                                                    <li>لتنشر فيديوهات وصور منتجات الشركة وتقوم بالتعديل والإضافة والحذف</li>
                                                    <li>لتحصل على علامة عضوية سبونسور كرائد من رواد التجارة والصناعة</li>
                                                    <li>لتحصل على خدمات تسجيل ومتابعة منتجات خاصة من المنصة</li>
                                                    <li>لتحصل على نيوز ليتر مجانى لكل المشاركين بالمنصة من شركاء العمل</li>
                                                    <li>اتحصل على إعلانات مجانية وتغطية للمعارض التى تشارك الشركة فيها</li>
                                                </ol>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/view.png" alt="View"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Connect Business Partners & Get Daily Responses">
                                            كن على إتصال يومى مع شركاء العمل التجارى الخاص بك من موردين وشركات منافسة وشركات شراء .. الخ
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Contact your suppliers or buyers and create daily successful interaction with Egyptian, Arabian & Global business professionals, receive daily responses into your account inbox.">
                                                كن على متواصلا بشركاء العمل من الشركات الأخرى ذات الصلة بأعمالك التجارية المشتريين منهم والبائعين وتلقى استجبات مختلفة منهم بصدد استفساراتك
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/company.png" alt="Company"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Effective Business Videos, PDF, Favorite Products!">
                                            تعرض المنصة كل الامكانيات التى تساعدك على عرض أعمالك التجارية بشكل شامل مثل أفلام الفيديو وملفات بى دى إف والمنتجات المفضلة
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="360 degree visibility through effective PDF / Mobile Video / Company Video / Favorite Products feature.">
                                                ظهور شامل وفعال لصور منتجاتك وخدماتك التجارية من خلال الصور والفيديوهات وال بى دى اف وصفحات المقارنة وصفحات المفضلة ودليل الموردين والموقع المصغر للشركة والخصائص المختلفة بالمنصة
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="secdiv1">
                                    <div class="secdiv1_para1"><img src="images/email.png" alt="Email"></div>
                                    <div class="secdiv1_para2">
                                        <p id="h1" title="Get Benefits of Sending Marketing Newsletters by Category / Country">
                                            إستفيد من إمكانية إرسال نيوز ليتر شاملة عن نشاطك التجارى الى عشرة آلاف عضو تجارى بالمنصة
                                            <br><br>
                                            <span style="color: black; font-size: 13px;" title="Make use of premium newsletters campaigns to target your wide audience clients. Target your audiences effectively by industry , by country and by category ..">
                                                كما يمكنك إرسال رسائل خاصة بتجارتك الى عشرة آلاف شركة على المنصة من عدة بلدان لتحقيق أهداف صناعتك عن طريق الاتصال بنا
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="secdiv1">
                                <div class="secdiv1_para2">
                                    <div class="secdiv1_para2_img">
                                        <img src="images/team.png" style="width: 270px; height: 45px;" alt="Team">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    
                    <div class="clear"></div>
                    
                    <div class="sections_page">
                        <hr style="border-top: 1px solid black;">
                        
                        <div class="section2_page">
                            <div class="suit_your_requirments">
                                <div class="section2_page_div11">
                                    <h4>: إختار نوع العضوية التى تتناسب مع أعمالك</h4>
                                    <p>
                                        : العضوية جونيور مجانية بينما يوجد بعض الرسوم الرمزية 15$ التى يتم تطبيقها على بعض الدول للأسباب التالية
                                        <br>
                                        1. عملية التحقق من الوجود الفعلى للشركة &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                                        2. قيام المنصة بعمل بعض من تحرير بروفايل الشركة
                                    </p>
                                </div>
                                
                                <?php if ($testimonial_buyer): ?>
                                <div class="testimonialbox12">
                                    <div class="testimonialbg">
                                        <h2>Buyer Speaks <img src="images/sqr.png" width="25px" alt="Square"></h2>
                                        <div class="arrow_box">
                                            <p><i><span>&ldquo;</span><?php echo htmlspecialchars(stripslashes($testimonial_buyer->testi_details)); ?><span class="spacecomma">&rdquo;</span></i></p>
                                        </div>
                                        <div class="clear"></div>
                                        <div class="testiwriter">
                                            <div class="pic1">
                                                <img src="upload/testimonial_img/<?php echo htmlspecialchars($testimonial_buyer->testi_image); ?>" alt="<?php echo htmlspecialchars($testimonial_buyer->testi_name); ?>">
                                            </div>
                                            <div class="pic-info">
                                                <h5><?php echo htmlspecialchars($testimonial_buyer->testi_name); ?></h5>
                                                <p><a href="#"><?php echo htmlspecialchars(get_country_name($testimonial_buyer->testi_cn_id)); ?></a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="upgrader">
                                <div class="upgrade1">
                                    <h4><img src="images/1544273079VERIFIED2.gif" alt="Verified"> JUNIOR<br><span style="font-size: 16px;"> Supplier</span></h4>
                                    <p class="upgradepara1">مجانا كما جاء بالتفاصيل</p>
                                    <p class="upgradepara2">المجانية PROMO<span>&nbsp; خطة <br> <span style="font-size: 13px; color:#0a569b;">للشركات الكبرى الرائدة</span></span></p>
                                    <p class="upgradepara1">سنة تجريبية</p>
                                    <p class="upgradepara2">كتالوج التصدير / الجملة</p>
                                    <p class="upgradepara1">$15.00 مع خدمة إنشاء كتالوج التصدير والتشغيل</p>
                                    <p class="Mbtn1 oval-btn">
                                        <a href="<?php echo ($uid > 0) ? '#shift' : 'http://arabyos.com/create_account.php'; ?>" style="color: white; text-decoration: none;">JUNIOR PROMO أحصل على خطة</a>
                                    </p>
                                </div>
                                
                                <div class="upgrade2">
                                    <img src="images/ribbon.png" style="left:27.2em; position: absolute; bottom: 16.3em;" alt="Ribbon">
                                    <h4><img src="images/cir.png" alt="Circle"> SENIOR <br><span style="font-size: 19px;">Supplier</span></h4>
                                    <p class="upgradepara3">رخصة إشتراك مدى الحياه <br><span>عرض لمدة أسبوع</span></p>
                                    <p class="upgradepara4">
                                        $<?php echo number_format($senior_amount, 2); ?> <span>&nbsp; / مدى الحياة <br>نشر وتسويق عدد 9 منتجات <br> <span> $<?php echo $senior_amount > 0 ? number_format($senior_amount / 9, 2) : '0.00'; ?> / للمنتج</span></span>
                                    </p>
                                    <p class="upgradepara3">SENIOR طبقا لفوائد الخطة المذكورة</p>
                                    <?php if (function_exists('getUserInfo') && getUserInfo($uid, 'usr_mp_id') == $senior_id): ?>
                                        <p class="Mbtn3 oval-btn"><a href="#" onclick="event.preventDefault(); alert('Kindly be noted that you are already a SENIOR member');" style="color: white; text-decoration: none;">إشتراك</a></p>
                                    <?php else: ?>
                                        <p class="Mbtn3 oval-btn"><a href="payment-option.php?id=<?php echo rand(10000, 99999) . md5((string)$senior_id); ?>" style="color: white; text-decoration: none;">إشتراك</a></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="upgrade1">
                                    <h4><img src="images/sqr.png" alt="Square"> SPONSOR<br><span style="font-size: 16px;">Supplier</span></h4>
                                    <p class="upgradepara1">إقرأ فوائد الخطة عاليه <br>! يستمتع بكل مزايا البوابة</p>
                                    <p class="upgradepara2">$<?php echo number_format($sponsor_amount, 2); ?><span>&nbsp;/year</span></p>
                                    <p class="upgradepara1">$ <?php echo $sponsor_amount > 0 ? number_format($sponsor_amount / 12, 2) : '0.00'; ?><span>&nbsp;/month</span></p>
                                    <?php if (function_exists('getUserInfo') && getUserInfo($uid, 'usr_mp_id') == $sponsor_id): ?>
                                        <p class="Mbtn3 oval-btn"><a href="#" onclick="event.preventDefault(); alert('Kindly be noted that you are already a SPONSOR member');" style="color: white; text-decoration: none;">إشتراك</a></p>
                                    <?php else: ?>
                                        <p class="Mbtn2 oval-btn"><a href="payment-option.php?id=<?php echo rand(10000, 99999) . md5((string)$sponsor_id); ?>" style="color: white; text-decoration: none;">إشتراك</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="clear"></div>
                            
                            <div class="verification_process" style="text-align: right;">
                                <h4>عملية التحقق من الوجود الفعلى للشركة</h4>
                                <div class="verification_process_div1">
                                    <p>الخطوة الأولى <br> &nbsp;&nbsp;&nbsp;<span>التأكد من الوجود الفعلى والقانونى للشركة عن طريق الحكومات المحلية</span></p>
                                </div>
                                <div class="verification_process_div2">
                                    <p>الخطوة الثانية<br> &nbsp;&nbsp;<span>التأكد من قيام الشخص المسئول والمفوض عن الشركة بالقيام بعملية إدخال منتجات / بروفايل الشركة</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="clear"></div>
                    <hr style="border-top: 1px solid black;">
                    
                    <div class="section3_page">
                        <form action="" method="post" name="membership_form" title="Feel free to tell us your requirements to get the best of our membership offers:">
                            <h4>: حتى نتمكن من تقديم أحسن أنواع العضوية لك .. لاتتردد فى كتابة وإرسال متطلباتك</h4>
                            <p id="section2_p1" title="Contact EgyptMART Admin via below form, your message will be replied shortly">
                                ! تواصل مع إدارة المنصة عبر النموذج التالى وسوف تقوم بالرد على اسئلتك خلال وقت قصير &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <span><a href="membership_plans.php" style="color: blue; font-weight: normal; font-size: 15px" title="Learn about membership plans >>">تعـرف على مزايا كل نوع عضوية >></a></span>
                            </p>
                            
                            <p id="section2_p2" title="Select Membership Plan">
                                إختار خطة العضوية التى تناسبك : &nbsp;&nbsp;&nbsp;
                                <span>
                                    <?php if (!empty($membership_plans_array)): ?>
                                        <?php 
                                        $selected_plans = !empty($membership_plan) ? explode(",", $membership_plan) : [];
                                        foreach ($membership_plans_array as $plan): 
                                            $checked = in_array($plan->mp_id, $selected_plans) ? 'checked="checked"' : '';
                                        ?>
                                            <input type="checkbox" name="membership_plan[]" <?php echo $checked; ?> value="<?php echo (int)$plan->mp_id; ?>">
                                            &nbsp;<?php echo htmlspecialchars($plan->mst_name ?? ''); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </span>
                            </p>
                            
                            <div class="membership_form m" style="width: 77%; float: left; margin-bottom: 30px;">
                                <div class="bag" id="shift">
                                    <label>إسم الشركة <i>*</i></label><br>
                                    <input type="text" class="mtxtbx" name="cname" id="cname" value="<?php echo htmlspecialchars($cname); ?>" required><br>
                                    
                                    <label>إسم المتواصل <i>*</i></label><br>
                                    <input type="text" class="mtxtbx" name="fullname" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" required><br>
                                    
                                    <label>إيميل المستخدم <i>*</i></label><br>
                                    <input type="email" class="mtxtbx" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required><br>
                                    
                                    <label>موبايل المستخدم <i>*</i></label><br>
                                    <input type="tel" class="mtxtbx" name="mobile" id="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required><br>
                                    
                                    <label>بلد المستخدم <i>*</i></label><br>
                                    <input type="text" class="mtxtbx" name="country" id="country" value="<?php echo htmlspecialchars($country); ?>" required><br>
                                    
                                    <label>مدينة المستخدم <i>*</i></label><br>
                                    <input type="text" class="mtxtbx" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>" required><br>
                                    
                                    <label>عنوان المستخدم</label><br>
                                    <input type="text" class="mtxtbx" name="address" id="address" value="<?php echo htmlspecialchars($address); ?>"><br>
                                    
                                    <label>متطلبات المستخدم من إدارة المنصة <i>*</i></label><br>
                                    <textarea rows="5" cols="5" name="requirement" id="requirement" class="mtxtArea" required><?php echo htmlspecialchars($requirement); ?></textarea>
                                    <br><br>
                                </div>
                                <br>
                                <input type="submit" name="Mb_Submit" class="Mbtn oval-btn" value="إرسـل طلبـك الآن">
                                <br>
                            </div>
                            
                            <div id="msg" style="width:65%; font-size:16px;"><?php echo $msg; ?></div>
                            
                            <?php if ($msg != '' && $from == 0): ?>
                            <script>
                                jQuery(document).ready(function() {
                                    jQuery("html, body").animate({ scrollTop: jQuery(".Mbtn").offset().top }, "fast");
                                });
                            </script>
                            <?php endif; ?>
                            
                            <div class="wrmap" style="width: 23%; float: right; padding-left: 49px;">
                                <img src="images/wrmap.png" style="width: 229px; height: 130px; margin-top: 27px;" alt="World Map">
                                <br><br><br><br><br><br><br><br><br><br>
                                <img src="images/shaik.png" style="padding-left: 16px;" alt="Shaik">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include('includes/footer.php'); ?>
</body>
</html>