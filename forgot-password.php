<?php
/**
 * File: forgot-password.php
 * Version: PHP 8.3
 * Description: صفحة استعادة كلمة المرور - تسمح للمستخدم بإدخال بريده الإلكتروني لإرسال رابط إعادة تعيين كلمة المرور
 */

// بدء تشغيل المخزن المؤقت
ob_start();

// تضمين الملفات الأساسية
include "common.php";

// تعيين الصفحة الحالية في الجلسة
$_SESSION['last_page'] = "forgot-password.php";

// التحقق من وجود معرف المستخدم في الجلسة (إذا كان مسجلاً، توجيهه للوحة التحكم)
if (isset($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != '') {
    header("Location: my-dashboard.php");
    exit();
}

// متغير لتخزين رسائل الخطأ/النجاح
$msg = '';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo htmlspecialchars(getSiteTitle() ?? ''); ?></title>
<meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25"></meta>
<meta name="title" content="<?php echo htmlspecialchars(getSiteTitle() ?? ''); ?>">
<meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? ''); ?>">
<meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? ''); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
<link href="css/jf-1.css" type="text/css" rel="stylesheet">
<link href="css/glusr_usr.css" type="text/css" rel="stylesheet">
<link href="css/login.css" type="text/css" rel="stylesheet">
<script language="javascript" type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
/**
 * التحقق من صحة البريد الإلكتروني وإرسال طلب استعادة كلمة المرور
 */
function checkvalid() {
    var email = document.getElementById('email');
    var is_email = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    var message = "";
    var valid = true;
    
    // التحقق من عدم إدخال البريد الإلكتروني
    if (email.value == "" || email.value == null) {
        message = "Please enter your Email Id";
        email.focus();
        valid = false;
    }
    // التحقق من صيغة البريد الإلكتروني
    else if (!email.value.match(is_email)) {
        message = "Please enter valid email";
        email.value = "";
        email.focus();
        valid = false;
    }
    
    // عرض رسالة الخطأ إذا وجدت
    if (!valid) {
        document.getElementById('message').style.display = "block";
        document.getElementById('message').style.color = "red";
        document.getElementById('message').innerHTML = '<div class="ma ebox1 log3 bnr fw lh" id="errr">' + message + '</div>';
    }
    
    // إذا كانت البيانات صحيحة، إرسال طلب AJAX
    if (valid) {
        $.post("ajax-file/forgotPassword.php", {email: email.value}, function(data) {
            var dt = data.split("|");
            document.getElementById('message').style.display = "block";
            
            if (dt[1] == 1) {
                // نجاح - البريد الإلكتروني موجود وتم إرسال الإشعار
                document.getElementById('message').style.color = "green";
                document.getElementById('message').innerHTML = dt[0];
                
                // تفريغ حقل البريد الإلكتروني بعد النجاح
                email.value = "";
            } else {
                // فشل - البريد الإلكتروني غير موجود
                document.getElementById('message').style.color = "red";
                document.getElementById('message').innerHTML = '<div class="ma ebox1 log3 bnr fw lh" id="errr">' + dt[0] + '</div>';
            }
        }).fail(function() {
            // في حالة فشل الاتصال بالخادم
            document.getElementById('message').style.display = "block";
            document.getElementById('message').style.color = "red";
            document.getElementById('message').innerHTML = '<div class="ma ebox1 log3 bnr fw lh" id="errr">حدث خطأ في الاتصال. الرجاء المحاولة مرة أخرى.</div>';
        });
    }
    return valid;
}
</script>
<style>
#btnForgotPsw {
    background-color: #B90000;
    background: -moz-linear-gradient(top, #B90000 0%, #B90000 8%, #DF0000 54%, #DF0000 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#B90000), color-stop(8%,#B90000), color-stop(54%,#DF0000), color-stop(100%,#DF0000));
    background: -webkit-linear-gradient(top, #B90000 0%,#B90000 8%,#710000 54%,#B90000 100%);
    background: -o-linear-gradient(top, #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
    background: -ms-linear-gradient(top, #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
    background: linear-gradient(to bottom, #B90000 0%,#B90000 8%,#DF0000 54%,#B90000 100%);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#B90000', endColorstr='#DF0000',GradientType=0);
    box-shadow: 0pt 1px 5px #666;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 16px;
    font-weight: bold;
    text-align: center;
    color: #FFF;
    border: 1px solid #C10000;
    border-radius: 6px;
    padding: 5px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
}
#btnForgotPsw:hover {
    background: #DF0000;
    box-shadow: 0pt 2px 8px #444;
}
#btnForgotPsw:active {
    background: #B90000;
    box-shadow: inset 0pt 1px 3px #333;
}
</style>
</head>

<body>
<div class="hm1 bbc" id="res-mob1">
    <?php 
    // تضمين رأس الصفحة مع التحقق من وجود الملف
    if (file_exists("includes/header_new.php")) {
        include "includes/header_new.php"; 
    }
    ?>
    <div class="bt">
        <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" width="1" height="1">
    </div>

    <div align="CENTER">
        <center>
            <div class="w56">
                <div>&nbsp;</div>
                <div>&nbsp;</div>
                
                <div class="bc f11" align="left">
                    <a class="bc f11" style="text-decoration:none" href="index.php">
                        MY <?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>
                    </a> &nbsp;»&nbsp; 
                </div>
                
                <h3>... هل نسيت كلمة المرور؟ قم بتعيين كلمة مرور جديدة</h3>
                
                <div class="fp mt5">
                    <div>
                        <form method="POST" action="" name="login">
                            <table style="BORDER: #EAD5FF 1px solid; BACKGROUND-COLOR: #F5ECFF" border="0" cellpadding="7" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td colspan="2">
                                            <div id="message" style="margin-bottom:2px; display: <?php echo empty($msg) ? 'none' : 'block'; ?>;">
                                                <?php echo $msg; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw" align="center" valign="middle" width="28%">
                                            أكتب العنوان البريدى الخاص بك:
                                        </td>
                                        <td width="72%">
                                            <input class="mu11" name="email" id="email" maxlength="60" style="width:72%" placeholder="example@domain.com">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="100%" colspan="2" align="center">
                                            <input value="غير كلمة المرور" type="button" name="Submit" id="btnForgotPsw" onclick="checkvalid();">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>
                    </div>
                    
                    <div>&nbsp;</div>
                    
                    <div class="note mt" align="right">
                        <strong>: ملاحظة</strong> 
                        من فضلك أكتب العنوان البريدى الذى تم تسجيله وقت التسجيل بالخدمة على المنصة.. وسوف 
                        <strong>تتلقى حالاً إشعار</strong> بتفاصيل كلمة المرور الجديدة
                    </div>
                </div>
            </div>
        </center>
    </div>
    
    <div>&nbsp;</div>
    <div>&nbsp;</div>
    <div>&nbsp;</div>
    
    <div align="center">
        <!-- يمكن إضافة محتوى إضافي هنا -->
    </div>
    
    <!-- Footer Begin -->
    <div class="c3">&nbsp;</div>
</div>

<!--footer:start-->
<?php 
if (file_exists('includes/footer.php')) {
    include 'includes/footer.php'; 
}
?>
</body>
</html>
<?php
// إنهاء المخزن المؤقت وإرسال المخرجات
ob_end_flush();
?>