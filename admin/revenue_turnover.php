<?php
/**
 * ملف: revenue_turnover.php

 * الإصدار: 2.0.0
 * توافق PHP: 8.3
 * 
 * الوصف: إضافة فيديو جديد لسلايدر الفيديو - إدارة الإعلانات المرئية
 * Add new video to video slider - Visual advertisement management
 * 
 * المميزات:
 * - إضافة روابط فيديو جديدة
 * - تحديد الدول المستهدفة (متعددة)
 * - إضافة عنوان ووصف لكل فيديو
 * - رابط إعادة التوجيه
 * - التحقق من صحة المدخلات
 */

declare(strict_types=1);

// بدء تشغيل المخزن المؤقت والجلسة
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من تسجيل دخول المستخدم
check_admin_login();

/**
 * كلاس إدارة إعلانات الفيديو
 */
class VideoAdvertisementManager {
    
    /** @var mysqli اتصال قاعدة البيانات */
    private mysqli $db;
    
    /** @var string رسالة النجاح/الخطأ */
    private string $msg = '';
    
    /** @var array بيانات النموذج */
    private array $formData = [];
    
    /** @var array رسائل الخطأ */
    private array $errors = [];
    
    /** @var array رسائل النجاح */
    private array $success = [];
    
    /** @var string اسم الجدول */
    private string $table = 'video_slider';
    
    /**
     * المُنشئ
     * 
     * @param mysqli $database اتصال قاعدة البيانات
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * تعيين بيانات النموذج
     * 
     * @param array $data بيانات النموذج
     */
    public function setFormData(array $data): void {
        $this->formData = [
            'adv_link' => $this->sanitizeUrl($data['adv_link'] ?? ''),
            'adv_redirect' => $this->sanitizeUrl($data['adv_redirect'] ?? ''),
            'adv_title' => $this->sanitize($data['adv_title'] ?? ''),
            'adv_description' => $this->sanitizeTextarea($data['adv_description'] ?? ''),
            'adv_country' => $this->validateCountries($data['adv_country'] ?? []),
            'adv_global' => isset($data['adv_global']) ? (int)$data['adv_global'] : 0
        ];
        
        // حفظ في الجلسة للاحتفاظ بالقيم عند الخطأ
        $_SESSION['adv_link'] = $this->formData['adv_link'];
        $_SESSION['adv_redirect'] = $this->formData['adv_redirect'];
        $_SESSION['adv_title'] = $this->formData['adv_title'];
        $_SESSION['adv_description'] = $this->formData['adv_description'];
    }
    
