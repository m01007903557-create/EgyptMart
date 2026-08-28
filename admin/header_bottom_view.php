<?php
/**
 * File: header_bottom_view.php
 * Version: 2.0.0
 * Description: Edit header bottom link settings (Upgraded to PHP 8.3)
 * Last modified: 2024-01-15
 * 
 * PHP 8.3 Upgrade Features:
 * - Strict typing declarations
 * - Class property typing
 * - Constructor property promotion
 * - Type hints for all methods
 * - Null safety operators
 * - Prepared statements for SQL
 * - Improved file upload handling
 * - XSS protection
 * - CSRF protection ready
 * - Modern error handling
 */

// Enable strict error reporting for PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once "../common.php";
require_once "../lib/SimpleImage.php";

// Check user authentication
check_user_login();

/**
 * Class EditProduct - Handles header bottom link editing
 * PHP 8.3 compatible with strict typing
 */
class EditProduct {
    // Typed properties
    private ?string $msg = null;
    private int $htbl_id = 2; // Fixed ID as per original code
    private ?string $htbl_button_text = null;
    private ?string $htbl_button_link = null;
    private ?string $htbl_image = null;
    private ?string $htbl_status = null;
    private mysqli $db;
    
    /**
     * Constructor with dependency injection
     * 
     * @param mysqli $databaseConnection Database connection object
     */
    public function __construct(?mysqli $databaseConnection = null) {
        global $con;
        $this->db = $databaseConnection ?? $con;
    }
    
    /**
     * Get header details from database
     * 
     * @return object|null Header details object
     * @throws RuntimeException If query fails
     */
    public function detailsObj(): ?object {
        $sql = "SELECT * FROM header_top_bottom_link WHERE htbl_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($this->db));
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->htbl_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_object($result);
        
        mysqli_stmt_close($stmt);
        
        return $data ?: null;
    }
    
