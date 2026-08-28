<?php
/**
 * File: product-edit.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة تعديل المنتجات
 * Product edit page
 * 
 * Features:
 * - تعديل جميع بيانات المنتج
 * - التحقق من الكلمات الممنوعة
 * - رفع الصور
 * - إرسال إشعارات البريد الإلكتروني
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Include required files
include "../common.php";

// Check if user is logged in
if (!isset($_SESSION['ad_id_indm']) || empty($_SESSION['ad_id_indm'])) {
    header('Location: index.php');
    exit;
}

/**
 * Class EditProduct
 * 
 * Handles product editing operations
 */
class EditProduct {
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var int Product ID */
    private int $pd_id;
    
    /** @var string Product code */
    private string $pd_code = '';
    
    /** @var int Category ID */
    private int $cat_id = 0;
    
    /** @var int Subcategory ID */
    private int $pd_subcat_id = 0;
    
    /** @var string Product title */
    private string $pd_title = '';
    
    /** @var array Payment methods */
    private array $pd_payment = [];
    
    /** @var string Product description */
    private string $pd_desc = '';
    
    /** @var float Price from */
    private float $pd_fob_price = 0.0;
    
    /** @var float Price to */
    private float $pd_fob_price2 = 0.0;
    
    /** @var int Currency ID */
    private int $pd_currency = 0;
    
    /** @var string Preferred buyer location */
    private string $pd_preferred_buyer_location = 'any';
    
    /** @var int Minimum order quantity */
    private int $pd_min_order_qty = 0;
    
    /** @var int Measurement unit ID */
    private int $pd_unit = 0;
    
    /** @var string Port of dispatch */
    private string $pd_pod = '';
    
    /** @var string Production capacity */
    private string $pd_pn_capct = '';
    
    /** @var string Delivery time */
    private string $pd_dlv_time = '';
    
    /** @var string Packing details */
    private string $pd_pck_dets = '';
    
    /** @var int Hot status (0/1) */
    private int $pd_hot = 0;
    
    /**
     * Constructor
     * 
     * @param int $pd_id Product ID
     */
    public function __construct(int $pd_id) {
        $this->pd_id = $pd_id;
    }
    
    /**
     * Get product details
     * 
     * @return object|null Product details
     */
    public function getDetails(): ?object {
        global $con;
        
        $sql = "SELECT p.*, pc.pc_parent_id, pc.pc_name as category_name 
                FROM products p
                JOIN product_category pc ON p.pd_subcat_id = pc.pc_id 
                WHERE p.pd_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->pd_id);
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
        global $con;
        
        // Get bad words
        $badWords = [];
        $result = mysqli_query($con, "SELECT bd_word FROM bad_word");
        while ($row = mysqli_fetch_object($result)) {
            $badWords[] = strtoupper($row->bd_word);
        }
        
        $titleUpper = strtoupper($this->pd_title);
        $descUpper = strtoupper($this->pd_desc);
        
        // Check category
        if ($this->cat_id === 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose the Product Category here.</div>';
            return false;
        }
        
        // Check subcategory
        if ($this->pd_subcat_id === 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please choose the Product subcategory here.</div>';
            return false;
        }
        
        // Check title
        if (empty($this->pd_title)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter the Product Name.</div>';
            return false;
        }
        
