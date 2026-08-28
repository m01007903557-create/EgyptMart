<?php
/**
 * File: function.php
 * Version: 1.0.0
 * PHP Version: 8.3
 * 
 * Description: الدوال الأساسية للوحة تحكم المشرف
 * Core functions for admin panel
 * 
 * Features:
 * - التحقق من تسجيل دخول المشرف
 * - إعدادات الموقع
 * - دوال مساعدة للمستخدم والشركة
 * - معالجة الوقت والتاريخ
 */

declare(strict_types=1);

// Prevent direct access
//if (!defined('IN_EGYPTMART') && !defined('IN_ADMIN_PANEL')) {
    //exit('Direct access not allowed');
//}

// Initialize site settings if not already done
if (!isset($site_settings)) {
    $site_settings = initSiteSettings();
}

/**
 * Check if admin user is logged in
 * Redirects to login page if not authenticated
 * 
 * @throws RuntimeException If session not started
 */
function checkUserLogin(): void {
    global $con;
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['ad_username_indm'])) {
        header('Location: index.php');
        exit;
    }
    
    $username = mysqli_real_escape_string($con, $_SESSION['ad_username_indm']);
    
    $sql = "SELECT id FROM admin_user WHERE username = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        error_log("Failed to prepare user check query: " . mysqli_error($con));
        header('Location: index.php');
        exit;
    }
    
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    $count = mysqli_stmt_num_rows($stmt);
    mysqli_stmt_close($stmt);
    
    if ($count === 0) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Get admin user ID from session
 * 
 * @return int Admin user ID
 * @throws RuntimeException If user not found
 */
function getAdminUserId(): int {
    global $con;
    
    if (!isset($_SESSION['ad_username_indm'])) {
        throw new RuntimeException('Admin not logged in');
    }
    
    $username = mysqli_real_escape_string($con, $_SESSION['ad_username_indm']);
    
    $sql = "SELECT id FROM admin_user WHERE username = ?";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        throw new RuntimeException('Failed to prepare user query');
    }
    
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $id = (int)$row['id'];
        mysqli_stmt_close($stmt);
        return $id;
    }
    
    mysqli_stmt_close($stmt);
    throw new RuntimeException('Admin user not found');
}

/**
 * Initialize site settings from database
 * Loads all active settings into array for caching
 * 
 * @return array<string, string> Site settings array
 */
function initSiteSettings(): array {
    global $con;
    
    $settings = [];
    
    $sql = "SELECT st_id, st_field, st_value FROM site_settings WHERE st_status = 1";
    $result = mysqli_query($con, $sql);
    
    if (!$result) {
        error_log("Failed to load site settings: " . mysqli_error($con));
        return $settings;
    }
    
    while ($row = mysqli_fetch_assoc($result)) {
        $key = ($row['st_field'] === 'website-title') ? $row['st_field'] : (string)$row['st_id'];
        $settings[$key] = $row['st_value'];
    }
    
    return $settings;
}

/**
 * Get site title
 * 
 * @return string Site title
 */
function getSiteTitle(): string {
    global $site_settings;
    
    if (isset($site_settings['website-title']) && !empty($site_settings['website-title'])) {
        return ucfirst($site_settings['website-title']);
    }
    
    // Fallback to direct database query
    global $con;
    $sql = "SELECT st_value FROM site_settings WHERE st_field = 'website-title' AND st_status = 1 LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return ucfirst($row['st_value']);
    }
    
    return 'No Title';
}

/**
 * Get website name
 * 
 * @return string Website name
 */
function getWebSiteName(): string {
    global $site_settings;
    
    if (isset($site_settings[4]) && !empty($site_settings[4])) {
        return ucfirst($site_settings[4]);
    }
    
    // Fallback to direct database query
    global $con;
    $sql = "SELECT st_value FROM site_settings WHERE st_id = 4 AND st_status = 1 LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return ucfirst($row['st_value']);
    }
    
    return 'No Name';
}

/**
 * Get website title (alias for getSiteTitle)
 * 
 * @return string Website title
 */
function getWebSiteTitle(): string {
    return getSiteTitle();
}

/**
 * Get page settings by ID
 * 
 * @param int|string $id Setting ID or field name
 * @return string Setting value
 */
function getPageSettings(int|string $id): string {
    global $site_settings;
    
    $key = (string)$id;
    
    if (isset($site_settings[$key])) {
        return $site_settings[$key];
    }
    
    // Fallback to direct database query
    global $con;
    $sql = "SELECT st_value FROM site_settings WHERE st_id = ? AND st_status = 1 LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['st_value'];
        }
        mysqli_stmt_close($stmt);
    }
    
    return '';
}

/**
 * Alias for getPageSettings
 */
function get_page_settings(int|string $x): string {
    return getPageSettings($x);
}

/**
 * Get site settings by ID (alias)
 */
function get_site_settings(int|string $x): string {
    return getPageSettings($x);
}

