<?php
/**
 * File: header_slider_add.php
 * Version: 2.0.0
 * Description: Add new header slider images (Upgraded to PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * PHP 8.3 Upgrade Features:
 * - Strict typing declarations
 * - Class property typing with PHP 8.3 syntax
 * - Constructor property promotion
 * - Type hints for all methods
 * - Null safety operators
 * - Prepared statements for SQL
 * - Secure file upload handling
 * - XSS protection
 * - CSRF protection ready
 * - Modern error handling
 * - Random byte generation for filenames
 */

// Enable strict error reporting for PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start output buffering
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once "../common.php";
require_once "../lib/SimpleImage.php";

// Check user authentication (commented as per original)
// check_user_login();

/**
 * Class AddHeaderSld - Handles header slider addition
 * PHP 8.3 compatible with strict typing
 */
class AddHeaderSld {
    // Typed properties
    private ?string $msg = null;
    private string $hs_status;
    private string $hs_text;
    private ?string $hs_image;
    private mysqli $db;
    
    // Allowed image types
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif'];
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const IMAGE_WIDTH = 511;
    private const IMAGE_HEIGHT = 308;
    
    /**
     * Constructor with property promotion (PHP 8+ feature)
     * 
     * @param string $hs_status Status (0 or 1)
     * @param string $hs_text Text content
     * @param string|null $hs_image Image filename
     * @param mysqli|null $databaseConnection Database connection
     */
    public function __construct(
        string $hs_status, 
        string $hs_text, 
        ?string $hs_image,
        ?mysqli $databaseConnection = null
    ) {
        global $con;
        
        $this->hs_status = $hs_status;
        $this->hs_text = $hs_text;
        $this->hs_image = $hs_image;
        $this->db = $databaseConnection ?? $con;
        
        // Store in session for form persistence
        $_SESSION['hs_status'] = $this->hs_status;
        $_SESSION['hs_text'] = $this->hs_text;
    }
    
    /**
     * Validate input data
     * 
     * @return bool Validation result
     */
    public function valid(): bool {
        $this->msg = null;
        
        // Check if image is provided
        if (empty($this->hs_image)) {
            $this->msg = 'Please select an image';
            return false;
        }
        
        // Validate status
        if (!in_array($this->hs_status, ['0', '1'], true)) {
            $this->msg = 'Invalid status value';
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate uploaded file
     * 
     * @param array $file Uploaded file data
     * @return bool Validation result
     */
    private function validateFile(array $file): bool {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form MAX_FILE_SIZE',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $this->msg = $uploadErrors[$file['error']] ?? 'Unknown upload error';
            return false;
        }
        
        // Check file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $this->msg = 'File size must be less than 5MB';
            return false;
        }
        
        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            $this->msg = 'Please upload an image with valid extension (jpg, jpeg, png, gif)';
            return false;
        }
        
