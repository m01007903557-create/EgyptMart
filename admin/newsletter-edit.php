<?php
/**
 * File: newsletter-edit.php

 * Version: 2.0.0
 * Description: إرسال نشرات بريدية جديدة مع إمكانية استهداف فئات وبلدان وشركات محددة (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة
session_start();

// إعدادات المهلة
set_time_limit(600);
ini_set('max_input_time', '600');

// تضمين الملفات المطلوبة
require_once "../common.php";

// التحقق من تسجيل الدخول
check_user_login();

// استرجاع قيم الجلسة
$nc_subject = $_SESSION['nc_subject'] ?? '';
unset($_SESSION['nc_subject']);

$nc_content = $_SESSION['nc_content'] ?? '';
unset($_SESSION['nc_content']);

/**
 * Class AddPlan - إضافة وإرسال النشرات البريدية
 */
class AddPlan {
    private ?string $msg = null;
    private ?string $nc_subject;
    private ?string $nc_content;
    private mysqli $db;
    
    /**
     * المُنشئ
     */
    public function __construct(?string $nc_subject, ?string $nc_content, ?mysqli $databaseConnection = null) {
        global $con;
        
        $this->nc_subject = $nc_subject;
        $this->nc_content = $nc_content;
        $this->db = $databaseConnection ?? $con;
        
        $_SESSION['nc_subject'] = $this->nc_subject;
        $_SESSION['nc_content'] = $this->nc_content;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $this->msg = null;
        
        if (empty($this->nc_subject)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال عنوان النشرة</div>';
            return false;
        }
        
        if (empty($this->nc_content)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال محتوى النشرة</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * معالجة الصور المضمنة في المحتوى
     */
    private function processInlineImages(): string {
        $content = $this->nc_content;
        $dir = dirname(__FILE__) . '/../images/reply/';
        
        // التأكد من وجود المجلد
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // البحث عن صور base64
        preg_match_all('/src="data:image\/([^;]+);base64,([^"]+)"/', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $imageType = $match[1];
            $base64Data = $match[2];
            $fullMatch = $match[0];
            
            $extension = $imageType === 'jpeg' ? 'jpg' : $imageType;
            $filename = uniqid() . '.' . $extension;
            $filepath = $dir . $filename;
            
            // فك تشفير وحفظ الصورة
            $imageData = base64_decode(str_replace(' ', '+', $base64Data));
            file_put_contents($filepath, $imageData);
            
            // استبدال الرابط في المحتوى
            $newSrc = 'http://egyptmart.online/images/reply/' . $filename;
            $content = str_replace($fullMatch, 'src="' . $newSrc . '"', $content);
        }
        
        return $content;
    }
    
    /**
     * إضافة وإرسال النشرة
     */
    public function add(): bool {
        if (!$this->valid()) {
            return false;
        }
        
        // معالجة التصنيفات المحددة
        $categoryassigned = isset($_POST['categoryassigned']) && is_array($_POST['categoryassigned']) 
            ? implode(",", $_POST['categoryassigned']) 
            : '';
        
        // معالجة الدول المحددة
        $country = isset($_POST['country']) && is_array($_POST['country']) 
            ? implode(",", $_POST['country']) 
            : '';
        
        // معالجة الشركات المحددة
        $companies = isset($_POST['companies']) && is_array($_POST['companies']) 
            ? implode(",", $_POST['companies']) 
            : '';
        
        // معالجة الصور المضمنة
        $processedContent = $this->processInlineImages();
        
        // حفظ في قاعدة البيانات
        $sql = "INSERT INTO newsletter_content
                SET nc_subject = ?,
                    nc_content = ?,
                    nc_category = ?,
                    nc_country = ?,
                    nc_companies = ?,
                    nc_updated_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في قاعدة البيانات</div>';
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sssss", 
            $this->nc_subject, 
            $processedContent, 
            $categoryassigned, 
            $country, 
            $companies
        );
        
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if (!$success) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> فشل حفظ النشرة</div>';
            return false;
        }
        
        // إرسال البريد الإلكتروني للمستخدمين المستهدفين
        $this->sendEmails($country, $companies, $categoryassigned);
        
        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم إرسال النشرة بنجاح</div>';
        unset($_SESSION['nc_subject'], $_SESSION['nc_content']);
        
        return true;
    }
    
