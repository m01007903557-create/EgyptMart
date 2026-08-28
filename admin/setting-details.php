<?php
/**
 * File: setting-details.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض تفاصيل إعدادات الموقع
 * View site settings details
 * 
 * Features:
 * - عرض قيمة إعداد محدد
 * - عرض نوع الإعداد (عنوان، كلمات مفتاحية، وصف، عنوان)
 * - زر العودة إلى قائمة الإعدادات
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
 * Class SiteSettingsDetails
 * 
 * Handles site settings details display
 */
class SiteSettingsDetails {
    
    /** @var int Setting ID */
    private int $st_id;
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $st_id Setting ID
     */
    public function __construct(mysqli $database, int $st_id) {
        $this->db = $database;
        $this->st_id = $st_id;
    }
    
    /**
     * Get setting details
     * 
     * @return object|null Setting details
     */
    public function getDetails(): ?object {
        $sql = "SELECT * FROM site_settings WHERE st_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->st_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Get field display name
     * 
     * @param string $field Field name
     * @return string Display name
     */
    public function getFieldDisplayName(string $field): string {
        return match ($field) {
            'website-title' => 'Website Title :',
            'meta-keywords' => 'Meta Keywords :',
            'meta-description' => 'Meta Description :',
            'address' => 'Address :',
            default => ucfirst(str_replace('-', ' ', $field)) . ' :'
        };
    }
    
    /**
     * Format value for display
     * 
     * @param string $value Raw value
     * @return string Formatted value
     */
    public function formatValue(string $value): string {
        if (empty($value)) {
            return '<span class="text-muted">(Not set)</span>';
        }
        
        return nl2br(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
    }
}

// Get setting ID
$settingId = isset($_GET['sid']) ? (int)$_GET['sid'] : 0;
if ($settingId === 0) {
    header("Location: setting-view.php");
    exit;
}

// Initialize details object
$details = new SiteSettingsDetails($con, $settingId);
$row = $details->getDetails();

if (!$row) {
    header("Location: setting-view.php");
    exit;
}

// Handle back button
if (isset($_POST['btnBack'])) {
    header("Location: setting-view.php");
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
                        <a href="setting-view.php">Site Settings</a>
                    </li>
                    <li class="active">Setting Details</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Site Setting Details
                        <small>
                            <i class="icon-double-angle-right"></i>
                            View setting information
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" name="fd_view" id="fd_view" method="post">
                            
                            <div id="msg"></div>

                            <!-- Setting Information Card -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    <strong>Setting ID:</strong>
                                </label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <?php echo (int)$row->st_id; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    <strong>Field Name:</strong>
                                </label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <?php echo htmlspecialchars($row->st_field ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($row->st_value)): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong><?php echo $details->getFieldDisplayName($row->st_field ?? ''); ?></strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="well well-sm" style="min-height:60px; background:#f9f9f9;">
                                            <?php echo $details->formatValue($row->st_value); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong>Value:</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static text-muted">
                                            (No value set for this setting)
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($row->st_updated_date)): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong>Last Updated:</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static">
                                            <?php echo date('F j, Y, g:i a', strtotime($row->st_updated_date)); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($row->st_status)): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong>Status:</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static">
                                            <?php if ((int)$row->st_status === 1): ?>
                                                <span class="label label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inactive</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Back Button -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-5 col-md-5">
                                    <button class="btn btn-info" type="submit" name="btnBack">
                                        <i class="icon-reply icon-only"></i> Back to Settings List
                                    </button>
                                    <a href="setting-edit.php?sid=<?php echo (int)$row->st_id; ?>" 
                                       class="btn btn-primary">
                                        <i class="icon-edit"></i> Edit Setting
                                    </a>
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
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Initialize chosen selects
        $(".chosen-select").chosen({width: 'auto'});
        
        // Add copy to clipboard functionality
        $('.well-sm').on('click', function() {
            var range = document.createRange();
            range.selectNode(this);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    // Show temporary tooltip or message
                    $(this).append('<span class="copy-notice">Copied!</span>');
                    setTimeout(function() {
                        $('.copy-notice').remove();
                    }, 1500);
                }
            } catch(err) {
                console.log('Copy failed');
            }
            
            window.getSelection().removeAllRanges();
        });
    });
</script>

<style>
    .well-sm {
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .well-sm:hover {
        background-color: #f0f0f0 !important;
    }
    .copy-notice {
        position: absolute;
        background: #2c3e50;
        color: white;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 10px;
    }
    .form-control-static {
        min-height: 34px;
        padding-top: 7px;
    }
    .text-muted {
        color: #999;
        font-style: italic;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>