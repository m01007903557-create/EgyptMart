<?php
/**
 * File: index.php
 * Version: 2.0.0 (PHP 8.3)
 * Description: Admin login page - ترقية من النسخة الإنجليزية
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__) . "/common.php";

// Clear any existing error messages
$error_message = $_SESSION['err_msg'] ?? '';
unset($_SESSION['err_msg']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Admin Login Page - <?php echo htmlspecialchars(getWebSiteName() ?? 'Admin Panel'); ?></title>

    <meta name="description" content="User login page" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- basic styles -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <!-- fonts -->
    <link rel="stylesheet" href="assets/css/ace-fonts.css" />

    <!-- ace styles -->
    <link rel="stylesheet" href="assets/css/ace.min.css" />
    <link rel="stylesheet" href="assets/css/ace-rtl.min.css" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="assets/js/html5shiv.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->
</head>
<body class="login-layout">
    <div class="main-container">
        <div class="main-content">
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="login-container" style="margin-top:50px;">
                        <div class="center">
                            <h1>
                                <span class="white"><?php echo htmlspecialchars(getWebSiteName() ?? 'Admin Panel'); ?></span>
                            </h1>
                            <h4 class="blue">Admin Panel</h4>
                        </div>

                        <div class="space-6"></div>

                        <div class="position-relative">
                            <div id="login-box" class="login-box visible widget-box no-border">
                                <div class="widget-body">
                                    <div class="widget-main">
                                        <?php if (empty($error_message)): ?>
                                            <h4 class="header blue lighter bigger">
                                                <i class="icon-coffee green"></i>
                                                Please Enter Your Information
                                            </h4>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($error_message)): ?>
                                            <h4 class="header red">
                                                <?php echo htmlspecialchars($error_message); ?>
                                            </h4>
                                        <?php endif; ?>
                                        
                                        <div class="space-6"></div>

                                        <form id="myform" name="myform" method="post" action="validate-admin.php">
                                            <fieldset>
                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="text" id="username" name="username" class="form-control" placeholder="Username" required />
                                                        <i class="icon-user"></i>
                                                    </span>
                                                </label>

                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="password" id="password" name="password" class="form-control" placeholder="Password" required />
                                                        <i class="icon-lock"></i>
                                                    </span>
                                                </label>

                                                <div class="space"></div>

                                                <div class="clearfix">
                                                    <button name="login" type="submit" class="width-35 pull-right btn btn-sm btn-primary">
                                                        <i class="icon-key"></i>
                                                        Login
                                                    </button>
                                                </div>

                                                <div class="space-4"></div>
                                            </fieldset>
                                        </form>

                                    </div><!-- /widget-main -->
                                </div><!-- /widget-body -->
                            </div><!-- /login-box -->

                            <!-- Forgot Password Box (Hidden by default) -->
                            <div id="forgot-box" class="forgot-box widget-box no-border" style="display:none;">
                                <div class="widget-body">
                                    <div class="widget-main">
                                        <h4 class="header red lighter bigger">
                                            <i class="icon-key"></i>
                                            Retrieve Password
                                        </h4>

                                        <div class="space-6"></div>
                                        <p>
                                            Enter your email and to receive instructions
                                        </p>

                                        <form id="forgot-form" method="post" action="forgot-password.php">
                                            <fieldset>
                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="email" name="email" class="form-control" placeholder="Email" required />
                                                        <i class="icon-envelope"></i>
                                                    </span>
                                                </label>

                                                <div class="clearfix">
                                                    <button type="submit" class="width-35 pull-right btn btn-sm btn-danger">
                                                        <i class="icon-lightbulb"></i>
                                                        Send Me!
                                                    </button>
                                                </div>
                                            </fieldset>
                                        </form>
                                    </div><!-- /widget-main -->

                                    <div class="toolbar center">
                                        <a href="#" onclick="showBox('login-box'); return false;" class="back-to-login-link">
                                            Back to login
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div><!-- /widget-body -->
                            </div><!-- /forgot-box -->
                        </div><!-- /position-relative -->
                    </div>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div>
    </div><!-- /.main-container -->

    <!-- JavaScript -->
    <script type="text/javascript">
        window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
    </script>

    <!--[if IE]>
    <script type="text/javascript">
        window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
    </script>
    <![endif]-->

    <script type="text/javascript">
        if ("ontouchend" in document) {
            document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
        }
    </script>

    <script type="text/javascript">
        // Function to switch between login and forgot password boxes
        function showBox(boxId) {
            // Hide all boxes
            document.querySelectorAll('.widget-box').forEach(function(box) {
                box.style.display = 'none';
            });
            
            // Show the requested box
            var targetBox = document.getElementById(boxId);
            if (targetBox) {
                targetBox.style.display = 'block';
            }
        }

        // Auto-hide error message after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            var errorHeader = document.querySelector('.header.red');
            if (errorHeader) {
                setTimeout(function() {
                    errorHeader.style.transition = 'opacity 0.5s';
                    errorHeader.style.opacity = '0';
                    setTimeout(function() {
                        errorHeader.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });

        // Form validation
        document.getElementById('myform')?.addEventListener('submit', function(e) {
            var username = document.getElementById('username').value.trim();
            var password = document.getElementById('password').value.trim();
            
            if (username === '' || password === '') {
                e.preventDefault();
                alert('Please enter both username and password');
            }
        });
    </script>
</body>
</html>