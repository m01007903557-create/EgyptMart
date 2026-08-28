<?php
/**
 * File: selloffer_details.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: صفحة عرض تفاصيل عرض البيع
 * View sale offer details page
 * 
 * Features:
 * - عرض جميع تفاصيل عرض البيع
 * - عرض حالة الموافقة
 * - عرض معلومات المستخدم والشركة
 * - الموافقة/رفض العرض
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
 * Class SaleOfferDetails
 * 
 * Handles sale offer details display
 */
class SaleOfferDetails {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int Sale offer ID */
    private int $so_id;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param string $token Token from URL
     */
    public function __construct(mysqli $database, string $token) {
        $this->db = $database;
        $this->so_id = $this->decodeToken($token);
    }
    
    /**
     * Decode token to ID
     * 
     * @param string $token Token (random number + md5)
     * @return int Sale offer ID
     */
    private function decodeToken(string $token): int {
        // Remove first 4 characters (random number)
        $md5Hash = substr($token, 4);
        
        $sql = "SELECT so_id FROM sale_offer WHERE md5(so_id) = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $md5Hash);
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
        $sql = "SELECT s.*, u.*, bf.* 
                FROM sale_offer s
                JOIN user u ON s.so_usr_id = u.usr_id
                JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
                WHERE s.so_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
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
     * Get category path
     * 
     * @param int $subcatId Subcategory ID
     * @return string Category path
     */
    public function getCategoryPath(int $subcatId): string {
        $sql = "SELECT m.pc_name as main, c.pc_sort_name as cat, s.pc_sort_name as subcat 
                FROM product_category m
                JOIN product_category c ON m.pc_id = c.pc_parent_id
                JOIN product_category s ON c.pc_id = s.pc_parent_id
                WHERE s.pc_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        if (!$stmt) {
            return '';
        }
        
        mysqli_stmt_bind_param($stmt, "i", $subcatId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['main'] . " » " . $row['cat'] . " » " . $row['subcat'];
        }
        
        mysqli_stmt_close($stmt);
        return '';
    }
    
    /**
     * Get location display text
     * 
     * @param string $location Location code
     * @param object $row Database row
     * @return string Location text
     */
    public function getLocationText(string $location, object $row): string {
        return match ($location) {
            'any' => 'Anywhere',
            'abroad' => 'Foreign',
            'domestic' => get_country_name($row->country ?? 0),
            'my_city' => (!empty($row->bnsprof_city) && $row->bnsprof_city != '0') 
                ? get_city_name($row->bnsprof_city) 
                : 'My City',
            default => $location
        };
    }
    
    /**
     * Get validity text
     * 
     * @param int $validity Validity in days
     * @return string Validity text
     */
    public function getValidityText(int $validity): string {
        return match ($validity) {
            365 => '1 Year',
            90 => '3 Months',
            default => '1 Month'
        };
    }
    
    /**
     * Get status label HTML
     * 
     * @param int $status Status code
     * @return string HTML label
     */
    public function getStatusLabel(int $status): string {
        return match ($status) {
            0 => '<span class="label label-warning">Pending Approval</span>',
            1 => '<span class="label label-success">Approved</span>',
            2 => '<span class="label label-danger">Disapproved</span>',
            default => '<span class="label label-default">Unknown</span>'
        };
    }
    
    /**
     * Approve sale offer
     * 
     * @return bool Success status
     */
    public function approve(): bool {
        $sql = "UPDATE sale_offer SET so_approval_status = 1, so_approval_date = NOW() WHERE so_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->so_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Disapprove sale offer
     * 
     * @return bool Success status
     */
    public function disapprove(): bool {
        $sql = "UPDATE sale_offer SET so_approval_status = 2, so_approval_date = NOW() WHERE so_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->so_id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        return $result;
    }
    
    /**
     * Get sale offer ID
     * 
     * @return int Sale offer ID
     */
    public function getId(): int {
        return $this->so_id;
    }
}

// Get token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: selloffer-view.php");
    exit;
}

// Initialize details object
$details = new SaleOfferDetails($con, $token);
$row = $details->getDetails();

if (!$row) {
    header("Location: selloffer-view.php");
    exit;
}

// Handle approve
if (isset($_POST['btnApprove'])) {
    $so_id = (int)($_POST['so_id'] ?? 0);
    if ($so_id > 0 && $details->approve()) {
        $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer approved successfully.</div>';
    }
    header("Location: selloffer-details.php?token=" . rand(1000, 9999) . md5((string)$so_id));
    exit;
}

// Handle disapprove
if (isset($_POST['btnDisApprove'])) {
    $so_id = (int)($_POST['so_id'] ?? 0);
    if ($so_id > 0 && $details->disapprove()) {
        $_SESSION['msg'] = '<div class="alert alert-success"><i class="icon-ok"></i> Sale Offer disapproved successfully.</div>';
    }
    header("Location: selloffer-details.php?token=" . rand(1000, 9999) . md5((string)$so_id));
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
                    <li class="active">Sale Offer Details</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Sale Offer Details
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo htmlspecialchars($row->so_service ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post">
                            
                            <input type="hidden" name="so_id" value="<?php echo (int)$row->so_id; ?>" />

                            <div id="msg"><?php echo $msg; ?></div>

                            <!-- Service Name -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Service:</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <?php echo htmlspecialchars($row->so_service ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Description:</label>
                                <div class="col-sm-8">
                                    <div class="well well-sm" style="min-height:100px;">
                                        <?php echo nl2br(htmlspecialchars($row->so_description ?? '', ENT_QUOTES, 'UTF-8')); ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Category -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Category:</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <?php 
                                        $categoryPath = $details->getCategoryPath((int)$row->so_pc_id);
                                        echo htmlspecialchars($categoryPath ?: 'N/A', ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Location Preferences -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Location Preferences:</label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <?php 
                                        echo htmlspecialchars(
                                            $details->getLocationText($row->so_preferred_buyer_location ?? 'any', $row),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                        ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Validity -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Validity:</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <?php echo $details->getValidityText((int)($row->so_validity ?? 90)); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Posting Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Posting Date:</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <?php echo !empty($row->so_posting_date) ? date("d-M-Y", strtotime($row->so_posting_date)) : 'N/A'; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Image -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Image:</label>
                                <div class="col-sm-8">
                                    <?php if (!empty($row->so_pic) && file_exists("../upload/sale_offer/" . $row->so_pic)): ?>
                                        <img src="../upload/sale_offer/<?php echo htmlspecialchars($row->so_pic, ENT_QUOTES, 'UTF-8'); ?>" 
                                             style="max-width:300px; max-height:200px; border:1px solid #ddd; padding:5px;" 
                                             alt="Sale Offer Image"/>
                                    <?php else: ?>
                                        <img src="../upload/sale_offer/no-image.png" style="max-width:300px; max-height:200px;" alt="No Image"/>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Company Information -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Company:</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <strong><?php echo htmlspecialchars($row->bnsprof_compname ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                    </p>
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Contact:</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <?php 
                                        $fullName = trim(($row->name_prefix ?? '') . ' ' . ($row->fname ?? '') . ' ' . ($row->lname ?? ''));
                                        echo htmlspecialchars($fullName ?: 'N/A', ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <br>
                                        <i class="icon-envelope"></i> 
                                        <a href="mailto:<?php echo htmlspecialchars($row->email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($row->email ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                        </a>
                                        <br>
                                        <i class="icon-phone"></i> <?php echo htmlspecialchars($row->mobile1 ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Approval Status -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">Approval Status:</label>
                                <div class="col-sm-8">
                                    <p class="form-control-static">
                                        <?php echo $details->getStatusLabel((int)($row->so_approval_status ?? 0)); ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
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
                                    <a href="selloffer-edit.php?token=<?php echo md5((string)$row->so_id); ?>" 
                                       class="btn btn-info">
                                        <i class="icon-edit bigger-110"></i> Edit
                                    </a>
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
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
    });
</script>

<style>
    .form-control-static {
        padding-top: 7px;
        margin-bottom: 0;
    }
    .well-sm {
        background-color: #f9f9f9;
        border: 1px solid #e0e5ec;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>