<?php
/**
 * ملف تعديل التصنيفات (Categories)
 * 
 * @filename    maincat-edit.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تعديل بيانات التصنيفات الرئيسية
 *              مع إمكانية تغيير الصورة المصغرة وصورة البانر
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// التحقق من صلاحيات المستخدم
check_user_login();

/**
 * كلاس تعديل التصنيفات
 */
class addproduct {
    
    private $msg;
    private $pc_id;
    private $pc_name;
    private $pc_image;
    private $pc_banner;
    private $errors = [];
    
    /**
     * constructor
     * @param string $pc_name اسم التصنيف
     * @param int $pc_id معرف التصنيف
     * @param string $pc_image اسم ملف الصورة
     */
    public function __construct($pc_name, $pc_id, $pc_image) {
        $this->pc_id = $this->validateInt($pc_id);
        $this->pc_name = $this->sanitizeText($pc_name, 100);
        $this->pc_image = $this->sanitizeFileName($pc_image);
        
        // حفظ في الجلسة للاحتفاظ بالقيم عند الخطأ
        $_SESSION['pc_name'] = $this->pc_name;
    }
    
    /**
     * التحقق من صحة الرقم
     */
    private function validateInt($value, $default = 0) {
        $filtered = filter_var($value, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 999999]
        ]);
        return $filtered !== false ? $filtered : $default;
    }
    
    /**
     * تنظيف النص
     */
    private function sanitizeText($value, $maxLength = 255) {
        $clean = trim(strip_tags($value));
        $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
        return substr($clean, 0, $maxLength);
    }
    
    /**
     * تنظيف اسم الملف
     */
    private function sanitizeFileName($filename) {
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $filename);
        return $filename;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid() {
        $this->errors = [];
        
        if (empty($this->pc_name)) {
            $this->errors[] = 'الرجاء إدخال اسم التصنيف';
        }
        
        if ($this->pc_id <= 0) {
            $this->errors[] = 'معرف التصنيف غير صحيح';
        }
        
        return empty($this->errors);
    }
    
    /**
     * التحقق من صحة نوع الملف
     */
    private function validateFileType($file, $fieldName) {
        if (!isset($_FILES[$file]) || $_FILES[$file]['error'] === UPLOAD_ERR_NO_FILE) {
            return true; // لا يوجد ملف جديد، هذا مقبول
        }
        
        if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'الملف أكبر من الحد المسموح به',
                UPLOAD_ERR_FORM_SIZE => 'الملف أكبر من الحد المسموح به في النموذج',
                UPLOAD_ERR_PARTIAL => 'تم رفع جزء فقط من الملف',
                UPLOAD_ERR_NO_TMP_DIR => 'المجلد المؤقت غير موجود',
                UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص',
            ];
            $this->errors[] = $uploadErrors[$_FILES[$file]['error']] ?? 'خطأ غير معروف في الرفع';
            return false;
        }
        
        // التحقق من MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES[$file]['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($mime, $allowedMimeTypes)) {
            $this->errors[] = 'نوع الملف غير مسموح به لـ ' . $fieldName;
            return false;
        }
        
        $ext = strtolower(pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            $this->errors[] = 'امتداد الملف غير مسموح به لـ ' . $fieldName;
            return false;
        }
        
        return true;
    }
    
    /**
     * التحقق من حجم الملف
     */
    private function validateFileSize($file, $maxSize = 2 * 1024 * 1024) {
        if (!isset($_FILES[$file]) || $_FILES[$file]['error'] === UPLOAD_ERR_NO_FILE) {
            return true;
        }
        
        if ($_FILES[$file]['size'] > $maxSize) {
            $this->errors[] = 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت';
            return false;
        }
        
        return true;
    }
    
    /**
     * معالجة صورة التصنيف
     */
    private function processCategoryImage() {
        if (!isset($_FILES['pc_image']) || $_FILES['pc_image']['error'] === UPLOAD_ERR_NO_FILE) {
            return $this->getOldImage('pc_image');
        }
        
        if (!$this->validateFileType('pc_image', 'صورة التصنيف') || 
            !$this->validateFileSize('pc_image')) {
            return false;
        }
        
        $ext = strtolower(pathinfo($_FILES['pc_image']['name'], PATHINFO_EXTENSION));
        $newFileName = 'CATIMG-' . date('Ymd') . '-' . uniqid() . '.' . $ext;
        
        $targetDir = '../upload/category/';
        $targetPath = $targetDir . $newFileName;
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        try {
            $imgSImage = new SimpleImage();
            $imgSImage->load($_FILES['pc_image']['tmp_name']);
            $imgSImage->resize(70, 70);
            $imgSImage->save($targetPath);
            
            // حذف الصورة القديمة
            $oldImage = $this->getOldImage('pc_image');
            if ($oldImage && $oldImage != $newFileName) {
                $oldPath = $targetDir . $oldImage;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            return $newFileName;
            
        } catch (Exception $e) {
            $this->errors[] = 'خطأ في معالجة صورة التصنيف: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * معالجة صورة البانر
     */
    private function processBannerImage() {
        if (!isset($_FILES['ci_banner']) || $_FILES['ci_banner']['error'] === UPLOAD_ERR_NO_FILE) {
            return $_POST['old_img'] ?? $this->getOldImage('pc_banner');
        }
        
        if (!$this->validateFileType('ci_banner', 'صورة البانر') || 
            !$this->validateFileSize('ci_banner', 3 * 1024 * 1024)) { // 3 ميجابايت للبانر
            return false;
        }
        
        $ext = strtolower(pathinfo($_FILES['ci_banner']['name'], PATHINFO_EXTENSION));
        $newFileName = 'BANNER-' . date('Ymd') . '-' . uniqid() . '.' . $ext;
        
        $targetDir = '../upload/bannerimage/';
        $targetPath = $targetDir . $newFileName;
        
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        try {
            $imgSImage = new SimpleImage();
            $imgSImage->load($_FILES['ci_banner']['tmp_name']);
            $imgSImage->resize(968, 230);
            $imgSImage->save($targetPath);
            
            // حذف الصورة القديمة
            $oldImage = $this->getOldImage('pc_banner');
            if ($oldImage && $oldImage != $newFileName) {
                $oldPath = $targetDir . $oldImage;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            return $newFileName;
            
        } catch (Exception $e) {
            $this->errors[] = 'خطأ في معالجة صورة البانر: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * الحصول على الصورة القديمة
     */
    private function getOldImage($field) {
        global $con;
        
        $sql = "SELECT $field FROM product_category WHERE pc_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->pc_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row ? $row[$field] : '';
    }
    
    /**
     * تحديث البيانات في قاعدة البيانات
     */
    public function add() {
        global $con;
        
        // معالجة الصور
        $categoryImage = $this->processCategoryImage();
        if ($categoryImage === false) {
            return false;
        }
        
        $bannerImage = $this->processBannerImage();
        if ($bannerImage === false) {
            return false;
        }
        
        // تحديث قاعدة البيانات
        $sql = "UPDATE product_category 
                SET pc_name = ?,
                    pc_image = ?,
                    pc_banner = ?,
                    pc_updated_date = NOW()
                WHERE pc_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", 
            $this->pc_name,
            $categoryImage,
            $bannerImage,
            $this->pc_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            
            $this->msg = '<div class="alert alert-success">
                <i class="icon-ok"></i> تم تحديث التصنيف بنجاح
            </div>';
            
            unset($_SESSION['pc_name']);
            return true;
        } else {
            $this->errors[] = 'خطأ في تحديث قاعدة البيانات: ' . mysqli_error($con);
            mysqli_stmt_close($stmt);
            return false;
        }
    }
    
    /**
     * الحصول على رسالة النجاح
     */
    public function getMsg() {
        return $this->msg ?? '';
    }
    
    /**
     * الحصول على أخطاء التحقق
     */
    public function getErrors() {
        return $this->errors;
    }
}

// معالجة الرسائل من الجلسة
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

$pc_name = '';
if (isset($_SESSION['pc_name'])) {
    $pc_name = $_SESSION['pc_name'];
    unset($_SESSION['pc_name']);
}

// التحقق من وجود token
$token = isset($_GET['token']) ? $_GET['token'] : '';
if (empty($token)) {
    $_SESSION['error'] = 'معرف التصنيف غير موجود';
    header("Location: maincat-view.php");
    exit();
}

// إزالة الـ 4 أحرف الأولى من token
$token = substr($token, 4);

// جلب بيانات التصنيف
$stmt = mysqli_prepare($con, "SELECT * FROM product_category WHERE MD5(pc_id) = ?");
mysqli_stmt_bind_param($stmt, "s", $token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    $_SESSION['error'] = 'التصنيف غير موجود';
    header("Location: maincat-view.php");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdate'])) {
    
    $adn = new addproduct(
        $_POST['pc_name'] ?? '',
        $_POST['pc_id'] ?? 0,
        $_FILES['pc_image']['name'] ?? ''
    );
    
    if ($adn->valid()) {
        $adn->add();
    }
    
    // تخزين الأخطاء في الجلسة
    if (!empty($adn->getErrors())) {
        $_SESSION['errors'] = $adn->getErrors();
    }
    
    $_SESSION['msg'] = $adn->getMsg();
    
    header("Location: maincat-edit.php?token=" . $_GET['token']);
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

<div class="main-container" id="main-container">
    <script type="text/javascript">
        try{ace.settings.check('main-container' , 'fixed')}catch(e){}
    </script>

    <div class="main-container-inner">
        <a class="menu-toggler" id="menu-toggler" href="#">
            <span class="menu-text"></span>
        </a>
        
        <script type="text/javascript">
        // دالة التحقق من النموذج
        function myvalid() {
            var pc_name = $('#pc_name').val().trim();
            var message = "";
            var valid = true;
            
            if(pc_name === "") {
                message = 'الرجاء إدخال اسم التصنيف';
                $('#pc_name').focus();
                valid = false;
            }
            
            if(!valid) {
                $('#msg').html('<div class="alert alert-danger"><i class="icon-remove"></i> ' + message + '</div>');
                return false;
            }
            
            return confirm('هل أنت متأكد من حفظ التغييرات؟');
        }
        
        // معاينة الصورة المصغرة
        function previewCategoryImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#categoryPreview').html(
                        '<div class="preview-container">' +
                        '<h4>معاينة الصورة الجديدة:</h4>' +
                        '<img src="' + e.target.result + '" style="max-width:70px; max-height:70px; border:2px solid #4CAF50;"/>' +
                        '</div>'
                    );
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // معاينة صورة البانر
        function previewBannerImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#bannerPreview').html(
                        '<div class="preview-container">' +
                        '<h4>معاينة البانر الجديد:</h4>' +
                        '<img src="' + e.target.result + '" style="max-width:250px; max-height:70px; border:2px solid #4CAF50;"/>' +
                        '</div>'
                    );
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        $(document).ready(function() {
            // تفعيل معاينة الصور
            $('input[name="pc_image"]').change(function() {
                previewCategoryImage(this);
            });
            
            $('input[name="ci_banner"]').change(function() {
                previewBannerImage(this);
            });
        });
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
                    <li class="active">تعديل التصنيف</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        <i class="icon-edit"></i> تعديل التصنيف
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($row->pc_name); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- رسائل النظام -->
                        <div id="msg"><?php echo $msg . $errorMsg; ?></div>
                        
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                            
                            <em style="display:block; margin:5px;">
                                الحقول التي تحمل <span style="color:#F00">*</span> إجبارية
                            </em>
                            
                            <!-- اسم التصنيف -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right" for="pc_name">
                                    اسم التصنيف <span style="color:#CC0000">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="text" name="pc_name" id="pc_name" 
                                           class="col-xs-10 col-sm-5" 
                                           value="<?php echo htmlspecialchars($row->pc_name); ?>" />
                                    <input type="hidden" name="pc_id" value="<?php echo $row->pc_id; ?>" />
                                </div>
                            </div>
                            
                            <!-- الصورة الحالية -->
                            <?php if (!empty($row->pc_image)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">الصورة الحالية</label>
                                <div class="col-sm-9">
                                    <img src="../upload/category/<?php echo htmlspecialchars($row->pc_image); ?>" 
                                         style="width:70px; height:70px; border:1px solid #ddd; padding:3px;" />
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- صورة جديدة -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">تغيير الصورة</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input type="file" name="pc_image" accept=".jpg,.jpeg,.png,.gif" />
                                    </div>
                                    <span class="help-block">
                                        <i class="icon-info-sign"></i>
                                        الأبعاد الموصى بها: 70 × 70 بكسل
                                    </span>
                                    <div id="categoryPreview"></div>
                                </div>
                            </div>
                            
                            <!-- البانر الحالي -->
                            <?php if (!empty($row->pc_banner)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">البانر الحالي</label>
                                <div class="col-sm-9">
                                    <img src="../upload/bannerimage/<?php echo htmlspecialchars($row->pc_banner); ?>" 
                                         style="width:250px; height:70px; border:1px solid #ddd; padding:3px;" />
                                    <input type="hidden" name="old_img" value="<?php echo htmlspecialchars($row->pc_banner); ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- بانر جديد -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">تغيير البانر</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input type="file" name="ci_banner" accept=".jpg,.jpeg,.png,.gif" />
                                    </div>
                                    <span class="help-block">
                                        <i class="icon-info-sign"></i>
                                        الأبعاد الموصى بها: 968 × 230 بكسل
                                    </span>
                                    <div id="bannerPreview"></div>
                                </div>
                            </div>
                            
                            <!-- أزرار التحكم -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> تحديث
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> إعادة تعيين
                                    </button>
                                    <a href="maincat-view.php" class="btn btn-default">
                                        <i class="icon-remove bigger-110"></i> إلغاء
                                    </a>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript files -->
<script src="assets/js/jquery-2.0.3.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- نهاية ملف maincat-edit.php - الإصدار 2.0.0 -->
</body>
</html>