    /**
     * إرسال البريد الإلكتروني
     */
    private function sendEmails(string $country, string $companies, string $categoryassigned): void {
        // بناء شرط الاستعلام
        $and = "";
        
        if (!empty($country)) {
            $and .= " AND country IN ($country)";
        }
        
        if (!empty($companies)) {
            $and .= " AND usr_id IN ($companies)";
        }
        
        if (!empty($categoryassigned)) {
            $and .= $this->buildCategoryCondition($categoryassigned);
        }
        
        // جلب المستخدمين المستهدفين
        $sql_usr = "SELECT * FROM user WHERE status='1' $and";
        $res_usr = mysqli_query($this->db, $sql_usr);
        
        if (!$res_usr) {
            error_log('خطأ في جلب المستخدمين: ' . mysqli_error($this->db));
            return;
        }
        
        $from_name = get_page_settings(4) ?: 'الموقع';
        $from_email = get_adminemail() ?: 'admin@example.com';
        
        while ($row_usr = mysqli_fetch_object($res_usr)) {
            // تضمين قالب البريد الإلكتروني
            $message1 = '';
            $messageFilePath = __DIR__ . "/email/newsletter-send.php";
            
            if (file_exists($messageFilePath)) {
                ob_start();
                include $messageFilePath;
                $message1 = ob_get_clean();
            }
            
            if (!empty($row_usr->email)) {
                sendSMTPMail($row_usr->email, $this->nc_subject, $message1);
            }
        }
    }
    
