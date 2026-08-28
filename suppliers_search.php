<?php
/**
 * File: suppliers_search.php

 * Description: عرض الشركات (الموردين) حسب التصنيف مع تفاصيل المنتجات والمعلومات
 * Version: 2.0.0 (PHP 8.3 Compatible)
 */

declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/common.php';

global $con;

// =============================================
// دوال مساعدة لتوليد سلاسل البحث
// =============================================
function generateProdSearchString(string $keywords): string {
    $key_array = explode(" ", $keywords);
    $conditions = [];
    
    foreach ($key_array as $v) {
        $v = trim($v);
        if (!empty($v)) {
            $conditions[] = "pd_title LIKE '%" . mysqli_real_escape_string($GLOBALS['con'], $v) . "%'";
        }
    }
    
    return implode(" AND ", $conditions);
}

function generateSupplierSearchString(string $keywords): string {
    $key_array = explode(" ", $keywords);
    $conditions = [];
    
    foreach ($key_array as $v) {
        $v = trim($v);
        if (!empty($v)) {
            $conditions[] = "bnsprof_compname LIKE '%" . mysqli_real_escape_string($GLOBALS['con'], $v) . "%'";
        }
    }
    
    return implode(" OR ", $conditions);
}

function generateBuyleadSearchString(string $keywords): string {
    $key_array = explode(" ", $keywords);
    $conditions = [];
    
    foreach ($key_array as $v) {
        $v = trim($v);
        if (!empty($v)) {
            $v = mysqli_real_escape_string($GLOBALS['con'], $v);
            $conditions[] = "(br_pd_name LIKE '%$v%' OR br_requirement LIKE '%$v%')";
        }
    }
    
    return implode(" OR ", $conditions);
}

function generateTenderSearchString(string $keywords): string {
    $key_array = explode(" ", $keywords);
    $conditions = [];
    
    foreach ($key_array as $v) {
        $v = trim($v);
        if (!empty($v)) {
            $v = mysqli_real_escape_string($GLOBALS['con'], $v);
            $conditions[] = "(tnd_heading LIKE '%$v%' OR tnd_details LIKE '%$v%')";
        }
    }
    
    return implode(" OR ", $conditions);
}

function generateAuctionSearchString(string $keywords): string {
    $key_array = explode(" ", $keywords);
    $conditions = [];
    
    foreach ($key_array as $v) {
        $v = trim($v);
        if (!empty($v)) {
            $v = mysqli_real_escape_string($GLOBALS['con'], $v);
            $conditions[] = "(auc_heading LIKE '%$v%' OR auc_details LIKE '%$v%')";
        }
    }
    
    return implode(" OR ", $conditions);
}

// =============================================
// بناء شروط الموقع (Location Conditions)
// =============================================
$sql_pd_ck = "";
if (isset($_COOKIE['loc_id']) && !empty($_COOKIE['loc_id'])) {
    $loc_id = (int)$_COOKIE['loc_id'];
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'domestic' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id)) 
        OR 
        (pd_preferred_buyer_location = 'any' AND pd_uid IN (SELECT DISTINCT usr_id FROM user WHERE country = $loc_id))
        OR
        (pd_preferred_buyer_location = 'my_city' AND pd_uid IN (
            SELECT DISTINCT bnsprof_uid FROM business_profile bf 
            INNER JOIN city c ON bf.bnsprof_city = c.ct_id 
            WHERE c.ct_cn_id = $loc_id
        ))
    )";
} else {
    $location_geo_country = $location_geo_country ?? '';
    $sql_pd_ck = " AND (
        (pd_preferred_buyer_location = 'any')
        OR
        (pd_preferred_buyer_location = 'abroad' AND pd_uid NOT IN (
            SELECT DISTINCT usr_id FROM user 
            WHERE country = (SELECT cn_id FROM country WHERE cn_code = ?)
        ))
    )";
}

// الحصول على معلومات الموقع من IP
$location = getLocationInfoByIp();

