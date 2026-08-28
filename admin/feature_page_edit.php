<?php
/**
 * ملف تحرير محتوى الصفحات المميزة
 * 
 * @filename    feature_page_edit.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تحرير وتحديث عناوين الصفحات المميزة في النظام
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

// التحقق من صلاحيات المستخدم (تم تفعيلها)
function check_user_login() {
    if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}
check_user_login();

/**
 * كلاس إدارة محتوى الصفحات المميزة
 */
class editProduct {
    
    private $msg;
    private $fpc_id;
    private $fpc_heading;
    private $errors = [];
    
    /**
     * constructor
     * @param int $fpc_id معرف المحتوى
     */
    public function __construct($fpc_id) {
        $this->fpc_id = filter_var($fpc_id, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999]
        ]);
        
        if ($this->fpc_id === false || $this->fpc_id === null) {
            throw new InvalidArgumentException('معرف المحتوى غير صحيح');
        }
    }
    
    /**
     * جلب تفاصيل المحتوى
     * @return object|null
     */
    public function detailsObj() {
        global $con;
        
        $sql = "SELECT * FROM featurepage_content WHERE fpc_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->fpc_id);
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
        // تنظيف النص من أي أكواد ضارة مع الاحتفاظ بوسوم HTML المسموح بها
        $allowed_tags = '<p><br><strong><b><em><i><u><ol><ul><li><a><img><div><span><h1><h2><h3><h4><h5><h6><table><tr><td><th>';
        $this->fpc_heading = strip_tags(trim($heading), $allowed_tags);
    }
    
    /**
     * التحقق من صحة البيانات
     * @return bool
     */
    public function valid() {
        $this->errors = [];
        
        if (empty($this->fpc_heading)) {
            $this->errors[] = 'عنوان الصفحة مطلوب';
        }
        
        // التحقق من طول النص
        if (strlen($this->fpc_heading) > 65535) { // حد TEXT في MySQL
            $this->errors[] = 'النص طويل جداً';
        }
        
        return empty($this->errors);
    }
    
    /**
     * تحديث البيانات في قاعدة البيانات
     * @return bool
     */
    public function update() {
        global $con;
        
        // استخدام الاستعلامات المحضرة
        $sql = "UPDATE featurepage_content 
                SET fpc_heading = ?,
                    fpc_updated_date = NOW()
                WHERE fpc_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "si", $this->fpc_heading, $this->fpc_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            $this->msg = '<div class="success-message">✅ تم تحديث السجل بنجاح</div>';
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
        return $this->fpc_id;
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
    header("Location: feature_page_view.php");
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
    header("Location: feature_page_view.php");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdate'])) {
    
    // التحقق من رمز CSRF (يفضل إضافته)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     die('طلب غير مصرح به');
    // }
    
    try {
        // تعيين البيانات
        $ob->setHeading($_POST['fpc_heading'] ?? '');
        
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
        
    } catch (Exception $e) {
        $_SESSION['errors'] = [$e->getMessage()];
    }
    
    // إعادة التوجيه لتجنب إعادة الإرسال
    header("Location: feature_page_edit.php?sid=" . $ob->getId());
    exit();
}

// عرض الأخطاء إذا وجدت
if (isset($_SESSION['errors'])) {
    $errorMsg = '<div class="error-message"><ul>';
    foreach ($_SESSION['errors'] as $error) {
        $errorMsg .= '<li>❌ ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>';
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
    
    // المحتوى
    content_css: "css/content.css",
    
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
    // حفظ محتوى TinyMCE في الحقل
    tinyMCE.triggerSave();
    
    var heading = document.getElementById('fpc_heading').value.trim();
    
    if (heading === '') {
        alert('الرجاء إدخال عنوان الصفحة');
        return false;
    }
    
    return confirm('هل أنت متأكد من حفظ التغييرات؟');
}
</script>
<!-- /TinyMCE -->

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <h2>📄 إدارة الصفحات › تعديل الصفحة المميزة</h2>
            
            <!-- عرض رسائل النجاح/الخطأ -->
            <?php if (!empty($msg)): ?>
                <div class="success-message"><?php echo $msg; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMsg)): ?>
                <div class="error-message"><?php echo $errorMsg; ?></div>
            <?php endif; ?>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                
                <em style="display:block; margin:5px;">الحقول التي تحمل <span class="required">*</span> إجبارية</em>
                
                <div class="row buttons">
                    <input type="button" onclick="location.href='feature_page_view.php'" value="🔙 رجوع" class="x2-button" style="margin-right:10px; margin-top:5px;">
                </div>
                
                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">العنوان: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px; height:auto;">
                                                    <textarea name="fpc_heading" id="fpc_heading" class="reg_txtfld" style="width:100%; height:300px;"><?php echo htmlspecialchars($row->fpc_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;"></label>
                                                <div class="formInputBox" style="width:387px; height:auto;">
                                                    <a href="software_feature_list.php" class="x2-link">📋 عرض قائمة المحتوى</a>
                                                </div>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- إضافة رمز CSRF للأمان (يفضل تفعيله) -->
                <!-- <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>"> -->
                
                <div class="row buttons">
                    <input type="submit" name="btnUpdate" id="btnUpdate" value="💾 تحديث" class="x2-button" style="margin-right:10px; margin-top:5px;">
                </div>
                
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

</body>
</html>