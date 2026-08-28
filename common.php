<?php
declare(strict_types=1);
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

// تعريف المتغيرات العالمية الشائعة
if (!isset($location_geo_country)) $location_geo_country = '';
$uid_indm = $_SESSION['uid_indm'] ?? 0;
$row = $row ?? null;
// ==============================================
// 1. منع التضمين المزدوج (يجب أن يكون أول شيء)
// ==============================================
if (defined('COMMON_LOADED')) {
    return;
}
define('COMMON_LOADED', true);

// ==============================================
// 2. إعدادات البيئة والأخطاء
// ==============================================
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/logs/php_errors.log');
}

// ==============================================
// 3. تضمين اتصال قاعدة البيانات أولاً
// ==============================================
require_once __DIR__ . '/lib/connect.php';

// التحقق من اتصال قاعدة البيانات
if (!isset($con) || !($con instanceof mysqli)) {
    error_log("خطأ: اتصال قاعدة البيانات غير مهيأ في " . __FILE__);
    if (ENVIRONMENT === 'development') {
        die('خطأ في اتصال قاعدة البيانات. الرجاء التحقق من ملف connect.php');
    }
}

// ==============================================
// 4. تعريف جميع الدوال (مرتبة أبجدياً للمنطق)
// ==============================================

// تعريف شرطي لجميع الدوال
if (!function_exists('categoryAdsBanner')) {
    function categoryAdsBanner($loc_id, $cat_id, $subcat_id, $position) {
        return '';
    }
}

if (!function_exists('check_admin_login')) {
    function check_admin_login() {
        // بدء الجلسة إذا لم تكن قد بدأت
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // التحقق من تسجيل الدخول
        $isLoggedIn = false;
        
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            $isLoggedIn = true;
        } elseif (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
            $_SESSION['admin_logged_in'] = true;
            $isLoggedIn = true;
        }
        
        // إذا لم يكن مسجلاً الدخول، قم بإعادة التوجيه
        if (!$isLoggedIn) {
            $_SESSION['msg'] = '<font color="#CC0000">الرجاء تسجيل الدخول أولاً</font>';
            header("Location: login.php");
            exit();
        }
        
        return true;
    }
}

if (!function_exists('checkDirectAccess')) {
    function checkDirectAccess($file) {
        if (basename($_SERVER['SCRIPT_FILENAME']) == $file) {
            die("Direct Access Not Allowed");
        }
    }
}