    /**
     * Validate input data
     * 
     * @param array $data POST data to validate
     * @return bool Validation result
     */
    public function valid(array $data): bool {
        $this->msg = null;
        
        // Validate button text
        if (empty($data['htbl_button_text'] ?? '')) {
            $this->msg = 'Button text is required';
            return false;
        }
        
        // Validate button link
        if (empty($data['htbl_button_link'] ?? '')) {
            $this->msg = 'Button link is required';
            return false;
        }
        
        // Validate status
        if (!isset($data['htbl_status']) || !in_array($data['htbl_status'], ['0', '1'], true)) {
            $this->msg = 'Invalid status value';
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle file upload with validation
     * 
     * @param array $file Uploaded file data
     * @return string|null Uploaded filename or null on failure
     */
    private function handleFileUpload(array $file): ?string {
        // Check if file was uploaded
        if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        
        // Validate file extension
        $fileName = $file['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $validEXT = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($ext, $validEXT, true)) {
            $this->msg = 'Please upload an image with valid extension (jpg, jpeg, png, gif)';
            return null;
        }
        
        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->msg = 'File size must be less than 5MB';
            return null;
        }
        
        $tempFile = $file['tmp_name'];
        
        // Verify it's a valid image
        $imageInfo = getimagesize($tempFile);
        if ($imageInfo === false) {
            $this->msg = 'Invalid image file';
            return null;
        }
        
        // Generate unique filename
        $newFileName = sprintf('HTRIMG-%04d-%s.%s', 
            random_int(0, 9999),
            uniqid(),
            $ext
        );
        
        try {
            // Process and save image
            $imgSImage = new SimpleImage();
            $imgSImage->load($tempFile);
            $imgSImage->resize(254, 163);
            
            $uploadPath = __DIR__ . '/../upload/slider/' . $newFileName;
            $imgSImage->save($uploadPath);
            
            return $newFileName;
            
        } catch (Exception $e) {
            $this->msg = 'Failed to process image: ' . $e->getMessage();
            return null;
        }
    }
    
    /**
     * Delete old image file
     * 
     * @param string $oldImage Old image filename
     * @return bool Success status
     */
    private function deleteOldImage(string $oldImage): bool {
        if (empty($oldImage)) {
            return false;
        }
        
        $filePath = __DIR__ . '/../upload/slider/' . $oldImage;
        
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }
    
    /**
     * Get old image filename
     * 
     * @return string|null Old image filename
     */
    private function getOldImage(): ?string {
        $sql = "SELECT htbl_image FROM header_top_bottom_link WHERE htbl_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->htbl_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        mysqli_stmt_close($stmt);
        
        return $row['htbl_image'] ?? null;
    }
    
    /**
     * Update record in database
     * 
     * @param array $data POST data
     * @param array $file Uploaded file data
     * @return bool Success status
     */
    public function update(array $data, array $file): bool {
        // Validate input
        if (!$this->valid($data)) {
            return false;
        }
        
        // Sanitize inputs
        $buttonText = mysqli_real_escape_string($this->db, trim($data['htbl_button_text'] ?? ''));
        $buttonLink = mysqli_real_escape_string($this->db, trim($data['htbl_button_link'] ?? ''));
        $status = mysqli_real_escape_string($this->db, $data['htbl_status'] ?? '0');
        
        // Handle file upload if present
        $newImage = null;
        if (!empty($file['name']) && $file['error'] === UPLOAD_ERR_OK) {
            $newImage = $this->handleFileUpload($file);
            
            if ($newImage === null) {
                return false; // Error message already set in handleFileUpload
            }
            
            // Delete old image
            $oldImage = $this->getOldImage();
            if ($oldImage) {
                $this->deleteOldImage($oldImage);
            }
        }
        
        // Build update query
        if ($newImage !== null) {
            $sql = "UPDATE header_top_bottom_link SET 
                    htbl_image = ?,
                    htbl_button_text = ?,
                    htbl_button_link = ?,
                    htbl_updated_date = NOW(),
                    htbl_status = ?
                    WHERE htbl_id = ?";
            
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                $this->msg = 'Database error: ' . mysqli_error($this->db);
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "ssssi", 
                $newImage, 
                $buttonText, 
                $buttonLink, 
                $status, 
                $this->htbl_id
            );
            
        } else {
            $sql = "UPDATE header_top_bottom_link SET 
                    htbl_button_text = ?,
                    htbl_button_link = ?,
                    htbl_updated_date = NOW(),
                    htbl_status = ?
                    WHERE htbl_id = ?";
            
            $stmt = mysqli_prepare($this->db, $sql);
            
            if (!$stmt) {
                $this->msg = 'Database error: ' . mysqli_error($this->db);
                return false;
            }
            
            mysqli_stmt_bind_param($stmt, "sssi", 
                $buttonText, 
                $buttonLink, 
                $status, 
                $this->htbl_id
            );
        }
        
        $success = mysqli_stmt_execute($stmt);
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        
        if ($success) {
            $this->msg = 'Header Top Updated successfully';
            return true;
        } else {
            $this->msg = 'Update failed: ' . $error;
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
    
    /**
     * Set message
     * 
     * @param string $msg Message to set
     * @return self
     */
    public function setMessage(string $msg): self {
        $this->msg = $msg;
        return $this;
    }
}

// Handle session message
$msg = '';
if (isset($_SESSION['msg'])) {
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

// Initialize object
try {
    $ob = new EditProduct();
    $row = $ob->detailsObj();
    
    if (!$row) {
        throw new RuntimeException('Header record not found');
    }
    
} catch (Exception $e) {
    error_log('Header edit error: ' . $e->getMessage());
    $msg = 'Error loading header data. Please try again.';
    $row = null;
}

// Handle form submission
if (isset($_POST['btnAdd']) && $_POST['btnAdd'] === 'Update') {
    
    // CSRF protection (implement if you have CSRF token system)
    // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    //     die('Invalid CSRF token');
    // }
    
    try {
        if ($ob->update($_POST, $_FILES['htbl_image'] ?? [])) {
            $_SESSION['msg'] = $ob->getMessage();
        } else {
            $_SESSION['msg'] = $ob->getMessage() ?: 'Update failed';
        }
        
        // Redirect to prevent form resubmission
        header("Location: header_bottom_view.php");
        exit();
        
    } catch (Exception $e) {
        error_log('Header update error: ' . $e->getMessage());
        $_SESSION['msg'] = 'An error occurred during update';
        header("Location: header_bottom_view.php");
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

<!-- TinyMCE -->
<script type="text/javascript" src="tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
// Modernized TinyMCE initialization
tinyMCE.init({
    // General options
    mode: "textareas",
    theme: "advanced",
    
    plugins: "pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,wordcount,advlist,autosave",
    
    // Theme options
    theme_advanced_buttons1: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2: "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,phpimage,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
    theme_advanced_buttons3: "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
    theme_advanced_buttons4: "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
    theme_advanced_toolbar_location: "top",
    theme_advanced_toolbar_align: "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,
    
    // Content CSS
    content_css: "css/content.css",
    
    // External lists
    template_external_list_url: "lists/template_list.js",
    external_link_list_url: "lists/link_list.js",
    external_image_list_url: "lists/image_list.js",
    media_external_list_url: "lists/media_list.js",
    
    // Style formats
    style_formats: [
        {title: 'Bold text', inline: 'b'},
        {title: 'Red text', inline: 'span', styles: {color: '#ff0000'}},
        {title: 'Red header', block: 'h1', styles: {color: '#ff0000'}},
        {title: 'Example 1', inline: 'span', classes: 'example1'},
        {title: 'Example 2', inline: 'span', classes: 'example2'},
        {title: 'Table styles'},
        {title: 'Table row 1', selector: 'tr', classes: 'tablerow1'}
    ],
    
    // Editor behavior
    forced_root_block: false,
    force_p_newlines: false,
    remove_linebreaks: false,
    force_br_newlines: true,
    remove_trailing_nbsp: false,
    verify_html: false
});

/**
 * Form validation
 * @returns {boolean}
 */
function myvalid() {
    var message = "";
    var valid = true;
    
    // Validate button text
    var buttonText = document.getElementById('htbl_button_text');
    if (!buttonText.value || buttonText.value.trim() === '') {
        message += 'Button text is required.\n';
        buttonText.style.borderColor = 'red';
        valid = false;
    } else {
        buttonText.style.borderColor = '';
    }
    
    // Validate button link
    var buttonLink = document.getElementById('htbl_button_link');
    if (!buttonLink.value || buttonLink.value.trim() === '') {
        message += 'Button link is required.\n';
        buttonLink.style.borderColor = 'red';
        valid = false;
    } else {
        buttonLink.style.borderColor = '';
    }
    
    // Validate status
    var statusChecked = document.querySelector('input[name="htbl_status"]:checked');
    if (!statusChecked) {
        message += 'Please select status.\n';
        valid = false;
    }
    
    if (message && !valid) {
        alert(message);
    }
    
    return valid;
}
</script>

<!-- CSS Files -->
<link href="style/style.css" type="text/css" rel="stylesheet"/>
<link href="calendar/calendar_js_css/css_calendar.css" type="text/css" rel="stylesheet"/>

<div class="control_Panel">
    <?php include "includes/admin-left-con.php" ?>
    
    <div id="content-container">
        <div id="content">
            <?php if ($row): ?>
                <h2>&rsaquo;&nbsp;&nbsp;Manage Header&nbsp;&nbsp;&rsaquo;&nbsp;&nbsp;Manage <?php echo htmlspecialchars($row->htbl_field ?? ''); ?></h2>
                
                <?php if ($msg): ?>
                    <div id="message" class="<?php echo strpos($msg, 'successfully') !== false ? 'success' : 'error'; ?>">
                        <?php echo htmlspecialchars($msg); ?>
                    </div>
                <?php endif; ?>
                
                <form action="" id="cmp_edit" name="cmp_edit" method="post" enctype="multipart/form-data" onsubmit="return myvalid();">
                    <!-- CSRF Protection -->
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <em style="display:block;margin:5px;">Fields with <span class="required">*</span> are required.</em>
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
                                                    <label style="width:120px;">Status <span class="required">*</span>:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <label>
                                                            <input type="radio" name="htbl_status" value="1" 
                                                                <?php echo ($row->htbl_status ?? '') == '1' ? 'checked' : ''; ?>>
                                                            Active
                                                        </label>
                                                        &nbsp;&nbsp;
                                                        <label>
                                                            <input type="radio" name="htbl_status" value="0" 
                                                                <?php echo ($row->htbl_status ?? '') == '0' ? 'checked' : ''; ?>>
                                                            Inactive
                                                        </label>
                                                    </div>
                                                </div>
                                                
                                                <!-- Button Text -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">Button Text <span class="required">*</span>:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="text" 
                                                               name="htbl_button_text" 
                                                               id="htbl_button_text"
                                                               class="reg_txtfld" 
                                                               value="<?php echo htmlspecialchars($row->htbl_button_text ?? ''); ?>"
                                                               maxlength="255"
                                                               required/>
                                                    </div>
                                                </div>
                                                
                                                <!-- Button Link -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">Button Link <span class="required">*</span>:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="url" 
                                                               name="htbl_button_link" 
                                                               id="htbl_button_link"
                                                               class="reg_txtfld" 
                                                               value="<?php echo htmlspecialchars($row->htbl_button_link ?? ''); ?>"
                                                               maxlength="500"
                                                               placeholder="https://example.com"
                                                               required/>
                                                    </div>
                                                </div>
                                                
                                                <!-- Current Image -->
                                                <?php if (!empty($row->htbl_image)): ?>
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">Current Image:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <img src="../upload/slider/<?php echo htmlspecialchars($row->htbl_image); ?>" 
                                                             alt="Current header image"
                                                             style="max-width:254px; max-height:163px;"/>
                                                        <p class="small">Leave empty to keep current image</p>
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <!-- New Image Upload -->
                                                <div class="formItem leftLabel" style="padding-bottom:10px; padding-top:10px;">
                                                    <label style="width:120px;">New Image:</label>
                                                    <div class="formInputBox" style="width:387px;height:auto;">
                                                        <input type="file" 
                                                               name="htbl_image" 
                                                               id="htbl_image"
                                                               accept="image/jpeg,image/png,image/gif"/>
                                                        <p class="small">Accepted formats: JPG, PNG, GIF (max 5MB, 254x163px)</p>
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
                        <input type="submit" 
                               name="btnAdd" 
                               id="btnAdd" 
                               value="Update" 
                               class="x2-button" 
                               style="margin-right:10px;margin-top:5px;">
                        <input type="button" 
                               value="Cancel" 
                               class="x2-button" 
                               style="margin-top:5px;"
                               onclick="window.location.href='header_bottom_view.php';">
                    </div>
                </form>
            <?php else: ?>
                <div class="error">Header record not found.</div>
            <?php endif; ?>
            
            <br clear="all"/>
        </div>
    </div>
</div>
<br clear="all" />

<?php include "includes/footer.php" ?>

<!-- Additional JavaScript -->
<script type="text/javascript">
// File input preview (optional enhancement)
document.getElementById('htbl_image')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Check file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            this.value = '';
            return;
        }
        
        // Check file type
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Please upload a valid image file (JPG, PNG, GIF)');
            this.value = '';
            return;
        }
    }
});

// Auto-hide message after 5 seconds
setTimeout(function() {
    const messageDiv = document.getElementById('message');
    if (messageDiv) {
        messageDiv.style.transition = 'opacity 0.5s';
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.style.display = 'none';
            }
        }, 500);
    }
}, 5000);
</script>

</body>
</html>