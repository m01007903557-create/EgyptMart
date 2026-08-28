<?php
declare(strict_types=1);
// Start output buffering and session
ob_start();
session_start();

// Include required files
require_once 'common.php';
require_once __DIR__ . '/lib/function.php';

// Set last page for redirect
$_SESSION['last_page'] = "privacy_policy.php";

// Get privacy policy content (assuming page ID 4 for privacy policy)
$privacyContent = get_page_content(4, 'cms_content') ?: 'سياسة الخصوصية قيد التحديث. الرجاء المحاولة مرة أخرى لاحقاً.';
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?> - سياسة الخصوصية">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    
    <title><?php echo htmlspecialchars(getSiteTitle()); ?> - سياسة الخصوصية</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico">
    
    <!-- CSS Files -->
    <link href="css/style.css" rel="stylesheet" type="text/css">
    <link href="css/header-style.css" rel="stylesheet" type="text/css">
    <link href="css/ddsmoothmenu1.css" rel="stylesheet" type="text/css">
    
    <!-- jQuery -->
    <script language="javascript" type="text/javascript" src="js/jquery.js"></script>
    
   
    
    
</head>
<body>
    <div id="main_container">
        
        <!-- Header -->
        <div class="hm1 bbc">
            <?php include 'includes/header_new.php'; ?>
            <br>
            <div class="clr"></div>
        </div>
        
        <div class="clr"></div>
        
        <!-- Middle Container -->
        <div id="middle_container">
            
            <!-- Banner -->
            <div id="banner-contact-us">
                <span class="left-cor"></span>
                <span class="right-cor"></span>
                
                <!-- Navigation Menu -->
                <?php include 'includes/contact_head_menu.php'; ?>
            </div>
            
            <div class="clr"></div>
            
            <!-- Content Area -->
            <div id="content_area">
                
                <!-- Left Menu -->
                <?php include 'includes/contact_left_menu.php'; ?>
                
                <!-- Main Content -->
                <div class="right-side">
                    <h2>سياسة <span>الخصوصية</span></h2>
                    
                    <div class="privacy-content">
                        <?php echo $privacyContent; ?>
                    </div>
                    
                    <!-- Additional structured sections for better readability -->
                    <div class="privacy-sections" style="margin-top: 30px;">
                        <h3>المعلومات التي نجمعها</h3>
                        <p>نحن نجمع المعلومات التي تقدمها لنا مباشرة، مثل المعلومات الشخصية عند إنشاء حساب، إضافة منتج، أو التواصل معنا.</p>
                        
                        <h3>كيف نستخدم معلوماتك</h3>
                        <p>نستخدم معلوماتك لتقديم وتحسين خدماتنا، التواصل معك، ومعالجة معاملاتك.</p>
                        
                        <h3>مشاركة المعلومات</h3>
                        <p>نحن لا نشارك معلوماتك الشخصية مع أطراف ثالثة إلا بموافقتك أو عندما يكون ذلك ضرورياً لتقديم خدماتنا.</p>
                        
                        <h3>حقوقك</h3>
                        <p>لديك الحق في الوصول إلى معلوماتك الشخصية وتصحيحها أو حذفها. يمكنك القيام بذلك من خلال إعدادات حسابك.</p>
                        
                        <h3>التغييرات على سياسة الخصوصية</h3>
                        <p>قد نقوم بتحديث سياسة الخصوصية هذه من وقت لآخر. سنقوم بإشعارك بأي تغييرات جوهرية.</p>
                    </div>
                    
                    <!-- Last Updated Date -->
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: left; font-size: 12px; color: #999;">
                        آخر تحديث: <?php echo date('d F Y'); ?>
                    </div>
                </div>
            </div>
            
        </div>
        
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Smooth Menu Script -->
    <script type="text/javascript" src="js/ddsmoothmenu.js"></script>
    <script type="text/javascript">
        ddsmoothmenu.init({
            mainmenuid: "smoothmenu1",
            orientation: 'h',
            classname: 'ddsmoothmenu',
            contentsource: "markup"
        });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>