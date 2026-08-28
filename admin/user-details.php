<?php
/**
 * File: user-details.php

 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: عرض تفاصيل المستخدم والملف الشخصي للشركة
 * View user details and company profile
 * 
 * Features:
 * - عرض معلومات الحساب
 * - عرض المعلومات الشخصية
 * - عرض الملف الشخصي للشركة
 * - عرض جميع أرقام التسجيل والتراخيص
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
 * Class UserDetails
 * 
 * Handles user details display operations
 */
class UserDetails {
    
    /** @var mysqli Database connection */
    private mysqli $db;
    
    /** @var int User ID */
    private int $userId;
    
    /**
     * Constructor
     * 
     * @param mysqli $database Database connection
     * @param string $token MD5 token
     */
    public function __construct(mysqli $database, string $token) {
        $this->db = $database;
        $this->userId = $this->decodeToken($token);
    }
    
    /**
     * Decode token to user ID
     * 
     * @param string $token Token (random number + md5)
     * @return int User ID
     */
    private function decodeToken(string $token): int {
        // Remove first 4 characters (random number)
        $md5Hash = substr($token, 4);
        
        $sql = "SELECT usr_id FROM user WHERE md5(usr_id) = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return 0;
        }
        
        mysqli_stmt_bind_param($stmt, "s", $md5Hash);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (int)$row['usr_id'];
        }
        
        mysqli_stmt_close($stmt);
        return 0;
    }
    
    /**
     * Get user and business details
     * 
     * @return array|null User details
     */
    public function getDetails(): ?array {
        $sql = "SELECT u.*, bf.* 
                FROM user u
                JOIN business_profile bf ON u.usr_id = bf.bnsprof_uid
                WHERE u.usr_id = ?";
        
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "i", $this->userId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row;
        }
        
        mysqli_stmt_close($stmt);
        return null;
    }
    
    /**
     * Get country name by ID
     * 
     * @param int $countryId Country ID
     * @return string Country name
     */
    public function getCountryName(int $countryId): string {
        return get_country_name($countryId);
    }
    
    /**
     * Get city name by ID
     * 
     * @param int $cityId City ID
     * @return string City name
     */
    public function getCityName(int $cityId): string {
        return get_city_name($cityId);
    }
    
    /**
     * Get state name by ID
     * 
     * @param int $stateId State ID
     * @return string State name
     */
    public function getStateName(int $stateId): string {
        return get_state_name($stateId);
    }
    
    /**
     * Get membership plan name
     * 
     * @param int $planId Plan ID
     * @return string Plan name
     */
    public function getMembershipPlan(int $planId): string {
        $sql = "SELECT mp_name FROM membership_plan WHERE mp_id = ?";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return '';
        }
        
        mysqli_stmt_bind_param($stmt, "i", $planId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['mp_name'] ?? '';
        }
        
        mysqli_stmt_close($stmt);
        return '';
    }
    
    /**
     * Get business type titles from comma-separated IDs
     * 
     * @param string $businessTypeIds Comma-separated business type IDs
     * @return string Formatted business types
     */
    public function getBusinessTypes(string $businessTypeIds): string {
        if (empty($businessTypeIds)) {
            return '';
        }
        
        $ids = explode(',', $businessTypeIds);
        $types = [];
        
        foreach ($ids as $id) {
            $sql = "SELECT bsntyp_title FROM business_type WHERE bsntyp_id = ? AND bsntyp_status = '1'";
            $stmt = mysqli_prepare($this->db, $sql);
            
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                if ($row = mysqli_fetch_object($result)) {
                    $types[] = $row->bsntyp_title;
                }
                mysqli_stmt_close($stmt);
            }
        }
        
        return implode(', ', $types);
    }
    
    /**
     * Get ownership type title
     * 
     * @param int $ownershipTypeId Ownership type ID
     * @return string Ownership type title
     */
    public function getOwnershipType(int $ownershipTypeId): string {
        $sql = "SELECT owntyp_title FROM ownership_type WHERE owntyp_id = ? AND owntyp_status = '1'";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return '';
        }
        
        mysqli_stmt_bind_param($stmt, "i", $ownershipTypeId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_object($result)) {
            mysqli_stmt_close($stmt);
            return $row->owntyp_title ?? '';
        }
        
        mysqli_stmt_close($stmt);
        return '';
    }
    
    /**
     * Get revenue turnover title
     * 
     * @param int $turnoverId Turnover ID
     * @return string Turnover title
     */
    public function getRevenueTurnover(int $turnoverId): string {
        $sql = "SELECT revturnover_title FROM revenue_turnover WHERE revturnover_id = ? AND revturnover_status = '1'";
        $stmt = mysqli_prepare($this->db, $sql);
        
        if (!$stmt) {
            return '';
        }
        
        mysqli_stmt_bind_param($stmt, "i", $turnoverId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_object($result)) {
            mysqli_stmt_close($stmt);
            return $row->revturnover_title ?? '';
        }
        
        mysqli_stmt_close($stmt);
        return '';
    }
    
    /**
     * Get user full name
     * 
     * @param array $row User data
     * @return string Full name
     */
    public function getFullName(array $row): string {
        $parts = [];
        if (!empty($row['name_prefix'])) {
            $parts[] = ucwords($row['name_prefix']);
        }
        if (!empty($row['fname'])) {
            $parts[] = ucwords($row['fname']);
        }
        if (!empty($row['lname'])) {
            $parts[] = ucwords($row['lname']);
        }
        return implode(' ', $parts);
    }
    
    /**
     * Get CEO full name
     * 
     * @param array $row User data
     * @return string CEO full name
     */
    public function getCeoFullName(array $row): string {
        $parts = [];
        if (!empty($row['bnsprof_ceoprefix'])) {
            $parts[] = ucfirst($row['bnsprof_ceoprefix']);
        }
        if (!empty($row['bnsprof_ceofname'])) {
            $parts[] = ucfirst($row['bnsprof_ceofname']);
        }
        if (!empty($row['bnsprof_ceolname'])) {
            $parts[] = ucfirst($row['bnsprof_ceolname']);
        }
        return implode(' ', $parts);
    }
    
    /**
     * Get email verification status
     * 
     * @param string $status Verification status
     * @return string HTML status
     */
    public function getEmailVerificationStatus(string $status): string {
        if ($status == '1') {
            return '<span class="label label-success">Email verified</span>';
        }
        return '<span class="label label-danger">Email not verified</span>';
    }
}

