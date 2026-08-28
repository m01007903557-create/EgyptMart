<?php
/**
 * File: reseller-terms.php
 * Version: 2.0.0
 * Description: إدارة شروط وأحكام الموزع (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// تضمين الملفات المطلوبة
require_once "../common.php";

// التحقق من تسجيل الدخول
check_user_login();

// الحصول على معرف الموزع من الجلسة
$reseller_id = $_SESSION['reseller_id'] ?? 0;

if ($reseller_id <= 0) {
    $_SESSION['msg'] = '<font color="#CC0000">الرجاء تسجيل الدخول أولاً</font>';
    header("Location: login.php");
    exit();
}

// جلب رمز العملة
$currencySymbol = '$';
$curSql = "SELECT st_value FROM site_settings WHERE st_field = 'currency-symbol'";
$curResult = mysqli_query($con, $curSql);
if ($curResult && $curRow = mysqli_fetch_assoc($curResult)) {
    $currencySymbol = $curRow['st_value'] ?: '$';
}

// جلب بيانات الموزع
$resellerData = null;
$sqlmchk = "SELECT * FROM reseller WHERE reseller_id = ?";
$stmt = mysqli_prepare($con, $sqlmchk);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $reseller_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $resellerData = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// جلب الشروط الافتراضية من CMS
$defaultTerms = '';
$tsql = "SELECT cms_content FROM cms WHERE cms_id = 3";
$tres = mysqli_query($con, $tsql);
if ($tres && $trow = mysqli_fetch_assoc($tres)) {
    $defaultTerms = $trow['cms_content'] ?? '';
}

/**
 * Class EditProduct - تحديث شروط الموزع
 */
class EditProduct {
    private ?string $msg = null;
    private int $reseller_id;
    private ?string $reseller_terms;
    private mysqli $db;
    
    /**
     * المُنشئ
     */
    public function __construct(int $reseller_id, ?mysqli $databaseConnection = null) {
        global $con;
        $this->reseller_id = $reseller_id;
        $this->db = $databaseConnection ?? $con;
    }
    
    /**
     * جلب تفاصيل المنتج (محذوفة)
     */
    public function detailsObj(): ?object {
        return null;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $this->msg = null;
        
        if ($this->reseller_terms === null || trim($this->reseller_terms) === '') {
            $this->msg = '<font color="#CC0000">الرجاء إدخال الشروط والأحكام</font>';
            return false;
        }
        
        return true;
    }
    
    /**
     * تحديث شروط الموزع
     */
    public function update(): bool {
        if (!$this->valid()) {
            return false;
        }
        
        $sql = "UPDATE reseller SET reseller_terms = ? WHERE reseller_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">خطأ في قاعدة البيانات</font>';
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $this->reseller_terms, $this->reseller_id);
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = '<font color="#009900">تم تحديث الشروط بنجاح</font>';
            return true;
        } else {
            $this->msg = '<font color="#CC0000">فشل التحديث: ' . $error . '</font>';
            return false;
        }
    }
    
    /**
     * تعيين الشروط
     */
    public function setTerms(?string $terms): self {
        $this->reseller_terms = $terms !== null ? trim($terms) : null;
        return $this;
    }
    
    /**
     * الحصول على الرسالة
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// معالجة النموذج
if (isset($_POST['btnUpdate'])) {
    $ob = new EditProduct($reseller_id);
    $ob->setTerms($_POST['reseller_terms'] ?? '');
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->getMessage();
    header("Location: reseller-terms.php");
    exit();
}

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// تحديد الشروط المعروضة
$currentTerms = '';
if ($resellerData && !empty($resellerData['reseller_terms'])) {
    $currentTerms = $resellerData['reseller_terms'];
} else {
    $currentTerms = $defaultTerms;
}
?>

<?php include "includes/admin-top.php" ?>

<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
// تهيئة TinyMCE
tinyMCE.init({
    mode: "textareas",
    theme: "advanced",
    plugins: "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
    theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
    theme_advanced_toolbar_location: "top",
    theme_advanced_toolbar_align: "right",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,
    content_css: "css/content.css",
    directionality: "rtl",
    language: "ar",
    forced_root_block: false,
    force_p_newlines: false,
    remove_linebreaks: false,
    force_br_newlines: true,
    remove_trailing_nbsp: false,
    verify_html: false
});

/**
 * التحقق من صحة النموذج قبل الإرسال
 */
function validateForm() {
    var content = tinyMCE.get('reseller_terms').getContent();
    
    if (!content || content.trim() === '') {
        document.getElementById('err_msg').innerHTML = '<font color="#CC0000">الرجاء إدخال الشروط والأحكام</font>';
        return false;
    }
    
    return true;
}

