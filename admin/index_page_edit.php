<?php
/**
 * ملف تعديل محتوى الصفحة الرئيسية (Index Page)
 * 
 * @filename    index_page_edit.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تعديل عناوين ومحتوى الصفحة الرئيسية
 *              مع دعم محرر النصوص TinyMCE والمعالجة الآمنة للبيانات
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";
// include '../lib/simpleimage.php'; // غير مستخدم حالياً

// التحقق من صلاحيات المستخدم (يمكن تفعيلها)
// check_user_login();

/**
 * كلاس تعديل محتوى الصفحة الرئيسية
 */
class editProduct {
    
    private $msg;
    private $ic_id;
    private $ic_heading;
    private $ic_content;
    private $errors = [];
    
    /**
     * constructor
     * @param int $ic_id معرف المحتوى
     */
    public function __construct($ic_id) {
        $this->ic_id = $this->validateInt($ic_id);
    }
    
    /**
     * التحقق من صحة الرقم
     * @param mixed $value
     * @return int
     */
    private function validateInt($value) {
        $filtered = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999]
        ]);
        return $filtered !== false ? $filtered : 0;
    }
    
    /**
     * جلب تفاصيل المحتوى
     * @return object|null
     */
    public function detailsObj() {
        global $con;
        
        if ($this->ic_id <= 0) {
            return null;
        }
        
        $sql = "SELECT * FROM index_content WHERE ic_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->ic_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * تعيين العنوان مع التنظيف
     * @param string $heading
     */
    public function setHeading($heading) {
        // تنظيف النص مع الاحتفاظ ببعض وسوم HTML
        $allowed_tags = '<p><br><strong><b><em><i><u><span><div>';
        $this->ic_heading = $this->sanitizeHtml($heading, $allowed_tags);
    }
    
    /**
     * تعيين المحتوى مع التنظيف
     * @param string $content
     */
    public function setContent($content) {
        // للمحتوى نسمح بوسوم أكثر
        $allowed_tags = '<p><br><strong><b><em><i><u><ol><ul><li><a><img><div><span><h1><h2><h3><h4><h5><h6><table><tr><td><th><blockquote><pre><code>';
        $this->ic_content = $this->sanitizeHtml($content, $allowed_tags);
    }
    
    /**
     * تنظيف HTML من الأكواد الضارة
     * @param string $html
     * @param string $allowed_tags
     * @return string
     */
    private function sanitizeHtml($html, $allowed_tags = '') {
        // إزالة الـ slashes إذا كانت موجودة
        $html = stripslashes($html);
        
        // تنظيف النص من أي أكواد ضارة مع الاحتفاظ بالوسوم المسموح بها
        $clean = strip_tags($html, $allowed_tags);
        
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
        
        if ($this->ic_id <= 0) {
            $this->errors[] = 'معرف المحتوى غير صحيح';
        }
        
        if (empty($this->ic_heading)) {
            $this->errors[] = 'عنوان الصفحة مطلوب';
        }
        
        // التحقق من طول النص
        if (strlen($this->ic_heading) > 65535) {
            $this->errors[] = 'العنوان طويل جداً';
        }
        
        return empty($this->errors);
    }
    
    /**
     * تحديث البيانات في قاعدة البيانات
     * @return bool
     */
    public function update() {
        global $con;
        
        // تحديد الاستعلام حسب وجود المحتوى أو لا
        if (!empty($this->ic_content)) {
            $sql = "UPDATE index_content 
                    SET ic_heading = ?,
                        ic_content = ?,
                        ic_updated_date = NOW()
                    WHERE ic_id = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssi", 
                $this->ic_heading,
                $this->ic_content,
                $this->ic_id
            );
        } else {
            $sql = "UPDATE index_content 
                    SET ic_heading = ?,
                        ic_updated_date = NOW()
                    WHERE ic_id = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "si", 
                $this->ic_heading,
                $this->ic_id
            );
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            $this->msg = '<div class="alert alert-success">✅ تم تحديث السجل بنجاح</div>';
            return true;
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
     * الحصول على معرف المحتوى
     * @return int
     */
    public function getId() {
        return $this->ic_id;
    }
    
    /**
     * التحقق مما إذا كان المحتوى يحتوي على قائمة مرتبطة
     * @return bool
     */
    public function hasList() {
        return in_array($this->ic_id, [2, 3, 5]);
    }
    
    /**
     * الحصول على رابط القائمة المناسب
     * @return string
     */
    public function getListLink() {
        switch ($this->ic_id) {
            case 2:
                return 'software_version_list.php';
            case 3:
                return 'software_feature_list.php';
            case 5:
                return 'service_list.php';
            default:
                return '#';
        }
    }
}

