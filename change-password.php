<?php
/**
 * اسم الملف: change-password.php
 * الوصف: صفحة تغيير كلمة المرور للمستخدمين المسجلين
 * الإصدار: 2.0.0
 * تاريخ التحديث: 2024-01-25
 * متطلبات PHP: 8.3
 */

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();
session_start();

// تضمين ملف الإعدادات المشتركة
require_once 'common.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['uid_indm']) || empty($_SESSION['uid_indm'])) {
    header("Location: sign-in.php");
    exit();
}

$uid = intval($_SESSION['uid_indm']);

// استرجاع الرسائل المخزنة في الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$curr_pass = $_SESSION['curr_pass'] ?? '';
$new_pass = $_SESSION['new_pass'] ?? '';
$conf_pass = $_SESSION['conf_pass'] ?? '';

/**
 * كلاس تغيير كلمة المرور
 */
class ChangePassword
{
    private $msg;
    private $curr_pass;
    private $new_pass;
    private $conf_pass;
    private $uid;
    private $con;
    
    /**
     * constructor
     */
    public function __construct($curr_pass, $new_pass, $conf_pass, $uid, $con)
    {
        $this->curr_pass = $curr_pass;
        $this->new_pass = $new_pass;
        $this->conf_pass = $conf_pass;
        $this->uid = intval($uid);
        $this->con = $con;
        
        $_SESSION['curr_pass'] = $this->curr_pass;
        $_SESSION['new_pass'] = $this->new_pass;
        $_SESSION['conf_pass'] = $this->conf_pass;
    }
    
    /**
     * التحقق من صحة كلمة المرور الحالية
     */
    private function validPass(): bool
    {
        $sql = "SELECT usr_id FROM user WHERE pass = ? AND usr_id = ? LIMIT 1";
        $stmt = mysqli_prepare($this->con, $sql);
        
        if (!$stmt) {
            error_log("خطأ في التحضير: " . mysqli_error($this->con));
            return false;
        }
        
        $hashed_pass = md5($this->curr_pass);
        mysqli_stmt_bind_param($stmt, "si", $hashed_pass, $this->uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);
        mysqli_stmt_close($stmt);
        
        return $count > 0;
    }
    
    /**
     * التحقق من صحة المدخلات
     */
    public function isValid(): bool
    {
        if (empty($this->curr_pass)) {
            $this->msg = '<font color="#CC0000">من فضلك أدخل كلمة مرورك الحالية</font>';
            return false;
        }
        
        if (!$this->validPass()) {
            $this->msg = '<font color="#CC0000">كلمة المرور الحالية غير صحيحة</font>';
            return false;
        }
        
        if (empty($this->new_pass)) {
            $this->msg = '<font color="#CC0000">من فضلك أدخل كلمة مرورك الجديدة</font>';
            return false;
        }
        
        // التحقق من قوة كلمة المرور
        if (strlen($this->new_pass) < 6) {
            $this->msg = '<font color="#CC0000">كلمة المرور يجب أن تكون على الأقل 6 أحرف</font>';
            return false;
        }
        
        if (empty($this->conf_pass)) {
            $this->msg = '<font color="#CC0000">من فضلك أعد تأكيد كتابة كلمة المرور الجديدة</font>';
            return false;
        }
        
        if ($this->new_pass !== $this->conf_pass) {
            $this->msg = '<font color="#CC0000">كلمة المرور الجديدة وتأكيدها غير متطابقين</font>';
            return false;
        }
        
        return true;
    }
    
    /**
     * تحديث كلمة المرور
     */
    public function updatePassword(): bool
    {
        $sql = "UPDATE user SET pass = ? WHERE usr_id = ?";
        $stmt = mysqli_prepare($this->con, $sql);
        
        if (!$stmt) {
            error_log("خطأ في تحضير التحديث: " . mysqli_error($this->con));
            return false;
        }
        
        $hashed_new_pass = md5($this->new_pass);
        mysqli_stmt_bind_param($stmt, "si", $hashed_new_pass, $this->uid);
        
        if (!mysqli_stmt_execute($stmt)) {
            error_log("خطأ في تنفيذ التحديث: " . mysqli_stmt_error($stmt));
            mysqli_stmt_close($stmt);
            return false;
        }
        
        $affected_rows = mysqli_stmt_affected_rows($stmt);
        mysqli_stmt_close($stmt);
        
        return $affected_rows > 0;
    }
    
