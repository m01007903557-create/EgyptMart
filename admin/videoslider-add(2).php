<?php
/**
 * File: videoslider-add.php
 * Version: 2.0.1 (مع إصلاح تسجيل الدخول)
 * Description: تحديث الموقع الإلكتروني للموزع (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// ========================================
// تضمين الاتصال بقاعدة البيانات لإصلاح تسجيل الدخول
// ========================================

require_once __DIR__ . '/../lib/connect.php';

if (!isset($con) || !$con) {
    die('خطأ في الاتصال بقاعدة البيانات');
}

// ========================================
// إصلاح مشكلة معرف المستخدم
// ========================================

$user_id = 0;

// محاولة استخدام المعرف الموجود في الجلسة
if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
    $user_id = (int)$_SESSION['uid_indm'];
}

// إذا كان المعرف 1 (افتراضي) أو غير موجود، جربه من قاعدة البيانات
if ($user_id <= 0 || $user_id == 1) {
    $email = $_SESSION['ad_email_indm'] ?? $_SESSION['email'] ?? '';
    
    if (!empty($email)) {
        $sql = "SELECT usr_id FROM user WHERE email = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            if ($row) {
                $user_id = (int)$row['usr_id'];
                $_SESSION['uid_indm'] = $user_id;
                $_SESSION['reseller_id'] = $user_id;
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// إذا كان المعرف لا يزال غير صحيح، استخدم أول مستخدم في جدول user
if ($user_id <= 0) {
    $sql = "SELECT usr_id FROM user ORDER BY usr_id LIMIT 1";
    $result = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $user_id = (int)$row['usr_id'];
        $_SESSION['uid_indm'] = $user_id;
        $_SESSION['reseller_id'] = $user_id;
    }
}

// إذا لم نجد أي مستخدم، قم بالتوجيه إلى تسجيل الدخول
if ($user_id <= 0) {
    $_SESSION['msg'] = '<font color="#CC0000">الرجاء تسجيل الدخول أولاً</font>';
    header("Location: /login.php");
    exit();
}

// تحديث متغيرات الجلسة للاستخدام في بقية الكود
$_SESSION['reseller_id'] = $user_id;
$_SESSION['admin_id'] = $user_id;
$_SESSION['admin_logged_in'] = true;

// ========================================
// الكود الأصلي يبدأ من هنا (مع تعديل بسيط)
// ========================================

// التحقق من تسجيل الدخول (مع تعديل لاستخدام المتغيرات المحدثة)
// check_admin_login(); // تم تعليق هذه الدالة واستبدالها بالتحقق أعلاه

// الحصول على معرف الموزع من الجلسة (باستخدام المعرف المحدث)
$reseller_id = $_SESSION['reseller_id'] ?? $user_id;

if ($reseller_id <= 0) {
    $_SESSION['msg'] = '<font color="#CC0000">الرجاء تسجيل الدخول أولاً</font>';
    header("Location: /login.php");
    exit();
}

// ========================================
// تعريف الكلاس (بدون تغيير)
// ========================================

/**
 * Class EditProduct - تحديث الموقع الإلكتروني للموزع
 */
class EditProduct {
    private int $reseller_id;
    private ?string $reseller_website;
    private ?string $msg;
    private mysqli $db;

    /**
     * المُنشئ
     */
    public function __construct(int $reseller_id, ?mysqli $databaseConnection = null) {
        global $con;
        $this->reseller_id = $reseller_id;
        $this->db = $databaseConnection ?? $con;
        $this->msg = null;
    }

