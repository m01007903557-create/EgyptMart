<?php
/**
 * File: maincat-add.php
 * Version: 2.0.0
 * Description: إضافة تصنيف رئيسي جديد مع صورة وبانر (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

// بدء تشغيل output buffering
ob_start();

// بدء الجلسة إذا لم تكن قد بدأت بالفعل
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// تضمين الملفات المطلوبة
require_once "../common.php";
require_once "../lib/SimpleImage.php";

// التحقق من تسجيل الدخول
check_user_login();

/**
 * Class AddProduct - إضافة تصنيف رئيسي جديد
 * متوافق مع PHP 8.3
 */
class AddProduct {
    private ?string $msg = null;
    private string $pc_name;
    private ?string $ci_image;
    private ?string $ci_banner;
    private mysqli $db;
    
    // الثوابت
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const IMAGE_SIZE = 70;
    private const BANNER_WIDTH = 1000;
    private const BANNER_HEIGHT = 450;
    
    /**
     * المُنشئ
     */
    public function __construct(string $pc_name, ?string $ci_image, ?string $ci_banner, ?mysqli $databaseConnection = null) {
        global $con;
        
        $this->pc_name = $pc_name;
        $this->ci_image = $ci_image;
        $this->ci_banner = $ci_banner;
        $this->db = $databaseConnection ?? $con;
        
        $_SESSION['pc_name'] = $this->pc_name;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        $this->msg = null;
        
        if (empty($this->pc_name)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء إدخال اسم التصنيف.</div>';
            return false;
        }
        
        if (empty($this->ci_image)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء اختيار صورة التصنيف.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * التحقق من صحة الملف المرفوع
     */
    private function validateFile(array $file, string $type): bool {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في رفع الملف.</div>';
            return false;
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> حجم الملف يجب أن يكون أقل من 5 ميجابايت.</div>';
            return false;
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الامتداد غير مسموح به. الأنواع المسموحة: jpg, jpeg, png, gif</div>';
            return false;
        }
        
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الملف ليس صورة صالحة.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * إنشاء اسم ملف آمن
     */
    private function generateSecureFilename(string $originalName, string $prefix): string {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $random = bin2hex(random_bytes(8));
        $timestamp = time();
        
        return sprintf('%s-%s-%s.%s', $prefix, $timestamp, $random, $ext);
    }
    
    /**
     * معالجة وحفظ الصورة
     */
    private function processImage(array $file, string $prefix, int $width, ?int $height = null): ?string {
        if (!$this->validateFile($file, $prefix)) {
            return null;
        }
        
        try {
            $filename = $this->generateSecureFilename($file['name'], $prefix);
            
            $image = new SimpleImage();
            $image->load($file['tmp_name']);
            
            if ($height) {
                $image->resize($width, $height);
            } else {
                $image->resize($width, $width);
            }
            
            $uploadPath = __DIR__ . '/../upload/' . ($prefix === 'BAN' ? 'bannerimage/' : 'category/') . $filename;
            $image->save($uploadPath);
            
            return $filename;
            
        } catch (Exception $e) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في معالجة الصورة: ' . $e->getMessage() . '</div>';
            return null;
        }
    }
    
    /**
     * إضافة التصنيف إلى قاعدة البيانات
     */
    public function add(): bool {
        if (!$this->valid()) {
            return false;
        }
        
        // معالجة صورة التصنيف
        $imageFile = $_FILES['ci_image'] ?? null;
        if (!$imageFile || $imageFile['error'] === UPLOAD_ERR_NO_FILE) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الرجاء اختيار صورة التصنيف.</div>';
            return false;
        }
        
        $image = $this->processImage($imageFile, 'CAT', self::IMAGE_SIZE);
        if (!$image) {
            return false;
        }
        
        // معالجة صورة البانر (اختياري)
        $banner = null;
        if (isset($_FILES['ci_banner']) && $_FILES['ci_banner']['error'] === UPLOAD_ERR_OK) {
            $banner = $this->processImage($_FILES['ci_banner'], 'BAN', self::BANNER_WIDTH, self::BANNER_HEIGHT);
        }
        
        // إدخال البيانات في قاعدة البيانات
        $sql = "INSERT INTO product_category (pc_name, pc_image, pc_banner, pc_parent_id, pc_status) 
                VALUES (?, ?, ?, '0', '1')";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في قاعدة البيانات.</div>';
            return false;
        }
        