    /**
     * بناء شرط التصنيفات
     */
    private function buildCategoryCondition(string $categoryassigned): string {
        // الحصول على التصنيفات الرئيسية
        $sql12 = "SELECT CONCAT( GROUP_CONCAT(p1.pc_id), ',', GROUP_CONCAT(DISTINCT p2.pc_id) , ',', GROUP_CONCAT(DISTINCT p3.pc_id) ) as Grandparentname
                  FROM product_category p1
                  LEFT JOIN product_category p2 ON p1.pc_parent_id = p2.pc_id
                  LEFT JOIN product_category p3 ON p2.pc_parent_id = p3.pc_id 
                  WHERE p3.pc_id IN ($categoryassigned)";
        
        $res_main_category = mysqli_query($this->db, $sql12);
        $allCategories = $categoryassigned;
        
        if ($res_main_category) {
            while ($row_cat1 = mysqli_fetch_object($res_main_category)) {
                if (!empty($row_cat1->Grandparentname)) {
                    $allCategories = $row_cat1->Grandparentname;
                }
            }
        }
        
        return " AND (
            usr_id IN (SELECT DISTINCT sac_usr_id FROM selloffer_alert_category WHERE sac_pc_id IN ($allCategories) AND sac_status = 1)
            OR usr_id IN (SELECT DISTINCT so_usr_id FROM sale_offer WHERE so_pc_id IN ($allCategories) AND so_status = 1)
            OR usr_id IN (SELECT DISTINCT tac_usr_id FROM tender_alert_category WHERE tac_pc_id IN ($allCategories) AND tac_status = 1)
            OR usr_id IN (SELECT DISTINCT aac_usr_id FROM auction_alert_category WHERE aac_pc_id IN ($allCategories) AND aac_status = 1)
            OR usr_id IN (SELECT DISTINCT bac_usr_id FROM buylead_alert_category WHERE bac_pc_id IN ($allCategories) AND bac_status = 1)
        )";
    }
    
    /**
     * الحصول على الرسالة
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// جلب بيانات التصنيفات
$cat_array = [];
$sql_main = "SELECT * FROM product_category WHERE pc_parent_id = '0' ORDER BY pc_id ASC";
$res_main = mysqli_query($con, $sql_main);
while ($row_cat = mysqli_fetch_assoc($res_main)) {
    $cat_array[] = ['id' => $row_cat['pc_id'], 'name' => $row_cat['pc_name']];
}

// جلب بيانات الدول
$country_array = [];
$sql_country = "SELECT * FROM country ORDER BY cn_name ASC";
$res_country = mysqli_query($con, $sql_country);
while ($row_country = mysqli_fetch_assoc($res_country)) {
    $country_array[] = ['id' => $row_country['cn_id'], 'name' => $row_country['cn_name']];
}

// جلب بيانات الشركات
$company_array = [];
$sql_company = "SELECT * FROM business_profile WHERE bnsprof_compname != '' ORDER BY bnsprof_compname ASC";
$res_company = mysqli_query($con, $sql_company);
while ($row_company = mysqli_fetch_assoc($res_company)) {
    $company_array[] = ['id' => $row_company['bnsprof_uid'], 'name' => $row_company['bnsprof_compname']];
}

// جلب بيانات النشرة للتعديل (إذا وجدت)
$row_nc = null;
if (isset($_GET['fid']) && is_numeric($_GET['fid'])) {
    $fid = (int)$_GET['fid'];
    $sql_nc = "SELECT * FROM newsletter_content WHERE nc_id = $fid";
    $res_nc = mysqli_query($con, $sql_nc);
    $row_nc = mysqli_fetch_object($res_nc);
}

// معالجة النموذج
if (isset($_POST['btnAdd'])) {
    $adn = new AddPlan(
        trim($_POST['nc_subject'] ?? ''),
        trim($_POST['nc_content'] ?? '')
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    $_SESSION['msg'] = $adn->getMessage();
    header("Location: newsletter-view.php");
    exit();
}

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<?php include "includes/admin-top.php" ?>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>

        <script type="text/javascript">
        function myvalid() {
            var nc_subject = document.getElementById('nc_subject');
            var nc_content = CKEDITOR.instances.nc_content.getData();

            var message = "";
            var valid = true;

            if (!nc_subject.value || nc_subject.value.trim() === '') {
                message = 'الرجاء إدخال عنوان النشرة';
                nc_subject.focus();
                valid = false;
            }
            else if (!nc_content || nc_content.trim() === '') {
                message = 'الرجاء إدخال محتوى النشرة';
                valid = false;
            }

            if (!valid) {
                document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                document.getElementById('msg').className = "alert alert-danger";
            }

            return valid;
        }
        </script>

        <?php include "includes/admin-left-con.php" ?>

        <div class="main-content">
            <div class="breadcrumbs" id="breadcrumbs">
                <script type="text/javascript">
                    try{ace.settings.check('breadcrumbs' , 'fixed')}catch(e){}
                </script>

                <ul class="breadcrumb">
                    <li>
                        <i class="icon-home home-icon"></i>
                        <a href="welcome.php">الرئيسية</a>
                    </li>
                    <li>
                        <a href="newsletter-view.php">إدارة النشرات</a>
                    </li>
                    <li class="active">إرسال نشرة جديدة</li>
                </ul>
            </div>

            <div class="page-content">
                <div class="page-header">
                    <h1>
                        إدارة النشرات
                        <small>
                            <i class="icon-double-angle-right"></i>
                            إرسال نشرة جديدة
                        </small>
                    </h1>
                </div>

                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" 
                              method="post" enctype="multipart/form-data" onsubmit="return myvalid();">

                            <em style="display:block;margin:5px;">
                                الحقول التي تحمل علامة <span style="color:#F00">*</span> مطلوبة
                            </em>

                            <div id="msg"><?php echo $msg; ?></div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="nc_subject">
                                    العنوان <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="nc_subject" 
                                           id="nc_subject" 
                                           class="col-xs-10 col-sm-8" 
                                           type="text" 
                                           value="<?php echo htmlspecialchars($nc_subject ?: ($row_nc->nc_subject ?? '')); ?>"
                                           maxlength="255" />
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="categoryassigned">
                                    التصنيفات
                                </label>
                                <div class="col-sm-9">
                                    <select class="col-xs-10 col-sm-8 chosen-select" 
                                            name="categoryassigned[]" 
                                            multiple="multiple"
                                            data-placeholder="اختر التصنيفات...">
                                        <?php foreach ($cat_array as $cat): ?>
                                            <option value="<?php echo $cat['id']; ?>">
                                                <?php echo htmlspecialchars($cat['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="country">
                                    الدول
                                </label>
                                <div class="col-sm-9">
                                    <select class="col-xs-10 col-sm-8 chosen-select" 
                                            name="country[]" 
                                            multiple="multiple"
                                            data-placeholder="اختر الدول...">
                                        <?php foreach ($country_array as $country): ?>
                                            <option value="<?php echo $country['id']; ?>">
                                                <?php echo htmlspecialchars($country['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="companies">
                                    الشركات
                                </label>
                                <div class="col-sm-9">
                                    <select class="selectpicker col-xs-10 col-sm-8" 
                                            name="companies[]" 
                                            multiple
                                            data-live-search="true"
                                            data-width="100%">
                                        <?php foreach ($company_array as $company): ?>
                                            <option value="<?php echo $company['id']; ?>">
                                                <?php echo htmlspecialchars($company['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="nc_content">
                                    المحتوى <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="nc_content" id="nc_content">
                                        <?php 
                                        $content = $nc_content ?: ($row_nc->nc_content ?? '');
                                        echo htmlspecialchars(stripslashes($content));
                                        ?>
                                    </textarea>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd">
                                        <i class="icon-ok bigger-110"></i>
                                        إرسال
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i>
                                        إعادة تعيين
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <br clear="all" />
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<script type="text/javascript">
window.jQuery || document.write("<script src='assets/js/jquery-2.0.3.min.js'>"+"<"+"/script>");
</script>

<!--[if IE]>
<script type="text/javascript">
window.jQuery || document.write("<script src='assets/js/jquery-1.10.2.min.js'>"+"<"+"/script>");
</script>
<![endif]-->

<script type="text/javascript">
if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/typeahead-bs2.min.js"></script>
<script src="assets/js/jquery-ui-1.10.3.custom.min.js"></script>
<script src="assets/js/jquery.ui.touch-punch.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/fuelux/fuelux.spinner.min.js"></script>
<script src="assets/js/date-time/bootstrap-datepicker.min.js"></script>
<script src="assets/js/date-time/bootstrap-timepicker.min.js"></script>
<script src="assets/js/date-time/moment.min.js"></script>
<script src="assets/js/date-time/daterangepicker.min.js"></script>
<script src="assets/js/bootstrap-colorpicker.min.js"></script>
<script src="assets/js/jquery.knob.min.js"></script>
<script src="assets/js/jquery.autosize.min.js"></script>
<script src="assets/js/jquery.inputlimiter.1.3.1.min.js"></script>
<script src="assets/js/jquery.maskedinput.min.js"></script>
<script src="assets/js/bootstrap-tag.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>
<script src="ckeditor/ckeditor.js"></script>
<script src="assets/js/markdown/markdown.min.js"></script>
<script src="assets/js/markdown/bootstrap-markdown.min.js"></script>
<script src="assets/js/jquery.hotkeys.min.js"></script>
<script src="assets/js/bootstrap-wysiwyg.min.js"></script>
<script src="assets/js/bootbox.min.js"></script>

<!-- Bootstrap Select -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/css/bootstrap-select.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/bootstrap-select.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    // تفعيل Chosen
    $(".chosen-select").chosen({
        no_results_text: "لا توجد نتائج",
        placeholder_text_multiple: "اختر الخيارات...",
        width: "100%"
    });
    
    // تفعيل Bootstrap Select
    $('#companies').selectpicker({
        noneSelectedText: 'اختر الشركات...',
        noneResultsText: 'لا توجد نتائج',
        countSelectedText: '{0} شركة محددة',
        maxOptionsText: ['الحد الأقصى', 'الحد الأقصى'],
        selectAllText: 'اختر الكل',
        deselectAllText: 'إلغاء الكل',
        doneButtonText: 'تم',
        liveSearch: true,
        liveSearchPlaceholder: 'بحث...'
    });
    
    // تفعيل CKEditor
    CKEDITOR.replace('nc_content', {
        extraPlugins: 'imageuploader',
        language: 'ar',
        height: 400,
        toolbarGroups: [
            { name: 'document', groups: ['mode', 'document', 'doctools'] },
            { name: 'clipboard', groups: ['clipboard', 'undo'] },
            { name: 'editing', groups: ['find', 'selection', 'spellchecker'] },
            { name: 'forms' },
            '/',
            { name: 'basicstyles', groups: ['basicstyles', 'cleanup'] },
            { name: 'paragraph', groups: ['list', 'indent', 'blocks', 'align', 'bidi'] },
            { name: 'links' },
            { name: 'insert' },
            '/',
            { name: 'styles' },
            { name: 'colors' },
            { name: 'tools' },
            { name: 'others' }
        ]
    });
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
});
</script>

</body>
</html>