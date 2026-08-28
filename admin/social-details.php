<?php
/**
 * File: social-details.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض تفاصيل معلومات تسجيل الدخول عبر وسائل التواصل الاجتماعي
 * View social media login information details
 * 
 * Features:
 * - عرض قيمة إعداد وسائل التواصل الاجتماعي
 * - تنسيق اسم الحقل للعرض
 * - زر العودة إلى قائمة وسائل التواصل الاجتماعي
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
 * Class SocialMediaDetails
 * 
 * Handles social media login information details display
 */
class SocialMediaDetails {
    
    /** @var int Social media info ID */
    private int $smli_id;
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param int $smli_id Social media info ID
     */
    public function __construct(mysqli $database, int $smli_id) {
        $this->db = $database;
        $this->smli_id = $smli_id;
    }
    
    /**
     * Get social media details
     * 
     * @return object|null Social media details
     */
    public function getDetails(): ?object {
        $sql = "SELECT * FROM social_media_login_info WHERE smli_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->smli_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Format field name for display
     * 
     * @param string $field Field name (e.g., "facebook-app-id")
     * @return string Formatted field name (e.g., "Facebook App Id")
     */
    public function formatFieldName(string $field): string {
        if (empty($field)) {
            return '';
        }
        
        $parts = explode('-', $field);
        $formattedParts = array_map('ucfirst', $parts);
        return implode(' ', $formattedParts);
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
    
    /**
     * Check if value is a URL
     * 
     * @param string $value Value to check
     * @return bool True if value looks like a URL
     */
    public function isUrl(string $value): bool {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }
    
    /**
     * Get value display HTML
     * 
     * @param string $value Value to display
     * @return string HTML display
     */
    public function getValueDisplay(string $value): string {
        if (empty($value)) {
            return $this->formatValue($value);
        }
        
        if ($this->isUrl($value)) {
            return '<a href="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '" target="_blank">' 
                   . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</a>';
        }
        
        // Check if it might be an API key/secret (mask it partially for security)
        if (strlen($value) > 20 && preg_match('/^[A-Za-z0-9_\-]+$/', $value)) {
            $masked = substr($value, 0, 8) . '...' . substr($value, -4);
            return '<code title="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '">' 
                   . htmlspecialchars($masked, ENT_QUOTES, 'UTF-8') . '</code>';
        }
        
        return $this->formatValue($value);
    }
}

// Get social media info ID
$infoId = isset($_GET['sid']) ? (int)$_GET['sid'] : 0;
if ($infoId === 0) {
    header("Location: social-view.php");
    exit;
}

// Initialize details object
$details = new SocialMediaDetails($con, $infoId);
$row = $details->getDetails();

if (!$row) {
    header("Location: social-view.php");
    exit;
}

// Handle back button
if (isset($_POST['btnBack'])) {
    header("Location: social-view.php");
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
                        <a href="social-view.php">Social Media Settings</a>
                    </li>
                    <li class="active">Details: <?php echo $details->formatFieldName($row->smli_field ?? ''); ?></li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Social Media Setting Details
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <?php echo $details->formatFieldName($row->smli_field ?? ''); ?>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" name="fd_view" id="fd_view" method="post">
                            
                            <div id="msg"></div>

                            <!-- Information Card -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    <strong>Setting ID:</strong>
                                </label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <?php echo (int)$row->smli_id; ?>
                                    </p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    <strong>Field Name:</strong>
                                </label>
                                <div class="col-sm-9">
                                    <p class="form-control-static">
                                        <?php echo htmlspecialchars($row->smli_field ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                        <br>
                                        <small class="text-muted">Display: <?php echo $details->formatFieldName($row->smli_field ?? ''); ?></small>
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($row->smli_value)): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong>Value:</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <div class="well well-sm" style="min-height:60px; background:#f9f9f9; word-break:break-all;">
                                            <?php echo $details->getValueDisplay($row->smli_value); ?>
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

                            <?php if (!empty($row->smli_updated_date)): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong>Last Updated:</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static">
                                            <?php echo date('F j, Y, g:i a', strtotime($row->smli_updated_date)); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($row->smli_status)): ?>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label no-padding-right">
                                        <strong>Status:</strong>
                                    </label>
                                    <div class="col-sm-9">
                                        <p class="form-control-static">
                                            <?php if ((int)$row->smli_status === 1): ?>
                                                <span class="label label-success">Active</span>
                                            <?php else: ?>
                                                <span class="label label-danger">Inactive</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-5 col-md-5">
                                    <button class="btn btn-info" type="submit" name="btnBack">
                                        <i class="icon-reply icon-only"></i> Back to List
                                    </button>
                                    <a href="social-edit.php?sid=<?php echo (int)$row->smli_id; ?>" 
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
        
        // Add copy to clipboard functionality for code blocks
        $('code').on('click', function() {
            var range = document.createRange();
            range.selectNode(this);
            window.getSelection().removeAllRanges();
            window.getSelection().addRange(range);
            
            try {
                var successful = document.execCommand('copy');
                if (successful) {
                    // Show temporary tooltip
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
        min-height: 60px;
        padding: 15px;
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
    code {
        cursor: pointer;
        padding: 5px 8px;
        background: #f5f5f5;
        border: 1px solid #ddd;
        border-radius: 3px;
        color: #d14;
        display: inline-block;
        max-width: 100%;
        overflow-x: auto;
    }
    code:hover {
        background: #e8e8e8;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>