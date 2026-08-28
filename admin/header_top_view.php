<?php
/**
 * ملف تعديل روابط أعلى/أسفل الصفحة (Header Top/Bottom Links)
 * 
 * @filename    header_top_view.php
 * @version     2.0.0
 * @author      نظام إدارة المحتوى
 * @description هذا الملف مسؤول عن تعديل إعدادات الروابط في أعلى وأسفل الصفحة
 *              مع إمكانية تغيير الصورة والنصوص والروابط
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
 * كلاس تعديل روابط أعلى/أسفل الصفحة
 */
class editproduct {
    
    private $msg;
    private $htbl_id = 1; // معرف ثابت لأن هناك سجل واحد فقط
    private $htbl_button_text;
    private $htbl_button_link;
    private $htbl_image;
    private $htbl_status;
    private $errors = [];
    
    /**
     * جلب تفاصيل الرابط
     * @return object|null
     */
    public function detailsObj() {
        global $con;
        
        $sql = "SELECT * FROM header_top_bottom_link WHERE htbl_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->htbl_id);
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
        $this->htbl_status = ($value == '1') ? '1' : '0';
    }
    
    /**
     * تعيين نص الزر
     * @param string $value
     */
    public function setButtonText($value) {
        $this->htbl_button_text = $this->sanitizeText($value, 100);
    }
    
    /**
     * تعيين رابط الزر
     * @param string $value
     */
    public function setButtonLink($value) {
        $this->htbl_button_link = $this->sanitizeUrl($value);
    }
    