        // Check title for bad words
        foreach ($badWords as $badWord) {
            if (strpos($titleUpper, $badWord) !== false) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> You can\'t post words like ' . htmlspecialchars($badWord) . ' in Product Name.</div>';
                return false;
            }
        }
        
        // Check description length
        if (strlen($this->pd_desc) > 4000) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please check that Product Description cannot have more than 4000 characters.</div>';
            return false;
        }
        
        // Check description for bad words
        foreach ($badWords as $badWord) {
            if (strpos($descUpper, $badWord) !== false) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> You can\'t post words like ' . htmlspecialchars($badWord) . ' in Product Description.</div>';
                return false;
            }
        }
        
        // Check prices
        if ($this->pd_fob_price <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter valid From Price.</div>';
            return false;
        }
        
        if ($this->pd_fob_price2 <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter valid To Price.</div>';
            return false;
        }
        
        if ($this->pd_currency === 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Currency.</div>';
            return false;
        }
        
        // Check MOQ
        if ($this->pd_min_order_qty <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please enter valid Minimum order quantity.</div>';
            return false;
        }
        
        // Check unit
        if ($this->pd_unit === 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select Measurement Unit.</div>';
            return false;
        }
        
        return true;
    }
    
    /**
     * Update product
     */
    public function update(): void {
        global $con;
        
        // Process payment methods
        $paymentString = !empty($this->pd_payment) ? implode(',', $this->pd_payment) : '';
        
        $sql = "UPDATE products SET
                pd_subcat_id = ?,
                pd_title = ?,
                pd_code = ?,
                pd_desc = ?,
                pd_payment = ?,
                pd_fob_price = ?,
                pd_fob_price2 = ?,
                pd_currency = ?,
                pd_preferred_buyer_location = ?,
                pd_min_order_qty = ?,
                pd_unit = ?,
                pd_pod = ?,
                pd_pn_capct = ?,
                pd_dlv_time = ?,
                pd_pck_dets = ?,
                pd_hot = ?
                WHERE pd_id = ?";
        
        $stmt = mysqli_prepare($con, $sql);
        
        if (!$stmt) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Database error</div>';
            return;
        }
        
        mysqli_stmt_bind_param(
            $stmt,
            "issssdddsiissssii",
            $this->pd_subcat_id,
            $this->pd_title,
            $this->pd_code,
            $this->pd_desc,
            $paymentString,
            $this->pd_fob_price,
            $this->pd_fob_price2,
            $this->pd_currency,
            $this->pd_preferred_buyer_location,
            $this->pd_min_order_qty,
            $this->pd_unit,
            $this->pd_pod,
            $this->pd_pn_capct,
            $this->pd_dlv_time,
            $this->pd_pck_dets,
            $this->pd_hot,
            $this->pd_id
        );
        
        if (mysqli_stmt_execute($stmt)) {
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Product updated successfully</div>';
            $this->sendUpdateNotification();
        } else {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Update failed</div>';
        }
        
        mysqli_stmt_close($stmt);
    }
    
    /**
     * Send update notification email
     */
    private function sendUpdateNotification(): void {
        global $con;
        
        // Get user details
        $userSql = "SELECT u.* FROM user u 
                    WHERE u.usr_id = (SELECT pd_uid FROM products WHERE pd_id = ?)";
        $stmt = mysqli_prepare($con, $userSql);
        
        if (!$stmt) {
            return;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->pd_id);
        mysqli_stmt_execute($stmt);
        $userResult = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_object($userResult);
        mysqli_stmt_close($stmt);
        
        if (!$user) {
            return;
        }
        
        // Get product details
        $productSql = "SELECT pd_title FROM products WHERE pd_id = ?";
        $stmt = mysqli_prepare($con, $productSql);
        mysqli_stmt_bind_param($stmt, "i", $this->pd_id);
        mysqli_stmt_execute($stmt);
        $productResult = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_object($productResult);
        mysqli_stmt_close($stmt);
        
        $fullName = trim(($user->name_prefix ?? '') . ' ' . ($user->fname ?? '') . ' ' . ($user->lname ?? ''));
        $productName = $product->pd_title ?? '';
        
        // Prepare email
        $siteName = get_page_settings(4);
        $subject = "Your Product Has Been Updated From " . $siteName;
        $fromName = $siteName;
        $fromEmail = get_adminemail();
        
        $message = $fullName . "<br /><br />";
        $message .= "Your Product <b>" . htmlspecialchars($productName) . "</b> Has Been Updated By " . $siteName . " Admin";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: $fromName <$fromEmail>\r\n";
        
        if (mail($user->email, $subject, $message, $headers)) {
            header('Location: ../product-email.php?admn_pd_id=' . $this->pd_id);
            exit;
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
            if (in_array($name, ['pd_fob_price', 'pd_fob_price2'])) {
                $this->$name = (float)$value;
            } elseif (in_array($name, ['cat_id', 'pd_subcat_id', 'pd_currency', 'pd_min_order_qty', 'pd_unit', 'pd_hot'])) {
                $this->$name = (int)$value;
            } elseif ($name === 'pd_payment' && is_array($value)) {
                $this->$name = array_map('intval', $value);
            } else {
                $this->$name = $value;
            }
        }
    }
}

// Handle session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);

// Get product ID
$productId = isset($_GET['fid']) ? (int)$_GET['fid'] : 0;
if ($productId === 0) {
    header("Location: product-view.php");
    exit;
}

// Initialize edit object
$editProduct = new EditProduct($productId);
$row = $editProduct->getDetails();

if (!$row) {
    header("Location: product-view.php");
    exit;
}

