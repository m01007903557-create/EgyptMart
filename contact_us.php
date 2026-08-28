<?php
/**
 * File: contact_us.php
 * Version: PHP 8.3
 * Description: صفحة الاتصال بنا - نموذج تواصل مع إرسال بريد إلكتروني للمستخدم والإدارة
 */

// بدء المخزن المؤقت والجلسة
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include 'common.php';

// تعيين الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "contact_us.php";

// التحقق من وجود مستخدم مسجل دخوله
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit();
}

$uid = (int)$_SESSION['uid_indm'];

// التحقق من وجود اتصال قاعدة البيانات
global $con;
if (!isset($con)) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// جلب بيانات المستخدم
$sql = "SELECT * FROM `user` WHERE `usr_id` = {$uid} LIMIT 1";
$qry = mysqli_query($con, $sql);

if (!$qry) {
    die('خطأ في جلب بيانات المستخدم: ' . mysqli_error($con));
}

$user_detail = mysqli_fetch_array($qry, MYSQLI_ASSOC);

// جلب اسم بلد المستخدم
$user_cn_name = '';
if (!empty($user_detail['country'])) {
    $usqlcountry = "SELECT cn_name FROM country WHERE cn_id = " . (int)$user_detail['country'] . " LIMIT 1";
    $urscountry = mysqli_query($con, $usqlcountry);
    
    if ($urscountry && mysqli_num_rows($urscountry) > 0) {
        $urowcountrty = mysqli_fetch_object($urscountry);
        $user_cn_name = $urowcountrty->cn_name ?? '';
    }
}

// معالجة متغيرات الجلسة
$msg = isset($_SESSION['msg']) ? $_SESSION['msg'] : "";
unset($_SESSION['msg']);

$cu_fname = isset($_SESSION['cu_fname']) ? $_SESSION['cu_fname'] : ($user_detail['fname'] ?? '');
unset($_SESSION['cu_fname']);

$cu_lname = isset($_SESSION['cu_lname']) ? $_SESSION['cu_lname'] : ($user_detail['lname'] ?? '');
unset($_SESSION['cu_lname']);

$cu_contactnumber = isset($_SESSION['cu_contactnumber']) ? $_SESSION['cu_contactnumber'] : ($user_detail['mobile1'] ?? '');
unset($_SESSION['cu_contactnumber']);

$cu_state = isset($_SESSION['cu_state']) ? $_SESSION['cu_state'] : ($user_detail['state'] ?? '');
unset($_SESSION['cu_state']);

$cu_country = isset($_SESSION['cu_country']) ? $_SESSION['cu_country'] : $user_cn_name;
unset($_SESSION['cu_country']);

$cu_email = isset($_SESSION['cu_email']) ? $_SESSION['cu_email'] : ($user_detail['email'] ?? '');
unset($_SESSION['cu_email']);

$cu_comments = isset($_SESSION['cu_comments']) ? $_SESSION['cu_comments'] : '';
unset($_SESSION['cu_comments']);

class addContact
{
    public $msg;
    public $cu_fname;
    public $cu_lname;
    public $cu_email;
    public $cu_contactnumber;
    public $cu_country;
    public $cu_state;
    public $cu_comments;
    public $con;
    
    public function __construct($cu_fname, $cu_lname, $cu_email, $cu_contactnumber, $cu_country, $cu_state, $cu_comments)
    {
        global $con;
        $this->con = $con;
        $this->cu_fname = $this->sanitize($cu_fname);
        $this->cu_lname = $this->sanitize($cu_lname);
        $this->cu_email = $this->sanitize($cu_email);
        $this->cu_contactnumber = $this->sanitize($cu_contactnumber);
        $this->cu_country = $this->sanitize($cu_country);
        $this->cu_state = $this->sanitize($cu_state);
        $this->cu_comments = $this->sanitize($cu_comments);
    }
    
