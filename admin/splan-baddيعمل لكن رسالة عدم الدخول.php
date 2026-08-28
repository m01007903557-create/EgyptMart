<?php
/**
 * File: splan-badd.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: تعيين خطة العضوية الكاملة للشركات مع إرسال إشعارات
 * Complete membership plan assignment to companies with notifications
 * 
 * Features:
 * - اختيار الشركة
 * - اختيار خطة العضوية والأيقونة
 * - تحديد تاريخ الانتهاء (مع خيار دائم)
 * - إرسال بريد إلكتروني للمستخدم
 * - إنشاء رسالة داخلية
 * - تحديث حالة المستخدم
 * 
 * @author Keshav Kalra
 * @copyright 2016
 */

declare(strict_types=1);

// Start output buffering and session
ob_start();
session_start();

// Include required files
include "../common-support.php";

// Check if user is logged in
check_admin_login();

/**
 * Class BusinessPlanAssignerComplete
 * 
 * Handles complete business plan assignment operations
 */
class BusinessPlanAssignerComplete {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var string Success/error message */
    public string $msg = '';
    
    /** @var array Permanent date constants */
    private const PERMANENT_DATE = '9999-09-09';
    private const PERMANENT_TIMESTAMP = 253402210800; // Approximate timestamp for 9999-09-09
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     */
    public function __construct(mysqli $database) {
        $this->db = $database;
    }
    