// Handle form submission
if (isset($_POST['btnUpdate'])) {
    $editProduct->cat_id = $_POST['cat_id'] ?? 0;
    $editProduct->pd_subcat_id = $_POST['pd_subcat_id'] ?? 0;
    $editProduct->pd_code = $_POST['pd_code'] ?? '';
    $editProduct->pd_title = $_POST['pd_title'] ?? '';
    $editProduct->pd_payment = $_POST['pd_payment'] ?? [];
    $editProduct->pd_desc = $_POST['pd_desc'] ?? '';
    $editProduct->pd_fob_price = $_POST['pd_fob_price'] ?? 0;
    $editProduct->pd_fob_price2 = $_POST['pd_fob_price2'] ?? 0;
    $editProduct->pd_currency = $_POST['pd_currency'] ?? 0;
    $editProduct->pd_preferred_buyer_location = $_POST['pd_preferred_buyer_location'] ?? 'any';
    $editProduct->pd_min_order_qty = $_POST['pd_min_order_qty'] ?? 0;
    $editProduct->pd_unit = $_POST['pd_unit'] ?? 0;
    $editProduct->pd_pod = $_POST['pd_pod'] ?? '';
    $editProduct->pd_pn_capct = $_POST['pd_pn_capct'] ?? '';
    $editProduct->pd_dlv_time = $_POST['pd_dlv_time'] ?? '';
    $editProduct->pd_pck_dets = $_POST['pd_pck_dets'] ?? '';
    $editProduct->pd_hot = $_POST['pd_hot'] ?? 0;
    
    if ($editProduct->validate()) {
        $editProduct->update();
    }
    
    $_SESSION['msg'] = $editProduct->msg;
    header("Location: product-edit.php?fid=" . $productId);
    exit;
}

// Get payment methods
$paymentMethods = [];
$paymentResult = mysqli_query($con, "SELECT * FROM payment_gateway ORDER BY pg_name");
while ($paymentRow = mysqli_fetch_object($paymentResult)) {
    $paymentMethods[] = $paymentRow;
}

// Get measurement units
$units = [];
$unitResult = mysqli_query($con, "SELECT * FROM measurement_unit WHERE mu_status = 1 ORDER BY mu_name");
while ($unitRow = mysqli_fetch_object($unitResult)) {
    $units[] = $unitRow;
}

