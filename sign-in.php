<?php
declare(strict_types=1);

ob_start();
session_start();
require_once __DIR__ . '/common.php';

if (!empty($_SESSION['uid_indm'])) {
    header('Location: index.php');
    exit;
}

$_SESSION['popup'] = 1;
$redirect_url = isset($_GET['redirect']) ? trim((string) $_GET['redirect']) : '';
$error_message = $_SESSION['errr_msg'] ?? '';
unset($_SESSION['errr_msg']);

function googleMark(): string
{
    return '<svg class="google-mark" viewBox="0 0 18 18" aria-hidden="true"><path fill="#4285F4" d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.482h4.844a4.14 4.14 0 0 1-1.797 2.716v2.258h2.909c1.702-1.567 2.684-3.875 2.684-6.615z"/><path fill="#34A853" d="M9 18c2.43 0 4.468-.806 5.956-2.18l-2.909-2.258c-.806.54-1.835.859-3.047.859-2.344 0-4.328-1.585-5.037-3.714H.956v2.332A8.999 8.999 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.963 10.707A5.41 5.41 0 0 1 3.681 9c0-.592.102-1.167.282-1.707V4.961H.956A8.997 8.997 0 0 0 0 9c0 1.452.347 2.827.956 4.039l3.007-2.332z"/><path fill="#EA4335" d="M9 3.579c1.321 0 2.507.454 3.441 1.346l2.581-2.582C13.464.891 11.427 0 9 0A8.999 8.999 0 0 0 .956 4.961l3.007 2.332C4.672 5.164 6.656 3.579 9 3.579z"/></svg>';
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    <link href="css/my-v1-v-12.css" rel="stylesheet">
    <link href="css/responsive1.css" rel="stylesheet">
    <link href="css/jf-1.css" rel="stylesheet">
    <script src="js/jquery-1.2.1.min.js"></script>
    <script src="js/uservalid.js"></script>
    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body.auth-page {
            color: #24343a;
            font-family: Tahoma, Arial, sans-serif;
            overflow-x: hidden;
        }
        .site-background {
            background:
                linear-gradient(115deg, rgba(0, 0, 0, .25), rgba(0, 57, 77, .15)),
                url("images/headerbg.jpg") center / cover no-repeat #063b48;
            inset: 0;
            overflow: hidden;
            position: fixed;
            z-index: 0;
        }
        .site-background:after {
            background: rgba(0, 18, 25, .72);
            content: "";
            inset: 0;
            position: absolute;
        }
        .auth-shell {
            align-items: center;
            display: flex;
            justify-content: center;
            min-height: 100svh;
            padding: 22px;
            position: relative;
            z-index: 1;
        }
        .auth-card {
            background: #fff;
            border-radius: 7px;
            box-shadow: 0 18px 60px rgba(0, 0, 0, .32);
            direction: rtl;
            max-height: calc(100svh - 44px);
            max-width: 560px;
            overflow: auto;
            padding: 22px 30px 24px;
            position: relative;
            width: 100%;
        }
        .auth-close {
            color: #708087;
            font-family: Arial, sans-serif;
            font-size: 30px;
            left: 17px;
            line-height: 1;
            position: absolute;
            text-decoration: none;
            top: 13px;
        }
        .auth-tabs {
            direction: ltr;
            display: grid;
            gap: 14px;
            grid-template-columns: 1fr 1fr;
            margin: 0 28px 16px;
        }
        .auth-tab {
            border-bottom: 3px solid transparent;
            color: #0a4d5b;
            cursor: pointer;
            direction: rtl;
            font-size: 17px;
            font-weight: 700;
            padding: 9px 4px;
            text-align: center;
        }
        .auth-tab.active { border-bottom-color: #00667a; }
        .auth-panel {
            display: none;
            margin: 0 auto;
            max-width: 490px;
        }
        .auth-panel.active { display: block; }
        .auth-panel h1 {
            color: #263238;
            font-size: 21px;
            font-weight: 700;
            margin: 6px 0 17px;
            text-align: center;
        }
        .auth-google {
            align-items: center;
            border: 1px solid #d5dadd;
            color: #2c2c2c;
            display: flex;
            font-size: 15px;
            font-weight: 700;
            gap: 10px;
            height: 46px;
            justify-content: center;
            text-decoration: none;
        }
        .google-mark { height: 20px; width: 20px; }
        .auth-divider {
            border-top: 1px solid #e1e5e7;
            margin: 23px 0 12px;
            text-align: center;
        }
        .auth-divider span {
            background: #fff;
            color: #778187;
            padding: 0 18px;
            position: relative;
            top: -10px;
        }
        .auth-methods {
            display: flex;
            gap: 25px;
            justify-content: flex-start;
            margin: 0 0 12px;
        }
        .auth-methods label {
            align-items: center;
            cursor: pointer;
            display: flex;
            font-size: 13px;
            gap: 6px;
        }
        .auth-form > label {
            color: #30383c;
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin: 8px 0 6px;
        }
        .auth-form input[type="text"],
        .auth-form input[type="password"] {
            border: 1px solid #bcc7cc;
            height: 42px;
            padding: 0 12px;
            width: 100%;
        }
        .auth-email-row { display: none; }
        .auth-otp-row, .auth-code-row { display: flex; gap: 7px; }
        .auth-country {
            align-items: center;
            background: #f8fafb;
            border: 1px solid #bcc7cc;
            direction: ltr;
            display: inline-flex;
            font-size: 13px;
            height: 42px;
            justify-content: center;
            min-width: 88px;
        }
        .egypt-flag {
            border-radius: 1px;
            box-shadow: 0 0 0 1px rgba(0,0,0,.12);
            display: inline-grid;
            height: 13px;
            margin-right: 6px;
            overflow: hidden;
            width: 20px;
        }
        .egypt-flag i:nth-child(1) { background: #ce1126; }
        .egypt-flag i:nth-child(2) { background: #fff; position: relative; }
        .egypt-flag i:nth-child(3) { background: #000; }
        .egypt-flag i:nth-child(2):after {
            background: #c8a449;
            content: "";
            height: 3px;
            left: 8px;
            position: absolute;
            top: 1px;
            width: 4px;
        }
        .auth-otp-row input, .auth-code-row input { flex: 1; min-width: 0; }
        .auth-otp-btn, .auth-verify-btn {
            border: 1px solid #b7cbd1;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            min-width: 92px;
        }
        .auth-otp-btn { background: #eef5f7; color: #0a4d5b; }
        .auth-verify-btn { background: #00667a; color: #fff; }
        .auth-code-row { display: none; margin-top: 8px; }
        .auth-code-row.is-visible { display: flex; }
        .auth-otp-status {
            background: #eef8fb;
            border: 1px solid #b9dce6;
            color: #0a4d5b;
            display: none;
            font-size: 12px;
            line-height: 1.5;
            margin-top: 8px;
            padding: 7px 9px;
        }
        .auth-optin {
            align-items: flex-start;
            display: flex !important;
            font-size: 12px !important;
            font-weight: 400 !important;
            gap: 7px;
            line-height: 1.55;
            margin: 12px 0 !important;
        }
        .auth-optin input { margin-top: 3px; }
        .auth-submit {
            background: #00667a;
            border: 0;
            color: #fff;
            cursor: pointer;
            font-size: 15px;
            font-weight: 800;
            height: 44px;
            width: 100%;
        }
        .auth-terms, .auth-switch {
            color: #68777d;
            font-size: 12px;
            line-height: 1.7;
            margin: 10px 0 0;
            text-align: center;
        }
        .auth-terms a, .auth-switch a, .auth-link {
            color: #07596b;
            font-weight: 700;
            text-decoration: underline;
        }
        .auth-error {
            background: #fff2f2;
            border: 1px solid #f0b7b7;
            color: #a12424;
            font-size: 13px;
            margin-bottom: 12px;
            padding: 8px;
            text-align: center;
        }
        .login-google { margin-bottom: 17px; }
        @media (max-width: 640px) {
            .auth-shell { padding: 14px; }
            .auth-card {
                max-height: calc(100svh - 28px);
                max-width: 330px;
                padding: 18px 14px 20px;
            }
            .auth-close { font-size: 25px; left: 10px; top: 9px; }
            .auth-tabs { gap: 7px; margin: 0 17px 12px; }
            .auth-tab { font-size: 14px; padding: 7px 2px; }
            .auth-panel h1 { font-size: 18px; margin-bottom: 13px; }
            .auth-google { font-size: 13px; height: 42px; }
            .auth-divider { margin: 19px 0 9px; }
            .auth-methods { gap: 12px; }
            .auth-otp-row {
                display: grid;
                grid-template-columns: 78px 1fr;
            }
            .auth-country { min-width: 0; }
            .auth-otp-btn { grid-column: 1 / 3; height: 38px; }
            .auth-submit { height: 41px; }
        }
    </style>
</head>
<body class="auth-page">
    <div class="site-background" aria-hidden="true"></div>
    <main class="auth-shell">
        <section class="auth-card" aria-label="الدخول أو إنشاء حساب">
            <a href="index.php" class="auth-close" aria-label="إغلاق">&times;</a>
            <div class="auth-tabs">
                <div class="auth-tab active" data-auth-target="signup">إنشاء حساب</div>
                <div class="auth-tab" data-auth-target="login">تسجيل الدخول</div>
            </div>

            <div class="auth-panel active" data-auth-panel="signup">
                <h1>أنشئ حسابك</h1>
                <a href="google-login.php" class="auth-google"><?php echo googleMark(); ?> التسجيل بواسطة Google</a>
                <div class="auth-divider"><span>أو</span></div>
                <div class="auth-methods">
                    <label><input type="radio" name="join_method" value="mobile" checked> رقم الجوال</label>
                    <label><input type="radio" name="join_method" value="email"> البريد الإلكتروني</label>
                </div>
                <form method="get" action="create_account.php#signupform" class="auth-form">
                    <div class="auth-mobile-row">
                        <label>رقم الجوال *</label>
                        <div class="auth-otp-row">
                            <span class="auth-country"><span class="egypt-flag" aria-label="مصر"><i></i><i></i><i></i></span>+20</span>
                            <input type="text" name="mobile" inputmode="tel" placeholder="أدخل رقم الجوال">
                            <button type="button" class="auth-otp-btn">إرسال رمز التحقق</button>
                        </div>
                        <div class="auth-code-row">
                            <input type="text" name="otp_code" inputmode="numeric" maxlength="6" placeholder="أدخل رمز التحقق">
                            <button type="button" class="auth-verify-btn">تحقق</button>
                        </div>
                        <div class="auth-otp-status"></div>
                    </div>
                    <div class="auth-email-row">
                        <label>البريد الإلكتروني *</label>
                        <input type="text" name="email" inputmode="email" placeholder="أدخل بريدك الإلكتروني">
                    </div>
                    <label class="auth-optin"><input type="checkbox" name="notify_optin" value="1" checked> أريد استقبال تنبيهات طلبات الشراء والعروض المناسبة على البريد والواتساب</label>
                    <button type="submit" class="auth-submit">متابعة</button>
                </form>
                <p class="auth-terms">بالمتابعة، فإنك توافق على <a href="terms.php">الشروط والأحكام</a> و<a href="privacy.php">سياسة الخصوصية</a>.</p>
                <p class="auth-switch">لديك حساب بالفعل؟ <a href="#" data-auth-target="login">سجّل الدخول</a></p>
            </div>

            <div class="auth-panel" data-auth-panel="login" id="loginform">
                <h1>مرحباً بعودتك</h1>
                <a href="google-login.php" class="auth-google login-google"><?php echo googleMark(); ?> تسجيل الدخول بواسطة Google</a>
                <?php if (!empty($error_message)): ?>
                    <div class="auth-error"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <form method="post" action="login.php?redirect=<?php echo urlencode($redirect_url); ?>" name="loginform" onsubmit="return userr_validate()" id="lfm" class="auth-form">
                    <label>البريد الإلكتروني أو رقم الجوال *</label>
                    <input type="text" name="email" id="email" placeholder="أدخل البريد الإلكتروني أو رقم الجوال" value="<?php echo isset($_GET['email']) ? htmlspecialchars((string) $_GET['email'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                    <label>كلمة المرور *</label>
                    <input type="password" name="pass" id="pass" placeholder="أدخل كلمة المرور">
                    <label class="auth-optin"><input type="checkbox" name="notify_optin" value="1" checked> تذكر بيانات الدخول واستقبال التنبيهات المناسبة</label>
                    <button type="submit" name="login" id="login" value="1" class="auth-submit">تسجيل الدخول</button>
                </form>
                <p class="auth-switch"><a href="forgot-password.php" class="auth-link">هل نسيت كلمة المرور؟</a></p>
                <p class="auth-switch"><a href="#" data-auth-target="signup">إنشاء حساب جديد</a></p>
            </div>
        </section>
    </main>
    <script>
        (function () {
            function each(selector, callback) {
                var nodes = document.querySelectorAll(selector);
                for (var i = 0; i < nodes.length; i += 1) {
                    callback(nodes[i]);
                }
            }
            function switchPanel(panel) {
                each('.auth-tab', function (node) { node.classList.remove('active'); });
                each('.auth-panel', function (node) { node.classList.remove('active'); });
                var tab = document.querySelector('.auth-tab[data-auth-target="' + panel + '"]');
                var panelNode = document.querySelector('.auth-panel[data-auth-panel="' + panel + '"]');
                if (tab) { tab.classList.add('active'); }
                if (panelNode) { panelNode.classList.add('active'); }
            }
            each('[data-auth-target]', function (node) {
                node.addEventListener('click', function (event) {
                    event.preventDefault();
                    switchPanel(node.getAttribute('data-auth-target'));
                });
            });
            each('input[name="join_method"]', function (node) {
                node.addEventListener('change', function () {
                    var selected = document.querySelector('input[name="join_method"]:checked');
                    var isMobile = selected && selected.value === 'mobile';
                    document.querySelector('.auth-mobile-row').style.display = isMobile ? 'block' : 'none';
                    document.querySelector('.auth-email-row').style.display = isMobile ? 'none' : 'block';
                });
            });
            document.querySelector('.auth-panel[data-auth-panel="signup"] .auth-form').addEventListener('submit', function (event) {
                var selected = document.querySelector('input[name="join_method"]:checked');
                var isMobile = selected && selected.value === 'mobile';
                var verified = document.querySelector('.auth-verify-btn').getAttribute('data-verified') === 'true';
                if (isMobile && !verified) {
                    event.preventDefault();
                    var status = document.querySelector('.auth-otp-status');
                    status.style.display = 'block';
                    status.textContent = 'يرجى التحقق من الرمز المرسل عبر واتساب قبل المتابعة.';
                    document.querySelector('input[name="otp_code"]').focus();
                    return false;
                }
            });
            document.querySelector('.auth-otp-btn').addEventListener('click', function () {
                var btn = this;
                var statusBox = document.querySelector('.auth-otp-status');
                var mobileInput = document.querySelector('input[name="mobile"]');
                var mobile = mobileInput.value.replace(/^\s+|\s+$/g, '');
                if (!mobile) {
                    mobileInput.focus();
                    return;
                }
                btn.textContent = 'جارٍ الإرسال...';
                btn.disabled = true;
                statusBox.style.display = 'block';
                statusBox.textContent = 'جارٍ إرسال رمز التحقق عبر واتساب...';
                var request = new XMLHttpRequest();
                request.open('POST', 'send_otp.php', true);
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                request.onreadystatechange = function () {
                    if (request.readyState !== 4) { return; }
                    var res = {};
                    try { res = JSON.parse(request.responseText); } catch (error) {}
                    if (request.status >= 200 && request.status < 300 && res.status === 'success') {
                        var codeInput = document.querySelector('input[name="otp_code"]');
                        codeInput.setAttribute('data-request-id', res.request_id || '');
                        codeInput.value = '';
                        document.querySelector('.auth-code-row').classList.add('is-visible');
                        btn.textContent = 'إعادة إرسال الرمز';
                        btn.disabled = false;
                        statusBox.textContent = res.msg || 'تم إرسال الرمز عبر واتساب. أدخل الرمز للمتابعة.';
                        codeInput.focus();
                    } else {
                        btn.textContent = 'إرسال رمز التحقق';
                        btn.disabled = false;
                        statusBox.textContent = res.msg || 'تعذر إرسال الرمز. يرجى المحاولة مرة أخرى.';
                    }
                };
                request.send('mobile=' + encodeURIComponent(mobile));
            });
            document.querySelector('.auth-verify-btn').addEventListener('click', function () {
                var btn = this;
                var statusBox = document.querySelector('.auth-otp-status');
                var mobile = document.querySelector('input[name="mobile"]').value.replace(/^\s+|\s+$/g, '');
                var codeInput = document.querySelector('input[name="otp_code"]');
                var code = codeInput.value.replace(/^\s+|\s+$/g, '');
                var requestId = codeInput.getAttribute('data-request-id') || '';
                if (!code) {
                    codeInput.focus();
                    return;
                }
                btn.textContent = 'جارٍ التحقق...';
                btn.disabled = true;
                var request = new XMLHttpRequest();
                request.open('POST', 'verify_otp.php', true);
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                request.onreadystatechange = function () {
                    if (request.readyState !== 4) { return; }
                    var res = {};
                    try { res = JSON.parse(request.responseText); } catch (error) {}
                    statusBox.style.display = 'block';
                    if (request.status >= 200 && request.status < 300 && res.status === 'success') {
                        statusBox.textContent = 'تم التحقق من رقم الجوال. يمكنك متابعة التسجيل.';
                        btn.textContent = 'تم التحقق';
                        btn.setAttribute('data-verified', 'true');
                    } else {
                        btn.textContent = 'تحقق';
                        btn.disabled = false;
                        statusBox.textContent = res.msg || 'رمز التحقق غير صحيح. حاول مرة أخرى.';
                    }
                };
                request.send('request_id=' + encodeURIComponent(requestId) + '&mobile=' + encodeURIComponent(mobile) + '&code=' + encodeURIComponent(code));
            });
            if (window.location.hash === '#loginform') {
                switchPanel('login');
            }
        })();
    </script>
</body>
</html>