    /**
     * تنظيف المدخلات
     */
    private function sanitize($data)
    {
        if ($data === null) return '';
        return trim($data);
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid()
    {
        $valid = true;
        
        if ($this->cu_fname == "") {
            $this->msg = '<font color="#CC0000">Please enter first name.</font>';
            $valid = false;
        } else if (!validate::is_name($this->cu_fname)) {
            $this->msg = '<font color="#CC0000">Please enter correct first name.</font>';
            $valid = false;
        } else if ($this->cu_lname == "") {
            $this->msg = '<font color="#CC0000">Please enter last name.</font>';
            $valid = false;
        } else if (!validate::is_name($this->cu_lname)) {
            $this->msg = '<font color="#CC0000">Please enter correct last name.</font>';
            $valid = false;
        } else if ($this->cu_email == "") {
            $this->msg = '<font color="#CC0000">Please enter your email address</font>';
            $valid = false;
        } else if (!validate::is_email($this->cu_email)) {
            $this->msg = '<font color="#CC0000">Please enter valid email address</font>';
            $valid = false;
        } else if ($this->cu_contactnumber == "") {
            $this->msg = '<font color="#CC0000">Please enter your contact number</font>';
            $valid = false;
        } else if ($this->cu_country == "") {
            $this->msg = '<font color="#CC0000">Please enter your country</font>';
            $valid = false;
        } else if ($this->cu_state == "") {
            $this->msg = '<font color="#CC0000">Please enter your state</font>';
            $valid = false;
        } else if ($this->cu_comments == "") {
            $this->msg = '<font color="#CC0000">Please enter comments</font>';
            $valid = false;
        }
        
        return $valid;
    }
    
    /**
     * حفظ البيانات في الجلسة
     */
    public function set_session()
    {
        $_SESSION['cu_fname'] = $this->cu_fname;
        $_SESSION['cu_lname'] = $this->cu_lname;
        $_SESSION['cu_country'] = $this->cu_country;
        $_SESSION['cu_state'] = $this->cu_state;
        $_SESSION['cu_contactnumber'] = $this->cu_contactnumber;
        $_SESSION['cu_email'] = $this->cu_email;
        $_SESSION['cu_comments'] = $this->cu_comments;
    }
    
    /**
     * إضافة بيانات الاتصال إلى قاعدة البيانات وإرسال الإيميلات
     */
    public function add()
    {
        global $uid;
        
        // إدخال البيانات في قاعدة البيانات
        $sql = "INSERT INTO contact_us 
                SET 
                    cu_fname = '" . mysqli_real_escape_string($this->con, ucwords($this->cu_fname)) . "',
                    cu_lname = '" . mysqli_real_escape_string($this->con, ucwords($this->cu_lname)) . "',
                    cu_contactnumber = '" . mysqli_real_escape_string($this->con, $this->cu_contactnumber) . "',
                    cu_country = '" . mysqli_real_escape_string($this->con, $this->cu_country) . "',
                    cu_state = '" . mysqli_real_escape_string($this->con, $this->cu_state) . "',
                    cu_email = '" . mysqli_real_escape_string($this->con, $this->cu_email) . "',
                    cu_user_id = {$uid},
                    cu_comments = '" . mysqli_real_escape_string($this->con, ucwords($this->cu_comments)) . "',
                    cu_updated_date = NOW()";
        
        $result = mysqli_query($this->con, $sql);
        
        if (!$result) {
            error_log("خطأ في إدخال بيانات الاتصال: " . mysqli_error($this->con));
            $this->msg = '<font color="#CC0000">حدث خطأ في إرسال الطلب. الرجاء المحاولة مرة أخرى.</font>';
            return;
        }
        
        $this->msg = '<font color="#e80d0d">شكرا لإرسالك الطلب أو الإستفسار وسوف نتواصل معك قريبا</font>';

        /********************* إرسال بريد للمستخدم **********************/
        
        $to = $this->cu_email;
        $subject = "شكرا لتواصلك مع منصة إيجيبت مارت أونلاين " . get_page_settings(4);
        $from_name = get_page_settings(4);
        $from_email = get_adminemail();
        
        // تضمين محتوى البريد الإلكتروني للمستخدم
        $message1 = '';
        if (file_exists("email/contact_us.php")) {
            include "email/contact_us.php";
        } else {
            $message1 = "شكراً لتواصلك معنا. سنقوم بالرد عليك قريباً.";
        }
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        $headers .= "Reply-To: $from_email\r\n";

        if (!mail($to, $subject, $message1, $headers)) {
            error_log("فشل في إرسال البريد الإلكتروني للمستخدم: $to");
        }
        
        /********************* إرسال بريد للإدارة **********************/
        
        $to_admin = get_adminemail();
        $subject_admin = "رسالة جديدة من " . $this->cu_fname . " " . $this->cu_lname;
        
        // تضمين محتوى البريد الإلكتروني للإدارة
        $message2 = '';
        if (file_exists("email/contact_us.php")) {
            include "email/contact_us.php";
        } else {
            $message2 = "رسالة جديدة من " . $this->cu_fname . " " . $this->cu_lname . "\n";
            $message2 .= "البريد الإلكتروني: " . $this->cu_email . "\n";
            $message2 .= "رقم الاتصال: " . $this->cu_contactnumber . "\n";
            $message2 .= "البلد: " . $this->cu_country . "\n";
            $message2 .= "المدينة: " . $this->cu_state . "\n";
            $message2 .= "الرسالة: " . $this->cu_comments . "\n";
        }
        
        $headers_admin = "MIME-Version: 1.0\r\n";
        $headers_admin .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers_admin .= "From: $from_name <$from_email>\r\n";

        if (!mail($to_admin, $subject_admin, $message2, $headers_admin)) {
            error_log("فشل في إرسال البريد الإلكتروني للإدارة");
        }
    }
}

// معالجة إرسال النموذج
if (isset($_POST['contactSubmit'])) {
    
    $adn = new addContact(
        $_POST['cu_fname'] ?? '',
        $_POST['cu_lname'] ?? '',
        $_POST['cu_email'] ?? '',
        $_POST['cu_contactnumber'] ?? '',
        $_POST['cu_country'] ?? '',
        $_POST['cu_state'] ?? '',
        $_POST['cu_comments'] ?? ''
    );
    
    if ($adn->valid()) {
        $adn->add();
    } else {
        $adn->set_session();
    }
    
    $_SESSION['msg'] = $adn->msg;
    header("Location: contact_us.php");
    exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
    
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <!--include-->
    <link rel="stylesheet" type="text/css" href="css/header-style.css">
    <!--include end-->
    <!--navigation-->
    <link rel="stylesheet" type="text/css" href="css/ddsmoothmenu1.css">
    
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
    <style type="text/css">
    <!--
    .style2 {font-weight: bold}
    -->
    </style>
    
    <style>
    .inputs { 
        -webkit-border-radius: 3px; 
        -moz-border-radius: 3px; 
        -ms-border-radius: 3px; 
        -o-border-radius: 3px; 
        border-radius: 3px; 
        -webkit-box-shadow: 0 1px 0 #FFF, 0 -2px 5px #F0E1FF inset; 
        -moz-box-shadow: 0 1px 0 #fff, 0 -2px 5px #F0E1FF inset; 
        -ms-box-shadow: 0 1px 0 #fff, 0 -2px 5px #F0E1FF inset; 
        -o-box-shadow: 0 1px 0 #fff, 0 -2px 5px #F0E1FF inset; 
        box-shadow: 0 1px 0 #FFF, 0 -2px 5px #F0E1FF inset; 
        -webkit-transition: all 0.5s ease; 
        -moz-transition: all 0.5s ease; 
        -ms-transition: all 0.5s ease; 
        -o-transition: all 0.5s ease; 
        transition: all 0.5s ease; 
        background: #E6E6E6; 
        border: 1px solid #C488FF; 
        color: #000; 
        font: 13px Helvetica, Arial, sans-serif;
        margin: 0 0 10px; 
        padding: 10px 10px 10px 10px; 
        width:80%; 
    }
    
    .inputs:focus { 
        -webkit-box-shadow: 0 0 2px #F0E1FF inset; 
        -moz-box-shadow: 0 0 2px #F0E1FF inset; 
        -ms-box-shadow: 0 0 2px #F0E1FF inset; 
        -o-box-shadow: 0 0 2px #F0E1FF inset; 
        box-shadow: 0 0 2px #F0E1FF inset; 
        background-color: #FFF; 
        border: 1px solid #C488FF; 
        outline: none; 
    }
    
    .contBtn {
        -moz-box-shadow: inset 0px 0px 0px 0px #ffffff;
        -webkit-box-shadow: inset 0px 0px 0px 0px #ffffff;
        box-shadow: inset 0px 0px 0px 0px #ffffff;
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #999), color-stop(1, #999));
        background: -moz-linear-gradient(top, #999 5%, #999 100%);
        background: -webkit-linear-gradient(top, #999 5%, #999 100%);
        background: -o-linear-gradient(top, #999 5%, #999 100%);
        background: -ms-linear-gradient(top, #999 5%, #999 100%);
        background: linear-gradient(to bottom, #999 5%, #999 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#ededed', endColorstr='#dfdfdf',GradientType=0);
        background-color: #999;
        -moz-border-radius: 4px;
        -webkit-border-radius: 4px;
        border-radius: 4px;
        border: 1px solid #dcdcdc;
        display: inline-block;
        cursor: pointer;
        color: #333;
        font-family: arial;
        font-size: 16px;
        font-weight: bold;
        padding: 8px 14px;
        text-decoration: none;
    }
    
    .contBtn:hover {
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0.05, #DF0000), color-stop(1, #B30000));
        background: -moz-linear-gradient(top, #DF0000 5%, #B30000 100%);
        background: -webkit-linear-gradient(top, #DF0000 5%, #B30000 100%);
        background: -o-linear-gradient(top, #DF0000 5%, #B30000 100%);
        background: -ms-linear-gradient(top, #DF0000 5%, #B30000 100%);
        background: linear-gradient(to bottom, #DF0000 5%, #B30000 100%);
        filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#DF0000', endColorstr='#B30000',GradientType=0);
        background-color: #DF0000;
        color: #FFF;
        font-weight: bold;
    }
    
    .contBtn:active {
        position: relative;
        top: 1px;
    }
    </style>
    
    <script type="text/javascript">
    function validContactForm2()
    {
        var cu_fname = document.getElementById('cu_fname');
        var cu_lname = document.getElementById('cu_lname');
        var cu_email = document.getElementById('cu_email');
        var cu_contactnumber = document.getElementById('cu_contactnumber');
        var cu_comments = document.getElementById('cu_comments');
        var cu_country = document.getElementById('cu_country');
        var cu_state = document.getElementById('cu_state');
        
        var phonePattern = /^([0-9_\ \-\/\(\)\.\+]{10,18})$/;
        var emailPattern = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
        
        var msgContact = "";
        var valid = true;
        
        if (!cu_fname.value || cu_fname.value.trim() == "") {
            msgContact = 'Please enter your first name';
            cu_fname.value = "";
            cu_fname.focus();
            valid = false;
        } else if (!isNaN(cu_fname.value)) {
            msgContact = 'Please enter your valid first name';
            cu_fname.value = "";
            cu_fname.focus();
            valid = false;
        } else if (!cu_lname.value || cu_lname.value.trim() == "") {
            msgContact = 'Please enter your last name';
            cu_lname.value = "";
            cu_lname.focus();
            valid = false;
        } else if (!isNaN(cu_lname.value)) {
            msgContact = 'Please enter your valid last name';
            cu_lname.value = "";
            cu_lname.focus();
            valid = false;
        } else if (!cu_email.value || cu_email.value.trim() == "") {
            msgContact = "Please enter your email address";
            cu_email.value = "";
            cu_email.focus();
            valid = false;
        } else if (!emailPattern.test(cu_email.value)) {
            msgContact = "Please enter valid email address";
            cu_email.value = "";
            cu_email.focus();
            valid = false;
        } else if (!cu_contactnumber.value || cu_contactnumber.value.trim() == "") {
            msgContact = 'Please enter your contact number';
            cu_contactnumber.value = "";
            cu_contactnumber.focus();
            valid = false;
        } else if (!phonePattern.test(cu_contactnumber.value)) {
            msgContact = "Please enter correct contact number";
            cu_contactnumber.value = "";
            cu_contactnumber.focus();
            valid = false;
        } else if (!cu_country.value || cu_country.value.trim() == "") {
            msgContact = "Please enter country";
            cu_country.value = "";
            cu_country.focus();
            valid = false;
        } else if (!cu_state.value || cu_state.value.trim() == "") {
            msgContact = "Please enter state";
            cu_state.value = "";
            cu_state.focus();
            valid = false;
        } else if (!cu_comments.value || cu_comments.value.trim() == "") {
            msgContact = "Please enter comments";
            cu_comments.value = "";
            cu_comments.focus();
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
<body>

<div style="left: 1180px; top: 330px;" class="ddshadow toplevelshadow"></div>
<div style="left: 1042px; top: 330px;" class="ddshadow toplevelshadow"></div>
<div style="left: 904px; top: 330px;" class="ddshadow toplevelshadow"></div>
<div style="left: 749px; top: 330px;" class="ddshadow toplevelshadow"></div>
<div style="left: 611px; top: 330px;" class="ddshadow toplevelshadow"></div>
<div style="left: 473px; top: 330px;" class="ddshadow toplevelshadow">
    <div style="left: 170px; top: 125px;" class="ddshadow"></div>
</div>

<div id="main_container">
    <div class="hm1 bbc">
        <!-- Header start Here::-->
        <?php 
        if (file_exists('includes/header_new.php')) {
            include 'includes/header_new.php';
        }
        ?>
        <br>
        <!-- Header End Here::-->
    </div>
    <div class="clr"></div>

    <div id="middle_container">
        <div id="banner-contact-us">
            <img src="images/banner-contact-us.jpg" width="100%" alt="Contact Us Banner" />
            <!--navigation start-->
            <span class="left-cor"></span>
            <span class="right-cor"></span>
            
            <?php 
            if (file_exists('includes/contact_head_menu.php')) {
                include 'includes/contact_head_menu.php';
            }
            ?>
        </div>
        <!--navigation close-->
        <div class="clr"></div>
        
        <div id="content_area">
            <?php 
            if (file_exists('includes/contact_left_menu.php')) {
                include 'includes/contact_left_menu.php';
            }
            ?>
            
            <?php
            $latitude = '';
            $longitude = '';
            $iframe_width = '479px';
            $iframe_height = '296px';
            $address = get_page_settings(20);
            
            if (!empty($address)) {
                $address_encoded = urlencode($address);
                $geocode = @file_get_contents('http://maps.google.com/maps/api/geocode/json?address=' . $address_encoded . '&sensor=false');
                
                if ($geocode) {
                    $output = json_decode($geocode);
                    if (isset($output->results[0]->geometry->location->lat)) {
                        $latitude = $output->results[0]->geometry->location->lat;
                    }
                    if (isset($output->results[0]->geometry->location->lng)) {
                        $longitude = $output->results[0]->geometry->location->lng;
                    }
                }
            }
            ?>
            
            <div class="right-side">
                <div style="float:right; border:1px solid #999; -webkit-box-shadow: 8px -4px 10px 0px rgba(50, 50, 50, 0.42); -moz-box-shadow: 8px -4px 10px 0px rgba(50, 50, 50, 0.42); box-shadow:8px -4px 10px 0px rgba(50, 50, 50, 0.42);">
                    <iframe width="<?php echo htmlspecialchars($iframe_width); ?>" 
                            height="<?php echo htmlspecialchars($iframe_height); ?>" 
                            frameborder="0" scrolling="no" marginheight="0" marginwidth="0" 
                            src="https://maps.google.co.in/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=<?php echo htmlspecialchars($address_encoded ?? ''); ?>&amp;aq=&amp;sll=<?php echo htmlspecialchars($latitude); ?>,<?php echo htmlspecialchars($longitude); ?>&amp;ie=UTF8&amp;hq=&amp;hnear=<?php echo htmlspecialchars($address_encoded ?? ''); ?>&amp;ll=<?php echo htmlspecialchars($latitude); ?>,<?php echo htmlspecialchars($longitude); ?>&amp;t=m&amp;z=13&amp;output=embed"></iframe>
                </div>
                
                <div style="float:left; border:1px solid #999; -webkit-box-shadow: 8px -4px 10px 0px rgba(50, 50, 50, 0.42); -moz-box-shadow: 8px -4px 10px 0px rgba(50, 50, 50, 0.42); box-shadow:8px -4px 10px 0px rgba(50, 50, 50, 0.42);"></div>
                
                <div id="contact-form" style="width:38%">
                    <form action="" method="post" onsubmit="return validContactForm2();">
                        <div id="msg" style="width:28%; color: <?php echo (strpos($msg, 'شكرا') !== false) ? 'green' : 'red'; ?>;">
                            <?php echo htmlspecialchars($msg); ?>
                        </div>
                        
                        <input class="inputs" placeholder="First Name" name="cu_fname" id="cu_fname" type="text" value="<?php echo htmlspecialchars($cu_fname); ?>"/>
                        <input class="inputs" placeholder="Last Name" name="cu_lname" id="cu_lname" type="text" value="<?php echo htmlspecialchars($cu_lname); ?>"/>
                        <input class="inputs" placeholder="Email Address" name="cu_email" id="cu_email" type="text" value="<?php echo htmlspecialchars($cu_email); ?>"/>
                        <input class="inputs" placeholder="Contact Number" name="cu_contactnumber" id="cu_contactnumber" type="text" value="<?php echo htmlspecialchars($cu_contactnumber); ?>"/>
                        <input class="inputs" placeholder="Country" name="cu_country" id="cu_country" type="text" value="<?php echo htmlspecialchars($cu_country); ?>"/>
                        <input class="inputs" placeholder=".. أكـتب المحافظة أو المدينة هـنـا" name="cu_state" id="cu_state" type="text" value="<?php echo htmlspecialchars($cu_state); ?>"/>
                        <textarea class="inputs" placeholder=".. أكـتب طلبـاتـك هـنـا" id="cu_comments" name="cu_comments"><?php echo htmlspecialchars($cu_comments); ?></textarea>
                        
                        <input type="submit" class="contBtn" value="إرسل الطـلب" id="contactSubmit" name="contactSubmit"/>
                    </form>
                </div>
                
                <div style="margin-top:1%">
                    <strong>Branch Work Office</strong><br>
                    <div class="right" align="center"></div>
                    <?php echo nl2br(htmlspecialchars(get_page_settings(16) ?? '')); ?>
                    <br>
                    Mobile & WhatsApp: <?php echo htmlspecialchars(get_page_settings(17) ?? ''); ?><br>
                    <br>
                    <br>
                    <strong>
                        ... أكتب عاليه طلباتك بالتفصيل وسوف نرد عليك فى وقت قصير<br />
                        +أو تواصل معنا 7 / 24 على واتس رقم : 201030029097
                    </strong>
                </div>
            </div>
        </div>
    </div>
</div>

<!--footer-->
<?php 
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php';
}
?>
</body>
</html>
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>