    /**
     * إرسال إشعار البريد الإلكتروني
     */
    private function sendEmailNotification(): void
    {
        $to = user_info($this->uid, 'email');
        if (empty($to)) {
            return;
        }
        
        $subject = "تنبيه تغيير كلمة المرور على " . getWebSiteName();
        $from_email = get_adminemail();
        $from_name = getWebSiteName();
        
        $name_prefix = user_info($this->uid, 'name_prefix') ?? '';
        $first_name = user_info($this->uid, 'fname') ?? '';
        $last_name = user_info($this->uid, 'lname') ?? '';
        
        $message = "
        <html dir='ltr'>
        <head>
            <title>تغيير كلمة المرور</title>
        </head>
        <body>
            <h3>عزيزي " . htmlspecialchars(trim($name_prefix . ' ' . $first_name . ' ' . $last_name)) . "،</h3>
            <p>لقد تم تغيير كلمة المرور الخاصة بك بنجاح في " . getWebSiteName() . ".</p>
            <p>إذا لم تكن أن من قام بهذا التغيير، يرجى الاتصال بفريق الدعم فوراً.</p>
            <br>
            <p>مع تحيات،<br>فريق " . getWebSiteName() . "</p>
        </body>
        </html>";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $from_name <$from_email>\r\n";
        
        // محاولة إرسال البريد الإلكتروني
        if (!mail($to, $subject, $message, $headers)) {
            error_log("فشل إرسال بريد تغيير كلمة المرور للمستخدم: " . $this->uid);
        }
    }
    
    /**
     * تنفيذ عملية تغيير كلمة المرور
     */
    public function execute(): bool
    {
        if (!$this->isValid()) {
            return false;
        }
        
        if ($this->updatePassword()) {
            $this->sendEmailNotification();
            $this->msg = '<font color="#009900">✓ تم تغيير كلمة المرور الخاصة بك بنجاح</font>';
            
            // مسح البيانات من الجلسة
            unset($_SESSION['curr_pass']);
            unset($_SESSION['new_pass']);
            unset($_SESSION['conf_pass']);
            
            return true;
        }
        
        $this->msg = '<font color="#CC0000">عذراً، حدث خطأ أثناء تغيير كلمة المرور. الرجاء المحاولة مرة أخرى.</font>';
        return false;
    }
    
    /**
     * الحصول على رسالة الخطأ/النجاح
     */
    public function getMessage(): string
    {
        return $this->msg ?? '';
    }
}

