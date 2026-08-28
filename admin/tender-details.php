<?php
/**
 * File: tender-details.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض تفاصيل المناقصة
 * View tender details
 * 
 * Features:
 * - عرض جميع تفاصيل المناقصة
 * - عرض معلومات الشركة المعلنة
 * - عرض الحقول الإضافية
 * - تنسيق التواريخ والعملات
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
 * Class TenderDetails
 * 
 * Handles tender details display operations
 */
class TenderDetails {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Tender ID */
    private int $tenderId;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param string $token MD5 token
     */
    public function __construct(mysqli $database, string $token) {
        $this->db = $database;
        $this->tenderId = $this->decodeToken($token);
    }
    
    /**
     * Decode token to tender ID
     * 
     * @param string $token Token (random number + md5)
     * @return int Tender ID
     */
    private function decodeToken(string $token): int {
        // Remove first 4 characters (random number)
        $md5Hash = substr($token, 4);
        
        $sql = "SELECT tnd_id FROM tender WHERE md5(tnd_id) = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $md5Hash);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (int)$row['tnd_id'];
        }
        
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    /**
     * Get tender details
     * 
     * @return object|null Tender details
     */
    public function getTenderDetails(): ?object {
        $sql = "SELECT * FROM tender WHERE tnd_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->tenderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Get business profile details
     * 
     * @return object|null Business profile details
     */
    public function getBusinessDetails(): ?object {
        $sql = "SELECT bu.* FROM tender t 
                JOIN business_profile bu ON t.tnd_usr_id = bu.bnsprof_uid 
                WHERE t.tnd_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->tenderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Get category path
     * 
     * @param int $subcatId Subcategory ID
     * @return array Category path [main, category, subcategory]
     */
    public function getCategoryPath(int $subcatId): array {
        $sql = "SELECT 
                    m.pc_name as main_category,
                    c.pc_name as category,
                    s.pc_name as subcategory
                FROM product_category s
                JOIN product_category c ON s.pc_parent_id = c.pc_id
                JOIN product_category m ON c.pc_parent_id = m.pc_id
                WHERE s.pc_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return ['', '', ''];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $subcatId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return [
                'main' => $row['main_category'] ?? '',
                'category' => $row['category'] ?? '',
                'subcategory' => $row['subcategory'] ?? ''
            ];
        }
        
        mysqli_stmt_close($stmt);
        return ['', '', ''];
    }
    
    /**
     * Get additional fields
     * 
     * @return array List of additional fields
     */
    public function getAdditionalFields(): array {
        $fields = [];
        
        $sql = "SELECT af.*, tav.tav_value 
                FROM tender_additional_value tav
                JOIN additional_field af ON tav.tav_af_id = af.af_id
                WHERE tav.tav_tnd_id = ?
                GROUP BY af.af_id";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return $fields;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->tenderId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            // Get all values for this field
            $values = [];
            $valueSql = "SELECT tav_value FROM tender_additional_value 
                         WHERE tav_af_id = ? AND tav_tnd_id = ?";
            $valueStmt = mysqli_prepare($this->db, $valueSql);
            
            if ($valueStmt) {
                mysqli_stmt_bind_param($valueStmt, "ii", $row['af_id'], $this->tenderId);
                mysqli_stmt_execute($valueStmt);
                $valueResult = mysqli_stmt_get_result($valueStmt);
                
                while ($valueRow = mysqli_fetch_assoc($valueResult)) {
                    $values[] = $valueRow['tav_value'];
                }
                mysqli_stmt_close($valueStmt);
            }
            
            $fields[] = [
                'label' => $row['af_label'],
                'values' => $values
            ];
        }
        
        mysqli_stmt_close($stmt);
        
        return $fields;
    }
    
    /**
     * Format date
     * 
     * @param string|null $date Date string
     * @return string Formatted date
     */
    public function formatDate(?string $date): string {
        if (empty($date) || $date === '0000-00-00') {
            return 'N/A';
        }
        
        return date("d-M, Y", strtotime($date));
    }
    
    /**
     * Get location text
     * 
     * @param string $location Location code
     * @return string Location text
     */
    public function getLocationText(string $location): string {
        return match ($location) {
            'abroad' => 'Abroad Only',
            'any' => 'Anywhere',
            'domestic' => 'Domestic Only',
            'my_city' => 'My City Only',
            default => $location
        };
    }
    
    /**
     * Get currency symbol
     * 
     * @param int $currencyId Currency ID
     * @return string Currency symbol
     */
    public function getCurrencySymbol(int $currencyId): string {
        return getCurrency($currencyId);
    }
}

// Get token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: tender-view.php");
    exit;
}

// Initialize details object
$details = new TenderDetails($con, $token);
$row = $details->getTenderDetails();

if (!$row) {
    header("Location: tender-view.php");
    exit;
}

// Get business details
$business = $details->getBusinessDetails();

// Get category path
$categoryPath = $details->getCategoryPath((int)$row->tnd_pc_id);