if (!function_exists('escapeHtml')) {
    function escapeHtml(?string $data): string {
        return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('executeNonQuery')) {
    function executeNonQuery(string $sql, string $types = '', array $params = []): int|false {
        global $con;
        try {
            if (!$con || !($con instanceof mysqli)) {
                throw new Exception('اتصال قاعدة البيانات غير متوفر');
            }
            $stmt = mysqli_prepare($con, $sql);
            if (!$stmt) {
                throw new Exception('فشل تحضير الاستعلام: ' . mysqli_error($con));
            }
            if (!empty($types) && !empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('فشل تنفيذ الاستعلام: ' . mysqli_stmt_error($stmt));
            }
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            mysqli_stmt_close($stmt);
            return $affected_rows;
        } catch (Exception $e) {
            error_log("خطأ في executeNonQuery: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('executePreparedQuery')) {
    function executePreparedQuery(string $sql, string $types = '', array $params = []): mysqli_result|false {
        global $con;
        try {
            if (!$con || !($con instanceof mysqli)) {
                throw new Exception('اتصال قاعدة البيانات غير متوفر');
            }
            if (!empty($types) && strlen($types) !== count($params)) {
                throw new Exception('عدم تطابق عدد المعاملات مع عدد الأنواع');
            }
            $stmt = mysqli_prepare($con, $sql);
            if (!$stmt) {
                throw new Exception('فشل تحضير الاستعلام: ' . mysqli_error($con));
            }
            if (!empty($types) && !empty($params)) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception('فشل تنفيذ الاستعلام: ' . mysqli_stmt_error($stmt));
            }
            $result = mysqli_stmt_get_result($stmt);
            mysqli_stmt_close($stmt);
            return $result;
        } catch (Exception $e) {
            error_log("خطأ في executePreparedQuery: " . $e->getMessage() . " - SQL: " . $sql);
            return false;
        }
    }
}

if (!function_exists('fetchAll')) {
    function fetchAll(string $sql, string $types = '', array $params = []): array {
        $result = executePreparedQuery($sql, $types, $params);
        $rows = [];
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            mysqli_free_result($result);
        }
        return $rows;
    }
}

if (!function_exists('fetchOne')) {
    function fetchOne(string $sql, string $types = '', array $params = []): ?array {
        $result = executePreparedQuery($sql, $types, $params);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            return $row;
        }
        return null;
    }
}

if (!function_exists('generateCSRFToken')) {
    function generateCSRFToken(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('get_adminemail')) {
    function get_adminemail(): string {
        global $con;
        $result = mysqli_query($con, "SELECT st_value FROM site_settings WHERE st_field = 'admin_email' LIMIT 1");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return $row['st_value'] ?? '';
        }
        return 'admin@egyptmart.shop';
    }
}

if (!function_exists('get_country_flag')) {
    function get_country_flag(int $cn_id): string {
        $row = fetchOne("SELECT cn_code FROM country WHERE cn_id = ?", 'i', [$cn_id]);
        return ($row['cn_code'] ?? 'Global') . '.png';
    }
}

if (!function_exists('get_country_name')) {
    function get_country_name($cn_id): string {
        $cn_id = (int)$cn_id;
        if ($cn_id <= 0) return '';
        
        $row = fetchOne("SELECT cn_name FROM country WHERE cn_id = ?", 'i', [$cn_id]);
        return $row['cn_name'] ?? '';
    }
}

if (!function_exists('get_currency_symbol')) {
    function get_currency_symbol(int $cn_id): string {
        $row = fetchOne("SELECT cn_currency FROM country WHERE cn_id = ?", 'i', [$cn_id]);
        return $row['cn_currency'] ?? '$';
    }
}

if (!function_exists('get_measurement_unit')) {
    function get_measurement_unit(int $mu_id): string {
        $row = fetchOne("SELECT mu_name FROM measurement_unit WHERE mu_id = ?", 'i', [$mu_id]);
        return $row['mu_name'] ?? 'Unit';
    }
}

if (!function_exists('get_city_name')) {
    function get_city_name($ct_id): string {
        $ct_id = intval($ct_id);
        if ($ct_id <= 0) return '';
        global $con;
        $query = mysqli_query($con, "SELECT ct_name FROM city WHERE ct_id = '$ct_id'");
        if ($query && mysqli_num_rows($query) > 0) {
            $row = mysqli_fetch_assoc($query);
            return $row['ct_name'];
        }
        return '';
    }
}

if (!function_exists('getCompanyName')) {
    function getCompanyName(int $uid): string {
        global $con;
        $sql = "SELECT bnsprof_compname FROM business_profile WHERE bnsprof_uid = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) return '';
        mysqli_stmt_bind_param($stmt, "i", $uid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return $row['bnsprof_compname'] ?? '';
        }
        mysqli_stmt_close($stmt);
        return '';
    }
}

if (!function_exists('getCurrentUserId')) {
    function getCurrentUserId(): ?int {
        return isset($_SESSION['uid_indm']) && is_numeric($_SESSION['uid_indm']) 
               ? (int)$_SESSION['uid_indm'] 
               : null;
    }
}

if (!function_exists('getEmailVerificationStatus')) {
    function getEmailVerificationStatus(): int {
        return (int)get_page_settings(26);
    }
}

if (!function_exists('getLocationInfoByIp')) {
    function getLocationInfoByIp(): array {
        global $con;
        static $cached_result = null;
        
        // العودة من الكاش إذا كان موجوداً
        if ($cached_result !== null) {
            return $cached_result;
        }
        
        // الحصول على IP المستخدم
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? 
              $_SERVER['HTTP_X_FORWARDED_FOR'] ?? 
              $_SERVER['HTTP_CLIENT_IP'] ?? 
              $_SERVER['REMOTE_ADDR'] ?? '';
        
        // تنظيف IP (أخذ الأول فقط إذا كان هناك عدة IPs)
        if (strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }
        
        // التحقق من صحة IP
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $cached_result = []; // Global mode - لا يوجد بلد محدد
            return $cached_result;
        }
        
        // قائمة IPs المحلية (localhost) -> عرض Global
        $local_ips = ['127.0.0.1', '::1', 'localhost'];
        if (in_array($ip, $local_ips)) {
            $cached_result = []; // Global mode
            return $cached_result;
        }
        
        // محاولة جلب معلومات البلد من API
        try {
            $context = stream_context_create(['http' => ['timeout' => 3]]);
            $response = @file_get_contents("http://ip-api.com/json/" . $ip . "?fields=status,countryCode", false, $context);
            
            if ($response !== false) {
                $data = json_decode($response, true);
                if ($data && isset($data['status']) && $data['status'] === 'success') {
                    $countryCode = $data['countryCode'] ?? '';
                    
                    if (!empty($countryCode)) {
                        // البحث عن البلد في قاعدة البيانات
                        $stmt = mysqli_prepare($con, "SELECT cn_id FROM country WHERE cn_code = ? LIMIT 1");
                        if ($stmt) {
                            mysqli_stmt_bind_param($stmt, "s", $countryCode);
                            mysqli_stmt_execute($stmt);
                            $result = mysqli_stmt_get_result($stmt);
                            if ($row = mysqli_fetch_assoc($result)) {
                                mysqli_stmt_close($stmt);
                                $cached_result = [(int)$row['cn_id']];
                                return $cached_result;
                            }
                            mysqli_stmt_close($stmt);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("IP detection error: " . $e->getMessage());
        }
        
        // إذا فشل كل شيء، نعود إلى الـ Global mode (لا يوجد بلد محدد)
        $cached_result = []; // مصفوفة فارغة تعني Global mode
        return $cached_result;
    }
}

if (!function_exists('get_membership_expired')) {
    function get_membership_expired(): bool {
        global $con;
        $uid = isset($_SESSION['uid_indm']) ? (int)$_SESSION['uid_indm'] : 0;
        if ($uid <= 0) return true;
        
        $sql = "SELECT usr_mp_id FROM user WHERE usr_id = $uid";
        $result = mysqli_query($con, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $usr_mp_id = (int)$row['usr_mp_id'];
            
            // ✅ الأرقام الصحيحة من قاعدة البيانات
            $allowed_memberships = [3, 10, 11 , 0]; // JUNIOR, SENIOR, SPONSOR, PROMO
            
            if (in_array($usr_mp_id, $allowed_memberships)) {
                return false; // عضوية صالحة
            }
        }
        return true; // عضوية منتهية
    }
}

if (!function_exists('get_page_settings')) {
    function get_page_settings($setting_id): string {
        $setting_id = (int)$setting_id;
        $row = fetchOne("SELECT st_value FROM site_settings WHERE st_id = ?", 'i', [$setting_id]);
        return $row['st_value'] ?? '';
    }
}

if (!function_exists('get_product_detail')) {
    function get_product_detail(int $product_id, string $field): string {
        global $con;
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
        if (empty($field)) return '';
        
        $sql = "SELECT $field FROM products WHERE pd_id = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $sql);
        if (!$stmt) return '';
        
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return (string)($row[$field] ?? '');
        }
        mysqli_stmt_close($stmt);
        return '';
    }
}

if (!function_exists('getSiteTitle')) {
    function getSiteTitle(): string {
        return (defined('SITE_NAME') ? SITE_NAME : 'Egypt Mart') . ' v' . (defined('SITE_VERSION') ? SITE_VERSION : '3.2.0');
    }
}

if (!function_exists('get_state_name')) {
    function get_state_name(int $state_id): string {
        if ($state_id <= 0) return '';
        global $con;
        try {
            $table_check = mysqli_query($con, "SHOW TABLES LIKE 'states'");
            if (!$table_check || mysqli_num_rows($table_check) == 0) return '';
            
            $sql = "SELECT * FROM states WHERE state_id = ? LIMIT 1";
            $stmt = mysqli_prepare($con, $sql);
            if (!$stmt) return '';
            
            mysqli_stmt_bind_param($stmt, "i", $state_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_assoc($result)) {
                mysqli_stmt_close($stmt);
                return $row['state_name'] ?? ($row['st_name'] ?? ($row['name'] ?? ''));
            }
            mysqli_stmt_close($stmt);
        } catch (Exception $e) {
            error_log("get_state_name: " . $e->getMessage());
        }
        return '';
    }
}

if (!function_exists('getUserName')) {
    function getUserName(int $uid): string {
        global $con;
        $result = mysqli_query($con, "SELECT fname, lname FROM user WHERE usr_id = '$uid' LIMIT 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            return trim($row['fname'] . ' ' . $row['lname']);
        }
        return '';
    }
}


if (!function_exists('getUserInfo')) {
    function getUserInfo($uid, string $field = ''): string {  // قم بإزالة int $uid
        // تحويل إلى int بشكل آمن
        $uid = intval($uid);
        if ($uid <= 0) return '';
        
        if (empty($field)) {
            $row = fetchOne("SELECT * FROM user WHERE usr_id = ?", 'i', [$uid]);
            return json_encode($row);
        }
        
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
        if (empty($field)) return '';
        
        $row = fetchOne("SELECT * FROM user WHERE usr_id = ?", 'i', [$uid]);
        if (isset($row[$field])) {
            $value = $row[$field];
            if (is_null($value)) return '';
            if (is_bool($value)) return $value ? '1' : '0';
            return (string)$value;
        }
        return '';
    }
}

if (!function_exists('getWebSiteName')) {
    function getWebSiteName(): string {
        return defined('SITE_NAME') ? SITE_NAME : 'Egypt Mart';
    }
}

if (!function_exists('getActiveCountryList')) {
    function getActiveCountryList() {
        global $con;
        $sql = "SELECT DISTINCT u.country 
                FROM user u
                INNER JOIN business_profile bp ON u.usr_id = bp.bnsprof_uid
                INNER JOIN products p ON bp.bnsprof_uid = p.pd_uid
                INNER JOIN plan_member_id pm ON pm.b_id = bp.bnsprof_id
                WHERE u.status = '1' 
                AND p.pd_status = '1'
                AND p.pd_image != ''
                AND bp.bnsprof_status = '1'
                AND pm.expiry_date > " . time() . "
                AND u.country > 0
                ORDER BY u.country";
        
        $result = mysqli_query($con, $sql);
        $ids = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $ids[] = (int)$row['country'];
        }
        return !empty($ids) ? implode(',', $ids) : '63';
    }
}

if (!function_exists('GetHomeBanner')) {
    function GetHomeBanner(string $position, string $country = ''): string {
        $rows = fetchAll(
            "SELECT banner_code FROM home_banners 
             WHERE banner_position = ? AND banner_status = '1' 
             ORDER BY banner_order LIMIT 5",
            's',
            [$position]
        );
        $output = '';
        foreach ($rows as $row) {
            $output .= $row['banner_code'] ?? '';
        }
        return $output;
    }
}

if (!function_exists('GettingSite_Setting')) {
    function GettingSite_Setting(string $key): string {
        $row = fetchOne(
            "SELECT st_value FROM site_settings WHERE st_field = ? AND (st_status = 1 OR st_status IS NULL) LIMIT 1",
            's',
            [$key]
        );
        return $row['st_value'] ?? '';
    }
}

if (!function_exists('logUserActivity')) {
    function logUserActivity(string $action, string $details = ''): void {
        $userId = getCurrentUserId();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        
        $logEntry = sprintf(
            "[%s] المستخدم: %s | الإجراء: %s | التفاصيل: %s | IP: %s | المتصفح: %s\n",
            date('Y-m-d H:i:s'),
            $userId ?? 'زائر',
            $action,
            $details,
            $ip,
            $userAgent
        );
        error_log($logEntry, 3, __DIR__ . '/logs/user_activity.log');
    }
}

if (!function_exists('measurement_unit')) {
    function measurement_unit($unit_id): string {
        global $con;
        $unit_id = (int)$unit_id;
        if ($unit_id <= 0) return '';
        
        $sql = "SELECT mu_name FROM measurement_unit WHERE mu_id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $unit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($result)) {
            mysqli_stmt_close($stmt);
            return htmlspecialchars($row['mu_name'], ENT_QUOTES, 'UTF-8');
        }
        mysqli_stmt_close($stmt);
        return '';
    }
}

if (!function_exists('secureSessionStart')) {
    function secureSessionStart() {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.gc_maxlifetime', 7200);
            session_start();
            if (!isset($_SESSION['created'])) {
                $_SESSION['created'] = time();
            } else if (time() - $_SESSION['created'] > 1800) {
                session_regenerate_id(true);
                $_SESSION['created'] = time();
            }
        }
    }
}