    /**
     * تنقية النص
     * 
     * @param string $str النص المدخل
     * @return string النص المنقى
     */
    private function sanitize(string $str): string {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * تنقية نص طويل (textarea)
     * 
     * @param string $str النص المدخل
     * @return string النص المنقى
     */
    private function sanitizeTextarea(string $str): string {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * تنقية رابط URL
     * 
     * @param string $url الرابط المدخل
     * @return string الرابط المنقى
     */
    private function sanitizeUrl(string $url): string {
        $url = trim($url);
        if (empty($url)) {
            return '';
        }
        
        // إضافة https:// إذا لم يكن موجوداً
        if (!preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }
        
        return filter_var($url, FILTER_SANITIZE_URL);
    }
    
    /**
     * التحقق من صحة الروابط
     * 
     * @param string $url الرابط
     * @return bool صحيح إذا كان الرابط صالحاً
     */
    private function isValidUrl(string $url): bool {
        if (empty($url)) {
            return true; // الحقول غير مطلوبة
        }
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * التحقق من صحة الدول المختارة
     * 
     * @param array $countries مصفوفة معرفات الدول
     * @return string الدول كسلسلة نصية مفصولة بفواصل
     */
    private function validateCountries(array $countries): string {
        $validCountries = [];
        foreach ($countries as $country) {
            $id = filter_var($country, FILTER_VALIDATE_INT);
            if ($id && $id > 0) {
                $validCountries[] = $id;
            }
        }
        return implode(',', $validCountries);
    }
    
    /**
     * التحقق من صحة البيانات
     * 
     * @return bool صحيح إذا كانت البيانات صحيحة
     */
    public function validate(): bool {
        $this->errors = [];
        
        if (empty($this->formData['adv_link'])) {
            $this->errors[] = 'الرجاء إدخال رابط الفيديو';
        } elseif (!$this->isValidUrl($this->formData['adv_link'])) {
            $this->errors[] = 'رابط الفيديو غير صحيح';
        }
        
        if (!empty($this->formData['adv_redirect']) && !$this->isValidUrl($this->formData['adv_redirect'])) {
            $this->errors[] = 'رابط إعادة التوجيه غير صحيح';
        }
        
        if (empty($this->formData['adv_title'])) {
            $this->errors[] = 'الرجاء إدخال عنوان الفيديو';
        } elseif (strlen($this->formData['adv_title']) > 255) {
            $this->errors[] = 'عنوان الفيديو طويل جداً (الحد الأقصى 255 حرف)';
        }
        
        if (empty($this->formData['adv_description'])) {
            $this->errors[] = 'الرجاء إدخال وصف الفيديو';
        }
        
        return empty($this->errors);
    }
    
    /**
     * إضافة إعلان فيديو جديد
     * 
     * @return bool حالة النجاح
     */
    public function add(): bool {
        if (!$this->validate()) {
            return false;
        }
        
        $sql = "INSERT INTO {$this->table} 
                (adv_link, adv_redirect, adv_title, adv_description, adv_country, adv_global, adv_updated_date, adv_status) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "خطأ في قاعدة البيانات: " . mysqli_error($this->db);
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sssssi", 
            $this->formData['adv_link'],
            $this->formData['adv_redirect'],
            $this->formData['adv_title'],
            $this->formData['adv_description'],
            $this->formData['adv_country'],
            $this->formData['adv_global']
        );
        
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            $newId = mysqli_insert_id($this->db);
            $this->success[] = "تم إضافة الفيديو بنجاح";
            $this->logActivity('add', $newId);
            
            // مسح بيانات الجلسة
            unset($_SESSION['adv_link']);
            unset($_SESSION['adv_redirect']);
            unset($_SESSION['adv_title']);
            unset($_SESSION['adv_description']);
        } else {
            $this->errors[] = "فشل في إضافة الفيديو: " . mysqli_error($this->db);
        }
        
        mysqli_stmt_close($stmt);
        return $result;
    }
    
    /**
     * تسجيل النشاط
     * 
     * @param string $action الإجراء
     * @param int $itemId معرف العنصر
     */
    private function logActivity(string $action, int $itemId): void {
        $userId = $_SESSION['admin_id'] ?? 0;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $sql = "INSERT INTO activity_log (user_id, action, item_type, item_id, item_title, ip_address, created_at) 
                VALUES (?, ?, 'video_slider', ?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isiss", $userId, $action, $itemId, $this->formData['adv_title'], $ipAddress);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * الحصول على جميع الدول النشطة
     * 
     * @return mysqli_result|false نتيجة الاستعلام
     */
    public function getActiveCountries() {
        $sql = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name ASC";
        return mysqli_query($this->db, $sql);
    }
    
    /**
     * الحصول على رسائل الخطأ
     * 
     * @return array رسائل الخطأ
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * الحصول على رسائل النجاح
     * 
     * @return array رسائل النجاح
     */
    public function getSuccess(): array {
        return $this->success;
    }
    
    /**
     * الحصول على رسالة
     * 
     * @return string الرسالة
     */
    public function getMessage(): string {
        return $this->msg;
    }
    
    /**
     * مسح الرسائل
     */
    public function clearMessages(): void {
        $this->errors = [];
        $this->success = [];
        $this->msg = '';
    }
}

// تهيئة المدير
$manager = new VideoAdvertisementManager($con);

// استرجاع القيم من الجلسة
$adv_link = $_SESSION['adv_link'] ?? '';
$adv_redirect = $_SESSION['adv_redirect'] ?? '';
$adv_title = $_SESSION['adv_title'] ?? '';
$adv_description = $_SESSION['adv_description'] ?? '';

// معالجة النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    
    $manager->setFormData($_POST);
    
    if ($manager->add()) {
        $_SESSION['video_success'] = $manager->getSuccess();
    } else {
        $_SESSION['video_errors'] = $manager->getErrors();
    }
    
    header("Location: videoslider-add.php");
    exit();
}

// الحصول على الدول النشطة
$countries = $manager->getActiveCountries();

// الحصول على الرسائل من الجلسة
$errorMessages = $_SESSION['video_errors'] ?? [];
$successMessages = $_SESSION['video_success'] ?? [];
unset($_SESSION['video_errors'], $_SESSION['video_success']);
?>

<?php include "includes/admin-top.php" ?>

<style>
    /* تنسيقات مخصصة */
    .form-container {
        max-width: 900px;
        margin: 0 auto;
    }
    .form-section {
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .form-section h3 {
        margin-top: 0;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #4CAF50;
        color: #333;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }
    .form-group .required {
        color: #f00;
        margin-right: 3px;
    }
    .form-control {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-sizing: border-box;
        transition: all 0.3s;
    }
    .form-control:focus {
        border-color: #4CAF50;
        outline: none;
        box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
    }
    .form-control.error {
        border-color: #f00;
    }
    .form-control.valid {
        border-color: #4CAF50;
    }
    textarea.form-control {
        resize: vertical;
        min-height: 150px;
    }
    .help-text {
        font-size: 12px;
        color: #666;
        margin-top: 5px;
    }
    .alert {
        padding: 12px 20px;
        margin: 15px 0;
        border-radius: 4px;
    }
    .alert-success {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }
    .alert-danger {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }
    .alert-warning {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }
    .btn-submit {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }
    .btn-submit:hover {
        background-color: #45a049;
    }
    .btn-reset {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
        margin-right: 10px;
    }
    .btn-reset:hover {
        background-color: #5a6268;
    }
    .chosen-container {
        width: 100% !important;
    }
    .chosen-choices {
        border-radius: 4px !important;
        border-color: #ddd !important;
    }
    .input-group {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .input-group .form-control {
        flex: 1;
    }
    .input-group .btn {
        padding: 8px 20px;
        white-space: nowrap;
    }
    .url-preview {
        margin-top: 5px;
        font-size: 12px;
    }
    .url-preview.valid {
        color: #4CAF50;
    }
    .url-preview.invalid {
        color: #f00;
    }
</style>

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
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
                        <a href="videoslider-view.php">سلايدر الفيديو</a>
                    </li>
                    <li class="active">إضافة فيديو جديد</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="icon-film"></i> إضافة فيديو جديد
                        <small>
                            <i class="icon-double-angle-right"></i>
                            أضف فيديو إعلاني جديد
                        </small>
                    </h1>
                </div>
                
                <!-- عرض رسائل النظام -->
                <?php if (!empty($errorMessages)): ?>
                    <div class="alert alert-danger">
                        <i class="icon-remove"></i>
                        <ul style="margin:5px 0 0 20px;">
                            <?php foreach ($errorMessages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($successMessages)): ?>
                    <div class="alert alert-success">
                        <i class="icon-ok"></i>
                        <ul style="margin:5px 0 0 20px;">
                            <?php foreach ($successMessages as $msg): ?>
                                <li><?php echo htmlspecialchars($msg); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div id="msg"></div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" id="video-form">
                            
                            <!-- اختيار الدول -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    الدول المستهدفة:
                                </label>
                                <div class="col-sm-8">
                                    <?php if ($countries && mysqli_num_rows($countries) > 0): ?>
                                        <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select">
                                            <?php while ($country = mysqli_fetch_object($countries)): ?>
                                                <option value="<?php echo (int)$country->cn_id; ?>">
                                                    <?php echo htmlspecialchars($country->cn_name); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    <?php else: ?>
                                        <div class="alert alert-warning">
                                            <i class="icon-warning"></i> لا توجد دول نشطة متاحة
                                        </div>
                                    <?php endif; ?>
                                    <span class="help-text">
                                        <i class="icon-info-sign"></i> يمكنك اختيار أكثر من دولة (اختياري)
                                    </span>
                                </div>
                            </div>
                            
                            <input type="hidden" name="adv_global" value="0">
                            
                            <!-- رابط الفيديو -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    رابط الفيديو <span class="required">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="url" name="adv_link" id="adv_link" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($adv_link); ?>" 
                                               placeholder="https://www.youtube.com/watch?v=..."
                                               required>
                                    </div>
                                    <span class="help-text">
                                        <i class="icon-info-sign"></i> رابط الفيديو من يوتيوب أو فيميو
                                    </span>
                                    <div id="link-preview" class="url-preview"></div>
                                </div>
                            </div>
                            
                            <!-- رابط إعادة التوجيه -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    رابط إعادة التوجيه:
                                </label>
                                <div class="col-sm-9">
                                    <div class="input-group">
                                        <input type="url" name="adv_redirect" id="adv_redirect" 
                                               class="form-control" 
                                               value="<?php echo htmlspecialchars($adv_redirect); ?>" 
                                               placeholder="https://example.com">
                                    </div>
                                    <span class="help-text">
                                        <i class="icon-info-sign"></i> الرابط الذي سينتقل إليه المستخدم عند النقر على الفيديو (اختياري)
                                    </span>
                                    <div id="redirect-preview" class="url-preview"></div>
                                </div>
                            </div>
                            
                            <!-- عنوان الفيديو -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    عنوان الفيديو <span class="required">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="adv_title" id="adv_title" 
                                           class="form-control" 
                                           value="<?php echo htmlspecialchars($adv_title); ?>" 
                                           maxlength="255"
                                           placeholder="أدخل عنوان الفيديو"
                                           required>
                                    <span class="help-text">
                                        <i class="icon-info-sign"></i> عنوان واضح للفيديو (الحد الأقصى 255 حرف)
                                    </span>
                                </div>
                            </div>
                            
                            <!-- وصف الفيديو -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    وصف الفيديو <span class="required">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <textarea name="adv_description" id="adv_description" 
                                              class="form-control" 
                                              rows="6"
                                              placeholder="أدخل وصفاً تفصيلياً للفيديو"
                                              required><?php echo htmlspecialchars($adv_description); ?></textarea>
                                    <span class="help-text">
                                        <i class="icon-info-sign"></i> وصف مختصر لمحتوى الفيديو
                                    </span>
                                </div>
                            </div>
                            
                            <!-- أزرار التحكم -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn-submit" type="submit" name="btnAdd">
                                        <i class="icon-plus"></i> إضافة
                                    </button>
                                    <button class="btn-reset" type="reset">
                                        <i class="icon-undo"></i> إعادة تعيين
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
    if("ontouchend" in document) document.write("<script src='assets/js/jquery.mobile.custom.min.js'>"+"<"+"/script>");
</script>

<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/chosen.jquery.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<script type="text/javascript">
jQuery(function($) {
    
    // تفعيل Chosen للقوائم المتعددة
    $(".chosen-select").chosen({
        allow_single_deselect: true,
        disable_search_threshold: 10,
        placeholder_text_multiple: "اختر الدول المستهدفة...",
        no_results_text: "لا توجد نتائج"
    });
    
    // التحقق المباشر من الروابط
    function validateUrl(url, element) {
        if (!url) return true;
        
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }
    
    // معاينة رابط الفيديو
    $('#adv_link').on('input', function() {
        var url = $(this).val();
        var $preview = $('#link-preview');
        
        if (url) {
            if (validateUrl(url)) {
                $preview.html('<i class="icon-ok"></i> رابط صحيح').addClass('valid').removeClass('invalid');
                $(this).removeClass('error').addClass('valid');
            } else {
                $preview.html('<i class="icon-remove"></i> رابط غير صحيح').addClass('invalid').removeClass('valid');
                $(this).removeClass('valid').addClass('error');
            }
        } else {
            $preview.empty();
            $(this).removeClass('error valid');
        }
    });
    
    // معاينة رابط إعادة التوجيه
    $('#adv_redirect').on('input', function() {
        var url = $(this).val();
        var $preview = $('#redirect-preview');
        
        if (url) {
            if (validateUrl(url)) {
                $preview.html('<i class="icon-ok"></i> رابط صحيح').addClass('valid').removeClass('invalid');
                $(this).removeClass('error').addClass('valid');
            } else {
                $preview.html('<i class="icon-remove"></i> رابط غير صحيح').addClass('invalid').removeClass('valid');
                $(this).removeClass('valid').addClass('error');
            }
        } else {
            $preview.empty();
            $(this).removeClass('error valid');
        }
    });
    
    // التحقق من العنوان
    $('#adv_title').on('input', function() {
        var value = $(this).val().trim();
        if (value.length === 0) {
            $(this).removeClass('valid').addClass('error');
        } else if (value.length > 255) {
            $(this).removeClass('valid').addClass('error');
        } else {
            $(this).removeClass('error').addClass('valid');
        }
    });
    
    // التحقق من الوصف
    $('#adv_description').on('input', function() {
        var value = $(this).val().trim();
        if (value.length === 0) {
            $(this).removeClass('valid').addClass('error');
        } else {
            $(this).removeClass('error').addClass('valid');
        }
    });
    
    // التحقق من النموذج قبل الإرسال
    $('#video-form').on('submit', function(e) {
        var adv_link = $('#adv_link').val().trim();
        var adv_title = $('#adv_title').val().trim();
        var adv_description = $('#adv_description').val().trim();
        
        var errors = [];
        
        if (!adv_link) {
            errors.push('الرجاء إدخال رابط الفيديو');
        } else if (!validateUrl(adv_link)) {
            errors.push('رابط الفيديو غير صحيح');
        }
        
        if (!adv_title) {
            errors.push('الرجاء إدخال عنوان الفيديو');
        }
        
        if (!adv_description) {
            errors.push('الرجاء إدخال وصف الفيديو');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            var errorHtml = '<div class="alert alert-danger"><i class="icon-remove"></i><ul style="margin:5px 0 0 20px;">';
            $.each(errors, function(i, msg) {
                errorHtml += '<li>' + msg + '</li>';
            });
            errorHtml += '</ul></div>';
            $('#msg').html(errorHtml);
            
            // تمرير للرسالة
            $('html, body').animate({
                scrollTop: $('#msg').offset().top - 50
            }, 500);
            
            return false;
        }
        
        return confirm('هل أنت متأكد من إضافة هذا الفيديو؟');
    });
    
    // تفعيل tooltips
    $('[data-rel="tooltip"]').tooltip({placement: function(context, source) {
        var $source = $(source);
        var $parent = $source.closest('.form-group');
        var off1 = $parent.offset();
        var w1 = $parent.width();
        var off2 = $source.offset();
        var w2 = $source.width();
        
        if (parseInt(off2.left) < parseInt(off1.left) + parseInt(w1 / 2)) {
            return 'right';
        }
        return 'left';
    }});
    
    // تركيز على أول حقل
    $('#adv_link').focus();
    
});
</script>

<!-- إنشاء جدول سجل النشاطات إذا لم يكن موجوداً -->
<?php
$checkTable = mysqli_query($con, "SHOW TABLES LIKE 'activity_log'");
if (mysqli_num_rows($checkTable) == 0) {
    $createTable = "CREATE TABLE IF NOT EXISTS `activity_log` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(50) NOT NULL,
        `item_type` varchar(50) NOT NULL,
        `item_id` int(11) DEFAULT NULL,
        `item_title` varchar(255) DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        KEY `user_id` (`user_id`),
        KEY `item_type` (`item_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $createTable);
}
?>

<!-- نهاية ملف videoslider-add.php - الإصدار 2.0.0 -->
</body>
</html>

<?php ob_end_flush(); ?>