// Get additional fields
$additionalFields = $details->getAdditionalFields();
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
                        <a href="tender-view.php">Manage Tenders</a>
                    </li>
                    <li class="active">Tender Details</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Tender Details
                        <small>
                            <i class="icon-double-angle-right"></i>
                            View complete tender information
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- Basic Information -->
                        <div class="widget-box">
                            <div class="widget-header">
                                <h4 class="widget-title">Basic Information</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Heading:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo htmlspecialchars($row->tnd_heading ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Tender Value:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                if (!empty($row->tnd_value) && $row->tnd_value != '0.00') {
                                                    echo number_format((float)$row->tnd_value, 2) . ' ' . $details->getCurrencySymbol((int)$row->tnd_currency);
                                                } else {
                                                    echo 'Not specified';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Category:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($categoryPath['main'] ?? '', ENT_QUOTES, 'UTF-8'); ?> &raquo;
                                                <?php echo htmlspecialchars($categoryPath['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?> &raquo;
                                                <?php echo htmlspecialchars($categoryPath['subcategory'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Notice Type:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo htmlspecialchars($row->tnd_notice_type ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Quantity:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                if (!empty($row->tnd_qty) && $row->tnd_qty != '0.00') {
                                                    echo (float)$row->tnd_qty . ' ' . measurement_unit((int)$row->tnd_qty_mu_id);
                                                } else {
                                                    echo 'Not specified';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">EMD:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo htmlspecialchars($row->tnd_emd ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Document Fees:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                if (!empty($row->tnd_document_fees) && $row->tnd_document_fees != '0.00') {
                                                    echo number_format((float)$row->tnd_document_fees, 2) . ' ' . $details->getCurrencySymbol((int)$row->tnd_document_fees_currency);
                                                } else {
                                                    echo 'Not specified';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Project Period:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo htmlspecialchars($row->tnd_project_period ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Products:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo nl2br(htmlspecialchars($row->tnd_products ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dates Section -->
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Important Dates</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Publish Date:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $details->formatDate($row->tnd_publish_date ?? ''); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Document Sale Start:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $details->formatDate($row->tnd_docSaleStart_date ?? ''); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Document Sale End:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $details->formatDate($row->tnd_docSaleEnd_date ?? ''); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Document Submit Before:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $details->formatDate($row->tnd_docSubmitBefore_date ?? ''); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Due Date:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $details->formatDate($row->tnd_due_date ?? ''); ?></p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Location & Criteria -->
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Location & Requirements</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Preferred Supplier Location:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static"><?php echo $details->getLocationText($row->tnd_preferred_location ?? 'any'); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Pre-qualification Criteria:</label>
                                        <div class="col-sm-9">
                                            <div class="well well-sm">
                                                <?php echo nl2br(htmlspecialchars(stripslashes($row->tnd_prequalification_criteria ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Detailed Description:</label>
                                        <div class="col-sm-9">
                                            <div class="well well-sm">
                                                <?php echo nl2br(htmlspecialchars(stripslashes($row->tnd_details ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Additional Fields -->
                        <?php if (!empty($additionalFields)): ?>
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Additional Information</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <?php foreach ($additionalFields as $field): ?>
                                        <div class="form-group">
                                            <label class="col-sm-3 control-label"><?php echo htmlspecialchars($field['label'], ENT_QUOTES, 'UTF-8'); ?>:</label>
                                            <div class="col-sm-9">
                                                <p class="form-control-static">
                                                    <?php 
                                                    foreach ($field['values'] as $index => $value) {
                                                        if ($index > 0) echo '<br>';
                                                        echo htmlspecialchars(stripslashes($value), ENT_QUOTES, 'UTF-8');
                                                    }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Company Information -->
                        <?php if ($business): ?>
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Posted By</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Company Name:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <strong><?php echo htmlspecialchars($business->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($business->bnsprof_website)): ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Website:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <a href="<?php echo htmlspecialchars($business->bnsprof_website, ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                                    <?php echo htmlspecialchars($business->bnsprof_website, ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Action Buttons -->
                        <div class="clearfix form-actions">
                            <div class="col-md-offset-3 col-md-9">
                                <a href="tender-edit.php?tid=<?php echo (int)$row->tnd_id; ?>" class="btn btn-info">
                                    <i class="icon-edit bigger-110"></i> Edit Tender
                                </a>
                                <a href="tender-view.php" class="btn btn-primary">
                                    <i class="icon-arrow-left bigger-110"></i> Back to List
                                </a>
                            </div>
                        </div>
                        
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
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
    });
</script>

<style>
    .widget-box {
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
        background: #fff;
    }
    .widget-header {
        background: #f5f5f5;
        padding: 10px 15px;
        border-bottom: 1px solid #ddd;
        border-radius: 4px 4px 0 0;
    }
    .widget-title {
        margin: 0;
        font-size: 14px;
        font-weight: bold;
    }
    .widget-body {
        padding: 15px;
    }
    .well-sm {
        background: #f9f9f9;
        border: 1px solid #e0e5ec;
        padding: 10px;
        min-height: 50px;
        border-radius: 3px;
    }
    .form-control-static {
        min-height: 34px;
        padding-top: 7px;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>