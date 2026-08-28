<?php
/**
 * File: admin/admin-top.php

 * Version: PHP 8.3
 * Description: رأس صفحة لوحة التحكم الإدارية
 * 
 * هذا الملف يحتوي على هيكل رأس صفحة الإدارة
 * ويتضمن روابط CSS و JavaScript اللازمة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// التحقق من صلاحية المستخدم (يمكن تفعيلها حسب الحاجة)
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
//     header("Location: ../sign-in.php");
//     exit();
// }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم الإدارية | EgyptMART</title>
    <!-- تحميل jQuery مبكراً -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>\x3C/script>");
</script>
</body>
</html>

    <!-- JavaScript Libraries -->
    <script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
    <script src="js/menu-collapsed.js" type="text/javascript"></script>
    <script language="javascript" type="text/javascript" src="../js/jquery.truncator.js"></script>
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" type="text/css" href="style/styles.css">
    <link rel="stylesheet" type="text/css" href="style/pager.css">
    <link rel="stylesheet" type="text/css" href="style/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="style/jquery.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/screen.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/jquery-ui_002.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/dragtable.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/print.css" media="print">
    <link rel="stylesheet" type="text/css" href="style/main.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/layout.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/details.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/x2forms.css" media="screen, projection">
    <link rel="stylesheet" type="text/css" href="style/form.css" media="screen, projection">
    
    <!-- Additional Styles for RTL support if needed -->
    <style>
        /* دعم اللغة العربية إذا كان مطلوباً */
        body.rtl {
            direction: rtl;
            text-align: right;
        }
        
        /* تحسينات للشاشات الصغيرة */
        @media (max-width: 768px) {
            #header-inner {
                padding: 10px;
            }
            .main-menu {
                flex-wrap: wrap;
            }
        }
    </style>
    
</head>
<body>
    <div class="main">
        <div id="header" class="defaultBg">
            <div id="header-inner">
                <div id="main-menu-bar">
                    <div class="width-constraint">
                        <ul class="main-menu" id="main-menu">
                            <li id="search-bar-title" class="special">
                                <a href="#">
                                    <img src="images/logo.png" alt="EgyptMART Admin" height="30" width="92">
                                </a>
                            </li>
                            <li><a href="#">Contacts</a></li>
                            <li><a href="#">Accounts</a></li>
                            <li><a href="#">Marketing</a></li>
                        </ul>
                        
                        <ul class="main-menu" id="user-menu">
                            <?php if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])): ?>
                                <li>
                                    <a href="logout.php">تسجيل خروج</a>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a href="../sign-in.php">تسجيل دخول</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
                <div style="clear:both;"></div>
            </div>
        </div>
        <br clear="all"/>
    </div>

<!-- نهاية رأس الصفحة - يتم إغلاق body و html في ملفات المحتوى -->
<?php
// لا نغلق المخزن المؤقت هنا لأن هذا الملف يتم تضمينه في صفحات أخرى
// ob_end_flush(); // لا تستخدم هنا
?>