    /**
     * Get all companies
     * 
     * @return array List of companies
     */
    public function getCompanies(): array {
        $companies = [];
        $sql = "SELECT bf.*, u.usr_id, u.email, u.name_prefix, u.fname, u.lname 
                FROM business_profile bf 
                JOIN user u ON bf.bnsprof_uid = u.usr_id 
                WHERE bf.bnsprof_compname != '' 
                ORDER BY bf.bnsprof_compname";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $companies[] = [
                    'id' => (int)$row['bnsprof_id'],
                    'name' => $row['bnsprof_compname'],
                    'usr_id' => (int)$row['usr_id'],
                    'email' => $row['email'],
                    'fullname' => trim(($row['name_prefix'] ?? '') . ' ' . ($row['fname'] ?? '') . ' ' . ($row['lname'] ?? ''))
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
        $sql = "SELECT * FROM smembership_plan WHERE mp_status = 1 ORDER BY mp_id";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $plans[] = [
                    'id' => (int)$row['mp_id'],
                    'name' => $row['mst_name'],
                    'icon' => $row['mst_icon'],
                    's_key' => (int)($row['s_key'] ?? 0)
                ];
            }
        }
        
        return $plans;
    }
    
    /**
     * Get all active icon plans
     * 
     * @return array List of icon plans
     */
    public function getIconPlans(): array {
        $iconPlans = [];
        $sql = "SELECT * FROM smembership_icon_plan WHERE mp_status = 1 ORDER BY mst_name";
        
        $result = mysqli_query($this->db, $sql);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $iconPlans[] = [
                    'id' => (int)$row['mp_id'],
                    'name' => $row['mst_name'],
                    'icon' => $row['mst_icon']
                ];
            }
        }
        
        return $iconPlans;
    }
    
    /**
     * Get membership plan details by key
     * 
     * @param int $sKey Plan key
     * @return object|null Plan details
     */
    public function getMembershipPlanByKey(int $sKey): ?object {
        $sql = "SELECT * FROM membership_plan WHERE s_key = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $sKey);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Get billing history
     * 
     * @param int $companyId Company ID
     * @return object|null Billing details
     */
    public function getBillingHistory(int $companyId): ?object {
        $sql = "SELECT * FROM billing_history 
                WHERE bh_type = 5 AND bh_usr_id IN (
                    SELECT bnsprof_uid FROM business_profile WHERE bnsprof_id = ?
                ) 
                ORDER BY bh_updated_date DESC LIMIT 1";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $companyId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Assign plan to company
     * 
     * @param int $companyId Company ID
     * @param int $planId Plan ID
     * @param int $iconId Icon ID
     * @param string $expiryDate Expiry date
     * @return array Result with status and details
     */
    public function assignPlan(int $companyId, int $planId, int $iconId, string $expiryDate): array {
        // Convert date to timestamp
        $timestamp = $expiryDate === self::PERMANENT_DATE 
            ? self::PERMANENT_TIMESTAMP 
            : strtotime($expiryDate);
            
        if ($timestamp === false) {
            return ['success' => false, 'message' => 'Invalid expiry date'];
        }
        
        $startTimestamp = strtotime(date("Y-m-d"));
        
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
            $insertSql = "INSERT INTO plan_member_id SET 
                          b_id = ?, 
                          p_id = ?,
                          icon_id = ?,
                          start_date = ?,
                          expiry_date = ?";
            
            $insertStmt = mysqli_prepare($this->db, $insertSql);
            
            if (!$insertStmt) {
                throw new Exception("Failed to prepare insert statement");
            }
            
            mysqli_stmt_bind_param($insertStmt, "iiiii", $companyId, $planId, $iconId, $startTimestamp, $timestamp);
            
            if (!mysqli_stmt_execute($insertStmt)) {
                throw new Exception("Failed to insert new plan");
            }
            mysqli_stmt_close($insertStmt);
            
            // Determine user type
            $userType = ($planId > 3) ? 2 : 1;
            
            // Update user
            $updateUserSql = "UPDATE user SET usr_mp_id = ?, user_type = ? 
                              WHERE usr_id IN (SELECT bnsprof_uid FROM business_profile WHERE bnsprof_id = ?)";
            
            $updateStmt = mysqli_prepare($this->db, $updateUserSql);
            
            if (!$updateStmt) {
                throw new Exception("Failed to prepare user update statement");
            }
            
            mysqli_stmt_bind_param($updateStmt, "iii", $planId, $userType, $companyId);
            
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception("Failed to update user");
            }
            mysqli_stmt_close($updateStmt);
            
            // Commit transaction
            mysqli_commit($this->db);
            
            return [
                'success' => true,
                'message' => 'Plan assigned successfully',
                'start_date' => date("Y-m-d", $startTimestamp),
                'expiry_date' => date("Y-m-d", $timestamp),
                'is_permanent' => ($expiryDate === self::PERMANENT_DATE)
            ];
            
        } catch (Exception $e) {
            mysqli_rollback($this->db);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get user details by company ID
     * 
     * @param int $companyId Company ID
     * @return object|null User details
     */
    public function getUserDetails(int $companyId): ?object {
        $sql = "SELECT u.*, bf.* FROM user u 
                JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid 
                WHERE bf.bnsprof_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $companyId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Get plan details by ID
     * 
     * @param int $planId Plan ID
     * @return object|null Plan details
     */
    public function getPlanDetails(int $planId): ?object {
        $sql = "SELECT * FROM smembership_plan WHERE mp_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $planId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_object($result);
        mysqli_stmt_close($stmt);
        
        return $row;
    }
    
    /**
     * Send notifications
     * 
     * @param object $user User details
     * @param object $plan Plan details
     * @param object $businessPlan Business plan details
     * @param object $billing Billing details
     * @param string $startDate Start date
     * @param string $expiryDate Expiry date
     * @param bool $isPermanent Is permanent
     */
    public function sendNotifications(object $user, object $plan, object $businessPlan, ?object $billing, string $startDate, string $expiryDate, bool $isPermanent): void {
        
        $fullname = trim(($user->name_prefix ?? '') . ' ' . ($user->fname ?? '') . ' ' . ($user->lname ?? ''));
        
        // Prepare email subject
        $subject = "Latest Membership Status " . ($businessPlan->mst_name ?? '') . " on " . get_page_settings(4);
        $fromName = get_page_settings(4);
        $fromEmail = get_adminemail();
        
        // Include email template
        ob_start();
        include "email/plan_start_notification.php";
        $message1 = ob_get_clean();
        
        // Send email
        sendSMTPMail($user->email, $subject, $message1);
        
        // Create message content
        if ($isPermanent) {
            $message = '<h2 style="text-align:center;"><span style="color: blue">Congratulations!</span></h2>';
            $message .= '<b style="text-align:center;">You are now a <span style="color: blue">' . ($businessPlan->mst_name ?? '') . '</span> with ARABYOS! </b><br>';
            $message .= 'Your promotion is started on ' . $startDate . '. ';
            $message .= 'You can add products and contact buy leads. The promotion will be Permanent until you notified.';
        } else {
            $message = '<h2 style="text-align:center;"><span style="color: blue">Congratulations!</span></h2>';
            $message .= '<b style="text-align:center;">You are now a <span style="color: blue">' . ($businessPlan->mst_name ?? '') . '</span> with ARABYOS! </b><br>';
            $message .= 'Your promotion is started on ' . $startDate . '. ';
            $message .= 'You can add products and contact buy leads. The promotion will be expired on ' . $expiryDate . '. ';
            $message .= 'You will be notified for renewal.';
        }
        
        // Insert internal message
        $insertMsgSql = "INSERT INTO message SET 
                         msg_from = ?,
                         msg_to = ?,
                         msg_subject = ?,
                         msg_message = ?,
                         msg_entity = 'membership_plan',
                         msg_entity_id = ?,
                         msg_date = NOW()";
        
        $adminId = getAdminUserId();
        $companyId = $user->bnsprof_id ?? 0;
        
        $stmt = mysqli_prepare($this->db, $insertMsgSql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "iissi", $adminId, $user->usr_id, $subject, $message, $companyId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }
    
    /**
     * Validate input
     * 
     * @param int $companyId Company ID
     * @param int $planId Plan ID
     * @param int $iconId Icon ID
     * @param string $expiryDate Expiry date
     * @return bool True if valid
     */
    public function validateInput(int $companyId, int $planId, int $iconId, string $expiryDate): bool {
        if ($companyId <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a company</div>';
            return false;
        }
        
        if ($planId <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select a business plan</div>';
            return false;
        }
        
        if ($iconId <= 0) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select an icon plan</div>';
            return false;
        }
        
        if (empty($expiryDate)) {
            $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Please select expiry date</div>';
            return false;
        }
        
        // Validate date format if not permanent
        if ($expiryDate !== self::PERMANENT_DATE) {
            $date = DateTime::createFromFormat('Y-m-d', $expiryDate);
            if (!$date || $date->format('Y-m-d') !== $expiryDate) {
                $this->msg = '<div class="alert alert-danger"><i class="icon-remove"></i> Invalid date format</div>';
                return false;
            }
        }
        
        return true;
    }
}

// Initialize assigner
$assigner = new BusinessPlanAssignerComplete($con);

// Get data for dropdowns
$companies = $assigner->getCompanies();
$plans = $assigner->getPlans();
$iconPlans = $assigner->getIconPlans();

// Handle form submission
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expiry_date']) && !empty($_POST['expiry_date'])) {
    
    $companyId = (int)($_POST['companyname'] ?? 0);
    $planId = (int)($_POST['buisness_plan'] ?? 0);
    $iconId = (int)($_POST['buisness_icon_plan'] ?? 0);
    $expiryDate = trim($_POST['expiry_date'] ?? '');
    
    if ($assigner->validateInput($companyId, $planId, $iconId, $expiryDate)) {
        
        // Assign plan
        $result = $assigner->assignPlan($companyId, $planId, $iconId, $expiryDate);
        
        if ($result['success']) {
            // Get user and plan details for notifications
            $user = $assigner->getUserDetails($companyId);
            $plan = $assigner->getPlanDetails($planId);
            
            if ($user && $plan) {
                $businessPlan = $assigner->getMembershipPlanByKey((int)($plan->s_key ?? 0));
                $billing = $assigner->getBillingHistory($companyId);
                
                $assigner->sendNotifications(
                    $user, 
                    $plan, 
                    $businessPlan ?? (object)['mst_name' => ''],
                    $billing,
                    $result['start_date'],
                    $result['expiry_date'],
                    $result['is_permanent']
                );
            }
            
            $msg = '<div class="alert alert-success"><i class="icon-ok"></i> Your membership assignment has been successfully completed.</div>';
        } else {
            $msg = '<div class="alert alert-danger"><i class="icon-remove"></i> ' . $result['message'] . '</div>';
        }
    } else {
        $msg = $assigner->msg;
    }
}
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
                    <li class="active">Complete Assignment</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        Assign Business Plan
                        <small>
                            <i class="icon-double-angle-right"></i>
                            Complete plan assignment with notifications
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
                                    Business Name <span class="text-danger">*</span>
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

                            <!-- Plan Selection with Icons -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Side Icon <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="row">
                                        <?php foreach ($plans as $plan): ?>
                                            <div class="col-sm-3" style="margin-bottom:10px;">
                                                <label class="radio-inline" style="border:1px solid #ddd; padding:10px; border-radius:5px; width:100%;">
                                                    <input type="radio" name="buisness_plan" value="<?php echo (int)$plan['id']; ?>" required>
                                                    <?php if (!empty($plan['icon'])): ?>
                                                        <img src="./images/<?php echo htmlspecialchars($plan['icon'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                             width="60px" height="60px" style="display:block; margin:0 auto 5px;">
                                                    <?php endif; ?>
                                                    <span style="display:block; text-align:center;"><?php echo htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Icon Plan Selection -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Business Icon <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <div class="row">
                                        <?php foreach ($iconPlans as $iconPlan): ?>
                                            <div class="col-sm-3" style="margin-bottom:10px;">
                                                <label class="radio-inline" style="border:1px solid #ddd; padding:10px; border-radius:5px; width:100%;">
                                                    <input type="radio" name="buisness_icon_plan" value="<?php echo (int)$iconPlan['id']; ?>" required>
                                                    <?php if (!empty($iconPlan['icon'])): ?>
                                                        <img src="./images/<?php echo htmlspecialchars($iconPlan['icon'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                             width="30px" height="30px" style="display:block; margin:0 auto 5px;">
                                                    <?php endif; ?>
                                                    <span style="display:block; text-align:center;"><?php echo htmlspecialchars($iconPlan['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Expiry Date <span class="text-danger">*</span>
                                </label>
                                <div class="col-sm-9">
                                    <input type="date" name="expiry_date" id="expiry_date" 
                                           class="form-control" style="width:200px;" required />
                                    <span class="help-block">Select expiry date or check "Permanent" below</span>
                                </div>
                            </div>

                            <!-- Permanent Checkbox -->
                            <div class="form-group">
                                <label class="col-sm-3 control-label no-padding-right">
                                    Permanent
                                </label>
                                <div class="col-sm-9">
                                    <label>
                                        <input type="checkbox" name="change_date" id="change_date" class="ace">
                                        <span class="lbl"> Check for permanent membership (no expiry)</span>
                                    </label>
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
        
        // Permanent checkbox functionality
        $("#change_date").change(function() {
            if(this.checked) {
                $('#expiry_date').val('9999-09-09');
                $('#expiry_date').prop('readonly', true);
            } else {
                $('#expiry_date').val('');
                $('#expiry_date').prop('readonly', false);
            }
        });
        
        // Initialize date picker
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
            var plan = $('input[name="buisness_plan"]:checked').val();
            var iconPlan = $('input[name="buisness_icon_plan"]:checked').val();
            var expiry = $('#expiry_date').val();
            
            if (!company) {
                alert('Please select a company');
                e.preventDefault();
                return false;
            }
            
            if (!plan) {
                alert('Please select a business plan');
                e.preventDefault();
                return false;
            }
            
            if (!iconPlan) {
                alert('Please select an icon plan');
                e.preventDefault();
                return false;
            }
            
            if (!expiry) {
                alert('Please select expiry date');
                e.preventDefault();
                return false;
            }
            
            return confirm('Are you sure you want to assign this plan to the selected company? This will send notifications to the user.');
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
    .radio-inline {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .radio-inline:hover {
        background-color: #f5f5f5;
        border-color: #aaa !important;
    }
    .radio-inline input[type="radio"] {
        margin-right: 5px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>