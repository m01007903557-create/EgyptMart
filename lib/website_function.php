<?php
// function.php - نسخة PHP 8.3
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');

// منع الوصول المباشر
//if (!defined('IN_SITE')) {
    //exit('الوصول المباشر غير مسموح');
//}

// ==================== دوال الفيديو ====================

/**
 * تحويل رابط يوتيوب إلى كود تضمين
 * 
 * @param string $text النص الذي يحتوي على الرابط
 * @return string النص مع استبدال الرابط بكود التضمين
 */
function show_youtube(string $text): string {
    $VID_WID = 300;
    $VID_HEI = 200;
    
    for ($k = 0; $k < 9; $k++) {
        $text .= ' ';
        $find = 'youtube.com/watch?v=';
        $pos = strpos($text, $find);
        
        if ($pos === false) {
            break;
        }
        
        $len = strlen($text);
        
        // البحث عن بداية الرابط
        for ($i = $pos; $i >= 0; $i--) {
            if (substr($text, $i, 6) == 'http:/' || substr($text, $i, 7) == 'https:/') {
                $pos1 = $i;
                break;
            }
        }
        
        // البحث عن نهاية الرابط
        for ($i = $pos; $i < $len; $i++) {
            if (in_array($text[$i], ['&', ' ', "\r", "\n", ',', "\t", '"', "'"])) {
                $pos2 = $i;
                break;
            }
        }
        
        $link1 = substr($text, $pos1, $pos2 - $pos1);
        $videoId = '';
        
        // استخراج معرف الفيديو
        if (preg_match('/[\\?\\&]v=([^\\?\\&]+)/', $link1, $matches)) {
            $videoId = $matches[1];
        } elseif (preg_match('/youtu\.be\/([^\\?\\&]+)/', $link1, $matches)) {
            $videoId = $matches[1];
        }
        
        if (!empty($videoId)) {
            // استخدام iframe بدلاً من object (حديث)
            $embed = '<iframe width="' . $VID_WID . '" height="' . $VID_HEI . '" 
                src="https://www.youtube.com/embed/' . $videoId . '" 
                frameborder="0" allowfullscreen></iframe>';
        } else {
            // الطريقة القديمة كاحتياطي
            $link2 = str_replace('/watch?v=', '/v/', $link1);
            $embed = '<object width="' . $VID_WID . '" height="' . $VID_HEI . '">
                <param name="movie" value="' . $link2 . '"></param>
                <param name="allowFullScreen" value="true"></param>
                <param name="allowscriptaccess" value="always"></param>
                <embed src="' . $link2 . '" type="application/x-shockwave-flash" 
                allowscriptaccess="always" allowfullscreen="true" 
                width="' . $VID_WID . '" height="' . $VID_HEI . '"></embed></object>';
        }
        
        $text = str_replace($link1, $embed, $text);
    }
    
    return $text;
}

// ==================== دوال التصنيفات ====================

/**
 * الحصول على اسم التصنيف
 */
