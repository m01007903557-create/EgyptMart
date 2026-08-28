<?php
/**
 * File: service_edit.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعديل الخدمات الموجودة مع تحديث الصورة والمحتوى
 * Edit existing services with image and content update
 * 
 * Features:
 * - تعديل عنوان الخدمة
 * - تعديل محتوى الخدمة
 * - تغيير صورة الخدمة
 * - معاينة الصورة الحالية
 * - محرر نصوص متقدم TinyMCE
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
 * Class ServiceEditor
 * 
 * Handles service editing operations
 */
class ServiceEditor {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Service ID */
    private int $ser_id;
    
    /** @var string Service heading */
    private string $ser_heading = '';
    
    /** @var string Service content */
    private string $ser_content = '';
    
    /** @var string Service image filename */
    private string $ser_image = '';
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /** @var string Upload directory */
    private string $uploadDir = '../image/';
    
    /** @var int Thumbnail width */
    private int $thumbWidth = 35;
    
    /** @var int Thumbnail height */
    private int $thumbHeight = 35;
    
    /**
     * Constructor
     * 
     * @param string $token MD5 token
     */
    public function __construct(string $token) {
        $this->ser_id = $this->decodeToken($token);
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Decode MD5 token to ID
     * 
     * @param string $token MD5 token
     * @return int Service ID
     */
    private function decodeToken(string $token): int {
        global $con;
        
        $sql = "SELECT ser_id FROM services WHERE md5(ser_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (int)$row['ser_id'];
        }
        
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    /**
     * Get service details
     * 
     * @return object|null Service details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM services WHERE md5(ser_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $this->ser_id);
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
        
        // Validate heading
        if (empty($this->ser_heading)) {
            $this->msg = '<font color="#CC0000">Please enter Service Heading</font>';
            return false;
        }
        
        // Validate content
        if (empty($this->ser_content)) {
            $this->msg = '<font color="#CC0000">Please enter Service Content</font>';
            return false;
        }
        
        // Validate file if uploaded
        if (!empty($_FILES['ser_image']['name'])) {
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
        $file = $_FILES['ser_image'];
        
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
     * Update service
     */
    public function update(): void {
        global $con;
        
        // If new image is uploaded
        if (!empty($this->ser_image)) {
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
        $extension = strtolower(pathinfo($this->ser_image, PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->msg = '<font color="#CC0000">Please Upload A Image With Valid Extension</font>';
            return;
        }
        
        // Get existing image to delete
        $existingImage = $this->getExistingImage();
        
        // Process and save new image
        if (!$this->processImageUpload()) {
            return;
        }
        
        // Delete old image
        if ($existingImage && file_exists($this->uploadDir . $existingImage)) {
            @unlink($this->uploadDir . $existingImage);
        }
        
        // Update database
        $sql = "UPDATE services SET
                ser_heading = ?,
                ser_content = ?,
                ser_image = ?,
                ser_updated_date = NOW()
                WHERE md5(ser_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "ssss", $this->ser_heading, $this->ser_content, $this->imageFilename, (string)$this->ser_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<font color="#009900">Service updated successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to update service</font>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update without changing image
     */
    private function updateWithoutImage(): void {
        global $con;
        
        $sql = "UPDATE services SET
                ser_heading = ?,
                ser_content = ?,
                ser_updated_date = NOW()
                WHERE md5(ser_id) = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<font color="#CC0000">Database error</font>';
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "sss", $this->ser_heading, $this->ser_content, (string)$this->ser_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<font color="#009900">Service updated successfully</font>';
        } else {
            $this->msg = '<font color="#CC0000">Failed to update service</font>';
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
        
        $sql = "SELECT ser_image FROM services WHERE md5(ser_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "s", (string)$this->ser_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['ser_image'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /** @var string Generated image filename */
    private string $imageFilename = '';
    
    /**
     * Process image upload
     * 
     * @return bool Success status
     */
    private function processImageUpload(): bool {
        $file = $_FILES['ser_image'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->imageFilename = 'SERLOGO-' . time() . '-' . rand(1000, 9999) . '.' . $extension;
        
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
    header("Location: service_list.php");
    exit;
}

$token = substr($fullToken, 4); // Remove first 4 characters (random number)

// Initialize editor
$editor = new ServiceEditor($token);
$row = $editor->getDetails();

if (!$row) {
    header("Location: service_list.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnAdd'])) {
    $editor->ser_heading = trim($_POST['ser_heading'] ?? '');
    $editor->ser_content = trim($_POST['ser_content'] ?? '');
    $editor->ser_image = $_FILES['ser_image']['name'] ?? '';
    
    if ($editor->validate()) {
        $editor->update();
    }
    
    $_SESSION['msg'] = $editor->msg;
    header("Location: service_edit.php?token=" . $fullToken);
    exit;
}
?>

<?php include "includes/admin-top.php" ?>

<!-- jQuery -->
<script src="../js/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="js/menu-collapsed.js" type="text/javascript"></script>

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        // General options
        mode : "textareas",
        theme : "advanced",
        
        plugins : "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",

        // Theme options
        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
        theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,

        // Content CSS
        content_css : "css/content.css",

        // Drop lists for link/image/media/template dialogs
        template_external_list_url : "lists/template_list.js",
        external_link_list_url : "lists/link_list.js",
        external_image_list_url : "lists/image_list.js",
        media_external_list_url : "lists/media_list.js",

        // Style formats
        style_formats : [
            {title : 'Bold text', inline : 'b'},
            {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
            {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
            {title : 'Example 1', inline : 'span', classes : 'example1'},
            {title : 'Example 2', inline : 'span', classes : 'example2'},
            {title : 'Table styles'},
            {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
        ],         
        forced_root_block : false,
        force_p_newlines : false,
        remove_linebreaks : false,
        force_br_newlines : true,
        remove_trailing_nbsp : false,
        verify_html : false           
    });
</script>

<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <h2>&rsaquo;&nbsp;&nbsp;Manage Service&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Edit Service</h2>
            
            <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data">
                
                <em style="display:block;margin:5px;">Fields with <span class="required">*</span> are required.</em>
                
                <input type="button" class="delete-btn" onClick="window.location ='service_list.php'" value="Service List">
                
                <div id="message"><?php echo $msg; ?></div>
                <br />

                <div class="x2-layout" style="width:850px;">
                    <div class="formSection showSection">
                        <div class="tableWrapper">
                            <table>
                                <tbody>
                                    <tr class="formSectionRow">
                                        <td style="width:678px">
                                            
                                            <!-- Heading -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Heading: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <textarea name="ser_heading" id="ser_heading" class="reg_txtfld" style="height:100px"><?php echo htmlspecialchars($row->ser_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea> 
                                                </div>
                                            </div>
                                            
                                            <!-- Content with TinyMCE -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Content: <span class="required">*</span></label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <textarea name="ser_content" id="ser_content" class="reg_txtfld" style="height:300px"><?php echo htmlspecialchars($row->ser_content ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea> 
                                                </div>
                                            </div>
                                            
                                            <!-- Current Image -->
                                            <?php if (!empty($row->ser_image) && file_exists("../image/" . $row->ser_image)): ?>
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">Current Image:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <img src="../image/<?php echo htmlspecialchars($row->ser_image, ENT_QUOTES, 'UTF-8'); ?>" alt="Current Service Image"/>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <!-- Image Upload -->
                                            <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                <label style="width:120px;">New Image:</label>
                                                <div class="formInputBox" style="width:387px;height:auto;">
                                                    <input type="file" name="ser_image" id="ser_image" accept="image/*"/>
                                                    <p class="help-text">Leave empty to keep current image. Allowed: JPG, PNG, GIF (Max 2MB, will be resized to 35x35)</p>
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

<style>
    .required {
        color: #F00;
    }
    .help-text {
        font-size: 11px;
        color: #777;
        margin: 3px 0 0 0;
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
    img {
        max-width: 100px;
        max-height: 100px;
        border: 1px solid #ddd;
        padding: 3px;
        border-radius: 3px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>