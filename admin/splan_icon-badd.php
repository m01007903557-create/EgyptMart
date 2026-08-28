<?php
/**
 * File: splan_icon-badd.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعيين خطة العضوية للشركات
 * Assign membership plans to companies
 * 
 * Features:
 * - اختيار الشركة من القائمة
 * - اختيار خطة العضوية
 * - تحديد تاريخ انتهاء الصلاحية
 * - إزالة الخطط القديمة تلقائياً
 * 
 * @author Keshav Kalra
 * @copyright 2016
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common.php";

// Check if user is logged in
check_admin_login();

/**
 * Class BusinessPlanAssigner
 * 
 * Handles business plan assignment operations
 */
class BusinessPlanAssigner {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Get all companies with names
     * 
     * @return array List of companies
     */
    public function getCompanies(): array {
        $companies = [];
        $sql = "SELECT bnsprof_id, bnsprof_compname FROM business_profile 
                WHERE bnsprof_compname != '' AND bnsprof_compname IS NOT NULL 
                ORDER BY bnsprof_compname";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $companies[] = [
                    'id' => (int)$row['bnsprof_id'],
                    'name' => $row['bnsprof_compname']
                ];
            }
        }
        
        return $companies;
    }
    
    /**
     * Get all active membership plans
     * 
     * @return array List of plans
     */
    public function getPlans(): array {
        $plans = [];
        $sql = "SELECT mp_id, mst_name FROM smembership_icon_plan 
                WHERE mp_status = 1 ORDER BY mst_name";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $plans[] = [
                    'id' => (int)$row['mp_id'],
                    'name' => $row['mst_name']
                ];
            }
        }
        
        return $plans;
    }
    
    /**
     * Check if company has an existing plan
     * 
     * @param int $companyId Company ID
     * @return bool True if has plan
     */
    public function companyHasPlan(int $companyId): bool {
        $sql = "SELECT COUNT(*) as count FROM plan_member_id WHERE b_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $companyId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return ($row['count'] ?? 0) > 0;
    }
    
    /**
     * Get company's current plan
     * 
     * @param int $companyId Company ID
     * @return array|null Current plan details
     */
    public function getCompanyCurrentPlan(int $companyId): ?array {
        $sql = "SELECT pm.*, sp.mst_name 
                FROM plan_member_id pm
                LEFT JOIN smembership_icon_plan sp ON pm.p_id = sp.mp_id
                WHERE pm.b_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $companyId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        
        return $row ?: null;
    }
    
    /**
     * Assign plan to company
     * 
     * @param int $companyId Company ID
     * @param int $planId Plan ID
     * @param string $expiryDate Expiry date
     * @return bool Success status
     */
    public function assignPlan(int $companyId, int $planId, string $expiryDate): bool {
        // Convert date to timestamp
        $timestamp = strtotime($expiryDate);
        if ($timestamp === false) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Invalid expiry date</div>';
            return false;
        }
        
        // Start transaction
        mysqli_begin_transaction($this->db);
        
        try {
            // Delete existing plan
            $deleteSql = "DELETE FROM plan_member_id WHERE b_id = ?";
            $deleteStmt = mysqli_prepare($this->db, $deleteSql);
            
            if (!$deleteStmt) {
                throw new Exception("Failed to prepare delete statement");
            }
            
            mysqli_stmt_bind_param($deleteStmt, "i", $companyId);
            if (!mysqli_stmt_execute($deleteStmt)) {
                throw new Exception("Failed to delete existing plan");
            }
            mysqli_stmt_close($deleteStmt);
            
            // Insert new plan
            $insertSql = "INSERT INTO plan_member_id SET b_id = ?, p_id = ?, expiry_date = ?";
            $insertStmt = mysqli_prepare($this->db, $insertSql);
            
            if (!$insertStmt) {
                throw new Exception("Failed to prepare insert statement");
            }
            
            mysqli_stmt_bind_param($insertStmt, "iis", $companyId, $planId, $timestamp);
            
            if (!mysqli_stmt_execute($insertStmt)) {
                throw new Exception("Failed to insert new plan");
            }
            
            mysqli_stmt_close($insertStmt);
            
            // Commit transaction
            mysqli_commit($this->db);
            
            $this->msg = '<div class="alert alert-success"><i class="icon-ok"></i> Plan assigned successfully</div>';
            return true;
            
        } catch (Exception $e) {
            // Rollback on error
            mysqli_rollback($this->db);
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> ' . $e->getMessage() . '</div>';
            return false;
        }
    }
    
    /**
     * Validate input data
     * 
     * @param int $companyId Company ID
     * @param int $planId Plan ID
     * @param string $expiryDate Expiry date
     * @return bool True if valid
     */
    public function validateInput(int $companyId, int $planId, string $expiryDate): bool {
        if ($companyId <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a company</div>';
            return false;
        }
        
        if ($planId <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a plan</div>';
            return false;
        }
        
        if (empty($expiryDate)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select expiry date</div>';
            return false;
        }
        
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $expiryDate);
        if (!$date || $date->format('Y-m-d') !== $expiryDate) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Invalid date format</div>';
            return false;
        }
        
        // Check if date is in the past
        $today = new DateTime('today');
        if ($date < $today) {
            $this->msg = '<div class="alert alert-warning"><i class="icon-warning"></i> Warning: Selected date is in the past</div>';
            // Don't return false, just warning
        }
        
        return true;
    }
}

