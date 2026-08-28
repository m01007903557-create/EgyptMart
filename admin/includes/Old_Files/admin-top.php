<?php
/**
 * File: admin/includes/admin-top.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: رأس صفحة لوحة التحكم
 */
declare(strict_types=1);

// منع الوصول المباشر
if (!defined('IN_ADMIN_PANEL') && !isset($_SESSION['admin_logged_in'])) {
    exit('Direct access not allowed');
}

// عناوين الصفحات
$pageTitles = [
    'welcome' => 'الرئيسية',
    'product-view' => 'المنتجات',
    'company-list' => 'الشركات',
    'user-list' => 'المستخدمين',
];

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitles[$currentPage] ?? 'لوحة التحكم';
$siteName = defined('SITE_NAME') ? SITE_NAME : 'EgyptMART';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($siteName . ' - ' . $pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- CSS files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/css/ace.min.css" />
    <link rel="stylesheet" href="assets/css/ace-skins.min.css" />

    <!-- Ace settings handler -->
    <script src="assets/js/ace-extra.min.js"></script>
</head>

<body>
    <div class="navbar navbar-default" id="navbar">
        <div class="navbar-container" id="navbar-container">
            <div class="navbar-header pull-left">
                <a href="welcome.php" class="navbar-brand">
                    <small>
                        <i class="fa fa-leaf"></i>
                        <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?>
                    </small>
                </a>
            </div>

            <div class="navbar-header pull-right" role="navigation">
                <ul class="nav ace-nav">
                    <li class="light-blue">
                        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                            <span class="user-info">
                                <small>Welcome,</small> 
                                <?php echo htmlspecialchars($_SESSION['ad_username_indm'] ?? 'Admin', ENT_QUOTES, 'UTF-8'); ?>
                            </span>
                            <i class="icon-caret-down"></i>
                        </a>

                        <ul class="user-menu dropdown-menu dropdown-menu-right dropdown-yellow dropdown-caret dropdown-close">
                            <li><a href="setting-view.php"><i class="icon-cog"></i> Settings</a></li>
                            <li class="divider"></li>
                            <li><a href="logout.php"><i class="icon-off"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <script type="text/javascript">
    // تفعيل القوائم المنسدلة في القائمة الجانبية
    $(document).ready(function() {
        // تفعيل القوائم المنسدلة
        $('.nav-list .dropdown-toggle').on('click', function(e) {
            e.preventDefault();
            var $li = $(this).closest('li');
            
            // إغلاق القوائم الأخرى
            $('.nav-list li.open').not($li).removeClass('open').find('.submenu').slideUp(200);
            
            // فتح/إغلاق القائمة الحالية
            $li.toggleClass('open');
            $li.find('.submenu').slideToggle(200);
        });
        
        // فتح القائمة النشطة تلقائياً
        var currentFile = window.location.pathname.split('/').pop();
        $('.nav-list a').each(function() {
            var href = $(this).attr('href');
            if (href === currentFile) {
                $(this).closest('li').addClass('active');
                $(this).closest('.submenu').parent().addClass('open');
                $(this).closest('.submenu').show();
            }
        });
    });
    </script>
</body>
</html>