<?php
declare(strict_types=1);
ob_start();

// ==================== دوال التاريخ والوقت ====================

if (!function_exists('dateDifference')) {
    function dateDifference(string $start_dt, string $end_dt): int {
        $start_ts = strtotime($start_dt);
        $end_ts = strtotime($end_dt);
        $diff = $end_ts - $start_ts;
        return (int)round($diff / 86400);
    }
}

if (!function_exists('dateRange')) {
    function dateRange(string $first, string $last, string $step = '+1 day', string $format = 'Y/m/d'): array {
        $dates = [];
        $current = strtotime($first);
        $last_ts = strtotime($last);
        
        while ($current < $last_ts) {
            $dates[] = date($format, $current);
            $current = strtotime($step, $current);
        }
        return $dates;
    }
}

if (!function_exists('dateAddition')) {
    function dateAddition(string $curr_date, int $days): string {
        return date('Y-m-j', strtotime("+{$days} day", strtotime($curr_date)));
    }
}

// ==================== دوال إعدادات الموقع ====================

if (!function_exists('init_site_settings')) {
    function init_site_settings(): array {
        global $con;
        $settings = [];
        
        $result = mysqli_query($con, "SELECT st_value, st_field, st_id FROM site_settings WHERE st_status = 1");
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $key = ($row['st_field'] == 'website-title') ? $row['st_field'] : (string)$row['st_id'];
                $settings[$key] = $row['st_value'];
            }
        }
        return $settings;
    }
}

if (!function_exists('getSiteTitle')) {
    function getSiteTitle(): string {
        global $site_settings;
        $title = $site_settings['website-title'] ?? '';
        return !empty($title) ? ucfirst($title) : 'EgyptMART';
    }
}

if (!function_exists('getWebSiteName')) {
    function getWebSiteName(): string {
        global $site_settings;
        $name = $site_settings[4] ?? '';
        return !empty($name) ? ucfirst($name) : 'EgyptMART';
    }
}

if (!function_exists('get_page_settings')) {
    function get_page_settings(int $id): string {
        global $site_settings;
        return $site_settings[$id] ?? '';
    }
}

if (!function_exists('get_site_settings')) {
    function get_site_settings(int $id): string {
        global $site_settings;
        return $site_settings[$id] ?? '';
    }
}

if (!function_exists('get_page_social')) {
    function get_page_social(int $id, string $type): string {
        global $con;
        $sql = "SELECT * FROM site_social WHERE st_id = '{$id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($result);
        return stripslashes($row[$type] ?? '');
    }
}

if (!function_exists('get_page_content')) {
    function get_page_content(int $id, string $field): string {
        global $con;
        $sql = "SELECT * FROM cms WHERE cms_id = '{$id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_assoc($result);
        return stripslashes($row[$field] ?? '');
    }
}

if (!function_exists('getSiteLogo')) {
    function getSiteLogo(): string {
        global $con;
        $sql = "SELECT st_value FROM site_settings WHERE st_id = '5' AND st_status = '1'";
        $result = mysqli_query($con, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_object($result);
            return $row->st_value;
        }
        return '';
    }
}

// ==================== دوال المستخدم ====================

if (!function_exists('getUserInfo')) {
    function getUserInfo(int $id, string $field): string {
        global $con;
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
        $sql = "SELECT {$field} FROM user WHERE usr_id = '{$id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        return (string)($row[0] ?? '');
    }
}

if (!function_exists('user_pref')) {
    function user_pref(int $id, string $field): string {
        return getUserInfo($id, $field);
    }
}

if (!function_exists('business_user_pref')) {
    function business_user_pref(int $id, string $field): string {
        global $con;
        $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
        $sql = "SELECT {$field} FROM business_profile WHERE bnsprof_uid = '{$id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
        return (string)($row[$field] ?? '');
    }
}

if (!function_exists('get_membership_expired')) {
    function get_membership_expired(): bool {
        global $con;
        $uid = $_SESSION['uid_indm'] ?? 0;
        
        if (!$uid) {
            return true;
        }
        
        $sql_bus = "SELECT bnsprof_id FROM business_profile WHERE bnsprof_uid = '{$uid}'";
        $result_bus = mysqli_query($con, $sql_bus);
        $bus = mysqli_fetch_object($result_bus);
        
        if (!$bus) {
            return true;
        }
        
        $sql_plan = "SELECT * FROM plan_member_id WHERE b_id = '" . (int)$bus->bnsprof_id . "'";
        $result_plan = mysqli_query($con, $sql_plan);
        $plan = mysqli_fetch_object($result_plan);
        
        if (!$plan) {
            return true;
        }
        
        return date('Y-m-d H:i:s', (int)$plan->expiry_date) > date('Y-m-d H:i:s');
    }
}