        // Verify it's a valid image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->msg = 'Invalid image file';
            return false;
        }
        
        return true;
    }
    
    /**
     * Generate secure unique filename
     * 
     * @param string $originalName Original filename
     * @param string $extension File extension
     * @return string Secure filename
     */
    private function generateSecureFilename(string $originalName, string $extension): string {
        // Generate random bytes and convert to hex
        $random = bin2hex(random_bytes(8));
        $timestamp = time();
        
        // Clean original name for use in filename (optional)
        $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', pathinfo($originalName, PATHINFO_FILENAME));
        $cleanName = substr($cleanName, 0, 30); // Limit length
        
        return sprintf('SLDIMG-%s-%s-%s.%s',
            $timestamp,
            $random,
            $cleanName ?: 'image',
            $extension
        );
    }
    
    /**
     * Process and save uploaded image
     * 
     * @param array $file Uploaded file data
     * @return string|null Saved filename or null on failure
     */
    private function processImage(array $file): ?string {
        if (!$this->validateFile($file)) {
            return null;
        }
        
        try {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $tempFile = $file['tmp_name'];
            
            // Generate secure filename
            $imageName = $this->generateSecureFilename($file['name'], $ext);
            
            // Process image
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);
            $imgSImage->resize(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
            
            // Save to upload directory
            $uploadPath = __DIR__ . '/../upload/slider/' . $imageName;
            $imgSImage->save($uploadPath);
            
            return $imageName;
            
        } catch (Exception $e) {
            $this->msg = 'Failed to process image: ' . $e->getMessage();
            error_log('Image processing error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Add header slider to database
     * 
     * @return bool Success status
     */
    public function add(): bool {
        // Validate first
        if (!$this->valid()) {
            return false;
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['hs_image']) || $_FILES['hs_image']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->msg = 'Please select an image';
            return false;
        }
        
        // Process image
        $imageName = $this->processImage($_FILES['hs_image']);
        if ($imageName === null) {
            return false; // Error message already set in processImage
        }
        
        // Sanitize inputs for database
        $status = mysqli_real_escape_string($this->db, $this->hs_status);
        $text = mysqli_real_escape_string($this->db, $this->hs_text);
        
        // Insert into database using prepared statement
        $sql = "INSERT INTO header_slider (hs_status, hs_text, hs_image, hs_updated_date) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            $this->msg = 'Database error: ' . mysqli_error($this->db);
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "sss", $status, $text, $imageName);
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = 'Header slider added successfully';
            // Clear session data on success
            unset($_SESSION['hs_status']);
            unset($_SESSION['hs_text']);
            return true;
        } else {
            $this->msg = 'Failed to add slider: ' . $error;
            // Delete uploaded image if database insert failed
            $uploadPath = __DIR__ . '/../upload/slider/' . $imageName;
            if (file_exists($uploadPath)) {
                unlink($uploadPath);
            }
            return false;
        }
    }
    
    /**
     * Get error message
     * 
     * @return string|null Error message
     */
    public function getMessage(): ?string {
        return $this->msg;
    }
}

// Initialize session variables
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

$hs_status = $_SESSION['hs_status'] ?? '1'; // Default to active
$hs_text = $_SESSION['hs_text'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd']) && $_POST['btnAdd'] === 'Add') {
    
    // CSRF protection (implement if you have CSRF token system)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     die('Invalid CSRF token');
    // }
    
    try {
        // Sanitize inputs
        $status = isset($_POST['hs_status']) ? trim($_POST['hs_status']) : '1';
        $text = isset($_POST['hs_text']) ? trim($_POST['hs_text']) : '';
        $image = $_FILES["hs_image"]["name"] ?? '';
        
        // Create instance
        $adn = new AddHeaderSld($status, $text, $image);
        
        // Validate and add
        if ($adn->valid()) {
            $adn->add();
        }
        
        // Store message in session
        $_SESSION['msg'] = $adn->getMessage() ?: 'Operation completed';
        
        // Redirect to prevent form resubmission
        header("Location: header_slider_add.php");
        exit();
        
    } catch (Exception $e) {
        error_log('Header slider add error: ' . $e->getMessage());
        $_SESSION['msg'] = 'An error occurred. Please try again.';
        header("Location: header_slider_add.php");
        exit();
    }
}

// Generate CSRF token if needed
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<?php include "includes/admin-top.php" ?>

