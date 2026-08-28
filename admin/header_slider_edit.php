<?php
/**
 * ملف تعديل شريط تمرير الرأس (Header Slider)
 * 
 * @filename    header_slider_edit.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تعديل بيانات وصور شريط تمرير الرأس
 *              مع إمكانية تغيير الصورة وحذف القديمة تلقائياً
 * @lastUpdated 2024-01-20
 * @phpVersion  8.3
 */

// بدء تشغيل المخزن المؤقت
ob_start();

// بدء الجلسة بشكل آمن
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

/**
 * كلاس تعديل شريط تمرير الرأس
 */
class editheadersld {
    
    private $msg;
    private $hs_id;
    private $hs_status;
    private $hs_text;
    private $hs_image;
    private $errors = [];
    
    /**
     * constructor
     * @param string $hs_id معرف الشريط (مشفر بـ MD5)
     */
    public function __construct($hs_id) {
        // التحقق من صحة المعرف المشفر
        $this->hs_id = $this->validateMd5Hash($hs_id);
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
     * جلب تفاصيل الشريط
     * @return object|null
     */
    public function detailsObj() {
        global $con;
        
        if (empty($this->hs_id)) {
            return null;
        }
        
        $sql = "SELECT * FROM header_slider WHERE MD5(hs_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->hs_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * تعيين الحالة
     * @param string $value
     */
    public function setStatus($value) {
        $this->hs_status = ($value == '1') ? '1' : '0';
    }
    
    /**
     * تعيين النص
     * @param string $value
     */
    public function setText($value) {
        $this->hs_text = $this->sanitizeText($value, 255);
    }
    
    /**
     * تعيين الصورة
     * @param string $value اسم الملف
     */
    public function setImage($value) {
        $this->hs_image = $this->sanitizeFileName($value);
    }
    
    /**
     * تنظيف النص
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
     * تنظيف اسم الملف
     * @param string $filename
     * @return string
     */
    private function sanitizeFileName($filename) {
        // إزالة المسارات والأحرف الخطرة
        $filename = basename($filename);
        $filename = preg_replace('/[^a-zA-Z0-9\-\._]/', '', $filename);
        return $filename;
    }
    
    /**
     * التحقق من صحة البيانات
     * @return bool
     */
    public function valid() {
        $this->errors = [];
        
        if (empty($this->hs_id)) {
            $this->errors[] = 'معرف الشريط غير صحيح';
        }
        
        return empty($this->errors);
    }
    
    /**
     * التحقق من صحة نوع الملف (إذا تم رفع ملف جديد)
     * @return bool
     */
    private function validateFileType() {
        // التحقق باستخدام MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['hs_image']['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        
        // التحقق من MIME type
        if (!in_array($mime, $allowedMimeTypes)) {
            $this->errors[] = 'نوع الملف غير مسموح به. الأنواع المسموحة: JPG, PNG, GIF, PDF';
            return false;
        }
        
        // التحقق من الامتداد
        $ext = strtolower(pathinfo($this->hs_image, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExtensions)) {
            $this->errors[] = 'امتداد الملف غير مسموح به';
            return false;
        }
        
        return true;
    }
    
    /**
     * التحقق من حجم الملف
     * @return bool
     */
    private function validateFileSize() {
        $maxSize = 5 * 1024 * 1024; // 5 ميجابايت
        
        if ($_FILES['hs_image']['size'] > $maxSize) {
            $this->errors[] = 'حجم الملف كبير جداً. الحد الأقصى 5 ميجابايت';
            return false;
        }
        
        return true;
    }
    
    /**
     * الحصول على الصورة القديمة
     * @return string|false
     */
    private function getOldImage() {
        global $con;
        
        $sql = "SELECT hs_image FROM header_slider WHERE MD5(hs_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "s", $this->hs_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row ? $row->hs_image : false;
    }
    
    /**
     * معالجة الصورة الجديدة وحفظها
     * @return string|false اسم الملف المحفوظ أو false في حالة الفشل
     */
    private function processNewImage() {
        // التحقق من صحة نوع الملف
        if (!$this->validateFileType()) {
            return false;
        }
        
        // التحقق من حجم الملف
        if (!$this->validateFileSize()) {
            return false;
        }
        
        // إنشاء اسم ملف فريد
        $ext = strtolower(pathinfo($this->hs_image, PATHINFO_EXTENSION));
        $newFileName = 'SLDIMG-' . date('Ymd') . '-' . uniqid() . '.' . $ext;
        
        $targetDir = '../upload/slider/';
        $targetPath = $targetDir . $newFileName;
        
        // التأكد من وجود المجلد
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                $this->errors[] = 'لا يمكن إنشاء مجلد الرفع';
                return false;
            }
        }
        
        try {
            // معالجة الصور فقط (وليس PDF)
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $imgSImage = new SimpleImage();
                $imgSImage->load($_FILES['hs_image']['tmp_name']);
                $imgSImage->resize(511, 308); // تغيير الحجم
                $imgSImage->save($targetPath);
            } else {
                // لملفات PDF، فقط نقل الملف
                if (!move_uploaded_file($_FILES['hs_image']['tmp_name'], $targetPath)) {
                    $this->errors[] = 'فشل في نقل الملف';
                    return false;
                }
            }
            
            return $newFileName;
            
        } catch (Exception $e) {
            $this->errors[] = 'خطأ في معالجة الصورة: ' . $e->getMessage();
            return false;
        }
    }
    
    /**
     * حذف الصورة القديمة
     * @param string $oldImage
     * @return bool
     */
    private function deleteOldImage($oldImage) {
        if (empty($oldImage)) {
            return true;
        }
        
        $targetPath = '../upload/slider/' . $oldImage;
        
        // التأكد من أن الملف موجود وليس مجلد
        if (file_exists($targetPath) && is_file($targetPath)) {
            return unlink($targetPath);
        }
        
        return true;
    }
    
    /**
     * تحديث البيانات في قاعدة البيانات
     * @return bool
     */
    public function update() {
        global $con;
        
        // التحقق من وجود ملف جديد
        $hasNewImage = !empty($this->hs_image) && 
                      isset($_FILES['hs_image']) && 
                      $_FILES['hs_image']['error'] === UPLOAD_ERR_OK;
        
        $newImageName = null;
        
        // معالجة الصورة الجديدة إذا وجدت
        if ($hasNewImage) {
            // الحصول على الصورة القديمة
            $oldImage = $this->getOldImage();
            
            // معالجة الصورة الجديدة
            $newImageName = $this->processNewImage();
            if (!$newImageName) {
                return false;
            }
            
            // حذف الصورة القديمة
            if ($oldImage) {
                $this->deleteOldImage($oldImage);
            }
        }
        
        // بناء استعلام التحديث
        if ($hasNewImage && $newImageName) {
            $sql = "UPDATE header_slider SET
                    hs_status = ?,
                    hs_text = ?,
                    hs_image = ?,
                    hs_updated_date = NOW()
                    WHERE MD5(hs_id) = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", 
                $this->hs_status,
                $this->hs_text,
                $newImageName,
                $this->hs_id
            );
        } else {
            $sql = "UPDATE header_slider SET
                    hs_status = ?,
                    hs_text = ?,
                    hs_updated_date = NOW()
                    WHERE MD5(hs_id) = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sss", 
                $this->hs_status,
                $this->hs_text,
                $this->hs_id
            );
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            if ($affected > 0) {
                $this->msg = '<div class="alert alert-success">✅ تم تحديث شريط التمرير بنجاح</div>';
                return true;
            } else {
                // إذا لم يتم تحديث أي سجل، ولكن الصورة الجديدة تم رفعها
                if ($hasNewImage && $newImageName) {
                    $this->msg = '<div class="alert alert-warning">⚠️ تم تحديث الصورة ولكن لم يتم تغيير البيانات</div>';
                    return true;
                }
                
                $this->errors[] = 'لم يتم تحديث أي بيانات';
                return false;
            }
        } else {
            $this->errors[] = 'خطأ في تحديث قاعدة البيانات: ' . mysqli_error($con);
            
            // حذف الصورة الجديدة إذا فشل التحديث
            if ($hasNewImage && $newImageName) {
                $targetPath = '../upload/slider/' . $newImageName;
                if (file_exists($targetPath)) {
                    unlink($targetPath);
                }
            }
            
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
     * الحصول على معرف الشريط
     * @return string
     */
    public function getId() {
        return $this->hs_id;
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
    $_SESSION['error'] = 'معرف الشريط غير موجود';
    header("Location: header_slider_view.php");
    exit();
}

// إزالة الـ 4 أحرف الأولى من token (لأغراض أمنية)
$token = substr($token, 4);

try {
    $ob = new editheadersld($token);
    $row = $ob->detailsObj();
    
    if (!$row) {
        throw new Exception('شريط التمرير غير موجود');
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: header_slider_view.php");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    
    $ob->setStatus($_POST['hs_status'] ?? '0');
    $ob->setText($_POST['hs_text'] ?? '');
    
    if (isset($_FILES['hs_image']) && $_FILES['hs_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $ob->setImage($_FILES['hs_image']['name'] ?? '');
    }
    
    if ($ob->valid()) {
        $ob->update();
    }
    
    // تخزين الأخطاء في الجلسة
    if (!empty($ob->getErrors())) {
        $_SESSION['errors'] = $ob->getErrors();
    }
    
    $_SESSION['msg'] = $ob->getMsg();
    
    // إعادة التوجيه لتجنب إعادة الإرسال
    header("Location: header_slider_edit.php?token=" . $_GET['token']);
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

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<script type="text/javascript">
// دالة التحقق من النموذج
function myvalid() {
    var hs_text = $('#hs_text').val().trim();
    
    // التحقق من النص (اختياري)
    if(hs_text.length > 255) {
        $('#message').html('<div class="alert alert-warning"><i class="icon-warning"></i> النص طويل جداً (الحد الأقصى 255 حرف)</div>');
        return false;
    }
    
    return confirm('هل أنت متأكد من حفظ التغييرات؟');
}

// معاينة الصورة قبل الرفع
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            $('#imagePreview').html('<img src="' + e.target.result + '" style="max-width: 200px; max-height: 150px; border: 2px solid #4CAF50; padding: 3px;"/>');
            $('#currentImageContainer').hide(); // إخفاء الصورة الحالية
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        $('#imagePreview').empty();
        $('#currentImageContainer').show(); // إظهار الصورة الحالية
    }
}

$(document).ready(function() {
    // تفعيل معاينة الصورة
    $('#hs_image').change(function() {
        previewImage(this);
    });
    
    // إظهار/إخفاء حقل رفع الصورة
    $('#changeImage').click(function() {
        $('#uploadImageDiv').toggle();
        if($('#uploadImageDiv').is(':visible')) {
            $(this).text('إلغاء تغيير الصورة');
        } else {
            $(this).text('تغيير الصورة');
            $('#hs_image').val(''); // تفريغ حقل الملف
            $('#imagePreview').empty();
            $('#currentImageContainer').show();
        }
    });
});
</script>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <h2>
                <i class="icon-picture"></i> 
                إدارة الرأس › تعديل شريط التمرير
            </h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
                
                <div class="row buttons">
                    <input type="button" class="x2-button" onClick="window.location ='header_slider_view.php'" value="🔙 عودة إلى القائمة">
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
                                            
                                            <!-- الحالة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الحالة:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <label class="radio-inline">
                                                        <input type="radio" name="hs_status" value="1" <?php echo ($row->hs_status == '1') ? 'checked' : ''; ?>>
                                                        <span class="label label-success">نشط</span>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="hs_status" value="0" <?php echo ($row->hs_status == '0') ? 'checked' : ''; ?>>
                                                        <span class="label label-default">غير نشط</span>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <!-- المحتوى النصي -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">النص:</label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <input type="text" name="hs_text" id="hs_text" 
                                                           value="<?php echo htmlspecialchars($row->hs_text ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           class="form-control" maxlength="255" 
                                                           placeholder="النص المصاحب للصورة (اختياري)" />
                                                </div>
                                            </div>
                                            
                                            <!-- الصورة الحالية -->
                                            <div id="currentImageContainer" class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الصورة الحالية:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <?php if (!empty($row->hs_image)): ?>
                                                        <img src="../upload/slider/<?php echo htmlspecialchars($row->hs_image); ?>" 
                                                             style="max-width: 200px; max-height: 150px; border: 1px solid #ddd; padding: 5px;"/>
                                                        <br>
                                                        <span class="help-block"><?php echo htmlspecialchars($row->hs_image); ?></span>
                                                    <?php else: ?>
                                                        <span class="label label-warning">لا توجد صورة</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- زر تغيير الصورة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;"></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <button type="button" id="changeImage" class="x2-button btn-info">
                                                        <i class="icon-edit"></i> تغيير الصورة
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- رفع صورة جديدة (مخفي افتراضياً) -->
                                            <div id="uploadImageDiv" style="display: none;">
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">صورة جديدة:</label>
                                                    <div class="formInputBox" style="width:587px;height:auto;">
                                                        <input type="file" name="hs_image" id="hs_image" 
                                                               class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf" />
                                                        <span class="help-block">
                                                            <i class="icon-info-sign"></i>
                                                            الأنواع المسموحة: JPG, PNG, GIF, PDF (الحد الأقصى 5 ميجابايت)
                                                            <br>أبعاد الصورة الموصى بها: 511 × 308 بكسل
                                                        </span>
                                                        
                                                        <!-- معاينة الصورة الجديدة -->
                                                        <div id="imagePreview" style="margin-top: 10px;"></div>
                                                    </div>
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
                    <button type="reset" class="x2-button btn-default">
                        <i class="icon-refresh"></i> إعادة تعيين
                    </button>
                    <button type="button" class="x2-button" onclick="window.location='header_slider_view.php'">
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

<!-- نهاية ملف header_slider_edit.php - الإصدار 2.0.0 -->
</body>
</html>