    /**
     * تعيين الصورة
     * @param string $value
     */
    public function setImage($value) {
        $this->htbl_image = $this->sanitizeFileName($value);
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
     * تنظيف وتنسيق الرابط
     * @param string $url
     * @return string
     */
    private function sanitizeUrl($url) {
        $url = trim(strip_tags($url));
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // إضافة http:// إذا لم يكن موجوداً
        if (!empty($url) && !preg_match('/^https?:\/\//i', $url)) {
            $url = 'http://' . $url;
        }
        
        return substr($url, 0, 255);
    }
    
    /**
     * تنظيف اسم الملف
     * @param string $filename
     * @return string
     */
    private function sanitizeFileName($filename) {
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
        
        if (empty($this->htbl_button_text)) {
            $this->errors[] = 'نص الزر مطلوب';
        }
        
        if (empty($this->htbl_button_link)) {
            $this->errors[] = 'رابط الزر مطلوب';
        } elseif (!filter_var($this->htbl_button_link, FILTER_VALIDATE_URL)) {
            $this->errors[] = 'الرابط غير صحيح';
        }
        
        return empty($this->errors);
    }
    
    /**
     * التحقق من صحة نوع الملف (إذا تم رفع ملف جديد)
     * @return bool
     */
    private function validateFileType() {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['htbl_image']['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        // التحقق من MIME type
        if (!in_array($mime, $allowedMimeTypes)) {
            $this->errors[] = 'نوع الملف غير مسموح به. الأنواع المسموحة: JPG, PNG, GIF';
            return false;
        }
        
        // التحقق من الامتداد
        $ext = strtolower(pathinfo($this->htbl_image, PATHINFO_EXTENSION));
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
        $maxSize = 2 * 1024 * 1024; // 2 ميجابايت
        
        if ($_FILES['htbl_image']['size'] > $maxSize) {
            $this->errors[] = 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت';
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
        
        $sql = "SELECT htbl_image FROM header_top_bottom_link WHERE htbl_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $this->htbl_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row ? $row->htbl_image : false;
    }
    
    /**
     * معالجة الصورة الجديدة وحفظها
     * @return string|false
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
        $ext = strtolower(pathinfo($this->htbl_image, PATHINFO_EXTENSION));
        $newFileName = 'HTRIMG-' . date('Ymd') . '-' . uniqid() . '.' . $ext;
        
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
            $imgSImage = new SimpleImage();
            $imgSImage->load($_FILES['htbl_image']['tmp_name']);
            $imgSImage->resize(378, 117); // تغيير الحجم
            $imgSImage->save($targetPath);
            
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
        $hasNewImage = !empty($this->htbl_image) && 
                      isset($_FILES['htbl_image']) && 
                      $_FILES['htbl_image']['error'] === UPLOAD_ERR_OK;
        
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
            if ($oldImage && $oldImage != $newImageName) {
                $this->deleteOldImage($oldImage);
            }
        }
        
        // بناء استعلام التحديث
        if ($hasNewImage && $newImageName) {
            $sql = "UPDATE header_top_bottom_link SET
                    htbl_button_text = ?,
                    htbl_button_link = ?,
                    htbl_image = ?,
                    htbl_updated_date = NOW(),
                    htbl_status = ?
                    WHERE htbl_id = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssi", 
                $this->htbl_button_text,
                $this->htbl_button_link,
                $newImageName,
                $this->htbl_status,
                $this->htbl_id
            );
        } else {
            $sql = "UPDATE header_top_bottom_link SET
                    htbl_button_text = ?,
                    htbl_button_link = ?,
                    htbl_updated_date = NOW(),
                    htbl_status = ?
                    WHERE htbl_id = ?";
            
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", 
                $this->htbl_button_text,
                $this->htbl_button_link,
                $this->htbl_status,
                $this->htbl_id
            );
        }
        
        if (mysqli_stmt_execute($stmt)) {
            $affected = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            
            $this->msg = '<div class="alert alert-success">✅ تم تحديث إعدادات الرأس بنجاح</div>';
            return true;
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
}

// معالجة الرسائل من الجلسة
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// إنشاء كائن وعرض البيانات الحالية
$ob = new editproduct();
$row = $ob->detailsObj();

if (!$row) {
    $_SESSION['error'] = 'لم يتم العثور على البيانات';
    header("Location: dashboard.php");
    exit();
}

// معالجة النموذج عند الإرسال
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    
    $ob->setStatus($_POST['htbl_status'] ?? '0');
    $ob->setButtonText($_POST['htbl_button_text'] ?? '');
    $ob->setButtonLink($_POST['htbl_button_link'] ?? '');
    
    if (isset($_FILES['htbl_image']) && $_FILES['htbl_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $ob->setImage($_FILES['htbl_image']['name'] ?? '');
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
    header("Location: header_top_view.php");
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

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    // تكوين TinyMCE (إذا لزم الأمر)
</script>

<script type="text/javascript">
// دالة التحقق من النموذج
function myvalid() {
    var button_text = $('#htbl_button_text').val().trim();
    var button_link = $('#htbl_button_link').val().trim();
    
    var message = "";
    var valid = true;
    
    if(button_text === "") {
        message = "الرجاء إدخال نص الزر";
        $('#htbl_button_text').focus();
        valid = false;
    } else if(button_link === "") {
        message = "الرجاء إدخال رابط الزر";
        $('#htbl_button_link').focus();
        valid = false;
    } else {
        // التحقق من صحة الرابط
        var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
        if(!urlPattern.test(button_link)) {
            message = "الرجاء إدخال رابط صحيح";
            $('#htbl_button_link').focus();
            valid = false;
        }
    }
    
    if(!valid) {
        $('#message').html('<div class="alert alert-danger"><i class="icon-remove"></i> ' + message + '</div>');
        return false;
    }
    
    return confirm('هل أنت متأكد من حفظ التغييرات؟');
}

// معاينة الصورة قبل الرفع
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            $('#imagePreview').html(
                '<div class="preview-container">' +
                '<h4>معاينة الصورة الجديدة:</h4>' +
                '<img src="' + e.target.result + '" style="max-width: 378px; max-height: 117px; border: 2px solid #4CAF50; padding: 3px;"/>' +
                '</div>'
            );
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        $('#imagePreview').empty();
    }
}

$(document).ready(function() {
    // تفعيل معاينة الصورة
    $('#htbl_image').change(function() {
        previewImage(this);
    });
    
    // التحقق من الرابط عند الكتابة
    $('#htbl_button_link').on('blur', function() {
        var link = $(this).val().trim();
        if(link && !/^https?:\/\//i.test(link)) {
            $(this).val('http://' + link);
        }
    });
});
</script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <h2>
                <i class="icon-edit"></i> 
                إدارة الرأس › تعديل إعدادات <?php echo htmlspecialchars($row->htbl_field ?? 'الرأس'); ?>
            </h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onSubmit="return myvalid();">
                
                <em style="display:block; margin:5px;">
                    الحقول التي تحمل <span class="required">*</span> إجبارية
                </em>
                
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
                                                        <input type="radio" name="htbl_status" value="1" <?php echo ($row->htbl_status == '1') ? 'checked' : ''; ?>>
                                                        <span class="label label-success">نشط</span>
                                                    </label>
                                                    <label class="radio-inline">
                                                        <input type="radio" name="htbl_status" value="0" <?php echo ($row->htbl_status == '0') ? 'checked' : ''; ?>>
                                                        <span class="label label-default">غير نشط</span>
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <!-- نص الزر -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">نص الزر: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <input type="text" name="htbl_button_text" id="htbl_button_text" 
                                                           class="form-control" 
                                                           value="<?php echo htmlspecialchars($row->htbl_button_text ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           maxlength="100" />
                                                </div>
                                            </div>
                                            
                                            <!-- رابط الزر -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">رابط الزر: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <input type="url" name="htbl_button_link" id="htbl_button_link" 
                                                           class="form-control" 
                                                           value="<?php echo htmlspecialchars($row->htbl_button_link ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           maxlength="255" placeholder="https://example.com" />
                                                    <span class="help-block">يجب أن يبدأ بـ http:// أو https://</span>
                                                </div>
                                            </div>
                                            
                                            <!-- الصورة الحالية -->
                                            <?php if (!empty($row->htbl_image)): ?>
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">الصورة الحالية:</label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <div class="current-image">
                                                        <img src="../upload/slider/<?php echo htmlspecialchars($row->htbl_image); ?>" 
                                                             style="max-width: 378px; max-height: 117px; border: 1px solid #ddd; padding: 5px;"/>
                                                        <br>
                                                        <span class="help-block"><?php echo htmlspecialchars($row->htbl_image); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- رفع صورة جديدة -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">تغيير الصورة:</label>
                                                <div class="formInputBox" style="width:587px;height:auto;">
                                                    <input type="file" name="htbl_image" id="htbl_image" 
                                                           class="form-control" accept=".jpg,.jpeg,.png,.gif" />
                                                    <span class="help-block">
                                                        <i class="icon-info-sign"></i>
                                                        الأنواع المسموحة: JPG, PNG, GIF (الحد الأقصى 2 ميجابايت)
                                                        <br>الأبعاد الموصى بها: 378 × 117 بكسل
                                                    </span>
                                                    
                                                    <!-- معاينة الصورة الجديدة -->
                                                    <div id="imagePreview" style="margin-top: 15px;"></div>
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
                </div>
                
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<!-- نهاية ملف header_top_view.php - الإصدار 2.0.0 -->
</body>
</html>