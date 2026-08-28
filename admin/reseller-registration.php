<?php
/**
 * File: reseller-registration.php
 * Version: 2.0.0
 * PHP Version: 8.3
 * 
 * Description: إضافة موزع جديد - نموذج تسجيل الموزعين
 * Add New Reseller - Reseller registration form
 * 
 * Features:
 * - تسجيل موزع جديد بكل البيانات المطلوبة
 * - التحقق من صحة المدخلات
 * - التحقق من تطابق كلمة المرور
 * - رفع شعار الموزع أو إنشاء شعار تلقائي
 * - تشفير كلمة المرور باستخدام MD5
 * - معالجة الصور وتحويلها
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

/**
 * Class ResellerRegistration
 * 
 * Handles reseller registration operations
 */
class ResellerRegistration {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Success/Error message */
    private string $msg = '';
    
    /** @var array Form data */
    private array $formData = [];
    
    /** @var array Error messages */
    private array $errors = [];
    
    /** @var array Success messages */
    private array $success = [];
    
    /** @var array Allowed file types for logo */
    private array $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    /** @var array Allowed extensions */
    private array $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
    
    /** @var int Max file size (2MB) */
    private int $maxFileSize = 2 * 1024 * 1024;
    
    /** @var string Upload directory */
    private string $uploadDir = '../upload/reseller_logos/';
    
    /** @var string Font path for text logo */
    private string $fontPath = '../font/hf.ttf';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Set form data
     * 
     * @param array $data Form data
     */
    public function setFormData(array $data): void {
        $this->formData = [
            'fullname' => $this->sanitize($data['reseller_fullname'] ?? ''),
            'username' => $this->sanitize($data['reseller_uname'] ?? ''),
            'email' => $this->sanitize($data['reseller_email'] ?? ''),
            'domain' => $this->sanitize($data['reseller_domain'] ?? ''),
            'discount' => $this->validateNumber($data['reseller_discount'] ?? 0),
            'website' => $this->sanitize($data['reseller_website'] ?? ''),
            'terms' => $this->sanitizeHtml($data['reseller_terms'] ?? ''),
            'password' => $data['reseller_pass'] ?? '',
            'confirm_password' => $data['reseller_confirmpass'] ?? ''
        ];
        
        // Store in session for form persistence
        $_SESSION['reseller_fullname'] = $this->formData['fullname'];
        $_SESSION['reseller_uname'] = $this->formData['username'];
        $_SESSION['reseller_email'] = $this->formData['email'];
        $_SESSION['reseller_domain'] = $this->formData['domain'];
        $_SESSION['reseller_discount'] = $this->formData['discount'];
        $_SESSION['reseller_website'] = $this->formData['website'];
        $_SESSION['reseller_terms'] = $this->formData['terms'];
    }
    
