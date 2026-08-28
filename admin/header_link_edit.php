<?php
/**
 * ملف تحرير روابط الرأس (Header Links)
 * 
 * @filename    header_link_edit.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تحرير وتحديث روابط الرأس مع النصوص والمحتوى المرتبط
 *              مع دعم محرر النصوص TinyMCE والمعالجة الآمنة للبيانات
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت وجلسة العمل
ob_start();

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

/**
 * كلاس إدارة روابط الرأس
 */
class addproduct {
    
    private $msg;
    private $hl_id;
    private $hl_upper_text;
    private $hl_link;
    private $hl_content;
    private $hl_lower_text;
    private $hl_status;
    private $errors = [];
    
    /**
     * constructor
     * @param string $hl_id معرف الرابط (مشفر بـ MD5)
     */
    public function __construct($hl_id) {
        // التحقق من صحة المعرف المشفر
        $this->hl_id = $this->validateMd5Hash($hl_id);
    }
    
    /**
     * التحقق من صحة الـ MD5 hash
     * @param string $hash
     * @return string
     */
    private function validateMd5Hash($hash) {
        $hash = preg_replace('/[^a-f0-9]/', '', $hash);
        return (strlen($hash) === 32) ? $hash : '';
    }
    
    /**
     * جلب تفاصيل الرابط
     * @return object|null
     */
    public function detailsObj() {
        global $con;
        
        if (empty($this->hl_id)) {
            return null;
        }
        
        $sql = "SELECT * FROM header_link WHERE MD5(hl_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->hl_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * تعيين النص العلوي مع التنظيف
     * @param string $value
     */
    public function setUpperText($value) {
        $this->hl_upper_text = $this->sanitizeText($value, 255);
    }
    
    /**
     * تعيين النص السفلي مع التنظيف
     * @param string $value
     */
    public function setLowerText($value) {
        $this->hl_lower_text = $this->sanitizeText($value, 255);
    }
    
    /**
     * تعيين الرابط مع التنظيف
     * @param string $value
     */
    public function setLink($value) {
        $allowed_tags = '<a><b><strong><i><em><span><div><p><br><ul><ol><li>';
        $this->hl_link = $this->sanitizeHtml($value, $allowed_tags);
    }
    
    /**
     * تعيين المحتوى مع التنظيف
     * @param string $value
     */
    public function setContent($value) {
        $allowed_tags = '<a><b><strong><i><em><span><div><p><br><ul><ol><li><img><h1><h2><h3><h4><h5><h6><table><tr><td><th>';
        $this->hl_content = $this->sanitizeHtml($value, $allowed_tags);
    }
    
    /**
     * تعيين الحالة
     * @param string $value
     */
    public function setStatus($value) {
        $this->hl_status = ($value == '1') ? '1' : '0';
    }
    
    /**
     * تنظيف النص العادي
     * @param string $value
     * @param int $maxLength
     * @return string
     */
    private function sanitizeText($value, $maxLength = 255) {
        $clean = trim(strip_tags($value));
        $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
        return substr($clean, 0, $maxLength);
    }
    
    /**
     * تنظيف HTML
     * @param string $value
     * @param string $allowed_tags
     * @return string
     */
    private function sanitizeHtml($value, $allowed_tags = '') {
        // إزالة الـ slashes إذا كانت موجودة
        $value = stripslashes($value);
        
        // تنظيف النص من أي أكواد ضارة مع الاحتفاظ بالوسوم المسموح بها
        $clean = strip_tags($value, $allowed_tags);
        
        // تنظيف الـ HTML من أي أكواد JavaScript ضارة
        $clean = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $clean);
        $clean = preg_replace('#on\w+="[^"]*"#', '', $clean);
        $clean = preg_replace('#on\w+=\'[^\']*\'#', '', $clean);
        $clean = preg_replace('#javascript:[^"]*#', '', $clean);
        
        return $clean;
    }
    
    /**
     * التحقق من صحة البيانات
     * @return bool
     */
    public function valid() {
        $this->errors = [];
        
        if (empty($this->hl_id)) {
            $this->errors[] = 'معرف الرابط غير صحيح';
        }
        
        if (empty($this->hl_upper_text)) {
            $this->errors[] = 'النص العلوي مطلوب';
        }
        
        if (empty($this->hl_lower_text)) {
            $this->errors[] = 'النص السفلي مطلوب';
        }
        
        if (empty($this->hl_link)) {
            $this->errors[] = 'الرابط مطلوب';
        }
        
        if (empty($this->hl_content)) {
            $this->errors[] = 'محتوى التلميح مطلوب';
        }
        
        return empty($this->errors);
    }
    