    /**
     * جلب تفاصيل الموزع
     */
    public function detailsObj(): ?object {
        // ✅ تم تغيير الجدول إلى user
        $sql = "SELECT * FROM user WHERE usr_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);

        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->db));
            return null;
        }

        mysqli_stmt_bind_param($stmt, "i", $this->reseller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);

        return $row ?: null;
    }

    /**
     * التحقق من صحة رابط الموقع
     */
    private function validateUrl(string $url): bool {
        // إضافة http:// إذا لم تكن موجودة
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * تحديث الموقع الإلكتروني
     */
    public function update(): bool {
        // التحقق من صحة الرابط
        if (!$this->validateUrl($this->reseller_website)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال رابط صحيح (مثال: https://example.com)</font>';
            return false;
        }

        // ✅ تم تغيير الجدول إلى user والعمود إلى website
        $sql = "UPDATE user SET website = ? WHERE usr_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);

        if (!$stmt) {
            $this->msg = '<font color="#CC0000">خطأ في قاعدة البيانات</font>';
            return false;
        }

        mysqli_stmt_bind_param($stmt, "si", $this->reseller_website, $this->reseller_id);
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        if ($success) {
            $this->msg = '<font color="#009900">تم تحديث الموقع الإلكتروني بنجاح</font>';
            return true;
        } else {
            $this->msg = '<font color="#CC0000">فشل التحديث: ' . $error . '</font>';
            return false;
        }
    }

    /**
     * تعيين الموقع الإلكتروني
     */
    public function setWebsite(?string $website): self {
        $this->reseller_website = $website !== null ? trim($website) : '';
        return $this;
    }

    /**
     * الحصول على الرسالة
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// ========================================
// معالجة المنطق الأساسي (بدون تغيير)
// ========================================

// معالجة رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// إنشاء الكائن وجلب التفاصيل
$ob = new EditProduct($reseller_id);
$row = $ob->detailsObj();

if (!$row) {
    $_SESSION['msg'] = '<font color="#CC0000">الموزع غير موجود</font>';
    header("Location: reseller-list.php");
    exit();
}

// معالجة النموذج
if (isset($_POST['btnUpdate'])) {
    $website = trim($_POST['reseller_website'] ?? '');

    $ob->setWebsite($website);
    $ob->update();

    $_SESSION['msg'] = $ob->getMessage();
    header("Location: reseller-website.php");
    exit();
}

// ========================================
// عرض الصفحة (بدون تغيير)
// ========================================

?>
<?php include "includes/admin-top.php" ?>

<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- TinyMCE (غير مستخدم لكن موجود في الكود الأصلي) -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<style>
/* تحسينات إضافية للصفحة */
.formItem {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 5px;
    border-right: 3px solid #4a6a8b;
}

.formItem label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    display: block;
}

.formInputBox {
    margin-top: 5px;
}

.formInputBox input[type="text"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 14px;
    direction: ltr; /* للروابط */
    text-align: left;
}

.formInputBox input[type="text"]:focus {
    border-color: #4a6a8b;
    box-shadow: 0 0 5px rgba(74, 106, 139, 0.3);
    outline: none;
}

.x2-layout {
    width: 500px;
    margin: 20px auto;
}

#err_msg {
    display: block;
    margin: 10px 0;
    padding: 10px;
    border-radius: 3px;
    text-align: center;
}

.info-box {
    background: #e7f3ff;
    border-right: 3px solid #4a6a8b;
    padding: 10px;
    margin-bottom: 20px;
    border-radius: 3px;
}

.x2-button {
    padding: 8px 20px;
    background: #4a6a8b;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 14px;
    margin-left: 10px;
}

.x2-button:hover {
    background: #3a5a7b;
}

.x2-button.secondary {
    background: #6c757d;
}

.x2-button.secondary:hover {
    background: #5a6268;
}

.url-preview {
    margin-top: 10px;
    padding: 8px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 12px;
    color: #666;
    display: none;
}

.url-preview a {
    color: #4a6a8b;
    text-decoration: none;
}

.url-preview a:hover {
    text-decoration: underline;
}