    /**
     * Sanitize input string
     * 
     * @param string $str Input string
     * @return string Sanitized string
     */
    private function sanitize(string $str): string {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize HTML content (allow some tags)
     * 
     * @param string $html HTML content
     * @return string Sanitized HTML
     */
    private function sanitizeHtml(string $html): string {
        $allowedTags = '<p><br><strong><b><em><i><u><ol><ul><li>';
        return strip_tags(trim($html), $allowedTags);
    }
    
    /**
     * Validate and sanitize number
     * 
     * @param mixed $value Input value
     * @return float Validated number
     */
    private function validateNumber($value): float {
        $number = filter_var($value, FILTER_VALIDATE_FLOAT, [
            'options' => ['min_range' => 0, 'max_range' => 100]
        ]);
        return $number !== false ? $number : 0;
    }
    
    /**
     * Validate email
     * 
     * @param string $email Email address
     * @return bool True if valid
     */
    private function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate website URL
     * 
     * @param string $url Website URL
     * @return bool True if valid
     */
    private function validateUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Check if username already exists
     * 
     * @param string $username Username to check
     * @return bool True if exists
     */
    private function usernameExists(string $username): bool {
        $sql = "SELECT COUNT(*) as count FROM reseller WHERE reseller_uname = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Check if email already exists
     * 
     * @param string $email Email to check
     * @return bool True if exists
     */
    private function emailExists(string $email): bool {
        $sql = "SELECT COUNT(*) as count FROM reseller WHERE reseller_email = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Validate uploaded logo file
     * 
     * @param array $file $_FILES array element
     * @return bool True if valid
     */
    private function validateLogo(array $file): bool {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $this->errors[] = 'خطأ في رفع الملف';
            }
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
     * Process uploaded logo
     * 
     * @param array $file $_FILES array element
     * @return string|false File content or false on failure
     */
    private function processUploadedLogo(array $file) {
        if (!$this->validateLogo($file)) {
            return false;
        }
        
        return file_get_contents($file['tmp_name']);
    }
    
    /**
     * Create text-based logo from website name
     * 
     * @param string $website Website name/URL
     * @return string|false PNG image content or false on failure
     */
    private function createTextLogo(string $website) {
        // Extract domain name for logo
        $parsed = parse_url($website);
        $domain = $parsed['host'] ?? $website;
        $domain = preg_replace('/^www\./', '', $domain);
        $text = substr($domain, 0, 20); // Limit text length
        
        // Check if font exists
        if (!file_exists($this->fontPath)) {
            // Fallback to simple text image without font
            $img = imagecreate(200, 100);
            $bg = imagecolorallocate($img, 51, 51, 51);
            $textColor = imagecolorallocate($img, 255, 255, 255);
            imagestring($img, 5, 20, 40, $text, $textColor);
            
            ob_start();
            imagepng($img);
            $imageData = ob_get_clean();
            imagedestroy($img);
            
            return $imageData;
        }
        
        try {
            // Calculate text dimensions
            $size = 30;
            $bbox = imagettfbbox($size, 0, $this->fontPath, $text);
            
            $width = abs($bbox[2] - $bbox[0]) + 40;
            $height = abs($bbox[7] - $bbox[1]) + 40;
            
            // Create image
            $image = imagecreatetruecolor($width, $height);
            
            // Colors
            $bgColor = imagecolorallocate($image, 51, 51, 51);
            $textColor = imagecolorallocate($image, 255, 255, 255);
            
            // Fill background
            imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgColor);
            
            // Calculate text position
            $textX = ($width - ($bbox[2] - $bbox[0])) / 2;
            $textY = ($height - ($bbox[7] - $bbox[1])) / 2 + ($bbox[7] - $bbox[1]);
            
            // Add text
            imagettftext($image, $size, 0, $textX, $textY, $textColor, $this->fontPath, $text);
            
            // Output as PNG
            ob_start();
            imagepng($image);
            $imageData = ob_get_clean();
            imagedestroy($image);
            
            return $imageData;
            
        } catch (Exception $e) {
            // Fallback to simple image
            $img = imagecreate(200, 100);
            $bg = imagecolorallocate($img, 51, 51, 51);
            $textColor = imagecolorallocate($img, 255, 255, 255);
            imagestring($img, 5, 20, 40, $text, $textColor);
            
            ob_start();
            imagepng($img);
            $imageData = ob_get_clean();
            imagedestroy($img);
            
            return $imageData;
        }
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        $this->errors = [];
        
        // Validate fullname
        if (empty($this->formData['fullname'])) {
            $this->errors[] = 'الرجاء إدخال الاسم الكامل';
        }
        
        // Validate username
        if (empty($this->formData['username'])) {
            $this->errors[] = 'الرجاء إدخال اسم المستخدم';
        } elseif (strlen($this->formData['username']) < 3) {
            $this->errors[] = 'اسم المستخدم يجب أن يكون 3 أحرف على الأقل';
        } elseif ($this->usernameExists($this->formData['username'])) {
            $this->errors[] = 'اسم المستخدم موجود مسبقاً';
        }
        
        // Validate email
        if (empty($this->formData['email'])) {
            $this->errors[] = 'الرجاء إدخال البريد الإلكتروني';
        } elseif (!$this->validateEmail($this->formData['email'])) {
            $this->errors[] = 'البريد الإلكتروني غير صحيح';
        } elseif ($this->emailExists($this->formData['email'])) {
            $this->errors[] = 'البريد الإلكتروني موجود مسبقاً';
        }
        
        // Validate domain
        if (empty($this->formData['domain'])) {
            $this->errors[] = 'الرجاء إدخال النطاق';
        }
        
        // Validate discount
        if ($this->formData['discount'] < 0 || $this->formData['discount'] > 100) {
            $this->errors[] = 'نسبة الخصم يجب أن تكون بين 0 و 100';
        }
        
        // Validate website
        if (empty($this->formData['website'])) {
            $this->errors[] = 'الرجاء إدخال الموقع الإلكتروني';
        } elseif (!$this->validateUrl($this->formData['website'])) {
            $this->errors[] = 'الموقع الإلكتروني غير صحيح (يجب أن يبدأ بـ http:// أو https://)';
        }
        
        // Validate password
        if (empty($this->formData['password'])) {
            $this->errors[] = 'الرجاء إدخال كلمة المرور';
        } elseif (strlen($this->formData['password']) < 6) {
            $this->errors[] = 'كلمة المرور يجب أن تكون 6 أحرف على الأقل';
        } elseif ($this->formData['password'] !== $this->formData['confirm_password']) {
            $this->errors[] = 'كلمة المرور غير متطابقة';
        }
        
        return empty($this->errors);
    }
    
    /**
     * Register new reseller
     * 
     * @param array $file Uploaded file data
     * @return bool Success status
     */
    public function register(array $file): bool {
        if (!$this->validate()) {
            return false;
        }
        
        // Process logo
        $logoContent = null;
        
        if (isset($file['reseller_logo']) && $file['reseller_logo']['error'] === UPLOAD_ERR_OK) {
            $logoContent = $this->processUploadedLogo($file['reseller_logo']);
        }
        
        // Create text-based logo if no logo uploaded
        if ($logoContent === false) {
            return false;
        }
        
        if ($logoContent === null) {
            $logoContent = $this->createTextLogo($this->formData['website']);
        }
        
        if ($logoContent === false) {
            $this->errors[] = 'فشل في إنشاء الشعار';
            return false;
        }
        
        // Hash password
        $hashedPassword = md5($this->formData['password']);
        
        // Insert into database
        $sql = "INSERT INTO reseller SET
                reseller_fullname = ?,
                reseller_uname = ?,
                reseller_pass = ?,
                reseller_email = ?,
                reseller_domain = ?,
                reseller_website = ?,
                reseller_discount = ?,
                reseller_terms = ?,
                reseller_logo = ?,
                reseller_creation_date = NOW()";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->errors[] = "Database error: " . mysqli_error($this->db);
            return false;
        }
        
        $null = null;
        mysqli_stmt_bind_param($stmt, "ssssssdsb", 
            $this->formData['fullname'],
            $this->formData['username'],
            $hashedPassword,
            $this->formData['email'],
            $this->formData['domain'],
            $this->formData['website'],
            $this->formData['discount'],
            $this->formData['terms'],
            $null
        );
        
        // Send logo as BLOB
        mysqli_stmt_send_long_data($stmt, 8, $logoContent);
        
        $result = mysqli_stmt_execute($stmt);
        
        if ($result) {
            $this->success[] = "تم إضافة الموزع بنجاح";
            $this->logActivity('add', mysqli_insert_id($this->db));
            
            // Clear session data
            unset($_SESSION['reseller_fullname']);
            unset($_SESSION['reseller_uname']);
            unset($_SESSION['reseller_email']);
            unset($_SESSION['reseller_domain']);
            unset($_SESSION['reseller_discount']);
            unset($_SESSION['reseller_website']);
            unset($_SESSION['reseller_terms']);
            
            return true;
        } else {
            $this->errors[] = "فشل في إضافة الموزع: " . mysqli_error($this->db);
            return false;
        }
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
                VALUES (?, ?, 'reseller', ?, ?, NOW())";
        
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
     * Get message
     * 
     * @return string Message
     */
    public function getMessage(): string {
        return $this->msg;
    }
}

// Get session data
$reseller_fullname = $_SESSION['reseller_fullname'] ?? '';
$reseller_uname = $_SESSION['reseller_uname'] ?? '';
$reseller_email = $_SESSION['reseller_email'] ?? '';
$reseller_domain = $_SESSION['reseller_domain'] ?? '';
$reseller_discount = $_SESSION['reseller_discount'] ?? '';
$reseller_website = $_SESSION['reseller_website'] ?? '';
$reseller_terms = $_SESSION['reseller_terms'] ?? '';

// Initialize registration handler
$registration = new ResellerRegistration($con);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btnAdd'])) {
    
    $registration->setFormData($_POST);
    
    if ($registration->register($_FILES)) {
        $_SESSION['reseller_success'] = $registration->getSuccess();
    } else {
        $_SESSION['reseller_errors'] = $registration->getErrors();
    }
    
    header("Location: reseller-registration.php");
    exit();
}

// Get messages from session
$errorMessages = $_SESSION['reseller_errors'] ?? [];
$successMessages = $_SESSION['reseller_success'] ?? [];
unset($_SESSION['reseller_errors'], $_SESSION['reseller_success']);
?>

<?php include "includes/admin-top.php" ?>

<style>
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
        margin-bottom: 15px;
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
        transition: border-color 0.3s;
    }
    .form-control:focus {
        border-color: #4CAF50;
        outline: none;
    }
    .form-control.error {
        border-color: #f00;
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
        padding: 12px 30px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s;
    }
    .btn-submit:hover {
        background-color: #45a049;
    }
    .btn-submit i {
        margin-left: 5px;
    }
    .password-strength {
        height: 5px;
        margin-top: 5px;
        border-radius: 3px;
        transition: all 0.3s;
    }
    .password-strength.weak {
        width: 33%;
        background-color: #f00;
    }
    .password-strength.medium {
        width: 66%;
        background-color: #ff0;
    }
    .password-strength.strong {
        width: 100%;
        background-color: #0f0;
    }
    .logo-preview {
        max-width: 200px;
        max-height: 200px;
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 10px;
        margin-top: 10px;
        display: none;
    }
    .logo-preview img {
        max-width: 100%;
        max-height: 180px;
    }