function get_category_name(string $id): string {
    global $con;
    $id = mysqli_real_escape_string($con, $id);
    $sql = mysqli_query($con, "SELECT pc_name FROM product_category WHERE md5(pc_id) = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return $row->pc_name ?? '';
}

/**
 * الحصول على معرف التصنيف
 */
function get_category_id(string $id): int {
    global $con;
    $id = mysqli_real_escape_string($con, $id);
    $sql = mysqli_query($con, "SELECT pc_id FROM product_category WHERE md5(pc_id) = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return (int)($row->pc_id ?? 0);
}

/**
 * الحصول على معلومات التصنيف الأب
 */
function prev_cat(string $id, string $type = 'name') {
    global $con;
    $id = mysqli_real_escape_string($con, $id);
    $sql = mysqli_query($con, "SELECT p.pc_name, p.pc_id, p.pc_parent_id 
                               FROM product_category p 
                               WHERE p.pc_id = (SELECT pc_parent_id FROM product_category WHERE md5(pc_id) = '{$id}') 
                               AND p.pc_status = '1' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    
    if (!$row) return '';
    
    if ($type == 'name') {
        return $row->pc_name ?? '';
    } elseif ($type == 'id') {
        return (int)($row->pc_id ?? 0);
    }
    return '';
}

/**
 * الحصول على اسم التصنيف حسب المستوى
 */
function get_catname(string $id, int $level, string $field = 'pc_name'): string {
    global $con;
    $id = mysqli_real_escape_string($con, $id);
    $sql = mysqli_query($con, "SELECT * FROM product_category WHERE md5(pc_id) = '{$id}' LIMIT 1");
    $row = mysqli_fetch_assoc($sql);
    
    if (!$row) return '';
    
    if ($level == 0) {
        $sqlh = mysqli_query($con, "SELECT * FROM product_category WHERE pc_id = '" . (int)$row['pc_parent_id'] . "' LIMIT 1");
        $rowh = mysqli_fetch_assoc($sqlh);
        return ucwords($rowh[$field] ?? '');
    } elseif ($level == 1) {
        return ucwords($row[$field] ?? '');
    }
    
    return '';
}

// ==================== دوال الدول والمدن ====================

/**
 * الحصول على اسم الدولة
 */
function get_country_name(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT cn_name FROM country WHERE cn_id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return ucwords($row->cn_name ?? '');
}

/**
 * الحصول على علم الدولة
 */
function get_country_flag(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT cn_flag FROM country WHERE cn_id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return $row->cn_flag ?? '';
}

/**
 * الحصول على رمز هاتف الدولة
 */
function get_country_phn_code(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT cn_ph FROM country WHERE cn_id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return $row->cn_ph ?? '';
}

/**
 * الحصول على اسم الدولة من المدينة
 */
function city_to_country(int $city_id): string {
    global $con;
    if ($city_id <= 0) return '';
    $sql = mysqli_query($con, "SELECT cn_name FROM country, city 
                               WHERE ct_cn_id = cn_id AND ct_id = '{$city_id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return ucwords($row->cn_name ?? '');
}

/**
 * الحصول على اسم الولاية
 */
function get_state_name(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT state_name FROM states WHERE state_id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return ucwords($row->state_name ?? '');
}

/**
 * الحصول على اسم المدينة
 */
function get_city_name(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT ct_name FROM city WHERE ct_id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return ucwords($row->ct_name ?? '');
}

/**
 * الحصول على اسم الدولة من معرف الولاية
 */
function get_cntname(int $id, string $type = 'state'): string {
    global $con;
    if ($id <= 0) return '';
    
    if ($type == 'state') {
        $sql = mysqli_query($con, "SELECT cn_name FROM states, country 
                                   WHERE state_cn_id = cn_id AND state_id = '{$id}' LIMIT 1");
        $row = mysqli_fetch_object($sql);
        return ucwords($row->cn_name ?? '');
    }
    
    return '';
}

// ==================== دوال المنتجات ====================

/**
 * الحصول على تفاصيل المنتج
 */
function get_product_detail(int $id, string $field) {
    global $con;
    $sqlchk = "SELECT * FROM products WHERE pd_id = '{$id}' LIMIT 1";
    $reschk = mysqli_query($con, $sqlchk);
    $rowchk = mysqli_fetch_assoc($reschk);
    
    if (!$rowchk) return '';
    
    if ($field == 'pd_currency') {
        $sql = mysqli_query($con, "SELECT cn_currency FROM country WHERE cn_id = '" . (int)$rowchk['pd_currency'] . "' LIMIT 1");
        $row = mysqli_fetch_assoc($sql);
        return $row['cn_currency'] ?? '';
    } elseif ($field == 'pd_unit') {
        $sql = mysqli_query($con, "SELECT mu_name FROM measurement_unit WHERE mu_id = '" . (int)$rowchk['pd_unit'] . "' LIMIT 1");
        $row = mysqli_fetch_assoc($sql);
        return $row['mu_name'] ?? '';
    } else {
        return $rowchk[$field] ?? '';
    }
}

/**
 * الحصول على شروط الدفع للمنتج
 */
function get_payment_terms(int $id): string {
    global $con;
    $sqlchk = "SELECT pd_payment FROM products WHERE pd_id = '{$id}' LIMIT 1";
    $reschk = mysqli_query($con, $sqlchk);
    $rowchk = mysqli_fetch_assoc($reschk);
    
    if (!$rowchk || empty($rowchk['pd_payment'])) return '';
    
    $ids = explode(',', $rowchk['pd_payment']);
    $ids = array_map('intval', $ids);
    $ids_string = implode(',', $ids);
    
    $sql = "SELECT pg_name FROM payment_gateway WHERE id IN ({$ids_string}) AND pg_status = '1'";
    $res = mysqli_query($con, $sql);
    
    $names = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $names[] = $row['pg_name'];
    }
    
    return implode(', ', $names);
}

// ==================== دوال المستخدمين ====================

/**
 * الحصول على معلومات المستخدم
 */
function user_info(int $id, string $field): string {
    global $con;
    $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    $sql = "SELECT u.*, b.* FROM user u 
            LEFT JOIN business_profile b ON u.usr_id = b.bnsprof_uid 
            WHERE u.usr_id = '{$id}' AND u.status = '1' LIMIT 1";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row[$field] ?? '';
}

/**
 * الحصول على معلومات البائع
 */
function seller_info(int $id, string $field): string {
    global $con;
    $field = preg_replace('/[^a-zA-Z0-9_]/', '', $field);
    $sql = "SELECT * FROM seller WHERE sllr_usr_id = '{$id}' AND sllr_status = '1' LIMIT 1";
    $res = mysqli_query($con, $sql);
    $row = mysqli_fetch_assoc($res);
    return $row[$field] ?? '';
}

/**
 * الحصول على اسم المسمى الوظيفي
 */
function user_designation_name(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT desig_title FROM designation 
                               WHERE desig_id = '{$id}' AND desig_status = '1' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return $row->desig_title ?? '';
}

// ==================== دوال بوابات الدفع ====================

/**
 * الحصول على اسم بوابة الدفع
 */
function get_gateway_name(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT pg_name FROM payment_gateway WHERE id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return $row->pg_name ?? '';
}

// ==================== دوال الإدارة ====================

/**
 * الحصول على بريد الإدارة
 */
function get_adminemail(): string {
    global $con;
    $sql = mysqli_query($con, "SELECT email FROM admin_user WHERE status = '1' LIMIT 1");
    $row = mysqli_fetch_assoc($sql);
    return $row['email'] ?? 'admin@egyptmart.shop';
}

// ==================== دوال القياس ====================

/**
 * الحصول على اسم وحدة القياس
 */
function get_measurement_unit(int $id): string {
    global $con;
    if ($id <= 0) return '';
    $sql = mysqli_query($con, "SELECT mu_name FROM measurement_unit WHERE mu_id = '{$id}' LIMIT 1");
    $row = mysqli_fetch_object($sql);
    return $row->mu_name ?? '';
}

// ==================== دوال متنوعة ====================

/**
 * التحقق من تسجيل الدخول
 */
function chkloggedin(int $id): void {
    global $con;
    $sql = mysqli_query($con, "SELECT usr_id FROM user WHERE usr_id = '{$id}' LIMIT 1");
    if (mysqli_num_rows($sql) < 1) {
        header("Location: index.php");
        exit;
    }
}

/**
 * تحويل الرقم إلى ترتيبي (1st, 2nd, 3rd...)
 */
function ordinal(int $num): string {
    if (($num / 10) % 10 != 1) {
        switch ($num % 10) {
            case 1: return $num . 'st';
            case 2: return $num . 'nd';
            case 3: return $num . 'rd';
        }
    }
    return $num . 'th';
}

/**
 * الحصول على قائمة الدول النشطة
 */
function getActiveCountryList(): string {
    global $con;
    $time = time();
    
    $sql = "SELECT DISTINCT cn_id FROM country 
            WHERE cn_id IN (
                SELECT DISTINCT u.country FROM products p
                JOIN user u ON p.pd_uid = u.usr_id
                JOIN business_profile b ON u.usr_id = b.bnsprof_uid
                JOIN plan_member_id pm ON b.bnsprof_id = pm.b_id
                WHERE pm.expiry_date > {$time} AND p.pd_status = '1'
            ) OR cn_id IN (
                SELECT DISTINCT u.country FROM buy_requirement br
                JOIN user u ON br.br_u_id = u.usr_id
                JOIN business_profile b ON u.usr_id = b.bnsprof_uid
                JOIN plan_member_id pm ON b.bnsprof_id = pm.b_id
                WHERE pm.expiry_date > {$time} AND br.br_approval_status = '1' 
                AND br.br_display_status = '1' AND br.br_status = '1'
            ) OR cn_id IN (
                SELECT DISTINCT u.country FROM sale_offer so
                JOIN user u ON so.so_usr_id = u.usr_id
                JOIN business_profile b ON u.usr_id = b.bnsprof_uid
                JOIN plan_member_id pm ON b.bnsprof_id = pm.b_id
                WHERE pm.expiry_date > {$time} AND so.so_approval_status = '1'
                AND DATE_ADD(so.so_approval_date, INTERVAL so.so_validity DAY) >= NOW()
                AND so.so_status = '1'
            )";
    
    $res = mysqli_query($con, $sql);
    $countries = [];
    
    while ($row = mysqli_fetch_object($res)) {
        $countries[] = $row->cn_id;
    }
    
    return implode(',', $countries);
}

// ==================== دوال البانرات والإعلانات ====================

/**
 * الحصول على بانر الصفحة الرئيسية
 */
function GetHomeBanner(string $pos, string $strconutnry = ""): string {
    global $con;
    $sqlban = "SELECT * FROM advertisementhome 
               WHERE adv_position = '" . mysqli_real_escape_string($con, $pos) . "' 
               AND adv_status = '1' {$strconutnry} 
               ORDER BY adv_id DESC LIMIT 1";
    
    $rsquery = mysqli_query($con, $sqlban);
    
    if (mysqli_num_rows($rsquery) > 0) {
        $row = mysqli_fetch_object($rsquery);
        $logopath = "https://egyptmart.shop/upload/advertisementhome/" . $row->adv_img;
        $adv_link = $row->adv_link;
        return '<a href="' . htmlspecialchars($adv_link) . '"><img src="' . $logopath . '" width="100%" alt="Advertisement"></a>';
    }
    
    return '';
}

/**
 * الحصول على إعدادات الموقع
 */
function GettingSite_Setting(string $st_field): string {
    global $con;
    $st_field = mysqli_real_escape_string($con, $st_field);
    $sqlban = "SELECT st_value FROM site_settings 
               WHERE st_field = '{$st_field}' AND st_status = '1' LIMIT 1";
    $rsquery = mysqli_query($con, $sqlban);
    
    if (mysqli_num_rows($rsquery) > 0) {
        $row = mysqli_fetch_object($rsquery);
        return $row->st_value ?? '';
    }
    
    return '';
}

/**
 * الحصول على بانر التصنيفات
 */
function categoryAdsBanner(string $strconutnry = "", string $categoryid = "", string $supplierid = "", string $position = ""): string {
    global $con;
    
    $countryCond = '';
    if (!empty($strconutnry)) {
        $countryCond = " AND (adv_country LIKE '%" . mysqli_real_escape_string($con, $strconutnry) . "%' 
                           OR adv_country LIKE '%," . mysqli_real_escape_string($con, $strconutnry) . "%' 
                           OR adv_country LIKE '%" . mysqli_real_escape_string($con, $strconutnry) . ",%')";
    }
    
    $positionCond = !empty($position) ? " AND adv_position = '" . mysqli_real_escape_string($con, $position) . "'" : "";
    
    if (!empty($supplierid)) {
        $sqlban = "SELECT * FROM advertisementcathome 
                   WHERE adv_supplier_id = '" . (int)$supplierid . "' 
                   AND adv_status = '1' {$countryCond} {$positionCond} 
                   ORDER BY adv_id DESC LIMIT 1";
    } elseif (!empty($categoryid)) {
        $cat_id = (int)$categoryid;
        $sqlban = "SELECT * FROM advertisementcathome 
                   WHERE adv_status = '1' 
                   AND (adv_cat_id = '{$cat_id}' OR adv_subcat_id = '{$cat_id}' OR adv_subsub_cat_id = '{$cat_id}') 
                   {$countryCond} {$positionCond} 
                   ORDER BY adv_id DESC LIMIT 1";
    } else {
        return '';
    }
    
    $rsquery = mysqli_query($con, $sqlban);
    
    if (mysqli_num_rows($rsquery) > 0) {
        $row = mysqli_fetch_object($rsquery);
        $logopath = "https://egyptmart.shop/upload/advertisementcathome/" . $row->adv_img;
        $adv_link = $row->adv_link;
        return $row->adv_position . '~~<a href="' . htmlspecialchars($adv_link) . '"><img src="' . $logopath . '" width="100%" alt="Category Advertisement"></a>';
    }
    
    return '';
}

/**
 * الحصول على بانر ثابت
 */
function staticAdsBanner(string $position = ""): string {
    global $con;
    
    $positionCond = !empty($position) ? " AND adv_position = '" . mysqli_real_escape_string($con, $position) . "'" : "";
    
    $sqlban = "SELECT * FROM advertisement 
               WHERE adv_status = '1' {$positionCond} 
               ORDER BY adv_id DESC LIMIT 1";
    
    $rsquery = mysqli_query($con, $sqlban);
    
    if (mysqli_num_rows($rsquery) > 0) {
        $row = mysqli_fetch_object($rsquery);
        $logopath = "https://egyptmart.shop/upload/advertisement/" . $row->adv_img;
        $adv_link = $row->adv_link;
        
        $height_attr = '';
        if ($position == 'left') {
            $data = @getimagesize($logopath);
            if ($data) {
                $height_attr = 'height="' . $data[1] . '"';
            }
        }
        
        return '<a href="' . htmlspecialchars($adv_link) . '"><img src="' . $logopath . '" width="100%" ' . $height_attr . ' style="display:block !important;" alt="Advertisement"></a>';
    }
    
    return '';
}
?>