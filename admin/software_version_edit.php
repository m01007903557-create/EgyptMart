<?php
/**
 * File: software_version_edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل إصدارات البرامج في الصفحة الرئيسية
 * Edit software versions on the homepage
 * 
 * Features:
 * - تعديل رابط الإصدار
 * - تغيير صورة الإصدار
 * - معاينة الصورة الحالية
 * - رفع صور جديدة
 * - التحقق من صحة الصور
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class SoftwareVersionEditor
 * 
 * Handles software version editing operations
 */
class SoftwareVersionEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Version ID (MD5) */
    private string $isv_id;
    
    /** @var string Version link */
    private string $isv_link = '';
    
    /** @var string Version image filename */
    private string $isv_image = '';
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /** @var string Upload directory */
    private string $uploadDir = '../image/';
    
    /** @var int Thumbnail width */
    private int $thumbWidth = 263;
    
    /** @var int Thumbnail height */
    private int $thumbHeight = 63;
    
    /**
     * Constructor
     * 
     * @param string $isv_id MD5 hashed version ID
     */
    public function __construct(string $isv_id) {
        $this->isv_id = $isv_id;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Get version details
     * 
     * @return object|null Version details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM index_software_version WHERE md5(isv_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->isv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        
        // Validate file if uploaded
        if (!empty($_FILES['isv_image']['name'])) {
            return $this->validateFile();
        }
        
        return true;
    }
    
    /**
     * Validate uploaded file
     * 
     * @return bool True if file is valid
     */
    private function validateFile(): bool {
        $file = $_FILES['isv_image'];
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
            $this->msg = '<font color="#CC0000">' . $errorMsg . '</font>';
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->msg = '<font color="#CC0000">File size must be less than 2MB</font>';
            return false;
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->msg = '<font color="#CC0000">Please upload valid image (JPG, PNG, GIF)</font>';
            return false;
        }
        
        // Verify image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->msg = '<font color="#CC0000">File is not a valid image</font>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update software version
     */
    public function update(): void {
        global $con;
        
        // If new image is uploaded
        if (!empty($this->isv_image)) {
            $this->updateWithImage();
        } else {
            $this->updateWithoutImage();
        }
    }
    
    /**
     * Update with new image
     */
    private function updateWithImage(): void {
        global $con;
        
        // Validate file extension
        $extension = strtolower(pathinfo($this->isv_image, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->msg = '<font color="#CC0000">Please upload a valid image</font>';
            return;
        }
        
        // Get existing image to delete
        $existingImage = $this->getExistingImage();
        
        // Process and save new image
        if (!$this->processAndSaveImage()) {
            return;
        }
        
        // Delete old image
        if ($existingImage && file_exists($this->uploadDir . $existingImage)) {
            @unlink($this->uploadDir . $existingImage);
        }
        
        // Update database
        $sql = "UPDATE index_software_version SET
                isv_link = ?,
                isv_image = ?
                WHERE md5(isv_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "sss", $this->isv_link, $this->imageFilename, $this->isv_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<font color="#009900">Software version updated successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to update software version</font>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update without changing image
     */
    private function updateWithoutImage(): void {
        global $con;
        
        $sql = "UPDATE index_software_version SET isv_link = ? WHERE md5(isv_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ss", $this->isv_link, $this->isv_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<font color="#009900">Software version updated successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to update software version</font>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Get existing image filename
     * 
     * @return string|null Image filename
     */
    private function getExistingImage(): ?string {
        global $con;
        
        $sql = "SELECT isv_image FROM index_software_version WHERE md5(isv_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->isv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['isv_image'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /** @var string Generated image filename */
    private string $imageFilename = '';
    
    /**
     * Process and save uploaded image
     * 
     * @return bool Success status
     */
    private function processAndSaveImage(): bool {
        $file = $_FILES['isv_image'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->imageFilename = 'SLDIMG-' . time() . '-' . rand(1000, 9999) . '.' . $extension;
        
        try {
            // Process and resize image
            $image = new SimpleImage();
            $image->load($file['tmp_name']);
            $image->resize($this->thumbWidth, $this->thumbHeight);
            
            // Save resized image
            $uploadPath = $this->uploadDir . $this->imageFilename;
            $image->save($uploadPath);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Image processing failed: " . $e->getMessage());
            $this->msg = '<font color="#CC0000">Failed to process image</font>';
            return false;
        }
    }
    
    /**
     * Magic setter for properties
     * 
     * @param string $name Property name
     * @param mixed $value Property value
     */
    public function __set(string $name, $value): void {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
}

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get token
$fullToken = $_GET['token'] ?? '';
if (empty($fullToken)) {
    header("Location: software_version_list.php");
    exit;
}

$token = substr($fullToken, 4); // Remove first 4 characters (random number)

// Initialize editor
$editor = new SoftwareVersionEditor($token);
$row = $editor->getDetails();

if (!$row) {
    header("Location: software_version_list.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnAdd'])) {
    $editor->isv_link = trim($_POST['isv_link'] ?? '');
    $editor->isv_image = $_FILES['isv_image']['name'] ?? '';
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: software_version_edit.php?token=" . $fullToken);
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

<!-- jQuery -->
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- Styles -->
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<style>
    .required {
        color: #F00;
    }
    #message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 3px;
    }
    #message font[color="#009900"] {
        color: #009900;
        font-weight: bold;
    }
    #message font[color="#CC0000"] {
        color: #CC0000;
        font-weight: bold;
    }
    .delete-btn {
        background: #d15b47;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 3px;
        cursor: pointer;
        margin-bottom: 10px;
    }
    .delete-btn:hover {
        background: #b74635;
    }
    .x2-button {
        background: #2c3e50;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 3px;
        cursor: pointer;
    }
    .x2-button:hover {
        background: #1a2632;
    }
    .formItem {
        margin-bottom: 15px;
    }
    .formItem label {
        display: inline-block;
        vertical-align: top;
        font-weight: bold;
    }
    .formInputBox {
        display: inline-block;
    }
    .reg_txtfld {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        font-family: Arial, sans-serif;
    }
    .help-text {
        font-size: 11px;
        color: #777;
        margin: 3px 0 0 0;
    }
    img {
        max-width: 200px;
        max-height: 150px;
        border: 1px solid #ddd;
        padding: 3px;
        border-radius: 3px;
        background: #fff;
    }
</style>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;Manage Software Versions&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Edit Version</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                
                <input type="button" class="delete-btn" onClick="window.location ='software_version_list.php'" value="Back to List">
                
                <div id="message"><?php echo $msg; ?></div>
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- Link Field -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Link:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input type="text" name="isv_link" id="isv_link" 
                                                           value="<?php echo htmlspecialchars($row->isv_link ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                                           class="reg_txtfld" placeholder="Enter download link or URL"/>
                                                    <p class="help-text">Enter the URL where users can download this version</p>
                                                </div>
                                            </div>
                                            
                                            <!-- Current Image -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Current Image:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <?php if (!empty($row->isv_image) && file_exists("../image/" . $row->isv_image)): ?>
                                                        <img src="../image/<?php echo htmlspecialchars($row->isv_image, ENT_QUOTES, 'UTF-8'); ?>" 
                                                             alt="Current Software Version Image"/>
                                                    <?php else: ?>
                                                        <p class="text-muted">No image available</p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Upload Section -->
                                            <div id="uploadImageDiv">
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">Upload New Image:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="file" name="isv_image" id="isv_image" accept="image/*" style="cursor:pointer"/>
                                                        <p class="help-text">
                                                            Leave empty to keep current image. 
                                                            Allowed: JPG, PNG, GIF (Max 2MB, will be resized to 263x63)
                                                        </p>
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
                
                <div class="row buttons">
                    <input type="submit" name="btnAdd" id="btnAdd" value="Update" class="x2-button" style="margin-right:10px;margin-top:5px;">
                </div>
            </form>    
            <br clear="all"/>
        </div>        
    </div>
</div>
<br clear="all" />   	

<?php include "includes/footer.php" ?>

<!-- Additional scripts for file validation -->
<script type="text/javascript">
    document.getElementById('isv_image').addEventListener('change', function(e) {
        var file = this.files[0];
        if (file) {
            var ext = file.name.split('.').pop().toLowerCase();
            var allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (allowedExts.indexOf(ext) === -1) {
                alert('Please upload valid image (JPG, PNG, GIF)');
                this.value = '';
                return false;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                return false;
            }
        }
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>