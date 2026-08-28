<?php
/**
 * File: admin/includes/admin-top.php
 <?php include "includes/admin-top.php"; ?>

<style>
/* إزالة المسافات الفارغة بشكل كامل */
body {
    margin: 0 !important;
    padding: 0 !important;
}
.main-container {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
.main-content {
    padding-top: 0 !important;
}
.breadcrumbs {
    margin-bottom: 0 !important;
    padding: 5px 10px !important;
}
.page-content {
    padding-top: 0 !important;
}
.page-header {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
</style>
 * Version: 2.0.0 (PHP 8.3)
 * Description: رأس صفحة لوحة التحكم - نسخة LTR
 */
declare(strict_types=1);
// منع الوصول المباشر
if (!defined('IN_ADMIN_PANEL') && !isset($_SESSION['admin_logged_in'])) {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $allowed = false;
    foreach ($backtrace as $trace) {
        if (isset($trace['file']) && strpos($trace['file'], '/admin/') !== false) {
            $allowed = true;
            break;
        }
    }
    if (!$allowed) {
        exit('Direct access not allowed');
    }
}

// عناوين الصفحات (بالعربية للنصوص)
$pageTitles = [
    'welcome' => 'الرئيسية',
    'setting-view' => 'الإعدادات',
    'social-view' => 'التواصل الاجتماعي',
    'country' => 'الدول',
    'states' => 'المناطق',
    'city' => 'المدن',
    'maincat-view' => 'التصنيفات الرئيسية',
    'category-view' => 'التصنيفات',
    'subcat-view' => 'التصنيفات الفرعية',
    'product-view' => 'المنتجات',
    'company-list' => 'الشركات',
    'user-list' => 'المستخدمين',
    'buyreq-view' => 'طلبات الشراء',
    'selloffer-view' => 'عروض البيع',
    'tender-view' => 'المناقصات',
    'auction-view' => 'المزايدات'
];

$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$pageTitle = $pageTitles[$currentPage] ?? 'لوحة التحكم';
$siteName = defined('SITE_NAME') ? SITE_NAME : 'EgyptMART';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="assets/css/dataTables.bootstrap.min.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title><?php echo htmlspecialchars($siteName . ' - ' . $pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3) ?? 'Admin Panel', ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2) ?? 'admin,management', ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <!-- CSS files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/css/ace-fonts.css" />
    <link rel="stylesheet" href="assets/css/ace.min.css" />
    <link rel="stylesheet" href="assets/css/ace-skins.min.css" />
    
    <!-- Plugin CSS -->
    <link rel="stylesheet" href="assets/css/jquery-ui-1.10.3.custom.min.css" />
    <link rel="stylesheet" href="assets/css/chosen.css" />
    <link rel="stylesheet" href="assets/css/datepicker.css" />
    <link rel="stylesheet" href="assets/css/bootstrap-timepicker.css" />
    <link rel="stylesheet" href="assets/css/daterangepicker.css" />
    <link rel="stylesheet" href="assets/css/colorpicker.css" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- Ace settings handler -->
    <script src="assets/js/ace-extra.min.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support -->
    <!--[if lt IE 9]>
    <script src="assets/js/html5shiv.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>

<body>
    <div class="navbar navbar-default" id="navbar">
        <script type="text/javascript">
            try{ace.settings.check('navbar' , 'fixed')}catch(e){}
        </script>

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
                            <li>
                                <a href="setting-view.php">
                                    <i class="icon-cog"></i>
                                    Settings
                                </a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="logout.php">
                                    <i class="icon-off"></i>
                                    Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <style>
   <style>
/* تقليل المسافات الفارغة */
.main-container {
    padding-top: 0 !important;
    margin-top: 0 !important;
}
.main-content {
    padding-top: 5px !important;
}
.breadcrumbs {
    margin-bottom: 5px !important;
    padding: 5px 10px !important;
}
.page-content {
    padding-top: 5px !important;
}
.table-header {
    margin-bottom: 10px !important;
}
.navbar {
    margin-bottom: 0 !important;
}
.sidebar {
    top: 45px !important;
}

/* إضافات جديدة لتقليل المسافات أكثر */
.breadcrumbs + .page-content {
    margin-top: 0 !important;
    padding-top: 0 !important;
}
.page-header {
    margin-top: 0 !important;
    padding-top: 5px !important;
    margin-bottom: 10px !important;
}
.table-responsive {
    margin-top: 0 !important;
}
.dataTables_length, .dataTables_filter {
    margin-bottom: 5px !important;
}
.dataTables_info, .dataTables_paginate {
    margin-top: 5px !important;
    padding-top: 5px !important;
}

/* عرض الجدول من اليسار إلى اليمين */
.table {
    direction: ltr !important;
}
.table th, .table td {
    text-align: left !important;
}
.table th:first-child, .table td:first-child {
    text-align: center !important;
}
</style>
</style>
    