// =============================================
// تحديد التصنيف
// =============================================
$category_p_id = 0;
$category_name = '';

if (isset($_GET['token'])) {
    $token = substr($_GET['token'], 4);
    
    $sql_scat = "SELECT pc_id, pc_name FROM product_category 
                 WHERE MD5(pc_id) = ? AND pc_status = '1' 
                 LIMIT 1";
    
    $stmt_scat = mysqli_prepare($con, $sql_scat);
    mysqli_stmt_bind_param($stmt_scat, 's', $token);
    mysqli_stmt_execute($stmt_scat);
    $result_scat = mysqli_stmt_get_result($stmt_scat);
    $row_scat = mysqli_fetch_assoc($result_scat);
    mysqli_stmt_close($stmt_scat);
    
    if ($row_scat) {
        $category_p_id = (int)$row_scat['pc_id'];
        $category_name = htmlspecialchars($row_scat['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
    }
}

// =============================================
// إعدادات التصفح (Pagination)
// =============================================
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// =============================================
// جلب الشركات
// =============================================
$companies = [];
$total_companies = 0;

if (isset($_GET['keywords']) && !empty($_GET['keywords'])) {
    // بحث بالكلمات المفتاحية
    $keywords = mysqli_real_escape_string($con, $_GET['keywords']);
    
    $count_sql = "SELECT COUNT(DISTINCT pd_uid) as total 
                  FROM business_profile bf 
                  INNER JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                  INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id 
                  INNER JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                  WHERE bf.bnsprof_compname LIKE '%$keywords%' 
                  AND pm.expiry_date > " . time() . " 
                  AND pc.pc_status = '1' 
                  AND p.pd_status = '1'";
    
    $count_result = mysqli_query($con, $count_sql);
    $count_row = mysqli_fetch_assoc($count_result);
    $total_companies = (int)($count_row['total'] ?? 0);
    
    $sql_comp = "SELECT bf.*, p.*, pc.*, pm.* 
                 FROM business_profile bf 
                 INNER JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                 INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id 
                 INNER JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                 WHERE bf.bnsprof_compname LIKE '%$keywords%' 
                 AND pm.expiry_date > " . time() . " 
                 AND pc.pc_status = '1' 
                 AND p.pd_status = '1' 
                 GROUP BY bf.bnsprof_uid 
                 LIMIT $offset, $per_page";
    
} else {
    // عرض حسب التصنيف
    if ($category_p_id > 0) {
        $count_sql = "SELECT COUNT(DISTINCT bf.bnsprof_uid) as total 
                      FROM business_profile bf 
                      INNER JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                      INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id 
                      INNER JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                      WHERE pc.pc_parent_id = $category_p_id 
                      AND pm.expiry_date > " . time() . " 
                      AND pc.pc_status = '1' 
                      $sql_pd_ck 
                      AND p.pd_status = '1'";
        
        $count_result = mysqli_query($con, $count_sql);
        $count_row = mysqli_fetch_assoc($count_result);
        $total_companies = (int)($count_row['total'] ?? 0);
        
        $sql_comp = "SELECT bf.*, p.*, pc.*, pm.* 
                     FROM business_profile bf 
                     INNER JOIN products p ON bf.bnsprof_uid = p.pd_uid 
                     INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id 
                     INNER JOIN plan_member_id pm ON pm.b_id = bf.bnsprof_id 
                     WHERE pc.pc_parent_id = $category_p_id 
                     AND pm.expiry_date > " . time() . " 
                     AND pc.pc_status = '1' 
                     $sql_pd_ck 
                     AND p.pd_status = '1' 
                     GROUP BY bf.bnsprof_uid";
    }
}

$result_comp = isset($sql_comp) ? mysqli_query($con, $sql_comp) : null;
?>
<!DOCTYPE html>
<html lang="ar" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(getSiteTitle(), ENT_QUOTES, 'UTF-8'); ?> :: Suppliers <?php echo $category_name; ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars(get_page_settings(2), ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="description" content="<?php echo htmlspecialchars(get_page_settings(3), ENT_QUOTES, 'UTF-8'); ?>">
    
    <link type="text/css" rel="stylesheet" href="css/main-v2.css">
    <link href="css/dir-style-8.css" type="text/css" rel="stylesheet">
    <link href="css/overlay-v2.css" type="text/css" rel="stylesheet">
    <link href="css/bl_form_temp5.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="css/menu_styles.css" type="text/css" />
    <link href="css/colorbox.css" type="text/css" rel="stylesheet">
    
    <style>
        .g9 {
            font-size: 13px;
            background: white;
            padding: 17px;
        }
        #supplierid img { border: 1px solid #000; }
        #supplierid td {
            width: 100px;
            max-width: 100px;
            word-wrap: break-word;
        }
        @media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
            .my-container {
                width: 344px !important;
                border: 1px solid #c2e6fe !important;
                padding: 14px !important;
                background: #fff !important;
            }
            .col-md-2 {
                padding-left: 7px!important;
                padding-right: 7px!important;
            }
        }
        @media only screen and (min-width : 1224px) {
            .my-container {
                width: 850px !important;
                border: 1px solid #c2e6fe !important;
                padding: 14px !important;
                background: #fff !important;
                margin-left: auto;
                margin-right: auto;
            }
            .middle-part {
                margin-left: auto;
                margin-right: auto;
            }
        }
        @media only screen and (min-width : 1824px) {
            .my-container {
                width: 1000px !important;
                border: 1px solid #c2e6fe !important;
                padding: 14px!important;
                background: #fff !important;
            }
        }
        .cat_image_div {
            height: 100px!important;
            width: 100px !important;
            border: 1px solid #c3bdbd !important;
        }
        .cat_image {
            width: 100% !important;
            height: 100% !important;
        }
        @media only screen and (max-width: 768px) {
            .cat_image {
                width: 100% !important;
                height: 100% !important;
            }
            .cat_image_div {
                border: 1px solid #cac6c6;
                height: 80px !important;
                width: 80px !important;
            }
            .three-pics1, .col-md-2 {
                width: 100px !important;
                padding: 0px !important;
            }
        }
        .page-header-col2-intro-texts .post-product-btn {
            font-size: 14px !important;
        }
        .page-header-col2-intro-texts .post-product-btn small {
            font-size: 10px !important;
        }
    </style>
    
    <script language="javascript" type="text/javascript" src="js/jquery-1.11.1.min.js"></script>
    <script src="js/jquery.colorbox.js"></script>
    
    <script>
    $(document).on('click', '.ajax', function() {
        $.colorbox({
            href: $(this).attr('href'),
            open: true,
            iframe: true,
            width: '750px',
            height: '600px'
        });
        return false;
    });
    
    $(document).ready(function() {
        $(".inline").colorbox({inline: true, width: "50%"});
    });
    </script>
</head>
<body data-twttr-rendered="true">
    <div class="q_hm1">
        <!-- Header -->
        <?php include __DIR__ . "/includes/header_new.php"; ?>
        
        <div class="a1 wd cl" align="left">
            <p class="m2"></p>
            
            <!-- مسار التنقل (Breadcrumb) -->
            <div class="brdcrm">
                <ul>
                    <li>
                        <a href="dir.php" target="_top">
                            <img class="h_imgin bg nr" alt="" src="images/zero.gif" style="padding-right:5px;" height="20px" width="24px">
                            <span itemprop="title">Suppliers Directory</span>
                        </a>
                        <span><img src="images/zero.gif" alt="" class="brdcrm-arwin_pgen bg nr" height="16px" width="16px"></span>
                    </li>
                    <li style="border:none; font-size:13px; color:#444; display:inline;">
                        <?php echo $category_name; ?>
                    </li>
                </ul>
                <p style="padding:0; margin:0; clear:both"></p>
            </div>
            
            <p class="m2"></p>
            
            <div class="p4">
                <!-- القائمة الجانبية اليسرى -->
                <div id="res" class="lft fl">
                    <?php include_once __DIR__ . '/index_leftsidebar.php'; ?>
                </div>
                
                <?php if (!isset($_GET['rctyp'])) echo "</div>"; ?>
                
                <!-- المحتوى الرئيسي -->
                <div class="middle-part" style="width:850px !important;">
                    <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize" 
                            style="border-top:2px solid #ff7519 !important; border:0px; font-weight:700;">
                        Suppliers
                    </button>
                    
                    <button type="button" class="btn btn-default border-radius-0 txt-bold bold-xs btn-white text-capitalize" 
                            style="background-color:#F5F7FA">
                        <a href="products.php?c=<?php echo md5((string)$category_p_id); ?>" style="color:#000; font-weight:700;" target="_blank">
                            Products
                        </a>
                    </button>
                </div>
                
                <?php if ($result_comp && mysqli_num_rows($result_comp) > 0): ?>
                    <?php while ($row_comp = mysqli_fetch_assoc($result_comp)): 
                        $bnsprof_uid = (int)($row_comp['bnsprof_uid'] ?? 0);
                        $bnsprof_id = (int)($row_comp['bnsprof_id'] ?? 0);
                        $bnsprof_compname = htmlspecialchars($row_comp['bnsprof_compname'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_city = (int)($row_comp['bnsprof_city'] ?? 0);
                        $bnsprof_state = (int)($row_comp['bnsprof_state'] ?? 0);
                        $bnsprof_address1 = htmlspecialchars($row_comp['bnsprof_address1'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_address2 = htmlspecialchars($row_comp['bnsprof_address2'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_website_alt = htmlspecialchars($row_comp['bnsprof_website_alt'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_mobile2 = htmlspecialchars($row_comp['bnsprof_mobile2'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_mobile3 = htmlspecialchars($row_comp['bnsprof_mobile3'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_mobile4 = htmlspecialchars($row_comp['bnsprof_mobile4'] ?? '', ENT_QUOTES, 'UTF-8');
                        $bnsprof_businesstype = $row_comp['bnsprof_businesstype'] ?? '';
                        $pd_image = htmlspecialchars($row_comp['pd_image'] ?? '', ENT_QUOTES, 'UTF-8');
                        $pd_title = htmlspecialchars($row_comp['pd_title'] ?? '', ENT_QUOTES, 'UTF-8');
                        
                        // جلب معلومات الدولة
                        $country_id = (int)user_info($bnsprof_uid, 'country');
                        $country_sql = "SELECT cn_name FROM country WHERE cn_id = ? LIMIT 1";
                        $stmt_country = mysqli_prepare($con, $country_sql);
                        mysqli_stmt_bind_param($stmt_country, 'i', $country_id);
                        mysqli_stmt_execute($stmt_country);
                        $country_result = mysqli_stmt_get_result($stmt_country);
                        $country_row = mysqli_fetch_assoc($country_result);
                        $country_name = $country_row ? htmlspecialchars($country_row['cn_name'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_country);
                        
                        // جلب معلومات خطة العضوية
                        $plan_sql = "SELECT sip.mst_icon, sip.mst_name 
                                     FROM plan_member_id pm
                                     INNER JOIN smembership_icon_plan sip ON pm.p_id = sip.mp_id
                                     WHERE pm.b_id = ? 
                                     LIMIT 1";
                        $stmt_plan = mysqli_prepare($con, $plan_sql);
                        mysqli_stmt_bind_param($stmt_plan, 'i', $bnsprof_id);
                        mysqli_stmt_execute($stmt_plan);
                        $plan_result = mysqli_stmt_get_result($stmt_plan);
                        $plan_row = mysqli_fetch_assoc($plan_result);
                        $plan_icon = $plan_row ? htmlspecialchars($plan_row['mst_icon'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        $plan_name = $plan_row ? htmlspecialchars($plan_row['mst_name'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_plan);
                        
                        // جلب البانر
                        $banner = null;
                        $banner_sql = "SELECT cb_image FROM company_banner WHERE cb_bnsprof_id = ? AND cb_status = '1' LIMIT 1";
                        $stmt_banner = mysqli_prepare($con, $banner_sql);
                        mysqli_stmt_bind_param($stmt_banner, 'i', $bnsprof_id);
                        mysqli_stmt_execute($stmt_banner);
                        $banner_result = mysqli_stmt_get_result($stmt_banner);
                        $banner_row = mysqli_fetch_assoc($banner_result);
                        $banner_image = $banner_row ? htmlspecialchars($banner_row['cb_image'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_banner);
                        
                        // جلب محتوى الموقع
                        $wc_content = '';
                        $wc_sql = "SELECT wc_homepage_key_desc FROM website_content WHERE wc_usr_id = ? LIMIT 1";
                        $stmt_wc = mysqli_prepare($con, $wc_sql);
                        mysqli_stmt_bind_param($stmt_wc, 'i', $bnsprof_uid);
                        mysqli_stmt_execute($stmt_wc);
                        $wc_result = mysqli_stmt_get_result($stmt_wc);
                        $wc_row = mysqli_fetch_assoc($wc_result);
                        $wc_content = $wc_row ? htmlspecialchars(stripslashes($wc_row['wc_homepage_key_desc'] ?? ''), ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_wc);
                        
                        // جلب "من نحن"
                        $about = null;
                        $about_sql = "SELECT abtus_image, abtus_desc 
                                      FROM about_us a
                                      INNER JOIN profile_heading ph ON a.abtus_ph_id = ph.ph_id
                                      INNER JOIN website_content wc ON a.abtus_wc_id = wc.wc_id
                                      WHERE wc.wc_usr_id = ? AND ph.ph_id = 1 
                                      LIMIT 1";
                        $stmt_about = mysqli_prepare($con, $about_sql);
                        mysqli_stmt_bind_param($stmt_about, 'i', $bnsprof_uid);
                        mysqli_stmt_execute($stmt_about);
                        $about_result = mysqli_stmt_get_result($stmt_about);
                        $about_row = mysqli_fetch_assoc($about_result);
                        $about_image = $about_row ? htmlspecialchars($about_row['abtus_image'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        $about_desc = $about_row ? htmlspecialchars($about_row['abtus_desc'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_about);
                        
                        // جلب أنواع الأعمال
                        $business_types = [];
                        if (!empty($bnsprof_businesstype)) {
                            $type_ids = explode(',', $bnsprof_businesstype);
                            $type_ids = array_map('intval', $type_ids);
                            if (!empty($type_ids)) {
                                $type_ids_str = implode(',', $type_ids);
                                $type_sql = "SELECT bsntyp_title FROM business_type WHERE bsntyp_id IN ($type_ids_str)";
                                $type_result = mysqli_query($con, $type_sql);
                                while ($type_row = mysqli_fetch_assoc($type_result)) {
                                    $business_types[] = htmlspecialchars($type_row['bsntyp_title'] ?? '', ENT_QUOTES, 'UTF-8');
                                }
                            }
                        }
                        $business_types_str = implode(', ', $business_types);
                        
                        // جلب التصنيفات الرئيسية للشركة
                        $main_business = [];
                        $prod_cat_sql = "SELECT DISTINCT pc.pc_name 
                                         FROM products p
                                         INNER JOIN product_category pc ON p.pd_subcat_id = pc.pc_id
                                         WHERE p.pd_uid = ? 
                                         LIMIT 5";
                        $stmt_prod_cat = mysqli_prepare($con, $prod_cat_sql);
                        mysqli_stmt_bind_param($stmt_prod_cat, 'i', $bnsprof_uid);
                        mysqli_stmt_execute($stmt_prod_cat);
                        $prod_cat_result = mysqli_stmt_get_result($stmt_prod_cat);
                        while ($cat_row = mysqli_fetch_assoc($prod_cat_result)) {
                            $main_business[] = htmlspecialchars($cat_row['pc_name'] ?? '', ENT_QUOTES, 'UTF-8');
                        }
                        mysqli_stmt_close($stmt_prod_cat);
                        $main_business_str = implode(', ', $main_business);
                        
                        // جلب معلومات المستخدم
                        $user_sql = "SELECT mobile1 FROM user WHERE usr_id = ? LIMIT 1";
                        $stmt_user = mysqli_prepare($con, $user_sql);
                        mysqli_stmt_bind_param($stmt_user, 'i', $bnsprof_uid);
                        mysqli_stmt_execute($stmt_user);
                        $user_result = mysqli_stmt_get_result($stmt_user);
                        $user_row = mysqli_fetch_assoc($user_result);
                        $user_mobile = $user_row ? htmlspecialchars($user_row['mobile1'] ?? '', ENT_QUOTES, 'UTF-8') : '';
                        mysqli_stmt_close($stmt_user);
                        
                        // التحقق من وجود الصور
                        $thumb_path = __DIR__ . "/upload/myproduct/thumb/" . $pd_image;
                        $thumb_exists = !empty($pd_image) && file_exists($thumb_path) && is_file($thumb_path);
                        
                        $banner_path = __DIR__ . "/upload/company_banner/" . $banner_image;
                        $banner_exists = !empty($banner_image) && file_exists($banner_path) && is_file($banner_path);
                        
                        $about_path = __DIR__ . "/upload/myprofile/" . $about_image;
                        $about_exists = !empty($about_image) && file_exists($about_path) && is_file($about_path);
                    ?>
                    <div class="my-container">
                        <div class="row">
                            <div class="col-md-1" style="width:4%">
                                <?php if (!empty($plan_icon)): ?>
                                <img src="admin/images/<?php echo $plan_icon; ?>" title="<?php echo $plan_name; ?>" width="30px" height="30px">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <a <?php if (!empty($bnsprof_compname)): ?>href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>"<?php endif; ?> target="_blank">
                                    <span class="city-country" style="font-weight:900;"><?php echo $bnsprof_compname; ?></span>
                                </a><br>
                                <?php echo $country_name; ?>, <?php echo htmlspecialchars(get_city_name($bnsprof_city), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>
                        
                        <div class="row" style="padding-top:10px;">
                            <div class="three-pics1">
                                <!-- صورة المنتج -->
                                <div class="col-md-2">
                                    <?php if ($thumb_exists): ?>
                                    <div style="width:100px;">
                                        <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>" target="_blank">
                                            <div class="cat_image_div">
                                                <img src="/upload/myproduct/thumb/<?php echo $pd_image; ?>" 
                                                     title="<?php echo $pd_title; ?>" class="cat_image">
                                            </div>
                                        </a>
                                        <br>
                                        <span style="font-size:11px; color:#37366d;">
                                            <?php echo htmlspecialchars(wordwrap(substr($pd_title, 0, 30), 17, "<br/>", true), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php else: ?>
                                    <div style="width:100px;">
                                        <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>" target="_blank">
                                            <div class="cat_image_div">
                                                <img src="/images/noimage.jpg" class="cat_image" title="<?php echo $pd_title; ?>">
                                            </div>
                                        </a>
                                        <br>
                                        <span style="font-size:11px; color:#37366d;">
                                            <?php echo htmlspecialchars(substr($pd_title, 0, 15), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- صورة البانر -->
                                <div class="col-md-2">
                                    <?php if ($banner_exists): ?>
                                    <div style="width:100px;">
                                        <a href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>" target="_blank">
                                            <div class="cat_image_div">
                                                <img src="/upload/company_banner/<?php echo $banner_image; ?>" 
                                                     class="cat_image" title="<?php echo $bnsprof_compname; ?>">
                                            </div>
                                        </a>
                                        <br>
                                        <span style="font-size:11px; color:#37366d;">
                                            <?php echo htmlspecialchars(substr($bnsprof_compname, 0, 30), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php else: ?>
                                    <div style="width:100px;">
                                        <a href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>" target="_blank">
                                            <div class="cat_image_div">
                                                <img src="/images/noimage.jpg" class="cat_image" title="<?php echo $bnsprof_compname; ?>">
                                            </div>
                                        </a>
                                        <br>
                                        <span style="font-size:11px; color:#37366d;">
                                            <?php echo htmlspecialchars(substr($bnsprof_compname, 0, 15), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- صورة "من نحن" -->
                                <div class="col-md-2">
                                    <?php if ($about_exists): ?>
                                    <div style="width:100px;">
                                        <a <?php if (!empty($bnsprof_compname)): ?>href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>"<?php endif; ?> target="_blank">
                                            <div class="cat_image_div">
                                                <img src="<?php echo $about_path; ?>" class="cat_image" title="<?php echo $about_desc; ?>">
                                            </div>
                                        </a>
                                        <br>
                                        <span style="font-size:11px; color:#37366d;">
                                            <?php echo htmlspecialchars(substr($about_desc, 0, 35), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php else: ?>
                                    <div style="width:100px;">
                                        <a <?php if (!empty($bnsprof_compname)): ?>href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>"<?php endif; ?> target="_blank">
                                            <div class="cat_image_div">
                                                <img src="/images/noimage.jpg" class="cat_image" title="<?php echo $about_desc; ?>">
                                            </div>
                                        </a>
                                        <br>
                                        <span style="font-size:11px; color:#37366d;">
                                            <?php echo htmlspecialchars(substr($about_desc, 0, 35), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <b><?php echo $wc_content; ?></b><br>
                                <a href="company/profile.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>" target="_blank" class="fc td j1 g4 bg im3 lht pb2">About Us</a> &nbsp;&nbsp;
                                <a href="company/products.php?c=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>" target="_blank" class="fc td j1 g4 bg im4 pb2 lht">View Products</a><br>
                                
                                <b>Main Business : </b><?php echo htmlspecialchars(substr($main_business_str, 0, 70), ENT_QUOTES, 'UTF-8'); ?>...<br>
                                
                                <b>Business Type : </b><?php echo htmlspecialchars(substr($business_types_str, 0, 70), ENT_QUOTES, 'UTF-8'); ?>...<br>
                                
                                <?php if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm']) && $_SESSION['uid_indm'] != $bnsprof_uid): ?>
                                <a class="bg ima z1 e4 hl a2 td bo f2 wdt c6 td ajax" rel="nofollow" 
                                   href="sendenquiry-form.php?id=<?php echo rand(1000, 9999) . md5((string)$bnsprof_id); ?>">
                                    Send Enquiry
                                </a>
                                <?php endif; ?>
                                
                                <p class="g1">
                                    <b>Address:</b>&nbsp;
                                    <?php 
                                    $address_parts = [];
                                    if (!empty($bnsprof_address1)) $address_parts[] = $bnsprof_address1;
                                    if (!empty($bnsprof_address2)) $address_parts[] = $bnsprof_address2;
                                    if ($bnsprof_city > 0) $address_parts[] = htmlspecialchars(get_city_name($bnsprof_city), ENT_QUOTES, 'UTF-8');
                                    if ($bnsprof_state > 0) $address_parts[] = htmlspecialchars(get_state_name($bnsprof_state), ENT_QUOTES, 'UTF-8');
                                    echo implode(', ', $address_parts);
                                    ?><br>
                                    
                                    <?php if (!empty($user_mobile) || !empty($bnsprof_mobile2) || !empty($bnsprof_mobile3) || !empty($bnsprof_mobile4)): ?>
                                    <b><strong style="color:#2923ae; font-weight:bold;"> Phone:</strong></b>&nbsp;
                                    
                                    <?php if (!empty($user_mobile)): ?>
                                        <span id="pns1">0</span>
                                        <?php if (isset($_SESSION['uid_indm']) && !empty($_SESSION['uid_indm'])): ?>
                                            <?php echo $user_mobile; ?>
                                        <?php else: ?>
                                            <a class="a_tel" href="/sign-in.php#loginform">Show number</a>
                                        <?php endif; ?>
                                        <img alt="" style="position:absolute; margin-top:0px; margin-left:5px;" 
                                             class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($bnsprof_mobile2)): ?>
                                        <span id="pns1">0</span><?php echo $bnsprof_mobile2; ?>
                                        <img alt="" style="position:absolute; margin-top:0px; margin-left:5px;" 
                                             class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($bnsprof_mobile3)): ?>
                                        <span id="pns1">0</span><?php echo $bnsprof_mobile3; ?>
                                        <img alt="" style="position:absolute; margin-top:0px; margin-left:5px;" 
                                             class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20"><br>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($bnsprof_mobile4)): ?>
                                        <span id="pns1">0</span><?php echo $bnsprof_mobile4; ?>
                                        <img alt="" style="position:absolute; margin-top:0px; margin-left:5px;" 
                                             class="bg nr pnsPos1" src="images/zero.gif" height="20" width="20">
                                    <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <span id="sms1" class="tool-tip-lg soff">
                                        <span class="bg nr toolarw"></span>
                                        <span class="txt-call">&nbsp;</span>
                                    </span><br>
                                    
                                    <?php if (!empty($bnsprof_website_alt)): ?>
                                    <b>Website:</b> <a target="_blank" href="https://<?php echo $bnsprof_website_alt; ?>"><?php echo $bnsprof_website_alt; ?></a><br>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <br>
                    <?php endwhile; ?>
                    
                    <?php if (isset($_GET['keywords']) && $total_companies > $offset + $per_page): ?>
                    <div class="col-lg-12 text-center" style="padding:30px;">
                        <a href="https://egyptmart.shop/suppliers_search.php?rctyp=<?php echo urlencode($_GET['rctyp'] ?? ''); ?>&keywords=<?php echo urlencode($_GET['keywords']); ?>&page=<?php echo $page + 1; ?>">
                            <button type="button" class="btn btn-md btn-warning border-radius-0 btn-enquiry" style="font-size:16px; font-weight:bolder;">
                                Display More Suppliers
                            </button>
                        </a>
                    </div>
                    <?php endif; ?>
                    
                <?php endif; ?>
            </div>
        </div>
        
        <!-- الإعلان الجانبي -->
        <div class="wd6 z1 cr hide" id="hdiv">
            <br>
            <img src="images/z.gif" alt="" height="5">
            <div class="c9 brd fy1 gv w12">
                <?php
                $adv_sql = "SELECT adv_link, adv_img FROM advertisement 
                            WHERE adv_imagewidth = '300' AND adv_imageheight = '300' 
                            AND adv_status = '1' 
                            ORDER BY RAND() 
                            LIMIT 1";
                $adv_result = mysqli_query($con, $adv_sql);
                if (mysqli_num_rows($adv_result) > 0):
                    $adv_row = mysqli_fetch_assoc($adv_result);
                ?>
                <a href="//<?php echo htmlspecialchars($adv_row['adv_link'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                    <img src="upload/advertisement/<?php echo htmlspecialchars($adv_row['adv_img'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                         width="300" height="300" alt="Advertisement">
                </a>
                <?php else: ?>
                <img src="upload/advertisement/300-300-advertisement.png" width="300" height="300" alt="Advertisement">
                <?php endif; ?>
            </div>
            <br>
            <div class="bg im25" align="LEFT">
                <div class="bg im25 n9 a1"></div>
            </div>
            <div class="m2"></div>
            <div style="margin:27px 0px 0px 2px; float:left; width:245px;"></div>
        </div>
        
        <p class="m2"></p>
    </div>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
</body>
</html>
<?php
// إغلاق الاتصال بقاعدة البيانات
// mysqli_close($con);
?>