// Get token
$token = $_GET['token'] ?? '';
if (empty($token)) {
    header("Location: user-list.php");
    exit;
}

// Initialize details object
$details = new UserDetails($con, $token);
$row = $details->getDetails();

if (!$row) {
    header("Location: user-list.php");
    exit;
}

// Handle back button
if (isset($_POST['btnBack'])) {
    header("Location: user-list.php");
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
                        <a href="user-list.php">Manage Users</a>
                    </li>
                    <li class="active">User Details</li>
                </ul>
            </div>
                        
            <div class="page-content">
                <div class="page-header">
                    <h1>
                        User Details
                        <small>
                            <i class="icon-double-angle-right"></i>
                            <strong><?php echo htmlspecialchars($details->getFullName($row), ENT_QUOTES, 'UTF-8'); ?></strong>
                        </small>
                    </h1>
                </div>
                
                <div class="row">
                    <div class="col-xs-12">
                        
                        <!-- Account Info Section -->
                        <div class="widget-box">
                            <div class="widget-header">
                                <h4 class="widget-title">Account Information</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Registration Date:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo !empty($row['bnsprof_creation_date']) ? date('F d, Y', strtotime($row['bnsprof_creation_date'])) : 'N/A'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Last Update:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo !empty($row['date']) ? date('F d, Y', strtotime($row['date'])) : 'N/A'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (getEmailVerificationStatus() == 1): ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Email Verification:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo $details->getEmailVerificationStatus($row['usr_emailVerify'] ?? '0'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Account Credit:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <strong><?php echo (int)($row['usr_credit'] ?? 0); ?></strong> credits
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Business Page:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                $businessToken = rand(1000, 9999) . md5((string)($row['bnsprof_id'] ?? ''));
                                                $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                                                ?>
                                                <a href="../company/index.php?c=<?php echo $businessToken; ?>" target="_blank">
                                                    <?php echo $host; ?>/company/index.php?c=<?php echo $businessToken; ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Info Section -->
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Personal Information</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Email:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <a href="mailto:<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Mobile:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                $mobile = trim(($row['country_ph_code'] ?? '') . ' ' . ($row['mobile1'] ?? ''));
                                                echo htmlspecialchars($mobile ?: 'N/A', ENT_QUOTES, 'UTF-8');
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Country:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($details->getCountryName((int)($row['country'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Website:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php if (!empty($row['website'])): ?>
                                                    <a href="<?php echo htmlspecialchars($row['website'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                                        <?php echo htmlspecialchars($row['website'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($row['usr_mp_id']) && $row['usr_mp_id'] != '0'): ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Membership Plan:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <span class="label label-info">
                                                    <?php echo htmlspecialchars($details->getMembershipPlan((int)$row['usr_mp_id']), ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Business Profile Section -->
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Business Profile</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Company Name:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <strong><?php echo htmlspecialchars($row['bnsprof_compname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Business Ownership Type:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($details->getOwnershipType((int)($row['bnsprof_owntype'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">CEO:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($details->getCeoFullName($row), ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Address:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                $address = [];
                                                if (!empty($row['bnsprof_address1'])) $address[] = $row['bnsprof_address1'];
                                                if (!empty($row['bnsprof_address2'])) $address[] = $row['bnsprof_address2'];
                                                if (!empty($row['bnsprof_city'])) $address[] = $details->getCityName((int)$row['bnsprof_city']);
                                                if (!empty($row['bnsprof_state'])) $address[] = $details->getStateName((int)$row['bnsprof_state']);
                                                if (!empty($row['bnsprof_zipcode'])) $address[] = $row['bnsprof_zipcode'];
                                                
                                                echo nl2br(htmlspecialchars(implode("\n", $address), ENT_QUOTES, 'UTF-8'));
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Phone Numbers:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                $phones = [];
                                                if (!empty($row['bnsprof_ph1'])) $phones[] = trim(($row['bnsprof_phcode1'] ?? '') . ' ' . $row['bnsprof_ph1']);
                                                if (!empty($row['bnsprof_ph2'])) $phones[] = trim(($row['bnsprof_phcode2'] ?? '') . ' ' . $row['bnsprof_ph2']);
                                                if (!empty($row['bnsprof_ph3'])) $phones[] = trim(($row['bnsprof_phcode3'] ?? '') . ' ' . $row['bnsprof_ph3']);
                                                if (!empty($row['bnsprof_ph4'])) $phones[] = trim(($row['bnsprof_phcode4'] ?? '') . ' ' . $row['bnsprof_ph4']);
                                                
                                                echo htmlspecialchars(implode("\n", $phones), ENT_QUOTES, 'UTF-8') ?: 'N/A';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Fax Numbers:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                $faxes = [];
                                                if (!empty($row['bnsprof_fax1'])) $faxes[] = trim(($row['bnsprof_faxcode1'] ?? '') . ' ' . $row['bnsprof_fax1']);
                                                if (!empty($row['bnsprof_fax2'])) $faxes[] = trim(($row['bnsprof_faxcode2'] ?? '') . ' ' . $row['bnsprof_fax2']);
                                                
                                                echo htmlspecialchars(implode("\n", $faxes), ENT_QUOTES, 'UTF-8') ?: 'N/A';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Email (Alternative):</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php 
                                                $emails = [];
                                                if (!empty($row['bnsprof_emailalt1'])) $emails[] = $row['bnsprof_emailalt1'];
                                                if (!empty($row['bnsprof_emailalt2'])) $emails[] = $row['bnsprof_emailalt2'];
                                                if (!empty($row['bnsprof_emailalt3'])) $emails[] = $row['bnsprof_emailalt3'];
                                                
                                                echo htmlspecialchars(implode("\n", $emails), ENT_QUOTES, 'UTF-8') ?: 'N/A';
                                                ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Website (Alternative):</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php if (!empty($row['bnsprof_website_alt'])): ?>
                                                    <a href="<?php echo htmlspecialchars($row['bnsprof_website_alt'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                                                        <?php echo htmlspecialchars($row['bnsprof_website_alt'], ENT_QUOTES, 'UTF-8'); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Business Type:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($details->getBusinessTypes($row['bnsprof_businesstype'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Year of Establishment:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($row['bnsprof_yoe'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">No of Employees:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo (int)($row['bnsprof_comemp'] ?? 0) > 0 ? (int)$row['bnsprof_comemp'] : 'N/A'; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Revenue Sales Turnover:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <?php echo htmlspecialchars($details->getRevenueTurnover((int)($row['bnsprof_turnover'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Registration Numbers Section -->
                        <div class="widget-box" style="margin-top:20px;">
                            <div class="widget-header">
                                <h4 class="widget-title">Registration & License Numbers</h4>
                            </div>
                            <div class="widget-body">
                                <div class="widget-main">
                                    
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <table class="table table-bordered table-striped">
                                                <tr>
                                                    <th width="40%">Registration No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_regno'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Registration Authority</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_regauthority'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>CIN No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_cin_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>TAN No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_tan_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>PAN No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_pan_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Service Tax No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_svtax_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Excise Reg. No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_excisereg_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>TIN No. / VAT No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_vat_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-sm-6">
                                            <table class="table table-bordered table-striped">
                                                <tr>
                                                    <th width="40%">TDGFT/IE Code</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_ie_code'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>CST No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_cst_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>SSI No. / MSME No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_msme_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>EPF No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_epf_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>ESI No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_esi_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>SCT No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_sct_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>DNB No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_dnb_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>RBI No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_rbi_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>FSSAI-LICENSE No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_fssailic_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>N.S.I.C No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_nsic_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th>S.S.T No.</th>
                                                    <td><?php echo htmlspecialchars($row['bnsprof_sst_no'] ?? 'N/A', ENT_QUOTES, 'UTF-8'); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($row['bnsprof_doc'])): ?>
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Company Documents:</label>
                                        <div class="col-sm-9">
                                            <p class="form-control-static">
                                                <a href="../server/php/files/<?php echo urlencode($row['bnsprof_doc']); ?>" target="_blank">
                                                    <i class="icon-file-text"></i> <?php echo htmlspecialchars($row['bnsprof_doc'], ENT_QUOTES, 'UTF-8'); ?>
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="clearfix form-actions">
                            <div class="col-md-offset-3 col-md-9">
                                <button class="btn btn-info" type="submit" name="btnBack">
                                    <i class="icon-reply bigger-110"></i> Back to List
                                </button>
                                <a href="user-edit.php?uid=<?php echo (int)$row['usr_id']; ?>" class="btn btn-primary">
                                    <i class="icon-edit bigger-110"></i> Edit User
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
    .form-control-static {
        min-height: 34px;
        padding-top: 7px;
    }
    .btn {
        margin-right: 5px;
    }
    .table th {
        background-color: #f5f5f5;
    }
    .label {
        font-size: 12px;
        padding: 3px 6px;
    }
</style>

</body>
</html>

<?php ob_end_flush(); ?>