// Get countries for currency
$countries = [];
$countryResult = mysqli_query($con, "SELECT * FROM country WHERE cn_status = 1 ORDER BY cn_name");
while ($countryRow = mysqli_fetch_object($countryResult)) {
    $countries[] = $countryRow;
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

        <script type="text/javascript">
            function showCategory() {
                var pc_id = document.getElementById('mcat_id').value;
                $.post("ajax-file/showSubcat.php", {id: pc_id}, function(data) {
                    $('#cat_id').html(data);
                });
            }
            
            function showSubcat() {
                var id = document.getElementById('cat_id').value;
                $.post("ajax-file/showSubcat.php", {id: id}, function(data) {
                    $('#pd_subcat_id').html(data);
                });
            }
            
            function validateForm() {
                var mcat_id = document.getElementById('mcat_id');
                var cat_id = document.getElementById('cat_id');
                var pd_subcat_id = document.getElementById('pd_subcat_id');
                var pd_title = document.getElementById('pd_title');
                var pd_desc = document.getElementById('pd_desc');
                var pd_min_order_qty = document.getElementById('pd_min_order_qty');
                var pd_unit = document.getElementById('pd_unit');
                
                var message = "";
                var valid = true;
                
                if (mcat_id.value == '') {
                    message = "Please choose the Product Main Category here.";
                    mcat_id.focus();
                    valid = false;
                } else if (cat_id.value == '') {
                    message = "Please choose the Product Category here.";
                    cat_id.focus();
                    valid = false;
                } else if (pd_subcat_id.value == '') {
                    message = "Please choose the Product Subcategory here";
                    pd_subcat_id.focus();
                    valid = false;
                } else if (pd_title.value == '') {
                    message = "Please enter the Product Name.";
                    pd_title.focus();
                    valid = false;
                } else if (pd_desc.value.length > 4000) {
                    message = "Please check that Product Description cannot have more than 4000 characters.";
                    pd_desc.focus();
                    valid = false;
                } else if (pd_min_order_qty.value == '' || pd_min_order_qty.value == '0') {
                    message = "Please enter Minimum order quantity.";
                    pd_min_order_qty.value = '';
                    pd_min_order_qty.focus();
                    valid = false;
                } else if (pd_min_order_qty.value != '' && pd_min_order_qty.value != '0' && isNaN(pd_min_order_qty.value)) {
                    message = "Minimum order quantity must be numeric.";
                    pd_min_order_qty.focus();
                    valid = false;
                } else if (pd_min_order_qty.value != '' && !isNaN(pd_min_order_qty.value) && pd_unit.value == '0') {
                    message = "Please select Measurement Unit.";
                    pd_unit.focus();
                    valid = false;
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
                        <a href="product-view.php">Manage Products</a>
                    </li>
                    <li class="active">Product Details</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Manage Products
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Product Details
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data" 
                              onsubmit="return validateForm();">

                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Date:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo date('d M, Y', strtotime($row->pd_date ?? 'now')); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Posted By -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Posted By:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $userToken = rand(1000, 9999) . md5((string)($row->pd_uid ?? 0));
                                    $userName = ucfirst(user_info($row->pd_uid ?? 0, 'fname')) . ' ' . 
                                                ucfirst(user_info($row->pd_uid ?? 0, 'lname'));
                                    ?>
                                    <a href="user-details.php?token=<?php echo $userToken; ?>">
                                        <?php echo htmlspecialchars($userName, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Product Code -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Code:</label>
                                <div class="col-sm-9">
                                    <input name="pd_code" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->pd_code ?? '', ENT_QUOTES, 'UTF-8'); ?>"/>
                                </div>
                            </div>
                            
                            <!-- Main Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Main Category:</label>
                                <div class="col-sm-9">
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
                                <div class="col-sm-9">
                                    <select id="cat_id" name="cat_id" onchange="showSubcat();">
                                        <?php
                                        $catSql = "SELECT * FROM product_category WHERE pc_parent_id != '0' AND pc_parent_id = ?";
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
                                <div class="col-sm-9">
                                    <select id="pd_subcat_id" name="pd_subcat_id">
                                        <?php
                                        $subCatSql = "SELECT * FROM product_category WHERE pc_parent_id != '0' AND pc_parent_id = ?";
                                        $stmt = mysqli_prepare($con, $subCatSql);
                                        if ($stmt) {
                                            mysqli_stmt_bind_param($stmt, "i", $row->pc_parent_id);
                                            mysqli_stmt_execute($stmt);
                                            $subCatResult = mysqli_stmt_get_result($stmt);
                                            while ($subCatRow = mysqli_fetch_object($subCatResult)) {
                                                $selected = ($subCatRow->pc_id == $row->pd_subcat_id) ? 'selected="selected"' : '';
                                                echo '<option value="' . (int)$subCatRow->pc_id . '" ' . $selected . '>' . 
                                                     htmlspecialchars($subCatRow->pc_name, ENT_QUOTES, 'UTF-8') . '</option>';
                                            }
                                            mysqli_stmt_close($stmt);
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Title:</label>
                                <div class="col-sm-9">
                                    <input name="pd_title" id="pd_title" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->pd_title ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Payment Methods -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Payment Through:</label>
                                <div class="checkbox col-sm-8">
                                    <?php
                                    $selectedPayments = explode(",", $row->pd_payment ?? '');
                                    foreach ($paymentMethods as $payment) {
                                        $checked = in_array((string)$payment->id, $selectedPayments) ? 'checked="checked"' : '';
                                        echo '<label style="margin-right:15px;">';
                                        echo '<input class="ace" name="pd_payment[]" value="' . (int)$payment->id . '" ' . $checked . ' type="checkbox" />';
                                        echo '<span class="lbl"> ' . htmlspecialchars(ucwords($payment->pg_name ?? ''), ENT_QUOTES, 'UTF-8') . '</span>';
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Description:</label>
                                <div class="col-sm-8">
                                    <textarea id="pd_desc" name="pd_desc" style="height: 132px;" 
                                              class="autosize-transition form-control"><?php 
                                        echo htmlspecialchars($row->pd_desc ?? '', ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                </div>
                            </div>

                            <!-- Price From -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Price From:</label>
                                <div class="col-sm-9">
                                    <input name="pd_fob_price" id="pd_fob_price" class="col-xs-10 col-sm-5" type="number" step="0.01" 
                                           value="<?php echo (float)($row->pd_fob_price ?? 0); ?>" />
                                </div>
                            </div>
                            
                            <!-- Price To -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Price To:</label>
                                <div class="col-sm-9">
                                    <input name="pd_fob_price2" id="pd_fob_price2" class="col-xs-10 col-sm-5" type="number" step="0.01" 
                                           value="<?php echo (float)($row->pd_fob_price2 ?? 0); ?>" />
                                </div>
                            </div>
                            
                            <!-- Currency -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Currency:</label>
                                <div class="col-sm-9">
                                    <select name="pd_currency" id="pd_currency">
                                        <option value="0">- Select Currency -</option>
                                        <?php foreach ($countries as $country): ?>
                                            <option value="<?php echo (int)$country->cn_id; ?>" 
                                                <?php echo ((int)($row->pd_currency ?? 0) === (int)$country->cn_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($country->cn_currency ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
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
                                    $currentLocation = $row->pd_preferred_buyer_location ?? 'any';
                                    foreach ($locations as $value => $label):
                                    ?>
                                        <label style="margin-right:15px;">
                                            <input type="radio" name="pd_preferred_buyer_location" class="ace" 
                                                   value="<?php echo $value; ?>" <?php echo ($currentLocation === $value) ? 'checked="checked"' : ''; ?>/>
                                            <span class="lbl"> <?php echo $label; ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Product Status -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Status:</label>
                                <div class="radio col-sm-8">
                                    <label style="margin-right:15px;">
                                        <input type="radio" name="pd_hot" class="ace" value="1" 
                                               <?php echo ((int)($row->pd_hot ?? 0) === 1) ? 'checked="checked"' : ''; ?>/>
                                        <span class="lbl"> Mark as HOT</span>
                                    </label>
                                    <label style="margin-right:15px;">
                                        <input type="radio" name="pd_hot" class="ace" value="0" 
                                               <?php echo ((int)($row->pd_hot ?? 0) === 0) ? 'checked="checked"' : ''; ?>/>
                                        <span class="lbl"> Default</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Minimum Order Quantity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Minimum Order Quantity:</label>
                                <div class="col-sm-9">
                                    <input name="pd_min_order_qty" id="pd_min_order_qty" class="col-xs-10 col-sm-5" type="number" 
                                           value="<?php echo (int)($row->pd_min_order_qty ?? 0); ?>" />
                                </div>
                            </div>
                            
                            <!-- Measurement Unit -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Measurement Unit:</label>
                                <div class="col-sm-9">
                                    <select id="pd_unit" name="pd_unit">
                                        <option value="0">- Select -</option>
                                        <?php foreach ($units as $unit): ?>
                                            <option value="<?php echo (int)$unit->mu_id; ?>" 
                                                <?php echo ((int)($row->pd_unit ?? 0) === (int)$unit->mu_id) ? 'selected="selected"' : ''; ?>>
                                                <?php echo htmlspecialchars($unit->mu_name ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Port of Dispatch -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Port of Dispatch:</label>
                                <div class="col-sm-9">
                                    <input name="pd_pod" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->pd_pod ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Production Capacity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Production Capacity:</label>
                                <div class="col-sm-9">
                                    <input name="pd_pn_capct" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->pd_pn_capct ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Delivery Time -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Delivery Time:</label>
                                <div class="col-sm-9">
                                    <input name="pd_dlv_time" class="col-xs-10 col-sm-5" type="text" 
                                           value="<?php echo htmlspecialchars($row->pd_dlv_time ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                            </div>
                            
                            <!-- Packing Details -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Packing Details:</label>
                                <div class="col-sm-8">
                                    <textarea name="pd_pck_dets" style="height: 132px;" class="form-control"><?php 
                                        echo htmlspecialchars($row->pd_pck_dets ?? '', ENT_QUOTES, 'UTF-8'); 
                                    ?></textarea>
                                </div>
                            </div>

                            <?php if (!empty($row->pd_pdf_attach)): ?>
                            <!-- PDF Attachment -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Attached PDF Brochure:</label>
                                <div class="col-sm-9">
                                    <a href="lib/download.php?filename=<?php echo urlencode($row->pd_pdf_attach); ?>">
                                        <?php echo htmlspecialchars($row->pd_pdf_attach, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Current Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Current Product Image:</label>
                                <div class="col-sm-9">
                                    <?php 
                                    $images = explode(',', $row->pd_image ?? '');
                                    $mainImage = !empty($images[0]) ? $images[0] : 'noimage.jpg';
                                    ?>
                                    <img src="../upload/myproduct/<?php echo htmlspecialchars($mainImage, ENT_QUOTES, 'UTF-8'); ?>" 
                                         style="max-width:200px; max-height:232px;" alt="Product Image"/>
                                </div>
                            </div>
                            
                            <!-- Upload New Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Upload New Image</label>
                                <div class="col-sm-9">
                                    <div class="ace-file-input" style="width:400px;">
                                        <input name="br_pic" id="id-input-file-2" type="file" accept="image/*">
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
        
        // Initialize autosize for textareas
        $('textarea.autosize-transition').autosize({append: "\n"});
        
        // Initialize date picker
        $('.date-picker').datepicker({autoclose: true}).next().on(ace.click_event, function() {
            $(this).prev().focus();
        });
        
        // Initialize spinner
        $('#spinner1').ace_spinner({
            value: 0,
            min: 0,
            max: 200,
            step: 10,
            btn_up_class: 'btn-info',
            btn_down_class: 'btn-info'
        });
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>