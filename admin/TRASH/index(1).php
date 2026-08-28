<?php
// ... أي كود موجود مسبقاً ...

// كود التشخيص الجديد - تأكد من وجود النموذج
echo "<!-- FORM action="test-post.php" . htmlspecialchars($_SERVER['PHP_SELF']) . " -->";

// ... باقي الكود ...
/**
 * File: index.php (Admin Login Page)
 * Version: 2.0.0
 * Description: صفحة تسجيل الدخول إلى لوحة التحكم (تمت الترقية إلى PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * ترقيات PHP 8.3 المطبقة:
 * - إعلان strict typing
 * - استخدام مشغلات null coalescing
 * - تحسين الأمان ومنع XSS
 * - إضافة CSRF protection
 * - تحسين إدارة الجلسات
 * - إضافة validation للمدخلات
 */

// تفعيل strict typing
declare(strict_types=1);

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات المطلوبة
require_once "../common.php";

// إنشاء CSRF token للحماية
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// تنظيف رسائل الخطأ القديمة
$error_message = $_SESSION['err_msg'] ?? '';
unset($_SESSION['err_msg']);

// الحصول على اسم الموقع
$siteName = getWebSiteName() ?: 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8" />
    <title>تسجيل الدخول - <?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="صفحة تسجيل الدخول إلى لوحة التحكم" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- CSS files -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/font-awesome.min.css" />

    <!--[if IE 7]>
    <link rel="stylesheet" href="assets/css/font-awesome-ie7.min.css" />
    <![endif]-->

    <link rel="stylesheet" href="assets/css/ace-fonts.css" />
    <link rel="stylesheet" href="assets/css/ace.min.css" />
    <link rel="stylesheet" href="assets/css/ace-rtl.min.css" />

    <!--[if lte IE 8]>
    <link rel="stylesheet" href="assets/css/ace-ie.min.css" />
    <![endif]-->

    <!-- HTML5 shim and Respond.js for IE8 support -->
    <!--[if lt IE 9]>
    <script src="assets/js/html5shiv.js"></script>
    <script src="assets/js/respond.min.js"></script>
    <![endif]-->

    <style>
        /* تحسينات إضافية للواجهة العربية */
        .login-container {
            direction: rtl;
        }
        .input-icon > input {
            padding-right: 30px !important;
            padding-left: 12px !important;
        }
        .input-icon > i {
            right: 8px;
            left: auto;
        }
        .pull-right {
            float: left !important;
        }
        .error-message {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body class="login-layout">
    <div class="main-container">
        <div class="main-content">
            <div class="row">
                <div class="col-sm-10 col-sm-offset-1">
                    <div class="login-container" style="margin-top:50px;">
                        <div class="center">
                            <h1>
                                <span class="white"><?php echo htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8'); ?></span>
                            </h1>
                            <h4 class="blue">لوحة التحكم</h4>
                        </div>

                        <div class="space-6"></div>

                        <div class="position-relative">
                            <div id="login-box" class="login-box visible widget-box no-border">
                                <div class="widget-body">
                                    <div class="widget-main">
                                        
                                        <?php if (empty($error_message)): ?>
                                            <h4 class="header blue lighter bigger">
                                                <i class="icon-coffee green"></i>
                                                الرجاء إدخال معلومات الدخول
                                            </h4>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($error_message)): ?>
                                            <div class="error-message">
                                                <i class="icon-exclamation-sign"></i>
                                                <?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="space-6"></div>

                                        <form id="myform" name="myform" method="post" action="validate-admin.php">
                                            <!-- CSRF Protection -->
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            
                                            <fieldset>
                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="text" 
                                                               id="username" 
                                                               name="username" 
                                                               class="form-control" 
                                                               placeholder="اسم المستخدم"
                                                               required
                                                               maxlength="50"
                                                               autocomplete="username" />
                                                        <i class="icon-user"></i>
                                                    </span>
                                                </label>

                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="password" 
                                                               id="password" 
                                                               name="password" 
                                                               class="form-control" 
                                                               placeholder="كلمة المرور"
                                                               required
                                                               minlength="6"
                                                               autocomplete="current-password" />
                                                        <i class="icon-lock"></i>
                                                    </span>
                                                </label>

                                                <div class="space"></div>

                                                <div class="clearfix">
                                                    <button name="login" 
                                                            type="submit" 
                                                            class="width-35 pull-right btn btn-sm btn-primary">
                                                        <i class="icon-key"></i>
                                                        دخول
                                                    </button>
                                                </div>

                                                <div class="space-4"></div>
                                            </fieldset>
                                        </form>

                                    </div><!-- /widget-main -->
                                </div><!-- /widget-body -->
                            </div><!-- /login-box -->

                            <!-- نافذة استعادة كلمة المرور (مخفية افتراضياً) -->
                            <div id="forgot-box" class="forgot-box widget-box no-border" style="display:none;">
                                <div class="widget-body">
                                    <div class="widget-main">
                                        <h4 class="header red lighter bigger">
                                            <i class="icon-key"></i>
                                            استعادة كلمة المرور
                                        </h4>

                                        <div class="space-6"></div>
                                        <p>
                                            أدخل بريدك الإلكتروني لاستلام تعليمات استعادة كلمة المرور
                                        </p>

                                        <form id="forgot-form" method="post" action="forgot-password.php">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            
                                            <fieldset>
                                                <label class="block clearfix">
                                                    <span class="block input-icon input-icon-right">
                                                        <input type="email" 
                                                               name="email" 
                                                               class="form-control" 
                                                               placeholder="البريد الإلكتروني"
                                                               required
                                                               maxlength="100" />
                                                        <i class="icon-envelope"></i>
                                                    </span>
                                                </label>

                                                <div class="clearfix">
                                                    <button type="submit" class="width-35 pull-right btn btn-sm btn-danger">
                                                        <i class="icon-lightbulb"></i>
                                                        إرسال
                                                    </button>
                                                </div>
                                            </fieldset>
                                        </form>
                                    </div><!-- /widget-main -->

                                    <div class="toolbar center">
                                        <a href="#" onclick="showBox('login-box'); return false;" class="back-to-login-link">
                                            <i class="icon-arrow-right"></i>
                                            العودة إلى تسجيل الدخول
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

    <!-- JavaScript files -->
    <script type="text/javascript">
        window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
    </script>

    <!--[if IE]>
    <script type="text/javascript">
        window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
    </script>
    <![endif]-->

    <script type="text/javascript">
        if("ontouchend" in document) {
            document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
        }
    </script>

    <script type="text/javascript">
        /**
         * إظهار نافذة معينة وإخفاء الباقي
         * @param {string} id معرف النافذة المراد إظهارها
         */
        function showBox(id) {
            // إخفاء جميع النوافذ
            document.querySelectorAll('.widget-box').forEach(function(box) {
                box.style.display = 'none';
            });
            
            // إظهار النافذة المطلوبة
            var targetBox = document.getElementById(id);
            if (targetBox) {
                targetBox.style.display = 'block';
            }
        }

        /**
         * التحقق من صحة نموذج تسجيل الدخول قبل الإرسال
         * @returns {boolean}
         */
        function validateLoginForm() {
            var username = document.getElementById('username');
            var password = document.getElementById('password');
            
            if (!username.value || username.value.trim() === '') {
                alert('الرجاء إدخال اسم المستخدم');
                username.focus();
                return false;
            }
            
            if (!password.value || password.value.trim() === '') {
                alert('الرجاء إدخال كلمة المرور');
                password.focus();
                return false;
            }
            
            if (password.value.length < 6) {
                alert('كلمة المرور يجب أن تكون 6 أحرف على الأقل');
                password.focus();
                return false;
            }
            
            return true;
        }

        /**
         * التحقق من صحة نموذج استعادة كلمة المرور
         * @returns {boolean}
         */
        function validateForgotForm() {
            var email = document.querySelector('#forgot-form input[name="email"]');
            
            if (!email.value || email.value.trim() === '') {
                alert('الرجاء إدخال البريد الإلكتروني');
                email.focus();
                return false;
            }
            
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                alert('الرجاء إدخال بريد إلكتروني صحيح');
                email.focus();
                return false;
            }
            
            return true;
        }

        // إضافة مستمعي الأحداث عند تحميل الصفحة
        document.addEventListener('DOMContentLoaded', function() {
            var loginForm = document.getElementById('myform');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    if (!validateLoginForm()) {
                        e.preventDefault();
                    }
                });
            }
            
            var forgotForm = document.getElementById('forgot-form');
            if (forgotForm) {
                forgotForm.addEventListener('submit', function(e) {
                    if (!validateForgotForm()) {
                        e.preventDefault();
                    }
                });
            }
            
            // تفعيل حقول الإدخال التلقائي
            var usernameField = document.getElementById('username');
            if (usernameField) {
                usernameField.focus();
            }
            
            // إخفاء رسالة الخطأ بعد 5 ثواني
            var errorMessage = document.querySelector('.error-message');
            if (errorMessage) {
                setTimeout(function() {
                    errorMessage.style.transition = 'opacity 0.5s';
                    errorMessage.style.opacity = '0';
                    setTimeout(function() {
                        if (errorMessage.parentNode) {
                            errorMessage.style.display = 'none';
                        }
                    }, 500);
                }, 5000);
            }
        });

        // منع إرسال النموذج عند الضغط على Enter في حقل الإدخال
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                var activeElement = document.activeElement;
                if (activeElement.tagName === 'INPUT' && 
                    (activeElement.id === 'username' || activeElement.id === 'password')) {
                    e.preventDefault();
                    document.getElementById('myform').requestSubmit();
                }
            }
        });
    </script>
</body>
</html>