// معالجة الرسائل من الجلسة
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// التحقق من وجود معرف الصفحة
$sid = filter_input(INPUT_GET, 'sid', FILTER_VALIDATE_INT);
if (!$sid) {
    $_SESSION['error'] = 'معرف الصفحة غير صحيح';
    header("Location: index_page_view.php");
    exit();
}

try {
    $ob = new editProduct($sid);
    $row = $ob->detailsObj();
    
    if (!$row) {
        throw new Exception('الصفحة غير موجودة');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: index_page_view.php");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdate'])) {
    
    $ob->setHeading($_POST['ic_heading'] ?? '');
    
    // تعيين المحتوى فقط إذا كان موجوداً في النموذج
    if (isset($_POST['ic_content'])) {
        $ob->setContent($_POST['ic_content'] ?? '');
    }
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    // تخزين الأخطاء في الجلسة
    if (!empty($ob->getErrors())) {
        $_SESSION['errors'] = $ob->getErrors();
    }
    
    $_SESSION['msg'] = $ob->getMsg();
    
    header("Location: index_page_edit.php?sid=" . $ob->getId());
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
<script src="js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

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

// دالة التحقق من النموذج
function myvalid() {
    // حفظ محتوى TinyMCE في الحقول
    tinyMCE.triggerSave();
    
    var heading = $('#ic_heading').val().trim();
    
    if(heading === '') {
        $('#message').html('<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال عنوان الصفحة</div>');
        $('#ic_heading').focus();
        return false;
    }
    
    return confirm('هل أنت متأكد من حفظ التغييرات؟');
}

$(document).ready(function() {
    // تفعيل tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>
<!-- /TinyMCE -->

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <h2>
                <i class="icon-edit"></i> 
                إدارة الصفحات › تعديل محتوى الصفحة الرئيسية
                <?php if ($row): ?>
                <small>(<?php echo htmlspecialchars($row->ic_name ?? 'غير معروف'); ?>)</small>
                <?php endif; ?>
            </h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                
                <em style="display:block; margin:5px;">
                    الحقول التي تحمل <span class="required">*</span> إجبارية
                </em>
                
                <div class="row buttons">
                    <input type="button" onclick="location.href='index_page_view.php'" 
                           value="🔙 عودة إلى القائمة" class="x2-button" style="margin-right:10px; margin-top:5px;">
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
                                            
                                            <!-- العنوان -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">العنوان: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <textarea name="ic_heading" id="ic_heading" 
                                                              class="form-control" style="height: 150px;"><?php echo htmlspecialchars($row->ic_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <span class="help-block">عنوان القسم في الصفحة الرئيسية</span>
                                                </div>
                                            </div>
                                            
                                            <?php if ($ob->hasList()): ?>
                                            <!-- روابط القوائم المرتبطة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;"></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <div class="alert alert-info">
                                                        <i class="icon-info-sign"></i>
                                                        هذا القسم يعرض محتوى من قائمة مرتبطة:
                                                        <br>
                                                        <a href="<?php echo $ob->getListLink(); ?>" class="btn btn-info" style="margin-top: 10px;">
                                                            <i class="icon-list"></i> إدارة المحتوى المرتبط
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php else: ?>
                                            <!-- المحتوى النصي -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">المحتوى: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <textarea name="ic_content" id="ic_content" 
                                                              class="form-control" style="height: 300px;"><?php echo htmlspecialchars($row->ic_content ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                    <span class="help-block">محتوى القسم في الصفحة الرئيسية</span>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- أزرار التحكم -->
                <div class="row buttons" style="margin-top: 20px;">
                    <button type="submit" name="btnUpdate" id="btnUpdate" class="x2-button btn-success">
                        <i class="icon-save"></i> تحديث
                    </button>
                    <button type="reset" class="x2-button btn-default">
                        <i class="icon-refresh"></i> إعادة تعيين
                    </button>
                </div>
                
            </form>
            
            <br clear="all"/>
            
            <!-- معلومات إضافية -->
            <div class="alert alert-warning" style="margin-top: 20px;">
                <i class="icon-warning-sign"></i>
                <strong>ملاحظة:</strong> التغييرات في العنوان والمحتوى ستظهر مباشرة في الصفحة الرئيسية.
            </div>
            
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<!-- نهاية ملف index_page_edit.php - الإصدار 2.0.0 -->
</body>
</html>