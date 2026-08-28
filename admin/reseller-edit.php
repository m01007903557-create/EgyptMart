<?php
/**
 * File: reseller-edit.php
 * Version: 2.0.0
 * Description: تعديل بيانات الموزع (الاسم - البريد - الخصم - الشروط - الشعار)
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

/**
 * Class EditReseller - تعديل بيانات الموزع
 */
class EditReseller {
    private ?string $msg = null;
    private string $reseller_id;
    private ?string $reseller_fullname;
    private ?string $reseller_uname;
    private ?string $reseller_email;
    private ?string $reseller_domain;
    private ?string $reseller_discount;
    private ?string $reseller_website;
    private ?string $reseller_terms;
    private ?string $reseller_logo;
    private int $reslid;
    private mysqli $db;
    
    // الحد الأقصى لحجم الصورة (2 ميجابايت)
    private const MAX_LOGO_SIZE = 2 * 1024 * 1024;
    
    /**
     * المُنشئ
     */
    public function __construct(string $reseller_id, ?mysqli $databaseConnection = null) {
        global $con;
        $this->reseller_id = $reseller_id;
        $this->db = $databaseConnection ?? $con;
    }
    
    /**
     * جلب تفاصيل الموزع
     */
    public function detailsObj(): ?object {
        $sql = "SELECT * FROM reseller WHERE md5(reseller_id) = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->reseller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * التحقق من صحة البريد الإلكتروني
     */
    private function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $this->msg = null;
        
        if (empty($this->reseller_fullname)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال الاسم الكامل</font>';
            return false;
        }
        
        if (empty($this->reseller_uname)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال اسم المستخدم</font>';
            return false;
        }
        
        if (empty($this->reseller_email)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال البريد الإلكتروني</font>';
            return false;
        }
        
        if (!$this->isValidEmail($this->reseller_email)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال بريد إلكتروني صحيح</font>';
            return false;
        }
        
        if (empty($this->reseller_domain)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال النطاق</font>';
            return false;
        }
        
        if ($this->reseller_discount === '' || $this->reseller_discount === null) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال الخصم</font>';
            return false;
        }
        
        if (!is_numeric($this->reseller_discount)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال قيمة رقمية للخصم</font>';
            return false;
        }
        
        if (empty($this->reseller_website)) {
            $this->msg = '<font color="#CC0000">الرجاء إدخال الموقع الإلكتروني</font>';
            return false;
        }
        