if (!function_exists('Show_shortcontent')) {
    function Show_shortcontent(string $content, int $length = 100): string {
        if (strlen($content) <= $length) return $content;
        return substr($content, 0, $length) . '...';
    }
}

if (!function_exists('user_info')) {
    function user_info(int $uid, string $field): string {
        return getUserInfo($uid, $field);
    }
}

if (!function_exists('verifyCSRFToken')) {
    function verifyCSRFToken(string $token): bool {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// ==============================================
// 5. الإعدادات الأساسية والثوابت
// ==============================================

// بدء الجلسة (بعد تعريف الدالة)
secureSessionStart();

// ضبط المنطقة الزمنية
date_default_timezone_set('Africa/Cairo');

// تعريفات الموقع الأساسية
if (!defined('BASE_URL')) {
    define('BASE_URL', (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
}

if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Egypt Mart');
}

if (!defined('SITE_VERSION')) {
    define('SITE_VERSION', '3.2.0');
}

// إنشاء مجلد logs إذا لم يكن موجوداً
if (!is_dir(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

// ==============================================
// 6. التحقق النهائي من الدوال الأساسية
// ==============================================
$required_functions = [
    'get_country_flag', 'get_city_name', 'get_state_name',
    'getWebSiteName', 'GettingSite_Setting', 'Show_shortcontent'
];

foreach ($required_functions as $func) {
    if (!function_exists($func)) {
        error_log("تحذير: الدالة $func غير موجودة في common.php");
    }
}

// ============================================
// دوال المستخدم الأدمن
// ============================================
if (!function_exists('getAdminUserId')) {
    function getAdminUserId(): ?int {
        // محاولة الحصول من الجلسة
        if (isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id'])) {
            return (int)$_SESSION['admin_id'];
        }
        
        if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])) {
            global $con;
            $uid = (int)$_SESSION['uid_indm'];
            $result = mysqli_query($con, "SELECT usr_id FROM user WHERE usr_id = $uid AND user_type = 'admin' LIMIT 1");
            if ($result && mysqli_num_rows($result) > 0) {
                return $uid;
            }
        }
        
        // جلب أول أدمن من قاعدة البيانات
        global $con;
        $result = mysqli_query($con, "SELECT usr_id FROM user WHERE user_type = 'admin' AND status = 1 LIMIT 1");
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            return (int)$row['usr_id'];
        }
        
        return null;
    }
}


// ==============================================
// النظام الموحد لإرسال الاستفسارات (بريد + واتساب)
// ==============================================

/**
 * إنشاء رابط واتساب مباشر (Click to Chat)
 */
function getWhatsAppLink($phone_number, $buyer_data, $product_data, $quantity_from, $quantity_to, $message) {
    // تنسيق رقم الجوال
    $clean_phone = ltrim($phone_number, '0');
    
    // بناء نص الرسالة
    $whatsapp_message = "🛒 *استفسار شراء جديد - مصر مارت* 🛒\n\n";
    $whatsapp_message .= "👤 *اسم المشتري:* " . $buyer_data['name'] . "\n";
    $whatsapp_message .= "🏢 *الشركة:* " . $buyer_data['company'] . "\n";
    $whatsapp_message .= "📞 *الجوال:* " . $buyer_data['phone'] . "\n";
    $whatsapp_message .= "✉️ *البريد:* " . $buyer_data['email'] . "\n\n";
    $whatsapp_message .= "📦 *المنتج:* " . $product_data['name'] . "\n";
    $whatsapp_message .= "📊 *الكمية المطلوبة:* من " . $quantity_from . " إلى " . $quantity_to . " " . $product_data['unit'] . "\n\n";
    $whatsapp_message .= "💬 *تفاصيل الاستفسار:*\n" . substr($message, 0, 200);
    if (strlen($message) > 200) $whatsapp_message .= "...";
    $whatsapp_message .= "\n\n";
    $whatsapp_message .= "🔗 *للرد عبر المنصة:* https://egyptmart.shop/admin/message-view.php?id=" . $buyer_data['enquiry_id'];
    
    // ترميز النص
    $encoded_message = urlencode($whatsapp_message);
    
    return "https://wa.me/{$clean_phone}?text={$encoded_message}";
}

/**
 * إرسال استفسار متكامل مع حفظ البيانات
 */
function sendCompleteEnquiry($supplier_id, $buyer_data, $product_data, $quantity_from, $quantity_to, $message) {
    global $con;
    
    $results = [
        'email_sent' => false,
        'whatsapp_link' => null,
        'enquiry_id' => null
    ];
    
    // حفظ الاستفسار في قاعدة البيانات مع الحقول الجديدة
    $sql = "INSERT INTO buy_enquiries (
        buyer_id, supplier_id, product_name, product_unit, 
        quantity_from, quantity_to, message, enquiry_date, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'pending')";
    
    $stmt = mysqli_prepare($con, $sql);
    mysqli_stmt_bind_param($stmt, 'iissiis', 
        $buyer_data['id'], 
        $supplier_id, 
        $product_data['name'], 
        $product_data['unit'],
        $quantity_from, 
        $quantity_to, 
        $message
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $results['enquiry_id'] = mysqli_insert_id($con);
        
        // جلب بريد المورد
        $supplier_email = user_info($supplier_id, 'email');
        $supplier_phone = user_info($supplier_id, 'mobile1');
        
        // إرسال بريد إلكتروني للمورد
        if (!empty($supplier_email)) {
            $results['email_sent'] = sendEnquiryEmail($supplier_email, $buyer_data, $product_data, $quantity_from, $quantity_to, $message, $results['enquiry_id']);
        }
        
        // إنشاء رابط واتساب للمورد
        if (!empty($supplier_phone)) {
            $results['whatsapp_link'] = getWhatsAppLink($supplier_phone, $buyer_data, $product_data, $quantity_from, $quantity_to, $message);
        }
    }
    
    mysqli_stmt_close($stmt);
    return $results;
}

