<?php
/**
 * File: productslider-add.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة إضافة عناصر جديدة لسلايدر المنتجات
 * Add new product slider items page
 * 
 * Features:
 * - إضافة صور جديدة للسلايدر
 * - تحديد أبعاد الصورة
 * - اختيار البلدان المستهدفة
 * - إضافة معلومات المنتج
 * - اختيار أيقونة العضوية
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Include required files
require_once "../common.php";

// Check if user is logged in
checkUserLogin();

/**
 * Class AddAdvertisement
 * 
 * Handles advertisement addition operations
 */
class AddAdvertisement {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var string Image size/dimensions */
    private string $imgsize;
    
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
     * @param string $imgsize Image dimensions
     * @param string $adv_img Image filename
     * @param string $adv_link Link URL
     * @param string $adv_title Title
     * @param string $adv_description Description
     * @param array $adv_country Selected countries
     * @param string $adv_icon Icon filename
     * @param float $adv_price Price
     * @param string $adv_currency Currency code
     * @param int $adv_piece MOQ unit
     * @param int $adv_sub_country Supplier country ID
     * @param string $unit_type Unit type
     */
    public function __construct(
        string $imgsize,
        string $adv_img,
        string $adv_link,
        string $adv_title,
        string $adv_description,
        array $adv_country,
        string $adv_icon,
        float $adv_price,
        string $adv_currency,
        int $adv_piece,
        int $adv_sub_country,
        string $unit_type
    ) {
        $this->imgsize = $imgsize;
        $this->adv_img = $adv_img;
        $this->adv_link = $adv_link;
        $this->adv_title = $adv_title;
        $this->adv_description = $adv_description;
        $this->adv_country = implode(",", array_map('intval', $adv_country));
        $this->adv_icon = $adv_icon;
        $this->adv_price = $adv_price;
        $this->adv_currency = $adv_currency;
        $this->adv_piece = $adv_piece;
        $this->adv_sub_country = $adv_sub_country;
        $this->unit_type = $unit_type;
        
        // Store in session for form persistence
        $_SESSION['imgsize'] = $this->imgsize;
        $_SESSION['adv_link'] = $this->adv_link;
    }
    