        return true;
    }
    
    /**
     * معالجة صورة الشعار
     */
    private function processLogo(array $file): ?string {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // التحقق من حجم الملف
        if ($file['size'] > self::MAX_LOGO_SIZE) {
            $this->msg = '<font color="#CC0000">حجم الشعار يجب أن يكون أقل من 2 ميجابايت</font>';
            return null;
        }
        
        // التحقق من نوع الملف
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes, true)) {
            $this->msg = '<font color="#CC0000">نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, GIF</font>';
            return null;
        }
        
        // قراءة محتوى الصورة
        $imageData = file_get_contents($file['tmp_name']);
        if ($imageData === false) {
            $this->msg = '<font color="#CC0000">فشل في قراءة ملف الشعار</font>';
            return null;
        }
        
        return $imageData;
    }
    
    /**
     * تحديث بيانات الموزع
     */
    public function update(): bool {
        if (!$this->valid()) {
            return false;
        }
        
        // معالجة الشعار إذا تم رفعه
        $logoData = null;
        if (isset($_FILES['reseller_logo']) && $_FILES['reseller_logo']['error'] === UPLOAD_ERR_OK) {
            $logoData = $this->processLogo($_FILES['reseller_logo']);
            if ($logoData === null) {
                return false; // رسالة الخطأ موجودة في processLogo
            }
        }
        
        if ($logoData !== null) {
            // تحديث مع الشعار
            $sql = "UPDATE reseller SET 
                    reseller_fullname = ?,
                    reseller_uname = ?,
                    reseller_email = ?,
                    reseller_domain = ?,
                    reseller_website = ?,
                    reseller_discount = ?,
                    reseller_terms = ?,
                    reseller_logo = ?
                    WHERE reseller_id = ?";
            
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                $this->msg = '<font color="#CC0000">خطأ في قاعدة البيانات</font>';
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "ssssssssi", 
                $this->reseller_fullname,
                $this->reseller_uname,
                $this->reseller_email,
                $this->reseller_domain,
                $this->reseller_website,
                $this->reseller_discount,
                $this->reseller_terms,
                $logoData,
                $this->reslid
            );
            
        } else {
            // تحديث بدون شعار
            $sql = "UPDATE reseller SET 
                    reseller_fullname = ?,
                    reseller_uname = ?,
                    reseller_email = ?,
                    reseller_domain = ?,
                    reseller_website = ?,
                    reseller_discount = ?,
                    reseller_terms = ?
                    WHERE reseller_id = ?";
            
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                $this->msg = '<font color="#CC0000">خطأ في قاعدة البيانات</font>';
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "sssssssi", 
                $this->reseller_fullname,
                $this->reseller_uname,
                $this->reseller_email,
                $this->reseller_domain,
                $this->reseller_website,
                $this->reseller_discount,
                $this->reseller_terms,
                $this->reslid
            );
        }
        
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = '<font color="#009900">تم تحديث بيانات الموزع بنجاح</font>';
            return true;
        } else {
            $this->msg = '<font color="#CC0000">فشل التحديث: ' . $error . '</font>';
            return false;
        }
    }
    
    /**
     * تعيين الاسم الكامل
     */
    public function setFullname(?string $fullname): self {
        $this->reseller_fullname = $fullname ? trim($fullname) : null;
        return $this;
    }
    
    /**
     * تعيين اسم المستخدم
     */
    public function setUsername(?string $username): self {
        $this->reseller_uname = $username ? trim($username) : null;
        return $this;
    }
    
    /**
     * تعيين البريد الإلكتروني
     */
    public function setEmail(?string $email): self {
        $this->reseller_email = $email ? trim($email) : null;
        return $this;
    }
    
    /**
     * تعيين النطاق
     */
    public function setDomain(?string $domain): self {
        $this->reseller_domain = $domain ? trim($domain) : null;
        return $this;
    }
    
    /**
     * تعيين الموقع الإلكتروني
     */
    public function setWebsite(?string $website): self {
        $this->reseller_website = $website ? trim($website) : null;
        return $this;
    }
    
    /**
     * تعيين الخصم
     */
    public function setDiscount(?string $discount): self {
        $this->reseller_discount = $discount ? trim($discount) : null;
        return $this;
    }
    
    /**
     * تعيين الشروط
     */
    public function setTerms(?string $terms): self {
        $this->reseller_terms = $terms ? trim($terms) : null;
        return $this;
    }
    
    /**
     * تعيين معرف الموزع
     */
    public function setResellerId(int $reslid): self {
        $this->reslid = $reslid;
        return $this;
    }
    
    /**
     * الحصول على الرسالة
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// التحقق من وجود المعرف
if (!isset($_GET['r']) || empty($_GET['r'])) {
    header("Location: reseller-view.php");
    exit();
}

$resellerid = substr($_GET['r'], 4);
if (empty($resellerid) || strlen($resellerid) !== 32) { // md5 ينتج 32 حرف
    header("Location: reseller-view.php");
    exit();
}

// إنشاء الكائن وجلب التفاصيل
$ob = new EditReseller($resellerid);
$row = $ob->detailsObj();

if (!$row) {
    $_SESSION['msg'] = '<font color="#CC0000">الموزع غير موجود</font>';
    header("Location: reseller-view.php");
    exit();
}

// معالجة النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->setFullname($_POST['reseller_fullname'] ?? '')
       ->setUsername($_POST['reseller_uname'] ?? '')
       ->setEmail($_POST['reseller_email'] ?? '')
       ->setDomain($_POST['reseller_domain'] ?? '')
       ->setWebsite($_POST['reseller_website'] ?? '')
       ->setDiscount($_POST['reseller_discount'] ?? '')
       ->setTerms($_POST['reseller_terms'] ?? '')
       ->setResellerId((int)($_POST['reslid'] ?? 0));
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->getMessage();
    header("Location: reseller-edit.php?r=" . urlencode($_GET['r']));
    exit();
}

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<?php include "includes/admin-top.php" ?>

<script src="js/jquery-1.2.1.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
tinyMCE.init({
    // General options
    mode: "textareas",
    theme: "advanced",
    
    plugins: "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

    // Theme options
    theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
    theme_advanced_toolbar_location: "top",
    theme_advanced_toolbar_align: "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,
    
    content_css: "css/content.css",

    template_external_list_url: "lists/template_list.js",
    external_link_list_url: "lists/link_list.js",
    external_image_list_url: "lists/image_list.js",
    media_external_list_url: "lists/media_list.js",

    style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ],         
    forced_root_block: false,
    force_p_newlines: false,
    remove_linebreaks: false,
    force_br_newlines: true,
    remove_trailing_nbsp: false,
    verify_html: false             
});

/**
 * التحقق من صحة النموذج
 */
