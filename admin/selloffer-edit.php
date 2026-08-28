<?php
// ✅ تعريف السماح بالوصول
define('ACCESS_ALLOWED', true);
declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class EditSellOffer
 * 
 * Handles sale offer editing operations
 */
class EditSellOffer {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Sale offer ID */
    private int $so_id;
    
    /** @var int Category ID */
    private int $pc_id = 0;
    
    /** @var int Subcategory ID */
    private int $so_pc_id = 0;
    
    /** @var string Service name */
    private string $so_service = '';
    
    /** @var string Service description */
    private string $so_description = '';
    
    /** @var string Preferred buyer location */
    private string $so_preferred_buyer_location = 'any';
    
    /** @var int Validity in days */
    private int $so_validity = 90;
    
    /** @var string Image filename */
    private string $so_pic = '';
    
    /** @var array Allowed image extensions */
    private array $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    /** @var int Maximum file size (2MB) */
    private int $maxFileSize = 2097152;
    
    /**
     * Constructor
     * 
     * @param string $token MD5 token
     */
    public function __construct(string $token) {
        $this->so_id = $this->decodeToken($token);
    }
    
    /**
     * Decode MD5 token to ID
     * 
     * @param string $token MD5 token
     * @return int ID
     */
    private function decodeToken(string $token): int {
        global $con;
        
        $sql = "SELECT so_id FROM sale_offer WHERE md5(so_id) = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (int)$row['so_id'];
        }
        
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    /**
     * Get sale offer details
     * 
     * @return object|null Sale offer details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT s.*, pc.pc_id as category_id, pc.pc_parent_id, pc.pc_name 
                FROM sale_offer s
                JOIN product_category pc ON s.so_pc_id = pc.pc_id 
                WHERE s.so_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->so_id);
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
        
        // Validate service name
        if (empty($this->so_service)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Service Name.</div>';
            return false;
        }
        
        // Validate description
        if (empty($this->so_description)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter Description.</div>';
            return false;
        }
        
        // Validate category
        if ($this->pc_id === 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Category.</div>';
            return false;
        }
        
        // Validate subcategory
        if ($this->so_pc_id === 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Sub-Category.</div>';
            return false;
        }
        
        // Validate file if uploaded
        if (!empty($_FILES['so_pic']['name']) && !$this->validateFile()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Validate uploaded file
     * 
     * @return bool True if file is valid
     */
    private function validateFile(): bool {
        $file = $_FILES['so_pic'];
        
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
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> ' . $errorMsg . '</div>';
            return false;
        }
        