    /**
     * Validate form data
     * 
     * @return bool True if valid
     */
    public function validate(): bool {
        if (empty($this->imgsize) || $this->imgsize === "0") {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select image size</div>';
            return false;
        }
        
        if (empty($this->adv_link)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter a link</div>';
            return false;
        }
        
        if (empty($this->adv_img)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please upload an image</div>';
            return false;
        }
        
        if (empty($this->adv_title)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter the title</div>';
            return false;
        }
        
        if (empty($this->adv_description)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter the description</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Add advertisement to database
     */
    public function add(): void {
        global $con;
        
        if (empty($_FILES["adv_img"]["name"])) {
            return;
        }
        
        if ($_FILES["adv_img"]["error"] > 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Error uploading file</div>';
            return;
        }
        
        // Parse dimensions from size string (e.g., "150x116")
        $dimensions = explode('x', $this->imgsize);
        $this->adv_imagewidth = (int)($dimensions[0] ?? 150);
        $this->adv_imageheight = (int)($dimensions[1] ?? 116);
        
        // Process and save image
        $this->processAndSaveImage();
        
        // Insert into database
        $this->insertIntoDatabase();
        
        // Clear session variables
        unset($_SESSION['imgsize']);
        unset($_SESSION['adv_link']);
        
        $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Advertisement added successfully.</div>';
    }
    
    /**
     * Process and save uploaded image
     */
    private function processAndSaveImage(): void {
        // Generate unique filename
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
     * Insert record into database
     */
    private function insertIntoDatabase(): void {
        global $con;
        
        $sql = "INSERT INTO prodservice_slider SET
                adv_link = ?,
                adv_img = ?,
                adv_imagewidth = ?,
                adv_imageheight = ?,
                adv_title = ?,
                adv_description = ?,
                adv_country = ?,
                adv_type = '1',
                adv_icon = ?,
                adv_price = ?,
                adv_currency = ?,
                adv_piece = ?,
                unit_type = ?,
                adv_updated_date = NOW(),
                slider_supplier_country = ?,
                adv_status = 1";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "ssiissssdsissi",
            $this->adv_link,
            $this->adv_img,
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
            $this->adv_sub_country
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Failed to add advertisement</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
}

// Handle session messages
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get persisted form values from session
$imgsize = $_SESSION['imgsize'] ?? '';
$adv_link = $_SESSION['adv_link'] ?? '';

// Handle form submission
if (isset($_POST['btnAdd'])) {
    $advertisement = new AddAdvertisement(
        trim($_POST['imgsize'] ?? ''),
        $_FILES['adv_img']['name'] ?? '',
        trim($_POST['adv_link'] ?? ''),
        trim($_POST['adv_title'] ?? ''),
        trim($_POST['adv_description'] ?? ''),
        $_POST['adv_country'] ?? [],
        $_POST['adv_icon'] ?? 'slider-icon01.jpg',
        (float)($_POST['adv_price'] ?? 0),
        $_POST['adv_currency'] ?? '',
        (int)($_POST['adv_piece'] ?? 0),
        (int)($_POST['adv_country_sup'] ?? 0),
        trim($_POST['unit_type'] ?? '')
    );
    
    if ($advertisement->validate()) {
        $advertisement->add();
    }
    
    $_SESSION['msg'] = $advertisement->msg;
    header("Location: productslider-add.php");
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
                    <li class="active">Add Slider</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Product Slider
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Add Slider
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            
                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Image Dimensions -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Width & Height of Image</label>
                                <div class="col-sm-9">
                                    <select name="imgsize" class="chosen-select" style="width:200px;">
                                        <option value="150x116" <?php echo $imgsize === "150x116" ? 'selected="selected"' : ''; ?>>
                                            150 x 116
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <!-- Country Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Target Countries:</label>
                                <div class="col-sm-8">
                                    <?php
                                    $sqlCountry = "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name";
                                    $rsCountry = mysqli_query($con, $sqlCountry);
                                    ?>
                                    <select id="adv_country" name="adv_country[]" multiple="multiple" class="chosen-select" style="width:400px;">
                                        <option value="">Select Countries</option>
                                        <?php while ($country = mysqli_fetch_object($rsCountry)): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>">
                                                <?php echo htmlspecialchars($country->cn_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="adv_global" value="0">

                            <!-- Link -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Link URL</label>
                                <div class="col-sm-9">
                                    <input name="adv_link" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($adv_link, ENT_QUOTES, 'UTF-8'); ?>" 
                                           placeholder="http://example.com/page" required/>
                                </div>
                            </div>

                            <!-- Heading -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Heading/Title</label>
                                <div class="col-sm-9">
                                    <input name="adv_title" class="col-xs-10 col-sm-5" type="text" 
                                           value="" required/>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">FOB Price</label>
                                <div class="col-sm-9">
                                    <input name="adv_price" class="col-xs-10 col-sm-5" type="number" step="any" 
                                           value="" required/>
                                </div>
                            </div>

                            <!-- Currency -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Currency Symbol</label>
                                <div class="col-sm-9">
                                    <select name="adv_currency" class="chosen-select" style="width:200px;" required>
                                        <option value="">Select Currency</option>
                                        <?php foreach ($currency_symbols as $code => $symbol): ?>
                                            <option value="<?php echo htmlspecialchars($code, ENT_QUOTES, 'UTF-8'); ?>">
                                                <?php echo htmlspecialchars($symbol, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- MOQ Unit -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">MOQ Unit</label>
                                <div class="col-sm-1">
                                    <select name="adv_piece" class="chosen-select" style="width:100px;" required>
                                        <option value="">Qty</option>
                                        <?php for ($i = 0; $i <= 200; $i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                                
                                <label class="col-sm-1 control-label no-padding-right">Unit Type</label>
                                <div class="col-sm-2">
                                    <input name="unit_type" type="text" value="" placeholder="e.g., pieces" required>
                                </div>
                            </div>

                            <!-- Membership Icon -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Membership Icon:</label>
                                <div class="radio col-sm-8">
                                    <?php
                                    $icons = [
                                        'slider-icon01.jpg' => 'Icon 1',
                                        'slider-icon02.jpg' => 'Icon 2',
                                        'slider-icon03.jpg' => 'Icon 3'
                                    ];
                                    $first = true;
                                    foreach ($icons as $iconFile => $iconName):
                                    ?>
                                        <label style="margin-right:15px;">
                                            <input type="radio" name="adv_icon" class="ace" value="<?php echo $iconFile; ?>" 
                                                <?php echo $first ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl">
                                                <img src="../images/<?php echo $iconFile; ?>" alt="<?php echo $iconName; ?>" style="vertical-align:middle;"/>
                                            </span>
                                        </label>
                                    <?php 
                                        $first = false;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">MOQ Data / Description</label>
                                <div class="col-sm-9">
                                    <textarea name="adv_description" rows="8" cols="60" required></textarea>
                                </div>
                            </div>

                            <!-- Supplier Country -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Supplier Country:</label>
                                <div class="col-sm-8">
                                    <?php
                                    mysqli_data_seek($rsCountry, 0);
                                    ?>
                                    <select id="adv_country_sup" name="adv_country_sup" class="chosen-select" style="width:300px;">
                                        <option value="">Select Supplier Country</option>
                                        <?php while ($country = mysqli_fetch_object($rsCountry)): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>">
                                                <?php echo htmlspecialchars($country->cn_name, ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Image Upload -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload Product Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="adv_img" id="id-input-file-2" type="file" accept="image/*" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd">
                                        <i class="icon-ok bigger-110"></i> Add
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
        $(".chosen-select").chosen({width: 'auto'});
        
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