function validateForm() {
    var fullname = document.getElementById('reseller_fullname');
    var username = document.getElementById('reseller_uname');
    var email = document.getElementById('reseller_email');
    var domain = document.getElementById('reseller_domain');
    var discount = document.getElementById('reseller_discount');
    var website = document.getElementById('reseller_website');
    var logo = document.getElementById('reseller_logo');
    
    var message = "";
    var valid = true;
    
    if (!fullname.value || fullname.value.trim() === '') {
        message = 'الرجاء إدخال الاسم الكامل';
        fullname.focus();
        valid = false;
    }
    else if (!username.value || username.value.trim() === '') {
        message = 'الرجاء إدخال اسم المستخدم';
        username.focus();
        valid = false;
    }
    else if (!email.value || email.value.trim() === '') {
        message = 'الرجاء إدخال البريد الإلكتروني';
        email.focus();
        valid = false;
    }
    else if (!domain.value || domain.value.trim() === '') {
        message = 'الرجاء إدخال النطاق';
        domain.focus();
        valid = false;
    }
    else if (!discount.value || discount.value.trim() === '') {
        message = 'الرجاء إدخال الخصم';
        discount.focus();
        valid = false;
    }
    else if (isNaN(discount.value)) {
        message = 'الرجاء إدخال قيمة رقمية للخصم';
        discount.value = '';
        discount.focus();
        valid = false;
    }
    else if (!website.value || website.value.trim() === '') {
        message = 'الرجاء إدخال الموقع الإلكتروني';
        website.focus();
        valid = false;
    }
    
    if (!valid) {
        document.getElementById('message').innerHTML = '<font color="#CC0000">' + message + '</font>';
    }
    
    return valid;
}
</script>
<!-- /TinyMCE -->

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<style>
/* تحسينات إضافية */
.formItem {
    margin-bottom: 15px;
    padding: 5px 0;
    border-bottom: 1px dashed #eee;
}

.formItem label {
    font-weight: bold;
    color: #333;
}

.formItem .formInputBox {
    background: #f9f9f9;
    padding: 8px;
    border-radius: 3px;
}