// Initialize assigner
$assigner = new BusinessPlanAssigner($con);

// Get companies and plans
$companies = $assigner->getCompanies();
$plans = $assigner->getPlans();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['companyname'])) {
    $companyId = (int)($_POST['companyname'] ?? 0);
    $planId = (int)($_POST['buisness_plan'] ?? 0);
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    
    if ($assigner->validateInput($companyId, $planId, $expiryDate)) {
        $assigner->assignPlan($companyId, $planId, $expiryDate);
    }
    
    // Store message in session
    $_SESSION['msg'] = $assigner->msg;
    
    // Redirect to prevent form resubmission
    header("Location: splan_badd.php");
    exit;
}

// Get session message
$msg = $_SESSION['msg'] ?? '';
unset($_SESSION['msg']);
?>

<?php include "includes/admin-top.php" ?>

<!-- Bootstrap Select CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/css/bootstrap-select.min.css">

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
                        <a href="#">Assign Business Plan</a>
                    </li>
                    <li class="active">Assign Plan</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Assign Business Plan
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Assign membership plan to company
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        <form class="form-horizontal" action="" method="post">

                            <em style="display:block;margin:5px;">Fields with <span style="color:#F00">*</span> are required.</em>
                            
                            <div id="msg"><?php echo $msg; ?></div>
                            
                            <!-- Company Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Company Name <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="companyname" id="company_name" class="selectpicker form-control" 
                                            data-live-search="true" data-width="fit" title="Select a company..." required>
                                        <option value="">Select Company</option>
                                        <?php foreach ($companies as $company): ?>
                                            <option value="<?php echo (int)$company['id']; ?>">
                                                <?php echo htmlspecialchars($company['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Plan Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Business Plan <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <select name="buisness_plan" id="buisness_plan" class="selectpicker form-control" 
                                            data-width="fit" title="Select a plan..." required>
                                        <option value="">Select Plan</option>
                                        <?php foreach ($plans as $plan): ?>
                                            <option value="<?php echo (int)$plan['id']; ?>">
                                                <?php echo htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Expiry Date <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="date" name="expiry_date" id="expiry_date" 
                                           class="form-control" style="width:200px;" 
                                           min="<?php echo date('Y-m-d'); ?>" required />
                                    <span class="help-block">Select the date when the plan will expire</span>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="clearfix form-actions">
                                <div class="col-md-offset-3 col-md-9">
                                    <button class="btn btn-info" type="submit" name="btnAdd">
                                        <i class="icon-ok bigger-110"></i> Assign Plan
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

<!-- JavaScript includes -->
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

<!-- Bootstrap Select JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.9.4/js/bootstrap-select.min.js"></script>

<!-- Ace scripts -->
<script src="assets/js/ace-elements.min.js"></script>
<script src="assets/js/ace.min.js"></script>

<!-- Inline scripts -->
<script type="text/javascript">
    jQuery(function($) {
        // Initialize Bootstrap Select
        $('.selectpicker').selectpicker();
        
        // Initialize date picker as fallback
        $('#expiry_date').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            startDate: new Date()
        }).next().on(ace.click_event, function() {
            $(this).prev().focus();
        });
        
        // Initialize tooltips
        $('[data-rel=tooltip]').tooltip({container: 'body'});
        
        // Form validation
        $('form').on('submit', function(e) {
            var company = $('#company_name').val();
            var plan = $('#buisness_plan').val();
            var expiry = $('#expiry_date').val();
            
            if (!company) {
                alert('Please select a company');
                e.preventDefault();
                return false;
            }
            
            if (!plan) {
                alert('Please select a plan');
                e.preventDefault();
                return false;
            }
            
            if (!expiry) {
                alert('Please select expiry date');
                e.preventDefault();
                return false;
            }
            
            return confirm('Are you sure you want to assign this plan to the selected company?');
        });
        
        // Load company details when selected
        $('#company_name').on('changed.bs.select', function() {
            var companyId = $(this).val();
            if (companyId) {
                // Optional: Load current plan details via AJAX
                console.log('Selected company:', companyId);
            }
        });
    });
</script>

<style>
    .text-danger {
        color: #d15b47;
    }
    .help-block {
        font-size: 12px;
        color: #777;
        margin-top: 5px;
    }
    .alert {
        margin-bottom: 20px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .bootstrap-select {
        width: auto !important;
        min-width: 300px;
    }
    .btn {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>