/**
 * إرسال بريد إلكتروني للاستفسار
 */
function sendEnquiryEmail($to_email, $buyer_data, $product_data, $quantity_from, $quantity_to, $message, $enquiry_id) {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'info@egyptmart.shop';
        $mail->Password   = 'your_password_here';
        $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        
        $mail->setFrom('info@egyptmart.shop', 'EgyptMART');
        $mail->addAddress($to_email);
        $mail->isHTML(true);
        $mail->Subject = 'استفسار شراء جديد - ' . $product_data['name'];
        
        $body = "
        <div dir='rtl' style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; border: 1px solid #ddd; padding: 20px;'>
            <h2 style='color: #466da0;'>📦 استفسار شراء جديد</h2>
            <table style='width: 100%; border-collapse: collapse;' cellpadding='10'>
                <tr><td style='background: #f5f5f5;'><strong>اسم المشتري:</strong></td><td>{$buyer_data['name']}</td></tr>
                <tr><td style='background: #f5f5f5;'><strong>الشركة:</strong></td><td>{$buyer_data['company']}</td></tr>
                <tr><td style='background: #f5f5f5;'><strong>الجوال:</strong></td><td>{$buyer_data['phone']}</td></tr>
                <tr><td style='background: #f5f5f5;'><strong>البريد الإلكتروني:</strong></td><td>{$buyer_data['email']}</td></tr>
                <tr><td style='background: #f5f5f5;'><strong>المنتج:</strong></td><td>{$product_data['name']}</td></tr>
                <tr><td style='background: #f5f5f5;'><strong>الكمية المطلوبة:</strong></td><td>من {$quantity_from} إلى {$quantity_to} {$product_data['unit']}</td></tr>
                <tr><td style='background: #f5f5f5;'><strong>تفاصيل الاستفسار:</strong></td><td>" . nl2br(htmlspecialchars($message)) . "</td></tr>
            </table>
            <hr>
            <p style='text-align: center;'>
                <a href='https://egyptmart.shop/admin/message-view.php?id={$enquiry_id}' style='background: #466da0; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>📋 عرض الاستفسار والرد</a>
            </p>
        </div>";
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Email failed: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * الحصول على رصيد المستخدم (الكريديت)
 * @param int $user_id معرف المستخدم
 * @return int رصيد المستخدم
 */
function getUserCredit($user_id) {
    global $con;
    
    $user_id = (int)$user_id;
    if ($user_id <= 0) {
        return 0;
    }
    
    // ✅ جلب الرصيد من جدول user
    $sql = "SELECT usr_credit FROM user WHERE usr_id = $user_id LIMIT 1";
    $result = mysqli_query($con, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return (int)($row['usr_credit'] ?? 0);
    }
    
    return 0;
}

/**
 * إرسال بريد إلكتروني عبر SMTP (معدل لـ PHP 8.3)
 * @param string $email البريد الإلكتروني للمستلم
 * @param string $subject عنوان الرسالة
 * @param string $message محتوى الرسالة (HTML)
 * @param string $headers (اختياري) رؤوس إضافية
 * @return bool
 */
/**
 * إرسال بريد إلكتروني عبر SMTP (مع التحقق من وجود PHPMailer)
 */
function sendSMTPMail($email, $subject, $message, $headers = '')
{
    // 1. التحقق من وجود PHPMailer
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        // محاولة تضمين PHPMailer من المسار الصحيح
        $phpmailer_paths = [
            __DIR__ . '/../lib/PHPMailer/PHPMailer.php',
            __DIR__ . '/../PHPMailer/PHPMailer.php',
            __DIR__ . '/PHPMailer/PHPMailer.php',
            __DIR__ . '/../vendor/autoload.php' // Composer
        ];
        $loaded = false;
        foreach ($phpmailer_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                $loaded = true;
                break;
            }
        }
        if (!$loaded) {
            // إذا لم يتم العثور على PHPMailer، استخدم mail() كبديل
            error_log('PHPMailer not found. Using mail() as fallback.');
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: EgyptMART <info@egyptmart.shop>" . "\r\n";
            return mail($email, $subject, $message, $headers);
        }
    }

    try {
        // 2. إنشاء كائن PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // 3. إعدادات الخادم
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        
        // 4. إعدادات SMTP
        $mail->Host = 'smtp.hostinger.com';
        $mail->Port = 465;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'ssl';
        $mail->Username = 'info@egyptmart.shop';
        $mail->Password = 'ANAehab@64';
        
        // 5. المرسل والمستلم
        $mail->setFrom('info@egyptmart.shop', 'EgyptMART');
        $mail->addAddress($email);
        $mail->addReplyTo('info@egyptmart.shop', 'EgyptMART');
        
        // 6. المحتوى
        $mail->Subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $mail->msgHTML($message);
        
        // 7. رؤوس إضافية
        if (!empty($headers)) {
            $mail->addCustomHeader($headers);
        }
        
        // 8. إرسال
        return $mail->send();
        
    } catch (PHPMailer\PHPMailer\Exception $e) {
        error_log('PHPMailer Error: ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        error_log('General Error: ' . $e->getMessage());
        return false;
    }
}