$(document).ready(function() {
    // إضافة زر معاينة
    var previewBtn = $('<button type="button" class="x2-button" style="margin-right:10px;" onclick="previewTerms()">معاينة</button>');
    $('.row.buttons').append(previewBtn);
    
    // إضافة زر استعادة الافتراضي
    var resetBtn = $('<button type="button" class="x2-button" style="background:#6c757d;" onclick="resetToDefault()">استعادة الافتراضي</button>');
    $('.row.buttons').append(resetBtn);
});

/**
 * معاينة الشروط
 */
function previewTerms() {
    var content = tinyMCE.get('reseller_terms').getContent();
    var previewWindow = window.open('', '_blank');
    previewWindow.document.write('<html><head><title>معاينة الشروط</title>');
    previewWindow.document.write('<link rel="stylesheet" href="style/style.css">');
    previewWindow.document.write('</head><body><div style="padding:20px; direction:rtl;">');
    previewWindow.document.write(content);
    previewWindow.document.write('</div></body></html>');
    previewWindow.document.close();
}

/**
 * استعادة الشروط الافتراضية
 */
function resetToDefault() {
    if (confirm('هل أنت متأكد من استعادة الشروط الافتراضية؟')) {
        tinyMCE.get('reseller_terms').setContent('<?php echo addslashes($defaultTerms); ?>');
    }
}

/**
 * تكبير وتصغير حجم الخط
 */
function adjustFontSize(delta) {
    var editor = tinyMCE.get('reseller_terms');
    var body = editor.getBody();
    var currentSize = parseInt(window.getComputedStyle(body).fontSize);
    body.style.fontSize = (currentSize + delta) + 'px';
}
</script>
<!-- /TinyMCE -->

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<style>
/* تحسينات إضافية */
.formItem {
    margin-bottom: 20px;
}

.formItem label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    display: block;
}

.formInputBox {
    background: #f9f9f9;
    padding: 10px;
    border-radius: 5px;
}

.reg_txtarea {
    width: 100%;
    min-height: 300px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 3px;
    resize: vertical;
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

.toolbar {
    margin-bottom: 10px;
    padding: 5px;
    background: #f0f0f0;
    border-radius: 3px;
}

.toolbar button {
    padding: 3px 8px;
    margin: 0 2px;
    border: 1px solid #ccc;
    background: white;
    border-radius: 3px;
    cursor: pointer;
}

.toolbar button:hover {
    background: #e0e0e0;
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;شروط الموزع&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;تحديث الشروط</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validateForm();">
                
                <div class="info-box">
                    <strong>معلومات:</strong> يمكنك تنسيق النص باستخدام الأدوات المتاحة. الشروط الافتراضية مستوردة من نظام إدارة المحتوى.
                </div>
                
                <strong>
                    <font color="#CC0000">
                        <label id='err_msg' style="width:200px; color:#D00;"><?php echo $msg; ?></label>
                    </font>
                </strong>
                <br />

                <div class="x2-layout" style="width:650px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow" style="width:500px;">
                                        <td style="width:278px;">
                                            
                                            <!-- شريط أدوات إضافي -->
                                            <div class="toolbar">
                                                <button type="button" onclick="adjustFontSize(2)">+ تكبير النص</button>
                                                <button type="button" onclick="adjustFontSize(-2)">- تصغير النص</button>
                                                <button type="button" onclick="tinyMCE.get('reseller_terms').execCommand('Bold')">عريض</button>
                                                <button type="button" onclick="tinyMCE.get('reseller_terms').execCommand('Italic')">مائل</button>
                                                <button type="button" onclick="tinyMCE.get('reseller_terms').execCommand('Underline')">تسطير</button>
                                            </div>
                                            
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الشروط والأحكام:</label>
                                                <div class="formInputBox" style="width:440px; height:auto;">
                                                    <textarea name="reseller_terms" id="reseller_terms" class="reg_txtarea" rows="10" cols="40"><?php echo htmlspecialchars($currentTerms); ?></textarea>
                                                </div>
                                                <div style="margin-top:5px; font-size:12px; color:#666;">
                                                    يمكنك استخدام أدوات التنسيق لتخصيص النص حسب رغبتك
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
                    <button type="button" class="x2-button" style="background:#6c757d; margin-top:5px;" onclick="window.location='welcome.php'">إلغاء</button>
                </div>
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<script type="text/javascript">
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
    var editor = tinyMCE.get('reseller_terms');
    if (editor) {
        var currentContent = editor.getContent();
        var originalContent = '<?php echo addslashes($currentTerms); ?>';
        
        if (currentContent !== originalContent) {
            return 'لديك تغييرات غير محفوظة. هل أنت متأكد من المغادرة؟';
        }
    }
};
</script>

</body>
</html>
<?php ob_end_flush(); ?>