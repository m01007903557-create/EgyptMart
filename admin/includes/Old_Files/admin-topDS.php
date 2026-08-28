<?php
/**
 * File: admin/includes/admin-top.php
 * Description: رأس صفحة لوحة التحكم - نسخة مبسطة
 */

// منع الوصول المباشر
if (!defined('IN_ADMIN_PANEL') && !isset($_SESSION['admin_logged_in'])) {
    exit('Direct access not allowed');
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta charset="utf-8" />
    <title><?php echo SITE_NAME; ?> - Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />

    <!-- Bootstrap & Font Awesome -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" />

    <!-- Ace Admin Styles -->
    <link rel="stylesheet" href="assets/css/ace.min.css" />
    <link rel="stylesheet" href="assets/css/ace-skins.min.css" />

    <!-- HTML5 shim and Respond.js for IE8 support -->
    <!--[if lt IE 9]>
    <script src="assets/js/html5shiv.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="no-skin">
    <div class="navbar navbar-default" id="navbar">
        <div class="navbar-container" id="navbar-container">
            <div class="navbar-header pull-left">
                <a href="#" class="navbar-brand">
                    <small>
                        <i class="fa fa-leaf"></i>
                        <?php echo SITE_NAME; ?> Admin
                    </small>
                </a>
            </div>
            <div class="navbar-buttons navbar-header pull-right" role="navigation">
                <ul class="nav ace-nav">
                    <li class="light-blue">
                        <a data-toggle="dropdown" href="#" class="dropdown-toggle">
                            <span class="user-info">
                                <small>Welcome,</small> Admin
                            </span>
                            <i class="icon-caret-down"></i>
                        </a>
                        <ul class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-closer">
                            <li><a href="logout.php"><i class="icon-off"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>