    /**
     * تحديث البيانات في قاعدة البيانات
     * @return bool
     */
    public function update() {
        global $con;
        
        $sql = "UPDATE header_link SET
                    hl_upper_text = ?,
                    hl_lower_text = ?,
                    hl_link = ?,
                    hl_content = ?,
                    hl_updated_date = NOW(),
                    hl_status = ?
                WHERE MD5(hl_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $this->hl_upper_text,
            $this->hl_lower_text,
            $this->hl_link,
            $this->hl_content,
            $this->hl_status,
            $this->hl_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            if ($affected > 0) {
                $this->msg = '<div class="alert alert-success">✅ تم تحديث رابط الرأس بنجاح</div>';
                return true;
            } else {
                $this->errors[] = 'لم يتم تحديث أي بيانات';
                return false;
            }
        } else {
            $this->errors[] = 'خطأ في تحديث قاعدة البيانات: ' . mysqli_error($con);
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    
    /**
     * الحصول على رسالة النجاح
     * @return string
     */
    public function getMsg() {
        return $this->msg ?? '';
    }
    
    /**
     * الحصول على أخطاء التحقق
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * الحصول على معرف الرابط
     * @return string
     */
    public function getId() {
        return $this->hl_id;
    }
}

// معالجة الرسائل من الجلسة
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// التحقق من وجود token
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    $_SESSION['error'] = 'معرف الرابط غير موجود';
    header("Location: header_link_view.php");
    exit();
}

// إزالة الـ 4 أحرف الأولى من token (لأغراض أمنية)
$token = substr($token, 4);

try {
    $ob = new addproduct($token);
    $row = $ob->detailsObj();
    
    if (!$row) {
        throw new Exception('الرابط غير موجود');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: header_link_view.php");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    
    // تعيين البيانات مع التنظيف
    $ob->setUpperText($_POST['hl_upper_text'] ?? '');
    $ob->setLowerText($_POST['hl_lower_text'] ?? '');
    $ob->setLink($_POST['hl_link'] ?? '');
    $ob->setContent($_POST['hl_content'] ?? '');
    $ob->setStatus($_POST['hl_status'] ?? '0');
    
    // التحقق من الصحة
    if ($ob->valid()) {
        if ($ob->update()) {
            $_SESSION['msg'] = $ob->getMsg();
        } else {
            $_SESSION['errors'] = $ob->getErrors();
        }
    } else {
        $_SESSION['errors'] = $ob->getErrors();
    }
    
    // إعادة التوجيه لتجنب إعادة الإرسال
    header("Location: header_link_edit.php?token=" . $_GET['token']);
    exit();
}

// عرض الأخطاء إذا وجدت
$errorMsg = '';
if (isset($_SESSION['errors'])) {
    $errorMsg = '<div class="alert alert-danger"><i class="icon-remove"></i><ul>';
    foreach ($_SESSION['errors'] as $error) {
        $errorMsg .= '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
    }
    $errorMsg .= '</ul></div>';
    unset($_SESSION['errors']);
}
?>

<?php include "includes/admin-top.php" ?>

<!-- تحميل المكتبات المطلوبة -->
<script src="../js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- TinyMCE - إصدار محدث -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
// تكوين TinyMCE محسن
tinyMCE.init({

    // General options
    mode: "textareas",
    theme: "advanced",
    language: "ar", // دعم اللغة العربية
    
    plugins: "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

    // Theme options
    theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
    
    theme_advanced_toolbar_location: "top",
    theme_advanced_toolbar_align: "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,
    
    /*theme_advanced_disable: "image,advimage",*/

    // Example content CSS (should be your site CSS)
    content_css: "css/content.css",

    // Drop lists for link/image/media/template dialogs
    template_external_list_url: "lists/template_list.js",
    external_link_list_url: "lists/link_list.js",
    external_image_list_url: "lists/image_list.js",
    media_external_list_url: "lists/media_list.js",

    // Style formats
    style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ],
    
    // إعدادات متقدمة
    forced_root_block: false,
    force_p_newlines: false,
    remove_linebreaks: false,
    force_br_newlines: true,
    remove_trailing_nbsp: false,
    verify_html: false,
    
    // تحسين الأمان
    extended_valid_elements: "iframe[src|width|height|name|align|frameborder|scrolling|style]",
    
    // دعم اللغة العربية
    directionality: "rtl",
    
    // تحسين الأداء
    gecko_spellcheck: true,
    
    // Custom callback
    setup: function(ed) {
        ed.onChange.add(function(ed, l) {
            // تحديث النص تلقائياً
            tinyMCE.triggerSave();
        });
    }
});
</script>
<!-- /TinyMCE -->

<script>
// دالة إظهار/إخفاء قسم رفع الصور
function showUploader() {
    if($('#hl_status').is(':checked')) {
        $("#uploadImageDiv").css("display", "block");
    } else {
        $("#uploadImageDiv").css("display", "none");
    }
}

// دالة التحقق من النموذج
function myvalid() {
    // حفظ محتوى TinyMCE في الحقول
    tinyMCE.triggerSave();
    
    var hl_upper_text = $('#hl_upper_text').val().trim();
    var hl_lower_text = $('#hl_lower_text').val().trim();
    var hl_link = $('#hl_link').val().trim();
    var hl_content = $('#hl_content').val().trim();
    
    var message = "";
    var valid = true;
    
    if(hl_upper_text === "") {
        message = "الرجاء إدخال النص العلوي";
        $('#hl_upper_text').focus();
        valid = false;
    } else if(hl_lower_text === "") {
        message = "الرجاء إدخال النص السفلي";
        $('#hl_lower_text').focus();
        valid = false;
    } else if(hl_link === "") {
        message = "الرجاء إدخال الرابط";
        $('#hl_link').focus();
        valid = false;
    } else if(hl_content === "") {
        message = "الرجاء إدخال محتوى التلميح";
        $('#hl_content').focus();
        valid = false;
    }
    
    if(!valid) {
        $('#message').html('<div class="alert alert-danger"><i class="icon-remove"></i> ' + message + '</div>');
        return false;
    }
    
    return confirm('هل أنت متأكد من حفظ التغييرات؟');
}

$(document).ready(function() {
    // تفعيل tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>
                <i class="icon-link"></i> 
                إدارة الروابط › تعديل رابط الرأس
            </h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                
                <em style="display:block; margin:5px;">
                    الحقول التي تحمل <span class="required">*</span> إجبارية
                </em>
                
                <div class="row buttons">
                    <input type="button" class="x2-button" onClick="window.location ='header_link_view.php'" value="🔙 عودة إلى القائمة">
                </div>
                
                <!-- رسائل النظام -->
                <div id="message">
                    <?php echo $msg . $errorMsg; ?>
                </div>
                
                <br />
                
                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- حالة الرابط -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الحالة:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="hl_status" value="1" 
                                                            <?php echo ($row->hl_status == '1') ? 'checked' : ''; ?>>
                                                        <span class="label label-success">نشط</span>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="hl_status" value="0" 
                                                            <?php echo ($row->hl_status == '0') ? 'checked' : ''; ?>>
                                                        <span class="label label-default">غير نشط</span>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <!-- النص العلوي -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">النص العلوي: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <input type="text" name="hl_upper_text" id="hl_upper_text" 
                                                           class="form-control" 
                                                           value="<?php echo htmlspecialchars($row->hl_upper_text ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           maxlength="255" />
                                                </div>
                                            </div>
                                            
                                            <!-- النص السفلي -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">النص السفلي: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <input type="text" name="hl_lower_text" id="hl_lower_text" 
                                                           class="form-control" 
                                                           value="<?php echo htmlspecialchars($row->hl_lower_text ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           maxlength="255" />
                                                </div>
                                            </div>
                                            
                                            <!-- الرابط -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الرابط: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <textarea name="hl_link" id="hl_link" class="form-control" 
                                                              style="height: 150px;"><?php echo htmlspecialchars($row->hl_link ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <span class="help-block">يمكن استخدام HTML بسيط مثل الروابط والتنسيقات الأساسية</span>
                                                </div>
                                            </div>
                                            
                                            <!-- محتوى التلميح -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">محتوى التلميح: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <textarea name="hl_content" id="hl_content" class="form-control" 
                                                              style="height: 300px;"><?php echo htmlspecialchars($row->hl_content ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <span class="help-block">محتوى منسق يظهر عند تمرير المؤشر على الرابط</span>
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
                <div class="row buttons" style="margin-top: 20px;">
                    <button type="submit" name="btnAdd" id="btnAdd" class="x2-button btn-success">
                        <i class="icon-save"></i> تحديث
                    </button>
                    <button type="button" class="x2-button" onclick="window.location='header_link_view.php'">
                        <i class="icon-remove"></i> إلغاء
                    </button>
                </div>
                
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<!-- نهاية ملف header_link_edit.php - الإصدار 2.0.0 -->
</body>
</html>