// معالجة طلب تغيير كلمة المرور
if (isset($_POST['btnAdd'])) {
    // تنظيف المدخلات
    $curr_pass = trim($_POST['curr_pass'] ?? '');
    $new_pass = trim($_POST['new_pass'] ?? '');
    $conf_pass = trim($_POST['conf_pass'] ?? '');
    
    // إنشاء كائن تغيير كلمة المرور
    $changePass = new ChangePassword($curr_pass, $new_pass, $conf_pass, $uid, $con);
    
    // تنفيذ عملية التغيير
    $changePass->execute();
    
    // تخزين الرسالة في الجلسة وإعادة التوجيه
    $_SESSION['msg'] = $changePass->getMessage();
    header("Location: change-password.php");
    exit();
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" dir="rtl" lang="ar">
<head>
    <title><?php echo htmlspecialchars(getSiteTitle()); ?></title>
    <meta name="google-translate-customization" content="fef4c372cb8ae5a0-ab3c7604c23f3803-gde364a5c12cf0b8a-25">
    <meta name="title" content="<?php echo htmlspecialchars(getSiteTitle()); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2)); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3)); ?>">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- CSS Files -->
    <link href="css/my-v1-v-12.css" type="text/css" rel="stylesheet">
    <link href="css/jf-1.css" type="text/css" rel="stylesheet">
    <link href="css/my-v1-v-15.css" type="text/css" rel="stylesheet">
    <link href="css/cp.css" type="text/css" rel="stylesheet">
    
    <!-- JavaScript -->
    <script src="js/jquery-1.2.1.min.js"></script>
    
    <style>
        #changePsw {
            border: 1px solid #6F0000;
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            font-weight: bold;
            padding: 8px 15px;
            text-align: center;
            -webkit-border-radius: 5px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            background-color: #DF0000;
            background: -webkit-gradient(linear, left top, left bottom, from(#DF0000), to(#DF0000));
            background: -moz-linear-gradient(top, #DF0000, #DF0000);
            cursor: pointer;
            font-family: Arial, Helvetica, sans-serif;
            border: none;
            width: 174px;
            height: 35px;
            transition: all 0.3s ease;
        }
        
        #changePsw:hover {
            background-color: #B30000;
            -webkit-box-shadow: 0 3px 5px rgba(0,0,0,0.3);
            -moz-box-shadow: 0 3px 5px rgba(0,0,0,0.3);
            box-shadow: 0 3px 5px rgba(0,0,0,0.3);
            transform: translateY(-2px);
        }
        
        #changePsw:active {
            transform: translateY(0);
            box-shadow: none;
        }
        
        .message-box {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        
        .message-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .password-input {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 8px;
            width: 250px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .password-input:focus {
            border-color: #DF0000;
            outline: none;
            box-shadow: 0 0 5px rgba(223,0,0,0.3);
        }
        
        .required-star {
            color: #ff0000;
            font-weight: bold;
            margin-left: 3px;
        }
        
        @media (max-width: 768px) {
            .password-input {
                width: 100%;
            }
            
            td {
                display: block;
                width: 100%;
                text-align: right !important;
            }
            
            td.textfld2 {
                padding-bottom: 5px;
            }
        }
    </style>
</head>
<body>

<div class="hm1 bbc" id="res-mob1">
    
    <?php include "includes/header_new.php"; ?>
    
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName()); ?>" height="1" width="1"></div>
    
    <?php include 'includes/header_menu.php'; ?>
    
    <div class="bt"><img src="images/z.gif" alt="<?php echo htmlspecialchars(getWebSiteName()); ?>" width="1" height="1"></div>
    
    <div class="inner_wrapper">
        
        <script type="text/javascript">
        function checkValid() {
            var curr_pass = document.getElementById('curr_pass');
            var new_pass = document.getElementById('new_pass');
            var conf_pass = document.getElementById('conf_pass');
            var usid = document.getElementById('usid').value;
            var message = "";
            var valid = true;
            
            // إخفاء رسالة الخطأ السابقة
            var messageDiv = document.getElementById('message');
            messageDiv.style.display = "none";
            messageDiv.className = "";
            
            if (curr_pass.value == '') {
                message = "❌ من فضلك أدخل كلمة مرورك الحالية";
                curr_pass.focus();
                valid = false;
            } else if (new_pass.value == '') {
                message = "❌ من فضلك أدخل كلمة مرور جديدة";
                new_pass.focus();
                valid = false;
            } else if (new_pass.value.length < 6) {
                message = "❌ كلمة المرور يجب أن تكون على الأقل 6 أحرف";
                new_pass.focus();
                valid = false;
            } else if (conf_pass.value == '') {
                message = "❌ من فضلك أعد إدخال كلمة المرور للتأكيد";
                conf_pass.focus();
                valid = false;
            } else if (new_pass.value != conf_pass.value) {
                message = "❌ كلمة المرور الجديدة وتأكيدها غير متطابقين";
                conf_pass.focus();
                valid = false;
            }
            
            if (!valid) {
                messageDiv.style.display = "block";
                messageDiv.className = "message-box message-error";
                messageDiv.innerHTML = message;
                return false;
            }
            
            // إرسال الطلب عبر AJAX
            $.post("ajax-file/changePassword.php", {
                curr_pass: curr_pass.value,
                new_pass: new_pass.value,
                conf_pass: conf_pass.value,
                usid: usid
            }, function(data) {
                var dt = data.split("|");
                messageDiv.style.display = "block";
                
                if (dt[1] == 1) {
                    messageDiv.className = "message-box message-success";
                    document.getElementById('curr_pass').value = '';
                    document.getElementById('new_pass').value = '';
                    document.getElementById('conf_pass').value = '';
                } else {
                    messageDiv.className = "message-box message-error";
                }
                
                messageDiv.innerHTML = dt[0];
            }).fail(function() {
                messageDiv.style.display = "block";
                messageDiv.className = "message-box message-error";
                messageDiv.innerHTML = "❌ حدث خطأ في الاتصال. الرجاء المحاولة مرة أخرى.";
            });
            
            return true;
        }
        </script>
        
        <!-- القائمة الجانبية -->
        <?php include 'includes/left_menu.php'; ?>
        
        <!-- المحتوى الرئيسي -->
        <div class="w56b f1 p2b p14 bl">
            <h1>تغيير كلمة المرور</h1>
            <div>&nbsp;</div>
            
            <!-- رسالة النتيجة -->
            <div id="message" class="message-box <?php echo strpos($msg, '✓') !== false ? 'message-success' : 'message-error'; ?>" 
                 style="<?php echo empty($msg) ? 'display:none;' : 'display:block;'; ?>">
                <?php echo $msg; ?>
            </div>
            
            <table align="CENTER" border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                    <tr>
                        <td style="border-right:0px;" valign="top">
                            <form style="margin:0px;" method="POST" action="" name="ChPass">
                                <input type="hidden" name="usid" id="usid" value="<?php echo $uid; ?>">
                                
                                <table style="BORDER-COLLAPSE: collapse" class="td-padd" border="0" bordercolor="#F2F2F2" bgcolor="#FAF4FF" cellpadding="0" cellspacing="0" width="100%">
                                    <tbody>
                                        <tr>
                                            <td class="textfld2" align="RIGHT" width="40%">
                                                <b><span class="required-star">*</span> كلمة المرور الحالية :</b>
                                            </td>
                                            <td class="adss" style="text-align:left" height="40">
                                                <input type="password" 
                                                       name="curr_pass" 
                                                       id="curr_pass" 
                                                       maxlength="60" 
                                                       size="33" 
                                                       value="<?php echo htmlspecialchars($curr_pass); ?>" 
                                                       class="password-input"
                                                       autocomplete="current-password">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textfld2" align="RIGHT" width="40%">
                                                <b><span class="required-star">*</span> كلمة المرور الجديدة :</b>
                                            </td>
                                            <td style="text-align:left" height="40">
                                                <input type="password" 
                                                       name="new_pass" 
                                                       id="new_pass" 
                                                       maxlength="60" 
                                                       size="33" 
                                                       value="" 
                                                       class="password-input"
                                                       autocomplete="new-password">
                                                <div style="font-size:11px; color:#666;">(6 أحرف على الأقل)</div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="textfld2" align="RIGHT" width="40%">
                                                <b><span class="required-star">*</span> تأكيد كلمة المرور الجديدة :</b>
                                            </td>
                                            <td style="text-align:left" height="40">
                                                <input type="password" 
                                                       name="conf_pass" 
                                                       id="conf_pass" 
                                                       maxlength="60" 
                                                       size="33" 
                                                       value="" 
                                                       class="password-input"
                                                       autocomplete="new-password">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" align="CENTER">
                                                <div align="center">
                                                    <img src="images/zero1.gif" width="1" height="15"><br>
                                                    <table style="BORDER-COLLAPSE: collapse" border="0" cellpadding="5" cellspacing="0" width="48%">
                                                        <tbody>
                                                            <tr>
                                                                <td>
                                                                    <div align="center">
                                                                        <img src="images/zero1.gif" width="1" height="5"><br>
                                                                        <input type="button" 
                                                                               name="btnAdd" 
                                                                               id="changePsw" 
                                                                               value="تغيير كلمة المرور" 
                                                                               onclick="checkValid();">
                                                                        <br>
                                                                        <img src="images/zero1.gif" width="1" height="5"><br>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <img src="images/zero1.gif" width="1" height="15"><br>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <div><br></div>
            <div><br><br><br></div>
        </div>
        
        <!-- تذييل الصفحة -->
        <?php include 'includes/footer.php'; ?>
        
    </div>
</div>

<?php
// إنهاء المخزن المؤقت وإرسال المحتوى
ob_end_flush();
?>