<?php
/**
 * File: videoslider-edit.php
 * Version: 2.0.0
 * Description: تعديل إعلانات الفيديو (الرابط - العنوان - الوصف - الدول) (تمت الترقية إلى PHP 8.3)
 */

// تفعيل strict typing
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

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
check_admin_login();

/**
 * Class EditAdvertisement - تعديل إعلانات الفيديو
 */
class EditAdvertisement {
    private ?string $msg = null;
    private int $adv_id;
    private ?string $adv_img;
    private ?string $adv_link;
    private int $adv_imagewidth;
    private int $adv_imageheight;
    private ?string $adv_title;
    private ?string $adv_description;
    private ?string $adv_country;
    private ?string $adv_global;
    private ?string $adv_redirect;
    private mysqli $db;
    
    // الثوابت
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    
    /**
     * المُنشئ
     */
    public function __construct(int $adv_id, ?mysqli $databaseConnection = null) {
        global $con;
        $this->adv_id = $adv_id;
        $this->db = $databaseConnection ?? $con;
    }
    
    /**
     * جلب تفاصيل الإعلان
     */
    public function detailsObj(): ?object {
        $sql = "SELECT * FROM video_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            error_log('خطأ في تحضير الاستعلام: ' . mysqli_error($this->db));
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * التحقق من صحة البيانات
     */
    public function valid(): bool {
        // يمكن إضافة تحقق هنا إذا لزم الأمر
        return true;
    }
    
    /**
     * التحقق من صحة الملف المرفوع
     */
    private function validateFile(array $file): bool {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في رفع الملف</div>';
            return false;
        }
        
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> حجم الملف يجب أن يكون أقل من 5 ميجابايت</div>';
            return false;
        }
        
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الامتداد غير مسموح به</div>';
            return false;
        }
        
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> الملف ليس صورة صالحة</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * معالجة وحفظ الصورة الجديدة
     */
    private function processNewImage(): ?string {
        if (!isset($_FILES['adv_img']) || $_FILES['adv_img']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        if (!$this->validateFile($_FILES['adv_img'])) {
            return null;
        }
        
        // حذف الصورة القديمة
        $this->deleteOldImage();
        
        // إنشاء اسم فريد للصورة
        $ext = strtolower(pathinfo($_FILES['adv_img']['name'], PATHINFO_EXTENSION));
        $filename = 'adv_' . $this->adv_imagewidth . '_' . $this->adv_imageheight . '_' . time() . '.' . $ext;
        
        try {
            $image = new SimpleImage();
            $image->load($_FILES['adv_img']['tmp_name']);
            $image->resize($this->adv_imagewidth, $this->adv_imageheight);
            
            $uploadPath = __DIR__ . '/../upload/video_slider/' . $filename;
            $image->save($uploadPath);
            
            return $filename;
            
        } catch (Exception $e) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في معالجة الصورة: ' . $e->getMessage() . '</div>';
            return null;
        }
    }
    
    /**
     * حذف الصورة القديمة
     */
    private function deleteOldImage(): void {
        $sql = "SELECT adv_img FROM video_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        if ($row && !empty($row['adv_img'])) {
            $filePath = __DIR__ . '/../upload/video_slider/' . $row['adv_img'];
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    /**
     * تحديث الإعلان
     */
    public function update(): bool {
        // معالجة الصورة الجديدة إذا وجدت
        $newImage = $this->processNewImage();
        
        if ($newImage !== null) {
            // تحديث مع الصورة
            $sql = "UPDATE video_slider SET
                    adv_img = ?,
                    adv_link = ?,
                    adv_imagewidth = ?,
                    adv_imageheight = ?,
                    adv_title = ?,
                    adv_description = ?,
                    adv_country = ?,
                    adv_global = ?,
                    adv_redirect = ?
                    WHERE adv_id = ?";
            
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في قاعدة البيانات</div>';
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "ssiisssssi", 
                $newImage,
                $this->adv_link,
                $this->adv_imagewidth,
                $this->adv_imageheight,
                $this->adv_title,
                $this->adv_description,
                $this->adv_country,
                $this->adv_global,
                $this->adv_redirect,
                $this->adv_id
            );
            
        } else {
            // تحديث بدون صورة
            $sql = "UPDATE video_slider SET
                    adv_link = ?,
                    adv_title = ?,
                    adv_description = ?,
                    adv_country = ?,
                    adv_global = ?,
                    adv_redirect = ?
                    WHERE adv_id = ?";
            
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> خطأ في قاعدة البيانات</div>';
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "ssssssi", 
                $this->adv_link,
                $this->adv_title,
                $this->adv_description,
                $this->adv_country,
                $this->adv_global,
                $this->adv_redirect,
                $this->adv_id
            );
        }
        
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> تم تحديث الإعلان بنجاح</div>';
            return true;
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> فشل التحديث: ' . $error . '</div>';
            return false;
        }
    }
    
    /**
     * تعيين عرض الصورة
     */
    public function setWidth(?string $width): self {
        $this->adv_imagewidth = (int)($width ?: 0);
        return $this;
    }
    
    /**
     * تعيين ارتفاع الصورة
     */
    public function setHeight(?string $height): self {
        $this->adv_imageheight = (int)($height ?: 0);
        return $this;
    }
    
    /**
     * تعيين رابط الفيديو
     */
    public function setLink(?string $link): self {
        $this->adv_link = $link ? trim($link) : '';
        return $this;
    }
    
    /**
     * تعيين العنوان
     */
    public function setTitle(?string $title): self {
        $this->adv_title = $title ? trim($title) : '';
        return $this;
    }
    
    /**
     * تعيين الوصف
     */
    public function setDescription(?string $description): self {
        $this->adv_description = $description ? trim($description) : '';
        return $this;
    }
    
    /**
     * تعيين الدول
     */
    public function setCountry(?array $countries): self {
        $this->adv_country = !empty($countries) ? implode(",", array_map('intval', $countries)) : '';
        return $this;
    }
    
    /**
     * تعيين النطاق العام
     */
    public function setGlobal(?string $global): self {
        $this->adv_global = $global ? trim($global) : '0';
        return $this;
    }
    
    /**
     * تعيين رابط إعادة التوجيه
     */
    public function setRedirect(?string $redirect): self {
        $this->adv_redirect = $redirect ? trim($redirect) : '';
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
if (!isset($_GET['aid']) || empty($_GET['aid'])) {
    header("Location: videoslider-view.php");
    exit();
}

$aid = filter_input(INPUT_GET, 'aid', FILTER_VALIDATE_INT);
if (!$aid || $aid <= 0) {
    header("Location: videoslider-view.php");
    exit();
}

// إنشاء الكائن وجلب التفاصيل
$ob = new EditAdvertisement($aid);
$row = $ob->detailsObj();

if (!$row) {
    $_SESSION['msg'] = '<div class="alert alert-danger"><i class="icon-remove"></i> الإعلان غير موجود</div>';
    header("Location: videoslider-view.php");
    exit();
}

// معالجة النموذج
if (isset($_POST['btnUpdate'])) {
    $ob->setWidth($_POST['adv_imagewidth'] ?? '')
       ->setHeight($_POST['adv_imageheight'] ?? '')
       ->setLink($_POST['adv_link'] ?? '')
       ->setTitle($_POST['adv_title'] ?? '')
       ->setDescription($_POST['adv_description'] ?? '')
       ->setGlobal($_POST['adv_global'] ?? '0')
       ->setRedirect($_POST['adv_redirect'] ?? '')
       ->setCountry($_POST['adv_country'] ?? []);
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    $_SESSION['msg'] = $ob->getMessage();
    header("Location: videoslider-edit.php?aid=" . $aid);
    exit();
}

// رسالة الجلسة
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// جلب قائمة الدول
$countries = [];
$sqlcnty = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name ASC";
$rscontry = mysqli_query($con, $sqlcnty);
if ($rscontry) {
    while ($country = mysqli_fetch_assoc($rscontry)) {
        $countries[] = $country;
    }
}

// الدول المحددة
$selectedCountries = !empty($row->adv_country) ? explode(",", $row->adv_country) : [];
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
                    <li class="active">تعديل السلايدر</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        سلايدر الفيديو
                        <small>
                            <i class="icon-double-angle-right"></i>
                            تعديل السلايدر
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <?php if ($msg): ?>
                            <div class="row">
                                <div class="col-xs-12">
                                    <?php echo $msg; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" 
                              method="post" enctype="multipart/form-data">
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">الدول:</label>
                                <div class="col-sm-8">
                                    <select id="adv_country" name="adv_country[]" multiple="multiple" 
                                            class="chosen-select" style="width:440px;">
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo (int)$country['cn_id']; ?>" 
                                                <?php echo in_array($country['cn_id'], $selectedCountries) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country['cn_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <input type="hidden" name="adv_global" value="0">
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">رابط الفيديو</label>
                                <div class="col-sm-9">
                                    <input name="adv_link" 
                                           id="adv_link" 
                                           class="col-xs-10 col-sm-5" 
                                           type="url" 
                                           style="width:440px;" 
                                           value="<?php echo htmlspecialchars($row->adv_link ?? ''); ?>" 
                                           placeholder="https://www.youtube.com/watch?v=...">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">رابط إعادة التوجيه</label>
                                <div class="col-sm-9">
                                    <input name="adv_redirect" 
                                           id="adv_redirect" 
                                           class="col-xs-10 col-sm-5" 
                                           type="url" 
                                           style="width:440px;" 
                                           value="<?php echo htmlspecialchars($row->adv_redirect ?? ''); ?>"
                                           placeholder="https://example.com">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">العنوان</label>
                                <div class="col-sm-9">
                                    <input name="adv_title" 
                                           id="adv_title" 
                                           class="col-xs-10 col-sm-5" 
                                           type="text" 
                                           style="width:440px;" 
                                           value="<?php echo htmlspecialchars($row->adv_title ?? ''); ?>" 
                                           required
                                           maxlength="255">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">الوصف</label>
                                <div class="col-sm-9">
                                    <textarea name="adv_description" 
                                              id="adv_description" 
                                              rows="10" 
                                              cols="60" 
                                              required
                                              style="width:440px;"><?php echo htmlspecialchars($row->adv_description ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">أبعاد الصورة</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="adv_imagewidth" value="640">
                                    <input type="hidden" name="adv_imageheight" value="360">
                                    <p class="form-control-static">640 × 360 بكسل (يتم تغيير حجم الصورة تلقائياً)</p>
                                </div>
                            </div>
                            
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate" id="btnUpdate">
                                        <i class="icon-ok bigger-110"></i>
                                        تحديث
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i>
                                        إعادة تعيين
                                    </button>
                                    <a href="videoslider-view.php" class="btn btn-default">
                                        <i class="icon-arrow-left bigger-110"></i>
                                        رجوع
                                    </a>
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

<script type="text/javascript">
jQuery(function($) {
    // تفعيل Chosen للقوائم المتعددة
    $(".chosen-select").chosen({
        width: "440px",
        placeholder_text_multiple: "اختر الدول...",
        no_results_text: "لا توجد نتائج"
    });
    
    // إخفاء رسالة النجاح بعد 5 ثوان
    setTimeout(function() {
        $('.alert-success').fadeOut('slow');
    }, 5000);
    
    // التحقق من صحة الرابط
    $('#adv_link, #adv_redirect').on('blur', function() {
        var url = $(this).val();
        if (url && !url.match(/^https?:\/\//)) {
            $(this).val('http://' + url);
        }
    });
});
</script>

</body>
</html>
<?php ob_end_flush(); ?>