        $pc_name = mysqli_real_escape_string($this->db, $this->pc_name);
        mysqli_stmt_bind_param($stmt, "sss", $pc_name, $image, $banner);
        
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم إضافة التصنيف بنجاح.</div>';
            unset($_SESSION['pc_name']);
            return true;
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> فشل في إضافة التصنيف.</div>';
            return false;
        }
    }
    
    /**
     * الحصول على رسالة الخطأ
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// معالجة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$pc_name = $_SESSION['pc_name'] ?? '';
unset($_SESSION['pc_name']);

// معالجة النموذج
if (isset($_POST['btnAdd'])) {
    $adn = new AddProduct(
        trim($_POST['pc_name'] ?? ''),
        $_FILES['ci_image']['name'] ?? null,
        $_FILES['ci_banner']['name'] ?? null
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    $_SESSION['msg'] = $adn->getMessage();
    header("Location: maincat-add.php");
    exit();
}
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
            var pc_name = document.getElementById('pc_name');
            var ci_image = document.getElementById('ci_image');
            var message = "";
            var valid = true;
            
            if (!pc_name.value || pc_name.value.trim() === '') {
                message = 'الرجاء إدخال اسم التصنيف';
                pc_name.focus();
                valid = false;
            }
            else if (!ci_image.value || ci_image.value.trim() === '') {
                message = 'الرجاء اختيار صورة التصنيف';
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
                        <a href="maincat-view.php">إدارة التصنيفات</a>
                    </li>
                    <li class="active">إضافة تصنيف</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        إدارة التصنيفات
                        <small>
                            <i class="icon-double-angle-right"></i>
                            إضافة تصنيف جديد
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
                                <label class="col-sm-3 control-label no-padding-right" for="pc_name">
                                    اسم التصنيف <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input name="pc_name" 
                                           id="pc_name" 
                                           class="col-xs-10 col-sm-5" 
                                           type="text" 
                                           value="<?php echo htmlspecialchars($pc_name); ?>"
                                           maxlength="100" />
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="ci_image">
                                    صورة التصنيف <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="ci_image" id="ci_image" type="file" 
                                               accept="image/jpeg,image/png,image/gif">
                                    </div>
                                    <small class="text-muted">
                                        (الأبعاد المفضلة: 70 × 70 بكسل - الحجم الأقصى: 5 ميجابايت)
                                    </small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="ci_banner">
                                    بانر صفحة التصنيف
                                </label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="ci_banner" id="ci_banner" type="file" 
                                               accept="image/jpeg,image/png,image/gif">
                                    </div>
                                    <small class="text-muted">
                                        (الأبعاد المفضلة: 1000 × 450 بكسل - الحجم الأقصى: 5 ميجابايت)
                                    </small>
                                </div>
                            </div>

                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd" id="btnAdd">
                                        <i class="icon-ok bigger-110"></i>
                                        إضافة
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
            </div>
            <br clear="all" />
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

<script type="text/javascript">
jQuery(function($) {
    // تفعيل اختيار الملفات
    $('#ci_image, #ci_banner').ace_file_input({
        no_file: 'اختر ملف...',
        btn_choose: 'اختر',
        btn_change: 'تغيير',
        droppable: false,
        thumbnail: 'small'
    });
    
    // معاينة الصورة قبل الرفع
    $('#ci_image').on('change', function() {
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                // يمكن إضافة معاينة هنا
            }
            reader.readAsDataURL(input.files[0]);
        }
    });
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
});
</script>

</body>
</html>