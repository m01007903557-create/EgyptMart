<?php
/**
 * File: productslider-edit.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة تعديل عناصر سلايدر المنتجات
 * Product slider items edit page
 * 
 * Features:
 * - تعديل بيانات السلايدر
 * - تغيير الصور
 * - تحديث معلومات المنتج
 * - اختيار البلدان
 * - تحديد أيقونة العضوية
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Include required files
require_once "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class EditAdvertisement
 * 
 * Handles advertisement editing operations
 */
class EditAdvertisement {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Advertisement ID */
    private int $adv_id;
    
    /** @var string Image filename */
    private string $adv_img = '';
    
    /** @var string Link URL */
    private string $adv_link = '';
    
    /** @var int Image width */
    private int $adv_imagewidth = 0;
    
    /** @var int Image height */
    private int $adv_imageheight = 0;
    
    /** @var string Title/heading */
    private string $adv_title = '';
    
    /** @var string Description/MOQ data */
    private string $adv_description = '';
    
    /** @var string Country IDs (comma-separated) */
    private string $adv_country = '';
    
    /** @var string Icon filename */
    private string $adv_icon = '';
    
    /** @var float Price */
    private float $adv_price = 0.0;
    
    /** @var string Currency code */
    private string $adv_currency = '';
    
    /** @var int MOQ piece/unit */
    private int $adv_piece = 0;
    
    /** @var string Unit type */
    private string $unit_type = '';
    
    /** @var int Supplier country ID */
    private int $adv_sub_country = 0;
    
    /**
     * Constructor
     * 
     * @param int $adv_id Advertisement ID
     */
    public function __construct(int $adv_id) {
        $this->adv_id = $adv_id;
    }
    
    /**
     * Get advertisement details
     * 
     * @return object|null Advertisement object
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT * FROM prodservice_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Validate form data
     * 
     * @return bool Always returns true (can be extended)
     */
    public function validate(): bool {
        return true;
    }
    
    /**
     * Update advertisement
     */
    public function update(): void {
        global $con;
        
        // Handle image upload if provided
        if (!empty($_FILES["adv_img"]["name"])) {
            $this->handleImageUpload();
        } else {
            $this->updateWithoutImage();
        }
    }
    
    /**
     * Handle image upload and update
     */
    private function handleImageUpload(): void {
        global $con;
        
        if ($_FILES["adv_img"]["error"] > 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-warning"></i> Error uploading file</div>';
            return;
        }
        
        // Get existing image to delete
        $existingImage = $this->getExistingImage();
        
        // Delete old image if exists
        if ($existingImage) {
            $this->deleteImageFile($existingImage);
        }
        
        // Process new image
        $this->processAndSaveImage();
        
        // Update database with new image
        $this->updateWithImage();
    }
    
