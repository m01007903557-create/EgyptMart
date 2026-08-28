<?php
/**
 * File: reseller-logo.php
 * Version: 2.0.0
 * PHP Version: 8.3
 * 
 * Description: إدارة شعار الموزع - رفع وتحديث وعرض الشعار
 * Reseller Logo Management - Upload, update and display logo
 * 
 * Features:
 * - عرض الشعار الحالي
 * - رفع شعار جديد
 * - التحقق من صحة نوع وحجم الملف
 * - معالجة الصور وتغيير حجمها
 * - دعم تنسيقات PNG, JPG, JPEG, GIF
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "../common.php";

// Check if user is logged in
check_user_login();

// Get reseller ID from session
$reseller_id = $_SESSION['reseller_id'] ?? 0;
if (!$reseller_id) {
    header("Location: login.php");
    exit();
}

// Get currency symbol from settings
$currencySql = mysqli_query($con, "SELECT * FROM site_settings WHERE st_field = 'currency-symbol'");
$currencyRow = mysqli_fetch_object($currencySql);

// Get reseller details
$resellerSql = "SELECT * FROM reseller WHERE reseller_id = ?";
$resellerStmt = mysqli_prepare($con, $resellerSql);
mysqli_stmt_bind_param($resellerStmt, "i", $reseller_id);
mysqli_stmt_execute($resellerStmt);
$resellerResult = mysqli_stmt_get_result($resellerStmt);
$resellerData = mysqli_fetch_object($resellerResult);
mysqli_stmt_close($resellerStmt);

// Get CMS terms
$cmsSql = "SELECT * FROM cms WHERE cms_id = '3'";
$cmsResult = mysqli_query($con, $cmsSql);
$cmsData = mysqli_fetch_object($cmsResult);

/**
 * Class ResellerLogoManager
 * 
 * Handles reseller logo operations
 */
class ResellerLogoManager {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Reseller ID */
    private int $reseller_id;
    
    /** @var string Uploaded logo filename */
    private string $reseller_logo = '';
    
    /** @var string Success message */
    private string $msg = '';
    
    /** @var array Error messages */
    private array $errors = [];
    
    /** @var array Success messages */
    private array $success = [];
    
    /** @var array Allowed file types */
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    /** @var array Allowed extensions */
    private array $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
    
    /** @var int Max file size (2MB) */
    private int $maxFileSize = 2 * 1024 * 1024;
    
    /** @var string Upload directory */
    private string $uploadDir = '../upload/reseller_logos/';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $resellerId Reseller ID
     */
    public function __construct(mysqli $database, int $resellerId) {
        $this->db = $database;
        $this->reseller_id = $resellerId;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Get reseller details
     * 
     * @return object|null Reseller data or null if not found
     */
    public function getDetails(): ?object {
        $sql = "SELECT * FROM reseller WHERE reseller_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "Database error: " . mysqli_error($this->db);
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->reseller_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Set logo filename
     * 
     * @param string $filename Logo filename
     */
    public function setLogo(string $filename): void {
        $this->reseller_logo = $this->sanitizeFilename($filename);
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file $_FILES array element
     * @return bool True if valid
     */
    public function validateFile(array $file): bool {
        // Check if file was uploaded
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'الملف أكبر من الحد المسموح به في السيرفر',
                UPLOAD_ERR_FORM_SIZE => 'الملف أكبر من الحد المسموح به في النموذج',
                UPLOAD_ERR_PARTIAL => 'تم رفع جزء فقط من الملف',
                UPLOAD_ERR_NO_FILE => 'الرجاء اختيار ملف',
                UPLOAD_ERR_NO_TMP_DIR => 'المجلد المؤقت غير موجود',
                UPLOAD_ERR_CANT_WRITE => 'فشل في كتابة الملف على القرص',
                UPLOAD_ERR_EXTENSION => 'امتداد الملف غير مسموح به'
            ];
            $this->errors[] = $uploadErrors[$file['error']] ?? 'خطأ غير معروف في الرفع';
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->errors[] = 'حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت';
            return false;
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'نوع الملف غير مسموح به. الأنواع المسموحة: JPG, PNG, GIF';
            return false;
        }
        
        // Check extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedExt)) {
            $this->errors[] = 'امتداد الملف غير مسموح به';
            return false;
        }
        
        return true;
    }
    