.reg_txtfld {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

.reg_txtarea {
    width: 100%;
    padding: 6px;
    border: 1px solid #ccc;
    border-radius: 3px;
    resize: vertical;
}

.current-logo {
    margin: 10px 0;
    padding: 10px;
    background: #f0f0f0;
    border-radius: 3px;
    display: inline-block;
}

.current-logo img {
    border: 1px solid #ddd;
    padding: 2px;
    border-radius: 3px;
    background: white;
}

#message {
    margin: 10px 0;
    padding: 10px;
    border-radius: 3px;
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;إدارة الموزعين&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;تعديل بيانات الموزع</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return validateForm();">
                <em style="display:block;margin:5px;">
                    الحقول التي تحمل علامة <span style="color:#F00">*</span> مطلوبة
                </em>
                
                <div id="message"><?php echo $msg; ?></div>
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            <!-- الاسم الكامل -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الاسم الكامل: <span style="color:#F00">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="reseller_fullname" 
                                                           id="reseller_fullname" 
                                                           type="text" 
                                                           class="reg_txtfld" 
                                                           maxlength="255" 
                                                           value="<?php echo htmlspecialchars($row->reseller_fullname ?? ''); ?>" />
                                                </div>
                                            </div>
                                            
                                            <!-- اسم المستخدم -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">اسم المستخدم: <span style="color:#F00">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="reseller_uname" 
                                                           id="reseller_uname" 
                                                           type="text" 
                                                           class="reg_txtfld" 
                                                           maxlength="255" 
                                                           value="<?php echo htmlspecialchars($row->reseller_uname ?? ''); ?>" />
                                                </div>
                                            </div>

                                            <!-- البريد الإلكتروني -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">البريد الإلكتروني: <span style="color:#F00">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="reseller_email" 
                                                           id="reseller_email" 
                                                           type="email" 
                                                           class="reg_txtfld" 
                                                           maxlength="255" 
                                                           value="<?php echo htmlspecialchars($row->reseller_email ?? ''); ?>" />
                                                </div>
                                            </div>
                                            
                                            <!-- النطاق -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">النطاق: <span style="color:#F00">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="reseller_domain" 
                                                           id="reseller_domain" 
                                                           type="text" 
                                                           class="reg_txtfld" 
                                                           maxlength="255" 
                                                           value="<?php echo htmlspecialchars($row->reseller_domain ?? ''); ?>" />
                                                </div>
                                            </div>
                                            
                                            <!-- الخصم -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الخصم (%): <span style="color:#F00">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="reseller_discount" 
                                                           id="reseller_discount" 
                                                           type="number" 
                                                           class="reg_txtfld" 
                                                           min="0" 
                                                           max="100" 
                                                           step="0.01" 
                                                           value="<?php echo htmlspecialchars((string)($row->reseller_discount ?? '0')); ?>" />
                                                </div>
                                            </div>
                                            
                                            <!-- الموقع الإلكتروني -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الموقع الإلكتروني: <span style="color:#F00">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input name="reseller_website" 
                                                           id="reseller_website" 
                                                           type="url" 
                                                           class="reg_txtfld" 
                                                           maxlength="255" 
                                                           value="<?php echo htmlspecialchars($row->reseller_website ?? ''); ?>" />
                                                </div>
                                            </div>
                                            
                                            <!-- الشروط -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الشروط:</label>
                                                <div class="formInputBox" style="width:440px;height:auto;">
                                                    <textarea name="reseller_terms" 
                                                              id="reseller_terms" 
                                                              class="reg_txtarea" 
                                                              rows="10" 
                                                              cols="30"><?php echo htmlspecialchars($row->reseller_terms ?? ''); ?></textarea>
                                                </div>
                                            </div>

                                            <!-- الشعار -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الشعار:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <?php if (!empty($row->reseller_logo)): ?>
                                                        <div class="current-logo">
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($row->reseller_logo); ?>" 
                                                                 width="80" height="80" 
                                                                 alt="الشعار الحالي"
                                                                 style="border:1px solid #ddd; padding:2px;">
                                                            <p style="margin:5px 0 0 0; font-size:12px; color:#666;">
                                                                الشعار الحالي
                                                            </p>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="current-logo">
                                                            <img src="../products_images/il_75x75.jpg" 
                                                                 width="80" height="80" 
                                                                 alt="لا يوجد شعار">
                                                            <p style="margin:5px 0 0 0; font-size:12px; color:#666;">
                                                                لا يوجد شعار حالياً
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <input name="reseller_logo" 
                                                           id="reseller_logo" 
                                                           type="file" 
                                                           accept="image/jpeg,image/png,image/gif" />
                                                    <small style="color:#666; display:block; margin-top:5px;">
                                                        (الحد الأقصى: 2 ميجابايت - الصيغ المدعومة: JPG, PNG, GIF)
                                                    </small>
                                                    
                                                    <input type="hidden" name="reslid" id="reslid" 
                                                           value="<?php echo (int)($row->reseller_id ?? 0); ?>">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- أزرار التحكم -->
                <div class="row buttons">
                    <input type="submit" name="btnUpdate" id="btnUpdate" value="تحديث" class="x2-button" style="margin-right:10px;margin-top:5px;">
                    <input type="button" value="إلغاء" class="x2-button" style="margin-top:5px;" onclick="window.location='reseller-view.php';">
                </div>
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<script type="text/javascript">
// معاينة الصورة قبل الرفع
document.getElementById('reseller_logo')?.addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (file) {
        // التحقق من حجم الملف
        if (file.size > 2 * 1024 * 1024) {
            alert('حجم الملف يجب أن يكون أقل من 2 ميجابايت');
            this.value = '';
            return;
        }
        
        // التحقق من نوع الملف
        var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('نوع الملف غير مدعوم. الأنواع المدعومة: JPG, PNG, GIF');
            this.value = '';
            return;
        }
        
        // عرض معاينة
        var reader = new FileReader();
        reader.onload = function(e) {
            var currentLogo = document.querySelector('.current-logo');
            if (currentLogo) {
                var img = currentLogo.querySelector('img');
                if (img) {
                    img.src = e.target.result;
                }
            }
        };
        reader.readAsDataURL(file);
    }
});

// إخفاء رسالة النجاح بعد 5 ثوان
setTimeout(function() {
    var msgDiv = document.getElementById('message');
    if (msgDiv && msgDiv.innerHTML.includes('009900')) {
        msgDiv.style.transition = 'opacity 0.5s';
        msgDiv.style.opacity = '0';
        setTimeout(function() {
            msgDiv.style.display = 'none';
        }, 500);
    }
}, 5000);
</script>

</body>
</html>
<?php ob_end_flush(); ?>