</style>

<script src="js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        mode: "textareas",
        theme: "advanced",
        language: "ar",
        plugins: "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
        theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
        theme_advanced_toolbar_location: "top",
        theme_advanced_toolbar_align: "left",
        theme_advanced_statusbar_location: "bottom",
        theme_advanced_resizing: true,
        content_css: "css/content.css",
        forced_root_block: false,
        force_p_newlines: false,
        remove_linebreaks: false,
        force_br_newlines: true,
        remove_trailing_nbsp: false,
        verify_html: false,
        directionality: "rtl"
    });
</script>

<script type="text/javascript">
$(document).ready(function() {
    
    // Real-time validation
    $('#reseller_fullname').on('input', function() {
        validateField($(this), $(this).val().trim() !== '');
    });
    
    $('#reseller_uname').on('input', function() {
        var value = $(this).val().trim();
        validateField($(this), value.length >= 3);
    });
    
    $('#reseller_email').on('input', function() {
        var value = $(this).val().trim();
        var isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        validateField($(this), isValid);
    });
    
    $('#reseller_domain').on('input', function() {
        validateField($(this), $(this).val().trim() !== '');
    });
    
    $('#reseller_discount').on('input', function() {
        var value = parseFloat($(this).val());
        var isValid = !isNaN(value) && value >= 0 && value <= 100;
        validateField($(this), isValid);
    });
    
    $('#reseller_website').on('input', function() {
        var value = $(this).val().trim();
        var isValid = value === '' || /^https?:\/\/.+\..+/.test(value);
        validateField($(this), isValid);
    });
    
    // Password strength indicator
    $('#reseller_pass').on('input', function() {
        var password = $(this).val();
        var strength = calculatePasswordStrength(password);
        
        var $strengthBar = $('.password-strength');
        $strengthBar.removeClass('weak medium strong');
        
        if (strength === 0) {
            $strengthBar.css('width', '0%');
        } else if (strength < 3) {
            $strengthBar.addClass('weak');
        } else if (strength < 5) {
            $strengthBar.addClass('medium');
        } else {
            $strengthBar.addClass('strong');
        }
    });
    
    // Confirm password validation
    $('#reseller_confirmpass').on('input', function() {
        var password = $('#reseller_pass').val();
        var confirm = $(this).val();
        validateField($(this), password === confirm && confirm !== '');
    });
    
    // Logo preview
    $('#reseller_logo').on('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                $('#logo-preview-img').attr('src', e.target.result);
                $('.logo-preview').fadeIn();
            }
            
            reader.readAsDataURL(e.target.files[0]);
            
            // Validate file size
            var fileSize = e.target.files[0].size / 1024 / 1024;
            if (fileSize > 2) {
                alert('حجم الملف كبير جداً. الحد الأقصى 2 ميجابايت');
                $(this).val('');
                $('.logo-preview').fadeOut();
            }
        }
    });
    
    // Form submission
    $('#reseller-form').on('submit', function(e) {
        tinyMCE.triggerSave();
        
        var isValid = true;
        
        // Validate all fields
        if ($('#reseller_fullname').val().trim() === '') {
            showError('الرجاء إدخال الاسم الكامل');
            isValid = false;
        } else if ($('#reseller_uname').val().trim().length < 3) {
            showError('اسم المستخدم يجب أن يكون 3 أحرف على الأقل');
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('#reseller_email').val().trim())) {
            showError('البريد الإلكتروني غير صحيح');
            isValid = false;
        } else if ($('#reseller_domain').val().trim() === '') {
            showError('الرجاء إدخال النطاق');
            isValid = false;
        } else {
            var discount = parseFloat($('#reseller_discount').val());
            if (isNaN(discount) || discount < 0 || discount > 100) {
                showError('نسبة الخصم يجب أن تكون بين 0 و 100');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Helper functions
    function validateField($field, isValid) {
        if (isValid) {
            $field.removeClass('error').addClass('valid');
        } else {
            $field.removeClass('valid').addClass('error');
        }
    }
    
    function calculatePasswordStrength(password) {
        var strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        return strength;
    }
    
    function showError(message) {
        $('#message').html('<div class="alert alert-danger"><i class="icon-remove"></i> ' + message + '</div>');
    }
});
</script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            
            <h2>
                <i class="icon-user"></i> 
                إدارة الموزعين › إضافة موزع جديد
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
            
            <div id="message"></div>
            
            <form action="" id="reseller-form" name="reseller-form" method="post" enctype="multipart/form-data">
                
                <div class="form-container">
                    
                    <!-- Personal Information -->
                    <div class="form-section">
                        <h3><i class="icon-user"></i> المعلومات الشخصية</h3>
                        
                        <div class="form-group">
                            <label>الاسم الكامل <span class="required">*</span></label>
                            <input type="text" name="reseller_fullname" id="reseller_fullname" 
                                   class="form-control" value="<?php echo htmlspecialchars($reseller_fullname); ?>" 
                                   maxlength="255" placeholder="أدخل الاسم الكامل">
                        </div>
                        
                        <div class="form-group">
                            <label>اسم المستخدم <span class="required">*</span></label>
                            <input type="text" name="reseller_uname" id="reseller_uname" 
                                   class="form-control" value="<?php echo htmlspecialchars($reseller_uname); ?>" 
                                   maxlength="50" placeholder="أدخل اسم المستخدم">
                            <div class="help-text">يجب أن يكون 3 أحرف على الأقل</div>
                        </div>
                        
                        <div class="form-group">
                            <label>البريد الإلكتروني <span class="required">*</span></label>
                            <input type="email" name="reseller_email" id="reseller_email" 
                                   class="form-control" value="<?php echo htmlspecialchars($reseller_email); ?>" 
                                   maxlength="255" placeholder="example@domain.com">
                        </div>
                    </div>
                    
                    <!-- Business Information -->
                    <div class="form-section">
                        <h3><i class="icon-briefcase"></i> معلومات النشاط التجاري</h3>
                        
                        <div class="form-group">
                            <label>النطاق <span class="required">*</span></label>
                            <input type="text" name="reseller_domain" id="reseller_domain" 
                                   class="form-control" value="<?php echo htmlspecialchars($reseller_domain); ?>" 
                                   maxlength="255" placeholder="مثال: mybusiness.com">
                        </div>
                        
                        <div class="form-group">
                            <label>نسبة الخصم (%) <span class="required">*</span></label>
                            <input type="number" name="reseller_discount" id="reseller_discount" 
                                   class="form-control" value="<?php echo htmlspecialchars($reseller_discount); ?>" 
                                   min="0" max="100" step="0.01" placeholder="0 - 100">
                        </div>
                        
                        <div class="form-group">
                            <label>الموقع الإلكتروني <span class="required">*</span></label>
                            <input type="url" name="reseller_website" id="reseller_website" 
                                   class="form-control" value="<?php echo htmlspecialchars($reseller_website); ?>" 
                                   maxlength="255" placeholder="https://example.com">
                            <div class="help-text">يجب أن يبدأ بـ http:// أو https://</div>
                        </div>
                        
                        <div class="form-group">
                            <label>الشروط والأحكام</label>
                            <textarea name="reseller_terms" id="reseller_terms" 
                                      class="form-control" rows="6"><?php echo htmlspecialchars($reseller_terms); ?></textarea>
                            <div class="help-text">يمكن تركها فارغة إذا لم تكن مطلوبة</div>
                        </div>
                    </div>
                    
                    <!-- Security Information -->
                    <div class="form-section">
                        <h3><i class="icon-lock"></i> معلومات الدخول</h3>
                        
                        <div class="form-group">
                            <label>كلمة المرور <span class="required">*</span></label>
                            <input type="password" name="reseller_pass" id="reseller_pass" 
                                   class="form-control" value="" placeholder="********">
                            <div class="password-strength"></div>
                            <div class="help-text">يجب أن تكون 6 أحرف على الأقل</div>
                        </div>
                        
                        <div class="form-group">
                            <label>تأكيد كلمة المرور <span class="required">*</span></label>
                            <input type="password" name="reseller_confirmpass" id="reseller_confirmpass" 
                                   class="form-control" value="" placeholder="********">
                        </div>
                    </div>
                    
                    <!-- Logo Upload -->
                    <div class="form-section">
                        <h3><i class="icon-picture"></i> شعار الموزع</h3>
                        
                        <div class="form-group">
                            <label>رفع شعار</label>
                            <input type="file" name="reseller_logo" id="reseller_logo" 
                                   accept=".jpg,.jpeg,.png,.gif">
                            <div class="help-text">
                                <i class="icon-info-sign"></i>
                                الصيغ المسموحة: JPG, PNG, GIF (الحد الأقصى 2 ميجابايت)
                                <br>إذا لم يتم رفع شعار، سيتم إنشاء شعار نصي تلقائي من اسم الموقع
                            </div>
                            
                            <div class="logo-preview" id="logo-preview">
                                <img id="logo-preview-img" src="#" alt="Logo Preview">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="form-section" style="text-align:center;">
                        <button type="submit" name="btnAdd" id="btnAdd" class="btn-submit">
                            <i class="icon-plus"></i> إضافة موزع
                        </button>
                    </div>
                    
                </div>
                
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>

<br clear="all" />

<?php include "includes/footer.php" ?>

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
?>

<!-- نهاية ملف reseller-registration.php - الإصدار 2.0.0 -->
</body>
</html>

<?php ob_end_flush(); ?>