    /**
     * Process and save uploaded logo
     * 
     * @param array $file $_FILES array element
     * @return string|false New filename or false on failure
     */
    public function processLogo(array $file) {
        if (!$this->validateFile($file)) {
            return false;
        }
        
        // Generate unique filename
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newFilename = 'reseller_' . $this->reseller_id . '_' . time() . '.' . $ext;
        $targetPath = $this->uploadDir . $newFilename;
        
        // Process image (resize if needed)
        if (class_exists('SimpleImage')) {
            try {
                $image = new SimpleImage();
                $image->load($file['tmp_name']);
                
                // Resize if image is too large (max 200x200)
                if ($image->getWidth() > 200 || $image->getHeight() > 200) {
                    $image->resize(200, 200);
                }
                
                $image->save($targetPath);
            } catch (Exception $e) {
                // Fallback to simple move if SimpleImage fails
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $this->errors[] = 'فشل في حفظ الملف';
                    return false;
                }
            }
        } else {
            // SimpleImage not available, just move the file
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $this->errors[] = 'فشل في حفظ الملف';
                return false;
            }
        }
        
        return $newFilename;
    }
    
    /**
     * Update reseller logo in database
     * 
     * @param string $filename New logo filename
     * @return bool Success status
     */
    public function updateLogo(string $filename): bool {
        if (empty($filename)) {
            $this->errors[] = 'اسم الملف غير صالح';
            return false;
        }
        
        // Get old logo to delete later
        $oldSql = "SELECT reseller_logo_file FROM reseller WHERE reseller_id = ?";
        $oldStmt = mysqli_prepare($this->db, $oldSql);
        mysqli_stmt_bind_param($oldStmt, "i", $this->reseller_id);
        mysqli_stmt_execute($oldStmt);
        $oldResult = mysqli_stmt_get_result($oldStmt);
        $oldData = mysqli_fetch_assoc($oldResult);
        mysqli_stmt_close($oldStmt);
        
        // Update database with file path
        $sql = "UPDATE reseller SET reseller_logo_file = ?, reseller_logo = NULL, updated_at = NOW() WHERE reseller_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "Database error: " . mysqli_error($this->db);
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "si", $filename, $this->reseller_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        if ($result) {
            // Delete old logo file
            if ($oldData && !empty($oldData['reseller_logo_file'])) {
                $oldPath = $this->uploadDir . $oldData['reseller_logo_file'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $this->success[] = "تم تحديث الشعار بنجاح";
            $this->logActivity('update_logo', $this->reseller_id);
            return true;
        }
        
        $this->errors[] = "فشل تحديث قاعدة البيانات";
        return false;
    }
    
    /**
     * Get logo as base64 for display (fallback for old data)
     * 
     * @param string|null $binaryData Binary image data
     * @return string Base64 encoded image
     */
    public function getLogoBase64(?string $binaryData): string {
        if (empty($binaryData)) {
            return '';
        }
        return 'data:image/jpeg;base64,' . base64_encode($binaryData);
    }
    
    /**
     * Sanitize filename
     * 
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string {
        $filename = basename($filename);
        return preg_replace('/[^a-zA-Z0-9\-\._]/', '', $filename);
    }
    
    /**
     * Log activity
     * 
     * @param string $action Action performed
     * @param int $itemId Item ID
     */
    private function logActivity(string $action, int $itemId): void {
        $userId = $_SESSION['admin_id'] ?? 0;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        
        $sql = "INSERT INTO activity_log (user_id, action, item_type, item_id, ip_address, created_at) 
                VALUES (?, ?, 'reseller_logo', ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isis", $userId, $action, $itemId, $ipAddress);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Get error messages
     * 
     * @return array Error messages
     */
    public function getErrors(): array {
        return $this->errors;
    }
    
    /**
     * Get success messages
     * 
     * @return array Success messages
     */
    public function getSuccess(): array {
        return $this->success;
    }
    
    /**
     * Clear messages
     */
    public function clearMessages(): void {
        $this->errors = [];
        $this->success = [];
    }
}

// Initialize manager
$manager = new ResellerLogoManager($con, $reseller_id);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnUpdate'])) {
    
    if (isset($_FILES['reseller_logo']) && $_FILES['reseller_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $processedFile = $manager->processLogo($_FILES['reseller_logo']);
        
        if ($processedFile) {
            $manager->updateLogo($processedFile);
        }
    } else {
        $manager->getErrors()[] = 'الرجاء اختيار ملف';
    }
    
    // Store messages in session
    if (!empty($manager->getErrors())) {
        $_SESSION['reseller_errors'] = $manager->getErrors();
    }
    if (!empty($manager->getSuccess())) {
        $_SESSION['reseller_success'] = $manager->getSuccess();
    }
    
    header("Location: reseller-logo.php");
    exit();
}

// Get reseller data
$reseller = $manager->getDetails();

// Get messages from session
$errorMessages = $_SESSION['reseller_errors'] ?? [];
$successMessages = $_SESSION['reseller_success'] ?? [];
unset($_SESSION['reseller_errors'], $_SESSION['reseller_success']);
?>

<?php include "includes/admin-top.php" ?>

<style>
    .logo-container {
        text-align: center;
        padding: 20px;
        background: #f9f9f9;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .current-logo {
        max-width: 200px;
        max-height: 200px;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 5px;
        background: white;
        margin-bottom: 15px;
    }
    .logo-preview {
        max-width: 200px;
        max-height: 200px;
        border: 2px dashed #4CAF50;
        border-radius: 8px;
        padding: 5px;
        margin-top: 15px;
        display: none;
    }
    .file-input-wrapper {
        position: relative;
        margin: 20px 0;
    }
    .file-input {
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        position: absolute;
        z-index: -1;
    }
    .file-label {
        display: inline-block;
        padding: 10px 20px;
        background-color: #4CAF50;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-label:hover {
        background-color: #45a049;
    }
    .file-name {
        margin-right: 10px;
        color: #666;
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
    .btn-update {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 30px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }
    .btn-update:hover {
        background-color: #45a049;
    }
    .btn-update:disabled {
        background-color: #cccccc;
        cursor: not-allowed;
    }
    .info-text {
        color: #666;
        font-size: 13px;
        margin-top: 5px;
    }
    .requirements {
        background: #f5f5f5;
        padding: 10px;
        border-radius: 4px;
        margin-top: 15px;
    }
    .requirements ul {
        margin: 5px 0 0 20px;
        color: #666;
    }
</style>

<script src="js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <h2>
                <i class="icon-picture"></i> 
                إدارة شعار الموزع › تحديث الشعار
            </h2>
            
            <!-- Display messages -->
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
            
            <form action="" id="logo-form" name="logo-form" method="post" enctype="multipart/form-data">
                
                <div class="x2-layout">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:100%; padding:20px;">
                                            
                                            <!-- Current Logo Display -->
                                            <div class="logo-container">
                                                <h3>الشعار الحالي</h3>
                                                <?php if ($reseller): ?>
                                                    <?php if (!empty($reseller->reseller_logo_file)): ?>
                                                        <img src="../upload/reseller_logos/<?php echo htmlspecialchars($reseller->reseller_logo_file); ?>" 
                                                             alt="Current Logo" class="current-logo">
                                                    <?php elseif (!empty($reseller->reseller_logo)): ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($reseller->reseller_logo); ?>" 
                                                             alt="Current Logo" class="current-logo">
                                                    <?php else: ?>
                                                        <div class="alert alert-warning" style="margin:10px;">
                                                            <i class="icon-warning"></i> لا يوجد شعار حالياً
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <!-- File Upload -->
                                            <div class="formItem leftLabel" style="text-align:center;">
                                                <div class="file-input-wrapper">
                                                    <input type="file" name="reseller_logo" id="reseller_logo" 
                                                           class="file-input" accept=".jpg,.jpeg,.png,.gif">
                                                    <label for="reseller_logo" class="file-label">
                                                        <i class="icon-upload"></i> اختر ملفاً
                                                    </label>
                                                    <span class="file-name" id="file-name">لم يتم اختيار ملف</span>
                                                </div>
                                                
                                                <!-- Image Preview -->
                                                <div id="image-preview" class="logo-preview">
                                                    <h4>معاينة الشعار الجديد:</h4>
                                                    <img id="preview-img" src="#" alt="Preview" style="max-width:200px; max-height:200px;">
                                                </div>
                                                
                                                <!-- Requirements Info -->
                                                <div class="requirements">
                                                    <strong>متطلبات الصورة:</strong>
                                                    <ul>
                                                        <li>الحد الأقصى للحجم: 2 ميجابايت</li>
                                                        <li>الصيغ المسموحة: JPG, PNG, GIF</li>
                                                        <li>الأبعاد الموصى بها: 200 × 200 بكسل</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            
                                            <input type="hidden" name="uid" value="<?php echo $reseller_id; ?>">
                                            
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="row buttons" style="text-align:center; margin-top:20px;">
                    <button type="submit" name="btnUpdate" id="btnUpdate" class="btn-update">
                        <i class="icon-save"></i> تحديث الشعار
                    </button>
                </div>
                
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

<script type="text/javascript">
$(document).ready(function() {
    
    // File input change handler
    $('#reseller_logo').on('change', function(e) {
        var fileName = e.target.files[0] ? e.target.files[0].name : 'لم يتم اختيار ملف';
        $('#file-name').text(fileName);
        
        // Preview image
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                $('#preview-img').attr('src', e.target.result);
                $('#image-preview').fadeIn();
            }
            
            reader.readAsDataURL(e.target.files[0]);
            
            // Validate file size
            var fileSize = e.target.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2) {
                alert('حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت');
                $(this).val('');
                $('#file-name').text('لم يتم اختيار ملف');
                $('#image-preview').fadeOut();
            }
            
            // Validate file type
            var fileType = e.target.files[0].type;
            if (!fileType.match('image.*')) {
                alert('الرجاء اختيار ملف صورة صالح');
                $(this).val('');
                $('#file-name').text('لم يتم اختيار ملف');
                $('#image-preview').fadeOut();
            }
        } else {
            $('#image-preview').fadeOut();
        }
    });
    
    // Form submission validation
    $('#logo-form').on('submit', function(e) {
        if ($('#reseller_logo').val() === '') {
            e.preventDefault();
            alert('الرجاء اختيار ملف للرفع');
            return false;
        }
        
        return confirm('هل أنت متأكد من تحديث الشعار؟');
    });
    
    // Drag and drop support
    var dropArea = $('.logo-container');
    
    dropArea.on('drag dragstart dragend dragover dragenter dragleave drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
    
    dropArea.on('dragover dragenter', function() {
        dropArea.addClass('drag-over');
    });
    
    dropArea.on('dragleave dragend drop', function() {
        dropArea.removeClass('drag-over');
    });
    
    dropArea.on('drop', function(e) {
        var files = e.originalEvent.dataTransfer.files;
        $('#reseller_logo')[0].files = files;
        $('#reseller_logo').trigger('change');
    });
});
</script>

<style>
.drag-over {
    background-color: #e8f5e9 !important;
    border: 2px dashed #4CAF50 !important;
}
</style>

<!-- Activity Log Table Creation if needed -->
<?php
// Create activity_log table if it doesn't exist
$checkTable = mysqli_query($con, "SHOW TABLES LIKE 'activity_log'");
if (mysqli_num_rows($checkTable) == 0) {
    $createTable = "CREATE TABLE IF NOT EXISTS `activity_log` (
        `log_id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) DEFAULT NULL,
        `action` varchar(50) NOT NULL,
        `item_type` varchar(50) NOT NULL,
        `item_id` int(11) DEFAULT NULL,
        `ip_address` varchar(45) DEFAULT NULL,
        `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`log_id`),
        KEY `user_id` (`user_id`),
        KEY `item_type` (`item_type`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    mysqli_query($con, $createTable);
}

// Add file column to reseller table if not exists
$checkColumn = mysqli_query($con, "SHOW COLUMNS FROM reseller LIKE 'reseller_logo_file'");
if (mysqli_num_rows($checkColumn) == 0) {
    $alterTable = "ALTER TABLE reseller 
                    ADD COLUMN reseller_logo_file VARCHAR(255) NULL AFTER reseller_logo,
                    ADD COLUMN updated_at DATETIME NULL,
                    ADD INDEX idx_reseller_logo (reseller_logo_file)";
    mysqli_query($con, $alterTable);
}
?>

<!-- نهاية ملف reseller-logo.php - الإصدار 2.0.0 -->
</body>
</html>

<?php ob_end_flush(); ?>