    /**
     * Get existing image filename
     * 
     * @return string|null Image filename
     */
    private function getExistingImage(): ?string {
        global $con;
        
        $sql = "SELECT adv_img FROM prodservice_slider WHERE adv_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->adv_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['adv_img'];
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Delete image file
     * 
     * @param string $filename Image filename
     */
    private function deleteImageFile(string $filename): void {
        $path = __DIR__ . "/../upload/product_slider/" . $filename;
        if (file_exists($path)) {
            @unlink($path);
        }
    }
    
    /**
     * Process and save uploaded image
     */
    private function processAndSaveImage(): void {
        // Generate new filename
        $extension = pathinfo($_FILES['adv_img']['name'], PATHINFO_EXTENSION);
        $this->adv_img = $this->adv_imagewidth . rand(1000, 9999) . $this->adv_imageheight . '.' . $extension;
        
        // Create SimpleImage instance
        $image = new SimpleImage();
        $image->load($_FILES['adv_img']['tmp_name']);
        $image->resize($this->adv_imagewidth, $this->adv_imageheight);
        
        // Save image
        $savePath = __DIR__ . "/../upload/product_slider/" . $this->adv_img;
        $image->save($savePath);
    }
    
    /**
     * Update database with new image
     */
    private function updateWithImage(): void {
        global $con;
        
        $sql = "UPDATE prodservice_slider SET
                adv_img = ?,
                adv_link = ?,
                adv_imagewidth = ?,
                adv_imageheight = ?,
                adv_title = ?,
                adv_description = ?,
                adv_country = ?,
                adv_icon = ?,
                adv_price = ?,
                adv_currency = ?,
                adv_piece = ?,
                unit_type = ?,
                slider_supplier_country = ?
                WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-warning"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssiisssssdsisi",
            $this->adv_img,
            $this->adv_link,
            $this->adv_imagewidth,
            $this->adv_imageheight,
            $this->adv_title,
            $this->adv_description,
            $this->adv_country,
            $this->adv_icon,
            $this->adv_price,
            $this->adv_currency,
            $this->adv_piece,
            $this->unit_type,
            $this->adv_sub_country,
            $this->adv_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-warning"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Update database without changing image
     */
    private function updateWithoutImage(): void {
        global $con;
        
        $sql = "UPDATE prodservice_slider SET
                adv_link = ?,
                adv_title = ?,
                adv_description = ?,
                adv_country = ?,
                adv_icon = ?,
                adv_price = ?,
                adv_currency = ?,
                adv_piece = ?,
                unit_type = ?,
                slider_supplier_country = ?
                WHERE adv_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-warning"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "sssssdsdssi",
            $this->adv_link,
            $this->adv_title,
            $this->adv_description,
            $this->adv_country,
            $this->adv_icon,
            $this->adv_price,
            $this->adv_currency,
            $this->adv_piece,
            $this->unit_type,
            $this->adv_sub_country,
            $this->adv_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement updated successfully</div>';
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-warning"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
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

// Get advertisement ID
$advId = isset($_GET['aid']) ? (int)$_GET['aid'] : 0;
if ($advId === 0) {
    header("Location: productslider-view.php");
    exit;
}

// Initialize edit object
$editAdv = new EditAdvertisement($advId);
$row = $editAdv->getDetails();

if (!$row) {
    header("Location: productslider-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editAdv->adv_imagewidth = (int)trim($_POST['adv_imagewidth'] ?? 0);
    $editAdv->adv_imageheight = (int)trim($_POST['adv_imageheight'] ?? 0);
    $editAdv->adv_link = trim($_POST['adv_link'] ?? '');
    $editAdv->adv_title = trim($_POST['adv_title'] ?? '');
    $editAdv->adv_description = trim($_POST['adv_description'] ?? '');
    $editAdv->adv_country = isset($_POST['adv_country']) ? implode(",", array_map('intval', $_POST['adv_country'])) : '';
    $editAdv->adv_icon = $_POST['adv_icon'] ?? 'slider-icon01.jpg';
    $editAdv->adv_price = (float)($_POST['adv_price'] ?? 0);
    $editAdv->adv_currency = $_POST['adv_currency'] ?? 'USD';
    $editAdv->adv_piece = (int)($_POST['adv_piece'] ?? 0);
    $editAdv->unit_type = trim($_POST['unit_type'] ?? '');
    $editAdv->adv_sub_country = (int)($_POST['adv_country_sup'] ?? 0);
    
    if ($editAdv->validate()) {
        $editAdv->update();
    }
    
    $_SESSION['msg'] = $editAdv->msg;
    header("Location: productslider-edit.php?aid=" . $advId);
    exit;
}
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
                        <a href="welcome.php">Home</a>
                    </li>
                    <li>
                        <a href="productslider-view.php">Product Slider</a>
                    </li>
                    <li class="active">Edit Slider</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Product Slider
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Edit Slider
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" id="cmp_edit" name="cmp_edit" 
                              method="post" enctype="multipart/form-data">
                            
                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Image Dimensions (Read-only) -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Image Width & Height</label>
                                <div class="col-sm-9">
                                    <input type="hidden" name="adv_imagewidth" value="<?php echo (int)$row->adv_imagewidth; ?>" />
                                    <input type="hidden" name="adv_imageheight" value="<?php echo (int)$row->adv_imageheight; ?>" />
                                    <?php echo (int)$row->adv_imagewidth . " x " . (int)$row->adv_imageheight; ?>
                                </div>
                            </div>

                            <!-- Country Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Country:</label>
                                <div class="col-sm-8">
                                    <?php
                                    $sqlCountry = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
                                    $rsCountry = mysqli_query($con, $sqlCountry);
                                    
                                    $selectedCountries = explode(",", $row->adv_country ?? '');
                                    ?>
                                    <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select" required>
                                        <?php while ($country = mysqli_fetch_object($rsCountry)): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>" 
                                                <?php echo in_array((string)$country->cn_id, $selectedCountries) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country->cn_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="adv_global" value="0">

                            <!-- Link -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Link</label>
                                <div class="col-sm-9">
                                    <input name="adv_link" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->adv_link ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>

                            <!-- Heading -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Heading</label>
                                <div class="col-sm-9">
                                    <input name="adv_title" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->adv_title ?? '', ENT_QUOTES, 'UTF-8'); ?>" required/>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Fob price</label>
                                <div class="col-sm-9">
                                    <input name="adv_price" class="col-xs-10 col-sm-5" type="number" step="any" 
                                           value="<?php echo (float)($row->adv_price ?? 0); ?>" required/>
                                </div>
                            </div>

                            <!-- Currency -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Currency Symbol</label>
                                <div class="col-sm-9">
                                    <select name="adv_currency" class="chosen-select" required>
                                        <option value="">Select</option>
                                        <?php foreach ($currency_symbols as $code => $symbol): ?>
                                            <option value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>" 
                                                <?php echo ($row->adv_currency ?? '') === $code ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($symbol, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- MOQ Unit -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">MOQ unit</label>
                                <div class="col-sm-1">
                                    <select name="adv_piece" class="chosen-select" required>
                                        <option value="">Select</option>
                                        <?php for ($i = 0; $i <= 200; $i++): ?>
                                            <option value="<?php echo $i; ?>" 
                                                <?php echo ((int)($row->adv_piece ?? 0) === $i) ? 'selected="selected"' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                <label class="col-sm-1 control-label no-padding-right">Unit Type</label>
                                <div class="col-sm-2">
                                    <input name="unit_type" type="text" 
                                           value="<?php echo htmlspecialchars($row->unit_type ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                                </div>
                            </div>

                            <!-- Membership Icon -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Membership Icon:</label>
                                <div class="radio col-sm-8">
                                    <?php
                                    $icons = ['slider-icon01.jpg', 'slider-icon02.jpg', 'slider-icon03.jpg'];
                                    foreach ($icons as $icon):
                                    ?>
                                        <label>
                                            <input type="radio" name="adv_icon" class="ace" value="<?php echo $icon; ?>" 
                                                <?php echo (($row->adv_icon ?? '') === $icon || ($icon === 'slider-icon01.jpg' && empty($row->adv_icon))) ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"><img src="../images/<?php echo $icon; ?>" alt="Icon"/></span>
                                        </label>
                                        &nbsp;&nbsp;
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">MOQ data</label>
                                <div class="col-sm-9">
                                    <textarea name="adv_description" rows="10" cols="60" required><?php 
                                        echo htmlspecialchars($row->adv_description ?? '', ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                </div>
                            </div>

                            <!-- Supplier Country -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Supplier Country:</label>
                                <div class="col-sm-8">
                                    <?php
                                    mysqli_data_seek($rsCountry, 0);
                                    ?>
                                    <select id="adv_country_sup" name="adv_country_sup" class="chosen-select">
                                        <option value="">Select Country</option>
                                        <?php while ($country = mysqli_fetch_object($rsCountry)): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>" 
                                                <?php echo ((int)($row->slider_supplier_country ?? 0) === (int)$country->cn_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country->cn_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Current Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Image</label>
                                <div class="col-sm-9">
                                    <img src="../upload/product_slider/<?php echo htmlspecialchars($row->adv_img ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                                         style="max-width:400px; max-height:300px;" alt="Current Image"/>
                                </div>
                            </div>

                            <!-- Upload New Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload New Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="adv_img" id="id-input-file-2" type="file" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnUpdate">
                                        <i class="icon-ok bigger-110"></i> Update
                                    </button>
                                    <button class="btn" type="reset">
                                        <i class="icon-undo bigger-110"></i> Reset
                                    </button>
                                </div>
                            </div>                              
                        </form>        
                    </div>
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
        // Initialize chosen selects
        $(".chosen-select").chosen({width: '400px'});
        
        // Initialize file input
        $('#id-input-file-2').ace_file_input({
            no_file: 'No File ...',
            btn_choose: 'Choose',
            btn_change: 'Change',
            droppable: false,
            thumbnail: 'small'
        });
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize autosize for textarea
        $('textarea[name="adv_description"]').autosize({append: "\n"});
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>