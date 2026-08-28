<?php
/**
 * File: about_us.php
 * Version: PHP 8.3
 * Description: صفحة "من نحن" - تعرض معلومات عن الشركة والمنصة
 */

// بدء المخزن المؤقت
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات الأساسية
include 'common.php';
require_once __DIR__ . '/lib/function.php';

// تعيين الصفحة الحالية في الجلسة (اختياري - معلق)
/* 
$_SESSION['last_page'] = "about_us.php";
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit();
}
*/
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
</head>
<body class="search-show-box">

<!-- عناصر القائمة المنبثقة - تظهر ديناميكياً -->
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
        <br><br>
        <div class="bt">
            <img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName() ?? ''); ?>" height="1" width="1">
        </div>
        <!-- Header End Here::-->
    </div>
    <div class="clr"></div>

    <div id="middle_container">
        <div>
            <img src="images/banner-about_us.jpg" width="100%" alt="About Us Banner" />
            
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
            
            <div class="right-side2" style="width:80%;">
                <h2>About <span>Us</span></h2>
                
                <div style="text-align: justify; line-height: 1.8; font-size: 16px; padding: 15px; background-color: #f9f9f9; border-radius: 8px; border: 1px solid #e0e0e0;">
                    <?php 
                    // عرض محتوى صفحة "من نحن"
                    $about_content = get_page_content(1, 'cms_content');
                    
                    if (!empty($about_content)) {
                        // الحفاظ على تنسيق HTML إذا كان موجوداً
                        echo $about_content;
                    } else {
                        // نص افتراضي إذا كان المحتوى فارغاً
                        echo '<p style="color: #666; text-align: center; padding: 30px;">جاري تحديث محتوى هذه الصفحة قريباً...</p>';
                    }
                    ?>
                </div>
                
                <!-- معلومات إضافية عن الشركة -->
                <div style="margin-top: 30px; padding: 20px; background-color: #f0f7ff; border-radius: 8px; border: 1px solid #d0e0f0;">
                    <h3 style="color: #006bb1; margin-bottom: 15px;">معلومات الاتصال</h3>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 8px; width: 150px; font-weight: bold;">العنوان:</td>
                            <td style="padding: 8px;"><?php echo htmlspecialchars(get_page_settings(16) ?? 'غير محدد'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: bold;">الهاتف:</td>
                            <td style="padding: 8px;"><?php echo htmlspecialchars(get_page_settings(17) ?? 'غير محدد'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: bold;">البريد الإلكتروني:</td>
                            <td style="padding: 8px;"><?php echo htmlspecialchars(get_page_settings(4) ?? 'غير محدد'); ?></td>
                        </tr>
                        <tr>
                            <td style="padding: 8px; font-weight: bold;">ساعات العمل:</td>
                            <td style="padding: 8px;">الأحد - الخميس، 9 صباحاً - 5 مساءً</td>
                        </tr>
                    </table>
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
<?php
// إنهاء المخزن المؤقت
ob_end_flush();
?>
</body>
</html>