// ==================== دوال العملة ====================


if (!function_exists('getCurrency')) {
    /**
     * الحصول على رمز العملة بناءً على رمزها النصي (USD, EGP, EUR...)
     * @param string $currency_code رمز العملة النصي
     * @return string رمز العملة (مثل $, ج.م, €)
     */
    function getCurrency(string $currency_code): string {
        global $con;
        
        // قائمة افتراضية في حال فشل الاستعلام
        $default_currencies = [
            'USD' => '$',
            'EGP' => 'ج.م',
            'EUR' => '€',
            'GBP' => '£',
            'SAR' => 'ر.س',
            'AED' => 'د.إ',
        ];
        
        // تنظيف المدخل
        $currency_code = trim($currency_code);
        if (empty($currency_code)) {
            return '$';
        }
        
        // محاولة جلب رمز العملة من قاعدة البيانات
        $sql = "SELECT cn_currency FROM country WHERE cn_code = ? LIMIT 1";
        $stmt = mysqli_prepare($con, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $currency_code);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if ($row = mysqli_fetch_object($result)) {
                mysqli_stmt_close($stmt);
                return $row->cn_currency ?? ($default_currencies[$currency_code] ?? '$');
            }
            mysqli_stmt_close($stmt);
        }
        
        // الرجوع إلى القائمة الافتراضية
        return $default_currencies[$currency_code] ?? '$';
    }
}


if (!function_exists('getCurrencySymbol')) {
    /**
     * الحصول على رمز العملة حسب البلد المختار أو الإعدادات العامة
     * @return string رمز العملة (مثل $, ج.م, €, ر.س, د.إ)
     */
    function getCurrencySymbol(): string {
        global $con;
        
        // محاولة جلب العملة من موقع المستخدم (إذا كان مختاراً)
        $currency_symbol = 'ج.م'; // قيمة افتراضية
        
        if (isset($_COOKIE['loc_id'])) {
            $loc_id = (int)$_COOKIE['loc_id'];
            $sql = "SELECT cn_currency FROM country WHERE cn_id = ? LIMIT 1";
            $stmt = mysqli_prepare($con, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "i", $loc_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_object($result)) {
                    $currency_symbol = $row->cn_currency ?? 'ج.م';
                }
                mysqli_stmt_close($stmt);
            }
        } else {
            // إذا لم يكن هناك بلد مختار، جلب من الإعدادات العامة
            $sql = "SELECT st_value FROM site_settings WHERE st_field = 'currency-symbol' LIMIT 1";
            $result = mysqli_query($con, $sql);
            if ($result && mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_object($result);
                $currency_symbol = $row->st_value ?? 'ج.م';
            }
        }
        
        return $currency_symbol;
    }
}


// ==================== دوال الكلمات الرئيسية والوصف ====================

if (!function_exists('getMetaKeyWords')) {
    function getMetaKeyWords(): string {
        global $con;
        $sql = "SELECT st_value FROM site_settings WHERE st_id = '2'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return $row->st_value ?? '';
    }
}

if (!function_exists('getMetaDescription')) {
    function getMetaDescription(): string {
        global $con;
        $sql = "SELECT st_value FROM site_settings WHERE st_id = '3'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return $row->st_value ?? '';
    }
}

// ==================== دوال IP والموقع ====================

if (!function_exists('getRealIpAddr')) {
    function getRealIpAddr(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        }
        
        return $ip;
    }
}

