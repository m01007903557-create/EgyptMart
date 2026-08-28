<?php
/**
 * File: product-details.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة عرض تفاصيل المنتج
 * Product details view page
 * 
 * Features:
 * - عرض جميع تفاصيل المنتج
 * - عرض حالة المنتج (قيد المراجعة، معتمد، مرفوض)
 * - عرض معلومات المالك
 * - عرض الصور والمرفقات
 */

declare(strict_types=1);

// Start output buffering
ob_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_login();

// Get product ID from token
$token = $_GET['token'] ?? '';
$productId = substr($token, 4); // Remove first 4 characters (random number)

// Validate product ID
if (empty($productId) || !ctype_xdigit($productId)) {
    header("Location: product-view.php");
    exit;
}

// Get product details
$sql = "SELECT p.*, pc.pc_name, pc.pc_id as category_id 
        FROM products p 
        JOIN product_category pc ON p.pd_subcat_id = pc.pc_id 
        WHERE md5(p.pd_id) = ?";

$stmt = mysqli_prepare($con, $sql);
if (!$stmt) {
    header("Location: product-view.php");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_object($result);
mysqli_stmt_close($stmt);

if (!$row) {
    header("Location: product-view.php");
    exit;
}

// Get payment gateway names
function getGatewayName(int $id): string {
    global $con;
    static $gateways = [];
    
    if (!isset($gateways[$id])) {
        $sql = "SELECT pg_name FROM payment_gateway WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($gateway = mysqli_fetch_object($result)) {
                $gateways[$id] = $gateway->pg_name;
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    return $gateways[$id] ?? '';
}

// Get measurement unit
function getMeasurementUnit(int $unitId): string {
    global $con;
    static $units = [];
    
    if (!isset($units[$unitId])) {
        $sql = "SELECT mu_name FROM measurement_unit WHERE mu_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $unitId);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($unit = mysqli_fetch_object($result)) {
                $units[$unitId] = $unit->mu_name;
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    return $units[$unitId] ?? '';
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
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">

                            <!-- Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Date:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo date('d M, Y', strtotime($row->pd_date ?? 'now')); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Product Status -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Status:</label>
                                <div class="col-sm-9">
                                    <?php 
                                    $status = (int)($row->pd_status ?? 0);
                                    $statusLabels = [
                                        0 => '<span class="label label-warning">Pending</span>',
                                        1 => '<span class="label label-success">Approved</span>',
                                        2 => '<span class="label label-danger">Rejected</span>'
                                    ];
                                    echo $statusLabels[$status] ?? '<span class="label label-default">Unknown</span>';
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Payment Methods -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Payment Methods:</label>
                                <div class="col-sm-9">
                                    <?php 
                                    $payments = explode(',', $row->pd_payment ?? '');
                                    foreach ($payments as $paymentId) {
                                        if (!empty($paymentId)) {
                                            $gatewayName = getGatewayName((int)$paymentId);
                                            if (!empty($gatewayName)) {
                                                echo '<span class="label label-info" style="margin-right:5px; display:inline-block; margin-bottom:3px;">' 
                                                     . htmlspecialchars($gatewayName, ENT_QUOTES, 'UTF-8') . '</span> ';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Posted By -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Posted By:</label>
                                <div class="col-sm-9">
                                    <?php
                                    $companyName = getCompanyName((int)($row->pd_uid ?? 0));
                                    $userToken = rand(1000, 9999) . md5((string)($row->pd_uid ?? 0));
                                    ?>
                                    <a href="user-details.php?token=<?php echo $userToken; ?>">
                                        <?php echo htmlspecialchars($companyName ?: 'Unknown', ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Product Code -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Code:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pd_code ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Category:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pc_name ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Title -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Title:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pd_title ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Description:</label>
                                <div class="col-sm-8">
                                    <div style="padding-top:4px; border:1px solid #ddd; padding:10px; background:#f9f9f9; border-radius:4px;">
                                        <?php echo nl2br(htmlspecialchars($row->pd_desc ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Price:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $currency = get_product_detail((int)$row->pd_id, 'pd_currency');
                                        $priceFrom = (float)($row->pd_fob_price ?? 0);
                                        $priceTo = (float)($row->pd_fob_price2 ?? 0);
                                        
                                        if ($priceFrom > 0 && $priceTo > 0) {
                                            echo htmlspecialchars($currency . ' ' . number_format($priceFrom, 2) . ' - ' . number_format($priceTo, 2));
                                        } elseif ($priceFrom > 0) {
                                            echo htmlspecialchars($currency . ' ' . number_format($priceFrom, 2));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>

                            <!-- Location Preferences -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Location Preferences:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php
                                        $locationLabels = [
                                            'abroad' => 'Abroad Only',
                                            'any' => 'Anywhere (Abroad + Domestic)',
                                            'domestic' => 'Domestic Only',
                                            'my_city' => 'My City Only'
                                        ];
                                        $location = $row->pd_preferred_buyer_location ?? 'any';
                                        echo htmlspecialchars($locationLabels[$location] ?? $location, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Stock -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Minimum Order Quantity:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        $moq = (int)($row->pd_min_order_qty ?? 0);
                                        $unit = getMeasurementUnit((int)($row->pd_unit ?? 0));
                                        echo $moq . ' ' . htmlspecialchars($unit, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Port of Dispatch -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Port of Dispatch:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pd_pod ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Production Capacity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Production Capacity:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pd_pn_capct ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Delivery Time -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Delivery Time:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pd_dlv_time ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Packing Details -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Packing Details:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php echo htmlspecialchars($row->pd_pck_dets ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Product Type -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Type:</label>
                                <div class="col-sm-9">
                                    <label style="padding-top:4px;">
                                        <?php 
                                        if ((int)($row->pd_hot ?? 0) === 1) {
                                            echo '<span class="label label-danger">Hot Product</span>';
                                        } else {
                                            echo '<span class="label label-default">Default Product</span>';
                                        }
                                        ?>
                                    </label>
                                </div>
                            </div>

                            <!-- PDF Attachment -->
                            <?php if (!empty($row->pd_pdf_attach)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">PDF Brochure:</label>
                                <div class="col-sm-9">
                                    <a href="lib/download.php?filename=<?php echo urlencode($row->pd_pdf_attach); ?>" 
                                       class="btn btn-xs btn-primary" target="_blank">
                                        <i class="icon-file-text"></i> 
                                        <?php echo htmlspecialchars($row->pd_pdf_attach, ENT_QUOTES, 'UTF-8'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Product Images -->
                            <?php if (!empty($row->pd_image)): ?>
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Product Image:</label>
                                <div class="col-sm-9">
                                    <?php 
                                    $images = explode(',', $row->pd_image);
                                    foreach ($images as $index => $image): 
                                        if (!empty($image)):
                                    ?>
                                        <div style="display:inline-block; margin-right:10px; margin-bottom:10px;">
                                            <a href="../upload/myproduct/<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                                <img src="../upload/myproduct/<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" 
                                                     style="max-width:150px; max-height:150px; border:1px solid #ddd; padding:3px;" 
                                                     alt="Product Image <?php echo $index + 1; ?>"/>
                                            </a>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Action Buttons -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <a href="product-edit.php?fid=<?php echo (int)$row->pd_id; ?>" class="btn btn-info">
                                        <i class="icon-edit bigger-110"></i> Edit Product
                                    </a>
                                    <a href="product-view.php" class="btn">
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
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize date picker
        $('.date-picker').datepicker({autoclose: true}).next().on(ace.click_event, function() {
            $(this).prev().focus();
        });
    });
</script>

</body>
</html>

<?php ob_end_flush(); ?>