        // Check file size
        if ($file['size'] > $this->maxFileSize) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> File size must be less than 2MB</div>';
            return false;
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedExtensions, true)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload valid image (JPG, PNG, GIF)</div>';
            return false;
        }
        
        // Verify image
        $imageInfo = getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> File is not a valid image</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update sale offer
     */
    public function update(): void {
        global $con;
        
        // Handle image upload if provided
        if (!empty($_FILES['so_pic']['name'])) {
            $this->updateWithImage();
        } else {
            $this->updateWithoutImage();
        }
        
        // Clear session variables
        $this->clearSession();
    }
    
    /**
     * Update with new image
     */
    private function updateWithImage(): void {
        global $con;
        
        // Get existing image to delete
        $existingImage = $this->getExistingImage();
        
        // Process and save new image
        $this->processAndSaveImage();
        
        // Delete old image
        if ($existingImage) {
            $this->deleteImageFiles($existingImage);
        }
        
        // Update database
        $sql = "UPDATE sale_offer SET
                so_pc_id = ?,
                so_service = ?,
                so_description = ?,
                so_preferred_buyer_location = ?,
                so_validity = ?,
                so_pic = ?,
                so_updated_date = NOW()
                WHERE so_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "isssisi",
            $this->so_pc_id,
            $this->so_service,
            $this->so_description,
            $this->so_preferred_buyer_location,
            $this->so_validity,
            $this->so_pic,
            $this->so_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update without changing image
     */
    private function updateWithoutImage(): void {
        global $con;
        
        $sql = "UPDATE sale_offer SET
                so_pc_id = ?,
                so_service = ?,
                so_description = ?,
                so_preferred_buyer_location = ?,
                so_validity = ?,
                so_updated_date = NOW()
                WHERE so_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "isssii",
            $this->so_pc_id,
            $this->so_service,
            $this->so_description,
            $this->so_preferred_buyer_location,
            $this->so_validity,
            $this->so_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer updated successfully.</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
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
        
        $sql = "SELECT so_pic FROM sale_offer WHERE so_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->so_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['so_pic'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Process and save uploaded image
     */
    private function processAndSaveImage(): void {
        $file = $_FILES['so_pic'];
        
        // Generate unique filename
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $this->so_pic = 'so-' . time() . '-' . rand(1000, 9999) . '.' . $extension;
        
        // Upload main image
        $uploadPath = __DIR__ . "/../upload/sale_offer/" . $this->so_pic;
        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to upload image</div>';
            return;
        }
        
        // Create thumbnail
        $this->createThumbnail();
    }
    
    /**
     * Create thumbnail image
     */
    private function createThumbnail(): void {
        $sourcePath = __DIR__ . "/../upload/sale_offer/" . $this->so_pic;
        $thumbPath = __DIR__ . "/../upload/sale_offer/thumb/" . $this->so_pic;
        
        // Create thumb directory if it doesn't exist
        $thumbDir = __DIR__ . "/../upload/sale_offer/thumb/";
        if (!is_dir($thumbDir)) {
            mkdir($thumbDir, 0755, true);
        }
        
        try {
            $image = new SimpleImage();
            $image->load($sourcePath);
            $image->resize(100, 80);
            $image->save($thumbPath);
        } catch (Exception $e) {
            error_log("Thumbnail creation failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete image files
     * 
     * @param string $filename Image filename
     */
    private function deleteImageFiles(string $filename): void {
        $paths = [
            __DIR__ . "/../upload/sale_offer/" . $filename,
            __DIR__ . "/../upload/sale_offer/thumb/" . $filename
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                @unlink($path);
            }
        }
    }
    
    /**
     * Clear session variables
     */
    private function clearSession(): void {
        $sessionVars = ['pc_id', 'so_pc_id', 'so_service', 'so_description', 'so_validity'];
        foreach ($sessionVars as $var) {
            unset($_SESSION[$var]);
        }
    }
    
    /**
     * Approve sale offer
     */
    public function approve(): void {
        global $con;
        
        $sql = "UPDATE sale_offer SET so_approval_status = 1, so_approval_date = NOW() WHERE so_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $this->so_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer approved successfully.</div>';
        }
    }
    
    /**
     * Disapprove sale offer
     */
    public function disapprove(): void {
        global $con;
        
        $sql = "UPDATE sale_offer SET so_approval_status = 2, so_approval_date = NOW() WHERE so_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $this->so_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            
            $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer disapproved successfully.</div>';
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
            if (in_array($name, ['so_id', 'pc_id', 'so_pc_id', 'so_validity'])) {
                $this->$name = (int)$value;
            } else {
                $this->$name = $value;
            }
        }
    }
}

// Get token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: selloffer-view.php");
    exit;
}

// Initialize edit object
$editOffer = new EditSellOffer($token);
$row = $editOffer->getDetails();