if (!function_exists('tableExists')) {
    function tableExists(string $table): bool {
        global $con;
        $safe_table = mysqli_real_escape_string($con, $table);
        $result = mysqli_query($con, "SHOW TABLES LIKE '" . $safe_table . "'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('tableHasColumn')) {
    function tableHasColumn(string $table, string $column): bool {
        global $con;
        $safe_table = mysqli_real_escape_string($con, $table);
        $safe_column = mysqli_real_escape_string($con, $column);
        $result = mysqli_query($con, "SHOW COLUMNS FROM `" . $safe_table . "` LIKE '" . $safe_column . "'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('getCombinedNotificationCount')) {
    function getCombinedNotificationCount(int $uid): int {
        global $con;
        if ($uid <= 0 || !$con) {
            return 0;
        }

        $total = 0;

        $stmt = mysqli_prepare($con, "SELECT COUNT(*) AS count FROM message WHERE msg_to = ? AND msg_to_status = '1'");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'i', $uid);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = $result ? mysqli_fetch_assoc($result) : null;
            $total += (int)($row['count'] ?? 0);
            mysqli_stmt_close($stmt);
        }

        if (tableExists('buy_enquiries') && tableHasColumn('buy_enquiries', 'supplier_id')) {
            $status_filter = tableHasColumn('buy_enquiries', 'status')
                ? " AND (status IS NULL OR status NOT IN ('deleted','removed'))"
                : "";
            $stmt = mysqli_prepare($con, "SELECT COUNT(*) AS count FROM buy_enquiries WHERE supplier_id = ?" . $status_filter);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'i', $uid);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = $result ? mysqli_fetch_assoc($result) : null;
                $total += (int)($row['count'] ?? 0);
                mysqli_stmt_close($stmt);
            }
        }

        return $total;
    }
}
?>