if (!function_exists('getCountryCode')) {
    function getCountryCode(): string {
        $ip = getRealIpAddr();
        $url = "http://www.geoplugin.net/xml.gp?ip={$ip}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $xml = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error || !$xml) {
            return '';
        }
        
        try {
            $data = new SimpleXMLElement($xml);
            return (string)$data->geoplugin_countryCode;
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('getLocationInfoByIp')) {
    function getLocationInfoByIp(): array {
        return ['country' => '', 'city' => ''];
    }
}

// ==================== دوال البلدان والمدن ====================

if (!function_exists('get_country_name')) {
    function get_country_name(int $country_id): string {
        global $con;
        if ($country_id <= 0) return '';
        
        $sql = "SELECT cn_name FROM country WHERE cn_id = '{$country_id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        return $row[0] ?? '';
    }
}

if (!function_exists('get_city_name')) {
    function get_city_name(int $city_id): string {
        global $con;
        if ($city_id <= 0) return '';
        
        $sql = "SELECT ct_name FROM city WHERE ct_id = '{$city_id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        return $row[0] ?? '';
    }
}

if (!function_exists('get_state_name')) {
    function get_state_name(int $state_id): string {
        global $con;
        if ($state_id <= 0) return '';
        
        $sql = "SELECT state_name FROM states WHERE state_id = '{$state_id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_array($result, MYSQLI_NUM);
        return $row[0] ?? '';
    }
}

if (!function_exists('get_country_flag')) {
    function get_country_flag(int $country_id): string {
        global $con;
        if ($country_id <= 0) return '';
        
        $sql = "SELECT cn_flag FROM country WHERE cn_id = '{$country_id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return $row->cn_flag ?? '';
    }
}

if (!function_exists('get_country_phn_code')) {
    function get_country_phn_code(int $country_id): string {
        global $con;
        if ($country_id <= 0) return '';
        
        $sql = "SELECT cn_ph FROM country WHERE cn_id = '{$country_id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return $row->cn_ph ?? '';
    }
}

if (!function_exists('getCountryId')) {
    function getCountryId(): int {
        global $con;
        $code = getCountryCode();
        if (empty($code)) return 0;
        
        $sql = "SELECT cn_id FROM country WHERE cn_code = '" . mysqli_real_escape_string($con, $code) . "'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return (int)($row->cn_id ?? 0);
    }
}

// ==================== دوال الحظر ====================

if (!function_exists('getBlockedUserList')) {
    function getBlockedUserList(int $usr): string {
        global $con;
        $sql = "SELECT DISTINCT usr_id FROM user, blocked_user 
                WHERE (bu_blockBy = '{$usr}' AND bu_blocked = usr_id)
                   OR (bu_blocked = '{$usr}' AND bu_blockBy = usr_id)";
        $result = mysqli_query($con, $sql);
        
        $ids = [];
        while ($row = mysqli_fetch_object($result)) {
            $ids[] = $row->usr_id;
        }
        
        return implode(',', $ids);
    }
}

// ==================== دوال متنوعة ====================

if (!function_exists('checkActive')) {
    function checkActive(int $option_id): bool {
        global $con;
        $sql = "SELECT so_value FROM site_option WHERE so_id = '{$option_id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return ($row->so_value ?? '0') == '1';
    }
}

if (!function_exists('random_ID')) {
    function random_ID(int $length, string $prefix = ''): string {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random = '';
        
        for ($i = 0; $i < $length; $i++) {
            $random .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $prefix . $random;
    }
}

if (!function_exists('curPageURL')) {
    function curPageURL(): string {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        
        return $protocol . $host . $uri;
    }
}

if (!function_exists('getEmailVerificationStatus')) {
    function getEmailVerificationStatus(): int {
        global $con;
        $sql = "SELECT st_value FROM site_settings WHERE st_field = 'email-verification'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return (int)($row->st_value ?? 0);
    }
}

if (!function_exists('measurement_unit')) {
    function measurement_unit(int $id): string {
        global $con;
        if ($id <= 0) return '';
        
        $sql = "SELECT mu_name FROM measurement_unit WHERE mu_id = '{$id}'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return $row->mu_name ?? '';
    }
}

if (!function_exists('getServiceTaxRate')) {
    function getServiceTaxRate(): float {
        global $con;
        $sql = "SELECT st_value FROM site_settings WHERE st_field = 'service-tax-rate'";
        $result = mysqli_query($con, $sql);
        $row = mysqli_fetch_object($result);
        return (float)($row->st_value ?? 0);
    }
}

if (!function_exists('Show_shortcontent')) {
    function Show_shortcontent(string $text, int $count): string {
        $text = preg_replace('/\s+/', ' ', trim($text));
        $words = explode(' ', $text);
        
        if (count($words) <= $count) {
            return $text;
        }
        
        $short = array_slice($words, 0, $count);
        return implode(' ', $short) . '...';
    }
}

if (!function_exists('get_adminemail')) {
    function get_adminemail(): string {
        return 'admin@egyptmart.shop';
    }
}

// تعريف IN_SITE لمنع الأخطاء
if (!defined('IN_SITE')) {
    define('IN_SITE', true);
}
?>