if (!$row) {
    header("Location: selloffer-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editOffer->so_id = $_POST['so_id'] ?? 0;
    $editOffer->pc_id = $_POST['pc_id'] ?? 0;
    $editOffer->so_pc_id = $_POST['so_pc_id'] ?? 0;
    $editOffer->so_service = $_POST['so_service'] ?? '';
    $editOffer->so_description = $_POST['so_description'] ?? '';
    $editOffer->so_preferred_buyer_location = $_POST['so_preferred_buyer_location'] ?? 'any';
    $editOffer->so_validity = $_POST['so_validity'] ?? 90;
    
    if ($editOffer->validate()) {
        $editOffer->update();
    }
    
    $_SESSION['msg'] = $editOffer->msg;
    header('Location: ../selloffer-email.php?admn_so_id=' . $editOffer->so_id);
    exit;
}

// Handle approve
if (isset($_POST['btnApprove'])) {
    $editOffer->so_id = $_POST['so_id'] ?? 0;
    $editOffer->approve();
    header("Location: selloffer-edit.php?token=" . md5((string)$editOffer->so_id));
    exit;
}

// Handle disapprove
if (isset($_POST['btnDisApprove'])) {
    $editOffer->so_id = $_POST['so_id'] ?? 0;
    $editOffer->disapprove();
    header("Location: selloffer-edit.php?token=" . md5((string)$editOffer->so_id));
    exit;
}

// Get session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
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
            function showCategory() {
                var pc_id = document.getElementById('mcat_id').value;
                $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
                    $('#pc_id').html(data);
                });
            }
            
            function showSubcat() {
                var pc_id = document.getElementById('pc_id').value;
                $.get("showSubcat.php", {q: pc_id}, function(data) {
                    $('#so_pc_id').html(data);
                });
            }
            
            function validateForm() {
                var so_service = document.getElementById('so_service');
                var pc_id = document.getElementById('pc_id');
                var so_pc_id = document.getElementById('so_pc_id');
                var so_description = document.getElementById('so_description');
                var so_pic = document.getElementById('so_pic');
                
                var message = "";
                var valid = true;
                
                // Validate service name
                if (so_service.value.trim() === '') {
                    message = 'Please enter Service Name.';
                    so_service.focus();
                    valid = false;
                }
                // Validate description
                else if (so_description.value.trim() === '') {
                    message = 'Please enter Description.';
                    so_description.focus();
                    valid = false;
                }
                // Validate category
                else if (pc_id.value === '' || pc_id.value === null) {
                    message = 'Please select Category.';
                    pc_id.focus();
                    valid = false;
                }
                // Validate subcategory
                else if (so_pc_id.value === '' || so_pc_id.value === null || so_pc_id.value === '0') {
                    message = 'Please select Sub-Category.';
                    so_pc_id.focus();
                    valid = false;
                }
                // Validate file if uploaded
                else if (so_pic.files.length > 0) {
                    var fileName = so_pic.value;
                    var ext = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
                    var allowedExts = ['gif', 'png', 'jpg', 'jpeg'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        message = 'Please upload valid image (JPG, PNG, GIF)';
                        so_pic.value = '';
                        so_pic.focus();
                        valid = false;
                    }
                }
                
                if (!valid) {
                    document.getElementById('msg').innerHTML = "<i class='icon-remove'></i> " + message;
                    document.getElementById('msg').className = "alert alert-danger";
                }
                
                return valid;
            }
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
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="selloffer-view.php">Manage Sale Offers</a>
                    </li>
                    <li class="active">Edit Sale Offer</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Edit Sale Offer
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($row->so_service ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" onsubmit="return validateForm();">
                            
                            <input type="hidden" name="so_id" value="<?php echo (int)$row->so_id; ?>" />

                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Service Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Service Name:</label>
                                <div class="col-sm-9">
                                    <input name="so_service" id="so_service" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->so_service ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Description:</label>
                                <div class="col-sm-9">
                                    <textarea id="so_description" name="so_description" class="col-xs-10 col-sm-7" rows="5"><?php 
                                        echo htmlspecialchars($row->so_description ?? '', ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Main Category -->
                            <div class="form-group">
                                <?php
                                // Get main category
                                $mainCatSql = "SELECT pc.* FROM product_category pc 
                                               WHERE pc.pc_id = (SELECT pc_parent_id FROM product_category 
                                               WHERE pc_id = ?) AND pc.pc_status = '1'";
                                $stmt = mysqli_prepare($con, $mainCatSql);
                                $mainCatId = 0;
                                if ($stmt) {
                                    mysqli_stmt_bind_param($stmt, "i", $row->pc_parent_id);
                                    mysqli_stmt_execute($stmt);
                                    $mainCatResult = mysqli_stmt_get_result($stmt);
                                    $mainCatRow = mysqli_fetch_object($mainCatResult);
                                    $mainCatId = $mainCatRow->pc_id ?? 0;
                                    mysqli_stmt_close($stmt);
                                }
                                ?>
                                <label class="col-sm-3 control-label no-padding-right">Main Category:</label>
                                <div class="col-sm-9">
                                    <select id="mcat_id" name="mcat_id" onchange="showCategory();">
                                        <?php
                                        $mainCats = mysqli_query($con, "SELECT * FROM product_category WHERE pc_parent_id = '0' AND pc_status = '1'");
                                        while ($mainCat = mysqli_fetch_object($mainCats)) {
                                            $selected = ($mainCat->pc_id == $mainCatId) ? 'selected="selected"' : '';
                                            echo '<option value="' . (int)$mainCat->pc_id . '" ' . $selected . '>' . 
                                                 htmlspecialchars($mainCat->pc_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Category:</label>
                                <div class="col-sm-8">
                                    <select id="pc_id" name="pc_id" onchange="showSubcat();">
                                        <?php
                                        $catSql = "SELECT * FROM product_category WHERE pc_parent_id != '0' AND pc_parent_id = ? AND pc_status = '1'";
                                        $stmt = mysqli_prepare($con, $catSql);
                                        if ($stmt) {
                                            mysqli_stmt_bind_param($stmt, "i", $mainCatId);
                                            mysqli_stmt_execute($stmt);
                                            $catResult = mysqli_stmt_get_result($stmt);
                                            while ($catRow = mysqli_fetch_object($catResult)) {
                                                $selected = ($catRow->pc_id == $row->pc_parent_id) ? 'selected="selected"' : '';
                                                echo '<option value="' . (int)$catRow->pc_id . '" ' . $selected . '>' . 
                                                     htmlspecialchars($catRow->pc_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                            }
                                            mysqli_stmt_close($stmt);
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Sub-Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Sub-Category:</label>
                                <div class="col-sm-8">
                                    <select id="so_pc_id" name="so_pc_id">
                                        <option value="0">- Select Sub-Category -</option>
                                        <?php
                                        $subCatSql = "SELECT * FROM product_category WHERE pc_parent_id = ?";
                                        $stmt = mysqli_prepare($con, $subCatSql);
                                        if ($stmt) {
                                            mysqli_stmt_bind_param($stmt, "i", $row->pc_parent_id);
                                            mysqli_stmt_execute($stmt);
                                            $subCatResult = mysqli_stmt_get_result($stmt);
                                            while ($subCatRow = mysqli_fetch_object($subCatResult)) {
                                                $selected = ($subCatRow->pc_id == $row->so_pc_id) ? 'selected="selected"' : '';
                                                echo '<option value="' . (int)$subCatRow->pc_id . '" ' . $selected . '>' . 
                                                     htmlspecialchars($subCatRow->pc_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                            }
                                            mysqli_stmt_close($stmt);
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Location Preferences -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Location Preferences:</label>
                                <div class="radio col-sm-8">
                                    <?php
                                    $locations = [
                                        'abroad' => 'Abroad Only',
                                        'any' => 'Abroad + Domestic',
                                        'domestic' => 'Domestic Only',
                                        'my_city' => 'My City Only'
                                    ];
                                    $currentLocation = $row->so_preferred_buyer_location ?? 'any';
                                    foreach ($locations as $value => $label):
                                    ?>
                                        <label style="margin-right:15px;">
                                            <input type="radio" name="so_preferred_buyer_location" class="ace" 
                                                   value="<?php echo $value; ?>" 
                                                   <?php echo ($currentLocation === $value) ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> <?php echo $label; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Validity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Validity:</label>
                                <div class="col-sm-8">
                                    <div class="radio">
                                        <?php
                                        $validityOptions = [
                                            '30' => '1 Month',
                                            '90' => '3 Months',
                                            '365' => '1 Year'
                                        ];
                                        $currentValidity = (string)($row->so_validity ?? '90');
                                        foreach ($validityOptions as $value => $label):
                                        ?>
                                            <label style="margin-right:15px;">
                                                <input name="so_validity" class="ace" type="radio" 
                                                       value="<?php echo $value; ?>" 
                                                       <?php echo ($currentValidity === $value) ? 'checked="checked"' : ''; ?>/>
                                                <span class="lbl"> <?php echo $label; ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Posting Date (Read-only) -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Posting Date:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php echo !empty($row->so_posting_date) ? date("d-M-Y", strtotime($row->so_posting_date)) : 'N/A'; ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Current Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Image:</label>
                                <div class="col-sm-8">
                                    <?php if (!empty($row->so_pic) && file_exists("../upload/sale_offer/" . $row->so_pic)): ?>
                                        <img src="../upload/sale_offer/<?php echo htmlspecialchars($row->so_pic, ENT_QUOTES, 'UTF-8'); ?>" 
                                             style="max-width:200px; max-height:150px;" alt="Sale Offer Image"/>
                                    <?php else: ?>
                                        <img src="../upload/sale_offer/no-image.png" style="max-width:200px; max-height:150px;" alt="No Image"/>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Upload New Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload New Image:</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="so_pic" id="so_pic" type="file" accept="image/*">
                                    </div>
                                    <span class="help-block">Leave empty to keep current image. Allowed: JPG, PNG, GIF (Max 2MB)</span>
                                </div>
                            </div>

                            <!-- Approval Status -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Approval Status:</label>
                                <div class="col-sm-8">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $status = (int)($row->so_approval_status ?? 0);
                                        $statusLabels = [
                                            0 => '<span class="label label-warning">Pending Approval</span>',
                                            1 => '<span class="label label-success">Approved</span>',
                                            2 => '<span class="label label-danger">Disapproved</span>'
                                        ];
                                        echo $statusLabels[$status] ?? '<span class="label label-default">Unknown</span>';
                                        ?>
                                    </label>    
                                </div>
                            </div>            

                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <?php if ((int)($row->so_approval_status ?? 0) === 0): ?>
                                        <button class="btn btn-success" type="submit" name="btnApprove">
                                            <i class="icon-ok bigger-110"></i> Approve
                                        </button>
                                        <button class="btn btn-danger" type="submit" name="btnDisApprove">
                                            <i class="icon-ban-circle bigger-110"></i> Disapprove
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-edit bigger-110"></i> Update
                                    </button>
                                    <a href="selloffer-view.php" class="btn btn-primary">
                                        <i class="icon-arrow-left bigger-110"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <br clear="all"/>
                </div>
            </div>
            <br clear="all" />
        </div>
    </div>
</div>

<?php include "includes/footer.php" ?>

<!-- JavaScript includes and initialization -->
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

<!--[if lte IE 8]>
<script src="assets/js/excanvas.min.js"></script>
<![endif]-->

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

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize file input with validation
        $('#so_pic').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small',
            whitelist: 'gif|png|jpg|jpeg',
            blacklist: 'exe|php|html|js',
            before_change: function(files, dropped) {
                if (files.length > 0) {
                    var file = files[0];
                    var ext = file.name.split('.').pop().toLowerCase();
                    var allowedExts = ['gif', 'png', 'jpg', 'jpeg'];
                    
                    if (allowedExts.indexOf(ext) === -1) {
                        alert('Please upload valid image (JPG, PNG, GIF)');
                        return false;
                    }
                    
                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        return false;
                    }
                }
                return files;
            }
        });
        
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Auto-resize textarea
        $('textarea').autosize({append: "\n"});
    });
</script>

<style>
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }
    .btn {
        margin-right: 5px;
    }
    .radio label {
        margin-right: 15px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>