/**
 * Get time difference in minutes
 * 
 * @param string $time1 First time (HH:MM format)
 * @param string $time2 Second time (HH:MM format)
 * @return int Difference in minutes
 */
function getTimeDifference(string $time1, string $time2): int {
    $time1 = strtotime("1980-01-01 $time1");
    $time2 = strtotime("1980-01-01 $time2");
    
    if ($time2 < $time1) {
        $time2 += 86400; // Add one day
    }
    
    return (int)(($time2 - $time1) / 60);
}

/**
 * Alias for backward compatibility
 */
function get_time_difference(string $time1, string $time2): int {
    return getTimeDifference($time1, $time2);
}

/**
 * Get number of days in a month
 * 
 * @param int $month Month number (1-12)
 * @param int $year Year (4 digits)
 * @return int Number of days
 */
function getDaysInMonth(int $month, int $year): int {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

/**
 * Alias for backward compatibility
 */
function get_days_in_month(int $month, int $year): int {
    return getDaysInMonth($month, $year);
}

/**
 * Get site logo filename
 * 
 * @return string Logo filename
 */
function getSiteLogo(): string {
    global $site_settings;
    
    if (isset($site_settings[5]) && !empty($site_settings[5])) {
        return $site_settings[5];
    }
    
    global $con;
    $sql = "SELECT st_value FROM site_settings WHERE st_id = 5 AND st_status = 1 LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['st_value'];
    }
    
    return '';
}

/**
 * Get email verification status
 * 
 * @return string Status (on/off)
 */
function getEmailVerificationStatus(): string {
    global $con;
    
    $sql = "SELECT st_value FROM site_settings WHERE st_field = 'email-verification' LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['st_value'];
    }
    
    return 'off';
}

/**
 * Get user information
 * 
 * @param int $id User ID
 * @param string $field Field name
 * @return string Field value
 */
function getUserInfo(int $id, string $field): string {
    global $con;
    
    // Validate field name to prevent SQL injection
    $allowedFields = ['usr_id', 'fname', 'lname', 'email', 'mobile1', 'country', 'usr_mp_id'];
    if (!in_array($field, $allowedFields, true)) {
        return '';
    }
    
    $sql = "SELECT $field FROM user WHERE usr_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        return '';
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return (string)$row[$field];
    }
    
    mysqli_stmt_close($stmt);
    return '';
}

/**
 * Get company name by user ID
 * 
 * @param int $uid User ID
 * @return string Company name
 */
function getCompanyName(int $uid): string {
    global $con;
    
    $sql = "SELECT bnsprof_compname FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        return '';
    }
    
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        mysqli_stmt_close($stmt);
        return (string)$row['bnsprof_compname'];
    }
    
    mysqli_stmt_close($stmt);
    return '';
}

/**
 * Get real IP address of visitor
 * 
 * @return string IP address
 */
function getRealIpAddr(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    
    // Check for proxy headers
    $proxyHeaders = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED'
    ];
    
    foreach ($proxyHeaders as $header) {
        if (!empty($_SERVER[$header])) {
            $ip = $_SERVER[$header];
            break;
        }
    }
    
    // Validate IP address
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return '0.0.0.0';
    }
    
    return $ip;
}

/**
 * Get country code based on IP
 * 
 * @return string Country code (2 letters)
 */
function getCountryCode(): string {
    $ip = getRealIpAddr();
    
    // Skip for local IPs
    if ($ip === '127.0.0.1' || $ip === '::1' || strpos($ip, '192.168.') === 0) {
        return 'LOCAL';
    }
    
    // Try to get from geoplugin
    try {
        $xml = simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);
        if ($xml !== false && isset($xml->geoplugin_countryCode)) {
            return (string)$xml->geoplugin_countryCode;
        }
    } catch (Exception $e) {
        error_log("Failed to get country code: " . $e->getMessage());
    }
    
    return 'XX';
}

/**
 * Get currency by country ID
 * 
 * @param int $curr Country ID
 * @return string Currency code
 */
function getCurrency(int $curr): string {
    global $con;
    
    $sql = "SELECT cn_currency FROM country WHERE cn_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        return '';
    }
    
    mysqli_stmt_bind_param($stmt, "i", $curr);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_object($result)) {
        mysqli_stmt_close($stmt);
        return $row->cn_currency ?? '';
    }
    
    mysqli_stmt_close($stmt);
    return '';
}

/**
 * Get measurement unit name by ID
 * 
 * @param int $id Unit ID
 * @return string Unit name
 */
function measurementUnit(int $id): string {
    global $con;
    
    $sql = "SELECT mu_name FROM measurement_unit WHERE mu_id = ? LIMIT 1";
    $stmt = mysqli_prepare($con, $sql);
    
    if (!$stmt) {
        return '';
    }
    
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_object($result)) {
        mysqli_stmt_close($stmt);
        return $row->mu_name ?? '';
    }
    
    mysqli_stmt_close($stmt);
    return '';
}

/**
 * Alias for backward compatibility
 */
function measurement_unit(int $id): string {
    return measurementUnit($id);
}
?>