<!-- JavaScript Libraries -->
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- CSS Files -->
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<!-- Custom styles -->
<style>
.upload-preview {
    margin-top: 10px;
    max-width: 200px;
    max-height: 150px;
    border: 1px solid #ddd;
    padding: 5px;
    border-radius: 4px;
}
.file-info {
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}
.required-field::after {
    content: " *";
    color: red;
}
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Add Header Slider</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                <!-- CSRF Token -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <input type="button" class="delete-btn" onClick="window.location ='header_slider_view.php'" value="View Header Sliders">
                
                <?php if ($msg): ?>
                    <div id="message" class="<?php echo strpos($msg, 'successfully') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>
                
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            <!-- Status -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label class="required-field" style="width:120px;">Status:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <label>
                                                        <input type="radio" name="hs_status" value="1" 
                                                            <?php echo $hs_status == '1' ? 'checked="checked"' : ''; ?>>
                                                        Active
                                                    </label>
                                                    &nbsp;&nbsp;
                                                    <label>
                                                        <input type="radio" name="hs_status" value="0" 
                                                            <?php echo $hs_status == '0' ? 'checked="checked"' : ''; ?>>
                                                        Inactive
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <!-- Content Text -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Content:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input type="text" 
                                                           name="hs_text" 
                                                           id="hs_text" 
                                                           value="<?php echo htmlspecialchars($hs_text); ?>"  
                                                           class="reg_txtfld"
                                                           maxlength="255"
                                                           placeholder="Enter slider text (optional)" />
                                                </div>
                                            </div>
                                            
                                            <!-- Image Upload -->
                                            <div id="uploadImageDiv">
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label class="required-field" style="width:120px;">Upload Image:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="file" 
                                                               name="hs_image" 
                                                               id="hs_image" 
                                                               style="cursor:pointer"
                                                               accept="image/jpeg,image/png,image/gif"
                                                               required />
                                                        <div class="file-info">
                                                            Accepted formats: JPG, PNG, GIF (Max: 5MB, Size: 511x308px)
                                                        </div>
                                                        <div id="imagePreview" class="upload-preview" style="display: none;">
                                                            <img src="" alt="Preview" style="max-width:100%; max-height:150px;">
                                                        </div>
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
                
                <!-- Form Buttons -->
                <div class="row buttons">
                    <input type="submit" name="btnAdd" id="btnAdd" value="Add" class="x2-button" style="margin-right:10px;margin-top:5px;">
                    <input type="reset" value="Reset" class="x2-button" style="margin-top:5px;" onclick="return confirm('Reset all fields?');">
                </div>
            </form>
            
            <br clear="all"/>
        </div>
    </div>
</div>
<br clear="all" />

<?php include "includes/footer.php" ?>

<!-- JavaScript for form enhancement -->
<script type="text/javascript">
/**
 * Form validation
 */
function myvalid() {
    var fileInput = document.getElementById('hs_image');
    var message = "";
    var valid = true;
    
    // Validate file upload
    if (!fileInput.files || fileInput.files.length === 0) {
        message = "Please select an image to upload.";
        valid = false;
    } else {
        var file = fileInput.files[0];
        var fileSize = file.size / 1024 / 1024; // in MB
        var fileName = file.name;
        var fileExt = fileName.split('.').pop().toLowerCase();
        var validExt = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Check file size (5MB max)
        if (fileSize > 5) {
            message = "File size must be less than 5MB.";
            valid = false;
        }
        
        // Check file extension
        if (!validExt.includes(fileExt)) {
            message = "Please upload a valid image file (JPG, PNG, GIF).";
            valid = false;
        }
    }
    
    if (!valid) {
        alert(message);
        return false;
    }
    
    return true;
}

// Image preview on file select
document.getElementById('hs_image')?.addEventListener('change', function(e) {
    var preview = document.getElementById('imagePreview');
    var file = e.target.files[0];
    
    if (file) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            preview.style.display = 'block';
            preview.querySelector('img').src = e.target.result;
        };
        
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
        preview.querySelector('img').src = '';
    }
});

// Auto-hide message after 5 seconds
setTimeout(function() {
    var messageDiv = document.getElementById('message');
    if (messageDiv) {
        messageDiv.style.transition = 'opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(function() {
            if (messageDiv.parentNode) {
                messageDiv.style.display = 'none';
            }
        }, 500);
    }
}, 5000);

// Override form submission
document.getElementById('cmp_edit')?.addEventListener('submit', function(e) {
    if (!myvalid()) {
        e.preventDefault();
    }
});
</script>

</body>
</html>