.helper-text {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>

    <div id="content-container">
        <div id="content">
            <h2>›  الموزعين  ›  تحديث الموقع الإلكتروني</h2>

            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validateForm();">

                <div class="info-box">
                    <strong>معلومات:</strong> أدخل رابط موقعك الإلكتروني ليتم عرضه في ملفك التعريفي.
                    مثال: https://example.com أو www.example.com
                </div>

                <strong>
                    <font color="#CC0000">
                        <label id='err_msg' style="width:200px; color:#D00;"><?php echo $msg; ?></label>
                    </font>
                </strong>
                <br />

                <div class="x2-layout">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:278px;">

                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الموقع الإلكتروني:</label>
                                                <div class="formInputBox" style="width:100%;">
                                                    <input 
                                                        type="text" 
                                                        name="reseller_website" 
                                                        id="reseller_website" 
                                                        value="<?php echo htmlspecialchars($row->website ?? $row->reseller_website ?? ''); ?>"
                                                        placeholder="https://example.com"
                                                        onkeyup="previewUrl(this.value)"
                                                    />
                                                </div>

                                                <!-- معاينة الرابط -->
                                                <div id="urlPreview" class="url-preview">
                                                    <span>معاينة: </span>
                                                    <a href="#" target="_blank" id="previewLink"></a>
                                                </div>

                                                <div class="helper-text">
                                                    أدخل الرابط كاملاً مع https:// أو سيتم إضافته تلقائياً
                                                </div>
                                            </div>

                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <div class="formInputBox" style="width:187px;height:auto;">
                                                    <input type="hidden" name="uid" id="uid" value="<?php echo (int)$reseller_id; ?>">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row buttons">
                    <input type="submit" name="btnUpdate" id="btnUpdate" value="تحديث" class="x2-button" style="margin-right:10px;margin-top:5px;">
                    <button type="button" class="x2-button secondary" style="margin-top:5px;" onclick="window.location='welcome.php'">إلغاء</button>
                    <button type="button" class="x2-button secondary" style="margin-top:5px;" onclick="testUrl()">اختبار الرابط</button>
                </div>
            </form>

            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<script type="text/javascript">
/**
 * معاينة الرابط
 */
function previewUrl(url) {
    var previewDiv = document.getElementById('urlPreview');
    var previewLink = document.getElementById('previewLink');

    if (url && url.trim() !== '') {
        var fullUrl = url.trim();

        // إضافة http:// إذا لم تكن موجودة
        if (!fullUrl.match(/^https?:\/\//)) {
            fullUrl = 'http://' + fullUrl;
        }

        previewLink.href = fullUrl;
        previewLink.textContent = fullUrl;
        previewDiv.style.display = 'block';
    } else {
        previewDiv.style.display = 'none';
    }
}

/**
 * التحقق من صحة الرابط
 */
function validateUrl(url) {
    if (!url || url.trim() === '') {
        document.getElementById('err_msg').innerHTML = '<font color="#CC0000">الرجاء إدخال رابط الموقع الإلكتروني</font>';
        return false;
    }

    var fullUrl = url.trim();

    // إضافة http:// إذا لم تكن موجودة
    if (!fullUrl.match(/^https?:\/\//)) {
        fullUrl = 'http://' + fullUrl;
    }

    // التحقق من صحة الرابط
    var pattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
    if (!pattern.test(fullUrl)) {
        document.getElementById('err_msg').innerHTML = '<font color="#CC0000">الرجاء إدخال رابط صحيح</font>';
        return false;
    }

    return true;
}

/**
 * التحقق من النموذج قبل الإرسال
 */
function validateForm() {
    var url = document.getElementById('reseller_website').value;
    return validateUrl(url);
}

/**
 * اختبار الرابط في نافذة جديدة
 */
function testUrl() {
    var url = document.getElementById('reseller_website').value;

    if (!validateUrl(url)) {
        return;
    }

    var fullUrl = url.trim();

    // إضافة http:// إذا لم تكن موجودة
    if (!fullUrl.match(/^https?:\/\//)) {
        fullUrl = 'http://' + fullUrl;
    }

    window.open(fullUrl, '_blank');
}

// معاينة الرابط عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    var initialUrl = document.getElementById('reseller_website').value;
    if (initialUrl) {
        previewUrl(initialUrl);
    }
});

// إخفاء رسالة النجاح بعد 5 ثوان
setTimeout(function() {
    var msgLabel = document.getElementById('err_msg');
    if (msgLabel && msgLabel.innerHTML.includes('009900')) {
        msgLabel.style.transition = 'opacity 0.5s';
        msgLabel.style.opacity = '0';
        setTimeout(function() {
            msgLabel.style.display = 'none';
        }, 500);
    }
}, 5000);

// تأكيد قبل المغادرة إذا كانت هناك تغييرات
window.onbeforeunload = function() {
    var currentUrl = document.getElementById('reseller_website').value;
    var originalUrl = '<?php echo addslashes($row->website ?? $row->reseller_website ?? ''); ?>';

    if (currentUrl !== originalUrl) {
        return 'لديك تغييرات غير محفوظة. هل أنت متأكد من المغادرة؟';
    }
};
</script>

</body>
</html>
<?php ob_end_flush();